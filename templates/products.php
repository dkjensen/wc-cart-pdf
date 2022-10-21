<?php
/**
 * WC Cart Products PDF template
 *
 * @package wc-cart-pdf
 */

/**
 * Before template hook
 *
 * @since 1.0.4
 * @package dkjensen/wc-cart-pdf
 */
do_action( 'wc_cart_pdf_before_template' );

?>
	<div class="wc_cart_pdf_template">
		<?php
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
				<div style="height:24cm;clear:both"
					 class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
					<h2 class="product-name" style="text-align:center;font-size:24px;line-height:30px;margin:20px 0 20px;"
						data-title="<?php esc_attr_e( 'Product', 'wc-cart-pdf' ); ?>">
						<?php
						if ( ! $product_permalink ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
						}

						do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
						?>
					</h2>

					<div class="product-thumbnail-images" style="text-align:center;">
						<?php

						//gallery
						// Not work with WPML
						// $attachment_ids    = $_product->get_gallery_image_ids();
						$post_meta = get_post_meta( $product_id, '_product_image_gallery',true );
						$attachment_ids    = $post_meta ? explode(',', $post_meta) : array();
						$gallery_items_num = count( $attachment_ids ) + 1;
						$image_width       = '100%';
						$wrapper           = 'div';
						$image_size        = 'full';
						switch ( $gallery_items_num ) {
							case 1:
								$wrapper    = 'span';
								$image_size = 'medium';
							case 2:
								$image_width = '48%';
								break;
							case 3:
								$image_width = '31%';
								break;
							case 4:
								$image_width = '23%';
								break;
							case 5:
								$image_width = '18%';
								break;
							default:
								$image_width = '18%';
						}

						//thumbnail
						$thumbnail = apply_filters(
							'woocommerce_cart_item_thumbnail',
							$_product->get_image(
								$image_size
							),
							$cart_item,
							$cart_item_key
						);

						echo '<' . $wrapper . ' style="padding:1%;float:left;width:' . $image_width . '">';
						if ( ! $product_permalink ) {
							echo $thumbnail; // phpcs:ignore
						} else {
							printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // phpcs:ignore
						}
						echo '</' . $wrapper . '>';


						if ( $attachment_ids && $_product->get_image_id() ) {
							foreach ( $attachment_ids as $attachment_id ) {
								echo '<div style="padding:1%;float:left;width:' . $image_width . '">';
								echo wp_get_attachment_image( (int) $attachment_id, 'full' );
								echo '</div>';
							}
						}

						?>
					</div>

					<div class="product-description" style="margin:40px 0 0;clear:both;">
						<?php
						$product_content = apply_filters( 'the_content', get_the_content( null, null, $product_id ) );
						$product_content = preg_replace('/\[\/vc.*]|\[vc.*]/', '', $product_content );
						echo wp_kses_post( $product_content );


						// Meta data.
						echo '<div class="shop_table product-meta-data" style="margin:40px 0;">' . wc_get_formatted_cart_item_data( $cart_item ) . '</div>'; // phpcs:ignore

						// Backorder notification.
						if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'wc-cart-pdf' ) . '</p>', $product_id ) );
						}

						?>
					</div>

					<h3 style="width:49%;float:left;" class="product-price"
						data-title="<?php esc_attr_e( 'Price', 'wc-cart-pdf' ); ?>">
						<?php
						echo esc_html__( 'Price', 'wc-cart-pdf' ) . ': ';

						echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore
						?>
					</h3>
					<h3 style="width:49%;float:right;text-align:right;">
						<?php
						if ( wc_product_sku_enabled() && ( $_product->get_sku() || $_product->is_type( 'variable' ) ) ) :
							$sku = $_product->get_sku() ? $_product->get_sku() : esc_html__( 'N/A', 'wc-cart-pdf' );
							echo esc_html__( 'SKU', 'wc-cart-pdf' ) . ': ';
							echo esc_html( $sku );
						endif;
						?>
					</h3>

					<div style="clear:both"></div>
				</div>
				<?php
				if ( get_option( 'wc_cart_pdf_show_bottom_site_url' ) && $product_permalink ) {
					echo '<p style="text-align:center"><a href="' . esc_url( $product_permalink ) . '">' . esc_url( $product_permalink ) . '</a></p>';
				}

			}
		}
		?>
	</div>

<?php
/**
 * After template hook
 *
 * @since 1.0.4
 */
do_action( 'wc_cart_pdf_after_template' );
