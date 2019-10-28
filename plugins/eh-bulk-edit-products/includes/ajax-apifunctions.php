<?php
if (!defined('ABSPATH')) {
    exit;
}
global $hook_suffix;
add_action('wp_ajax_eh_bep_get_attributes_action', 'eh_bep_get_attributes_action_callback');
add_action('wp_ajax_eh_bep_all_products', 'eh_bep_list_table_all_callback');
add_action('wp_ajax_eh_bep_count_products', 'eh_bep_count_products_callback');
add_action('wp_ajax_eh_bep_clear_products', 'eh_clear_all_callback');
add_action('wp_ajax_eh_bep_update_products', 'eh_bep_update_product_callback');
add_action('wp_ajax_eh_bep_filter_products', 'eh_bep_search_filter_callback');
add_action('wp_ajax_eh_bep_undo_html', 'eh_bep_undo_html_maker');
add_action('wp_ajax_eh_bulk_edit_display_count', 'eh_bulk_edit_display_count_callback');
add_action('wp_ajax_eh_bep_undo_update', 'eh_bep_undo_update_callback');

function eh_bulk_edit_display_count_callback() {
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    $value = sanitize_text_field($_POST['row_count']);
    update_option('eh_bulk_edit_table_row', $value);
    die('success');
}

function eh_bep_count_products_callback() {
    $filtered_products = xa_bep_get_selected_products();
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    die(json_encode($filtered_products));
}

function eh_bep_get_attributes_action_callback() {
    $attribute_name = $_POST['attrib'];
    $cat_args = array(
        'hide_empty' => false,
        'order' => 'ASC'
    );
    $attributes = wc_get_attribute_taxonomies();
    foreach ($attributes as $key => $value) {
        if ($attribute_name == $value->attribute_name) {
            $attribute_name = $value->attribute_name;
            $attribute_label = $value->attribute_label;
        }
    }
    $attribute_value = get_terms('pa_' . $attribute_name, $cat_args);
    if (isset($_POST['attr_and'])) {
        $return = "<optgroup label='" . $attribute_label . "' id='grp_and_" . $attribute_name . "'>";
    } else {
        $return = "<optgroup label='" . $attribute_label . "' id='grp_" . $attribute_name . "'>";
    }
    foreach ($attribute_value as $key => $value) {
        $return .= "<option value=\"'pa_" . $attribute_name . ":" . $value->slug . "'\">" . $value->name . "</option>";
    }
    $return .= "</optgroup>";
    echo $return;
    exit;
}

function eh_bep_undo_update_callback() {
    set_time_limit(300);
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    $product_count = 0;
    $variation_count = 0;

    $undo_product_id = get_option('eh_bulk_edit_undo_product_id', array());
    $undo_variation_id = get_option('eh_bulk_edit_undo_variation_id', array());
    $undo_edit_data = get_option('eh_bulk_edit_undo_edit_data', array());
    $product_chunk = array_chunk($undo_product_id, 100);
    $undo_fields = ($_POST['undo_values'] != '') ? explode(',', $_POST['undo_values']) : '';
    $product_data = $product_chunk[$_POST['index']];
    $variation_data = $undo_variation_id;
    foreach ($product_data as $pid => $current_product) {
        $product = wc_get_product($current_product['id']);
        if (!empty($product) && $product->is_type('variation')) {
            $parent_id = (WC()->version < '2.7.0') ? $product->parent->id : $product->get_parent_id();
            $product = wc_get_product($current_product['id']);
        }
        apply_filters('http_request_timeout', 30);
        if (eh_bep_in_array_fields_check('title', $undo_fields) && isset($current_product['title'])) {
            $my_post = array(
                'ID' => $current_product['id'],
                'post_title' => $current_product['title']
            );
            wp_update_post($my_post);
        }
        if (eh_bep_in_array_fields_check('sku', $undo_fields) && isset($current_product['sku'])) {
            eh_bep_update_meta_fn($current_product['id'], '_sku', $current_product['sku']);
        }
        if (eh_bep_in_array_fields_check('catalog', $undo_fields) && isset($current_product['catalog'])) {
            if (WC()->version < '3.0.0') {
                eh_bep_update_meta_fn($current_product['id'], '_visibility', $current_product['catalog']);
            } else {
                $options = array_keys(wc_get_product_visibility_options());
                if (in_array($current_product['catalog'], $options, true)) {
                    $product->set_catalog_visibility(wc_clean($current_product['catalog']));
                    $product->save();
                }
            }
        }
        if (eh_bep_in_array_fields_check('shipping', $undo_fields) && isset($current_product['shipping'])) {
            wp_set_object_terms((int) $current_product['id'], (int) $current_product['shipping'], 'product_shipping_class');
        }
        if (eh_bep_in_array_fields_check('sale', $undo_fields) && isset($current_product['sale'])) {
            $undo_sale_val = $current_product['sale'];
            if ($current_product['sale'] == 0) {
                $undo_sale_val = '';
            }
            eh_bep_update_meta_fn($current_product['id'], '_sale_price', $undo_sale_val);
        }
        if (eh_bep_in_array_fields_check('regular', $undo_fields) && isset($current_product['regular'])) {
            eh_bep_update_meta_fn($current_product['id'], '_regular_price', $current_product['regular']);
        }
        if (get_post_meta($current_product['id'], '_sale_price', true) !== '' && get_post_meta($current_product['id'], '_regular_price', true) !== '') {
            eh_bep_update_meta_fn($current_product['id'], '_price', get_post_meta($current_product['id'], '_sale_price', true));
        } elseif (get_post_meta($current_product['id'], '_sale_price', true) === '' && get_post_meta($current_product['id'], '_regular_price', true) !== '') {
            eh_bep_update_meta_fn($current_product['id'], '_price', get_post_meta($current_product['id'], '_regular_price', true));
        } elseif (get_post_meta($current_product['id'], '_sale_price', true) !== '' && get_post_meta($current_product['id'], '_regular_price', true) === '') {
            eh_bep_update_meta_fn($current_product['id'], '_price', get_post_meta($current_product['id'], '_sale_price', true));
        } elseif (get_post_meta($current_product['id'], '_sale_price', true) === '' && get_post_meta($current_product['id'], '_regular_price', true) === '') {
            eh_bep_update_meta_fn($current_product['id'], '_price', '');
        }
        if (eh_bep_in_array_fields_check('manage_stock', $undo_fields) && isset($current_product['stock_manage'])) {
            eh_bep_update_meta_fn($current_product['id'], '_manage_stock', $current_product['stock_manage']);
        }
        if (eh_bep_in_array_fields_check('quantity', $undo_fields) && isset($current_product['stock_quantity'])) {
            eh_bep_update_meta_fn($current_product['id'], '_stock', $current_product['stock_quantity']);
        }
        if (eh_bep_in_array_fields_check('backorders', $undo_fields) && isset($current_product['backorder'])) {
            eh_bep_update_meta_fn($current_product['id'], '_backorders', $current_product['backorder']);
        }
        if (eh_bep_in_array_fields_check('stock_status', $undo_fields) && isset($current_product['stock_status'])) {
            eh_bep_update_meta_fn($current_product['id'], '_stock_status', $current_product['stock_status']);
        }
        if (eh_bep_in_array_fields_check('length', $undo_fields) && isset($current_product['length'])) {
            eh_bep_update_meta_fn($current_product['id'], '_length', $current_product['length']);
        }
        if (eh_bep_in_array_fields_check('width', $undo_fields) && isset($current_product['width'])) {
            eh_bep_update_meta_fn($current_product['id'], '_width', $current_product['width']);
        }
        if (eh_bep_in_array_fields_check('height', $undo_fields) && isset($current_product['height'])) {
            eh_bep_update_meta_fn($current_product['id'], '_height', $current_product['height']);
        }
        if (eh_bep_in_array_fields_check('weight', $undo_fields) && isset($current_product['weight'])) {
            eh_bep_update_meta_fn($current_product['id'], '_weight', $current_product['weight']);
        }
        if (eh_bep_in_array_fields_check('aus_hs_tariff', $undo_fields)) {
            if (isset($current_product['aus_hs_tariff'])) {
                eh_bep_update_meta_fn($current_product['id'], '_wf_tariff_code', $current_product['aus_hs_tariff']);
            }
        }
        if (eh_bep_in_array_fields_check('aus_origin_country', $undo_fields)) {
            if (isset($current_product['aus_origin_country'])) {
                eh_bep_update_meta_fn($current_product['id'], '_wf_country_of_origin', $current_product['aus_origin_country']);
            }
        }
        if (eh_bep_in_array_fields_check('hide_price', $undo_fields) && isset($current_product['product_adjustment_hide_price_unregistered'])) {
            update_post_meta($current_product['id'], 'product_adjustment_hide_price_unregistered', $current_product['product_adjustment_hide_price_unregistered']);
        }
        if (eh_bep_in_array_fields_check('hide_price_role', $undo_fields) && isset($current_product['eh_pricing_adjustment_product_price_user_role'])) {
            eh_bep_update_meta_fn($current_product['id'], 'eh_pricing_adjustment_product_price_user_role', $current_product['eh_pricing_adjustment_product_price_user_role']);
        }
        if (eh_bep_in_array_fields_check('price_adjustment', $undo_fields) && isset($current_product['product_based_price_adjustment'])) {
            eh_bep_update_meta_fn($current_product['id'], 'product_based_price_adjustment', $current_product['product_based_price_adjustment']);
        }
        if (eh_bep_in_array_fields_check('wf_shipping_unit', $undo_fields) && isset($current_product['wf_shipping_unit'])) {
            eh_bep_update_meta_fn($current_product['id'], '_wf_shipping_unit', $current_product['wf_shipping_unit']);
        }

        if (eh_bep_in_array_fields_check('attributes', $undo_fields) && isset($current_product['attributes'])) {
            $_product_attributes = get_post_meta($current_product['id'], '_product_attributes', TRUE);

            foreach ($_product_attributes as $key => $val) {
                $_product_attributes[$key]['value'] = wc_get_product_terms($current_product['id'], $key);
            }
            foreach ($_product_attributes as $key2 => $val2) {
                foreach ($_product_attributes[$key]['value'] as $k => $v) {
                    wp_remove_object_terms($current_product['id'], $v, $key2);
                }
            }

            $is_vari = 0;
            $i = 0;
            foreach ($current_product['attributes'] as $attr_name => $attr_details) {
                $is_vari = $current_product['attributes'][$attr_name]['is_variation'];
                foreach ($current_product['attributes'][$attr_name]['value'] as $att_ind => $attr_value) {
                    wp_set_object_terms($current_product['id'], $attr_value, $attr_name, true);

                    $thedata = Array($attr_name => Array(
                            'name' => $attr_name,
                            'value' => $attr_value,
                            'is_visible' => '1',
                            'is_taxonomy' => '1',
                            'is_variation' => $is_vari
                    ));
                    if ($i == 0) {
                        update_post_meta($current_product['id'], '_product_attributes', $thedata);
                        $i++;
                    } else {
                        $_product_attr = get_post_meta($current_product['id'], '_product_attributes', TRUE);
                        if (!empty($_product_attr)) {
                            update_post_meta($current_product['id'], '_product_attributes', array_merge($_product_attr, $thedata));
                        } else {
                            update_post_meta($current_product['id'], '_product_attributes', $thedata);
                        }
                    }
                }
            }
        }
        $product_count++;
        wc_delete_product_transients($current_product['id']);
    }
    for ($i = 0; $i < count($variation_data); $i++) {
        $main_keys = array_keys($variation_data);
        $key_now = $main_keys[$i];
        for ($v = 0; $v < count($variation_data[$key_now]); $v++) {
            $current_variation = $variation_data[$key_now][$v];
            apply_filters('http_request_timeout', 30);
            if (eh_bep_in_array_fields_check('sku', $undo_fields) && isset($current_variation['sku'])) {
                eh_bep_update_meta_fn($current_variation['id'], '_sku', $current_variation['sku']);
            }
            if (eh_bep_in_array_fields_check('shipping', $undo_fields) && isset($current_variation['shipping'])) {
                wp_set_object_terms((int) $current_variation['id'], (int) $current_variation['shipping'], 'product_shipping_class');
            }
            if (eh_bep_in_array_fields_check('sale', $undo_fields) && isset($current_variation['sale'])) {
                eh_bep_update_meta_fn($current_variation['id'], '_sale_price', $current_variation['sale']);
            }
            if (eh_bep_in_array_fields_check('regular', $undo_fields) && isset($current_variation['regular'])) {
                eh_bep_update_meta_fn($current_variation['id'], '_regular_price', $current_variation['regular']);
            }
            if (get_post_meta($current_variation['id'], '_sale_price', true) !== '' && get_post_meta($current_variation['id'], '_regular_price', true) !== '') {
                eh_bep_update_meta_fn($current_variation['id'], '_price', get_post_meta($current_variation['id'], '_sale_price', true));
            } elseif (get_post_meta($current_variation['id'], '_sale_price', true) === '' && get_post_meta($current_variation['id'], '_regular_price', true) !== '') {
                eh_bep_update_meta_fn($current_variation['id'], '_price', get_post_meta($current_variation['id'], '_regular_price', true));
            } elseif (get_post_meta($current_variation['id'], '_sale_price', true) !== '' && get_post_meta($current_variation['id'], '_regular_price', true) === '') {
                eh_bep_update_meta_fn($current_variation['id'], '_price', get_post_meta($current_variation['id'], '_sale_price', true));
            } elseif (get_post_meta($current_variation['id'], '_sale_price', true) === '' && get_post_meta($current_variation['id'], '_regular_price', true) === '') {
                eh_bep_update_meta_fn($current_variation['id'], '_price', '');
            }
            if (eh_bep_in_array_fields_check('manage_stock', $undo_fields) && isset($current_variation['stock_manage'])) {
                eh_bep_update_meta_fn($current_variation['id'], '_manage_stock', $current_variation['stock_manage']);
            }
            if (eh_bep_in_array_fields_check('quantity', $undo_fields) && isset($current_variation['stock_quantity'])) {
                eh_bep_update_meta_fn($current_variation['id'], '_stock', $current_variation['stock_quantity']);
            }
            if (eh_bep_in_array_fields_check('backorders', $undo_fields) && isset($current_variation['backorder'])) {
                eh_bep_update_meta_fn($current_variation['id'], '_backorders', $current_variation['backorder']);
            }
            if (eh_bep_in_array_fields_check('stock_status', $undo_fields) && isset($current_variation['stock_status'])) {
                eh_bep_update_meta_fn($current_variation['id'], '_stock_status', $current_variation['stock_status']);
            }
            if (eh_bep_in_array_fields_check('length', $undo_fields) && isset($current_variation['length'])) {
                eh_bep_update_meta_fn($current_variation['id'], '_length', $current_variation['length']);
            }
            if (eh_bep_in_array_fields_check('width', $undo_fields) && isset($current_variation['width'])) {
                eh_bep_update_meta_fn($current_variation['id'], '_width', $current_variation['width']);
            }
            if (eh_bep_in_array_fields_check('height', $undo_fields) && isset($current_variation['height'])) {
                eh_bep_update_meta_fn($current_variation['id'], '_height', $current_variation['height']);
            }
            if (eh_bep_in_array_fields_check('weight', $undo_fields) && isset($current_variation['weight'])) {
                eh_bep_update_meta_fn($current_variation['id'], '_weight', $current_variation['weight']);
            }
            $variation_count++;
        }
        WC_Product_Variable::sync($key_now);
        wc_delete_product_transients($key_now);
    }
    if (count($product_chunk) - 1 == $_POST['index']) {
        delete_option('eh_bulk_edit_undo_product_id');
        delete_option('eh_bulk_edit_undo_variation_id');
        delete_option('eh_bulk_edit_undo_edit_data');
        die('done');
    }
    die(json_encode(count($undo_product_id)));
}

