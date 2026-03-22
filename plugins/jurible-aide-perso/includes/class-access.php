<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Jaide_Access {

    /**
     * Vérifie si l'utilisateur a accès à l'aide personnalisée.
     * L'accès est géré par Access Manager via user meta.
     */
    public static function user_has_access( $user_id = null ) {
        $user_id = $user_id ?: get_current_user_id();
        if ( ! $user_id ) {
            return false;
        }

        return get_user_meta( $user_id, 'jam_aide_perso_access', true ) == '1';
    }

    /**
     * Retourne la limite de copies pour un user.
     * Priorité : override individuel (jaide_copies_limit) > Access Manager meta > settings globaux.
     */
    public static function get_copies_limit( $user_id = null ) {
        $user_id  = $user_id ?: get_current_user_id();

        // 1. Override individuel (page admin Crédits)
        $override = get_user_meta( $user_id, 'jaide_copies_limit', true );
        if ( $override !== '' && $override !== false ) {
            return (int) $override;
        }

        // 2. Limite Access Manager
        $jam_limit = get_user_meta( $user_id, 'jam_aide_perso_copies_limit', true );
        if ( $jam_limit !== '' && $jam_limit !== false ) {
            return (int) $jam_limit;
        }

        // 3. Fallback settings globaux
        $options = get_option( 'jaide_options', [] );
        return (int) ( $options['copies_limit'] ?? 1 );
    }

    /**
     * Retourne la limite de questions pour un user.
     * Priorité : override individuel > Access Manager meta > settings globaux.
     */
    public static function get_questions_limit( $user_id = null ) {
        $user_id  = $user_id ?: get_current_user_id();

        // 1. Override individuel (page admin Crédits)
        $override = get_user_meta( $user_id, 'jaide_questions_limit', true );
        if ( $override !== '' && $override !== false ) {
            return (int) $override;
        }

        // 2. Limite Access Manager
        $jam_limit = get_user_meta( $user_id, 'jam_aide_perso_questions_limit', true );
        if ( $jam_limit !== '' && $jam_limit !== false ) {
            return (int) $jam_limit;
        }

        // 3. Fallback settings globaux
        $options = get_option( 'jaide_options', [] );
        return (int) ( $options['questions_limit'] ?? 0 );
    }

    /**
     * Nombre de copies restantes.
     */
    public static function copies_remaining( $user_id = null ) {
        global $wpdb;

        $user_id     = $user_id ?: get_current_user_id();
        $limit       = self::get_copies_limit( $user_id );

        $table       = $wpdb->prefix . 'jurible_aide_requests';
        $count       = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND type = 'copie'",
            $user_id
        ) );
        $manual_used = max( 0, (int) get_user_meta( $user_id, 'jaide_copies_manual_used', true ) );

        return max( 0, $limit - $count - $manual_used );
    }

    /**
     * Nombre de questions restantes.
     */
    public static function questions_remaining( $user_id = null ) {
        global $wpdb;

        $user_id     = $user_id ?: get_current_user_id();
        $limit       = self::get_questions_limit( $user_id );

        $table       = $wpdb->prefix . 'jurible_aide_requests';
        $count       = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND type = 'question'",
            $user_id
        ) );
        $manual_used = max( 0, (int) get_user_meta( $user_id, 'jaide_questions_manual_used', true ) );

        return max( 0, $limit - $count - $manual_used );
    }

    /**
     * Infos crédits détaillées pour un user (page admin Crédits).
     */
    public static function get_credits_info( $user_id ) {
        global $wpdb;

        $table          = $wpdb->prefix . 'jurible_aide_requests';
        $copies_limit   = self::get_copies_limit( $user_id );
        $questions_limit = self::get_questions_limit( $user_id );

        $copies_db_used = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND type = 'copie'", $user_id
        ) );
        $questions_db_used = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND type = 'question'", $user_id
        ) );

        $copies_manual    = max( 0, (int) get_user_meta( $user_id, 'jaide_copies_manual_used', true ) );
        $questions_manual = max( 0, (int) get_user_meta( $user_id, 'jaide_questions_manual_used', true ) );

        $copies_used    = $copies_db_used + $copies_manual;
        $questions_used = $questions_db_used + $questions_manual;

        $options = get_option( 'jaide_options', [] );

        return [
            'copies_limit'       => $copies_limit,
            'copies_limit_global'=> (int) ( $options['copies_limit'] ?? 1 ),
            'copies_has_override'=> get_user_meta( $user_id, 'jaide_copies_limit', true ) !== '',
            'copies_used'        => $copies_used,
            'copies_remaining'   => max( 0, $copies_limit - $copies_used ),

            'questions_limit'       => $questions_limit,
            'questions_limit_global'=> (int) ( $options['questions_limit'] ?? 0 ),
            'questions_has_override'=> get_user_meta( $user_id, 'jaide_questions_limit', true ) !== '',
            'questions_used'        => $questions_used,
            'questions_remaining'   => max( 0, $questions_limit - $questions_used ),
        ];
    }
}
