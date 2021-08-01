<?php
/**
 * Helper functions
 *
 * @package dkjensen/wc-cart-pdf
 */

/**
 * Helper function to retrieve current customer information
 *
 * @since 2.1.4
 * @return \WC_Customer
 */
function wc_cart_pdf_get_customer() {
	$cookie_data           = isset( $_COOKIE['wc-cart-pdf-customer'] ) ? wp_unslash( $_COOKIE['wc-cart-pdf-customer'] ) : '{}';
	$customer_session_data = json_decode( $cookie_data, true );

	$customer = new \WC_Customer();
	$customer->set_object_read( false );
	$customer->set_props( $customer_session_data );

	return $customer;
}
