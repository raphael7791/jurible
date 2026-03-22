<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * SureCart Hooks — écoute les événements d'achat pour inscrire/désinscrire.
 *
 * Architecture SureCart :
 * 1. Le cloud SureCart envoie des webhooks à /surecart/webhooks
 * 2. Le plugin SureCart vérifie la signature, enregistre dans surecart_incoming_webhooks
 * 3. Traitement asynchrone : convertit purchase.created → do_action('surecart/purchase_created', $purchase)
 * 4. $purchase est un objet SureCart\Models\Purchase
 * 5. $purchase->getWPUser() résout le user WP via le meta sc_customer_ids
 *
 * Hooks écoutés :
 * - surecart/purchase_created  → nouvel achat (one-shot ou premier paiement abo)
 * - surecart/purchase_invoked  → accès réactivé (réabonnement, renouvellement)
 * - surecart/purchase_revoked  → accès révoqué (annulation, remboursement)
 *
 * Sécurités :
 * - Retry WP Cron à +30s si le user WordPress n'est pas encore créé (race condition)
 * - Anti-doublon : vérifie si déjà traité dans les 5 dernières minutes
 * - Max 3 retries pour éviter les boucles infinies
 * - ON/OFF switch via option jam_hooks_enabled
 * - Mode debug via option jam_debug_enabled
 *
 * NOTE — Hooks subscription non implémentés :
 * L'ancien plugin (surecart-thrive-integration sur ecole.aideauxtd.com) écoutait aussi :
 *   - subscription.set_to_cancel → gardait l'accès jusqu'à fin de période
 *   - subscription.canceled / subscription.completed → révocation immédiate
 *   - subscription.made_active → attribution
 *   - subscription.renewed → vérification que l'accès est toujours actif
 *   - subscription.updated → analyse du statut
 *   - refund.created → révocation
 * Ces events ne sont PAS dans la config webhook par défaut de SureCart (sauf subscription.renewed).
 * Les 3 hooks purchase couvrent normalement tout le cycle de vie car SureCart révoque
 * automatiquement le purchase quand un abo est annulé.
 * SI LES TESTS MONTRENT DES MANQUES → envisager d'ajouter ces hooks subscription
 * ou de modifier la config webhook dans SureCart (app/config.php → webhook_events).
 */
class JAM_SureCart_Hooks {

    const MAX_RETRIES       = 3;
    const RETRY_DELAY       = 30; // seconds
    const DEDUP_WINDOW      = 300; // 5 minutes in seconds
    const CRON_HOOK_RETRY   = 'jam_retry_webhook';

    /**
     * Initialize hooks.
     */
    public static function init() {
        // Toujours enregistrer le cron de retry (un retry peut être en attente)
        add_action( self::CRON_HOOK_RETRY, [ __CLASS__, 'handle_retry' ] );

        if ( ! self::is_enabled() ) {
            return;
        }

        // Hooks d'achat — priorité 10 (SureCart intégrations internes = 9)
        add_action( 'surecart/purchase_created', [ __CLASS__, 'on_purchase_created' ], 10, 2 );
        add_action( 'surecart/purchase_invoked', [ __CLASS__, 'on_purchase_invoked' ], 10, 2 );
        add_action( 'surecart/purchase_revoked', [ __CLASS__, 'on_purchase_revoked' ], 10, 2 );
    }

    /**
     * Check if hooks are enabled (admin toggle).
     */
    public static function is_enabled() {
        return (bool) get_option( 'jam_hooks_enabled', false );
    }

    /**
     * Enable/disable hooks.
     */
    public static function set_enabled( $enabled ) {
        update_option( 'jam_hooks_enabled', (bool) $enabled );
    }

    // ─── Hook Handlers ─────────────────────────────────────────

    /**
     * Nouvel achat confirmé — inscrire aux cours.
     */
    public static function on_purchase_created( $purchase, $webhook_data = null ) {
        self::handle_enrollment( $purchase, 'purchase_created' );
    }

    /**
     * Accès réactivé — réinscrire aux cours.
     */
    public static function on_purchase_invoked( $purchase, $webhook_data = null ) {
        self::handle_enrollment( $purchase, 'purchase_invoked' );
    }

    /**
     * Accès révoqué — désinscrire des cours.
     */
    public static function on_purchase_revoked( $purchase, $webhook_data = null ) {
        self::handle_revocation( $purchase );
    }

    // ─── Core Logic ────────────────────────────────────────────

