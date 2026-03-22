<?php
/**
 * Plugin Name: Jurible Subscribe API
 * Description: Public REST endpoint for email subscription (FluentCRM)
 * Version: 1.1.0
 */

if ( ! defined( "ABSPATH" ) ) exit;

add_action( "rest_api_init", function() {
    register_rest_route( "jurible/v1", "/subscribe", [
        "methods"  => "POST",
        "callback" => "jurible_subscribe_handler",
        "permission_callback" => "__return_true",
        "args" => [
            "email" => [
                "required" => true,
                "sanitize_callback" => "sanitize_email",
                "validate_callback" => function( $v ) { return is_email( $v ); },
            ],
            "first_name" => [
                "required" => false,
                "sanitize_callback" => "sanitize_text_field",
            ],
        ],
    ]);
});

function jurible_subscribe_handler( WP_REST_Request $request ) {
    $email      = $request->get_param( "email" );
    $first_name = $request->get_param( "first_name" );

    if ( ! function_exists( "FluentCrmApi" ) ) {
        return new WP_REST_Response( [ "success" => false, "message" => "FluentCRM not available" ], 500 );
    }

    $data = [
        "email"  => $email,
        "status" => "subscribed",
    ];
    if ( $first_name ) {
        $data["first_name"] = $first_name;
    }

    $contact = FluentCrmApi( "contacts" )->createOrUpdate( $data );

    if ( ! $contact || is_wp_error( $contact ) ) {
        return new WP_REST_Response( [ "success" => false, "message" => "Could not create contact" ], 500 );
    }

    $contact->attachTags( [ 9 ] );
    $contact->attachLists( [ 13 ] );

    // Force FluentCRM to process email queue immediately
    if ( function_exists( 'as_enqueue_async_action' ) ) {
        as_enqueue_async_action( 'fluentcrm_scheduled_every_minute_tasks', [], 'fluent-crm' );
    } else {
        do_action( 'fluentcrm_scheduled_every_minute_tasks' );
    }

    return new WP_REST_Response( [ "success" => true, "message" => "Inscription réussie" ], 200 );
}

// CORS for aideauxtd.com
add_action( "rest_api_init", function() {
    remove_filter( "rest_pre_serve_request", "rest_send_cors_headers" );
    add_filter( "rest_pre_serve_request", function( $value ) {
        $origin = get_http_origin();
        if ( $origin && ( strpos( $origin, "aideauxtd.com" ) !== false ) ) {
            header( "Access-Control-Allow-Origin: " . esc_url_raw( $origin ) );
            header( "Access-Control-Allow-Methods: POST, OPTIONS" );
            header( "Access-Control-Allow-Headers: Content-Type" );
        }
        return $value;
    });
}, 15 );
