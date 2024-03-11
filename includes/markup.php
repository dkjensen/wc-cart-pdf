<?php
/**
 * Markup functions
 *
 * @package dkjensen/wc-cart-pdf
 */

/**
 * Enqueue script to save customer information in a cookie
 *
 * @return void
 */
function wc_cart_pdf_scripts() {
	wp_enqueue_style( 'wc-cart-pdf', WC_CART_PDF_URL . 'assets/css/wc-cart-pdf.css', array(), WC_CART_PDF_VER );

	wp_register_script( 'wc-cart-pdf', WC_CART_PDF_URL . 'assets/js/wc-cart-pdf.js', array(), WC_CART_PDF_VER, true );

	wp_localize_script(
		'wc-cart-pdf',
		'cartpdf',
		array(
			'modules'        => array(
				'capture_customer' => (int) get_option( 'wc_cart_pdf_capture_customer', 0 ),
				'modal_capture'    => (int) get_option( 'wc_cart_pdf_modal_capture', 0 ),
			),
			'ajax_url'       => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'wc_cart_pdf_modal' ),
			'capture_fields' => apply_filters(
				'wc_cart_pdf_capture_customer_fields',
				array(
					'email',
					'first_name',
					'last_name',
					'display_name',
					'username',
					'billing_first_name',
					'billing_last_name',
					'billing_company',
					'billing_address_1',
					'billing_address_2',
					'billing_city',
					'billing_postcode',
					'billing_country',
					'billing_state',
					'billing_email',
					'billing_phone',
					'shipping_first_name',
					'shipping_last_name',
					'shipping_company',
					'shipping_address_1',
					'shipping_address_2',
					'shipping_city',
					'shipping_postcode',
					'shipping_country',
					'shipping_state',
				)
			),
		)
	);

	wp_enqueue_script( 'wc-cart-pdf' );
}
add_action( 'wp_enqueue_scripts', 'wc_cart_pdf_scripts' );

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
 * @param string $string Default footer text.
 * @return string
 */
function wc_cart_pdf_footer_text( $string ) {
	return str_replace( '{site_title}', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $string );
}
add_filter( 'woocommerce_email_footer_text', 'wc_cart_pdf_footer_text' );
