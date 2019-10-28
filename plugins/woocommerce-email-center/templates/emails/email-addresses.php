<?php

/**
 * Email addresses template
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

<table id="addresses" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td class="td" valign="top" width="50%">
            <h3><?php _e('Billing address', 'woocommerce'); ?></h3>

            <p class="text"><?php echo $order->get_formatted_billing_address(); ?></p>
        </td>
        <?php if (!wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ($shipping = $order->get_formatted_shipping_address())): ?>
            <td class="td" valign="top" width="50%">
                <h3><?php _e('Shipping address', 'woocommerce'); ?></h3>

                <p class="text"><?php echo $shipping; ?></p>
            </td>
        <?php endif; ?>
    </tr>
</table>
