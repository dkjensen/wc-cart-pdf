<?php
/**
 * Settings.
 *
 * @package dkjensen/wc-cart-pdf
 */

/**
 * Register settings.
 *
 * @return void
 */
function wc_cart_pdf_settings_init() {
	if ( class_exists( 'WC_Integration' ) ) {
		require_once WC_CART_PDF_PATH . 'includes/class-wc-cart-pdf-settings.php';

		add_filter( 'woocommerce_integrations', 'wc_cart_pdf_settings_integration' );
	}
}
add_action( 'plugins_loaded', 'wc_cart_pdf_settings_init', 11 );

/**
 * Add the integration to WooCommerce.
 *
 * @param array $integrations WooCommerce integrations.
 * @return array
 */
function wc_cart_pdf_settings_integration( $integrations ) {
	$integrations[] = 'WC_Cart_PDF_Settings';

	return $integrations;
}
