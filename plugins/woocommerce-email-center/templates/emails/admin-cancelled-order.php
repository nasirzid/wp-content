<?php

/**
 * Admin cancelled order email
 * Based on WooCommerce 2.4
 * Adapted to work with WooCommerce 2.3+
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

<p><?php printf(__('The order #%d from %s has been cancelled. The order was as follows:', 'woocommerce'), $order->get_order_number(), $order->get_formatted_billing_full_name()); ?></p>

<?php do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_footer', $email); ?>
