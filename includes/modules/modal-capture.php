<?php
/**
 * Module to capture customer information with a modal
 *
 * @package dkjensen/wc-cart-pdf
 */

/**
 * Render modal to capture customer information.
 *
 * @return void
 */
function wc_cart_pdf_modal() {
	$customer = wc_cart_pdf_get_customer();

	$modal_capture = wc_locate_template( 'modal-capture.php', '/woocommerce/wc-cart-pdf/', WC_CART_PDF_TEMPLATE_PATH );

	if ( file_exists( $modal_capture ) ) {
		include $modal_capture;
	}
}
add_action( 'wp_footer', 'wc_cart_pdf_modal' );

/**
 * Ensure customer information is provided before downloading PDF
 *
 * @param WP_Error    $errors Errors.
 * @param WC_Customer $customer Customer.
 * @return WP_Error
 */
function wc_cart_pdf_modal_process_form( $errors, $customer ) {
	if ( empty( $customer->get_billing_email() ) ) {
		$errors->add( 'wc_cart_pdf_email_required', __( 'Email is required.', 'wc-cart-pdf' ) );
	}

	return $errors;
}
add_action( 'wc_cart_pdf_modal_form_save', 'wc_cart_pdf_modal_process_form', 10, 2 );

/**
 * Save customer information from modal form
 *
 * @return void
 */
function wc_cart_pdf_modal_form_save() {
	$errors = new WP_Error();

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'wc_cart_pdf_modal' ) ) {
		$errors->add( 'wc_cart_pdf_security_check_failed', __( 'Security check failed.', 'wc-cart-pdf' ) );
	}

	try {
		$customer = wc_cart_pdf_get_customer();

		do_action_ref_array( 'wc_cart_pdf_modal_form_save', array( &$errors, &$customer ) );
	} catch ( Exception $e ) {
		$errors->add( 'wc_cart_pdf_error', $e->getMessage() );
	}

	if ( $errors->has_errors() ) {
		wp_send_json_error( $errors->get_error_messages(), 400 );
	}

	wp_send_json_success( array( 'message' => __( 'Customer information saved.', 'wc-cart-pdf' ) ) );
}
add_action( 'wp_ajax_wc_cart_pdf_modal_form_save', 'wc_cart_pdf_modal_form_save' );
add_action( 'wp_ajax_nopriv_wc_cart_pdf_modal_form_save', 'wc_cart_pdf_modal_form_save' );

/**
 * Send a copy of the PDF to the customer
 *
 * @param \Mpdf\Mpdf $mpdf Mpdf object.
 * @return void
 */
function wc_cart_pdf_modal_email_copy( $mpdf ) {
	if ( ! isset( $_REQUEST['email_copy'] ) || 1 !== absint( wp_unslash( $_REQUEST['email_copy'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	$file_path = get_temp_dir() . 'WC_Cart-' . gmdate( 'Ymd' ) . bin2hex( openssl_random_pseudo_bytes( 5 ) ) . '.pdf';

	$mpdf->Output( $file_path, 'F' );

	$customer = wc_cart_pdf_get_customer();

	$body = esc_html__( 'A copy of your cart has been attached as a PDF to this email.', 'wc-cart-pdf' );

	wp_mail(
		apply_filters( 'wc_cart_pdf_modal_email_recipient', $customer->get_billing_email(), $mpdf, $customer ),
		/* translators: %s site title */
		apply_filters( 'wc_cart_pdf_modal_email_subject', sprintf( esc_html__( 'Your cart from %s', 'wc-cart-pdf' ), get_bloginfo( 'name' ) ), $customer ),
		apply_filters( 'wc_cart_pdf_modal_email_body', $body, $customer ),
		apply_filters( 'wc_cart_pdf_modal_email_headers', array( 'Content-Type: text/html; charset=UTF-8' ), $customer ),
		array( $file_path )
	);

	unlink( $file_path );
}
add_action( 'wc_cart_pdf_output', 'wc_cart_pdf_modal_email_copy' );

/**
 * Verify required customer information is present before downloading PDF
 *
 * @return void
 */
function wc_cart_pdf_modal_form_check() {
	$errors   = new WP_Error();
	$customer = wc_cart_pdf_get_customer();

	$errors = wc_cart_pdf_modal_process_form( $errors, $customer );

	if ( $errors->has_errors() ) {
		wp_die( wp_kses_post( wpautop( implode( "\n\n", $errors->get_error_messages() ) ) ), 400 );

		exit;
	}
}
add_action( 'wc_cart_pdf_before_process', 'wc_cart_pdf_modal_form_check' );
