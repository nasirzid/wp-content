<?php

/**
 * Customer refunded order email
 * Based on WooCommerce 2.4
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

<p><?php
    if ($partial_refund) {
        printf(__('Hi there. Your order on %s has been partially refunded.', 'woocommerce'), get_option('blogname'));
    }
    else {
        printf(__('Hi there. Your order on %s has been refunded.', 'woocommerce'), get_option('blogname'));
    }
?></p>

<?php do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_footer', $email); ?>
