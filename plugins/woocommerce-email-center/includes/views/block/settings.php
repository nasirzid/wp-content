<?php

/**
 * View for Block settings panel
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

<div id="rp_wcec_block_settings" class="rp_wcec">

    <div class="rp_wcec_settings_first_row">
        <div class="rp_wcec_field rp_wcec_field_full">
            <?php RP_WCEC_Form_Builder::text(array(
                'id'        => 'rp_wcec_title',
                'name'      => 'rp_wcec[title]',
                'value'     => $object->get_title(),
                'label'     => __('Private Title', 'rp_wcec'),
                'required'  => 'required',
            )); ?>
        </div>
        <div style="clear: both;"></div>
    </div>

</div>
