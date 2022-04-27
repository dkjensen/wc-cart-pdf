<?php
/**
 * Module to prompt user for contact information prior to downloading PDF
 *
 * @package dkjensen/wc-cart-pdf
 */

if ( ! function_exists( 'get_option' ) || ! get_option( 'wc_cart_pdf_info_modal', false ) ) {
	return;
}

/**
 * Enqueue script to save customer information in a cookie
 *
 * @since 2.1.4
 * @return void
 */
function wc_cart_pdf_info_modal_scripts() {
	wp_enqueue_style( 'wc-cart-pdf-info-modal', WC_CART_PDF_URL . 'assets/css/wc-cart-pdf-info-modal.css', array(), WC_CART_PDF_VER );
	wp_register_script( 'wc-cart-pdf-info-modal', WC_CART_PDF_URL . 'assets/js/wc-cart-pdf-info-modal.js', array( 'jquery' ), WC_CART_PDF_VER, true );

	wp_localize_script(
		'wc-cart-pdf-info-modal',
		'cartpdfInfoModal',
		array()
	);

	wp_enqueue_script( 'wc-cart-pdf-info-modal' );
}
add_action( 'wp_enqueue_scripts', 'wc_cart_pdf_info_modal_scripts' );

function wc_cart_pdf_info_modal_markup() {
	$customer = wc_cart_pdf_get_customer();
	?>

<div class="modal micromodal-slide" id="wc-cart-pdf-info-modal" aria-hidden="true">
	<div class="modal__overlay" tabindex="-1" data-micromodal-close>
	  <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="wc-cart-pdf-info-modal-title">
		<header class="modal__header">
		  <h2 class="modal__title" id="wc-cart-pdf-info-modal-title"><?php esc_html_e( 'Request for more information', 'wc-cart-pdf' ); ?></h2>
		  <a class="modal__close" aria-label="Close modal" data-micromodal-close></a>
		</header>
		<main class="modal__content" id="wc-cart-pdf-info-modal-content">
		  <form method="post" action="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'cart-pdf' => '1' ), wc_get_cart_url() ), 'cart-pdf' ) ); ?>">

				<div class="form-row">
					<label for="billing_first_name"><?php esc_html_e( 'First name', 'wc-cart-pdf' ); ?></label>
					<input type="text" class="input-text" name="billing_first_name" id="billing_first_name" autocomplete="given-name" value="<?php echo esc_attr( $customer->get_billing_first_name() ); ?>" required />
				</div>
				<div class="form-row">
					<label for="billing_last_name"><?php esc_html_e( 'Last name', 'wc-cart-pdf' ); ?></label>
					<input type="text" class="input-text" name="billing_last_name" id="billing_last_name" autocomplete="family-name" value="<?php echo esc_attr( $customer->get_billing_last_name() ); ?>" required />
				</div>
				<div class="form-row">
					<label for="billing_email"><?php esc_html_e( 'Email address', 'wc-cart-pdf' ); ?></label>
					<input type="email" class="input-text" name="billing_email" id="billing_email" autocomplete="email username" value="<?php echo esc_attr( $customer->get_billing_email() ); ?>" required />
				</div>
				<div class="form-row">
					<label for="billing_phone"><?php esc_html_e( 'Phone', 'wc-cart-pdf' ); ?></label>
					<input type="tel" class="input-text" name="billing_phone" id="billing_phone" autocomplete="tel" value="<?php echo esc_attr( $customer->get_billing_phone() ); ?>" required />
				</div>

			  <button type="submit" class="wc-cart-pdf-info-modal-button button"><?php esc_html_e( 'Download Cart as PDF', 'wc-cart-pdf' ); ?></button>
		  </form>
		</main>
	  </div>
	</div>
  </div>

	<?php
}
add_action( 'wp_footer', 'wc_cart_pdf_info_modal_markup' );

function wc_cart_pdf_info_modal_process() {
	$required_fields = apply_filters(
		'wc_cart_pdf_info_modal_required_fields',
		array(
			'billing_first_name' => esc_html__( 'Please enter your first name', 'wc-cart-pdf' ),
			'billing_last_name'  => esc_html__( 'Please enter your last name', 'wc-cart-pdf' ),
			'billing_email'      => esc_html__( 'Please enter your email address', 'wc-cart-pdf' ),
			'billing_phone'      => esc_html__( 'Please enter your phone number', 'wc-cart-pdf' ),
		)
	);

	try {
		foreach ( $required_fields as $name => $message ) {
			if ( empty( $_POST[ $name ] ) ) {
				throw new \Exception( esc_html( $message ) );
			}
		}

		do_action( 'wc_cart_pdf_info_modal_process_errors' );
	} catch ( \Exception $e ) {
		wp_die( $e->getMessage(), 403 );
	}

	$customer_data = array();

	foreach ( $required_fields as $name => $message ) {
		$customer_data[ $name ] = sanitize_text_field( $_POST[ $name ] );
	}

	$customer_data = apply_filters( 'wc_cart_pdf_customer_data', $customer_data );

	setcookie( 'wc-cart-pdf-customer', json_encode( $customer_data ), 0, COOKIEPATH, COOKIE_DOMAIN );
}
add_action( 'wc_cart_pdf_before_process', 'wc_cart_pdf_info_modal_process' );
