<?php
/**
 * Plugin and language compatibility
 *
 * @package dkjensen/wc-cart-pdf
 */

/**
 * TM Extra Product Options
 *
 * @see https://codecanyon.net/item/woocommerce-extra-product-options/7908619
 * @return void
 */
function wc_cart_pdf_compatibility_tm_extra_product_options() {
	add_filter( 'wc_epo_no_edit_options', '__return_true' ); // Hide "Edit options" link on product title.
}
add_action( 'wc_cart_pdf_before_process', 'wc_cart_pdf_compatibility_tm_extra_product_options' );

/**
 * Gravity PDF
 *
 * @return void
 */
function wc_cart_pdf_compatibility_gravity_pdf() {
	// phpcs:ignore
	if ( class_exists( 'GFPDF_Major_Compatibility_Checks' ) && isset( $GLOBALS['gravitypdf'] ) && isset( $_GET['cart-pdf'] ) ) {
		remove_action( 'plugins_loaded', array( $GLOBALS['gravitypdf'], 'plugins_loaded' ) );
	}
}
add_action( 'plugins_loaded', 'wc_cart_pdf_compatibility_gravity_pdf', 0 );

/**
 * Visual Products Configurator
 *
 * @return void
 */
function wc_cart_pdf_compatibility_visual_products_configurator() {
	add_filter(
		'vpc_get_config_data',
		function( $thumbnail_code ) {
			$edit_i18n = __( 'Edit', 'wc-cart-pdf' );

			$thumbnail_code = preg_replace( '/<\s*a[^>]*>' . $edit_i18n . '<\s*\/\s*a>/', '', $thumbnail_code ); // Hide the "Edit" link.

			return $thumbnail_code;
		}
	);
}
add_action( 'wc_cart_pdf_before_process', 'wc_cart_pdf_compatibility_visual_products_configurator' );

/**
 * Try removing product thumbnails filters if not rendering properly
 *
 * @return void
 */
function child_wc_cart_pdf_remove_thumbnail_filters() {
	if ( defined( 'WC_CART_PDF_THUMBNAIL_COMPATIBILITY' ) && constant( 'WC_CART_PDF_THUMBNAIL_COMPATIBILITY' ) ) {
		remove_all_filters( 'wp_get_attachment_image_src' );
		remove_all_filters( 'wp_get_attachment_image' );
		remove_all_filters( 'woocommerce_cart_item_thumbnail' );
		remove_all_filters( 'woocommerce_product_get_image' );
	}
}
add_action( 'wc_cart_pdf_before_process', 'child_wc_cart_pdf_remove_thumbnail_filters' );

/**
 * Multilingual support
 *
 * @param array $args MPDF args.
 * @return array
 */
function wc_cart_pdf_compatibility_language( $args ) {
	$site_lang = get_locale();

	switch ( $site_lang ) {
		case 'zh_CN': // Chinese (simplified).
		case 'zh_TW': // Chinese (traditional).
			$defaultConfig = ( new Mpdf\Config\ConfigVariables() )->getDefaults();
			$fontDirs      = $defaultConfig['fontDir'];

			$defaultFontConfig = ( new Mpdf\Config\FontVariables() )->getDefaults();
			$fontData          = $defaultFontConfig['fontdata'];

			$args['fontDir'] = array_merge(
				$fontDirs,
				array(
					WC_CART_PDF_PATH . 'resources/fonts',
				)
			);

			$args['fontdata'] = $fontData + array(
				'yahei' => array(
					'R'  => 'yahei.ttf',
				),
			);

			$args['default_font'] = 'yahei';
			$args['mode']         = '+aCJK';
			break;

		case 'ja': // Japanese.
		case 'ur': // Urdu.
		case 'am': // Amharic.
		case 'gu': // Gujarati.
		case 'hi_IN': // Hindi.
		case 'kn': // Kannada.
		case 'km': // Khmer.
		case 'ko_KR': // Korean.
		case 'ml_IN': // Malayalam.
		case 'mr': // Marathi.
		case 'my_MM': // Myanmar (burmese).
		case 'ne_NP': // Nepali.
		case 'pa_IN': // Punjabi.
		case 'si_LK': // Sinhala.
		case 'ta_IN': // Tamil.
		case 'te': // Telugu.
		case 'th': // Thai.
			add_filter(
				'wc_cart_pdf_mpdf',
				function( $mpdf ) {
					$mpdf->autoScriptToLang = true;

					return $mpdf;
				}
			);

			$args['mode'] = '+aCJK';

			break;
	}

	return $args;
}
add_filter( 'wc_cart_pdf_mpdf_args', 'wc_cart_pdf_compatibility_language' );
