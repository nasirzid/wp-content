<?php

/**
 * Email order details template
 * Based on WooCommerce 2.5
 * Tested up to WooCommerce 2.5.5
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// $email variable fix
$email = isset($email) ? $email : null;

?>

<?php do_action('woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email); ?>

<?php if (!$sent_to_admin): ?>
    <h2><?php printf(__('Order #%s', 'woocommerce'), $order->get_order_number()); ?></h2>
<?php else: ?>
    <h2><a class="link" href="<?php echo esc_url(admin_url('post.php?post=' . RightPress_WC_Legacy::order_get_id($order) . '&action=edit')); ?>"><?php printf(__('Order #%s', 'woocommerce'), $order->get_order_number()); ?></a> (<?php printf('<time datetime="%s">%s</time>', RightPress_WC_Legacy::order_get_formatted_date_created($order, 'c'), RightPress_WC_Legacy::order_get_formatted_date_created($order)); ?>)</h2>
<?php endif; ?>

<table id="rp_wcec_items_table" class="td" cellspacing="0" cellpadding="6" border="1">
    <thead>
        <tr>
            <th class="td" scope="col"><?php _e('Product', 'woocommerce'); ?></th>
            <th class="td" scope="col"><?php _e('Quantity', 'woocommerce'); ?></th>
            <th class="td" scope="col"><?php _e('Price', 'woocommerce'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        echo RightPress_WC_Legacy::get_email_order_items($order, array(
            'show_sku'      => $sent_to_admin,
            'show_image'    => false,
            'image_size'    => array(32, 32),
            'plain_text'    => $plain_text,
            'sent_to_admin' => $sent_to_admin
        ));
        ?>
    </tbody>
    <tfoot>
        <?php
            if ($totals = $order->get_order_item_totals()) {
                foreach ($totals as $total) {
                    ?><tr>
                        <th class="td" scope="row" colspan="2"><?php echo $total['label']; ?></th>
                        <td class="td"><?php echo $total['value']; ?></td>
                    </tr><?php
                }
            }
        ?>
    </tfoot>
</table>

<?php do_action('woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email); ?>
