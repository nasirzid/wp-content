/**
 * WooCommerce Email Center plugin Schedule control scripts
 */
jQuery(document).ready(function() {

    /**
     * Schedule input control
     */
    function schedule_input_changed()
    {
        var method = jQuery('#rp_wcec_schedule_method').val();

        if (typeof method === 'undefined') {
            return;
        }

        // Change size of method field
        var remove_class = method === 'send_immediately' ? 'single' : 'double';
        var add_class = method === 'send_immediately' ? 'double' : 'single';
        jQuery('#rp_wcec_schedule_method').parent().removeClass('rp_wcec_field_' + remove_class);
        jQuery('#rp_wcec_schedule_method').parent().addClass('rp_wcec_field_' + add_class);

        // Show or hide date field
        var display = method === 'specific_date' ? 'block' : 'none';
        jQuery('#rp_wcec_schedule_date').parent().css('display', display);
        jQuery('#rp_wcec_schedule_date').parent().find('input').prop('disabled', (display === 'none'));

        if (display === 'none') {
            clear_field_value(jQuery('#rp_wcec_schedule_date'));
            clear_field_value(jQuery('#rp_wcec_schedule_date_display'));
        }

        // Show or hide next day field
        display = method === 'next' ? 'block' : 'none';
        jQuery('#rp_wcec_schedule_weekday').parent().css('display', display);
        jQuery('#rp_wcec_schedule_weekday').prop('disabled', (display === 'none'));

        if (display === 'none') {
            clear_field_value(jQuery('#rp_wcec_schedule_weekday'));
        }

        // Show or hide number input field
        display = ['send_immediately', 'specific_date', 'next'].indexOf(method) > -1 ? 'none' : 'block';
        jQuery('#rp_wcec_schedule_number').parent().css('display', display);
        jQuery('#rp_wcec_schedule_number').prop('disabled', (display === 'none'));

        if (display === 'none') {
            clear_field_value(jQuery('#rp_wcec_schedule_number'));
        }
    }

    jQuery('#rp_wcec_schedule_method').change(function() {
        schedule_input_changed();
    });
    schedule_input_changed();

    /**
     * HELPER
     * Clear field value
     */
    function clear_field_value(field)
    {
        if (field.is('select')) {
            field.prop('selectedIndex', 0);
        }
        else if (field.is(':radio, :checkbox')) {
            field.removeAttr('checked');
        }
        else {
            field.val('');
        }
    }

});
