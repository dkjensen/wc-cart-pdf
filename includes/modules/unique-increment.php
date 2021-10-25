<?php
/**
 * Module to add generated PDF number
 *
 * @package dkjensen/wc-cart-pdf
 */

if ( ! function_exists( 'get_option' ) || ! get_option( 'wc_cart_pdf_unique_increment', false ) ) {
	return;
}

/**
 * Increment unique PDF generated number
 *
 * @return void
 */
function wc_cart_pdf_unique_incrementer() {
	$pdf_incrementer = absint( get_option( 'wc_cart_pdf_unique_increment_num', 0 ) );

	update_option( 'wc_cart_pdf_unique_increment_num', $pdf_incrementer + 1 );
}
add_action( 'wc_cart_pdf_before_process', 'wc_cart_pdf_unique_incrementer' );
