/**
 * WooCommerce Email Center plugin Email scripts
 */
jQuery(document).ready(function() {

    /**
     * Toggle other recipient list visibility
     */
    function toggle_other_recipient_list_visibility()
    {
        if (jQuery('#rp_wcec_send_to_other').is(':checked')) {
            jQuery('#rp_wcec_other_recipient_list').closest('.rp_wcec_field').show();
        }
        else {
            jQuery('#rp_wcec_other_recipient_list').closest('.rp_wcec_field').hide();
            jQuery('#rp_wcec_other_recipient_list').find('option').remove();
        }
    }
    jQuery('#rp_wcec_send_to_other').on('change', toggle_other_recipient_list_visibility).change();

    /**
     * Set up recipient list field
     */
    jQuery('#rp_wcec_other_recipient_list').select2({
        width: '100%',
        tags: true,
        allowClear: true,
        placeholder: rp_wcec.labels.type_email,
        language: {
            noResults: function (params) {
                return rp_wcec.labels.continue_typing;
            }
        },
        createTag: function(term, data) {
            var value = term.term;
            if (validateEmail(value)) {
                return {
                    id: value,
                    text: value
                };
            }
            return null;
        }
    });

    /**
     * Validate email address
     */
    function validateEmail(email)
    {
        var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

});
