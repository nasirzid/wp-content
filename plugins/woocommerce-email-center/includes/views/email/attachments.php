<?php

/**
 * View for Email attachments panel
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

<div id="rp_wcec_email_attachments" class="rp_wcec">

    <div class="rp_wcec_settings_first_row">

        <?php if ($object->has_attachments()): ?>
            <ul class="rp_wcec_attachment_list">
                <?php foreach ($object->get_attachments() as $attachment_key => $attachment): ?>
                    <li><?php echo $attachment; ?><a href="<?php echo admin_url('/?wcec_file_delete=' . $attachment_key . '&object=' . $object->id); ?>"><i class="fa fa-times rp_wcec_attachment_remove"></i></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="rp_wcec_field rp_wcec_field_full">
            <?php RP_WCEC_Form_Builder::file(array(
                'id'        => 'rp_wcec_attachment',
                'name'      => 'rp_wcec[attachment]',
            )); ?>
        </div>
        <div style="clear: both;"></div>
    </div>

</div>
