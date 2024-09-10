<?php
/**
 * PHP-Scoper configuration file.
 */

declare(strict_types = 1);

use Isolated\Symfony\Component\Finder\Finder;

$wp_classes   = json_decode( file_get_contents( 'vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-classes.json' ), true );
$wp_functions = json_decode( file_get_contents( 'vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-functions.json' ), true );
$wp_constants = json_decode( file_get_contents( 'vendor/sniccowp/php-scoper-wordpress-excludes/generated/exclude-wordpress-constants.json' ), true );

return array(
	'prefix'            => 'WCCartPDF',
	'finders'           => array(
		Finder::create()
			->files()
			->ignoreVCS( true )
			->ignoreDotFiles( true )
			->in(
				array(
					'vendor/mpdf/mpdf',
					'vendor/mpdf/psr-http-message-shim',
					'vendor/mpdf/psr-log-aware-trait',
				)
			)
			->append( array( 'vendor/mpdf/mpdf/composer.json', 'vendor/mpdf/psr-http-message-shim/composer.json', 'vendor/mpdf/psr-log-aware-trait/composer.json' ) ),

		Finder::create()
			->files()
			->ignoreVCS( true )
			->ignoreDotFiles( true )
			->in(
				array(
					'vendor/myclabs/deep-copy',
				)
			)
			->append( array( 'vendor/myclabs/deep-copy/composer.json' ) ),

		Finder::create()
			->files()
			->ignoreVCS( true )
			->ignoreDotFiles( true )
			->in(
				array(
					'vendor/paragonie/random_compat',
				)
			)
			->append( array( 'vendor/paragonie/random_compat/composer.json' ) ),

		Finder::create()
			->files()
			->ignoreVCS( true )
			->ignoreDotFiles( true )
			->in(
				array(
					'vendor/setasign/fpdi',
				)
			)
			->append( array( 'vendor/setasign/fpdi/composer.json' ) ),

		Finder::create()
			->files()
			->ignoreVCS( true )
			->ignoreDotFiles( true )
			->in(
				array(
					'vendor/psr/http-message',
					'vendor/psr/log',
				)
			)
			->append( array( 'vendor/psr/http-message/composer.json', 'vendor/psr/log/composer.json' ) ),

		// Main composer.json file so that we can build a classmap.
		Finder::create()
			->append( array( 'composer.json' ) ),
	),
	'patchers'          => array(
		// Patch to fix the namespace of the Tag classes.
		function ( $filePath, $prefix, $content ) {
			if ( strpos( $filePath, 'mpdf/mpdf/src/Tag.php' ) !== false ) {
				return str_replace( 'Mpdf\\\\Tag\\\\', $prefix . '\\\\Mpdf\\\\Tag\\\\', $content );
			}
			return $content;
		},

		// Patch to prevent scoping of specific constants and functions in TTFontFile.php.
		function ( $filePath, $prefix, $content ) {
			if ( strpos( $filePath, 'mpdf/mpdf/src/TTFontFile.php' ) !== false ) {
				$content = str_replace(
					array( 'WCCartPDF\\\\_OTL_OLD_SPEC_COMPAT_2', 'WCCartPDF\\\\_TTF_MAC_HEADER', 'WCCartPDF\\\\_RECALC_PROFILE' ),
					array( '_OTL_OLD_SPEC_COMPAT_2', '_TTF_MAC_HEADER', '_RECALC_PROFILE' ),
					$content
				);

				$content = str_replace( '= \\unicode_hex', '= unicode_hex', $content );
				$content = str_replace( '\\\\Mpdf\\\\unicode_hex', '\\WCCartPDF\\Mpdf\\unicode_hex', $content );
			}
			return $content;
		},

		// Patch to handle preg_replace issue.
		function ( $filePath, $prefix, $content ) {
			if ( strpos( $filePath, 'mpdf/mpdf/src/Mpdf.php' ) !== false ) {
				$content = str_replace( '\'WCCartPDF\\\\', '\'', $content );
			}
			return $content;
		},

		function ( $filePath, $prefix, $content ) {
			if ( 1 == 1 ) {
				// Functions to patch
				$functions = array( 'str_replace', 'preg_replace', 'preg_match', 'preg_match_all', 'preg_split' );
				$variables = ['$regexp', '$regexpem'];

				foreach ($functions as $function) {
					// Regular expression to match function calls and look for double slashes in the first argument
					$content = preg_replace_callback(
						'/(' . preg_quote($function) . '\s*\(\s*[\'\"])(.*?)([\'\"][\s]*,)/is',
						function ($matches) {
							if ( '\\\\' == $matches[2] ) {
								return $matches[0];
							}
							// Revert any double backslashes to single backslashes in the first parameter
							$firstParam = str_replace('\\\\', '\\', $matches[2]);
							return $matches[1] . $firstParam . $matches[3];
						},
						$content
					);
				}

				// Handle variables like $regexp and $regexpem
				foreach ($variables as $variable) {
					$content = preg_replace_callback(
						'/(' . preg_quote($variable) . '\s*=\s*[\'\"])(.*?)([\'\"][;])/is',
						function ($matches) {
							if ( '\\\\' == $matches[2] ) {
								return $matches[0];
							}

							error_log( print_r( $matches, true ) );

							$firstParam = $matches[2];

							$firstParam = str_replace('\\\\', '\\', $firstParam);
	
							return $matches[1] . $firstParam . $matches[3];
						},
						$content
					);
				}
			}

			return $content;
		},
	),
	'whitelist'         => array(),
	'exclude-classes'   => $wp_classes,
	'exclude-functions' => array(
		'str_replace',
		'preg_replace',
		// You can add more functions to exclude here if needed
	),
	'exclude-constants' => $wp_constants,
);
