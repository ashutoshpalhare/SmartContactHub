<?php
/**
 * Guard request metadata before passing it to WordPress sanitizers.
 *
 * @package SmartContactHub
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCH_Request_Guard {

	/**
	 * Treat malformed honeypot values as bot-like input without invoking string sanitizers.
	 *
	 * @param mixed $value Raw, unslashed honeypot value.
	 * @return bool
	 */
	public static function is_honeypot_filled( $value ) {
		if ( ! is_scalar( $value ) ) {
			return true;
		}

		return '' !== sanitize_text_field( (string) $value );
	}

	/**
	 * Check that a request contains a scalar value for a required key.
	 *
	 * @param mixed  $input Request data.
	 * @param string $key   Expected key.
	 * @return bool
	 */
	public static function has_scalar_value( $input, $key ) {
		return is_array( $input ) && isset( $input[ $key ] ) && is_scalar( $input[ $key ] );
	}

	/**
	 * Check whether the incoming request uses the expected POST method.
	 *
	 * @param mixed $server Server variables.
	 * @return bool
	 */
	public static function is_post_request( $server ) {
		return is_array( $server ) && isset( $server['REQUEST_METHOD'] ) && is_string( $server['REQUEST_METHOD'] ) && 'POST' === strtoupper( $server['REQUEST_METHOD'] );
	}

	/**
	 * Return a sanitized scalar remote address or a stable fallback.
	 *
	 * @param array $server Server variables.
	 * @return string
	 */
	public static function get_remote_address( $server ) {
		if ( ! is_array( $server ) || ! isset( $server['REMOTE_ADDR'] ) || ! is_scalar( $server['REMOTE_ADDR'] ) ) {
			return 'unknown';
		}

		$address = sanitize_text_field( (string) $server['REMOTE_ADDR'] );

		return '' !== $address ? $address : 'unknown';
	}
}
