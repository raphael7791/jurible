<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Jaide_Access {

    /**
     * Vérifie si l'utilisateur a accès à l'aide personnalisée (Formule Réussite).
     */
    public static function user_has_access( $user_id = null ) {
        $user_id = $user_id ?: get_current_user_id();
        if ( ! $user_id ) {
            return false;
        }

        $options    = get_option( 'jaide_options', [] );
        $product_id = $options['product_id'] ?? '';

        // Si pas de product ID configuré, accès libre
        if ( empty( $product_id ) ) {
            return true;
        }

        // Vérifier via SureCart : l'user a-t-il un achat actif pour ce produit ?
        return self::check_surecart_purchase( $user_id, $product_id );
    }

    /**
     * Vérifie un achat SureCart actif pour un produit donné.
     */
    private static function check_surecart_purchase( $user_id, $product_id ) {
        // Récupérer le customer ID SureCart du user
        $customer_ids = get_user_meta( $user_id, 'sc_customer_ids', true );

        if ( empty( $customer_ids ) || ! is_array( $customer_ids ) ) {
            return false;
        }

        // Utiliser l'API SureCart pour vérifier les achats
        if ( ! function_exists( 'sc_api_request' ) && ! class_exists( '\SureCart\Models\Purchase' ) ) {
            // SureCart non installé — fallback accès libre
            return true;
        }

        try {
            foreach ( $customer_ids as $account_id => $customer_id ) {
                $purchases = \SureCart\Models\Purchase::where( [
                    'customer_ids' => [ $customer_id ],
                    'product_ids'  => [ $product_id ],
                    'live_mode'    => true,
                ] )->get();

                if ( ! empty( $purchases ) && ! is_wp_error( $purchases ) ) {
                    foreach ( $purchases as $purchase ) {
                        $status = $purchase->status ?? '';
                        if ( in_array( $status, [ 'active', 'trialing', 'paid' ], true ) ) {
                            return true;
                        }
                        // Pour les achats uniques, revoked_at null = actif
                        if ( empty( $purchase->revoked_at ) && $status !== 'revoked' ) {
                            return true;
                        }
                    }
                }
            }
        } catch ( \Exception $e ) {
            // En cas d'erreur API, log et refuser l'accès
            error_log( 'Jaide Access check error: ' . $e->getMessage() );
            return false;
        }

        return false;
    }

    /**
     * Retourne la limite de copies pour un user.
     * Override individuel (user meta) > limite globale (settings).
     * Meta vide = utilise la globale. 0 = illimité.
     */
    public static function get_copies_limit( $user_id = null ) {
        $user_id  = $user_id ?: get_current_user_id();
        $override = get_user_meta( $user_id, 'jaide_copies_limit', true );

        if ( $override !== '' && $override !== false ) {
            return (int) $override;
        }

        $options = get_option( 'jaide_options', [] );
        return (int) ( $options['copies_limit'] ?? 1 );
    }

    /**
     * Retourne la limite de questions pour un user.
     */
    public static function get_questions_limit( $user_id = null ) {
        $user_id  = $user_id ?: get_current_user_id();
        $override = get_user_meta( $user_id, 'jaide_questions_limit', true );

        if ( $override !== '' && $override !== false ) {
            return (int) $override;
        }

        $options = get_option( 'jaide_options', [] );
        return (int) ( $options['questions_limit'] ?? 0 );
    }

    /**
     * Nombre de copies restantes.
     */
    public static function copies_remaining( $user_id = null ) {
        global $wpdb;

        $user_id = $user_id ?: get_current_user_id();
        $limit   = self::get_copies_limit( $user_id );

        if ( $limit === 0 ) {
            return PHP_INT_MAX;
        }

        $table = $wpdb->prefix . 'jurible_aide_requests';
        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND type = 'copie'",
            $user_id
        ) );

        return max( 0, $limit - $count );
    }

    /**
     * Nombre de questions restantes.
     */
    public static function questions_remaining( $user_id = null ) {
        global $wpdb;

        $user_id = $user_id ?: get_current_user_id();
        $limit   = self::get_questions_limit( $user_id );

        if ( $limit === 0 ) {
            return PHP_INT_MAX;
        }

        $table = $wpdb->prefix . 'jurible_aide_requests';
        $count = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND type = 'question'",
            $user_id
        ) );

        return max( 0, $limit - $count );
    }

    /**
     * Infos crédits détaillées pour un user (page admin Crédits).
     */
    public static function get_credits_info( $user_id ) {
        global $wpdb;

        $table          = $wpdb->prefix . 'jurible_aide_requests';
        $copies_limit   = self::get_copies_limit( $user_id );
        $questions_limit = self::get_questions_limit( $user_id );

        $copies_used = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND type = 'copie'", $user_id
        ) );
        $questions_used = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND type = 'question'", $user_id
        ) );

        $options = get_option( 'jaide_options', [] );

        return [
            'copies_limit'       => $copies_limit,
            'copies_limit_global'=> (int) ( $options['copies_limit'] ?? 1 ),
            'copies_has_override'=> get_user_meta( $user_id, 'jaide_copies_limit', true ) !== '',
            'copies_used'        => $copies_used,
            'copies_remaining'   => $copies_limit === 0 ? PHP_INT_MAX : max( 0, $copies_limit - $copies_used ),

            'questions_limit'       => $questions_limit,
            'questions_limit_global'=> (int) ( $options['questions_limit'] ?? 0 ),
            'questions_has_override'=> get_user_meta( $user_id, 'jaide_questions_limit', true ) !== '',
            'questions_used'        => $questions_used,
            'questions_remaining'   => $questions_limit === 0 ? PHP_INT_MAX : max( 0, $questions_limit - $questions_used ),
        ];
    }
}
