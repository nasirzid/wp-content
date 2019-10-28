/**
 * WooCommerce Email Center plugin Conditions control scripts
 */
jQuery(document).ready(function() {

    /**
     * Iterate over elements and set up view
     */
    if (typeof rp_wcec_object_config === 'object') {

        // Set up both simple conditions and scheduled conditions
        jQuery.each(['conditions', 'conditions_scheduled'], function(key, value) {
            var config = object_key_check(rp_wcec_object_config, value) ? rp_wcec_object_config[value] : [];
            var conditions_type = (value === 'conditions_scheduled' ? '_scheduled' : '');
            set_up_parent(config, conditions_type);        });
    }
    else {
        alert('Error: Unable to load Conditions configuration. Please reload this page.');
    }

    /**
     * Set up parent element
     */
    function set_up_parent(config, conditions_type)
    {
        jQuery('.rp_wcec_settings_conditions' + conditions_type + '_row .rp_wcec_inner_wrapper').each(function() {

            // Nothing configured yet?
            if (config.length === 0) {

                add_no_conditions_notice(jQuery(this));
            }
            // At least one row exists
            else {

                // Iterate over list of conditions and add them to UI
                for (var i in config) {
                    add_row(config[i], conditions_type);
                }

                // Fix field identifiers
                fix_rows(conditions_type);

                // Fix field values
                fix_values(conditions_type, false);

                // Fix condition
                jQuery('.rp_wcec_settings_conditions' + conditions_type + '_row .rp_wcec_inner_wrapper .rp_wcec_condition').each(function() {
                    fix_condition(jQuery(this));
                });

                // Fix condition visibility
                toggle_condition_visibility(conditions_type);
            }

            // Fix conditions scheduled visibility
            if (conditions_type === '_scheduled') {
                toggle_conditions_scheduled_visibility();
            }

            // Bind click action
            jQuery('.rp_wcec_settings_conditions' + conditions_type + '_row .rp_wcec_add_condition button').click(function() {
                add_row(false, conditions_type);
            });
        });
    }

    /**
     * Add no conditions notice
     */
    function add_no_conditions_notice(selector)
    {
        // Make sure it does not exist
        if (jQuery(selector).find('.rp_wcec_no_conditions').length < 1) {
            prepend(selector, 'no_conditions');
        }
    }

    /**
     * Remove no conditions notice
     */
    function remove_no_conditions_notice(selector)
    {
        jQuery(selector).find('.rp_wcec_no_conditions').remove();
    }

    /**
     * Add wrapper
     */
    function add_wrapper(conditions_type)
    {
        // Make sure we don't have one yet before proceeding
        if (jQuery('.rp_wcec_settings_conditions' + conditions_type + '_row .rp_wcec_inner_wrapper .rp_wcec_condition_wrapper').length === 0) {

            // Add wrapper
            prepend('.rp_wcec_settings_conditions' + conditions_type + '_row .rp_wcec_inner_wrapper', 'condition_wrapper');

            // Make it sortable
            jQuery('.rp_wcec_settings_conditions' + conditions_type + '_row .rp_wcec_inner_wrapper .rp_wcec_condition_wrapper').sortable({
                axis:       'y',
                handle:     '.rp_wcec_condition_sort_handle',
                opacity:    0.7,
                stop: function(event, ui) {

                    // Remove styles added by jQuery UI
                    jQuery(this).find('.rp_wcec_condition').each(function() {
                        jQuery(this).removeAttr('style');
                    });

                    // Fix ids, names etc
                    fix_rows(conditions_type);
                }
            });
        }
    }

    /**
     * Remove wrapper
     */
    function remove_wrapper(selector)
    {
        jQuery(selector).find('.rp_wcec_condition_wrapper').remove();
    }

    /**
     * Add one row
     */
    function add_row(config, conditions_type)
    {
        var selector = '.rp_wcec_settings_conditions' + conditions_type + '_row .rp_wcec_inner_wrapper .rp_wcec_condition_wrapper';

        // Add wrapper
        add_wrapper(conditions_type);

        // Make sure we don't have the no conditions notice
        remove_no_conditions_notice('.rp_wcec_settings_conditions' + conditions_type + '_row .rp_wcec_inner_wrapper');

        // Add row element
        append(selector, 'condition', {conditions_type: conditions_type});

        // Select current row
        var row = jQuery(selector).children().last();
        var row_key = jQuery(selector).children().length - 1;

        // Fix identifiers, values and visibility
        if (config === false) {
            fix_rows(conditions_type);
            fix_values(conditions_type, true, row, row_key);
            fix_condition(row);
            toggle_condition_visibility(conditions_type, row);
        }

        // Handle delete action
        jQuery('.rp_wcec_settings_conditions' + conditions_type + '_row .rp_wcec_condition_remove_handle').last().click(function() {
            remove_row(jQuery(this).closest('.rp_wcec_condition'), conditions_type);
        });
    }

    /**
     * Remove one row
     */
    function remove_row(row, conditions_type)
    {
        // Last row? Remove the entire wrapper and add no conditions notice
        if (row.closest('.rp_wcec_condition_wrapper').children().length < 2) {
            add_no_conditions_notice(row.closest('.rp_wcec_inner_wrapper'));
            remove_wrapper('.rp_wcec_settings_conditions' + conditions_type + '_row');
        }

        // Remove single row and fix ids
        else {
            row.remove();
            fix_rows(conditions_type);
        }
    }

    /**
     * Fix attributes
     */
    function fix_rows(conditions_type)
    {
        var i = 0;  // Row identifier

        // Iterate over rows
        jQuery('.rp_wcec_settings_conditions' + conditions_type + '_row .rp_wcec_inner_wrapper .rp_wcec_condition').each(function() {

            // Iterate over all field elements of this element
            jQuery(this).find('input, select').each(function() {

                // Attribute id
                if (typeof jQuery(this).prop('id') !== 'undefined') {
                    var new_value = jQuery(this).prop('id').replace(/(\{i\}|\d+)?$/, i);
                    jQuery(this).prop('id', new_value);
                }

                // Attribute name
                if (typeof jQuery(this).prop('name') !== 'undefined') {
                    var new_value = jQuery(this).prop('name').replace(new RegExp('rp_wcec\\[conditions' + conditions_type + '\\]\\[(\\{i\\}|\\d+)\\]?'), 'rp_wcec[conditions' + conditions_type + '][' + i + ']');
                    jQuery(this).prop('name', new_value);
                }
            });

            // Iterate over all label elements of this element
            jQuery(this).find('label').each(function() {

                // Attribute for
                if (typeof jQuery(this).prop('for') !== 'undefined' && jQuery(this).prop('for').length) {
                    var new_value = jQuery(this).prop('for').replace(/(\{i\}|\d+)?$/, i);
                    jQuery(this).prop('for', new_value);
                }
            });

            // Increment row identifier
            i++;
        });
    }

    /**
     * Fix field values
     */
    function fix_values(conditions_type, is_new, row, row_key)
    {
        // Row identifiers
        var i = typeof row_key !== 'undefined' ? row_key : 0;

        // Get rows to fix values for
        var rows = typeof row !== 'undefined' ? [row] : jQuery('.rp_wcec_settings_conditions' + conditions_type + '_row .rp_wcec_inner_wrapper .rp_wcec_condition');

        // Iterate over child rows
        jQuery.each(rows, function() {

            var current_row = jQuery(this);

            // Iterate over all field elements of current element
            current_row.find('input, select').each(function() {

                // Get field key
                var field_key = jQuery(this).prop('id').replace(new RegExp('^rp_wcec_conditions' + conditions_type + '_'), '').replace(/(_\d+)?$/, '');

                // Detect no longer available custom conditions
                if (field_key === 'type' && object_key_check(rp_wcec_object_config, ('conditions' + conditions_type), i, field_key)) {
                    if (current_row.find('#rp_wcec_conditions' + conditions_type + '_' + rp_wcec_object_config[('conditions' + conditions_type)][i][field_key] + '_method_' + i).length === 0) {
                        jQuery(this).closest('.rp_wcec_condition_content').html('<div class="rp_wcec_condition_not_available">' + rp_wcec_trigger.notices.condition_not_available + '</div>');
                    }
                }

                // Select options in select fields
                if (jQuery(this).is('select')) {

                    if (!is_new && rp_wcec_object_config !== false && object_key_check(rp_wcec_object_config, ('conditions' + conditions_type), i, field_key) && rp_wcec_object_config[('conditions' + conditions_type)][i][field_key]) {
                        if (is_multiselect(jQuery(this))) {
                            if (object_key_check(rp_wcec_object_multiselect_options, ('conditions' + conditions_type), i) && typeof rp_wcec_object_multiselect_options[('conditions' + conditions_type)][i][field_key] === 'object') {
                                for (var k = 0; k < rp_wcec_object_config[('conditions' + conditions_type)][i][field_key].length; k++) {
                                    var all_options = rp_wcec_object_multiselect_options[('conditions' + conditions_type)][i][field_key];
                                    var current_option_key = rp_wcec_object_config[('conditions' + conditions_type)][i][field_key][k];

                                    for (var l = 0; l < all_options.length; l++) {
                                        if (object_key_check(all_options, l, 'id') && all_options[l]['id'] == current_option_key) {
                                            var current_option_label = all_options[l]['text'];
                                            jQuery(this).append(jQuery('<option></option>').attr('value', current_option_key).prop('selected', true).text(current_option_label));
                                        }
                                    }
                                }
                            }
                        }
                        else {
                            jQuery(this).val(rp_wcec_object_config[('conditions' + conditions_type)][i][field_key]);
                        }
                    }
                }

                // Add value for text input fields
                else if (typeof jQuery(this).prop('value') !== 'undefined') {
                    if (!is_new && rp_wcec_object_config !== false && object_key_check(rp_wcec_object_config, ('conditions' + conditions_type), i, field_key)) {
                        jQuery(this).prop('value', rp_wcec_object_config[('conditions' + conditions_type)][i][field_key]);
                    }
                    else {
                        jQuery(this).removeAttr('value');
                    }
                }

                // Initialize select2
                if (jQuery(this).hasClass('rp_wcec_select2') && !jQuery(this).data('select2')) {
                    initialize_select2(jQuery(this));
                }
            });

            // Increment element identifier
            i++;
        });
    }

    /**
     * Initialize select2 on one element
     */
    function initialize_select2(element)
    {
        // Currently only multiselect fields are converted
        if (!is_multiselect(element)) {
            return;
        }

        // Make sure our Select2 reference is set
        if (typeof RP_Select2 === 'undefined') {
            return;
        }

        // Initialize Select2
        RP_Select2.call(element, {
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url:        rp_wcec.ajaxurl,
                type:       'POST',
                dataType:   'json',
                delay:      250,
                data: function(params) {
                    return {
                        query:      params.term,
                        action:     'rp_wcec_load_multiselect_items',
                        type:       parse_multiselect_subject(element),
                        selected:   element.val()
                    };
                },
                dataFilter: function(raw_response) {
                    return parse_ajax_json_response(raw_response, true);
                },
                processResults: function(data) {
                    return {
                        results: data.items
                    };
                }
            }
        });
    }

    /**
     * Parse multiselect field subject
     */
    function parse_multiselect_subject(element)
    {
        var subject = '';

        jQuery.each(element.attr('class').split(/\s+/), function(index, item) {
            if (item.indexOf('rp_wcec_condition_') > -1) {
                subject = item.replace('rp_wcec_condition_', '');
                return;
            }
        });

        return subject;
    }

    /**
     * Fix condition
     */
    function fix_condition(element)
    {
        // Condition type
        element.find('.rp_wcec_condition_type').change(function() {
            toggle_condition_fields(element);
        });
        toggle_condition_fields(element);

        // Meta field condition
        element.find('.rp_wcec_condition_method').change(function() {
            fix_meta_field_condition(element);
        });
        fix_meta_field_condition(element);
    }

    /**
     * Toggle visibility of condition fields
     */
    function toggle_condition_fields(element)
    {
        // Get current condition type
        var current_type = element.find('.rp_wcec_condition_type').val();

        // Show only fields related to current type
        element.find('.rp_wcec_condition_setting_fields').each(function() {

            // Show or hide fields
            var displayed = jQuery(this).hasClass('rp_wcec_condition_setting_fields_' + current_type);
            jQuery(this).css('display', (displayed ? 'block' : 'none'));

            // Iterate over all form elements
            jQuery(this).find('input, select').each(function() {

                // Enable/disable fields
                jQuery(this).prop('disabled', !displayed);

                // Clear field values
                if (!displayed) {
                    clear_field_value(jQuery(this));
                }
            });
        });

        // Fix meta field condition
        fix_meta_field_condition(element);
    }

    /**
     * Fix fields of meta field condition
     */
    function fix_meta_field_condition(element)
    {
        var condition_type = element.find('.rp_wcec_condition_type').val();

        // Only proceed if condition type is meta field
        if (condition_type !== 'customer_meta_field') {
            return;
        }

        // Get current method
        var current_method = element.find('.rp_wcec_condition_setting_fields_' + condition_type + ' .rp_wcec_condition_method').val();

        // Proceed depending on current method
        if (jQuery.inArray(current_method, ['is_empty', 'is_not_empty', 'is_checked', 'is_not_checked']) !== -1) {
            element.find('.rp_wcec_condition_setting_fields_' + condition_type).find('select').parent().removeClass('rp_wcec_condition_setting_fields_single').addClass('rp_wcec_condition_setting_fields_double');
            element.find('.rp_wcec_condition_setting_fields_' + condition_type + ' .rp_wcec_condition_text').prop('disabled', true).parent().css('display', 'none');
            clear_field_value(element.find('.rp_wcec_condition_setting_fields_' + condition_type + ' .rp_wcec_condition_text'));
        }
        else {
            element.find('.rp_wcec_condition_setting_fields_' + condition_type).find('select').parent().removeClass('rp_wcec_condition_setting_fields_double').addClass('rp_wcec_condition_setting_fields_single');
            element.find('.rp_wcec_condition_setting_fields_' + condition_type).find('.rp_wcec_condition_text').prop('disabled', false).parent().css('display', 'block');
        }
    }

    /**
     * Enable/disable condition types based on trigger event
     */
    function toggle_condition_visibility(conditions_type, row)
    {
        // Get selected trigger event
        var selected_event = jQuery('select#rp_wcec_event').val();

        // Get defined support by current event
        var supported = object_key_check(rp_wcec_event_supported_conditions, selected_event) ? rp_wcec_event_supported_conditions[selected_event] : [];

        // Get condition type fields by context
        var fields = row ? row.find('select.rp_wcec_condition_type') : jQuery('.rp_wcec_settings_conditions' + conditions_type + '_row select.rp_wcec_condition_type');

        // Iterate over condition type fields
        fields.each(function() {

            // Get selected condition type
            var selected_type = row ? null : jQuery(this).val();

            // If selected condition type is not supported, then remove the entire condition
            if (selected_type !== null && (typeof supported[selected_type] === 'undefined' || supported[selected_type].indexOf(('conditions' + conditions_type)) < 0)) {
                remove_row(jQuery(this).closest('.rp_wcec_condition'), conditions_type);
            }
            // Otherwise just disable or enable options
            else {

                var first_selected = false;

                // Iterate over all options
                jQuery(this).find('option').each(function() {

                    // Enable condition if it's in the supported list and is supported by schedule type
                    if (typeof supported[jQuery(this).val()] !== 'undefined' && supported[jQuery(this).val()].indexOf(('conditions' + conditions_type)) > -1) {
                        jQuery(this).prop('disabled', false);
                        jQuery(this).show();

                        // Select first visible option in case of new condition row
                        if (row && !first_selected) {
                            jQuery(this).prop('selected', true);
                            first_selected = true;
                        }
                    }
                    // Otherwise disable it
                    else {
                        jQuery(this).prop('disabled', true);
                        jQuery(this).hide();
                    }
                });

                // Now toggle visibility of options groups
                jQuery(this).find('optgroup').each(function() {

                    // Show if optgroup has at least one option
                    if (jQuery(this).find('option').filter(function() { return !this.disabled; }).length) {
                        jQuery(this).prop('disabled', false);
                        jQuery(this).show();
                    }
                    // Otherwise disable it
                    else {
                        jQuery(this).prop('disabled', true);
                        jQuery(this).hide();
                    }
                });

                // Now check all options and remove condition row if there are no supported options left
                if (jQuery(this).find('option').filter(function() { return !this.disabled; }).length === 0) {
                    remove_row(jQuery(this).closest('.rp_wcec_condition'), conditions_type);
                }
                // Make sure that proper condition fields are displayed
                else {
                    fix_condition(jQuery(this).closest('.rp_wcec_condition'));
                }
            }
        });
    }
    jQuery('select#rp_wcec_event').change(function() {
        jQuery.each(['conditions', 'conditions_scheduled'], function(key, value) {
            var conditions_type = (value === 'conditions_scheduled' ? '_scheduled' : '');
            toggle_condition_visibility(conditions_type);
        });
    });

    /**
     * Toggle conditions scheduled visibility
     */
    function toggle_conditions_scheduled_visibility()
    {
        // Hide conditions scheduled row
        if (jQuery('select#rp_wcec_schedule_method').val() === 'send_immediately') {

            // First delete all conditions in it and add no conditions notice
            add_no_conditions_notice('.rp_wcec_settings_conditions_scheduled_row .rp_wcec_inner_wrapper');
            remove_wrapper('.rp_wcec_settings_conditions_scheduled_row');

            // Hide entire row
            jQuery('.rp_wcec_settings_conditions_scheduled_row').hide();
        }
        // Display conditions scheduled row
        else {
            jQuery('.rp_wcec_settings_conditions_scheduled_row').show();
        }
    }
    jQuery('select#rp_wcec_schedule_method').change(function() {
        toggle_conditions_scheduled_visibility();
    });

    /**
     * Parse Ajax JSON response
     */
    function parse_ajax_json_response(response, return_raw_data)
    {
        // Check if we need to return parsed object or potentially fixed raw data
        var return_raw_data = (typeof return_raw_data !== 'undefined') ?  return_raw_data : false;

        try {

            // Attempt to parse data
            var parsed = jQuery.parseJSON(response);

            // Return appropriate value
            return return_raw_data ? response : parsed;
        }
        catch (e) {

            // Attempt to fix malformed JSON string
            var valid_response = response.match(/{"result.*"}]}/);

            // Check if we were able to fix it
            if (valid_response !== null) {
                response = valid_response[0];
            }
        }

        // Second attempt to parse response data
        return return_raw_data ? response : jQuery.parseJSON(response);
    }

    /**
     * We are done by now, remove preloader
     */
    jQuery('#rp_wcec_preloader').remove();




    /**
     * HELPER
     * Nested object key existence check
     */
    function object_key_check(object /*, key_1, key_2... */)
    {
        // First check if provided variable is object
        if (typeof object !== 'object') {
            return false;
        }

        // Get object keys and set current level
        var keys = Array.prototype.slice.call(arguments, 1);
        var current = object;

        // Iterate over keys
        for (var i = 0; i < keys.length; i++) {

            // Check if current key exists
            if (typeof current[keys[i]] === 'undefined') {
                return false;
            }

            // Check if all but last keys are for object
            if (i < (keys.length - 1) && typeof current[keys[i]] !== 'object') {
                return false;
            }

            // Go one step down
            current = current[keys[i]];
        }

        // If we reached this point all keys from path
        return true;
    }

    /**
     * HELPER
     * Append template with values to selected element's content
     */
    function append(selector, template, values)
    {
        var html = get_template(template, values);

        if (typeof selector === 'object') {
            selector.append(html);
        }
        else {
            jQuery(selector).append(html);
        }
    }

    /**
     * HELPER
     * Prepend template with values to selected element's content
     */
    function prepend(selector, template, values)
    {
        var html = get_template(template, values);

        if (typeof selector === 'object') {
            selector.prepend(html);
        }
        else {
            jQuery(selector).prepend(html);
        }
    }

    /**
     * HELPER
     * Get template's html code
     */
    function get_template(template, values)
    {
        return populate_template(jQuery('#rp_wcec_' + template + '_template').html(), values);
    }

    /**
     * HELPER
     * Populate template with values
     */
    function populate_template(template, values)
    {
        for (var key in values) {
            template = replace_macro(template, key, values[key]);
        }

        return template;
    }

    /**
     * HELPER
     * Replace all instances of macro in string
     */
    function replace_macro(string, macro, value)
    {
        var macro = '{' + macro + '}';
        var regex = new RegExp(macro, 'g');
        return string.replace(regex, value);
    }

    /**
     * HELPER
     * Check if HTML element is multiselect field
     */
    function is_multiselect(element)
    {
        return (element.is('select') && typeof element.attr('multiple') !== 'undefined' && element.attr('multiple') !== false);
    }

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
