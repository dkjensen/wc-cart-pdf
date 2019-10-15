=== WooCommerce Cart PDF ===
Contributors: dkjensen, seattlewebco
Tested up to: 5.2.4
Requires PHP: 5.6.0
Stable tag: 2.0.0

Adds ability for users and guests to download their WooCommerce cart as PDF

== Description ==
Adds ability for users and guests to download their WooCommerce cart as PDF

Useful for many cases such as if a user needs a quote before completing purchase

== Installation ==
1. Upload plugin and then activate
2. Ensure WooCommerce is installed and activated as well

== Changelog ==
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

== Frequently Asked Questions ==
= How to view or open PDF instead of download? =

Add the following code snippet to your themes functions.php:

    function child_theme_wc_cart_pdf_destination( $options ) {
        $options['Attachment'] = 0;

        return $options;
    }
    add_filter( 'wc_cart_pdf_destination', 'child_theme_wc_cart_pdf_destination' );