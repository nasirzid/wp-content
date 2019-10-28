/**
 * WooCommerce Email Center plugin Trigger scripts
 */
jQuery(document).ready(function() {

    /**
     * Clear Scheduled Emails confirmation
     */
    jQuery('form#post').submit(function(e) {

        var action = jQuery(this).find('select[name="rp_wcec_trigger_actions"]').val();

        if (action === 'clear_scheduled' && !confirm(rp_wcec_trigger.notices.clear_scheduled_confirmation)) {
            e.preventDefault();
        }
    });

});