function eh_bep_in_array_fields_check($key, $array) {
    if (empty($array)) {
        return;
    }
    if (in_array($key, $array)) {
        return true;
    } else {
        return false;
    }
}

//custom rounding

function eh_bep_round_ceiling($number, $significance = 1) {
    return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number / $significance) * $significance) : false;
}

function eh_bep_update_product_callback() {
    set_time_limit(300);
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    $selected_products = $_POST['pid'];
    $undo_product_data = array();
    $undo_variation_data = array();
    $product_data = array();
    $variation_data = array();
    $edit_data = array();
    $undo_update = $edit_data['undo_update'] = $_POST['undo_update_op'];
    $title_select = $edit_data['title_select'] = $_POST['title_select'];
    $sku_select = $edit_data['sku_select'] = $_POST['sku_select'];
    $catalog_select = $edit_data['catalog_select'] = $_POST['catalog_select'];
    $shipping_select = $edit_data['shipping_select'] = $_POST['shipping_select'];
    $sale_select = $edit_data['sale_select'] = $_POST['sale_select'];
    $sale_round_select = $edit_data['sale_round_select'] = $_POST['sale_round_select'];
    $regular_select = $edit_data['regular_select'] = $_POST['regular_select'];
    $regular_round_select = $edit_data['regular_round_select'] = $_POST['regular_round_select'];
    $stock_manage_select = $edit_data['stock_manage_select'] = $_POST['stock_manage_select'];
    $quantity_select = $edit_data['quantity_select'] = $_POST['quantity_select'];
    $backorder_select = $edit_data['backorder_select'] = $_POST['backorder_select'];
    $stock_status_select = $edit_data['stock_status_select'] = $_POST['stock_status_select'];
    $attribute_action = $edit_data['attribute_action'] = $_POST['attribute_action'];

    $length_select = $edit_data['length_select'] = $_POST['length_select'];
    $width_select = $edit_data['width_select'] = $_POST['width_select'];
    $height_select = $edit_data['height_select'] = $_POST['height_select'];
    $weight_select = $edit_data['weight_select'] = $_POST['weight_select'];
    $title_text = $edit_data['title_text'] = $_POST['title_text'];
    $replace_title_text = $edit_data['replace_title_text'] = sanitize_text_field($_POST['replace_title_text']);
    $regex_replace_title_text = $edit_data['regex_replace_title_text'] = sanitize_text_field($_POST['regex_replace_title_text']);
    $sku_text = $edit_data['sku_text'] = $_POST['sku_text'];
    $sku_replace_text = $edit_data['sku_replace_text'] = sanitize_text_field($_POST['sku_replace_text']);
    $regex_sku_replace_text = $edit_data['regex_sku_replace_text'] = sanitize_text_field($_POST['regex_sku_replace_text']);
    $sale_text = $edit_data['sale_text'] = $_POST['sale_text'];
    $sale_round_text = $edit_data['sale_round_text'] = isset($_POST['sale_round_text']) ? $_POST['sale_round_text'] : '';
    $regular_text = $edit_data['regular_text'] = $_POST['regular_text'];
    $regular_round_text = $edit_data['regular_round_text'] = isset($_POST['regular_round_text']) ? $_POST['regular_round_text'] : '';
    $quantity_text = $edit_data['quantity_text'] = $_POST['quantity_text'];
    $length_text = $edit_data['length_text'] = $_POST['length_text'];
    $width_text = $edit_data['width_text'] = $_POST['width_text'];
    $height_text = $edit_data['height_text'] = $_POST['height_text'];
    $weight_text = $edit_data['weight_text'] = $_POST['weight_text'];
    $hide_price = $edit_data['hide_price'] = $_POST['hide_price'];
    $hide_price_role = $edit_data['hide_price_role'] = ($_POST['hide_price_role'] != '') ? $_POST['hide_price_role'] : '';
    $price_adjustment = $edit_data['price_adjustment'] = $_POST['price_adjustment'];
    $shipping_unit = $edit_data['shipping_unit'] = sanitize_text_field($_POST['shipping_unit']);
    $shipping_unit_select = $edit_data['shipping_unit_select'] = $_POST['shipping_unit_select'];
    $sale_warning = array();
    foreach ($selected_products as $pid => $temp) {
        $pid = $temp;
        $collect_product_data = array();
        apply_filters('http_request_timeout', 30);
        switch ($hide_price) {
            case 'yes':
                $collect_product_data['product_adjustment_hide_price_unregistered'] = get_post_meta($pid, 'product_adjustment_hide_price_unregistered', true);
                eh_bep_update_meta_fn($pid, 'product_adjustment_hide_price_unregistered', 'yes');
                break;
            case 'no':
                $collect_product_data['product_adjustment_hide_price_unregistered'] = get_post_meta($pid, 'product_adjustment_hide_price_unregistered', true);
                eh_bep_update_meta_fn($pid, 'product_adjustment_hide_price_unregistered', 'no');
                break;
        }
        switch ($price_adjustment) {
            case 'yes':
                $collect_product_data['product_based_price_adjustment'] = get_post_meta($pid, 'product_based_price_adjustment', true);
                eh_bep_update_meta_fn($pid, 'product_based_price_adjustment', 'yes');
                break;
            case 'no':
                $collect_product_data['product_based_price_adjustment'] = get_post_meta($pid, 'product_based_price_adjustment', true);
                eh_bep_update_meta_fn($pid, 'product_based_price_adjustment', 'no');
                break;
        }
        if ($hide_price_role != '') {
            $collect_product_data['eh_pricing_adjustment_product_price_user_role'] = get_post_meta($pid, 'eh_pricing_adjustment_product_price_user_role', true);
            eh_bep_update_meta_fn($pid, 'eh_pricing_adjustment_product_price_user_role', $hide_price_role);
        }
        switch ($shipping_unit_select) {
            case "add":
                $unit = get_post_meta($pid, '_wf_shipping_unit', true);
                $collect_product_data['wf_shipping_unit'] = $unit;
                $unit_val = number_format($unit + $shipping_unit, 6, '.', '');
                eh_bep_update_meta_fn($pid, '_wf_shipping_unit', $unit_val);
                break;
            case "sub":
                $unit = get_post_meta($pid, '_wf_shipping_unit', true);
                $collect_product_data['wf_shipping_unit'] = $unit;
                $unit_val = number_format($unit - $shipping_unit, 6, '.', '');
                eh_bep_update_meta_fn($pid, '_wf_shipping_unit', $unit_val);
                break;
            case "replace":
                $unit = get_post_meta($pid, '_wf_shipping_unit', true);
                $collect_product_data['wf_shipping_unit'] = $unit;
                eh_bep_update_meta_fn($pid, '_wf_shipping_unit', $shipping_unit);
                break;
            default:
                break;
        }
        $temp = wc_get_product($pid);
        $parent = $temp;
        $parent_id = $pid;
        if (!empty($temp) && $temp->is_type('variation')) {
            $parent_id = (WC()->version < '2.7.0') ? $temp->parent->id : $temp->get_parent_id();
            $parent = wc_get_product($parent_id);
        }
        if (isset($_POST['aus_post_hs_tariff']) && $_POST['aus_post_hs_tariff'] != '' && !$temp->is_type('variation')) {
            $edit_data['aus_hs_tariff'] = $_POST['aus_post_hs_tariff'];
            $collect_product_data['aus_hs_tariff'] = get_post_meta($parent_id, '_wf_tariff_code', true);
            eh_bep_update_meta_fn($parent_id, '_wf_tariff_code', $_POST['aus_post_hs_tariff']);
        }
        if (isset($_POST['aus_post_origin_country']) && $_POST['aus_post_origin_country'] != '' && !$temp->is_type('variation')) {
            $edit_data['aus_origin_country'] = $_POST['aus_post_origin_country'];
            $collect_product_data['aus_origin_country'] = get_post_meta($parent_id, '_wf_country_of_origin', true);
            eh_bep_update_meta_fn($parent_id, '_wf_country_of_origin', $_POST['aus_post_origin_country']);
        }
        $temp_type = (WC()->version < '2.7.0') ? $temp->product_type : $temp->get_type();
        $temp_title = (WC()->version < '2.7.0') ? $temp->post->post_title : $temp->get_title();
        if ($temp_type == 'simple' || $temp_type == 'variation' || $temp_type == 'variable') {
            $product_data = array();
            $product_data['type'] = 'simple';
            $product_data['title'] = $temp_title;
            $product_data['sku'] = get_post_meta($pid, '_sku', true);
            $product_data['catalog'] = (WC()->version < '3.0.0') ? get_post_meta($pid, '_visibility', true) : $temp->get_catalog_visibility();
            $ship_args = array('fields' => 'ids');
            $product_data['shipping'] = current(wp_get_object_terms($pid, 'product_shipping_class', $ship_args));
            $product_data['sale'] = (float) get_post_meta($pid, '_sale_price', true);
            $product_data['regular'] = (float) get_post_meta($pid, '_regular_price', true);
            $product_data['stock_manage'] = get_post_meta($pid, '_manage_stock', true);
            $product_data['stock_quantity'] = (float) get_post_meta($pid, '_stock', true);
            $product_data['backorder'] = get_post_meta($pid, '_backorders', true);
            $product_data['stock_status'] = get_post_meta($pid, '_stock_status', true);
            $product_data['length'] = (float) get_post_meta($pid, '_length', true);
            $product_data['width'] = (float) get_post_meta($pid, '_width', true);
            $product_data['height'] = (float) get_post_meta($pid, '_height', true);
            $product_data['weight'] = (float) get_post_meta($pid, '_weight', true);
            $collect_product_data['id'] = $pid;
            $collect_product_data['type'] = $product_data['type'];
            switch ($title_select) {
                case 'set_new':
                    $my_post = array(
                        'ID' => $pid,
                        'post_title' => $title_text
                    );
                    $collect_product_data['title'] = $product_data['title'];
                    wp_update_post($my_post);
                    break;
                case 'append':
                    $my_post = array(
                        'ID' => $pid,
                        'post_title' => $product_data['title'] . $title_text
                    );
                    $collect_product_data['title'] = $product_data['title'];
                    wp_update_post($my_post);
                    break;
                case 'prepand':
                    $my_post = array(
                        'ID' => $pid,
                        'post_title' => $title_text . $product_data['title']
                    );
                    $collect_product_data['title'] = $product_data['title'];
                    wp_update_post($my_post);
                    break;
                case 'replace':
                    $my_post = array(
                        'ID' => $pid,
                        'post_title' => str_replace($replace_title_text, $title_text, $product_data['title'])
                    );
                    $collect_product_data['title'] = $product_data['title'];
                    wp_update_post($my_post);
                    break;
                case 'regex_replace':
                    if (@preg_replace('/' . $regex_replace_title_text . '/', $title_text, $product_data['title']) != false) {
                        $regex_flags = '';
                        if (!empty($_REQUEST['regex_flag_sele_title'])) {
                            foreach ($_REQUEST['regex_flag_sele_title'] as $reg_val) {
                                $regex_flags .= $reg_val;
                            }
                        }
                        $my_post = array(
                            'ID' => $pid,
                            'post_title' => preg_replace('/' . $regex_replace_title_text . '/' . $regex_flags, $title_text, $product_data['title'])
                        );
                        $collect_product_data['title'] = $product_data['title'];
                        wp_update_post($my_post);
                    }
                    break;
            }
            switch ($sku_select) {
                case 'set_new':
                    $collect_product_data['sku'] = $product_data['sku'];
                    eh_bep_update_meta_fn($pid, '_sku', $sku_text);
                    break;
                case 'append':
                    $collect_product_data['sku'] = $product_data['sku'];
                    $sku_val = $product_data['sku'] . $sku_text;
                    eh_bep_update_meta_fn($pid, '_sku', $sku_val);
                    break;
                case 'prepand':
                    $collect_product_data['sku'] = $product_data['sku'];
                    $sku_val = $sku_text . $product_data['sku'];
                    eh_bep_update_meta_fn($pid, '_sku', $sku_val);
                    break;
                case 'replace':
                    $collect_product_data['sku'] = $product_data['sku'];
                    $sku_val = str_replace($sku_replace_text, $sku_text, $product_data['sku']);
                    eh_bep_update_meta_fn($pid, '_sku', $sku_val);
                    break;
                case 'regex_replace':
                    if (@preg_replace('/' . $regex_sku_replace_text . '/', $sku_text, $product_data['sku']) != false) {
                        $regex_flags = '';
                        if (!empty($_REQUEST['regex_flag_sele_sku'])) {
                            foreach ($_REQUEST['regex_flag_sele_sku'] as $reg_val) {
                                $regex_flags .= $reg_val;
                            }
                        }
                        $sku_val = preg_replace('/' . $regex_sku_replace_text . '/' . $regex_flags, $sku_text, $product_data['sku']);
                        eh_bep_update_meta_fn($pid, '_sku', $sku_val);
                        $collect_product_data['sku'] = $product_data['sku'];
                    }
                    break;
            }
            if ($temp_type != 'variation') {
                $collect_product_data['catalog'] = $product_data['catalog'];
                if (WC()->version < '3.0.0') {
                    eh_bep_update_meta_fn($pid, '_visibility', $catalog_select);
                } else {
                    $options = array_keys(wc_get_product_visibility_options());
                    $catalog_select = wc_clean($catalog_select);
                    if (in_array($catalog_select, $options, true)) {
                        $parent->set_catalog_visibility($catalog_select);
                        $parent->save();
                    }
                }
            }

            if ($shipping_select != '') {
                $collect_product_data['shipping'] = $product_data['shipping'];
                wp_set_object_terms((int) $pid, (int) $shipping_select, 'product_shipping_class');
            }

            switch ($regular_select) {
                case 'up_percentage':
                    if ($product_data['regular'] !== '') {
                        $collect_product_data['regular'] = $product_data['regular'];
                        $per_val = $product_data['regular'] * ($regular_text / 100);
                        $cal_val = $product_data['regular'] + $per_val;
                        if ($regular_round_select != "" && $regular_round_text != "") {
                            $got_regular = $cal_val;
                            switch ($regular_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_regular, $regular_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_regular, -$regular_round_text);
                                    break;
                            }
                        }
                        $regular_val = wc_format_decimal($cal_val, "");

                        $sal_val = get_post_meta($pid, '_sale_price', true);
                        if ($temp_type != 'variable' && $sal_val < $regular_val) {
                            eh_bep_update_meta_fn($pid, '_regular_price', $regular_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Regular');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'down_percentage':
                    if ($product_data['regular'] !== '') {
                        $collect_product_data['regular'] = $product_data['regular'];
                        $per_val = $product_data['regular'] * ($regular_text / 100);
                        $cal_val = $product_data['regular'] - $per_val;
                        if ($regular_round_select != "" && $regular_round_text != "") {
                            $got_regular = $cal_val;
                            switch ($regular_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_regular, $regular_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_regular, -$regular_round_text);
                                    break;
                            }
                        }
                        $regular_val = wc_format_decimal($cal_val, "");

                        $sal_val = get_post_meta($pid, '_sale_price', true);
                        if ($temp_type != 'variable' && $sal_val < $regular_val) {
                            eh_bep_update_meta_fn($pid, '_regular_price', $regular_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Regular');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'up_price':
                    if ($product_data['regular'] !== '') {
                        $collect_product_data['regular'] = $product_data['regular'];
                        $cal_val = $product_data['regular'] + $regular_text;
                        if ($regular_round_select != "" && $regular_round_text != "") {
                            $got_regular = $cal_val;
                            switch ($regular_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_regular, $regular_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_regular, -$regular_round_text);
                                    break;
                            }
                        }
                        $regular_val = wc_format_decimal($cal_val, "");

                        $sal_val = get_post_meta($pid, '_sale_price', true);
                        if ($temp_type != 'variable' && $sal_val < $regular_val) {
                            eh_bep_update_meta_fn($pid, '_regular_price', $regular_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Regular');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'down_price':
                    if ($product_data['regular'] !== '') {
                        $collect_product_data['regular'] = $product_data['regular'];
                        $cal_val = $product_data['regular'] - $regular_text;
                        if ($regular_round_select != "" && $regular_round_text != "") {
                            $got_regular = $cal_val;
                            switch ($regular_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_regular, $regular_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_regular, -$regular_round_text);
                                    break;
                            }
                        }
                        $regular_val = wc_format_decimal($cal_val, "");
                        $sal_val = get_post_meta($pid, '_sale_price', true);
                        if ($temp_type != 'variable' && $sal_val < $regular_val) {
                            eh_bep_update_meta_fn($pid, '_regular_price', $regular_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Regular');
                            array_push($sale_warning, $temp_type);
                        }
                    }
                    break;
                case 'flat_all':
                    $collect_product_data['regular'] = $product_data['regular'];
                    $regular_val = wc_format_decimal($regular_text, "");
                    $sal_val = get_post_meta($pid, '_sale_price', true);
                    if ($temp_type != 'variable' && $sal_val < $regular_val) {
                        eh_bep_update_meta_fn($pid, '_regular_price', $regular_val);
                    } else {
                        array_push($sale_warning, $pid, $parent_id);
                        array_push($sale_warning, 'Regular');
                        array_push($sale_warning, $temp_type);
                    }
                    break;
            }
            switch ($sale_select) {
                case 'up_percentage':
                    if ($product_data['sale'] !== '') {
                        $collect_product_data['sale'] = $product_data['sale'];
                        $per_val = $product_data['sale'] * ($sale_text / 100);
                        $cal_val = $product_data['sale'] + $per_val;
                        if ($sale_round_select != "" && $sale_round_text != "") {
                            $got_sale = $cal_val;
                            switch ($sale_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_sale, $sale_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_sale, -$sale_round_text);
                                    break;
                            }
                        }
                        $sale_val = wc_format_decimal($cal_val, "");
                        //leave sale price blank if sale price increased by -100%
                        if ($sale_val == 0) {
                            $sale_val = '';
                        }
                        $reg_val = get_post_meta($pid, '_regular_price', true);
                        if ($temp_type != 'variable' && $sale_val < $reg_val) {
                            eh_bep_update_meta_fn($pid, '_sale_price', $sale_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Sales');
                            array_push($sale_warning, $temp_type);
                            if (isset($_POST['regular_select'])) {
                                eh_bep_update_meta_fn($pid, '_regular_price', $product_data['regular']);
                            }
                        }
                    }
                    break;
                case 'down_percentage':
                    if ($product_data['sale'] !== '') {
                        $collect_product_data['sale'] = $product_data['sale'];
                        $per_val = $product_data['sale'] * ($sale_text / 100);
                        $cal_val = $product_data['sale'] - $per_val;
                        if ($sale_round_select != "" && $sale_round_text != "") {
                            $got_sale = $cal_val;
                            switch ($sale_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_sale, $sale_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_sale, -$sale_round_text);
                                    break;
                            }
                        }
                        $sale_val = wc_format_decimal($cal_val, "");
                        //leave sale price blank if sale price decreased by 100%
                        if ($sale_val == 0) {
                            $sale_val = '';
                        }
                        $reg_val = get_post_meta($pid, '_regular_price', true);
                        if ($temp_type != 'variable' && $sale_val < $reg_val) {
                            eh_bep_update_meta_fn($pid, '_sale_price', $sale_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Sales');
                            array_push($sale_warning, $temp_type);
                            if (isset($_POST['regular_select'])) {
                                eh_bep_update_meta_fn($pid, '_regular_price', $product_data['regular']);
                            }
                        }
                    }
                    break;
                case 'up_price':
                    if ($product_data['sale'] !== '') {
                        $collect_product_data['sale'] = $product_data['sale'];
                        $cal_val = $product_data['sale'] + $sale_text;
                        if ($sale_round_select != "" && $sale_round_text != "") {
                            $got_sale = $cal_val;
                            switch ($sale_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_sale, $sale_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_sale, -$sale_round_text);
                                    break;
                            }
                        }
                        $sale_val = wc_format_decimal($cal_val, "");
                        $reg_val = get_post_meta($pid, '_regular_price', true);
                        if ($temp_type != 'variable' && $sale_val < $reg_val) {
                            eh_bep_update_meta_fn($pid, '_sale_price', $sale_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Sales');
                            array_push($sale_warning, $temp_type);
                            if (isset($_POST['regular_select'])) {
                                eh_bep_update_meta_fn($pid, '_regular_price', $product_data['regular']);
                            }
                        }
                    }
                    break;
                case 'down_price':
                    if ($product_data['sale'] !== '') {
                        $collect_product_data['sale'] = $product_data['sale'];
                        $cal_val = $product_data['sale'] - $sale_text;
                        if ($sale_round_select != "" && $sale_round_text != "") {
                            $got_sale = $cal_val;
                            switch ($sale_round_select) {
                                case 'up':
                                    $cal_val = eh_bep_round_ceiling($got_sale, $sale_round_text);
                                    break;
                                case 'down':
                                    $cal_val = eh_bep_round_ceiling($got_sale, -$sale_round_text);
                                    break;
                            }
                        }
                        $sale_val = wc_format_decimal($cal_val, "");
                        $reg_val = get_post_meta($pid, '_regular_price', true);
                        if ($temp_type != 'variable' && $sale_val < $reg_val) {
                            eh_bep_update_meta_fn($pid, '_sale_price', $sale_val);
                        } else {
                            array_push($sale_warning, $pid, $parent_id);
                            array_push($sale_warning, 'Sales');
                            array_push($sale_warning, $temp_type);
                            if (isset($_POST['regular_select'])) {
                                eh_bep_update_meta_fn($pid, '_regular_price', $product_data['regular']);
                            }
                        }
                    }
                    break;
                case 'flat_all':
                    $collect_product_data['sale'] = $product_data['sale'];
                    $sale_val = wc_format_decimal($sale_text, "");
                    $reg_val = get_post_meta($pid, '_regular_price', true);
                    if ($temp_type != 'variable' && $sale_val < $reg_val) {
                        eh_bep_update_meta_fn($pid, '_sale_price', $sale_val);
                    } else {
                        array_push($sale_warning, $pid, $parent_id);
                        array_push($sale_warning, 'Sales');
                        array_push($sale_warning, $temp_type);
                        if (isset($_POST['regular_select'])) {
                            eh_bep_update_meta_fn($pid, '_regular_price', $product_data['regular']);
                        }
                    }
                    break;
            }
            if (get_post_meta($pid, '_sale_price', true) !== '' && get_post_meta($pid, '_regular_price', true) !== '') {
                eh_bep_update_meta_fn($pid, '_price', get_post_meta($pid, '_sale_price', true));
            } elseif (get_post_meta($pid, '_sale_price', true) === '' && get_post_meta($pid, '_regular_price', true) !== '') {
                eh_bep_update_meta_fn($pid, '_price', get_post_meta($pid, '_regular_price', true));
            } elseif (get_post_meta($pid, '_sale_price', true) !== '' && get_post_meta($pid, '_regular_price', true) === '') {
                eh_bep_update_meta_fn($pid, '_price', get_post_meta($pid, '_sale_price', true));
            } elseif (get_post_meta($pid, '_sale_price', true) === '' && get_post_meta($pid, '_regular_price', true) === '') {
                eh_bep_update_meta_fn($pid, '_price', '');
            }
            switch ($stock_manage_select) {
                case 'yes':
                    $collect_product_data['stock_manage'] = $product_data['stock_manage'];
                    eh_bep_update_meta_fn($pid, '_manage_stock', 'yes');
                    break;
                case 'no':
                    $collect_product_data['stock_manage'] = $product_data['stock_manage'];
                    eh_bep_update_meta_fn($pid, '_manage_stock', 'no');
                    break;
            }
            switch ($quantity_select) {
                case 'add':
                    $collect_product_data['stock_quantity'] = $product_data['stock_quantity'];
                    $quantity_val = number_format($product_data['stock_quantity'] + $quantity_text, 6, '.', '');
                    eh_bep_update_meta_fn($pid, '_stock', $quantity_val);
                    break;
                case 'sub':
                    $collect_product_data['stock_quantity'] = $product_data['stock_quantity'];
                    $quantity_val = number_format($product_data['stock_quantity'] - $quantity_text, 6, '.', '');
                    eh_bep_update_meta_fn($pid, '_stock', $quantity_val);
                    break;
                case 'replace':
                    $collect_product_data['stock_quantity'] = $product_data['stock_quantity'];
                    $quantity_val = number_format($quantity_text, 6, '.', '');
                    eh_bep_update_meta_fn($pid, '_stock', $quantity_val);
                    break;
            }
            switch ($backorder_select) {
                case 'no':
                    $collect_product_data['backorder'] = $product_data['backorder'];
                    eh_bep_update_meta_fn($pid, '_backorders', 'no');
                    break;
                case 'notify':
                    $collect_product_data['backorder'] = $product_data['backorder'];
                    eh_bep_update_meta_fn($pid, '_backorders', 'notify');
                    break;
                case 'yes':
                    $collect_product_data['backorder'] = $product_data['backorder'];
                    eh_bep_update_meta_fn($pid, '_backorders', 'yes');
                    break;
            }
            switch ($stock_status_select) {
                case 'instock':
                    $collect_product_data['stock_status'] = $product_data['stock_status'];
                    eh_bep_update_meta_fn($pid, '_stock_status', 'instock');
                    break;
                case 'outofstock':
                    $collect_product_data['stock_status'] = $product_data['stock_status'];
                    eh_bep_update_meta_fn($pid, '_stock_status', 'outofstock');
                    break;
            }
            switch ($length_select) {
                case 'add':
                    $collect_product_data['length'] = $product_data['length'];
                    $length_val = $product_data['length'] + $length_text;
                    eh_bep_update_meta_fn($pid, '_length', $length_val);
                    break;
                case 'sub':
                    $collect_product_data['length'] = $product_data['length'];
                    $length_val = $product_data['length'] - $length_text;
                    eh_bep_update_meta_fn($pid, '_length', $length_val);
                    break;
                case 'replace':
                    $collect_product_data['length'] = $product_data['length'];
                    $length_val = $length_text;
                    eh_bep_update_meta_fn($pid, '_length', $length_val);
                    break;
            }
            switch ($width_select) {
                case 'add':
                    $collect_product_data['width'] = $product_data['width'];
                    $width_val = $product_data['width'] + $width_text;
                    eh_bep_update_meta_fn($pid, '_width', $width_val);
                    break;
                case 'sub':
                    $collect_product_data['width'] = $product_data['width'];
                    $width_val = $product_data['width'] - $width_text;
                    eh_bep_update_meta_fn($pid, '_width', $width_val);
                    break;
                case 'replace':
                    $collect_product_data['width'] = $product_data['width'];
                    $width_val = $width_text;
                    eh_bep_update_meta_fn($pid, '_width', $width_val);
                    break;
            }
            switch ($height_select) {
                case 'add':
                    $collect_product_data['height'] = $product_data['height'];
                    $height_val = $product_data['height'] + $height_text;
                    eh_bep_update_meta_fn($pid, '_height', $height_val);
                    break;
                case 'sub':
                    $collect_product_data['height'] = $product_data['height'];
                    $height_val = $product_data['height'] - $height_text;
                    eh_bep_update_meta_fn($pid, '_height', $height_val);
                    break;
                case 'replace':
                    $collect_product_data['height'] = $product_data['height'];
                    $height_val = $height_text;
                    eh_bep_update_meta_fn($pid, '_height', $height_val);
                    break;
            }
            switch ($weight_select) {
                case 'add':
                    $collect_product_data['weight'] = $product_data['weight'];
                    $weight_val = $product_data['weight'] + $weight_text;
                    eh_bep_update_meta_fn($pid, '_weight', $weight_val);
                    break;
                case 'sub':
                    $collect_product_data['weight'] = $product_data['weight'];
                    $weight_val = $product_data['weight'] - $weight_text;
                    eh_bep_update_meta_fn($pid, '_weight', $weight_val);
                    break;
                case 'replace':
                    $collect_product_data['weight'] = $product_data['weight'];
                    $weight_val = $weight_text;
                    eh_bep_update_meta_fn($pid, '_weight', $weight_val);
                    break;
            }
            wc_delete_product_transients($pid);
        }

        // Edit Attributes
        if ($temp_type != 'variation' && !empty($_POST['attribute'])) {
            $i = 0;
            $is_variation = 0;
            $prev_value = '';
            $_product_attributes = get_post_meta($pid, '_product_attributes', TRUE);
            $attr_undo = $_product_attributes;
            foreach ($attr_undo as $key => $val) {
                $attr_undo[$key]['value'] = wc_get_product_terms($pid, $key);
            }
            $collect_product_data['attributes'] = $attr_undo;
            if ($_POST['attribute_variation'] == 'add') {
                $is_variation = 1;
            }
            if ($_POST['attribute_variation'] == 'remove') {
                $is_variation = 0;
            }

            if (!empty($_POST['attribute_value'])) {
                foreach ($_POST['attribute_value'] as $key => $value) {

                    $value = stripslashes($value);
                    $value = preg_replace('/\'/', '', $value);
                    $att_slugs = explode(':', $value);
                    if ($_POST['attribute_variation'] == '' && isset($_product_attributes[$att_slugs[0]])) {
                        $is_variation = $_product_attributes[$att_slugs[0]]['is_variation'];
                    }
                    if ($prev_value != $att_slugs[0]) {
                        $i = 0;
                    }
                    $prev_value = $att_slugs[0];
                    if ($_POST['attribute_action'] == 'replace' && $i == 0) {
                        wp_set_object_terms($pid, $att_slugs[1], $att_slugs[0]);
                        $i++;
                    } else {
                        wp_set_object_terms($pid, $att_slugs[1], $att_slugs[0], true);
                    }
                    $thedata = Array($att_slugs[0] => Array(
                            'name' => $att_slugs[0],
                            'value' => $att_slugs[1],
                            'is_visible' => '1',
                            'is_taxonomy' => '1',
                            'is_variation' => $is_variation
                    ));
                    if ($_POST['attribute_action'] == 'add' || $_POST['attribute_action'] == 'replace') {
                        $_product_attr = get_post_meta($pid, '_product_attributes', TRUE);
                        if (!empty($_product_attr)) {
                            update_post_meta($pid, '_product_attributes', array_merge($_product_attr, $thedata));
                        } else {
                            update_post_meta($pid, '_product_attributes', $thedata);
                        }
                    }
                    if ($_POST['attribute_action'] == 'remove') {
                        wp_remove_object_terms($pid, $att_slugs[1], $att_slugs[0]);
                    }
                }
            }
            if (!empty($_POST['new_attribute_values']) || $_POST['new_attribute_values'] != '') {
                $ar1 = explode(',', $_POST['attribute']);
                foreach ($ar1 as $key => $value) {
                    foreach ($_POST['new_attribute_values'] as $key_index => $value_slug) {

                        $att_s = 'pa_' . $value;

                        if ($prev_value != $att_s) {
                            $i = 0;
                        }


                        if ($_POST['attribute_variation'] == '' && isset($_product_attributes[$att_s])) {
                            $is_variation = $_product_attributes[$att_s]['is_variation'];
                        }


                        $prev_value = $att_s;
                        if ($_POST['attribute_action'] == 'replace' && $i == 0) {
                            wp_set_object_terms($pid, $value_slug, $att_s);
                            $i++;
                        } else {
                            wp_set_object_terms($pid, $value_slug, $att_s, true);
                        }
                        $thedata = Array($att_s => Array(
                                'name' => $att_s,
                                'value' => $value_slug,
                                'is_visible' => '1',
                                'is_taxonomy' => '1',
                                'is_variation' => $is_variation
                        ));
                        if ($_POST['attribute_action'] == 'add' || $_POST['attribute_action'] == 'replace') {
                            $_product_attr = get_post_meta($pid, '_product_attributes', TRUE);
                            if (!empty($_product_attr)) {
                                update_post_meta($pid, '_product_attributes', array_merge($_product_attr, $thedata));
                            } else {
                                update_post_meta($pid, '_product_attributes', $thedata);
                            }
                        }
                    }
                }
            }
        }

        $undo_product_data[$pid] = $collect_product_data;
    }
    if ($undo_update === 'yes') {
        if ($_POST['index_val'] == 0) {
            update_option('eh_temp_product_id', $undo_product_data);
        } else {
            $update_pid = array();
            $update_pid = get_option('eh_temp_product_id');
            $update_pid = $update_pid + $undo_product_data;
            update_option('eh_temp_product_id', $update_pid);
        }
        $prod_id = get_option('eh_temp_product_id');
        update_option('eh_bulk_edit_undo_product_id', $prod_id);
        update_option('eh_bulk_edit_undo_variation_id', $undo_variation_data);
        update_option('eh_bulk_edit_undo_edit_data', $edit_data);
    }
    if ($_POST['index_val'] == $_POST['chunk_length'] - 1) {
        array_push($sale_warning, 'done');
        die(json_encode($sale_warning));
    }
    die(json_encode($sale_warning));
}

function eh_bep_update_meta_fn($id, $key, $value) {
    update_post_meta($id, $key, $value);
}

function eh_bep_list_table_all_callback() {
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    $obj = new Eh_DataTables();
    $obj->input();
    $obj->ajax_response('1');
}

function eh_clear_all_callback() {
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    update_option('eh_bulk_edit_choosed_product_id', eh_bep_get_first_products());
    $obj = new Eh_DataTables();
    $obj->input();
    $obj->ajax_response();
}

function eh_bep_search_filter_callback() {
    set_time_limit(300);
    check_ajax_referer('ajax-eh-bep-nonce', '_ajax_eh_bep_nonce');
    $obj_fil = new Eh_DataTables();
    $obj_fil->input();
    $obj_fil->ajax_response('1');
}

function eh_bep_undo_html_maker() {
    $undo_data = get_option('eh_bulk_edit_undo_edit_data', array());
    ob_start();
    if (!empty($undo_data)) {
        ?>
        <div class='wrap postbox table-box table-box-main' id="undo_update" style='padding:0px 20px;'>
            <h2>
                <?php _e('Undo the Update - Overview', 'eh_bulk_edit'); ?>
            </h2>
            <hr>
            <table class='eh-edit-table' id='update_general_table'>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['title_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='title'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Title', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a condition to edit the title, and enter the relevant text', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['title_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'set_new':
                                ?>
                                <span><?php _e('Set New [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'append':
                                ?>
                                <span><?php _e('Append [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'prepand':
                                ?>
                                <span><?php _e('Prepend [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'replace':
                                ?>
                                <span><?php _e('Replace [ ', 'eh_bulk_edit'); ?></Span>
                                <?php
                                break;
                            case 'regex_replace':
                                ?>
                                <span><?php _e('RegEx Replace [ ', 'eh_bulk_edit'); ?></Span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                        <span id='title_text'>
                            <?php
                            switch ($undo_data['title_select']) {
                                case '':
                                    break;
                                case 'replace':
                                    ?>
                                    <span style="background: whitesmoke">Text to be replaced : <b><?php _e($undo_data['replace_title_text'], 'eh_bulk_edit'); ?></b> -> Replace Text : <b><?php _e($undo_data['title_text'], 'eh_bulk_edit'); ?></b></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                case 'regex_replace':
                                    ?>
                                    <span style="background: whitesmoke">Pattern : <b><?php _e($undo_data['regex_replace_title_text'], 'eh_bulk_edit'); ?></b> -> Replacement : <b><?php _e($undo_data['title_text'], 'eh_bulk_edit'); ?></b></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                default:
                                    ?>
                                    <span style="background: whitesmoke"><b><?php _e($undo_data['title_text'], 'eh_bulk_edit'); ?></b></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                            }
                            ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['sku_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='sku'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('SKU', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a condition to edit the SKU, and enter the relevant text', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['sku_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'set_new':
                                ?>
                                <span><?php _e('Set New [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'append':
                                ?>
                                <span><?php _e('Append [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'prepand':
                                ?>
                                <span><?php _e('Prepend [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'replace':
                                ?>
                                <span><?php _e('Replace [ ', 'eh_bulk_edit'); ?></Span>
                                <?php
                                break;
                            case 'regex_replace':
                                ?>
                                <span><?php _e('RegEx_Replace [ ', 'eh_bulk_edit'); ?></Span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                        <span id='sku_text'>
                            <?php
                            switch ($undo_data['sku_select']) {
                                case '':
                                    break;
                                case 'replace':
                                    ?>
                                    <span style="background: whitesmoke">Text to be replaced : <b><?php _e($undo_data['sku_replace_text'], 'eh_bulk_edit'); ?></b> -> Replace Text : <b><?php _e($undo_data['sku_text'], 'eh_bulk_edit'); ?></b></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                case 'regex_replace':
                                    ?>
                                    <span style="background: whitesmoke">Pattern : <b><?php _e($undo_data['regex_sku_replace_text'], 'eh_bulk_edit'); ?></b> -> Replacement : <b><?php _e($undo_data['sku_text'], 'eh_bulk_edit'); ?></b></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;

                                default:
                                    ?>
                                    <span style="background: whitesmoke"><b><?php _e($undo_data['sku_text'], 'eh_bulk_edit'); ?></b></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                            }
                            ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['catalog_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='catalog'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Product Visiblity', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose which all shop pages the product will be listed on', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['catalog_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'visible':
                                ?>
                                <span><?php _e('Shop and Search', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'catalog':
                                ?>
                                <span><?php _e('Shop', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'search':
                                ?>
                                <span><?php _e('Search', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'hidden':
                                ?>
                                <span><?php _e('Hidden', 'eh_bulk_edit'); ?></Span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['shipping_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='shipping'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Shipping Class', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a shipping class that will be added to all the filtered products', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['shipping_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case '-1':
                                ?>
                                <span><?php _e('Shipping Class : No Shipping Class', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            default:
                                ?>
                                <span><?php _e('Shipping Class : ' . get_term($undo_data['shipping_select'])->name, 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <h2>
                <?php _e('Price', 'eh_bulk_edit'); ?>
            </h2>
            <hr>
            <table class='eh-edit-table' id="update_price_table">
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['sale_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='sale'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Sale Price', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a condition to adjust the price and enter the value. You can also choose an option to round it to the nearest value', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['sale_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'up_percentage':
                                ?>
                                <span><?php _e('Increased by Percentage ( + %) [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'down_percentage':
                                ?>
                                <span><?php _e('Decreased by Percentage ( - %) [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'up_price':
                                ?>
                                <span><?php _e('Increased by Price ( + $) [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'down_price':
                                ?>
                                <span><?php _e('Decreased by Price ( - $) [ ', 'eh_bulk_edit'); ?></Span>
                                <?php
                                break;
                            case 'flat_all':
                                ?>
                                <span><?php _e('Flat Price for all [ ', 'eh_bulk_edit'); ?></Span>
                                <?php
                                break;

                            default:
                                break;
                        }
                        ?>
                        <span id='sale_price_text'>
                            <?php
                            switch ($undo_data['sale_select']) {
                                case '':
                                    break;
                                case 'up_percentage':
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Percentage : ' . $undo_data['sale_text'] . ' %', 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                case 'down_percentage':
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Percentage : ' . $undo_data['sale_text'] . ' %', 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                case 'up_price':
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Amount : ' . $undo_data['sale_text'] . ' /-', 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                case 'down_price':
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Amount : ' . $undo_data['sale_text'] . ' /-', 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                case 'flat_all':
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Amount : ' . $undo_data['sale_text'] . ' /-', 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                default:
                                    break;
                            }
                            ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['regular_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='regular'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Regular Price', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select a condition to adjust the price and enter the value. You can also choose an option to round it to the nearest value', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['regular_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'up_percentage':
                                ?>
                                <span><?php _e('Increased by Percentage ( + %) [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'down_percentage':
                                ?>
                                <span><?php _e('Decreased by Percentage ( - %) [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'up_price':
                                ?>
                                <span><?php _e('Increased by Price ( + $) [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'down_price':
                                ?>
                                <span><?php _e('Decreased by Price ( - $) [ ', 'eh_bulk_edit'); ?></Span>
                                <?php
                                break;
                            case 'flat_all':
                                ?>
                                <span><?php _e('Flat Price for all [ ', 'eh_bulk_edit'); ?></Span>
                                <?php
                                break;

                            default:
                                break;
                        }
                        ?>
                        <span id='regular_price_text'>
                            <?php
                            switch ($undo_data['regular_select']) {
                                case '':
                                    break;
                                case 'up_percentage':
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Percentage : ' . $undo_data['regular_text'] . ' %', 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                case 'down_percentage':
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Percentage : ' . $undo_data['regular_text'] . ' %', 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                case 'up_price':
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Amount : ' . $undo_data['regular_text'] . ' /-', 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                case 'down_price':
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Amount : ' . $undo_data['regular_text'] . ' /-', 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                case 'flat_all':
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Amount : ' . $undo_data['regular_text'] . ' /-', 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                                default:
                                    break;
                            }
                            ?>
                        </span>
                    </td>
                </tr>
            </table>
            <h2>
                <?php _e('Stock', 'eh_bulk_edit'); ?>
            </h2>
            <hr>
            <table class='eh-edit-table' id='update_stock_table'>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['stock_manage_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='manage_stock'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Manage Stock', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Enable or Disable manage stock for products or variations', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['stock_manage_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'yes':
                                ?>
                                <span><?php _e('Enabled', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'no':
                                ?>
                                <span><?php _e('Disabled', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['quantity_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='quantity'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Stock Quantity', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update stock quantity and enter the value', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['quantity_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'add':
                                ?>
                                <span><?php _e('Increased [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'sub':
                                ?>
                                <span><?php _e('Decreased [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'replace':
                                ?>
                                <span><?php _e('Replaced [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                        <span id='stock_quantity_text'>
                            <?php
                            switch ($undo_data['quantity_select']) {
                                case '':
                                    break;
                                default:
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Quantity : ' . $undo_data['quantity_text'], 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                            }
                            ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['backorder_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='backorders'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Allow Backorders', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose how you want to handle backorders', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['backorder_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'no':
                                ?>
                                <span><?php _e('Do not Allow', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'notify':
                                ?>
                                <span><?php _e('Allow, but Notify the Customer', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'yes':
                                ?>
                                <span><?php _e('Allowed', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['stock_status_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='stock_status'>
                                <?php
                                break;
                        }
                        ?>                    
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Stock Status', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update  the stock status', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['stock_status_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'instock':
                                ?>
                                <span><?php _e('In Stock', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'outofstock':
                                ?>
                                <span><?php _e('Out of Stock', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <h2>
                <?php _e('Weight & Dimensions', 'eh_bulk_edit'); ?>
            </h2>
            <hr>
            <table class='eh-edit-table' id='update_properties_table'>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['length_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='length'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Length', 'eh_bulk_edit'); ?>
                        <span style="float:right;"><?php echo strtolower(get_option('woocommerce_dimension_unit')); ?></span>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update length and enter the value', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['length_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'add':
                                ?>
                                <span><?php _e('Increased [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'sub':
                                ?>
                                <span><?php _e('Decreased [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'replace':
                                ?>
                                <span><?php _e('Replaced [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                        <span id='length_text'>
                            <?php
                            switch ($undo_data['length_select']) {
                                case '':
                                    break;
                                default:
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Dimension : ' . $undo_data['length_text'], 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                            }
                            ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['width_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='width'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Width', 'eh_bulk_edit'); ?>
                        <span style="float:right;"><?php echo strtolower(get_option('woocommerce_dimension_unit')); ?></span>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update width and enter the value', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['width_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'add':
                                ?>
                                <span><?php _e('Increased [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'sub':
                                ?>
                                <span><?php _e('Decreased [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'replace':
                                ?>
                                <span><?php _e('Replaced [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                        <span id='width_text'>
                            <?php
                            switch ($undo_data['width_select']) {
                                case '':
                                    break;
                                default:
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Dimension : ' . $undo_data['width_text'], 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                            }
                            ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['height_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='height'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Height', 'eh_bulk_edit'); ?>
                        <span style="float:right;"><?php echo strtolower(get_option('woocommerce_dimension_unit')); ?></span>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update height and enter the value', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['height_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'add':
                                ?>
                                <span><?php _e('Increased [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'sub':
                                ?>
                                <span><?php _e('Decreased [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'replace':
                                ?>
                                <span><?php _e('Replaced [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                        <span id='height_text'>
                            <?php
                            switch ($undo_data['height_select']) {
                                case '':
                                    break;
                                default:
                                    ?>
                                    <span style="background: whitesmoke"><span><?php _e('Dimension : ' . $undo_data['height_text'], 'eh_bulk_edit'); ?></span>
                                        <?php
                                        _e(' ] ', 'eh_bulk_edit');
                                        break;
                                }
                                ?>
                            </span>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['weight_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='weight'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Weight', 'eh_bulk_edit'); ?>
                        <span style="float:right;"><?php echo strtolower(get_option('woocommerce_weight_unit')); ?></span>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Choose an option to update weight and enter the value', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['weight_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'add':
                                ?>
                                <span><?php _e('Increased [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'sub':
                                ?>
                                <span><?php _e('Decreased [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'replace':
                                ?>
                                <span><?php _e('Replaced [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                        <span id='weight_text'>
                            <?php
                            switch ($undo_data['weight_select']) {
                                case '':
                                    break;
                                default:
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Dimension : ' . $undo_data['weight_text'], 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                            }
                            ?>
                        </span>
                    </td>
                </tr>
            </table>


        </table>
        <h2>
            <?php _e('Attributes', 'eh_bulk_edit'); ?>
        </h2>
        <hr>


        <table class='eh-edit-table' id='update_properties_table'>
            <tr>
                <td class='eh-edit-tab-table-undo-check'>
                    <?php
                    switch ($undo_data['attribute_action']) {
                        case '':
                            break;
                        default:
                            ?>
                            <input type="checkbox" name='undo_checkbox_values' checked value='attributes'>
                            <?php
                            break;
                    }
                    ?>
                </td>
                <td class='eh-edit-tab-table-left'>
                    <?php _e('Attribute Actions', 'eh_bulk_edit'); ?>

                </td>
                <td class='eh-edit-tab-table-middle'>
                    <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select an option to make changes to your attribute values', 'eh_bulk_edit'); ?>'></span>
                </td>
                <td class='eh-edit-tab-table-input-td'>
                    <?php
                    switch ($undo_data['attribute_action']) {
                        case '':
                            ?>
                            <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                            <?php
                            break;
                        case 'add':
                            ?>
                            <span><?php _e('Added ', 'eh_bulk_edit'); ?></span>
                            <?php
                            break;
                        case 'remove':
                            ?>
                            <span><?php _e('Removed ', 'eh_bulk_edit'); ?></span>
                            <?php
                            break;
                        case 'replace':
                            ?>
                            <span><?php _e('Replaced ', 'eh_bulk_edit'); ?></span>
                            <?php
                            break;
                        default:
                            break;
                    }
                    ?>

                </td>
            </tr>
            <tr>
        </table>

        <?php
        if (in_array('pricing-discounts-by-user-role-woocommerce/pricing-discounts-by-user-role-woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            ?>
            <h2>
                <?php _e('Role Based Pricing', 'eh_bulk_edit'); ?>
            </h2>
            <hr>
            <table class='eh-edit-table' id='update_general_table'>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['hide_price']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='hide_price'>
                                <?php
                                break;
                        }
                        ?>                    
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Hide price', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select option to hide price for unregistered users.', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['hide_price']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'no':
                                ?>
                                <span><?php _e('Show Price', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'yes':
                                ?>
                                <span><?php _e('Hide Price', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        $selected_roles = $undo_data['hide_price_role'];
                        switch ($selected_roles) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='hide_price_role'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Hide product price based on user role', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('For selected user role, hide the product price', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <span class='select-eh'>
                            <?php
                            global $wp_roles;
                            $roles = $wp_roles->role_names;
                            $r = 0;
                            foreach ($roles as $key => $value) {
                                if (in_array($key, $selected_roles)) {
                                    _e($value, 'eh_bulk_edit');
                                    $r++;
                                }
                                if ($r > 0) {
                                    _e(', ', 'eh_bulk_edit');
                                }
                            }
                            ?>
                        </span>
                    </td>
                </tr>
                <?php
                $enabled_roles = get_option('eh_pricing_discount_product_price_user_role');
                if (is_array($enabled_roles)) {
                    if (!in_array('none', $enabled_roles)) {
                        ?>
                        <tr>
                            <td class='eh-edit-tab-table-undo-check'>
                                <?php
                                switch ($undo_data['price_adjustment']) {
                                    case '':
                                        break;
                                    default:
                                        ?>
                                        <input type="checkbox" name='undo_checkbox_values' checked value='price_adjustment'>
                                        <?php
                                        break;
                                }
                                ?>                        
                            </td>
                            <td class='eh-edit-tab-table-left'>
                                <?php _e('Enforce product price adjustment', 'eh_bulk_edit'); ?>
                            </td>
                            <td class='eh-edit-tab-table-middle'>
                                <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Select option to enforce indvidual price adjustment', 'eh_bulk_edit'); ?>'></span>
                            </td>
                            <td class='eh-edit-tab-table-input-td'>
                                <?php
                                switch ($undo_data['price_adjustment']) {
                                    case '':
                                        ?>
                                        <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                        <?php
                                        break;
                                    case 'no':
                                        ?>
                                        <span><?php _e('Disabled', 'eh_bulk_edit'); ?></span>
                                        <?php
                                        break;
                                    case 'yes':
                                        ?>
                                        <span><?php _e('Enabled', 'eh_bulk_edit'); ?></span>
                                        <?php
                                        break;
                                    default:
                                        break;
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
            <?php
        }

        if (in_array('australia-post-woocommerce-shipping/australia-post-woocommerce-shipping.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            ?>
            <h2>
                <?php _e('<a href="https://adaptxy.com/plugin/woocommerce-australia-post-shipping-plugin-with-print-label-tracking/" target="_blank">Australia Post Plugin</a> from AdaptXY', 'eh_bulk_edit'); ?>
            </h2>
            <hr>
            <table class='eh-edit-table' id='update_general_table'>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        if (isset($undo_data['aus_hs_tariff'])) {
                            switch ($undo_data['aus_hs_tariff']) {
                                case '':
                                    break;
                                default:
                                    ?>
                                    <input type="checkbox" name='undo_checkbox_values' checked value='aus_hs_tariff'>
                                    <?php
                                    break;
                            }
                        }
                        ?>                    
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Country of Origin (Australia Post)', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Update Tariff Code', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        if (isset($undo_data['aus_hs_tariff']) && $undo_data['aus_hs_tariff'] != '') {
                            ?>
                            <span><?php _e($undo_data['aus_hs_tariff'], 'eh_bulk_edit'); ?></span>
                            <?php
                        } else {
                            ?>
                            <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                            <?php
                        }
                        ?>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        if (isset($undo_data['aus_origin_country'])) {
                            switch ($undo_data['aus_origin_country']) {
                                case '':
                                    break;
                                default:
                                    ?>
                                    <input type="checkbox" name='undo_checkbox_values' checked value='aus_origin_country'>
                                    <?php
                                    break;
                            }
                        }
                        ?>                    
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Country of Origin (Australia Post)', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Update Country of Origin', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        if (isset($undo_data['aus_origin_country']) && $undo_data['aus_origin_country'] != '') {
                            ?>
                            <span><?php _e($undo_data['aus_origin_country'], 'eh_bulk_edit'); ?></span>
                            <?php
                        } else {
                            ?>
                            <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                            <?php
                        }
                        ?>
            </table>
            <?php
        }

        if (in_array('per-product-addon-for-woocommerce-shipping-pro/woocommerce-per-product-shipping-addon-for-shipping-pro.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            ?>
            <h2>
                <?php _e('Shipping Pro', 'eh_bulk_edit'); ?>
            </h2>
            <hr>
            <table class='eh-edit-table' id='update_general_table'>
                <tr>
                    <td class='eh-edit-tab-table-undo-check'>
                        <?php
                        switch ($undo_data['shipping_unit_select']) {
                            case '':
                                break;
                            default:
                                ?>
                                <input type="checkbox" name='undo_checkbox_values' checked value='wf_shipping_unit'>
                                <?php
                                break;
                        }
                        ?>
                    </td>
                    <td class='eh-edit-tab-table-left'>
                        <?php _e('Shipping Unit', 'eh_bulk_edit'); ?>
                    </td>
                    <td class='eh-edit-tab-table-middle'>
                        <span class='woocommerce-help-tip tooltip' data-tooltip='<?php _e('Update Shipping Unit', 'eh_bulk_edit'); ?>'></span>
                    </td>
                    <td class='eh-edit-tab-table-input-td'>
                        <?php
                        switch ($undo_data['shipping_unit_select']) {
                            case '':
                                ?>
                                <span><?php _e('No Change', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'add':
                                ?>
                                <span><?php _e('Added [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'sub':
                                ?>
                                <span><?php _e('Subtracted [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            case 'replace':
                                ?>
                                <span><?php _e('Replaced [ ', 'eh_bulk_edit'); ?></span>
                                <?php
                                break;
                            default:
                                break;
                        }
                        ?>
                        <span id='weight_text'>
                            <?php
                            switch ($undo_data['shipping_unit_select']) {
                                case '':
                                    break;
                                default:
                                    ?>
                                    <span style="background: whitesmoke"><?php _e('Unit : ' . $undo_data['shipping_unit'], 'eh_bulk_edit'); ?></span>
                                    <?php
                                    _e(' ] ', 'eh_bulk_edit');
                                    break;
                            }
                            ?>
                        </span>
                    </td>
                </tr>
            </table>
            <?php
        }
        ?>

        <button id='undo_cancel_button' style="margin-bottom: 1%; background-color: gray; color: white; width: 10%;" class='button button-large'><span class="update-text"><?php _e('Cancel', 'eh_bulk_edit'); ?></span></button>
        <button id='undo_update_button' style="margin-bottom: 1%; float: right; color: white; width: 10%;" class='button button-primary button-large'><span class="update-text"><?php _e('Continue', 'eh_bulk_edit'); ?></span></button>
        </div>
        <?php
    } else {
        ?>
        <div class='wrap postbox table-box table-box-main' id="undo_update" style='padding:0px 20px;'>
            <h2>
                <?php _e('Undo the update - Overview', 'eh_bulk_edit'); ?>
            </h2>
            <hr>
            <div class='eh-edit-table'>
                <?php _e('Oops! No previous update found.', 'eh_bulk_edit'); ?>
            </div>
            <button id='undo_cancel_button' style="margin-bottom: 1%;  background-color: gray; color: white; width: 10%;" class='button button-large'><span class="update-text"><?php _e('Back', 'eh_bulk_edit'); ?></span></button>
        </div>
        <?php
    }
    $value = ob_get_clean();
    die($value);
}

function xa_bep_get_selected_products($table_obj = null) {
    $sel_ids = array();
    if (isset($_REQUEST['count_products'])) {
        $sel_ids = get_option('xa_bulk_selected_ids');
        return $sel_ids;
    }
    delete_option('xa_bulk_selected_ids');
    $page_no = !empty($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
    $filter_range = !empty($_REQUEST['range']) ? $_REQUEST['range'] : '';
    $filter_desired_price = (float) sanitize_text_field(!empty($_REQUEST['desired_price']) ? $_REQUEST['desired_price'] : '');
    $filter_minimum_price = sanitize_text_field(!empty($_REQUEST['minimum_price']) ? $_REQUEST['minimum_price'] : '');
    $filter_maximum_price = sanitize_text_field(!empty($_REQUEST['maximum_price']) ? $_REQUEST['maximum_price'] : '');
    $selected_products = array();
    $per_page = (get_option('eh_bulk_edit_table_row')) ? get_option('eh_bulk_edit_table_row') : 20;
    $pid_to_include = xa_bep_filter_products();
    $ids = array();

    if ((WC()->version < '2.7.0')) {
        foreach ($pid_to_include as $pid) {
            $product = wc_get_product($pid);
            $title_valid = true;
            if (isset($_REQUEST['product_title_select']) && $_REQUEST['product_title_select'] != 'all' && $_REQUEST['product_title_text'] != '') {
                $product_title = strtolower($product->post->post_title);
                $product_title_text = strtolower($_REQUEST['product_title_text']);
                $length = strlen($product_title_text);
                $title_valid = true;
                if ((($_REQUEST['product_title_select'] == 'starts_with') && !(substr_compare($product_title, $product_title_text, 0, $length) === 0))) {
                    $title_valid = false;
                } else if ((($_REQUEST['product_title_select'] == 'ends_with') && !(substr_compare($product_title, $product_title_text, -$length) === 0))) {
                    $title_valid = false;
                } else if ((($_REQUEST['product_title_select'] == 'contains') && !(strpos($product_title, $product_title_text) !== false))) {
                    $title_valid = false;
                } else if ((($_REQUEST['product_title_select'] == 'title_regex'))) {
                    $regex_flags = '';
                    if (!empty($_REQUEST['regex_flags'])) {
                        foreach ($_REQUEST['regex_flags'] as $reg_val) {
                            $regex_flags .= $reg_val;
                        }
                    }
                    if (@preg_match('/' . $product_title_text . '/', null) === false) {
                        update_option('xa_regex_error', true);
                        break;
                    } else if (!(preg_match('/' . $product_title_text . '/' . $regex_flags, $product_title))) {
                        $title_valid = false;
                    }
                }
            }

            if ($title_valid) {
                $price_valid = 0;
                apply_filters('http_request_timeout', 30);
                $temp_id = $product->id;
                $temp_type = $product->product_type;
                if ($temp_type == 'simple') {
                    $attr_check = xa_attribute_check_simple($temp_id);
                    if ($attr_check) {
                        if ($filter_range != 'all' && !empty($filter_range)) {
                            switch ($filter_range) {
                                case '>':
                                    if ($proudct->get_regular_price() >= $filter_desired_price) {
                                        $price_valid = 1;
                                    }
                                    break;
                                case '<':
                                    if ($product->get_regular_price() <= $filter_desired_price) {
                                        $price_valid = 1;
                                    }
                                    break;
                                case '=':
                                    if ($product->get_regular_price() == $filter_desired_price) {
                                        $price_valid = 1;
                                    }
                                    break;
                                case '|':
                                    if ($product->get_regular_price() >= $filter_minimum_price && $product->get_regular_price() <= $filter_maximum_price) {
                                        $price_valid = 1;
                                    }
                                    break;
                            }
                        } else {
                            $price_valid = 1;
                        }

                        if ($price_valid) {
                            array_push($ids, $temp_id);
                            $selected_products[$temp_id] = $product;
                        }
                    }
                } elseif ($temp_type == 'variable') {
                    $show_parent = true;
                    if (isset($_REQUEST['type'])) {
                        $avail_variations = $product->get_children();
                        //performance tip use get_variation_prices()
                        $show_parent = false;
                        foreach ($avail_variations as $child_id) {
                            $cprod = wc_get_product($child_id);

                            $attr_check = xa_attribute_check_variable($cprod);

                            if ($attr_check) {
                                if ($filter_range != 'all' && !empty($filter_range)) {
                                    switch ($filter_range) {
                                        case '>':
                                            if ($cprod->get_regular_price() >= $filter_desired_price) {
                                                $price_valid = 1;
                                            }
                                            break;
                                        case '<':
                                            if ($cprod->get_regular_price() <= $filter_desired_price) {
                                                $price_valid = 1;
                                            }
                                            break;
                                        case '=':
                                            if ($cprod->get_regular_price() == $filter_desired_price) {
                                                $price_valid = 1;
                                            }
                                            break;
                                        case '|':
                                            if ($cprod->get_regular_price() >= $filter_minimum_price && $cprod->get_regular_price() <= $filter_maximum_price) {
                                                $price_valid = 1;
                                            }
                                            break;
                                    }
                                } else {
                                    $price_valid = 1;
                                }
                                if ($price_valid) {
                                    $show_parent = true;
                                    if ((empty($_REQUEST['type']) || in_array("\'variation\'", $_REQUEST['type']))) {
                                        array_push($ids, $child_id);
                                        $selected_products[$child_id] = $cprod;
                                    }
                                }
                            }
                        }
                    }
                    if (isset($_REQUEST['type']) && (empty($_REQUEST['type']) || in_array("\'variable\'", $_REQUEST['type'])) && ($filter_range == 'all' || (empty($_REQUEST['desired_price']) && empty($_REQUEST['maximum_price']) && empty($_REQUEST['minimum_price']) && $show_parent))) {
                        array_push($ids, $temp_id);
                        $selected_products[$temp_id] = $product;
                    }
                }
                if (isset($_REQUEST['page']) && !empty($table_obj)) {
                    break;
                }
            }
        }
    } else {
        $pid_ch = array_chunk($pid_to_include, 500);
        for ($i = 0; $i < count($pid_ch); $i++) {

            $args = array(
                'status' => array('private', 'publish'),
                'include' => $pid_ch[$i],
                'limit' => 500
            );
            $query = wc_get_products($args);
            foreach ($query as $product) {
                $title_valid = true;
                if (isset($_REQUEST['product_title_select']) && $_REQUEST['product_title_select'] != 'all' && $_REQUEST['product_title_text'] != '') {
                    $product_title = strtolower($product->get_name());
                    $product_title_text = strtolower($_REQUEST['product_title_text']);
                    $length = strlen($product_title_text);
                    $title_valid = true;
                    if ((($_REQUEST['product_title_select'] == 'starts_with') && !(substr_compare($product_title, $product_title_text, 0, $length) === 0))) {
                        $title_valid = false;
                    } else if ((($_REQUEST['product_title_select'] == 'ends_with') && !(substr_compare($product_title, $product_title_text, -$length) === 0))) {
                        $title_valid = false;
                    } else if ((($_REQUEST['product_title_select'] == 'contains') && !(strpos($product_title, $product_title_text) !== false))) {
                        $title_valid = false;
                    } else if ((($_REQUEST['product_title_select'] == 'title_regex'))) {//
                        // && !(preg_match($product_title_text, $product_title)))) {
                        $regex_flags = '';
                        if (!empty($_REQUEST['regex_flags'])) {
                            foreach ($_REQUEST['regex_flags'] as $reg_val) {
                                $regex_flags .= $reg_val;
                            }
                        }
                        if (@preg_match('/' . $product_title_text . '/', null) === false) {
                            update_option('xa_regex_error', true);
                            break;
                        } else if (!(preg_match('/' . $product_title_text . '/' . $regex_flags, $product_title))) {
                            $title_valid = false;
                        }
                    }
                }
                if ($title_valid) {
                    $price_valid = 0;
                    apply_filters('http_request_timeout', 30);
                    $temp_id = $product->get_id();
                    $temp_type = $product->get_type();
                    if ($temp_type == 'simple') {
                        $and_attr = xa_attribute_check_simple($temp_id);
                        if ($and_attr) {
                            if ($filter_range != 'all' && !empty($filter_range)) {
                                switch ($filter_range) {
                                    case '>':
                                        if ($product->get_regular_price() >= $filter_desired_price) {
                                            $price_valid = 1;
                                        }
                                        break;
                                    case '<':
                                        if ($product->get_regular_price() <= $filter_desired_price) {
                                            $price_valid = 1;
                                        }
                                        break;
                                    case '=':
                                        if ($product->get_regular_price() == $filter_desired_price) {
                                            $price_valid = 1;
                                        }
                                        break;
                                    case '|':
                                        if ($product->get_regular_price() >= $filter_minimum_price && $product->get_regular_price() <= $filter_maximum_price) {
                                            $price_valid = 1;
                                        }
                                        break;
                                }
                            } else {
                                $price_valid = 1;
                            }

                            if ($price_valid == 1 && $and_attr == true) {
                                array_push($ids, $temp_id);
                                $selected_products[$temp_id] = $product;
                            }
                        }
                    } elseif ($temp_type == 'variable') {
                        $show_parent = true;
                        if (isset($_REQUEST['type'])) {
                            $avail_variations = $product->get_children();
                            $show_parent = false;
                            //performance tip use get_variation_prices()
                            foreach ($avail_variations as $child_id) {
                                $price_valid = 0;
                                $cprod = wc_get_product($child_id);
                                $and_attr = xa_attribute_check_variable($cprod);
                                if ($and_attr) {

                                    if ($filter_range != 'all' && !empty($filter_range)) {
                                        switch ($filter_range) {
                                            case '>':
                                                if ($cprod->get_regular_price() >= $filter_desired_price) {
                                                    $price_valid = 1;
                                                }
                                                break;
                                            case '<':
                                                if ($cprod->get_regular_price() <= $filter_desired_price) {
                                                    $price_valid = 1;
                                                }
                                                break;
                                            case '=':
                                                if ($cprod->get_regular_price() == $filter_desired_price) {
                                                    $price_valid = 1;
                                                }
                                                break;
                                            case '|':
                                                if ($cprod->get_regular_price() >= $filter_minimum_price && $cprod->get_regular_price() <= $filter_maximum_price) {
                                                    $price_valid = 1;
                                                }
                                                break;
                                        }
                                    } else {
                                        $price_valid = 1;
                                    }
                                    if ($price_valid) {
                                        $show_parent = true;
                                        if ((empty($_REQUEST['type']) || in_array("\'variation\'", $_REQUEST['type']))) {
                                            array_push($ids, $child_id);
                                            $selected_products[$child_id] = $cprod;
                                        }
                                    }
                                }
                            }
                        }
                        if (isset($_REQUEST['type']) && (empty($_REQUEST['type']) || in_array("\'variable\'", $_REQUEST['type'])) && ($filter_range == 'all' || (empty($_REQUEST['desired_price']) && empty($_REQUEST['maximum_price']) && empty($_REQUEST['minimum_price']) && $show_parent))) {
                            array_push($ids, $temp_id);
                            $selected_products[$temp_id] = $product;
                        }
                    }
                }
            }
            if (isset($_REQUEST['page']) && !empty($table_obj)) {
                break;
            }
        }
    }

    update_option('xa_bulk_selected_ids', $ids);
    $selected_chunk = array();
    $selected_chunk = array_chunk($selected_products, $per_page, true);
    $total_pages = count($selected_chunk);
    if (isset($_REQUEST['page']) && !empty($table_obj) && ($total_pages == 1)) {
        $total_pages++;
    }
    $ele_on_page = count($selected_products);
    if (!empty($table_obj)) {
        $table_obj->set_pagination_args(array(
            'total_items' => count($selected_products),
            'per_page' => $ele_on_page,
            'total_pages' => $total_pages
        ));
    }
    //return $selected_products;
    if (!empty($selected_chunk)) {
        return $selected_chunk[$page_no - 1];
    }
}

function xa_attribute_check_simple($temp_id) {
    $valid = 1;
    $check_or = false;
    if (!empty($_REQUEST['attribute_value'])) {
        foreach ($_REQUEST['attribute_value'] as $key => $val) {
            $att_slug = stripslashes($val);
            $att_slug_to_test = preg_replace('/\'/', '', $att_slug);
            $att_slug = explode(':', $att_slug);
            $att_slug = preg_replace('/\'/', '', $att_slug[0]);
            $prod_attr = wc_get_product_terms($temp_id, $att_slug, array('fields' => 'all'));
            if (empty($prod_attr)) {
                $valid = 0;
            }
            foreach ($prod_attr as $prod_terms) {
                $taxonomy = $prod_terms->taxonomy;
                $slug = $prod_terms->slug;
                $product_atttr = $taxonomy . ':' . $slug;
                if ($att_slug_to_test == $product_atttr) {
                    $valid = 1;
                    break;
                } else {
                    $valid = 0;
                }
            }
            if ($valid) {
                $check_or = true;
                break;
            }
        }
    }

    $num = 1;
    $temp_val = '';
    if ($valid) {
        $and_attr = true;
    } else {
        $and_attr = false;
    }
    if ($check_or == false) {
        if (!empty($_REQUEST['attribute_value_and'])) {
            foreach ($_REQUEST['attribute_value_and'] as $key => $val) {
                $att_slug = stripslashes($val);
                $att_slug_to_test = preg_replace('/\'/', '', $att_slug);
                $att_slug = explode(':', $att_slug);
                $att_slug = preg_replace('/\'/', '', $att_slug[0]);
                if ($num == 0 || $temp_val != $att_slug) {
                    if ($num == 0 && $temp_val != $att_slug) {
                        break;
                    }
                    $temp_val = $att_slug;
                    $prod_attr = wc_get_product_terms($temp_id, $att_slug, array('fields' => 'all'));
                    if (empty($prod_attr)) {
                        $num = 0;
                        break;
                    }
                    foreach ($prod_attr as $prod_terms) {
                        $num = 0;
                        $taxonomy = $prod_terms->taxonomy;
                        $slug = $prod_terms->slug;
                        $product_atttr = $taxonomy . ':' . $slug;
                        if ($att_slug_to_test == $product_atttr) {
                            $num = 1;
                            break;
                        }
                    }
                }
            }
            if ($num != 1) {
                $and_attr = false;
            } else {
                $and_attr = true;
            }
        }
    }
    return $and_attr;
}

function xa_attribute_check_variable($cprod) {
    $valid = 1;
    $check_or = false;
    if (!empty($_REQUEST['attribute_value'])) {
        foreach ($_REQUEST['attribute_value'] as $key => $val) {
            $att_slug = stripslashes($val);
            $att_slug = explode(':', $att_slug);
            $att_slug = preg_replace('/\'/', '', $att_slug[1]);
            foreach (array_values($cprod->get_variation_attributes()) as $ind => $values) {
                if ($values == $att_slug) {
                    $valid = 1;
                    break;
                } else {
                    $valid = 0;
                }
            }
            if ($valid) {
                $check_or = true;
                break;
            }
        }
    }

    $num = 1;
    $temp_val = '';
    if ($valid) {
        $and_attr = true;
    } else {
        $and_attr = false;
    }
    if ($check_or == false) {
        if (!empty($_REQUEST['attribute_value_and'])) {
            foreach ($_REQUEST['attribute_value_and'] as $key => $val) {
                $attr_equal = false;
                $att_slug = stripslashes($val);
                $att_slug_to_test = preg_replace('/\'/', '', $att_slug);
                $att_slug = explode(':', $att_slug);
                $att_slug = preg_replace('/\'/', '', $att_slug[0]);
                if ($num == 0 || $att_slug != $temp_val) {
                    if ($num == 0 && $att_slug != $temp_val) {
                        break;
                    }
                    $temp_val = $att_slug;
                    $prod_attr = $cprod->get_attributes();
                    foreach ($prod_attr as $key2 => $val2) {
                        if ($att_slug_to_test == $key2 . ':' . $val2) {
                            $num = 1;
                            $attr_equal = false;
                            break;
                        } else if ($att_slug . ':' == $key2 . ':' . $val2) {
                            $num = 1;
                            $attr_equal = false;
                            break;
                        } else {
                            $num = 0;
                            if ($att_slug == $key2) {
                                $attr_equal = true;
                            }
                        }
                    }
                }
            }
            if ($num != 1) {
                $and_attr = false;
            } else {
                $and_attr = true;
            }
        }
    }
    return $and_attr;
}

function xa_bep_filter_products() {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $sql = "SELECT 
                    DISTINCT ID 
                FROM {$prefix}posts 
                    LEFT JOIN {$prefix}term_relationships on {$prefix}term_relationships.object_id={$prefix}posts.ID 
                    LEFT JOIN {$prefix}term_taxonomy on {$prefix}term_taxonomy.term_taxonomy_id  = {$prefix}term_relationships.term_taxonomy_id 
                    LEFT JOIN {$prefix}terms on {$prefix}terms.term_id  ={$prefix}term_taxonomy.term_id 
                WHERE  post_type = 'product' AND post_status='publish'";

    $attr_condition = "";
    $attribute_value = "";
    if (!empty($_REQUEST['attribute_value']) && is_array($_REQUEST['attribute_value'])) {
        $attribute_value = implode(",", $_REQUEST['attribute_value']);
        $attribute_value = stripslashes($attribute_value);
    }
    if (!empty($_REQUEST['attribute_value_and']) && is_array($_REQUEST['attribute_value_and'])) {
        $attribute_value_and = implode(",", $_REQUEST['attribute_value_and']);
        $attribute_value_and = stripslashes($attribute_value_and);
        if ($attribute_value != "") {
            $attribute_value .= "," . $attribute_value_and;
        } else {
            $attribute_value = $attribute_value_and;
        }
    }
    if ($attribute_value != "") {
        $attr_condition = " CONCAT(taxonomy,':',slug)  in ({$attribute_value}) ";
    }
    $category_condition = "";
    if (!empty($_REQUEST['category']) && is_array($_REQUEST['category'])) {
        $selected_categories = $_REQUEST['category'];
        $cat_cond = "";
        $t_arr = array();
        if ($_REQUEST['sub_category'] == true) {
            while (!empty($selected_categories)) {
                $slug_name = $selected_categories[0];
                $slug_name = trim($slug_name, "\'");
                if ($cat_cond == "") {
                    $cat_cond = "'" . $slug_name . "'";
                } else {
                    $cat_cond = $cat_cond . ",'" . $slug_name . "'";
                }
                unset($selected_categories[0]);
                $t_arr = xa_subcats_from_parentcat_by_slug($slug_name);
                $selected_categories = array_merge($selected_categories, $t_arr);
            }
        } else {
            $category = implode(",", $_REQUEST['category']);
            $category = stripslashes($category);
            $cat_cond = $category;
        }
        $category_condition = " taxonomy='product_cat' AND slug  in ({$cat_cond}) ";
    }
    $product_type_condition = "";
    $type = "";
    if (!empty($_REQUEST['type'])) {
        foreach ($_REQUEST['type'] as $key => $val) {
            $type = stripslashes($val);
            if ($type == "'variation'") {
                $type = "'variable'";
            }
            if ($key == 0) {
                $product_type = $type;
            } else
                $product_type = $product_type . ',' . $type;
        }
        // $product_type = stripslashes($_REQUEST['type'][0]);
        $product_type_condition = " taxonomy='product_type'  AND slug  in ({$product_type}) ";
    } else {
        $product_type_condition = " taxonomy='product_type'  AND slug  in ('simple','variable') ";
    }

    if (!empty($attr_condition) && !empty($category_condition)) {
        $main_query = $sql . " AND " . $attr_condition . " AND ID IN (" . $sql . " AND " . $category_condition . " AND ID IN (" . $sql . " AND " . $product_type_condition . "))";
    } elseif (!empty($attr_condition) && empty($category_condition)) {
        $main_query = $sql . " AND " . $attr_condition . " AND ID IN (" . $sql . " AND " . $product_type_condition . ")";
    } elseif (!empty($category_condition) && empty($attr_condition)) {
        $main_query = $sql . " AND " . $category_condition . " AND ID IN (" . $sql . " AND " . $product_type_condition . ")";
    } else {
        $main_query = $sql . " AND " . $product_type_condition;
    }
    $result = $wpdb->get_results($main_query, ARRAY_A);
    $ids = wp_list_pluck($result, 'ID');
    if (empty($ids)) {
        return array(0);
    }
    return $ids;
}

//Get Subcategories
function xa_subcats_from_parentcat_by_slug($parent_cat_slug) {
    $ID_by_slug = get_term_by('slug', $parent_cat_slug, 'product_cat');
    $product_cat_ID = $ID_by_slug->term_id;
    $args = array(
        'hierarchical' => 1,
        'show_option_none' => '',
        'hide_empty' => 0,
        'parent' => $product_cat_ID,
        'taxonomy' => 'product_cat'
    );
    $subcats = get_categories($args);
    $temp_arr = array();
    foreach ($subcats as $sc) {
        array_push($temp_arr, $sc->slug);
    }
    return $temp_arr;
}
