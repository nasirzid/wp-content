<?php

/**
 * Customer processing order email
 * Based on WooCommerce 2.4
 * Adapted to work with WooCommerce 2.2+
 * Tested up to WooCommerce 2.5.5
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// $email variable fix
$email = isset($email) ? $email : null;

?>

<?php do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php _e('Your order has been received and is now being processed. Your order details are shown below for your reference:', 'woocommerce'); ?></p>

<?php do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_footer', $email); ?>
