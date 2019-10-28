<?php

/**
 * View for constant email styles
 * Based on WooCommerce 2.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// !important; is a gmail hack to prevent styles being stripped if it doesn't like something.
?>
#wrapper {
    margin: 0;
    -webkit-text-size-adjust: none !important;
    width: 100%;
}

#template_container {
    box-shadow: 0 1px 6px rgba(0,0,0,0.15) !important;
}

#template_header {
    border-bottom: 0;
    line-height: 100%;
    vertical-align: middle;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

#template_header h1 {
    display: block;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    line-height: 150%;
    margin: 0;
    -webkit-font-smoothing: antialiased;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

#template_footer td {
    padding: 0;
    -webkit-border-radius: 6px;
}

#template_footer #credit {
    border: 0;
    font-family: Arial;
    line-height: 125%;
}

#body_content p {
    margin: 0 0 16px;
}

#body_content_inner {
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    line-height: 150%;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;
}

.text {
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
}

#header_wrapper {
    padding: 36px 48px;
    display: block;
}

#template_body h1,
#template_body h2,
#template_body h3,
#template_body h4,
#template_body h5,
#template_body h6 {
    display: block;
    font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
    text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>;

}

#template_body h1 {
    line-height: 150%;
    margin: 20px 0 5px;
    padding-bottom: 5px;
}

#template_body h2 {
    line-height: 130%;
    margin: 16px 0 4px;
    padding-bottom: 4px;
}

#template_body h3 {
    line-height: 130%;
    margin: 16px 0 4px;
    padding-bottom: 4px;
}

#template_body h4 {
    line-height: 110%;
    margin: 12px 0 3px;
    padding-bottom: 3px;
}

#template_body h5 {
    line-height: 110%;
    margin: 12px 0 3px;
    padding-bottom: 3px;
}

#template_body h6 {
    line-height: 100%;
    margin: 10px 0 3px;
    padding-bottom: 2px;
}

a {
    font-weight: normal;
    text-decoration: underline;
}

img {
    border: none;
    display: inline;
    font-weight: bold;
    height: auto;
    line-height: 100%;
    outline: none;
    text-decoration: none;
    text-transform: capitalize;
}

#rp_wcec_items_table {
    width: 100%;
    border-collapse: collapse;
}

#addresses {
    width: 100%;
    vertical-align: top;
}

.rp_wcec_item {
    vertical-align: middle;
    word-wrap: break-word;
}

.rp_wcec_item_thumbnail {
    margin-bottom: 5px;
}

.rp_wcec_item_thumbnail img {
    vertical-align: middle;
    margin-right: 10px;
}

.rp_wcec_order_refund_line {
    text-align: left;
    border-top-width: 4px;
}
<?php
