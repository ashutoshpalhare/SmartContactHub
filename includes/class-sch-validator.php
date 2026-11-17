<?php
/**
 * Normalize and validate contact form submissions.
 *
 * @package SmartContactHub
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SCH_Submission_Validator {

	/**
	 * Sanitize and validate the submitted contact fields.
	 *
	 * @param array $raw Raw, unslashed submission values.
	 * @return array|WP_Error
	 */
	public static function validate( $raw ) {
		$raw = is_array( $raw ) ? $raw : array();

		$data = array(
			'name'    => self::sanitize_text_value( $raw, 'name' ),
			'email'   => self::sanitize_email_value( $raw, 'email' ),
			'phone'   => self::sanitize_text_value( $raw, 'phone' ),
			'subject' => self::sanitize_text_value( $raw, 'subject' ),
			'message' => self::sanitize_message_value( $raw, 'message' ),
		);

		if ( '' === $data['name'] || '' === $data['email'] || '' === $data['subject'] || '' === $data['message'] ) {
			return new WP_Error( 'sch_required_fields', __( 'Please complete all required fields.', 'smart-contact-hub' ) );
		}

		if ( ! is_email( $data['email'] ) ) {
			return new WP_Error( 'sch_invalid_email', __( 'Enter a valid email address.', 'smart-contact-hub' ) );
		}

		$limits = array(
			'name'    => 120,
			'email'   => 190,
			'phone'   => 40,
			'subject' => 160,
			'message' => 5000,
		);

		foreach ( $limits as $field => $limit ) {
			if ( SCH_Compat::text_length( $data[ $field ] ) > $limit ) {
				return new WP_Error( 'sch_field_too_long', __( 'One or more fields exceed the allowed length.', 'smart-contact-hub' ) );
			}
		}

		return $data;
	}

	/**
	 * Sanitize a single-line field without passing arrays to WordPress APIs.
	 *
	 * @param array  $raw   Raw submitted values.
	 * @param string $field Field key.
	 * @return string
	 */
	private static function sanitize_text_value( $raw, $field ) {
		return isset( $raw[ $field ] ) && is_scalar( $raw[ $field ] )
			? sanitize_text_field( (string) $raw[ $field ] )
			: '';
	}

	/**
	 * Sanitize the email field.
	 *
	 * @param array $raw Raw submitted values.
	 * @param string $field Field key.
	 * @return string
	 */
	private static function sanitize_email_value( $raw, $field ) {
		return isset( $raw[ $field ] ) && is_scalar( $raw[ $field ] )
			? sanitize_email( (string) $raw[ $field ] )
			: '';
	}

	/**
	 * Sanitize the message field while preserving textarea line breaks.
	 *
	 * @param array  $raw   Raw submitted values.
	 * @param string $field Field key.
	 * @return string
	 */
	private static function sanitize_message_value( $raw, $field ) {
		return isset( $raw[ $field ] ) && is_scalar( $raw[ $field ] )
			? sanitize_textarea_field( (string) $raw[ $field ] )
			: '';
	}
}
