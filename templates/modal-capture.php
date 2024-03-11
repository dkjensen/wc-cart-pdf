<?php
/**
 * WC Cart PDF template
 *
 * @package dkjensen/wc-cart-pdf
 */

?>

<dialog id="wc-cart-pdf-modal" class="wc-cart-pdf-modal" role="dialog" aria-labelledby="dialogTitle" aria-describedby="dialogDesc">
	<div class="wc-cart-pdf-modal-content">
		<button class="wc-cart-pdf-modal-close" role="button" aria-label="Close">&times;</button>
		<p id="dialogTitle"><?php esc_html_e( 'Please enter your email address to receive your cart as a PDF.', 'wc-cart-pdf' ); ?></p>
		<div id="wc-cart-pdf-capture-form-errors"></div>
		<form id="wc-cart-pdf-capture-form" class="wc-cart-pdf-capture-form" method="post" action="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'cart-pdf' => '1' ), wc_get_cart_url() ), 'cart-pdf' ) ); ?>">
			<div><input type="email" name="billing_email" id="billing_email" placeholder="<?php esc_html_e( 'Email', 'wc-cart-pdf' ); ?>" value="<?php echo esc_attr( $customer->get_billing_email() ); ?>" required aria-required="true"></div>
			<div>
				<label for="email_copy">
					<input type="checkbox" name="email_copy" id="email_copy" value="1" <?php checked( apply_filters( 'wc_cart_pdf_modal_email_copy_default', true ), true ); ?>>
					<?php esc_html_e( 'Send a copy of the PDF via email.', 'wc-cart-pdf' ); ?>
				</label>
			</div>
			<div><button type="submit" class="button wp-element-button"><?php esc_html_e( 'Submit', 'wc-cart-pdf' ); ?></button></div>
		</form>
	</div>
</dialog>
