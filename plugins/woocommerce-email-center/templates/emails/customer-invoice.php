<?php

/**
 * Customer invoice email
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

<?php if ($order->has_status('pending')): ?>
    <p><?php printf(__('An order has been created for you on %s. To pay for this order please use the following link: %s', 'woocommerce'), get_bloginfo('name', 'display'), '<a href="' . esc_url($order->get_checkout_payment_url()) . '">' . __('pay', 'woocommerce') . '</a>'); ?></p>
<?php endif; ?>

<?php do_action('woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email); ?>

<?php do_action('woocommerce_email_footer', $email); ?>