    /**
     * Process enrollment for a purchase (created or invoked).
     */
    private static function handle_enrollment( $purchase, $event_name, $retry_count = 0 ) {
        try {
            $purchase_id = self::get_purchase_id( $purchase );
            $product_id  = self::get_product_id( $purchase );
            $price_id    = self::get_price_id( $purchase );

            self::log_debug( $event_name, [
                'purchase_id' => $purchase_id,
                'product_id'  => $product_id,
                'price_id'    => $price_id,
                'retry'       => $retry_count,
            ] );

            if ( ! $product_id ) {
                self::log_debug( $event_name . '_skip', [ 'reason' => 'No product ID' ] );
                return;
            }

            // Find matching rules for this product
            $rules = JAM_Access_Rules::find_by_product( $product_id );
            if ( empty( $rules ) ) {
                // No rule = PDF or unconfigured product → apply catch-all FluentCRM tags
                self::log_debug( $event_name . '_pdf_catchall', [
                    'product_id' => $product_id,
                ] );
                self::apply_pdf_catchall( $purchase, $event_name, $retry_count );
                return;
            }

            // Resolve WP user
            $wp_user = self::resolve_user( $purchase );
            if ( ! $wp_user ) {
                // User pas trouvé — programmer un retry si pas déjà au max
                self::schedule_retry( $purchase, $event_name, $retry_count );
                return;
            }

            // Anti-doublon : vérifier si déjà traité récemment
            if ( self::is_recently_processed( $wp_user->ID, $product_id, 'enrollment' ) ) {
                self::log_debug( $event_name . '_dedup', [
                    'reason'     => 'Already processed in last 5 min',
                    'user_email' => $wp_user->user_email,
                    'product_id' => $product_id,
                ] );
                return;
            }

            // Marquer comme traité
            self::mark_processed( $wp_user->ID, $product_id, 'enrollment' );

            // Apply each matching rule
            foreach ( $rules as $rule ) {
                $report = JAM_Enrollment::apply_rule( $wp_user->ID, $rule, 'surecart', $purchase_id, $price_id, $event_name );

                self::log_debug( $event_name . '_applied', [
                    'rule_id'    => $rule->id,
                    'rule_name'  => $rule->rule_name,
                    'user_email' => $wp_user->user_email,
                    'price_id'   => $price_id,
                    'report'     => $report,
                ] );
            }

            // FluentCRM (if available)
            if ( class_exists( 'JAM_FluentCRM' ) && method_exists( 'JAM_FluentCRM', 'on_purchase' ) ) {
                foreach ( $rules as $rule ) {
                    JAM_FluentCRM::on_purchase( $wp_user->ID, $rule );
                }
            }

        } catch ( \Exception $e ) {
            self::log_debug( $event_name . '_exception', [
                'error' => $e->getMessage(),
                'trace' => substr( $e->getTraceAsString(), 0, 500 ),
            ] );
        }
    }

    /**
     * Process revocation for a purchase.
     */
    private static function handle_revocation( $purchase, $retry_count = 0 ) {
        try {
            $purchase_id = self::get_purchase_id( $purchase );
            $product_id  = self::get_product_id( $purchase );

            self::log_debug( 'purchase_revoked', [
                'purchase_id' => $purchase_id,
                'product_id'  => $product_id,
                'retry'       => $retry_count,
            ] );

            if ( ! $product_id ) {
                self::log_debug( 'purchase_revoked_skip', [ 'reason' => 'No product ID' ] );
                return;
            }

            // Find matching rules
            $rules = JAM_Access_Rules::find_by_product( $product_id );
            if ( empty( $rules ) ) {
                return;
            }

            // Resolve WP user
            $wp_user = self::resolve_user( $purchase );
            if ( ! $wp_user ) {
                // Pour la révocation, on programme aussi un retry
                self::schedule_retry( $purchase, 'purchase_revoked', $retry_count );
                return;
            }

            // Anti-doublon
            if ( self::is_recently_processed( $wp_user->ID, $product_id, 'revocation' ) ) {
                self::log_debug( 'purchase_revoked_dedup', [
                    'reason'     => 'Already processed in last 5 min',
                    'user_email' => $wp_user->user_email,
                ] );
                return;
            }

            self::mark_processed( $wp_user->ID, $product_id, 'revocation' );

            // Revoke each matching rule
            foreach ( $rules as $rule ) {
                $report = JAM_Enrollment::revoke_rule( $wp_user->ID, $rule, 'surecart', $purchase_id );

                self::log_debug( 'purchase_revoked_applied', [
                    'rule_id'    => $rule->id,
                    'rule_name'  => $rule->rule_name,
                    'user_email' => $wp_user->user_email,
                    'report'     => $report,
                ] );
            }

            // FluentCRM (if available)
            if ( class_exists( 'JAM_FluentCRM' ) && method_exists( 'JAM_FluentCRM', 'on_revoke' ) ) {
                foreach ( $rules as $rule ) {
                    JAM_FluentCRM::on_revoke( $wp_user->ID, $rule );
                }
            }

        } catch ( \Exception $e ) {
            self::log_debug( 'purchase_revoked_exception', [
                'error' => $e->getMessage(),
            ] );
        }
    }

