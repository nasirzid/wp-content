<?php

/**
 * View for Email private note panel
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

<div id="rp_wcec_email_private_note" class="rp_wcec">

    <div class="rp_wcec_settings_first_row">
        <div class="rp_wcec_field rp_wcec_field_full">
            <?php RP_WCEC_Form_Builder::textarea(array(
                'id'        => 'rp_wcec_note',
                'name'      => 'rp_wcec[note]',
                'value'     => $object->get_note(),
            )); ?>
        </div>
        <div style="clear: both;"></div>
    </div>

</div>
