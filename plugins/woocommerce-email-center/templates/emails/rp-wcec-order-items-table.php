<?php

/**
 * Order items table template for custom emails
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<?php do_action('woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, null); ?>

<h2><?php printf(__('Order #%s', 'woocommerce'), $order->get_order_number()); ?></h2>

<table id="rp_wcec_items_table" class="td" cellspacing="0" cellpadding="6" border="1">
    <thead>
        <tr>
            <th class="td" scope="col"><?php _e('Product', 'woocommerce'); ?></th>
            <th class="td" scope="col"><?php _e('Quantity', 'woocommerce'); ?></th>
            <th class="td" scope="col"><?php _e('Price', 'woocommerce'); ?></th>
        </tr>
    </thead>
    <tbody>

        <?php foreach ($order->get_items() as $item_id => $item): ?>

        <?php
            $_product   = apply_filters('woocommerce_order_item_product', RightPress_WC_Legacy::order_item_get_product($item, $order), $item);
        ?>

        <?php if (apply_filters('woocommerce_order_item_visible', true, $item)): ?>
            <tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>">
                <td class="td rp_wcec_item">
                    <?php

                        // Product name
                        echo apply_filters('woocommerce_order_item_name', RightPress_WC_Legacy::order_item_get_name($item), $item, false);

                        // SKU
                        if ($order->get_status() !== 'completed' && is_object($_product) && $_product->get_sku()) {
                            echo ' (#' . $_product->get_sku() . ')';
                        }

                        // Allow other plugins to add additional product information here
                        do_action('woocommerce_order_item_meta_start', $item_id, $item, $order);

                        // Variation
                        if ($item_meta = RightPress_WC_Legacy::wc_display_item_meta($item, $_product, true, true, array('separator' => "\n"))) {
                            echo '<br/><small>' . nl2br($item_meta) . '</small>';
                        }

                        // File URLs
                        if (!$sent_to_admin && $order->is_download_permitted() && is_object($_product) && $_product->exists() && $_product->is_downloadable()) {

                            $download_files = $order->get_item_downloads($item);
                            $i              = 0;

                            foreach ($download_files as $download_id => $file) {
                                $i++;

                                if (count($download_files) > 1) {
                                    $prefix = sprintf(__('Download %d', 'woocommerce'), $i);
                                }
                                elseif ($i == 1) {
                                    $prefix = __('Download', 'woocommerce');
                                }

                                echo '<br/><small>' . $prefix . ': <a href="' . esc_url($file['download_url']) . '" target="_blank">' . esc_html($file['name']) . '</a></small>' . "\n";
                            }
                        }

                        // Allow other plugins to add additional product information here
                        do_action('woocommerce_order_item_meta_end', $item_id, $item, $order);
                    ?>
                </td>
                <td class="td rp_wcec_item"><?php echo apply_filters('woocommerce_email_order_item_quantity', RightPress_WC_Legacy::order_item_get_quantity($item), $item); ?></td>
                <td class="td rp_wcec_item"><?php echo $order->get_formatted_line_subtotal($item); ?></td>
            </tr>
        <?php endif; ?>

        <?php if (($sent_to_admin ? false : in_array($order->get_status(), array('processing', 'completed'))) && is_object($_product) && ($purchase_note = RightPress_WC_Meta::product_get_meta($_product, '_purchase_note', true))): ?>
            <tr>
                <td class="rp_wcec_item" colspan="3"><?php echo wpautop(do_shortcode(wp_kses_post($purchase_note))); ?></td>
            </tr>
        <?php endif; ?>

        <?php endforeach; ?>

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

<?php do_action('woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, null); ?>

<?php do_action('woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, null); ?>
