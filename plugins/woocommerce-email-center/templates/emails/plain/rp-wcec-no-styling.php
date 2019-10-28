<?php

/**
 * General plain text email template for custom emails with no styling
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!RightPress_Helper::is_empty($email_heading)) {
    echo $email_heading . "\n\n";
}

do_action('rp_wcec_before_custom_email_content', $rp_wcec_email_id, $rp_wcec_trigger_id, $sent_to_admin, $plain_text, $style);

echo wptexturize($content) . "\n\n";

do_action('rp_wcec_after_custom_email_content', $rp_wcec_email_id, $rp_wcec_trigger_id, $sent_to_admin, $plain_text, $style);
