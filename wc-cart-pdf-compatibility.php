<?php
/**
 * Plugin compatibility
 * 
 * @package wc_cart_pdf
 */


/**
 * TM Extra Product Options
 *
 * @see https://codecanyon.net/item/woocommerce-extra-product-options/7908619
 * @return void
 */
function wc_cart_pdf_compatibility_tm_extra_product_options() {
    add_filter( 'wc_epo_no_edit_options', '__return_true' ); // Hide "Edit options" link on product title
}
add_action( 'wc_cart_pdf_before_process', 'wc_cart_pdf_compatibility_tm_extra_product_options' );