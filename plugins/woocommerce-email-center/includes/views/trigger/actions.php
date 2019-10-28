<?php

/**
 * View for Trigger edit page Actions block
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="rp_wcec">
    <div class="rp_wcec_actions">
        <select name="rp_wcec_trigger_actions">
            <?php foreach ($actions as $action_key => $action_title): ?>
                <option value="<?php echo $action_key; ?>"><?php echo $action_title; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="rp_wcec_actions_footer submitbox">
        <?php if (!empty($id)): ?>
            <div class="rp_wcec_object_delete">
                <?php if (RP_WCEC::is_admin()): ?>
                    <a class="submitdelete deletion" href="<?php echo esc_url(get_delete_post_link($id)); ?>"><?php echo (!EMPTY_TRASH_DAYS ? __('Delete Permanently', 'rp_wcec') : __('Move to Trash', 'rp_wcec')); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <button type="submit" class="button button-primary" title="<?php _e('Submit', 'rp_wcec'); ?>" name="rp_wcec_trigger_button" value="actions"><?php _e('Submit', 'rp_wcec'); ?></button>
    </div>
    <div style="clear: both;"></div>
</div>
