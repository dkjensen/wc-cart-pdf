<div id="template_header_image">
    <?php
        if ( $img = get_option( 'woocommerce_email_header_image' ) ) {
            $img = parse_url( $img, PHP_URL_PATH );
            $img = esc_url( $_SERVER['DOCUMENT_ROOT'] . $img );

            echo '<p style="margin-top:0;"><img src="' . esc_url( $img ) . '" alt="' . get_bloginfo( 'name', 'display' ) . '" /></p>';
        }
    ?>
</div>
<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
    <thead>
        <tr>
            <th class="product-thumbnail">&nbsp;</th>
            <th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
            <th class="product-price"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
            <th class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
            <th class="product-subtotal"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                ?>
                <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

                    <td class="product-thumbnail">
                    <?php
                    if ( $_product->get_image_id() ) {
                        $image = wp_get_attachment_image_src( $_product->get_image_id() );

                        $image = parse_url( $image[0], PHP_URL_PATH );

                        $thumbnail = sprintf( '<img src="%s" />', esc_url( $_SERVER['DOCUMENT_ROOT'] . $image ) );
                    }

                    if ( ! $product_permalink ) {
                        echo wp_kses_post( $thumbnail );
                    } else {
                        printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), wp_kses_post( $thumbnail ) );
                    }
                    ?>
                    </td>

                    <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                    <?php
                    if ( ! $product_permalink ) {
                        echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
                    } else {
                        echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                    }

                    do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

                    // Meta data.
                    echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

                    // Backorder notification.
                    if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
                        echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>' ) );
                    }
                    ?>
                    </td>

                    <td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
                        <?php
                            echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
                        ?>
                    </td>

                    <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                        <?php print esc_html( $cart_item['quantity'] ); ?>
                    </td>

                    <td class="product-subtotal" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
                        <?php
                            echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
                        ?>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        <tr>
            <td colspan="4" align="right" class="row-subtotal"><strong><?php _e( 'Subtotal', 'wc-cart-pdf' ); ?></strong></td>
            <td class="row-subtotal"><?php print WC()->cart->get_cart_subtotal(); ?></td>
        </tr>
    </tbody>
</table>
<div id="template_footer">
    <?php echo wpautop( wp_kses_post( wptexturize( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) ); ?>
</div>