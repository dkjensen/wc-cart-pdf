=== WooCommerce Cart PDF ===
Contributors: cloudcatch, dkjensen, seattlewebco, davidperez, exstheme
Tested up to: 6.1
Requires PHP: 5.6.0
Stable tag: 2.3.0
Requires at least: 5.0
Donate link: https://www.buymeacoffee.com/dkjensen

Adds ability for users and guests to download their WooCommerce cart as PDF

== Description ==

Adds ability for users and guests to download their WooCommerce cart as PDF. Useful for many cases such as if a user needs a quote before completing purchase.

= Features =

- Full PDF template customization
- Send copy of each generated cart PDF to admin
- Capture customer / guest information from checkout and display on generated PDF

== Installation ==
1. Upload plugin and then activate
2. Ensure WooCommerce is installed and activated as well

== Screenshots ==

1. Preview of an example PDF generated from the WooCommerce Cart
2. Customizer settings

== Changelog ==

2.3.0
* Improve multilingual capabilities
* Add customizer control to open PDF in new tab

2.2.3
* Update compatibility

2.2.2
* Update compatibility

2.2.1
* Fix capture customer not working properly for logged in users

2.2.0
* Add customizer option to display unique generated PDF number on PDF

2.1.7
* Disable lazy loading product thumbnails by default
* Add `define( 'WC_CART_PDF_THUMBNAIL_COMPATIBILITY', true );` to **wp-config.php** in an attempt to fix product thumbnails not loading

2.1.6
* Fix logo rendering on cart table PDF template

2.1.5
* Fix capture customer feature to only capture certain checkout fields

2.1.4
* Customizer option to show Download Cart as PDF button on checkout
* Ability to capture customer and guest information from checkout and display on PDF

2.1.3
* Add language translation file
* Stylesheet changes

2.1.2
* Add compatibility with Gravity PDF plugin

2.1.1
* Send admin email as HTML

2.1.0
* Ability to send an emailed copy of generated PDF to admin
* Add date to PDF
* Add customer details to PDF

2.0.6
* Update compatibility tag
* Two new hooks to modify PDF output

2.0.5
* WordPress 5.4 compatibility
* Add support for coupons displaying on PDF
* Add minimum PHP requirements admin notice and prevent loading if not satisfied
* Minor styling update to PDF

2.0.4
* Added compatibility with Visual Products Configurator
* Perform WC cart calculate_totals() method prior to generating PDF
* Added customizer setting to modify download cart as PDF button label

2.0.3
* Fix composer dependencies

2.0.2
* Update compatibility

2.0.1
* Fix product thumbnails too large

2.0.0
* Change PDF library from Dompdf to mPDF
* Add support for RTL languages

1.0.5
* Fix CSS for language support
* Default font to Noto Sans for language support
* Update Dompdf

1.0.4
* Add PDF template actions hooks `wc_cart_pdf_before_template` and `wc_cart_pdf_after_template`

1.0.3
* Add customizer option to change PDF header logo, width and alignment
* Adjustments to default widths and styling of PDF cart table
* Bug fix: Expand {site_title} variable placeholder in PDF footer text

1.0.2
* Tested up to WordPress 5.1
* Add `wc_cart_pdf_stream_options` filter for stream options

1.0.1
* Add compatibility with TM Extra Product Options
* Add ability to override PDF template and CSS through the theme folder woocommerce/wc-cart-pdf/
* Add filter to change PDF filename
* Add shipping and taxes to PDF
* Add WooCommerce error notice if nonce is invalid
* Add action hook before PDF is generated

1.0.0
* Initial plugin release

== Upgrade Notice ==

2.0.0
* PDF generation library changed from Dompdf to mPDF
* Requires PHP >= 5.6.0

== Frequently Asked Questions ==

= How to view or open PDF instead of download? =

Add the following code snippet to your themes functions.php:

    function child_theme_wc_cart_pdf_destination( $dest ) {
        if ( class_exists( '\Mpdf\Output\Destination' ) ) {
            $dest = \Mpdf\Output\Destination::INLINE;
        }

        return $dest;
    }
    add_filter( 'wc_cart_pdf_destination', 'child_theme_wc_cart_pdf_destination' );

= How to require user to be logged in to download cart as PDF? =

Add the following code snippet to your themes functions.php:

    /**
    * Remove the default download cart button
    */
    remove_action( 'woocommerce_proceed_to_checkout', 'wc_cart_pdf_button', 21 );


    /**
    * Replace the default download cart button with our own logic to display a login notice for guests
    */
    function child_theme_wc_cart_pdf_button() {
        if( ! is_cart() || WC()->cart->is_empty() ) {
            return;
        }

        if ( is_user_logged_in() ) :
        ?>

        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'cart-pdf' => '1' ), wc_get_cart_url() ), 'cart-pdf' ) );?>" class="cart-pdf-button button" target="_blank">
            <?php esc_html_e( 'Download Cart as PDF', 'wc-cart-pdf' ); ?>
        </a>

        <?php else : ?>

        <p><a href="<?php echo get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ); ?>" class="cart-pdf-login"><?php esc_html_e( 'Please login to download your cart as a PDF', 'wc-cart-pdf' ); ?></a></p>

        <?php 
        endif;
    }
    add_action( 'woocommerce_proceed_to_checkout', 'child_theme_wc_cart_pdf_button', 21 );

= Product images are not showing on PDF =

Try adding the following to your **wp-config.php** file:

`define( 'WC_CART_PDF_THUMBNAIL_COMPATIBILITY', true );`

= How can I customize the PDF template? =

Copy the PDF template from the plugin folder **wc-cart-pdf/templates/cart-table.php** into your child theme folder **child-theme/woocommerce/wc-cart-pdf/cart-table.php**.

From there you can modify the PDF template in your child theme.