    // ─── PDF Catch-All ───────────────────────────────────────

    /**
     * Catch-all for products without rules (PDFs, etc.)
     * Applies Tag PDF (82) + Liste générale (1) via FluentCRM.
     */
    private static function apply_pdf_catchall( $purchase, $event_name, $retry_count = 0 ) {
        $wp_user = self::resolve_user( $purchase );
        if ( ! $wp_user ) {
            self::schedule_retry( $purchase, $event_name, $retry_count );
            return;
        }

        $product_id = self::get_product_id( $purchase );

        // Anti-doublon
        if ( self::is_recently_processed( $wp_user->ID, $product_id, 'pdf_catchall' ) ) {
            return;
        }
        self::mark_processed( $wp_user->ID, $product_id, 'pdf_catchall' );

        // FluentCRM : Tag PDF (82) + Liste générale (1)
        if ( function_exists( 'FluentCrmApi' ) ) {
            $contact = FluentCrmApi( 'contacts' )->createOrUpdate( [
                'email'  => $wp_user->user_email,
                'status' => 'subscribed',
            ] );

            if ( $contact && ! is_wp_error( $contact ) ) {
                $contact->attachTags( [ 82 ] );
                $contact->attachLists( [ 1 ] );
            }

            self::log_debug( $event_name . '_pdf_catchall_applied', [
                'user_email' => $wp_user->user_email,
                'product_id' => $product_id,
                'tag'        => 'PDF (82)',
                'list'       => 'Liste générale (1)',
            ] );
        }
    }

    // ─── Retry Mechanism ───────────────────────────────────────

    /**
     * Schedule a WP Cron retry when user is not found.
     * Race condition: SureCart peut envoyer le webhook avant que le user WP soit créé.
     */
    private static function schedule_retry( $purchase, $event_name, $retry_count ) {
        if ( $retry_count >= self::MAX_RETRIES ) {
            self::log_debug( $event_name . '_max_retries', [
                'error'       => 'User not found after ' . self::MAX_RETRIES . ' retries — giving up',
                'purchase_id' => self::get_purchase_id( $purchase ),
                'product_id'  => self::get_product_id( $purchase ),
            ] );

            // Log en tant qu'erreur dans le access_log pour visibilité admin
            JAM_Access_Log::log( [
                'user_id'        => 0,
                'user_email'     => self::get_customer_email( $purchase ),
                'fcom_course_id' => 0,
                'action'         => $event_name === 'purchase_revoked' ? 'unenrolled' : 'enrolled',
                'source'         => 'surecart',
                'sc_purchase_id' => self::get_purchase_id( $purchase ),
                'details'        => wp_json_encode( [
                    'error'      => 'User WordPress introuvable après ' . self::MAX_RETRIES . ' tentatives',
                    'product_id' => self::get_product_id( $purchase ),
                    'email'      => self::get_customer_email( $purchase ),
                ] ),
            ] );
            return;
        }

        // Extraire les données essentielles (sérialisables pour le cron)
        $retry_data = [
            'purchase_id' => self::get_purchase_id( $purchase ),
            'product_id'  => self::get_product_id( $purchase ),
            'price_id'    => self::get_price_id( $purchase ),
            'customer_id' => self::get_customer_id( $purchase ),
            'event_name'  => $event_name,
            'retry_count' => $retry_count + 1,
        ];

        $delay = self::RETRY_DELAY * ( $retry_count + 1 ); // 30s, 60s, 90s

        wp_schedule_single_event( time() + $delay, self::CRON_HOOK_RETRY, [ $retry_data ] );

        self::log_debug( $event_name . '_retry_scheduled', [
            'retry_count' => $retry_count + 1,
            'delay'       => $delay . 's',
            'purchase_id' => $retry_data['purchase_id'],
        ] );
    }

