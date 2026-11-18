<?php
/**
 * Dependency-free regression tests for Smart Contact Hub compatibility helpers.
 *
 * Run with: php -n tests/compatibility-test.php
 *
 * @package SmartContactHub
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );
require_once dirname( __DIR__ ) . '/includes/class-sch-compat.php';
/**
 * Minimal WordPress API stubs for isolated validation tests.
 */
class WP_Error {
	private $code;
	private $message;

	public function __construct( $code, $message ) {
		$this->code    = $code;
		$this->message = $message;
	}

	public function get_error_code() {
		return $this->code;
	}

	public function get_error_message() {
		return $this->message;
	}
}

function __( $message, $domain = null ) {
	return $message;
}

function sanitize_text_field( $value ) {
	$value = strip_tags( $value );
	return trim( preg_replace( '/[\r\n\\t ]+/', ' ', $value ) );
}

function sanitize_textarea_field( $value ) {
	return trim( strip_tags( $value ) );
}

function sanitize_email( $value ) {
	return filter_var( $value, FILTER_SANITIZE_EMAIL );
}

function is_email( $value ) {
	return false !== filter_var( $value, FILTER_VALIDATE_EMAIL );
}

$GLOBALS['sch_test_mail'] = null;

function get_bloginfo( $show ) {
	return 'Smart Contact Hub';
}

function wp_specialchars_decode( $value, $quote_style = ENT_NOQUOTES ) {
	return html_entity_decode( $value, $quote_style );
}

function current_time( $type ) {
	return '2026-11-18 12:00:00';
}

function wp_mail( $to, $subject, $message, $headers = array() ) {
	$GLOBALS['sch_test_mail'] = array(
		'to'      => $to,
		'subject' => $subject,
		'message' => $message,
		'headers' => $headers,
	);
	return true;
}

require_once dirname( __DIR__ ) . '/includes/class-sch-validator.php';
require_once dirname( __DIR__ ) . '/includes/class-sch-request-guard.php';
require_once dirname( __DIR__ ) . '/includes/class-sch-email.php';


/**
 * Assert that two values are strictly identical.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Failure description.
 */
function sch_test_assert_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException(
			sprintf(
				'%s Expected %s, received %s.',
				$message,
				var_export( $expected, true ),
				var_export( $actual, true )
			)
		);
	}
}

