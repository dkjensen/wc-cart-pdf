<?php
/**
 * Plugin Name:  WC Cart PDF
 * Description:  Allows customers to download their cart as a PDF
 * Version:      1.0.2
 * Author:       David Jensen
 * Author URI:   https://dkjensen.com
 * Text Domain:  wc-cart-pdf
 * Domain Path:  /languages/
 * Contributors: seattlewebco, dkjensen
 * Requires PHP: 5.3.6
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
    if( ! function_exists( 'WC' ) ) {
        return;
    }

    if( ! isset( $_GET['cart-pdf'] ) ) {
        return;
    }

    if( ! is_cart() || WC()->cart->is_empty() ) {
        return;
    }

    if( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'cart-pdf' ) ) {
        wc_add_notice( __( 'Invalid nonce. Unable to process PDF for download.', 'wc_cart_pdf' ), 'error' );
        return;
    }

    $dompdf = new \Dompdf\Dompdf();

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

    $dompdf->loadHtml( '<style>' . $css . '</style>' . $content );
    $dompdf->setPaper( 'A4', 'portrait' );
    $dompdf->render();
    $dompdf->stream( 
        apply_filters( 'wc_cart_pdf_filename', 'WC_Cart-' . date( 'Ymd' ) . bin2hex( openssl_random_pseudo_bytes( 5 ) ) ) . '.pdf', 
        
        /**
         * 'compress' => 1 or 0 - apply content stream compression, this is on (1) by default
         * 'Attachment' => 1 or 0 - if 1, force the browser to open a download dialog, on (1) by default
         */ 
        apply_filters( 'wc_cart_pdf_stream_options', array( 'compress' => 1, 'Attachment' => 1 ) ) 
    );

    exit;
}
add_action( 'template_redirect', 'wc_cart_pdf_process_download' );


if( ! function_exists( 'wc_cart_pdf_button' ) ) {

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
}
add_action( 'woocommerce_proceed_to_checkout', 'wc_cart_pdf_button', 21 );