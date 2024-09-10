<?php
/**
 * Plugin Name:         WooCommerce Cart PDF
 * Description:         Allows customers to download their cart as a PDF
 * Version:             0.0.0-development
 * Author:              CloudCatch LLC
 * Author URI:          https://cloudcatch.io
 * Text Domain:         wc-cart-pdf
 * Domain Path:         /languages/
 * Contributors:        cloudcatch, dkjensen, seattlewebco, davidperez, exstheme
 * Requires at least:   6.2
 * Requires PHP:        8.0.0
 * WC tested up to:     9.1.2
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @package dkjensen/wc-cart-pdf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_CART_PDF_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WC_CART_PDF_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'WC_CART_PDF_TEMPLATE_PATH', WC_CART_PDF_PATH . 'templates/' );
define( 'WC_CART_PDF_VER', '0.0.0-development' );

require_once WC_CART_PDF_PATH . 'third-party/vendor/scoper-autoload.php';
require_once WC_CART_PDF_PATH . 'wc-cart-pdf-compatibility.php';

require_once WC_CART_PDF_PATH . 'includes/helpers.php';
require_once WC_CART_PDF_PATH . 'includes/markup.php';
require_once WC_CART_PDF_PATH . 'includes/blocks.php';
require_once WC_CART_PDF_PATH . 'includes/settings.php';

/**
 * Load modules
 *
 * @return void
 */
function wc_cart_pdf_load_modules() {
	if ( get_option( 'wc_cart_pdf_capture_customer', false ) ) {
		require_once WC_CART_PDF_PATH . 'includes/modules/capture-customer.php';
	}

	if ( get_option( 'wc_cart_pdf_copy_admin', false ) ) {
		require_once WC_CART_PDF_PATH . 'includes/modules/copy-admin.php';
	}

	if ( get_option( 'wc_cart_pdf_unique_increment', false ) ) {
		require_once WC_CART_PDF_PATH . 'includes/modules/unique-increment.php';
	}

	if ( get_option( 'wc_cart_pdf_modal_capture', false ) ) {
		require_once WC_CART_PDF_PATH . 'includes/modules/modal-capture.php';
	}
}
add_action( 'plugins_loaded', 'wc_cart_pdf_load_modules' );

/**
 * Load localization files
 *
 * @return void
 */
function wc_cart_pdf_language_init() {
	load_plugin_textdomain( 'wc-cart-pdf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'wc_cart_pdf_language_init' );

if ( ! extension_loaded( 'gd' ) || ! extension_loaded( 'mbstring' ) || version_compare( phpversion(), '8.0.0', '<' ) ) {

	/**
	 * Admin notice to display requirements
	 *
	 * @return void
	 */
	function wc_cart_pdf_admin_notices() {
		?>

		<div class="notice notice-warning is-dismissible">
			<p><strong><?php esc_html_e( 'WooCommerce Cart PDF requirements not met', 'wc-cart-pdf' ); ?></strong></p>
			<p><?php esc_html_e( 'WooCommerce Cart PDF requires at least PHP 8.0.0 with the mbstring and gd extensions loaded. ', 'wc-cart-pdf' ); ?></p>
		</div>

		<?php
	}

	/**
	 * Hook to add admin notices...
	 *
	 * @return void
	 */
	function wc_cart_pdf_admin_requirements_notice() {
		add_action( 'admin_notices', 'wc_cart_pdf_admin_notices' );
	}
	add_action( 'admin_init', 'wc_cart_pdf_admin_requirements_notice' );

	return;
}

/**
 * Generates the PDF for download
 *
 * @return void
 */
function wc_cart_pdf_process_download() {
	$content = '';
	$css     = '';

	if ( ! function_exists( 'WC' ) ) {
		return;
	}

	if ( ! isset( $_GET['cart-pdf'] ) ) {
		return;
	}

	if ( ! is_cart() || WC()->cart->is_empty() ) {
		return;
	}

	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'cart-pdf' ) ) {
		wc_add_notice( __( 'Invalid nonce. Unable to process PDF for download.', 'wc-cart-pdf' ), 'error' );
		return;
	}

	$mpdf = new \WCCartPDF\Mpdf\Mpdf(
		apply_filters(
			'wc_cart_pdf_mpdf_args',
			array(
				'mode'           => get_locale(),
				'format'         => 'A4',
				'default_font'   => 'dejavusans',
			)
		)
	);

	$mpdf->shrink_tables_to_fit = 1;
	$mpdf->simpleTables         = true;
	$mpdf->packTableData        = true;
	$mpdf->autoLangToFont       = true;

	$cart_table = wc_locate_template( 'cart-table.php', '/woocommerce/wc-cart-pdf/', WC_CART_PDF_TEMPLATE_PATH );
	$css        = wc_locate_template( 'pdf-styles.php', '/woocommerce/wc-cart-pdf/', WC_CART_PDF_TEMPLATE_PATH );

	/**
	 * Disable lazy loading images
	 */
	add_filter( 'wp_lazy_loading_enabled', '__return_false' );

	do_action( 'wc_cart_pdf_before_process' );

	/**
	 * Run the calculate totals method for plugins that modify the cart using this hook
	 */
	WC()->cart->calculate_totals();

	if ( file_exists( $cart_table ) ) {
		ob_start();

		include $cart_table;

		$content = apply_filters( 'wc_cart_pdf_content', ob_get_clean() );
	}

	if ( file_exists( $css ) ) {
		ob_start();

		include $css;

		$css = ob_get_clean();
	}

	$dest = 'D';

	if ( is_rtl() ) {
		$mpdf->SetDirectionality( 'rtl' );
	}

	$stream_options = apply_filters(
		'wc_cart_pdf_stream_options',
		array(
			'compress'   => 1,
			'Attachment' => 1,
		)
	);

	// phpcs:ignore
	if ( $stream_options['Attachment'] == 0 || get_option( 'wc_cart_pdf_open_pdf', false ) ) {
		$dest = 'I';
	}

	/**
	 * Hook to modify mPDF object before generating
	 *
	 * @since 2.0.6
	 */
	$mpdf = apply_filters( 'wc_cart_pdf_mpdf', $mpdf );

	$mpdf->WriteHTML( $css, 1 );
	$mpdf->WriteHTML( $content, 2 );

	if ( defined( 'WC_CART_PDF_DEBUG' ) && constant( 'WC_CART_PDF_DEBUG' ) ) {
		echo '<pre>';
		echo esc_html( '<style>' . $css . '</style>' );
		echo esc_html( $content );
		echo '</pre>';
		exit;
	}

	$mpdf->Output(
		apply_filters( 'wc_cart_pdf_filename', 'WC_Cart-' . gmdate( 'Ymd' ) . bin2hex( openssl_random_pseudo_bytes( 5 ) ) ) . '.pdf',
		apply_filters( 'wc_cart_pdf_destination', $dest )
	);

	/**
	 * Perform custom actions after PDF generated
	 *
	 * @since 2.0.6
	 */
	do_action( 'wc_cart_pdf_output', $mpdf );

	exit;
}
add_action( 'template_redirect', 'wc_cart_pdf_process_download' );

/**
 * Declare compatibility with HPOS and Cart / Checkout blocks.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);
