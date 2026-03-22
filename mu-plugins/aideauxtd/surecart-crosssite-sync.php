<?php
/**
 * Plugin Name: SureCart Cross-Site User Sync
 * Description: Crée le compte sur ecole.aideauxtd.com quand un achat est fait sur aideauxtd.com
 * Version: 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'surecart/purchase_created', 'jsc_sync_user_to_ecole', 5, 2 );

function jsc_sync_user_to_ecole( $purchase, $webhook_data = null ) {
    $email       = '';
    $customer_id = '';
    $name        = '';

    if ( is_object( $purchase ) ) {
        $customer = $purchase->customer ?? null;
        if ( is_object( $customer ) ) {
            $email       = $customer->email ?? '';
            $customer_id = $customer->id ?? '';
            $name        = $customer->name ?? '';
        } elseif ( is_string( $customer ) ) {
            $customer_id = $customer;
        }
    }

    if ( empty( $email ) && ! empty( $customer_id ) && class_exists( '\SureCart\Models\Customer' ) ) {
        try {
            $sc_customer = \SureCart\Models\Customer::find( $customer_id );
            if ( $sc_customer ) {
                $email = $sc_customer->email ?? '';
                $name  = $sc_customer->name ?? '';
            }
        } catch ( \Exception $e ) {}
    }

    if ( empty( $email ) ) return;

    $ecole_db = new wpdb( 'aideauxtd_wp336', '37(4mpe.S9', 'aideauxtd_wp336', 'localhost' );
    $ecole_db->suppress_errors( true );
    $pfx = 'wpi3_';

    // Format sérialisé PHP (pas JSON) — c'est ce que SureCart attend
    $sc_ids_serialized = serialize( [ 'live' => $customer_id ] );

    $existing_id = $ecole_db->get_var( $ecole_db->prepare(
        "SELECT ID FROM {$pfx}users WHERE user_email = %s", $email
    ) );

    if ( $existing_id ) {
        if ( ! empty( $customer_id ) ) {
            $meta_id = $ecole_db->get_var( $ecole_db->prepare(
                "SELECT umeta_id FROM {$pfx}usermeta WHERE user_id = %d AND meta_key = 'sc_customer_ids'",
                $existing_id
            ) );
            if ( $meta_id ) {
                $ecole_db->update( "{$pfx}usermeta",
                    [ 'meta_value' => $sc_ids_serialized ],
                    [ 'umeta_id' => $meta_id ]
                );
            } else {
                $ecole_db->insert( "{$pfx}usermeta", [
                    'user_id'    => $existing_id,
                    'meta_key'   => 'sc_customer_ids',
                    'meta_value' => $sc_ids_serialized,
                ] );
            }
        }
        return;
    }

    // Créer le user
    $username = sanitize_user( strtolower( explode( '@', $email )[0] ), true );
    if ( empty( $username ) ) $username = 'user';
    $base = $username;
    $i = 1;
    while ( $ecole_db->get_var( $ecole_db->prepare( "SELECT ID FROM {$pfx}users WHERE user_login = %s", $username ) ) ) {
        $username = $base . $i++;
    }

    $ecole_db->insert( "{$pfx}users", [
        'user_login'      => $username,
        'user_pass'       => wp_hash_password( wp_generate_password( 24 ) ),
        'user_nicename'   => sanitize_title( $name ?: $username ),
        'user_email'      => $email,
        'user_registered' => current_time( 'mysql' ),
        'display_name'    => $name ?: $username,
        'user_status'     => 0,
    ] );

    $new_id = $ecole_db->insert_id;
    if ( ! $new_id ) return;

    $parts = explode( ' ', $name, 2 );
    $metas = [
        "{$pfx}capabilities"  => serialize( [ 'subscriber' => true ] ),
        "{$pfx}user_level"    => '0',
        'nickname'            => $name ?: $username,
        'first_name'          => $parts[0] ?? '',
        'last_name'           => $parts[1] ?? '',
        'sc_customer_ids'     => $sc_ids_serialized,
        'default_password_nag' => '1',
    ];

    foreach ( $metas as $key => $value ) {
        $ecole_db->insert( "{$pfx}usermeta", [
            'user_id'    => $new_id,
            'meta_key'   => $key,
            'meta_value' => $value,
        ] );
    }
}
