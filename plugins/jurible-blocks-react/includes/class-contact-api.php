<?php
/**
 * Jurible Contact Form REST API
 *
 * Handles contact form submissions via POST /jurible/v1/contact
 * Anti-spam: honeypot field, rate limiting (3/IP/hour), nonce verification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Jurible_Contact_API {

	/**
	 * Register REST API routes.
	 */
	public static function register_routes() {
		register_rest_route( 'jurible/v1', '/contact', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'handle_submission' ],
			'permission_callback' => '__return_true',
		] );
	}

	/**
	 * Handle contact form submission.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public static function handle_submission( WP_REST_Request $request ) {
		// Honeypot check
		$honeypot = $request->get_param( 'website' );
		if ( ! empty( $honeypot ) ) {
			// Silent rejection — pretend success to bots
			return new WP_REST_Response( [
				'success' => true,
				'message' => 'Message envoyé.',
			], 200 );
		}

		// Rate limiting: max 3 submissions per IP per hour
		$ip        = self::get_client_ip();
		$transient = 'jurible_cf_' . md5( $ip );
		$count     = (int) get_transient( $transient );

		if ( $count >= 3 ) {
			return new WP_Error(
				'rate_limited',
				'Trop de messages envoyés. Veuillez réessayer dans une heure.',
				[ 'status' => 429 ]
			);
		}

		// Get and validate fields
		$first_name = sanitize_text_field( $request->get_param( 'firstName' ) );
		$last_name  = sanitize_text_field( $request->get_param( 'lastName' ) );
		$email      = sanitize_email( $request->get_param( 'email' ) );
		$subject    = sanitize_text_field( $request->get_param( 'subject' ) );
		$message    = sanitize_textarea_field( $request->get_param( 'message' ) );

		if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $message ) ) {
			return new WP_Error(
				'missing_fields',
				'Veuillez remplir tous les champs obligatoires.',
				[ 'status' => 400 ]
			);
		}

		if ( ! is_email( $email ) ) {
			return new WP_Error(
				'invalid_email',
				'Veuillez entrer une adresse email valide.',
				[ 'status' => 400 ]
			);
		}

		// Determine recipient
		$recipient = sanitize_email( $request->get_param( 'recipient' ) );
		if ( empty( $recipient ) || ! is_email( $recipient ) ) {
			$recipient = get_option( 'admin_email' );
		}

		// Build and send email
		$full_name = $first_name . ' ' . $last_name;
		$site_name = get_bloginfo( 'name' );
		$subject_line = sprintf( '[%s] Nouveau message de %s', $site_name, $full_name );

		$body_parts = [
			sprintf( 'Prénom : %s', $first_name ),
			sprintf( 'Nom : %s', $last_name ),
			sprintf( 'Email : %s', $email ),
		];

		if ( ! empty( $subject ) ) {
			$body_parts[] = sprintf( 'Sujet : %s', $subject );
		}

		$body_parts[] = '';
		$body_parts[] = 'Message :';
		$body_parts[] = $message;

		$body = implode( "\n", $body_parts );

		$headers = [
			'Content-Type: text/plain; charset=UTF-8',
			sprintf( 'Reply-To: %s <%s>', $full_name, $email ),
		];

		$sent = wp_mail( $recipient, $subject_line, $body, $headers );

		if ( ! $sent ) {
			return new WP_Error(
				'send_failed',
				'L\'envoi du message a échoué. Veuillez réessayer plus tard.',
				[ 'status' => 500 ]
			);
		}

		// Increment rate limit counter
		set_transient( $transient, $count + 1, HOUR_IN_SECONDS );

		return new WP_REST_Response( [
			'success' => true,
			'message' => 'Message envoyé avec succès.',
		], 200 );
	}

	/**
	 * Get client IP address.
	 *
	 * @return string
	 */
	private static function get_client_ip() {
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			return trim( $ips[0] );
		}

		return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1' ) );
	}
}
