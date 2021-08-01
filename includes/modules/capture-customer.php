<?php
/**
 * Module to capture customer information on checkout
 *
 * @package dkjensen/wc-cart-pdf
 */

if ( ! function_exists( 'get_option' ) || ! get_option( 'wc_cart_pdf_capture_customer', false ) ) {
	return;
}

/**
 * Enqueue script to save customer information in a cookie
 *
 * @since 2.1.4
 * @return void
 */
function wc_cart_pdf_scripts() {
	wp_enqueue_script( 'wc-cart-pdf', WC_CART_PDF_URL . 'assets/js/wc-cart-pdf.js', array( 'jquery' ), WC_CART_PDF_VER, true );
}
add_action( 'wp_enqueue_scripts', 'wc_cart_pdf_scripts' );

/**
 * Set default checkout field values to what is saved in the cookie
 *
 * @since 2.1.4
 * @param string $value Current value.
 * @param string $input Field name.
 * @return string
 */
function wc_cart_pdf_checkout_fields( $value, $input ) {
	$cookie_data           = isset( $_COOKIE['wc-cart-pdf-customer'] ) ? wp_unslash( $_COOKIE['wc-cart-pdf-customer'] ) : '{}';
	$customer_session_data = json_decode( $cookie_data, true );

	if ( isset( $customer_session_data[ $input ] ) ) {
		return $customer_session_data[ $input ];
	}

	return $value;
}
add_filter( 'woocommerce_checkout_get_value', 'wc_cart_pdf_checkout_fields', 10, 2 );
