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
	register_block_type_from_metadata( WC_CART_PDF_PATH . 'assets/blocks' );
}
add_action( 'init', 'wc_cart_pdf_register_blocks' );

/**
 * Enqueue editor scripts
 *
 * @return void
 */
function wc_cart_pdf_block_assets() {
	wp_enqueue_script( 'wc-cart-pdf-blocks', WC_CART_PDF_URL . 'assets/blocks/blocks.js', array(), WC_CART_PDF_VER, true );
}
add_action( 'enqueue_block_editor_assets', 'wc_cart_pdf_block_assets' );

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