$tests = array(
	'counts ASCII characters' => function () {
		sch_test_assert_same( 5, SCH_Compat::text_length( 'hello' ), 'ASCII length.' );
	},
	'counts multibyte Unicode characters' => function () {
		sch_test_assert_same( 3, SCH_Compat::text_length( 'A😀B' ), 'Unicode length.' );
	},
	'returns a Unicode substring by character offset' => function () {
		sch_test_assert_same( '😀', SCH_Compat::text_substr( 'A😀B', 1, 1 ), 'Unicode substring.' );
	},
	'supports negative substring offsets' => function () {
		sch_test_assert_same( '😀', SCH_Compat::text_substr( 'A😀B', -2, 1 ), 'Negative substring offset.' );
	},
	'supports omitted substring length' => function () {
		sch_test_assert_same( '😀B', SCH_Compat::text_substr( 'A😀B', 1 ), 'Substring without length.' );
	},
	'returns an empty string for zero-length slices' => function () {
		sch_test_assert_same( '', SCH_Compat::text_substr( 'hello', 1, 0 ), 'Zero-length substring.' );
	},
	'handles empty strings' => function () {
		sch_test_assert_same( 0, SCH_Compat::text_length( '' ), 'Empty string length.' );
		sch_test_assert_same( '', SCH_Compat::text_substr( '', 0 ), 'Empty string substring.' );
	},
	'falls back safely for malformed UTF-8 bytes' => function () {
		$invalid = "\xFF";
		sch_test_assert_same( 1, SCH_Compat::text_length( $invalid ), 'Malformed UTF-8 length fallback.' );
		sch_test_assert_same( $invalid, SCH_Compat::text_substr( $invalid, 0, 1 ), 'Malformed UTF-8 substring fallback.' );
	},
	'accepts a valid submission and normalizes its fields' => function () {
		$result = SCH_Submission_Validator::validate(
			array(
				'name'    => '  <b>Ashutosh</b>  ',
				'email'   => 'ashutosh@example.com',
				'phone'   => '+91 98765 43210',
				'subject' => '  Project enquiry  ',
				'message' => "Hello\nWorld",
			)
		);

		sch_test_assert_same( array(
			'name'    => 'Ashutosh',
			'email'   => 'ashutosh@example.com',
			'phone'   => '+91 98765 43210',
			'subject' => 'Project enquiry',
			'message' => "Hello\nWorld",
		), $result, 'Valid submission normalization.' );
	},
	'rejects missing required fields' => function () {
		$result = SCH_Submission_Validator::validate( array( 'name' => 'Ashutosh' ) );
		sch_test_assert_same( 'sch_required_fields', $result->get_error_code(), 'Required field validation.' );
	},
	'rejects malformed email addresses' => function () {
		$result = SCH_Submission_Validator::validate(
			array(
				'name' => 'Ashutosh',
				'email' => 'not-an-email',
				'subject' => 'Hello',
				'message' => 'Message',
			)
		);
		sch_test_assert_same( 'sch_invalid_email', $result->get_error_code(), 'Email validation.' );
	},
	'rejects values beyond the configured Unicode length limits' => function () {
		$result = SCH_Submission_Validator::validate(
			array(
				'name' => str_repeat( '😀', 121 ),
				'email' => 'ashutosh@example.com',
				'subject' => 'Hello',
				'message' => 'Message',
			)
		);
		sch_test_assert_same( 'sch_field_too_long', $result->get_error_code(), 'Unicode field length validation.' );
	},
	'does not pass array values into sanitizers' => function () {
		$result = SCH_Submission_Validator::validate(
			array(
				'name' => array( 'unexpected' ),
				'email' => 'ashutosh@example.com',
				'subject' => 'Hello',
				'message' => 'Message',
			)
		);
		sch_test_assert_same( 'sch_required_fields', $result->get_error_code(), 'Array input handling.' );
	},
	'accepts an empty optional phone number' => function () {
		$result = SCH_Submission_Validator::validate(
			array(
				'name' => 'Ashutosh',
				'email' => 'ashutosh@example.com',
				'phone' => '',
				'subject' => 'Hello',
				'message' => 'Message',
			)
		);
		sch_test_assert_same( true, is_array( $result ), 'Empty optional phone.' );
	},
	'accepts an international phone number with common formatting' => function () {
		$result = SCH_Submission_Validator::validate(
			array(
				'name' => 'Ashutosh',
				'email' => 'ashutosh@example.com',
				'phone' => '+91 (98765) 43210',
				'subject' => 'Hello',
				'message' => 'Message',
			)
		);
		sch_test_assert_same( '+91 (98765) 43210', $result['phone'], 'Formatted phone number.' );
	},
	'rejects phone numbers containing letters' => function () {
		$result = SCH_Submission_Validator::validate(
			array(
				'name' => 'Ashutosh',
				'email' => 'ashutosh@example.com',
				'phone' => '98765ABCDE',
				'subject' => 'Hello',
				'message' => 'Message',
			)
		);
		sch_test_assert_same( 'sch_invalid_phone', $result->get_error_code(), 'Phone alphabet rejection.' );
	},
	'rejects phone numbers with too few digits' => function () {
		$result = SCH_Submission_Validator::validate(
			array(
				'name' => 'Ashutosh',
				'email' => 'ashutosh@example.com',
				'phone' => '123-456',
				'subject' => 'Hello',
				'message' => 'Message',
			)
		);
		sch_test_assert_same( 'sch_invalid_phone', $result->get_error_code(), 'Phone minimum digit count.' );
	},
	'rejects phone numbers with more than twenty digits' => function () {
		$result = SCH_Submission_Validator::validate(
			array(
				'name' => 'Ashutosh',
				'email' => 'ashutosh@example.com',
				'phone' => '123456789012345678901',
				'subject' => 'Hello',
				'message' => 'Message',
			)
		);
		sch_test_assert_same( 'sch_invalid_phone', $result->get_error_code(), 'Phone maximum digit count.' );
	},
	'treats a non-empty honeypot as a bot submission' => function () {
		sch_test_assert_same( true, SCH_Request_Guard::is_honeypot_filled( 'https://spam.example' ), 'Filled honeypot.' );
	},
	'accepts an empty honeypot value' => function () {
		sch_test_assert_same( false, SCH_Request_Guard::is_honeypot_filled( '' ), 'Empty honeypot.' );
	},
	'classifies array and object honeypot values as traps without sanitizing them' => function () {
		sch_test_assert_same( true, SCH_Request_Guard::is_honeypot_filled( array( 'unexpected' ) ), 'Array honeypot.' );
		sch_test_assert_same( true, SCH_Request_Guard::is_honeypot_filled( (object) array( 'unexpected' ) ), 'Object honeypot.' );
	},
	'uses a safe fallback for malformed remote address values' => function () {
		sch_test_assert_same( 'unknown', SCH_Request_Guard::get_remote_address( array( 'REMOTE_ADDR' => array( 'unexpected' ) ) ), 'Array remote address.' );
		sch_test_assert_same( 'unknown', SCH_Request_Guard::get_remote_address( array() ), 'Missing remote address.' );
	},
	'rejects missing and non-scalar nonce request values' => function () {
		sch_test_assert_same( false, SCH_Request_Guard::has_scalar_value( array(), 'sch_nonce' ), 'Missing nonce.' );
		sch_test_assert_same( false, SCH_Request_Guard::has_scalar_value( array( 'sch_nonce' => array( 'bad' ) ), 'sch_nonce' ), 'Array nonce.' );
		sch_test_assert_same( false, SCH_Request_Guard::has_scalar_value( array( 'sch_nonce' => (object) array() ), 'sch_nonce' ), 'Object nonce.' );
	},
	'accepts scalar nonce request values' => function () {
		sch_test_assert_same( true, SCH_Request_Guard::has_scalar_value( array( 'sch_nonce' => 'nonce-token' ), 'sch_nonce' ), 'String nonce.' );
	},
	'sanitizes scalar remote addresses' => function () {
		sch_test_assert_same( '127.0.0.1', SCH_Request_Guard::get_remote_address( array( 'REMOTE_ADDR' => '127.0.0.1' ) ), 'Scalar remote address.' );
	},
	'rejects non-POST request methods' => function () {
		sch_test_assert_same( false, SCH_Request_Guard::is_post_request( array( 'REQUEST_METHOD' => 'GET' ) ), 'GET request method.' );
		sch_test_assert_same( false, SCH_Request_Guard::is_post_request( array( 'REQUEST_METHOD' => 'PATCH' ) ), 'PATCH request method.' );
		sch_test_assert_same( false, SCH_Request_Guard::is_post_request( array() ), 'Missing request method.' );
	},
	'accepts POST request methods case-insensitively' => function () {
		sch_test_assert_same( true, SCH_Request_Guard::is_post_request( array( 'REQUEST_METHOD' => 'POST' ) ), 'POST request method.' );
		sch_test_assert_same( true, SCH_Request_Guard::is_post_request( array( 'REQUEST_METHOD' => 'post' ) ), 'Lowercase POST request method.' );
	},
	'rejects malformed recipient settings before calling wp_mail' => function () {
		$GLOBALS['sch_test_mail'] = null;
		$result = SCH_Email::send_contact_email( array(), array( 'recipient_email' => array( 'bad@example.com' ) ) );
		sch_test_assert_same( false, $result, 'Array recipient setting.' );
		sch_test_assert_same( null, $GLOBALS['sch_test_mail'], 'Mail must not be attempted for malformed recipient settings.' );
	},
	'sends a plain-text email with a validated Reply-To header' => function () {
		$GLOBALS['sch_test_mail'] = null;
		$data = array(
			'name'    => "Ashutosh\r\nBcc: attacker@example.com",
			'email'   => 'visitor@example.com',
			'phone'   => '',
			'subject' => "Project\r\nBcc: attacker@example.com",
			'message' => "First line\nSecond line",
		);
		$result = SCH_Email::send_contact_email( $data, array( 'recipient_email' => 'owner@example.com' ) );
		sch_test_assert_same( true, $result, 'Email send result.' );
		sch_test_assert_same( 'owner@example.com', $GLOBALS['sch_test_mail']['to'], 'Recipient address.' );
		sch_test_assert_same( true, false !== strpos( $GLOBALS['sch_test_mail']['headers'][0], 'text/plain; charset=UTF-8' ), 'Plain-text content type.' );
		sch_test_assert_same( true, false !== strpos( $GLOBALS['sch_test_mail']['headers'][1], 'visitor@example.com' ), 'Reply-To email.' );
		sch_test_assert_same( false, false !== strpos( $GLOBALS['sch_test_mail']['headers'][1], "\r" ), 'Reply-To header must not contain carriage returns.' );
		sch_test_assert_same( false, false !== strpos( $GLOBALS['sch_test_mail']['headers'][1], "\n" ), 'Reply-To header must not contain line feeds.' );
		sch_test_assert_same( false, false !== strpos( $GLOBALS['sch_test_mail']['subject'], "\nBcc:" ), 'Subject must not contain injected header lines.' );
		sch_test_assert_same( true, false !== strpos( $GLOBALS['sch_test_mail']['message'], "First line\nSecond line" ), 'Message line breaks are preserved.' );
	},
	'runs with mbstring disabled' => function () {
		sch_test_assert_same( false, extension_loaded( 'mbstring' ), 'The test process must disable mbstring.' );
	},
);

$failures = 0;

foreach ( $tests as $name => $test ) {
	try {
		$test();
		echo "PASS: {$name}\n";
	} catch ( Throwable $error ) {
		++$failures;
		fwrite( STDERR, "FAIL: {$name} — {$error->getMessage()}\n" );
	}
}

printf( "\n%d tests, %d failures.\n", count( $tests ), $failures );

exit( 0 === $failures ? 0 : 1 );
