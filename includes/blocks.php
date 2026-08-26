<?php
/**
 * Block editor functions
 *
 * @package dkjensen/wc-cart-pdf
 */

/**
 * Register custom blocks.
 *
 * @return void
 */
function wc_cart_pdf_register_blocks() {
	register_block_type_from_metadata( WC_CART_PDF_PATH . 'assets/blocks/cart-pdf-button' );
}
add_action( 'init', 'wc_cart_pdf_register_blocks' );

/**
 * Allow the Cart PDF button inside WooCommerce Cart and Checkout blocks.
 *
 * @return void
 */
function wc_cart_pdf_enqueue_checkout_filters() {
	if ( ! wp_script_is( 'wc-blocks-checkout', 'registered' ) ) {
		return;
	}

	$asset_path = WC_CART_PDF_PATH . 'assets/js/checkout-filters.asset.php';
	$asset      = file_exists( $asset_path )
		? require $asset_path
		: array(
			'dependencies' => array(),
			'version'      => WC_CART_PDF_VER,
		);

	$dependencies = array_unique(
		array_merge(
			$asset['dependencies'],
			array( 'wc-blocks-checkout' )
		)
	);

	wp_enqueue_script(
		'wc-cart-pdf-checkout-filters',
		WC_CART_PDF_URL . 'assets/js/checkout-filters.js',
		$dependencies,
		$asset['version'],
		true
	);
}
add_action( 'enqueue_block_assets', 'wc_cart_pdf_enqueue_checkout_filters' );

/**
 * Add the anchor link to the cart button block
 *
 * @param string $block_content The block content.
 * @param array  $block The full block, including name and attributes.
 * @return string
 */
function wc_cart_pdf_render_block( $block_content, $block ) {
	if ( 'wc-cart-button/cart-pdf-button' === $block['blockName'] ) {
		/**
		 * Ensure WooCommerce is active
		 */
		if ( ! function_exists( 'wc_get_cart_url' ) ) {
			return $block_content;
		}

		/**
		 * Ensure we are running WordPress 6.2+
		 */
		if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$block_content = '';

			if ( current_user_can( 'edit_posts' ) ) {
				$block_content .= '<p>';
				$block_content .= sprintf(
					/* translators: %s: Link to plugin settings */
					esc_html__( 'The %s block requires WordPress 6.2 or higher.', 'wc-cart-pdf' ),
					'<a href="' . esc_url( 'https://wordpress.org/plugins/wc-cart-pdf/' ) . '" target="_blank" rel="nofollow">' . esc_html__( 'Cart PDF Button', 'wc-cart-pdf' ) . '</a>'
				);
				$block_content .= '</p>';
			}

			return $block_content;
		}

		$tags = new WP_HTML_Tag_Processor( $block_content );

		if ( $tags->next_tag( 'a' ) ) {
			$tags->set_attribute( 'href', esc_url( wp_nonce_url( add_query_arg( array( 'cart-pdf' => '1' ), wc_get_cart_url() ), 'cart-pdf' ) ) );
		}

		$block_content = $tags->get_updated_html();
	}

	return $block_content;
}
add_filter( 'render_block', 'wc_cart_pdf_render_block', 10, 2 );
