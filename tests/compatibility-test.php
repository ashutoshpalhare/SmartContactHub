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
