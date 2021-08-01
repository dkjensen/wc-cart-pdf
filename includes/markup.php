<?php
/**
 * Markup functions
 *
 * @package dkjensen/wc-cart-pdf
 */

/**
 * Renders the download cart as PDF button in cart
 *
 * @return void
 */
function wc_cart_pdf_button() {
	if ( ( ! is_cart() && ! is_checkout() ) || WC()->cart->is_empty() ) {
		return;
	}

	?>

	<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'cart-pdf' => '1' ), wc_get_cart_url() ), 'cart-pdf' ) ); ?>" class="cart-pdf-button button" target="_blank">
		<?php echo esc_html( get_option( 'wc_cart_pdf_button_label', __( 'Download Cart as PDF', 'wc-cart-pdf' ) ) ); ?>
	</a>

	<?php
}
add_action( 'woocommerce_proceed_to_checkout', 'wc_cart_pdf_button', 21 );

/**
 * Renders the download cart as PDF button in checkout
 *
 * @since 2.1.4
 * @return void
 */
function wc_cart_pdf_show_checkout() {
	if ( get_option( 'wc_cart_pdf_show_checkout', false ) ) {
		print '<p class="cart-pdf-button-container">';
		wc_cart_pdf_button();
		print '</p>';
	}
}
add_action( 'woocommerce_review_order_before_payment', 'wc_cart_pdf_show_checkout' );

/**
 * Expand {site_title} placeholder variable
 *
 * @since 1.0.3
 * @param string $string Default footer text.
 * @return string
 */
function wc_cart_pdf_footer_text( $string ) {
	return str_replace( '{site_title}', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $string );
}
add_filter( 'woocommerce_email_footer_text', 'wc_cart_pdf_footer_text' );
