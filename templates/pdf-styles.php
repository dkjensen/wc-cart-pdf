<?php
/**
 * PDF styles
 *
 * @package dkjensen/wc-cart-pdf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load colors.
$bg        = get_option( 'woocommerce_email_background_color' );
$body      = get_option( 'woocommerce_email_body_background_color' );
$base      = get_option( 'woocommerce_email_base_color' );
$base_text = wc_light_or_dark( $base, '#202020', '#ffffff' );
$text      = get_option( 'woocommerce_email_text_color' );

// Pick a contrasting color for links.
$link = wc_hex_is_light( $base ) ? $base : $base_text;
if ( wc_hex_is_light( $body ) ) {
	$link = wc_hex_is_light( $base ) ? $base_text : $base;
}

$bg_darker_10    = wc_hex_darker( $bg, 10 );
$body_darker_10  = wc_hex_darker( $body, 10 );
$base_lighter_20 = wc_hex_lighter( $base, 80 );
$base_lighter_40 = wc_hex_lighter( $base, 40 );
$text_lighter_20 = wc_hex_lighter( $text, 20 );

?>
.wc_cart_pdf_template {
	color: <?php echo esc_attr( $text_lighter_20 ); ?>;
	font-family: "dejavusans", "notosans", "helveticaneue", helvetica, roboto, arial, sans-serif;
	font-size: 13px;
	margin: 0;
	text-indent: 0pt;
	line-height: normal;
	margin-collapse: collapse;
	hyphens: manual;
	font-kerning: auto;
	padding: 0;
	-webkit-text-size-adjust: none !important;
	width: 100%;
}
a {
	color: <?php echo esc_attr( $link ); ?>;
	text-decoration: none;
}
b, strong, th {
	font-weight: normal;
}
p {
	margin: 1.12em 0;
}
h1 {
	font-size: 2em;
	font-weight: bold;
	margin: 0.67em 0;
	page-break-after: avoid;
}
h2 {
	font-size: 1.5em;
	font-weight: bold;
	margin: 0.75em 0;
	page-break-after: avoid;
}
h3 {
	font-size: 1.17em;
	font-weight: bold;
	margin: 0.83em 0;
	page-break-after: avoid;
}
h4 {
	font-weight: bold;
	margin: 1.12em 0;
	page-break-after: avoid;
}
h5 {
	font-size: 0.83em;
	font-weight: bold;
	margin: 1.5em 0;
	page-break-after: avoid;
}
h6 {
	font-size: 0.75em;
	font-weight: bold;
	margin: 1.67em 0;
	page-break-after: avoid;
}
hr {
	color: #888888;
	text-align: center;
	width: 100%;
	height: 0.2mm;
	margin-top: 0.83em;
	margin-bottom: 0.83em;
}
pre {
	margin: 0.83em 0;
	font-family: monospace;
}
s,
strike,
del {
	text-decoration: line-through;
}
sub {
	vertical-align: sub;
	font-size: 55%;
}
sup {
	vertical-align: super;
	font-size: 55%;
}
u,
ins {
	text-decoration: underline;
}
i,
cite,
q,
em,
var,
address {
	font-style: italic;
}
samp,
code,
kbd,
tt {
	font-family: monospace;
}
small {
	font-size: 83%;
}
big {
	font-size: 117%;
}
acronym {
	font-size: 77%;
	font-weight: bold;
}
blockquote {
	margin-left: 40px;
	margin-right: 40px;
	margin-top: 1.12em;
	margin-bottom: 1.12em;
}
ul,
ol {
	padding: 0 auto;
	margin-top: 0.83em;
	margin-bottom: 0.83em;
}
dl {
	margin: 1.67em 0;
}
dd {
	padding-left: 40px;
}
table {
	color: <?php echo esc_attr( $text_lighter_20 ); ?>;
	margin: 0;
	border-collapse: separate;
	border-spacing: 0px;
	empty-cells: show;
	line-height: 1.2;
	font-size: 12px;
	vertical-align: middle;
	hyphens: manual;
	font-kerning: auto;
}
th {
	text-align: center;
	padding: 0.1em;
}
td {
	padding: 0.1em;
}
.shop_table {
	table-layout: fixed;
	width: 100%;
}
.shop_table .product-thumbnail {
	width: 10%;
	text-align: right;
	overflow: hidden;
}
.shop_table .product-quantity {
	width: 15%;
	text-align: right;
}
.shop_table .product-price,
.shop_table .product-subtotal {
	width: 20%;
	text-align: right;
}
.shop_table .product-name {
	width: 35%;
}
.shop_table .product-thumbnail img {
	max-width: 100%;
	height: auto;
}
.shop_table td,
.shop_table th {
	vertical-align: top;
	padding: .5em .5em 1em;
}
.shop_table dl {
	margin: 0;
	padding: .5em 0;
}
.shop_table dd,
.shop_table dt {
	font-size: 12px;
	display: inline;
	margin: 0;
	padding: 0;
}
.shop_table dt {
	font-weight: bold;
}
.shop_table dd * {
	display: inline;
}
.shop_table dd:after {
	display: block;
	font-size: 0;
	content: "\A";
	clear: both;
	height: 0;
	white-space: pre;
}
.cart-total-row th,
.cart-total-row td {
	background: #fafafa;
}
.cart-total-row td {
	text-align: right;
}
.woocommerce-remove-coupon {
	display: none !important;
}
#template_header_image {
	text-align: center;
	padding: 0 0 2em;
}
#template_header_meta {
	font-size: 12px;
	text-align: right;
}
#template_footer {
	color: <?php echo esc_attr( $text_lighter_20 ); ?>;
	padding: 2em 0 0;
	font-size: 12px;
	text-align: center;
}