    /**
     * Handle a scheduled retry from WP Cron.
     */
    public static function handle_retry( $retry_data ) {
        if ( ! is_array( $retry_data ) ) {
            return;
        }

        $event_name  = $retry_data['event_name'] ?? 'unknown';
        $retry_count = (int) ( $retry_data['retry_count'] ?? 1 );
        $purchase_id = $retry_data['purchase_id'] ?? '';
        $product_id  = $retry_data['product_id'] ?? '';
        $customer_id = $retry_data['customer_id'] ?? '';

        self::log_debug( 'retry_processing', [
            'event_name'  => $event_name,
            'retry_count' => $retry_count,
            'purchase_id' => $purchase_id,
        ] );

        // Reconstituer un objet purchase minimal pour le traitement
        // D'abord essayer de charger le vrai objet depuis l'API
        $purchase = self::fetch_purchase( $purchase_id );

        $price_id    = $retry_data['price_id'] ?? '';

        if ( ! $purchase ) {
            // Fallback : objet minimal avec les données du retry
            $purchase = (object) [
                'id'       => $purchase_id,
                'product'  => $product_id,
                'price'    => $price_id,
                'customer' => $customer_id,
            ];
        }

        if ( $event_name === 'purchase_revoked' ) {
            self::handle_revocation( $purchase, $retry_count );
        } else {
            self::handle_enrollment( $purchase, $event_name, $retry_count );
        }
    }

    /**
     * Fetch a purchase object from SureCart API.
     */
    private static function fetch_purchase( $purchase_id ) {
        if ( ! $purchase_id ) {
            return null;
        }

        if ( class_exists( '\SureCart\Models\Purchase' ) ) {
            try {
                return \SureCart\Models\Purchase::with( [ 'product', 'customer' ] )->find( $purchase_id );
            } catch ( \Exception $e ) {
                self::log_debug( 'fetch_purchase_error', [
                    'purchase_id' => $purchase_id,
                    'error'       => $e->getMessage(),
                ] );
            }
        }

        return null;
    }

    // ─── Anti-Duplicate ────────────────────────────────────────

    /**
     * Check if this user+product combination was already processed recently.
     * Prevents duplicate enrollments from multiple webhook events for the same purchase.
     */
    private static function is_recently_processed( $user_id, $product_id, $type ) {
        $key   = "jam_processed_{$type}_{$user_id}_{$product_id}";
        $value = get_transient( $key );
        return ! empty( $value );
    }

    /**
     * Mark a user+product combination as processed.
     */
    private static function mark_processed( $user_id, $product_id, $type ) {
        $key = "jam_processed_{$type}_{$user_id}_{$product_id}";
        set_transient( $key, time(), self::DEDUP_WINDOW );
    }

    // ─── Helpers ────────────────────────────────────────────────

    /**
     * Resolve WP user from purchase.
     * Uses SureCart's built-in getWPUser() which searches by sc_customer_ids meta.
     */
    private static function resolve_user( $purchase ) {
        // Method 1: SureCart's built-in method
        if ( is_object( $purchase ) && method_exists( $purchase, 'getWPUser' ) ) {
            $wp_user = $purchase->getWPUser();
            if ( $wp_user ) {
                return $wp_user;
            }
        }

        // Method 2: Try to get email from customer relation
        $email = self::get_customer_email( $purchase );
        if ( $email ) {
            $user = get_user_by( 'email', $email );
            if ( $user ) {
                return $user;
            }
        }

        // Method 3: Fetch full purchase with customer via API
        $purchase_id = self::get_purchase_id( $purchase );
        if ( $purchase_id && class_exists( '\SureCart\Models\Purchase' ) ) {
            try {
                $full = \SureCart\Models\Purchase::with( [ 'customer' ] )->find( $purchase_id );
                if ( $full ) {
                    if ( method_exists( $full, 'getWPUser' ) ) {
                        $wp_user = $full->getWPUser();
                        if ( $wp_user ) {
                            return $wp_user;
                        }
                    }
                    if ( isset( $full->customer->email ) ) {
                        $user = get_user_by( 'email', $full->customer->email );
                        if ( $user ) {
                            return $user;
                        }
                    }
                }
            } catch ( \Exception $e ) {
                // Continue
            }
        }

        return null;
    }

    /**
     * Get purchase ID.
     */
    private static function get_purchase_id( $purchase ) {
        if ( is_object( $purchase ) ) {
            return $purchase->id ?? '';
        }
        if ( is_array( $purchase ) ) {
            return $purchase['id'] ?? '';
        }
        return '';
    }

