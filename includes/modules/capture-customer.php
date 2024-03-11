<?php
/**
 * Module to capture customer information on checkout
 *
 * @package dkjensen/wc-cart-pdf
 */

/**
 * Set default checkout field values to what is saved in the cookie
 *
 * @since 2.1.4
 * @param string $value Current value.
 * @param string $input Field name.
 * @return string
 */
function wc_cart_pdf_checkout_fields( $value, $input ) {
	$cookie_data           = isset( $_COOKIE['wc-cart-pdf-customer'] ) ? wp_unslash( $_COOKIE['wc-cart-pdf-customer'] ) : '{}'; // phpcs:ignore
	$customer_session_data = json_decode( $cookie_data, true );

	if ( isset( $customer_session_data[ $input ] ) ) {
		return $customer_session_data[ $input ];
	}

	return $value;
}
add_filter( 'woocommerce_checkout_get_value', 'wc_cart_pdf_checkout_fields', 10, 2 );
