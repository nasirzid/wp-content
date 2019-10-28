<?php

/**
 * View for hidden conditions templates
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<!-- CONDITIONS TEMPLATES -->
<div id="rp_wcec_conditions_templates" style="display: none">

    <!-- NO CONDITIONS -->
    <div id="rp_wcec_no_conditions_template">
        <div class="rp_wcec_no_conditions"><?php _e('No conditions configured.', 'rp_wcec'); ?></div>
    </div>

    <!-- CONDITIONS WRAPPER -->
    <div id="rp_wcec_condition_wrapper_template">
        <div class="rp_wcec_condition_wrapper"></div>
    </div>

    <!-- CONDITION -->
    <div id="rp_wcec_condition_template">
        <div class="rp_wcec_condition">
            <div class="rp_wcec_condition_sort">
                <div class="rp_wcec_condition_sort_handle">
                    <i class="fa fa-bars"></i>
                </div>
            </div>

            <div class="rp_wcec_condition_content">
                <div class="rp_wcec_condition_setting rp_wcec_condition_setting_single rp_wcec_condition_setting_type">
                    <?php RP_WCEC_Form_Builder::grouped_select(array(
                        'id'        => 'rp_wcec_conditions{conditions_type}_type_{i}',
                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][type]',
                        'class'     => 'rp_wcec_condition_type',
                        'options'   => RP_WCEC_Conditions::conditions('trigger'),
                    )); ?>
                </div>

                <?php foreach(RP_WCEC_Conditions::conditions('trigger') as $group_key => $group): ?>
                    <?php foreach($group['options'] as $option_key => $option): ?>
                        <div class="rp_wcec_condition_setting_fields rp_wcec_condition_setting_fields_<?php echo $option_key ?>" style="display: none;">

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'meta_key')): ?>
                                <div class="rp_wcec_condition_setting_fields_single">
                                    <?php RP_WCEC_Form_Builder::text(array(
                                        'id'            => 'rp_wcec_conditions{conditions_type}_meta_key_{i}',
                                        'name'          => 'rp_wcec[conditions{conditions_type}][{i}][meta_key]',
                                        'class'         => 'rp_wcec_condition_meta_key',
                                        'placeholder'   => __('meta field key', 'rp_wcec'),
                                        'required'      => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'timeframe_all_time')): ?>
                                <div class="rp_wcec_condition_setting_fields_single">
                                    <?php RP_WCEC_Form_Builder::select(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_timeframe_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][timeframe]',
                                        'class'     => 'rp_wcec_condition_timeframe',
                                        'options'   => RP_WCEC_Conditions::timeframes(true),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key, true); ?>">
                                <?php RP_WCEC_Form_Builder::select(array(
                                    'id'        => 'rp_wcec_conditions{conditions_type}_' . $option_key . '_method_{i}',
                                    'name'      => 'rp_wcec[conditions{conditions_type}][{i}][' . $option_key . '_method]',
                                    'class'     => 'rp_wcec_condition_method',
                                    'options'   => RP_WCEC_Conditions::methods($option_key),
                                )); ?>
                            </div>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'timeframe')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::select(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_timeframe_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][timeframe]',
                                        'class'     => 'rp_wcec_condition_timeframe',
                                        'options'   => RP_WCEC_Conditions::timeframes(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'states')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_states_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][states][]',
                                        'class'     => 'rp_wcec_condition_states rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'countries')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_countries_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][countries][]',
                                        'class'     => 'rp_wcec_condition_countries rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'shipping_zones')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_shipping_zones_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][shipping_zones][]',
                                        'class'     => 'rp_wcec_condition_shipping_zones rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'order_statuses')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_order_statuses_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][order_statuses][]',
                                        'class'     => 'rp_wcec_condition_order_statuses rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'coupons')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_coupons_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][coupons][]',
                                        'class'     => 'rp_wcec_condition_coupons rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'payment_methods')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_payment_methods_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][payment_methods][]',
                                        'class'     => 'rp_wcec_condition_payment_methods rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'roles')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_roles_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][roles][]',
                                        'class'     => 'rp_wcec_condition_roles rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'capabilities')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_capabilities_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][capabilities][]',
                                        'class'     => 'rp_wcec_condition_capabilities rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'users')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_users_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][users][]',
                                        'class'     => 'rp_wcec_condition_users rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'attributes')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_attributes_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][attributes][]',
                                        'class'     => 'rp_wcec_condition_attributes rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'tags')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_tags_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][tags][]',
                                        'class'     => 'rp_wcec_condition_tags rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'products')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_products_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][products][]',
                                        'class'     => 'rp_wcec_condition_products rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'product_categories')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::multiselect(array(
                                        'id'        => 'rp_wcec_conditions{conditions_type}_product_categories_{i}',
                                        'name'      => 'rp_wcec[conditions{conditions_type}][{i}][product_categories][]',
                                        'class'     => 'rp_wcec_condition_product_categories rp_wcec_select2',
                                        'options'   => array(),
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'number')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::text(array(
                                        'id'            => 'rp_wcec_conditions{conditions_type}_number_{i}',
                                        'name'          => 'rp_wcec[conditions{conditions_type}][{i}][number]',
                                        'class'         => 'rp_wcec_condition_number',
                                        'placeholder'   => '0 ',
                                        'required'      => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'decimal')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>">
                                    <?php RP_WCEC_Form_Builder::text(array(
                                        'id'            => 'rp_wcec_conditions{conditions_type}_decimal_{i}',
                                        'name'          => 'rp_wcec[conditions{conditions_type}][{i}][decimal]',
                                        'class'         => 'rp_wcec_condition_decimal',
                                        'placeholder'   => '0.00',
                                        'required'      => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (RP_WCEC_Conditions::uses_field($option_key, 'text')): ?>
                                <div class="rp_wcec_condition_setting_fields_<?php echo RP_WCEC_Conditions::field_size($group_key, $option_key); ?>" <?php echo ((in_array($option_key, array('customer_meta_field'))) ? 'style="display: none;"' : ''); ?>>
                                    <?php RP_WCEC_Form_Builder::text(array(
                                        'id'            => 'rp_wcec_conditions{conditions_type}_text_{i}',
                                        'name'          => 'rp_wcec[conditions{conditions_type}][{i}][text]',
                                        'class'         => 'rp_wcec_condition_text',
                                        'placeholder'   => ($option_key === 'postcode' ? '90210, 902**, 90200-90299, SW1A 1AA, NSW 2001' : ''),
                                        'required'      => 'required',
                                    )); ?>
                                </div>
                            <?php endif; ?>

                            <div style="clear: both;"></div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <div style="clear: both;"></div>
            </div>

            <div class="rp_wcec_condition_remove">
                <div class="rp_wcec_condition_remove_handle">
                    <i class="fa fa-times"></i>
                </div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</div>
