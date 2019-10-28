<?php

/**
 * Customer details plain text email template for custom emails
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, null);

echo "\n****************************************************\n\n";
