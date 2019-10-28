<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods of this class overrides WooCommerce default templates
 *
 * Note: this is here only for backwards compatibility and
 * will be removed after July 1, 2017
 * 
 * @class RP_WCEC_WC
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_WC')) {

class RP_WCEC_WC
{

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Hook for template override
        add_filter('wc_get_template', array($this, 'get_template'), 9999, 5);

        // Hook for inline style override
        add_filter('woocommerce_email_styles', array($this, 'get_styles'), 9999);
    }

    /**
     * Maybe override WooCommerce email template
     *
     * Note: this functionality is no longer a part of this extenion, it is left
     * here only for backwards compatibility purposes
     *
     * @access public
     * @param string $located
     * @param string $template_name
     * @param array $args
     * @param string $template_path
     * @param string $default_path
     * @return string
     */
    public function get_template($located, $template_name, $args, $template_path, $default_path)
    {
        // Check if we still need to use this functionality
        if (!RP_WCEC_Styling::are_styling_options_in_use() || !RP_WCEC_Styling::are_wc_templates_overriden()) {
            return $located;
        }

        $custom_template = RP_WCEC_PLUGIN_PATH . 'templates/' . $template_name;

        if (self::override_woocommerce_templates() && file_exists($custom_template)) {
            return $custom_template;
        }

        return $located;
    }

    /**
     * Maybe override WooCommerce email styles
     *
     * Note: this functionality is no longer a part of this extenion, it is left
     * here only for backwards compatibility purposes
     *
     * @access public
     * @param string $styles
     * @return string
     */
    public function get_styles($styles)
    {
        // Check if we still need to use this functionality
        if (!RP_WCEC_Styling::are_styling_options_in_use() || !RP_WCEC_Styling::is_styler_used()) {
            return $styles;
        }

        // Check if this option was enabled in settings
        if (self::override_woocommerce_templates()) {
            return RP_WCEC_Styling::get_styles_string();
        }

        return $styles;
    }

    /**
     * Check if we need to override default WooCommerce email templates
     *
     * @access public
     * @return bool
     */
    public static function override_woocommerce_templates()
    {
        // Check environment
        if (!RP_WCEC::check_environment()) {
            return false;
        }

        // Override default WooCommerce email templates if set so in plugin options
        if (RP_WCEC::opt('override_woocommerce_templates')) {
            return true;
        }

        // Override templates (e.g. header, footer) for custom emails
        if (isset($GLOBALS['rp_wcec_dispatching_custom_email']) && $GLOBALS['rp_wcec_dispatching_custom_email'] === 1) {
            return true;
        }

        // Override if this is email preview request
        if (isset($GLOBALS['rp_wcec_email_prevew_request']) && $GLOBALS['rp_wcec_email_prevew_request'] === 1) {
            return true;
        }

        return false;
    }


}

new RP_WCEC_WC();

}
