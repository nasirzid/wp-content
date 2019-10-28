<?php

/**
 * View for Email recipients panel
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<style type="text/css">
    #submitdiv {
        display: none;
    }
</style>

<div id="rp_wcec_email_recipients" class="rp_wcec">

    <div class="rp_wcec_settings_first_row">
        <div class="rp_wcec_field rp_wcec_field_checkbox">
            <?php RP_WCEC_Form_Builder::checkbox(array(
                'id'        => 'rp_wcec_send_to_shop_manager',
                'name'      => 'rp_wcec[send_to_shop_manager]',
                'value'     => '1',
                'checked'   => (bool) $object->get_send_to_shop_manager(),
            )); ?>
            <label for="rp_wcec_send_to_shop_manager" style="font-weight: normal; display: inline-block;"><?php _e('Shop Manager', 'rp_wcec'); ?></label><br>
            <?php RP_WCEC_Form_Builder::checkbox(array(
                'id'        => 'rp_wcec_send_to_customer',
                'name'      => 'rp_wcec[send_to_customer]',
                'value'     => '1',
                'checked'   => (bool) $object->get_send_to_customer(),
            )); ?>
            <label for="rp_wcec_send_to_customer" style="font-weight: normal; display: inline-block;"><?php _e('Customer', 'rp_wcec'); ?></label><br>
            <?php RP_WCEC_Form_Builder::checkbox(array(
                'id'        => 'rp_wcec_send_to_other',
                'name'      => 'rp_wcec[send_to_other]',
                'value'     => '1',
                'checked'   => (bool) $object->get_send_to_other(),
            )); ?>
            <label for="rp_wcec_send_to_other" style="font-weight: normal; display: inline-block;"><?php _e('Email Addresses', 'rp_wcec'); ?></label>
        </div>

        <div class="rp_wcec_field rp_wcec_field_full rp_wcec_field_other_recipient_list" style="display: none;">
            <?php RP_WCEC_Form_Builder::multiselect(array(
                'id'        => 'rp_wcec_other_recipient_list',
                'name'      => 'rp_wcec[other_recipient_list][]',
                'selected'  => $other_recipient_list,
                'options'   => array_combine($other_recipient_list, $other_recipient_list),
            )); ?>
        </div>
        <div style="clear: both;"></div>
    </div>

</div>
