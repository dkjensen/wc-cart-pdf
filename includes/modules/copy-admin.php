<?php
/**
 * Module to send admin email copy of PDF
 *
 * @package dkjensen/wc-cart-pdf
 */

if ( ! function_exists( 'get_option' ) || ! get_option( 'wc_cart_pdf_copy_admin', false ) ) {
	return;
}

/**
 * Maybe send a copy of the PDF to admin
 *
 * @since 2.1.0
 * @param \Mpdf\Mpdf $mpdf Mpdf object.
 * @return void
 */
function wc_cart_pdf_maybe_send_admin_copy( $mpdf ) {
	$file_path = get_temp_dir() . 'WC_Cart-' . gmdate( 'Ymd' ) . bin2hex( openssl_random_pseudo_bytes( 5 ) ) . '.pdf';

	$mpdf->Output( $file_path, 'F' );

	$customer = new \WC_Customer( get_current_user_id() );

	ob_start();
	?>

	<p><?php esc_html_e( 'A user has downloaded their cart as PDF, and is attached to this email.', 'wc-cart-pdf' ); ?></p>

	<?php if ( $customer->get_id() ) : ?>

	<p>
		<?php esc_html_e( 'Name', 'wc-cart-pdf' ); ?>: <?php echo esc_html( $customer->get_first_name() . ' ' . $customer->get_last_name() ); ?><br>
		<?php esc_html_e( 'Email', 'wc-cart-pdf' ); ?>: <?php echo esc_html( $customer->get_email() ); ?>
	</p>

	<p>
		<?php
            // phpcs:ignore
            echo make_clickable( get_edit_user_link( $customer->get_id() ) ); 
		?>
	</p>

	<?php endif; ?>

	<?php
	$body = ob_get_clean();

	wp_mail(
		apply_filters( 'wc_cart_pdf_admin_copy_email', get_option( 'admin_email' ), $mpdf, $customer ),
		apply_filters( 'wc_cart_pdf_admin_copy_subject', esc_html__( 'A user has downloaded their cart as PDF', 'wc-cart-pdf' ), $customer ),
		apply_filters( 'wc_cart_pdf_admin_copy_body', $body, $customer ),
		apply_filters( 'wc_cart_pdf_admin_copy_headers', array( 'Content-Type: text/html; charset=UTF-8' ), $customer ),
		array( $file_path )
	);

	unlink( $file_path );
}
add_action( 'wc_cart_pdf_output', 'wc_cart_pdf_maybe_send_admin_copy' );
