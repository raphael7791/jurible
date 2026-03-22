<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class JAM_Enrollment {

    /**
     * Enroll a user in a Fluent Community course.
     *
     * @param int    $user_id   WordPress user ID.
     * @param int    $course_id Fluent Community course (space) ID.
     * @param string $source    Source of enrollment: 'surecart', 'manual', 'sync'.
     * @param string $sc_purchase_id Optional SureCart purchase ID.
     * @return true|WP_Error
     */
    public static function enroll_user( $user_id, $course_id, $source = 'surecart', $sc_purchase_id = '' ) {
        // Check if already enrolled
        if ( self::is_enrolled( $user_id, $course_id ) ) {
            return true;
        }

        $enrolled = false;

        // Method 1: Fluent Community CourseHelper
        if ( class_exists( '\FluentCommunity\Modules\Course\Services\CourseHelper' ) ) {
            try {
                $course = null;
                if ( class_exists( '\FluentCommunity\Modules\Course\Model\Course' ) ) {
                    $course = \FluentCommunity\Modules\Course\Model\Course::find( $course_id );
                }

                if ( $course ) {
                    \FluentCommunity\Modules\Course\Services\CourseHelper::enrollCourse( $course, $user_id, 'by_admin' );
                    $enrolled = true;
                }
            } catch ( \Exception $e ) {
                // Fall through to method 2
            }
        }

        // Method 2: Direct DB insert (fallback)
        if ( ! $enrolled ) {
            global $wpdb;
            $table = $wpdb->prefix . 'fcom_space_user';

            if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
                return new \WP_Error( 'no_table', 'Table fcom_space_user introuvable.' );
            }

            // Check if relation already exists
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE space_id = %d AND user_id = %d",
                $course_id,
                $user_id
            ) );

            if ( ! $exists ) {
                $result = $wpdb->insert( $table, [
                    'space_id'   => $course_id,
                    'user_id'    => $user_id,
                    'role'       => 'member',
                    'status'     => 'active',
                    'created_at' => current_time( 'mysql' ),
                    'updated_at' => current_time( 'mysql' ),
                ] );

                if ( $result === false ) {
                    return new \WP_Error( 'db_error', 'Erreur lors de l\'insertion en BDD.' );
                }
            }

            $enrolled = true;
        }

        // Log the enrollment
        $user = get_user_by( 'id', $user_id );
        JAM_Access_Log::log( [
            'user_id'        => $user_id,
            'user_email'     => $user ? $user->user_email : '',
            'fcom_course_id' => $course_id,
            'action'         => 'enrolled',
            'source'         => $source,
            'sc_purchase_id' => $sc_purchase_id ?: null,
            'details'        => wp_json_encode( [
                'course_id' => $course_id,
                'method'    => class_exists( '\FluentCommunity\Modules\Course\Services\CourseHelper' ) ? 'CourseHelper' : 'direct_db',
            ] ),
        ] );

        return true;
    }

    /**
     * Unenroll a user from a Fluent Community course.
     *
     * @param int    $user_id   WordPress user ID.
     * @param int    $course_id Fluent Community course (space) ID.
     * @param string $source    Source of unenrollment.
     * @param string $sc_purchase_id Optional SureCart purchase ID.
     * @return true|WP_Error
     */
    public static function unenroll_user( $user_id, $course_id, $source = 'surecart', $sc_purchase_id = '' ) {
        // Check if enrolled
        if ( ! self::is_enrolled( $user_id, $course_id ) ) {
            return true;
        }

        $unenrolled = false;

        // Method 1: Fluent Community CourseHelper
        if ( class_exists( '\FluentCommunity\Modules\Course\Services\CourseHelper' ) ) {
            try {
                $course = null;
                if ( class_exists( '\FluentCommunity\Modules\Course\Model\Course' ) ) {
                    $course = \FluentCommunity\Modules\Course\Model\Course::find( $course_id );
                }

                if ( $course ) {
                    \FluentCommunity\Modules\Course\Services\CourseHelper::leaveCourse( $course, $user_id, 'by_admin' );
                    $unenrolled = true;
                }
            } catch ( \Exception $e ) {
                // Fall through to method 2
            }
        }

        // Method 2: Direct DB delete (fallback)
        if ( ! $unenrolled ) {
            global $wpdb;
            $table = $wpdb->prefix . 'fcom_space_user';

            if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
                return new \WP_Error( 'no_table', 'Table fcom_space_user introuvable.' );
            }

            $wpdb->delete( $table, [
                'space_id' => $course_id,
                'user_id'  => $user_id,
            ] );

            $unenrolled = true;
        }

        // Log the unenrollment
        $user = get_user_by( 'id', $user_id );
        JAM_Access_Log::log( [
            'user_id'        => $user_id,
            'user_email'     => $user ? $user->user_email : '',
            'fcom_course_id' => $course_id,
            'action'         => 'unenrolled',
            'source'         => $source,
            'sc_purchase_id' => $sc_purchase_id ?: null,
            'details'        => wp_json_encode( [
                'course_id' => $course_id,
                'method'    => class_exists( '\FluentCommunity\Modules\Course\Services\CourseHelper' ) ? 'CourseHelper' : 'direct_db',
            ] ),
        ] );

        return true;
    }

    /**
     * Check if a user is enrolled in a course.
     */
    public static function is_enrolled( $user_id, $course_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'fcom_space_user';

        if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) {
            return false;
        }

        return (bool) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE space_id = %d AND user_id = %d",
            $course_id,
            $user_id
        ) );
    }

    /**
     * Process enrollment for all courses in a rule.
     *
     * @param int    $user_id  WordPress user ID.
     * @param object $rule     Access rule object.
     * @param string $source   Source of enrollment.
     * @param string $sc_purchase_id Optional SureCart purchase ID.
     * @param string $price_id Optional SureCart price ID (for credit_price_map).
     * @param string $event_name Optional event name ('purchase_created' or 'purchase_invoked').
     * @return array Report: ['enrolled' => N, 'already' => N, 'errors' => N]
     */
    public static function apply_rule( $user_id, $rule, $source = 'surecart', $sc_purchase_id = '', $price_id = '', $event_name = '' ) {
        $course_ids = JAM_Access_Rules::get_course_ids( $rule );
        $report     = [ 'enrolled' => 0, 'already' => 0, 'errors' => 0 ];

        foreach ( $course_ids as $course_id ) {
            if ( self::is_enrolled( $user_id, $course_id ) ) {
                $report['already']++;
                continue;
            }

            $result = self::enroll_user( $user_id, $course_id, $source, $sc_purchase_id );
            if ( is_wp_error( $result ) ) {
                $report['errors']++;
            } else {
                $report['enrolled']++;
            }
        }

        // Handle aide personnalisée
        if ( ! empty( $rule->aide_perso_enabled ) ) {
            update_user_meta( $user_id, 'jam_aide_perso_access', '1' );

            // MAX(existing, rule) — pour gérer un user avec plusieurs produits
            $current_copies = (int) get_user_meta( $user_id, 'jam_aide_perso_copies_limit', true );
            $rule_copies    = (int) $rule->aide_perso_copies;
            if ( $rule_copies > $current_copies ) {
                update_user_meta( $user_id, 'jam_aide_perso_copies_limit', $rule_copies );
            }

            $current_questions = (int) get_user_meta( $user_id, 'jam_aide_perso_questions_limit', true );
            $rule_questions    = (int) $rule->aide_perso_questions;
            if ( $rule_questions > $current_questions ) {
                update_user_meta( $user_id, 'jam_aide_perso_questions_limit', $rule_questions );
            }

            $user = get_user_by( 'id', $user_id );
            JAM_Access_Log::log( [
                'user_id'        => $user_id,
                'user_email'     => $user ? $user->user_email : '',
                'fcom_course_id' => 0,
                'action'         => 'aide_perso_granted',
                'source'         => $source,
                'sc_purchase_id' => $sc_purchase_id ?: null,
                'details'        => wp_json_encode( [
                    'copies_limit'    => max( $current_copies, $rule_copies ),
                    'questions_limit' => max( $current_questions, $rule_questions ),
                    'rule_name'       => $rule->rule_name,
                ] ),
            ] );
        }

        // Handle credits
        $credits_to_add = self::resolve_credits( $rule, $price_id );

        if ( $credits_to_add > 0 ) {
            // For non-Minos products (no price_map), only add credits on first purchase
            $price_map = JAM_Access_Rules::get_credit_price_map( $rule );
            $is_price_mapped = ! empty( $price_map );

            if ( ! $is_price_mapped ) {
                // Bonus credits (e.g. Académie): only on first purchase, not renewals
                if ( $event_name === 'purchase_invoked' ) {
                    // Renewal — skip bonus credits
                    return $report;
                }

                // Permanent flag: credits only once per product per user
                $flag_key = 'jam_credits_granted_' . $rule->sc_product_id;
                if ( get_user_meta( $user_id, $flag_key, true ) ) {
                    return $report;
                }
                update_user_meta( $user_id, $flag_key, time() );
            }

            // Use aga_add_credits() if available (academic-generator plugin), else raw meta
            if ( function_exists( 'aga_add_credits' ) ) {
                $new_total = aga_add_credits( $user_id, $credits_to_add );
            } else {
                $current   = max( 0, (int) get_user_meta( $user_id, 'aga_credits', true ) );
                $new_total = $current + $credits_to_add;
                update_user_meta( $user_id, 'aga_credits', $new_total );
            }

            $user = get_user_by( 'id', $user_id );
            JAM_Access_Log::log( [
                'user_id'        => $user_id,
                'user_email'     => $user ? $user->user_email : '',
                'fcom_course_id' => 0,
                'action'         => 'credits_added',
                'source'         => $source,
                'sc_purchase_id' => $sc_purchase_id ?: null,
                'details'        => wp_json_encode( [
                    'credits_added' => $credits_to_add,
                    'new_total'     => $new_total,
                    'price_id'      => $price_id ?: null,
                    'rule_name'     => $rule->rule_name,
                ] ),
            ] );
        }

        return $report;
    }

    /**
     * Resolve how many credits to add for a rule + price_id.
     *
     * If credit_price_map is set and price_id matches, use that amount.
     * Otherwise, fall back to credit_amount.
     *
     * @param object $rule
     * @param string $price_id
     * @return int
     */
    private static function resolve_credits( $rule, $price_id = '' ) {
        // Check credit_price_map first
        if ( $price_id ) {
            $price_map = JAM_Access_Rules::get_credit_price_map( $rule );
            if ( ! empty( $price_map ) && isset( $price_map[ $price_id ] ) ) {
                return max( 0, (int) $price_map[ $price_id ] );
            }
        }

        // Fallback to credit_amount
        return max( 0, (int) ( $rule->credit_amount ?? 0 ) );
    }

    /**
     * Process unenrollment for all courses in a rule.
     *
     * @param int    $user_id  WordPress user ID.
     * @param object $rule     Access rule object.
     * @param string $source   Source.
     * @param string $sc_purchase_id Optional SureCart purchase ID.
     * @return array Report.
     */
    public static function revoke_rule( $user_id, $rule, $source = 'surecart', $sc_purchase_id = '' ) {
        $course_ids = JAM_Access_Rules::get_course_ids( $rule );
        $report     = [ 'unenrolled' => 0, 'not_enrolled' => 0, 'errors' => 0 ];

        foreach ( $course_ids as $course_id ) {
            if ( ! self::is_enrolled( $user_id, $course_id ) ) {
                $report['not_enrolled']++;
                continue;
            }

            $result = self::unenroll_user( $user_id, $course_id, $source, $sc_purchase_id );
            if ( is_wp_error( $result ) ) {
                $report['errors']++;
            } else {
                $report['unenrolled']++;
            }
        }

        // Recalculate aide perso from remaining active rules for this user
        if ( ! empty( $rule->aide_perso_enabled ) ) {
            self::recalculate_aide_perso( $user_id, $source, $sc_purchase_id );
        }

        return $report;
    }

    /**
     * Recalculate aide perso limits from all active rules for a user.
     * Called after revoking a rule that had aide_perso_enabled.
     */
    private static function recalculate_aide_perso( $user_id, $source = '', $sc_purchase_id = '' ) {
        // Get all product IDs the user still has active purchases for
        $customer_ids = get_user_meta( $user_id, 'sc_customer_ids', true );
        if ( empty( $customer_ids ) || ! is_array( $customer_ids ) ) {
            // No active purchases — remove aide perso access
            delete_user_meta( $user_id, 'jam_aide_perso_access' );
            delete_user_meta( $user_id, 'jam_aide_perso_copies_limit' );
            delete_user_meta( $user_id, 'jam_aide_perso_questions_limit' );
            return;
        }

        // Find all rules with aide_perso_enabled
        $all_rules = JAM_Access_Rules::get_all();
        $max_copies    = 0;
        $max_questions = 0;
        $has_aide      = false;

        foreach ( $all_rules as $r ) {
            if ( empty( $r->aide_perso_enabled ) ) {
                continue;
            }

            // Check if user still has an active purchase for this product
            // We check by looking at the user's SC purchases
            $still_active = false;
            if ( class_exists( '\SureCart\Models\Purchase' ) ) {
                try {
                    foreach ( $customer_ids as $account_id => $customer_id ) {
                        $purchases = \SureCart\Models\Purchase::where( [
                            'customer_ids' => [ $customer_id ],
                            'product_ids'  => [ $r->sc_product_id ],
                            'live_mode'    => true,
                        ] )->get();

                        if ( ! empty( $purchases ) && ! is_wp_error( $purchases ) ) {
                            foreach ( $purchases as $purchase ) {
                                $status = $purchase->status ?? '';
                                if ( in_array( $status, [ 'active', 'trialing', 'paid' ], true ) ) {
                                    $still_active = true;
                                    break 2;
                                }
                                if ( empty( $purchase->revoked_at ) && $status !== 'revoked' ) {
                                    $still_active = true;
                                    break 2;
                                }
                            }
                        }
                    }
                } catch ( \Exception $e ) {
                    // Skip this rule on API error
                }
            }

            if ( $still_active ) {
                $has_aide = true;
                $max_copies    = max( $max_copies, (int) $r->aide_perso_copies );
                $max_questions = max( $max_questions, (int) $r->aide_perso_questions );
            }
        }

        if ( $has_aide ) {
            update_user_meta( $user_id, 'jam_aide_perso_access', '1' );
            update_user_meta( $user_id, 'jam_aide_perso_copies_limit', $max_copies );
            update_user_meta( $user_id, 'jam_aide_perso_questions_limit', $max_questions );
        } else {
            delete_user_meta( $user_id, 'jam_aide_perso_access' );
            delete_user_meta( $user_id, 'jam_aide_perso_copies_limit' );
            delete_user_meta( $user_id, 'jam_aide_perso_questions_limit' );

            $user = get_user_by( 'id', $user_id );
            JAM_Access_Log::log( [
                'user_id'        => $user_id,
                'user_email'     => $user ? $user->user_email : '',
                'fcom_course_id' => 0,
                'action'         => 'aide_perso_revoked',
                'source'         => $source,
                'sc_purchase_id' => $sc_purchase_id ?: null,
                'details'        => wp_json_encode( [ 'reason' => 'no_active_aide_perso_rules' ] ),
            ] );
        }
    }
}
