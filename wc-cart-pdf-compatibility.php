<?php
/**
 * Plugin compatibility
 *
 * @package wc-cart-pdf
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

/**
 * Gravity PDF
 *
 * @return void
 */
function wc_cart_pdf_compatibility_gravity_pdf() {
	if ( class_exists( 'GFPDF_Major_Compatibility_Checks' ) && isset( $GLOBALS['gravitypdf'] ) && isset( $_GET['cart-pdf'] ) ) {
		remove_action( 'plugins_loaded', array( $GLOBALS['gravitypdf'], 'plugins_loaded' ) );
	}
}
add_action( 'plugins_loaded', 'wc_cart_pdf_compatibility_gravity_pdf', 0 );

/**
 * Visual Products Configurator
 *
 * @return void
 */
function wc_cart_pdf_compatibility_visual_products_configurator() {
	add_filter(
		'vpc_get_config_data',
		function( $thumbnail_code ) {
			$edit_i18n = __( 'Edit', 'vpc' );

			$thumbnail_code = preg_replace( '/<\s*a[^>]*>' . $edit_i18n . '<\s*\/\s*a>/', '', $thumbnail_code ); // Hide the "Edit" link

			return $thumbnail_code;
		}
	);
}
add_action( 'wc_cart_pdf_before_process', 'wc_cart_pdf_compatibility_visual_products_configurator' );
