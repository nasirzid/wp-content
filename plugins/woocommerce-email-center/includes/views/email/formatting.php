<?php

/**
 * View for Email formatting panel
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

<div id="rp_wcec_email_formatting" class="rp_wcec">

    <div class="rp_wcec_settings_first_row">
        <div class="rp_wcec_field rp_wcec_field_full">
            <?php RP_WCEC_Form_Builder::select(array(
                'id'        => 'rp_wcec_content_type',
                'name'      => 'rp_wcec[content_type]',
                'value'     => $object->get_content_type(false),
                'options'   => RP_WCEC_Email::get_content_types(),
            )); ?>
        </div>
        <div class="rp_wcec_field rp_wcec_field_full">
            <?php RP_WCEC_Form_Builder::select(array(
                'id'        => 'rp_wcec_style',
                'name'      => 'rp_wcec[style]',
                'value'     => $object->get_style(),
                'options'   => RP_WCEC_Email::get_styles(),
            )); ?>
        </div>
        <div style="clear: both;"></div>
    </div>

</div>
