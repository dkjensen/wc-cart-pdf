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
	$customer = new \WC_Customer();

	if ( is_user_logged_in() ) {
		$user     = wp_get_current_user();
		$customer = new \WC_Customer( get_current_user_id(), true );

		if ( empty( $customer->get_billing_first_name() ) ) {
			$customer->set_billing_first_name( $user->first_name );
		}

		if ( empty( $customer->get_billing_last_name() ) ) {
			$customer->set_billing_last_name( $user->last_name );
		}

		if ( empty( $customer->get_billing_email() ) ) {
			$customer->set_billing_email( $user->user_email );
		}
	}

	$cookie_data           = isset( $_COOKIE['wc-cart-pdf-customer'] ) ? wp_unslash( $_COOKIE['wc-cart-pdf-customer'] ) : '{}'; // phpcs:ignore
	$customer_session_data = json_decode( $cookie_data, true );

	if ( ! empty( $customer_session_data ) ) {
		$customer->set_props( $customer_session_data );
	}

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
		$customer->set_object_read( false );
		// $customer->set_props( $_POST );
	}

	return $customer;
}
