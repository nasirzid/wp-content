<?php

/**
 * View for Email settings panel
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

<div id="rp_wcec_email_settings" class="rp_wcec">

    <div class="rp_wcec_settings_first_row">
        <div class="rp_wcec_field rp_wcec_field_full">
            <?php RP_WCEC_Form_Builder::text(array(
                'id'        => 'rp_wcec_subject',
                'name'      => 'rp_wcec[subject]',
                'value'     => $object->get_subject(),
                'label'     => __('Subject', 'rp_wcec'),
                'required'  => 'required',
            )); ?>
        </div>
        <div class="rp_wcec_field rp_wcec_field_full">
            <?php RP_WCEC_Form_Builder::text(array(
                'id'        => 'rp_wcec_heading',
                'name'      => 'rp_wcec[heading]',
                'value'     => $object->get_heading(),
                'label'     => __('Heading', 'rp_wcec'),
            )); ?>
        </div>
        <div style="clear: both;"></div>
    </div>

</div>
