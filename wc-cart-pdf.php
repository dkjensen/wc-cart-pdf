<?php
/**
 * Plugin Name:     WooCommerce Cart PDF
 * Description:     Allows customers to download their cart as a PDF
 * Version:         2.0.1
 * Author:          Seattle Web Co.
 * Author URI:      https://seattlewebco.com
 * Text Domain:     wc-cart-pdf
 * Domain Path:     /languages/
 * Contributors:    seattlewebco, dkjensen
 * Requires PHP:    5.6.0
 * WC tested up to: 3.7.1
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
 */


if( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WC_CART_PDF_PATH', plugin_dir_path( __FILE__ ) );


require WC_CART_PDF_PATH . 'vendor/autoload.php';
require WC_CART_PDF_PATH . 'wc-cart-pdf-compatibility.php';


/**
 * Generates the PDF for download
 *
 * @return void
 */
function wc_cart_pdf_process_download() {
    if ( ! function_exists( 'WC' ) ) {
        return;
    }

    if ( ! isset( $_GET['cart-pdf'] ) ) {
        return;
    }

    if ( ! is_cart() || WC()->cart->is_empty() ) {
        return;
    }

    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'cart-pdf' ) ) {
        wc_add_notice( __( 'Invalid nonce. Unable to process PDF for download.', 'wc_cart_pdf' ), 'error' );
        return;
    }

    $mpdf = new \Mpdf\Mpdf( array( 'format' => 'A4', 'default_font' => 'dejavusans' ) );
    $mpdf->shrink_tables_to_fit = 1;
    $mpdf->simpleTables = true;
    $mpdf->packTableData = true;

    $content = $css = '';

    $cart_table = wc_locate_template( 'cart-table.php', '/woocommerce/wc-cart-pdf/', __DIR__ . '/templates/' );
    $css        = wc_locate_template( 'pdf-styles.php', '/woocommerce/wc-cart-pdf/', __DIR__ . '/templates/' );

    do_action( 'wc_cart_pdf_before_process' );

    if( file_exists( $cart_table ) ) {
        ob_start();

        include $cart_table;

        $content = ob_get_clean();
    }
    
    if( file_exists( $css ) ) {
        ob_start();

        include $css;

        $css = apply_filters( 'woocommerce_email_styles', ob_get_clean() );
    }

    $dest = \Mpdf\Output\Destination::DOWNLOAD;

    if ( is_rtl() ) {
        $mpdf->SetDirectionality( 'rtl' );
    }

    $stream_options = apply_filters( 'wc_cart_pdf_stream_options', array( 'compress' => 1, 'Attachment' => 1 ) );

    if ( $stream_options['Attachment'] == 0 ) {
        $dest = \Mpdf\Output\Destination::INLINE;
    }

    $mpdf->WriteHTML( $css, \Mpdf\HTMLParserMode::HEADER_CSS );
    $mpdf->WriteHTML( $content, \Mpdf\HTMLParserMode::HTML_BODY );
    $mpdf->Output( 
        apply_filters( 'wc_cart_pdf_filename', 'WC_Cart-' . date( 'Ymd' ) . bin2hex( openssl_random_pseudo_bytes( 5 ) ) ) . '.pdf', 
        apply_filters( 'wc_cart_pdf_destination', $dest )
    );

    exit;
}
add_action( 'template_redirect', 'wc_cart_pdf_process_download' );


/**
 * Renders the download cart as PDF button
 *
 * @return void
 */
function wc_cart_pdf_button() {
    if( ! is_cart() || WC()->cart->is_empty() ) {
        return;
    }
    
    ?>

    <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'cart-pdf' => '1' ), wc_get_cart_url() ), 'cart-pdf' ) );?>" class="cart-pdf-button button" target="_blank">
        <?php esc_html_e( 'Download Cart as PDF', 'wc-cart-pdf' ); ?>
    </a>

    <?php
}
add_action( 'woocommerce_proceed_to_checkout', 'wc_cart_pdf_button', 21 );


/**
 * Register various customizer options for modifying the cart PDF
 *
 * @since 1.0.3
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 * @return void
 */
function wc_cart_pdf_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'wc_cart_pdf', array(
        'title'                 => __( 'Cart PDF', 'wc-cart-pdf' ),
        'priority'              => 50,
        'panel'                 => 'woocommerce',
    ) );

    $wp_customize->add_setting( 'wc_cart_pdf_logo', array(
        'default'               => get_option( 'woocommerce_email_header_image' ),
        'type'                  => 'option',
        'capability'            => 'manage_woocommerce',
        'sanitize_callback'     => 'esc_url',
        'transport'             => 'postMessage'
    ) );

    $wp_customize->add_control( 'wc_cart_pdf_logo', array(
        'label'                 => __( 'Logo URL', 'wc-cart-pdf'),
        'description'           => __( 'Image URL of logo for the cart PDF, must live on current server.', 'wc-cart-pdf'),
        'section'               => 'wc_cart_pdf',
        'settings'              => 'wc_cart_pdf_logo',
        'type'                  => 'text',
    ) );

    $wp_customize->add_setting( 'wc_cart_pdf_logo_width', array(
        'default'               => 400,
        'type'                  => 'option',
        'capability'            => 'manage_woocommerce',
        'sanitize_callback'     => 'absint',
        'sanitize_js_callback'  => 'absint',
        'transport'             => 'postMessage'
    ) );

    $wp_customize->add_control( 'wc_cart_pdf_logo_width', array(
        'label'                 => __( 'Logo width', 'wc-cart-pdf'),
        'description'           => __( 'Logo size used for the cart PDF.', 'wc-cart-pdf'),
        'section'               => 'wc_cart_pdf',
        'settings'              => 'wc_cart_pdf_logo_width',
        'type'                  => 'number',
        'input_attrs'           => array(
            'min'           => 0,
            'step'          => 1,
        ),
    ) );

    $wp_customize->add_setting( 'wc_cart_pdf_logo_alignment', array(
        'default'               => 'center',
        'type'                  => 'option',
        'capability'            => 'manage_woocommerce',
        'sanitize_callback'     => 'wc_clean',
        'sanitize_js_callback'  => 'wc_clean',
        'transport'             => 'postMessage'
    ) );

    $wp_customize->add_control( 'wc_cart_pdf_logo_alignment', array(
        'label'                 => __( 'Logo alignment', 'wc-cart-pdf'),
        'description'           => __( 'Alignment of the logo within header of the cart PDF.', 'wc-cart-pdf'),
        'section'               => 'wc_cart_pdf',
        'settings'              => 'wc_cart_pdf_logo_alignment',
        'type'                  => 'radio',
        'choices'               => array(
            'left'          => __( 'Left', 'wc-cart-pdf'),
            'center'        => __( 'Center', 'wc-cart-pdf'),
            'right'         => __( 'Right', 'wc-cart-pdf'),
        ),
    ) );
}
add_action( 'customize_register', 'wc_cart_pdf_customize_register' );



/**
 * Plugin activation hook
 *
 * @since 2.0.2
 * @return void
 */
function wc_cart_pdf_activate() {
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/wc-cart-pdf';
    if (! is_dir($upload_dir)) {
       mkdir( $upload_dir, 0755 );
    }
}

register_activation_hook( __FILE__, 'wc_cart_pdf_activate' );



/**
 * Expand {site_title} placeholder variable
 *
 * @since 1.0.3
 * @param string $string
 * @return string
 */
function wc_cart_pdf_footer_text( $string ) {
    return str_replace( '{site_title}', wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ), $string );
}
add_filter( 'woocommerce_email_footer_text', 'wc_cart_pdf_footer_text' );