    /**
     * Get product ID from purchase.
     */
    private static function get_product_id( $purchase ) {
        if ( is_object( $purchase ) ) {
            $product = $purchase->product ?? null;
        } elseif ( is_array( $purchase ) ) {
            $product = $purchase['product'] ?? null;
        } else {
            return '';
        }

        if ( is_object( $product ) ) {
            return $product->id ?? '';
        }
        if ( is_array( $product ) ) {
            return $product['id'] ?? '';
        }
        if ( is_string( $product ) ) {
            return $product;
        }
        return '';
    }

    /**
     * Get price ID from purchase.
     */
    private static function get_price_id( $purchase ) {
        if ( is_object( $purchase ) ) {
            // SureCart Purchase object may have price or price_id
            if ( isset( $purchase->price ) ) {
                $price = $purchase->price;
                if ( is_object( $price ) ) {
                    return $price->id ?? '';
                }
                if ( is_string( $price ) ) {
                    return $price;
                }
            }
            if ( isset( $purchase->price_id ) ) {
                return $purchase->price_id;
            }
        }
        if ( is_array( $purchase ) ) {
            if ( isset( $purchase['price'] ) ) {
                $price = $purchase['price'];
                if ( is_array( $price ) ) {
                    return $price['id'] ?? '';
                }
                if ( is_string( $price ) ) {
                    return $price;
                }
            }
            if ( isset( $purchase['price_id'] ) ) {
                return $purchase['price_id'];
            }
        }
        return '';
    }

    /**
     * Get customer ID from purchase.
     */
    private static function get_customer_id( $purchase ) {
        if ( ! is_object( $purchase ) && ! is_array( $purchase ) ) {
            return '';
        }

        $customer = is_object( $purchase ) ? ( $purchase->customer ?? null ) : ( $purchase['customer'] ?? null );

        if ( is_object( $customer ) ) {
            return $customer->id ?? '';
        }
        if ( is_string( $customer ) ) {
            return $customer;
        }
        return '';
    }

    /**
     * Get customer email from purchase.
     */
    private static function get_customer_email( $purchase ) {
        if ( ! is_object( $purchase ) && ! is_array( $purchase ) ) {
            return '';
        }

        $customer = is_object( $purchase ) ? ( $purchase->customer ?? null ) : ( $purchase['customer'] ?? null );

        if ( is_object( $customer ) && isset( $customer->email ) ) {
            return sanitize_email( $customer->email );
        }

        // Customer is a string ID — resolve via API
        $customer_id = '';
        if ( is_string( $customer ) && ! empty( $customer ) ) {
            $customer_id = $customer;
        } elseif ( is_object( $customer ) && isset( $customer->id ) ) {
            $customer_id = $customer->id;
        }

        if ( $customer_id ) {
            return self::resolve_email_from_customer_id( $customer_id );
        }

        return '';
    }

    /**
     * Resolve email from a SureCart customer ID.
     */
    private static function resolve_email_from_customer_id( $customer_id ) {
        // Try SureCart model first
        if ( class_exists( '\SureCart\Models\Customer' ) ) {
            try {
                $customer = \SureCart\Models\Customer::find( $customer_id );
                if ( $customer && isset( $customer->email ) ) {
                    return sanitize_email( $customer->email );
                }
            } catch ( \Exception $e ) {
                // Continue
            }
        }

        // Fallback: REST API
        $token = JAM_Helpers::get_sc_api_token();
        if ( $token ) {
            $response = wp_remote_get( "https://api.surecart.com/v1/customers/{$customer_id}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ],
                'timeout' => 10,
            ] );

            if ( ! is_wp_error( $response ) ) {
                $body = json_decode( wp_remote_retrieve_body( $response ), true );
                return sanitize_email( $body['email'] ?? '' );
            }
        }

        return '';
    }

    /**
     * Debug log to wp_options (viewable in admin on the Historique page).
     */
    private static function log_debug( $event, $data = [] ) {
        if ( ! get_option( 'jam_debug_enabled', false ) ) {
            return;
        }

        $logs   = get_option( 'jam_debug_logs', [] );
        $logs[] = [
            'time'  => current_time( 'mysql' ),
            'event' => $event,
            'data'  => $data,
        ];

        // Keep only last 200 entries
        if ( count( $logs ) > 200 ) {
            $logs = array_slice( $logs, -200 );
        }

        update_option( 'jam_debug_logs', $logs, false );
    }
}
