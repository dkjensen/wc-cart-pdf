<?php
/**
 * Plugin Name:  WooCommerce Cart PDF
 * Description:  Allows customers to download their cart as a PDF
 * Version:      1.0.0
 * Author:       David Jensen
 * Author URI:   https://dkjensen.com
 * Text Domain:  wc-cart-pdf
 * Domain Path:  /languages/
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

require 'vendor/autoload.php';


/**
 * Generates the PDF for download
 *
 * @return void
 */
function wc_cart_pdf_process_download() {
    if( ! function_exists( 'WC' ) ) {
        return;
    }

    if( ! is_cart() || WC()->cart->is_empty() ) {
        return;
    }

    if( ! isset( $_GET['cart-pdf'] ) ) {
        return;
    }

    if( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'cart-pdf' ) ) {
        return;
    }

    $dompdf = new \Dompdf\Dompdf();

    ob_start();

    include 'templates/cart-table.php';

    $content = ob_get_clean();

    ob_start();

    include 'templates/pdf-styles.php';
    
    $css = apply_filters( 'woocommerce_email_styles', ob_get_clean() );

    $dompdf->loadHtml( '<style>' . $css . '</style>' . $content );

    $dompdf->setPaper( 'A4', 'portrait' );

    $dompdf->render();

    ob_end_clean();

    $dompdf->stream( 'WC_Cart-' . date( 'Ymd' ) . bin2hex( openssl_random_pseudo_bytes( 5 ) ) . '.pdf' );
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

        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'cart-pdf' => '1' ), wc_get_cart_url() ), 'cart-pdf' ) );?>" class="cart-pdf-button button">
            <?php esc_html_e( 'Download Cart as PDF', 'wc-cart-pdf' ); ?>
        </a>

        <?php
    }
}
add_action( 'woocommerce_proceed_to_checkout', 'wc_cart_pdf_button', 21 );