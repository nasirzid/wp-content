<?php

/**
 * General plain text email template for custom emails
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!empty($email_heading)) {
    echo "= " . $email_heading . " =\n\n";
}

do_action('rp_wcec_before_custom_email_content', $rp_wcec_email_id, $rp_wcec_trigger_id, $sent_to_admin, $plain_text, $style);

echo wptexturize($content) . "\n\n";

do_action('rp_wcec_after_custom_email_content', $rp_wcec_email_id, $rp_wcec_trigger_id, $sent_to_admin, $plain_text, $style);

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'));
