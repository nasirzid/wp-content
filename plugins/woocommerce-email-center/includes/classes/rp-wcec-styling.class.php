<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Styling class
 *
 * Note: this is here only for backwards compatibility, this functionality has
 * since been extracted to a separate plugin which is available for free:
 * https://wordpress.org/plugins/decorator-woocommerce-email-customizer/
 * https://github.com/RightPress/decorator
 *
 * @class RP_WCEC_Styling
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_Styling')) {

class RP_WCEC_Styling
{

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Set up after plugins are loaded
        add_action('plugins_loaded', array($this, 'plugins_loaded_setup'), 1);
    }

    /**
     * Set up after plugins are loaded
     *
     * @access public
     * @return void
     */
    public function plugins_loaded_setup()
    {
        // Check environment
        if (!RP_WCEC::check_environment()) {
            return;
        }

        // Maybe display site wide notification
        add_action('admin_notices', array($this, 'maybe_display_notification'));

        // Dismiss site wide notification
        if (!empty($_REQUEST['rp_wcec_dismiss_styling_notification']) && $_REQUEST['rp_wcec_dismiss_styling_notification'] && RP_WCEC::is_admin()) {
            RP_WCEC_Styling::dismiss_styling_notification();
        }

        // Migrate Styler settings to Decorator
        if (!empty($_REQUEST['rp_wcec_migrate_styles_to_decorator']) && $_REQUEST['rp_wcec_migrate_styles_to_decorator'] && RP_WCEC::is_admin()) {
            RP_WCEC_Styling::migrate_styles_to_decorator();
        }

        // Discard all styling settings
        if (!empty($_REQUEST['rp_wcec_discard_styles']) && $_REQUEST['rp_wcec_discard_styles'] && RP_WCEC::is_admin()) {
            RP_WCEC_Styling::disable_styling_features();
        }

        // Discard all styling settings on first Decorator settings update
        add_action('customize_save_rp_decorator', array($this, 'decorator_plugin_detected'));

        // Add menu link
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    /**
     * Decorator plugin detected
     *
     * @access public
     * @return void
     */
    public function decorator_plugin_detected()
    {
        RP_WCEC_Styling::disable_styling_features(false);
    }

    /**
     * Maybe display site wide notification
     *
     * @access public
     * @return void
     */
    public static function maybe_display_notification()
    {
        if (RP_WCEC_Styling::are_styling_options_in_use() && (RP_WCEC_Styling::is_styler_used() || RP_WCEC_Styling::are_templates_customized()) && RP_WCEC::is_admin() && !get_option('rp_wcec_styling_notification_dismissed') && (!isset($_REQUEST['page']) || $_REQUEST['page'] !== 'wcec_styling')) {
            echo '<div class="notice notice-error" style="padding-bottom: 0.6em;"><h3 style="margin-top: 0.9em; margin-bottom: 0.9em;">Important Notice</h3><p>WooCommerce email styling features were removed from <strong>WooCommerce Email Center</strong> and you may need to take action.</p><p>Please head to the <a href="' . admin_url('edit.php?post_type=wcec_email&page=wcec_styling') . '">Styling page</a> to learn more.</p><p><small><a href="' . add_query_arg('rp_wcec_dismiss_styling_notification', '1') . '">Hide This Notice</a></small></p></div>';
        }
    }

    /**
     * Dismiss styling notification
     *
     * @access public
     * @return void
     */
    public static function dismiss_styling_notification()
    {
        // Add flag
        update_option('rp_wcec_styling_notification_dismissed', 1);

        // Redirect
        wp_redirect(remove_query_arg('rp_wcec_dismiss_styling_notification'));
        exit;
    }

    /**
     * Migrate Styler settings to Decorator plugin
     *
     * @access public
     * @return void
     */
    public static function migrate_styles_to_decorator()
    {
        $decorator_settings = array();

        // Define keys that do not match
        $key_mapping = array(
            'footer_text_text_align'                => 'footer_text_align',
            'footer_text_font_size'                 => 'footer_font_size',
            'footer_text_color'                     => 'footer_color',
            'footer_text_font_weight'               => 'footer_font_weight',
            'items_table_totals_separator_color'    => 'items_table_separator_color',
            'items_table_totals_separator_width'    => 'items_table_separator_width',
        );

        // Iterate over styler settings
        foreach (RP_WCEC_Styling::get_styler_settings() as $setting_key => $setting) {

            // Get stored value
            $value = RP_WCEC_Styling::get_styler_value($setting_key, false);

            // Value does not exist
            if ($value === false) {
                continue;
            }

            // Value matches default value
            if ($value === RP_WCEC_Styling::get_default_value($setting_key)) {
                continue;
            }

            // Key mapping
            $key_to_set = isset($key_mapping[$setting_key]) ? $key_mapping[$setting_key] : $setting_key;

            // Add to decorator settings
            $decorator_settings[$key_to_set] = $value;
        }

        // Store decorator settings
        if (!empty($decorator_settings)) {
            update_option('rp_decorator', $decorator_settings);
        }

        // Disable styling features
        RP_WCEC_Styling::disable_styling_features();
    }

    /**
     * Disable styling features
     *
     * @access public
     * @param bool $redirect
     * @return void
     */
    public static function disable_styling_features($redirect = true)
    {
        // Add flag
        update_option('rp_wcec_disable_styling_features', 1);

        // Backup styler settings
        RP_WCEC::update_option('styler_configuration_backup', RP_WCEC::opt('styler_configuration'));

        // Remove options
        RP_WCEC::update_option('override_woocommerce_templates', 0);
        RP_WCEC::update_option('styler_configuration', array());

        // Redirect user
        if ($redirect) {
            wp_redirect(admin_url('edit.php?post_type=wcec_email'));
            exit;
        }
    }

    /**
     * Add menu link
     *
     * @access public
     * @return void
     */
    public function admin_menu()
    {
        // Do not add link if Styler is not used
        if (!RP_WCEC_Styling::is_styler_used() && !RP_WCEC_Styling::are_templates_customized()) {
            return;
        }

        // Do not add link if functionality can no longer be used
        if (!RP_WCEC_Styling::are_styling_options_in_use()) {
            return;
        }

        // Add submenu links
        add_submenu_page(
            'edit.php?post_type=wcec_email',
            __('Styling', 'rp_wcec'),
            __('Styling', 'rp_wcec'),
            RP_WCEC::get_admin_capability(),
            'wcec_styling',
            array($this, 'set_up_page')
        );
    }

    /**
     * Set up styling page
     *
     * @access public
     * @return void
     */
    public function set_up_page()
    {
        // Do not set up page if Styler is not used
        if (!RP_WCEC_Styling::is_styler_used() && !RP_WCEC_Styling::are_templates_customized()) {
            return;
        }

        // Do not add link if functionality can no longer be used
        if (!RP_WCEC_Styling::are_styling_options_in_use()) {
            return;
        }

        // Load view
        include RP_WCEC_PLUGIN_PATH . 'includes/views/styling/settings.php';
    }

    /**
     * Get CSS styles string for html email styling
     * Used instead of templates/emails/email-styles.php in WooCommerce
     *
     * @access public
     * @param bool $include_custom_css
     * @return string
     */
    public static function get_styles_string($include_custom_css = true)
    {
        // First load styles that are not changeable via Styler
        ob_start();
        include RP_WCEC_PLUGIN_PATH . 'includes/views/styling/constant-styles.php';
        $styles = ob_get_clean();

        // Store styles in array first
        $styles_array = array();

        // Iterate over Styler settings
        foreach (self::get_styler_settings() as $setting_key => $setting) {

            // Only add CSS properties
            if (isset($setting['live_method']) && $setting['live_method'] === 'css') {

                // Iterate over selectors
                foreach ($setting['selectors'] as $selector => $properties) {

                    // Iterate over properties
                    foreach ($properties as $property) {

                        // Add value to styles array
                        $styles_array[$selector][$property] = self::opt($setting_key, $selector);
                    }
                }
            }
        }

        // Join property names with values
        foreach ($styles_array as $selector => $properties) {

            // Open selector
            $styles .= $selector . '{';

            foreach ($properties as $property_key => $property_value) {

                // Add property
                $styles .= $property_key . ':' . $property_value . ';';
            }

            // Close selector
            $styles .= '}';
        }

        // Append custom CSS
        if ($include_custom_css) {
            $styles .= self::opt('custom_css');
        }

        // Return styles string
        return $styles;
    }

    /**
     * Get styler value
     *
     * @access public
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function get_styler_value($key, $default = '')
    {
        // Get all stored values
        $stored = RP_WCEC::opt('styler_configuration');

        // Check if value exists in stored values array
        if (is_array($stored) && isset($stored[$key])) {
            return $stored[$key];
        }

        return $default;
    }

    /**
     * Display option in template
     *
     * @access public
     * @param string $key
     * @return void
     */
    public static function display($key)
    {
        echo self::opt($key);
    }

    /**
     * Get value for use in templates
     *
     * @access public
     * @param string $key
     * @param string $selector
     * @return string
     */
    public static function opt($key, $selector = null)
    {
        return self::prepare($key, self::get_styler_value($key, self::get_default_value($key)), $selector);
    }

    /**
     * Get Styler default values
     *
     * @access public
     * @param string $key
     * @return string
     */
    public static function get_default_value($key)
    {
        $default_values = array(
            'background_color'                      => '#f5f5f5',
            'email_background_color'                => '#fdfdfd',
            'header_background_color'               => '#557da1',
            'text_color'                            => '#737373',
            'font_size'                             => '14',
            'link_color'                            => '#557da1',
            'email_padding'                         => '70',
            'content_padding'                       => '48',
            'email_width'                           => '600',
            'border_radius'                         => '3',
            'heading_font_size'                     => '30',
            'heading_color'                         => '#ffffff',
            'heading_font_weight'                   => '300',
            'footer_padding'                        => '48',
            'footer_text_text_align'                => 'center',
            'footer_text_font_size'                 => '12',
            'footer_text_color'                     => '#99b1c7',
            'footer_text_font_weight'               => 'normal',
            'h1_font_size'                          => '24',
            'h1_color'                              => '#557da1',
            'h1_font_weight'                        => 'bold',
            'h1_separator_style'                    => 'none',
            'h1_separator_width'                    => '1',
            'h1_separator_color'                    => '#e4e4e4',
            'h2_font_size'                          => '18',
            'h2_color'                              => '#557da1',
            'h2_font_weight'                        => 'bold',
            'h2_separator_style'                    => 'none',
            'h2_separator_width'                    => '1',
            'h2_separator_color'                    => '#e4e4e4',
            'h3_font_size'                          => '16',
            'h3_color'                              => '#557da1',
            'h3_font_weight'                        => 'bold',
            'h3_separator_style'                    => 'none',
            'h3_separator_width'                    => '1',
            'h3_separator_color'                    => '#e4e4e4',
            'h4_font_size'                          => '14',
            'h4_color'                              => '#557da1',
            'h4_font_weight'                        => 'bold',
            'h4_separator_style'                    => 'none',
            'h4_separator_width'                    => '1',
            'h4_separator_color'                    => '#e4e4e4',
            'h5_font_size'                          => '12',
            'h5_color'                              => '#557da1',
            'h5_font_weight'                        => 'bold',
            'h5_separator_style'                    => 'none',
            'h5_separator_width'                    => '1',
            'h5_separator_color'                    => '#e4e4e4',
            'h6_font_size'                          => '10',
            'h6_color'                              => '#557da1',
            'h6_font_weight'                        => 'bold',
            'h6_separator_style'                    => 'none',
            'h6_separator_width'                    => '1',
            'h6_separator_color'                    => '#e4e4e4',
            'items_table_border_style'              => 'solid',
            'items_table_border_width'              => '1',
            'items_table_border_color'              => '#e4e4e4',
            'items_table_totals_separator_style'    => 'solid',
            'items_table_totals_separator_width'    => '4',
            'items_table_totals_separator_color'    => '#e4e4e4',
            'items_table_background_color'          => '',
            'items_table_padding'                   => '12',
        );

        return isset($default_values[$key]) ? $default_values[$key] : '';
    }

    /**
     * Prepare value for use in HTML
     *
     * @access public
     * @param string $key
     * @param string $value
     * @param string $selector
     * @return string
     */
    public static function prepare($key, $value, $selector = null)
    {
        // Ger key with prefix
        $key = 'rp_wcec_' . $key;

        // Prepend and append strings
        $result = self::prepend($key) . $value . self::append($key);

        // Special case for border_radius #template_header
        if ($key === 'rp_wcec_border_radius' && $selector === '#template_header') {
            $result = trim(str_replace('!important', '', $result));
            $result = $result . ' ' . $result . ' 0 0 !important';
        }

        // Special case for email_padding #wrapper
        if ($key === 'rp_wcec_email_padding' && $selector === '#wrapper') {
            // $result = trim(str_replace('!important', '', $result));
            $result = $result . ' 0 ' . $result . ' 0';
        }

        // Special case for footer_padding #template_footer #credit
        if ($key === 'rp_wcec_footer_padding' && $selector === '#template_footer #credit') {
            $result = '0 ' . $result . ' ' . $result . ' ' . $result;
        }

        // Return prepared value
        return $result;
    }

    /**
     * Define string(s) that need to be prepended to values
     *
     * @access public
     * @param string $key
     * @return void
     */
    public static function prepend($key = null)
    {
        $prepend = array(

        );

        if (isset($key)) {
            return isset($prepend[$key]) ? $prepend[$key] : '';
        }
        else {
            return $prepend;
        }
    }

    /**
     * Define string(s) that need to be appended to values
     *
     * @access public
     * @param string $key
     * @return void
     */
    public static function append($key = null)
    {
        $append = array(
            'rp_wcec_email_padding'                         => 'px',
            'rp_wcec_content_padding'                       => 'px',
            'rp_wcec_email_width'                           => 'px',
            'rp_wcec_border_radius'                         => 'px !important',
            'rp_wcec_heading_font_size'                     => 'px',
            'rp_wcec_footer_padding'                        => 'px',
            'rp_wcec_footer_text_font_size'                 => 'px',
            'rp_wcec_h1_font_size'                          => 'px',
            'rp_wcec_h2_font_size'                          => 'px',
            'rp_wcec_h3_font_size'                          => 'px',
            'rp_wcec_h4_font_size'                          => 'px',
            'rp_wcec_h5_font_size'                          => 'px',
            'rp_wcec_h6_font_size'                          => 'px',
            'rp_wcec_h1_separator_width'                    => 'px',
            'rp_wcec_h2_separator_width'                    => 'px',
            'rp_wcec_h3_separator_width'                    => 'px',
            'rp_wcec_h4_separator_width'                    => 'px',
            'rp_wcec_h5_separator_width'                    => 'px',
            'rp_wcec_h6_separator_width'                    => 'px',
            'rp_wcec_font_size'                             => 'px',
            'rp_wcec_items_table_border_width'              => 'px',
            'rp_wcec_items_table_totals_separator_width'    => 'px',
            'rp_wcec_items_table_padding'                   => 'px',
        );

        if (isset($key)) {
            return isset($append[$key]) ? $append[$key] : '';
        }
        else {
            return $append;
        }
    }

    /**
     * Get Styler settings
     *
     * @access public
     * @return array
     */
    public static function get_styler_settings()
    {
        return array(

            // Background color
            'background_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    'body'      => array('background-color'),
                    '#wrapper'  => array('background-color'),
                ),
            ),

            // Email padding
            'email_padding' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#wrapper' => array('padding'),
                ),
            ),

            // Email width
            'email_width' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_container'   => array('width'),
                    '#template_header'      => array('width'),
                    '#template_body'        => array('width'),
                    '#template_footer'      => array('width'),
                ),
            ),

            // Content padding
            'content_padding' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#body_content_cell' => array('padding'),
                ),
            ),

            // Email background color
            'email_background_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_container'   => array('background-color'),
                    '#body_content'         => array('background-color'),
                ),
            ),

            // Text color
            'text_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#body_content_inner'   => array('color'),
                    '.td'                   => array('color'),
                ),
            ),

            // Font size
            'font_size' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#body_content_inner'   => array('font-size'),
                    'img'                   => array('font-size'),
                ),
            ),

            // Link color
            'link_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    'a'     => array('color'),
                    '.link' => array('color'),
                ),
            ),

            // Header background color
            'header_background_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_header' => array('background-color'),
                ),
            ),

            // Border radius
            'border_radius' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_container'   => array('border-radius'),
                    '#template_header'      => array('border-radius'),
                ),
            ),

            // Heading Font size
            'heading_font_size' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_header h1' => array('font-size'),
                ),
            ),

            // Heading Color
            'heading_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_header'      => array('color'),
                    '#template_header h1'   => array('color'),
                ),
            ),

            // Heading Font weight
            'heading_font_weight' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_header'      => array('font-weight'),
                    '#template_header h1'   => array('font-weight'),
                ),
            ),

            // Footer padding
            'footer_padding' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_footer #credit' => array('padding'),
                ),
            ),

            // Footer Text Text align
            'footer_text_text_align' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_footer #credit' => array('text-align'),
                ),
            ),

            // Footer Text Font size
            'footer_text_font_size' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_footer #credit' => array('font-size'),
                ),
            ),

            // Footer Text Color
            'footer_text_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_footer #credit' => array('color'),
                ),
            ),

            // Footer Text Font weight
            'footer_text_font_weight' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_footer #credit' => array('font-weight'),
                ),
            ),

            // H1 Font size
            'h1_font_size' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h1' => array('font-size'),
                ),
            ),

            // H1 Color
            'h1_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h1' => array('color'),
                ),
            ),

            // H1 Font weight
            'h1_font_weight' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h1' => array('font-weight'),
                ),
            ),

            // H1 Separator
            'h1_separator_style' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h1' => array('border-bottom-style'),
                ),
            ),

            // H1 Separator color
            'h1_separator_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h1' => array('border-bottom-color'),
                ),
            ),

            // H1 Separator width
            'h1_separator_width' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h1' => array('border-bottom-width'),
                ),
            ),

            // H2 Font size
            'h2_font_size' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h2' => array('font-size'),
                ),
            ),

            // H2 Color
            'h2_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h2' => array('color'),
                ),
            ),

            // H2 Font weight
            'h2_font_weight' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h2' => array('font-weight'),
                ),
            ),

            // H2 Separator
            'h2_separator_style' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h2' => array('border-bottom-style'),
                ),
            ),

            // H2 Separator color
            'h2_separator_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h2' => array('border-bottom-color'),
                ),
            ),

            // H2 Separator width
            'h2_separator_width' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h2' => array('border-bottom-width'),
                ),
            ),

            // H3 Font size
            'h3_font_size' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h3' => array('font-size'),
                ),
            ),

            // H3 Color
            'h3_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h3' => array('color'),
                ),
            ),

            // H3 Font weight
            'h3_font_weight' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h3' => array('font-weight'),
                ),
            ),

            // H3 Separator
            'h3_separator_style' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h3' => array('border-bottom-style'),
                ),
            ),

            // H3 Separator color
            'h3_separator_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h3' => array('border-bottom-color'),
                ),
            ),

            // H3 Separator width
            'h3_separator_width' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h3' => array('border-bottom-width'),
                ),
            ),

            // H4 Font size
            'h4_font_size' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h4' => array('font-size'),
                ),
            ),

            // H4 Color
            'h4_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h4' => array('color'),
                ),
            ),

            // H4 Font weight
            'h4_font_weight' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h4' => array('font-weight'),
                ),
            ),

            // H4 Separator
            'h4_separator_style' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h4' => array('border-bottom-style'),
                ),
            ),

            // H4 Separator color
            'h4_separator_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h4' => array('border-bottom-color'),
                ),
            ),

            // H4 Separator width
            'h4_separator_width' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h4' => array('border-bottom-width'),
                ),
            ),

            // H5 Font size
            'h5_font_size' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h5' => array('font-size'),
                ),
            ),

            // H5 Color
            'h5_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h5' => array('color'),
                ),
            ),

            // H5 Font weight
            'h5_font_weight' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h5' => array('font-weight'),
                ),
            ),

            // H5 Separator
            'h5_separator_style' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h5' => array('border-bottom-style'),
                ),
            ),

            // H5 Separator color
            'h5_separator_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h5' => array('border-bottom-color'),
                ),
            ),

            // H5 Separator width
            'h5_separator_width' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h5' => array('border-bottom-width'),
                ),
            ),

            // H6 Font size
            'h6_font_size' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h6' => array('font-size'),
                ),
            ),

            // H6 Color
            'h6_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h6' => array('color'),
                ),
            ),

            // H6 Font weight
            'h6_font_weight' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h6' => array('font-weight'),
                ),
            ),

            // H6 Separator
            'h6_separator_style' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h6' => array('border-bottom-style'),
                ),
            ),

            // H6 Separator color
            'h6_separator_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h6' => array('border-bottom-color'),
                ),
            ),

            // H6 Separator width
            'h6_separator_width' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#template_body h6' => array('border-bottom-width'),
                ),
            ),

            // Custom CSS
            'custom_css' => array(
                'live_method'   => 'replace',
                'selectors'     => array(
                    'style#rp_wcec_custom_css'
                ),
            ),

            // Items table Border style
            'items_table_border_style' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#rp_wcec_items_table'              => array('border-style'),
                    '#rp_wcec_items_table .td'          => array('border-style'),
                    '.rp_wcec_order_refund_line .td'    => array('border-style'),
                ),
            ),

            // Items table Border color
            'items_table_border_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#rp_wcec_items_table'              => array('border-color'),
                    '#rp_wcec_items_table .td'          => array('border-color'),
                    '.rp_wcec_order_refund_line .td'    => array('border-color'),
                ),
            ),

            // Items table Border width
            'items_table_border_width' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#rp_wcec_items_table'              => array('border-width'),
                    '#rp_wcec_items_table .td'          => array('border-width'),
                    '.rp_wcec_order_refund_line .td'    => array('border-width'),
                ),
            ),

            // Items table Separator style
            'items_table_totals_separator_style' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#rp_wcec_items_table tfoot' => array('border-top-style'),
                ),
            ),

            // Items table Separator color
            'items_table_totals_separator_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#rp_wcec_items_table tfoot' => array('border-top-color'),
                ),
            ),

            // Items table Separator width
            'items_table_totals_separator_width' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#rp_wcec_items_table tfoot' => array('border-top-width'),
                ),
            ),

            // Items table Padding
            'items_table_padding' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#body_content table td th' => array('padding'),
                    '#body_content table td td' => array('padding'),
                ),
            ),

            // Items table Background color
            'items_table_background_color' => array(
                'live_method'   => 'css',
                'selectors'     => array(
                    '#rp_wcec_items_table' => array('background-color'),
                ),
            ),
        );
    }

    /**
     * Check if any Styler functionality is used on this site
     *
     * @access public
     * @return bool
     */
    public static function is_styler_used()
    {
        // Get stored values
        $stored = RP_WCEC::opt('styler_configuration');

        // Styler has configuration
        if (!empty($stored) && is_array($stored)) {
            return true;
        }

        // Styler is not used
        return false;
    }

    /**
     * Check if our email templates were customized
     *
     * @access public
     * @return bool
     */
    public static function are_templates_customized()
    {
        // Search in theme folders
        if (file_exists(STYLESHEETPATH . '/' . 'woocommerce-email-center')) {
            return true;
        }
        else if (file_exists(TEMPLATEPATH . '/' . 'woocommerce-email-center')) {
            return true;
        }
        else if (file_exists(ABSPATH . WPINC . '/theme-compat/' . 'woocommerce-email-center')) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Check if WooCommerce email templates are overriden with custom email templates
     *
     * @access public
     * @return bool
     */
    public static function are_wc_templates_overriden()
    {
        return (bool) RP_WCEC::opt('override_woocommerce_templates');
    }

    /**
     * Check if styling options are still in use
     *
     * @access public
     * @return bool
     */
    public static function are_styling_options_in_use()
    {
        return !get_option('rp_wcec_disable_styling_features');
    }




}

new RP_WCEC_Styling();

}
