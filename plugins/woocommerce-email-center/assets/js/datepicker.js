/**
 * WooCommerce Email Center plugin Datepicker scripts
 */
jQuery(document).ready(function() {

    /**
     * Datepicker
     */
    jQuery('.rp_wcec_date').each(function() {
        jQuery(this).datepicker(rp_wcec_datepicker_config);
    });

});
