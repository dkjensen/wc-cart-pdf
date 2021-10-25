<?php
/**
 * Plugin Name:     WooCommerce Cart PDF
 * Description:     Allows customers to download their cart as a PDF
 * Version:         2.2.0
 * Author:          Seattle Web Co.
 * Author URI:      https://seattlewebco.com
 * Text Domain:     wc-cart-pdf
 * Domain Path:     /languages/
 * Contributors:    seattlewebco, dkjensen, davidperez
 * Requires PHP:    5.6.0
 * WC tested up to: 5.8.0
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
define( 'WC_CART_PDF_VER', '2.2.0' );

require_once WC_CART_PDF_PATH . 'vendor/autoload.php';
require_once WC_CART_PDF_PATH . 'wc-cart-pdf-compatibility.php';

require_once WC_CART_PDF_PATH . 'includes/helpers.php';
require_once WC_CART_PDF_PATH . 'includes/markup.php';

require_once WC_CART_PDF_PATH . 'includes/modules/capture-customer.php';
require_once WC_CART_PDF_PATH . 'includes/modules/copy-admin.php';
require_once WC_CART_PDF_PATH . 'includes/modules/unique-increment.php';

/**
 * Load localization files
 *
 * @return void
 */
function wc_cart_pdf_language_init() {
	load_plugin_textdomain( 'wc-cart-pdf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'wc_cart_pdf_language_init' );

if ( ! extension_loaded( 'gd' ) || ! extension_loaded( 'mbstring' ) || version_compare( phpversion(), '5.6.0', '<' ) ) {

	/**
	 * Admin notice to display requirements
	 *
	 * @return void
	 */
	function wc_cart_pdf_admin_notices() {
		?>

		<div class="notice notice-warning is-dismissible">
			<p><strong><?php esc_html_e( 'WooCommerce Cart PDF requirements not met', 'wc-cart-pdf' ); ?></strong></p>
			<p><?php esc_html_e( 'WooCommerce Cart PDF requires at least PHP 5.6.0 with the mbstring and gd extensions loaded. ', 'wc-cart-pdf' ); ?></p>
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

	$mpdf = new \Mpdf\Mpdf(
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

	$cart_table = wc_locate_template( 'cart-table.php', '/woocommerce/wc-cart-pdf/', __DIR__ . '/templates/' );
	$css        = wc_locate_template( 'pdf-styles.php', '/woocommerce/wc-cart-pdf/', __DIR__ . '/templates/' );

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

		$content = ob_get_clean();
	}

	if ( file_exists( $css ) ) {
		ob_start();

		include $css;

		$css = apply_filters( 'woocommerce_email_styles', ob_get_clean() );
	}

	$dest = \Mpdf\Output\Destination::DOWNLOAD;

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
	if ( $stream_options['Attachment'] == 0 ) {
		$dest = \Mpdf\Output\Destination::INLINE;
	}

	/**
	 * Hook to modify mPDF object before generating
	 *
	 * @since 2.0.6
	 */
	$mpdf = apply_filters( 'wc_cart_pdf_mpdf', $mpdf );

	$mpdf->WriteHTML( $css, \Mpdf\HTMLParserMode::HEADER_CSS );
	$mpdf->WriteHTML( $content, \Mpdf\HTMLParserMode::HTML_BODY );
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
 * Register various customizer options for modifying the cart PDF
 *
 * @since 1.0.3
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function wc_cart_pdf_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'wc_cart_pdf',
		array(
			'title'                 => __( 'Cart PDF', 'wc-cart-pdf' ),
			'priority'              => 50,
			'panel'                 => 'woocommerce',
		)
	);

	$wp_customize->add_setting(
		'wc_cart_pdf_button_label',
		array(
			'default'               => __( 'Download Cart as PDF', 'wc-cart-pdf' ),
			'type'                  => 'option',
			'capability'            => 'manage_woocommerce',
			'sanitize_callback'     => 'esc_html',
			'transport'             => 'refresh',
		)
	);

	$wp_customize->add_control(
		'wc_cart_pdf_button_label',
		array(
			'label'                 => __( 'Button label', 'wc-cart-pdf' ),
			'description'           => __( 'Text that is displayed on the button which generates the PDF.', 'wc-cart-pdf' ),
			'section'               => 'wc_cart_pdf',
			'settings'              => 'wc_cart_pdf_button_label',
			'type'                  => 'text',
		)
	);

	$wp_customize->add_setting(
		'wc_cart_pdf_logo',
		array(
			'default'               => get_option( 'woocommerce_email_header_image' ),
			'type'                  => 'option',
			'capability'            => 'manage_woocommerce',
			'sanitize_callback'     => 'esc_url',
			'transport'             => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'wc_cart_pdf_logo',
		array(
			'label'                 => __( 'Logo URL', 'wc-cart-pdf' ),
			'description'           => __( 'Image URL of logo for the cart PDF, must live on current server.', 'wc-cart-pdf' ),
			'section'               => 'wc_cart_pdf',
			'settings'              => 'wc_cart_pdf_logo',
			'type'                  => 'text',
		)
	);

	$wp_customize->add_setting(
		'wc_cart_pdf_logo_width',
		array(
			'default'               => 400,
			'type'                  => 'option',
			'capability'            => 'manage_woocommerce',
			'sanitize_callback'     => 'absint',
			'sanitize_js_callback'  => 'absint',
			'transport'             => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'wc_cart_pdf_logo_width',
		array(
			'label'                 => __( 'Logo width', 'wc-cart-pdf' ),
			'description'           => __( 'Logo size used for the cart PDF.', 'wc-cart-pdf' ),
			'section'               => 'wc_cart_pdf',
			'settings'              => 'wc_cart_pdf_logo_width',
			'type'                  => 'number',
			'input_attrs'           => array(
				'min'           => 0,
				'step'          => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'wc_cart_pdf_logo_alignment',
		array(
			'default'               => 'center',
			'type'                  => 'option',
			'capability'            => 'manage_woocommerce',
			'sanitize_callback'     => 'wc_clean',
			'sanitize_js_callback'  => 'wc_clean',
			'transport'             => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'wc_cart_pdf_logo_alignment',
		array(
			'label'                 => __( 'Logo alignment', 'wc-cart-pdf' ),
			'description'           => __( 'Alignment of the logo within header of the cart PDF.', 'wc-cart-pdf' ),
			'section'               => 'wc_cart_pdf',
			'settings'              => 'wc_cart_pdf_logo_alignment',
			'type'                  => 'radio',
			'choices'               => array(
				'left'          => __( 'Left', 'wc-cart-pdf' ),
				'center'        => __( 'Center', 'wc-cart-pdf' ),
				'right'         => __( 'Right', 'wc-cart-pdf' ),
			),
		)
	);

	$wp_customize->add_setting(
		'wc_cart_pdf_copy_admin',
		array(
			'default'               => '',
			'type'                  => 'option',
			'capability'            => 'manage_woocommerce',
			'sanitize_callback'     => 'wc_clean',
			'sanitize_js_callback'  => 'wc_clean',
			'transport'             => 'postMessage',
		)
	);

	$wp_customize->add_setting(
		'wc_cart_pdf_show_checkout',
		array(
			'default'               => '',
			'type'                  => 'option',
			'capability'            => 'manage_woocommerce',
			'sanitize_callback'     => 'wc_clean',
			'sanitize_js_callback'  => 'wc_clean',
			'transport'             => 'postMessage',
		)
	);

	$wp_customize->add_setting(
		'wc_cart_pdf_capture_customer',
		array(
			'default'               => '',
			'type'                  => 'option',
			'capability'            => 'manage_woocommerce',
			'sanitize_callback'     => 'wc_clean',
			'sanitize_js_callback'  => 'wc_clean',
			'transport'             => 'postMessage',
		)
	);

	$wp_customize->add_setting(
		'wc_cart_pdf_unique_increment',
		array(
			'default'               => '',
			'type'                  => 'option',
			'capability'            => 'manage_woocommerce',
			'sanitize_callback'     => 'wc_clean',
			'sanitize_js_callback'  => 'wc_clean',
			'transport'             => 'postMessage',
		)
	);

	$wp_customize->add_control(
		'wc_cart_pdf_copy_admin',
		array(
			'label'                 => __( 'Send a copy of PDF to admin via email', 'wc-cart-pdf' ),
			'section'               => 'wc_cart_pdf',
			'settings'              => 'wc_cart_pdf_copy_admin',
			'type'                  => 'checkbox',
		)
	);

	$wp_customize->add_control(
		'wc_cart_pdf_show_checkout',
		array(
			'label'                 => __( 'Show Download Cart as PDF on checkout', 'wc-cart-pdf' ),
			'section'               => 'wc_cart_pdf',
			'settings'              => 'wc_cart_pdf_show_checkout',
			'type'                  => 'checkbox',
		)
	);

	$wp_customize->add_control(
		'wc_cart_pdf_capture_customer',
		array(
			'label'                 => __( 'Capture customer information on checkout', 'wc-cart-pdf' ),
			'section'               => 'wc_cart_pdf',
			'settings'              => 'wc_cart_pdf_capture_customer',
			'type'                  => 'checkbox',
		)
	);

	$wp_customize->add_control(
		'wc_cart_pdf_unique_increment',
		array(
			'label'                 => __( 'Display unique generated PDF number', 'wc-cart-pdf' ),
			'section'               => 'wc_cart_pdf',
			'settings'              => 'wc_cart_pdf_unique_increment',
			'type'                  => 'checkbox',
		)
	);
}
add_action( 'customize_register', 'wc_cart_pdf_customize_register' );
