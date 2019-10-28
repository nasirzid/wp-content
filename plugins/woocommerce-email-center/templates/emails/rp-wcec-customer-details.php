<?php

/**
 * Customer details template for custom emails
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<?php do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, null); ?>
