<?php

/**
 * Plugin Name: WooCommerce Custom Emails
 * Plugin URI: http://www.rightpress.net/woocommerce-email-center
 * Description: Send highly targeted WooCommerce emails. Formerly "WooCommerce Email Center".
 * Author: RightPress
 * Author URI: http://www.rightpress.net
 *
 * Text Domain: rp_wcec
 * Domain Path: /languages
 *
 * Version: 1.4.2
 *
 * Requires at least: 4.0
 * Tested up to: 4.8
 *
 * WC requires at least: 2.5
 * WC tested up to: 3.2
 *
 * @package WooCommerce Email Center
 * @category Core
 * @author RightPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define Constants
define('RP_WCEC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RP_WCEC_PLUGIN_URL', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)));
define('RP_WCEC_VERSION', '1.4.2');
define('RP_WCEC_OPTIONS_VERSION', '1');
define('RP_WCEC_SUPPORT_PHP', '5.3');
define('RP_WCEC_SUPPORT_WP', '4.0');
define('RP_WCEC_SUPPORT_WC', '2.5');

if (!class_exists('RP_WCEC')) {

/**
 * Main plugin class
 *
 * @package WooCommerce Email Center
 * @author RightPress
 */
class RP_WCEC
{
    // Singleton instance
    private static $instance = false;

    /**
     * Singleton control
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Load translation
        load_textdomain('rp_wcec', WP_LANG_DIR . '/woocommerce-email-center/rp_wcec-' . apply_filters('plugin_locale', get_locale(), 'rp_wcec') . '.mo');
        load_plugin_textdomain('rp_wcec', false, dirname(plugin_basename(__FILE__)) . '/languages/');

        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Initialize automatic updates
        require_once(plugin_dir_path(__FILE__) . 'rightpress/rightpress-updates.class.php');
        RightPress_Updates_13907681::init(__FILE__, RP_WCEC_VERSION);

        // Load helper class
        include_once RP_WCEC_PLUGIN_PATH . 'rightpress/rightpress-helper.class.php';
        include_once RP_WCEC_PLUGIN_PATH . 'rightpress/rightpress-wc-meta.class.php';
        include_once RP_WCEC_PLUGIN_PATH . 'rightpress/rightpress-wc-legacy.class.php';

        // Load abstract classes
        foreach (glob(RP_WCEC_PLUGIN_PATH . 'includes/classes/abstract/*.class.php') as $filename) {
            include $filename;
        }

        // Load classes
        foreach (glob(RP_WCEC_PLUGIN_PATH . 'includes/classes/*.class.php') as $filename) {
            include $filename;
        }

        // Additional Plugins page links
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugins_page_links'));

        // Continue when all plugins are loaded
        add_action('plugins_loaded', array($this, 'on_plugins_loaded'), 0);
    }

    /**
     * On plugins loaded action
     *
     * @access public
     * @return void
     */
    public function on_plugins_loaded()
    {
        // Check environment
        if (!RP_WCEC::check_environment()) {
            return;
        }

        // Load/parse plugin settings
        $this->opt = $this->get_options();

        // Hook to WordPress 'init' action
        add_action('init', array($this, 'on_init_pre'), 1);

        // Load assets conditionally
        add_action('init', array($this, 'enqueue_select2'), 1);

        // Load assets conditionally
        add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_assets'));

        // Load some assets on all admin pages
        add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_assets_all'));

        // Dequeue some WooCommerce assets on our own pages
        add_action('admin_enqueue_scripts', array($this, 'dequeue_woocommerce_scripts'), 11);

        // Add settings page menu link
        add_action('admin_menu', array($this, 'admin_menu'), 11);

        // Automatically login user after clicking on the auto login link
        add_action('init', array($this, 'auto_login'));
    }

    /**
     * Check if environment meets requirements
     *
     * @access public
     * @return bool
     */
    public static function check_environment()
    {
        $is_ok = true;

        // Check PHP version
        if (!version_compare(PHP_VERSION, RP_WCEC_SUPPORT_PHP, '>=')) {

            // Add notice
            add_action('admin_notices', array('RP_WCEC', 'php_version_notice'));

            // Do not proceed as RightPress Helper requires PHP 5.3 for itself
            return false;
        }

        // Check WordPress version
        if (!RightPress_Helper::wp_version_gte(RP_WCEC_SUPPORT_WP)) {
            add_action('admin_notices', array('RP_WCEC', 'wp_version_notice'));
            $is_ok = false;
        }

        // Check if WooCommerce is enabled
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array('RP_WCEC', 'wc_disabled_notice'));
            $is_ok = false;
        }
        else if (!RightPress_Helper::wc_version_gte(RP_WCEC_SUPPORT_WC)) {
            add_action('admin_notices', array('RP_WCEC', 'wc_version_notice'));
            $is_ok = false;
        }

        return $is_ok;
    }

    /**
     * Display PHP version notice
     *
     * @access public
     * @return void
     */
    public static function php_version_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce Email Center</strong> requires PHP %s or later. Please update PHP on your server to use this plugin.', 'rp_wcec'), RP_WCEC_SUPPORT_PHP) . ' ' . sprintf(__('If you have any questions, please contact %s.', 'rp_wcec'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'rp_wcec') . '</a>') . '</p></div>';
    }

    /**
     * Display WP version notice
     *
     * @access public
     * @return void
     */
    public static function wp_version_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce Email Center</strong> requires WordPress version %s or later. Please update WordPress to use this plugin.', 'rp_wcec'), RP_WCEC_SUPPORT_WP) . ' ' . sprintf(__('If you have any questions, please contact %s.', 'rp_wcec'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'rp_wcec') . '</a>') . '</p></div>';
    }

    /**
     * Display WC disabled notice
     *
     * @access public
     * @return void
     */
    public static function wc_disabled_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce Email Center</strong> requires WooCommerce to be active. You can download WooCommerce %s.', 'rp_wcec'), '<a href="http://url.rightpress.net/woocommerce-download-page">' . __('here', 'rp_wcec') . '</a>') . ' ' . sprintf(__('If you have any questions, please contact %s.', 'rp_wcec'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'rp_wcec') . '</a>') . '</p></div>';
    }

    /**
     * Display WC version notice
     *
     * @access public
     * @return void
     */
    public static function wc_version_notice()
    {
        echo '<div class="error"><p>' . sprintf(__('<strong>WooCommerce Email Center</strong> requires WooCommerce version %s or later. Please update WooCommerce to use this plugin.', 'rp_wcec'), RP_WCEC_SUPPORT_WC) . ' ' . sprintf(__('If you have any questions, please contact %s.', 'rp_wcec'), '<a href="http://url.rightpress.net/new-support-ticket">' . __('RightPress Support', 'rp_wcec') . '</a>') . '</p></div>';
    }

    /**
     * Add settings link on plugins page
     *
     * @access public
     * @param array $links
     * @return void
     */
    public function plugins_page_links($links)
    {
        $settings_link = '<a href="http://url.rightpress.net/woocommerce-email-center-help" target="_blank">'.__('Support', 'rp_wcec').'</a>';
        array_unshift($links, $settings_link);

        if (RP_WCEC::check_environment()) {
            $settings_link = '<a href="edit.php?post_type=wcec_email">'.__('Settings', 'rp_wcec').'</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    /**
     * WordPress activation hook
     *
     * @access public
     * @return void
     */
    public function activate()
    {
        // Define options
        if (!get_option('rp_wcec_options')) {
            add_option('rp_wcec_options', array(RP_WCEC_OPTIONS_VERSION => $this->get_default_options()));
        }
    }

    /**
     * Get options saved in database
     *
     * @access public
     * @return array
     */
    public function get_options()
    {
        // Get options from database
        $saved_options = (array) get_option('rp_wcec_options', array());

        // Get current version (for major updates in the future)
        if (!empty($saved_options)) {
            if (isset($saved_options[RP_WCEC_OPTIONS_VERSION])) {
                $saved_options = $saved_options[RP_WCEC_OPTIONS_VERSION];
            }
            else {
                // Migrate options here if needed...
            }
        }

        // Merge with default and return
        return array_merge($this->get_default_options(), $saved_options);
    }

    /**
     * Get default options
     *
     * @access public
     * @return array
     */
    public static function get_default_options()
    {
        return array(
            'override_woocommerce_templates' => 0,
        );
    }

    /**
     * Get single option
     *
     * @access public
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function opt($key, $default = false)
    {
        $wcec = self::get_instance();
        return isset($wcec->opt[$key]) ? $wcec->opt[$key] : $default;
    }

    /*
     * Update single option
     *
     * @access public
     * @return bool
     */
    public static function update_option($key, $value)
    {
        $rp_wcec = RP_WCEC::get_instance();
        $rp_wcec->opt[$key] = $value;
        return update_option('rp_wcec_options', $rp_wcec->opt);
    }

    /**
     * WordPress 'init' @ position 1
     *
     * @access public
     * @return void
     */
    public function on_init_pre()
    {
        // Add class to actions metaboxes
        foreach (RP_WCEC::get_visible_post_types() as $post_type) {
            add_filter('postbox_classes_' . $post_type . '_' . $post_type . '_actions', array($this, 'add_actions_metabox_class'));
        }

        // Load integration classes
        foreach (glob(RP_WCEC_PLUGIN_PATH . 'includes/classes/integration/*.class.php') as $filename) {
            include $filename;
        }
    }

    /**
     * Add class to actions metaboxes
     *
     * @access public
     * @param array $classes
     * @return array
     */
    public function add_actions_metabox_class($classes)
    {
        array_push($classes, 'rp_wcec_actions_metabox');
        return $classes;
    }

    /**
     * Add or remove admin menu items
     *
     * @access public
     * @return void
     */
    public function admin_menu()
    {
        global $submenu;

        // Define new menu order
        $reorder = array(
            'edit.php?post_type=wcec_block'     => 52,
            'edit.php?post_type=wcec_trigger'   => 53,
            'wcec_styling'                      => 54,
            'edit.php?post_type=wcec_log_entry' => 55,
        );

        // Check if our menu exists
        if (isset($submenu['edit.php?post_type=wcec_email'])) {

            // Iterate over submenu items
            foreach ($submenu['edit.php?post_type=wcec_email'] as $item_key => $item) {

                // Remove Add Email menu link
                if (in_array('post-new.php?post_type=wcec_email', $item)) {
                    unset($submenu['edit.php?post_type=wcec_email'][$item_key]);
                }

                // Rearrange other items
                foreach ($reorder as $order_key => $order) {
                    if (in_array($order_key, $item)) {
                        $submenu['edit.php?post_type=wcec_email'][$order] = $item;
                        unset($submenu['edit.php?post_type=wcec_email'][$item_key]);
                    }
                }
            }

            // Sort array by key
            ksort($submenu['edit.php?post_type=wcec_email']);
        }
    }

    /**
     * Enqueue Select2
     *
     * @access public
     * @return void
     */
    public function enqueue_select2()
    {
        // Get post type
        $post_type = !empty($_REQUEST['post']) ? get_post_type($_REQUEST['post']) : (!empty($_REQUEST['post_type']) ? $_REQUEST['post_type'] : null);

        // Check if Select2 needs to be loaded on a particular page
        if (empty($post_type) || !in_array($post_type, array('wcec_trigger', 'wcec_email'))) {
            return;
        }

        wp_enqueue_script('rp-wcec-select2-scripts', RP_WCEC_PLUGIN_URL . '/assets/select2/js/select2.min.js', array('jquery'), '4.0.3');
        wp_enqueue_script('rp-wcec-select2-rp', RP_WCEC_PLUGIN_URL . '/assets/js/rp-select2.js', array(), RP_WCEC_VERSION);
        wp_enqueue_style('rp-wcec-select2-styles', RP_WCEC_PLUGIN_URL . '/assets/select2/css/select2.min.css', array(), '4.0.3');

        // Print scripts before WordPress takes care of it automatically (helps load our version of Select2 before any other plugin does it)
        add_action('wp_print_scripts', array($this, 'print_select2'));
    }

    /**
     * Print Select2 scripts
     *
     * @access public
     * @return void
     */
    public function print_select2()
    {
        remove_action('wp_print_scripts', array($this, 'print_select2'));
        wp_print_scripts('rp-wcec-select2-scripts');
        wp_print_scripts('rp-wcec-select2-rp');
    }

    /**
     * Load backend assets conditionally
     *
     * @access public
     * @return void
     */
    public function enqueue_backend_assets()
    {
        global $typenow;

        // Check if we are on our own page
        if (!preg_match('/^wcec_/i', $typenow)) {
            return;
        }

        // Our own scripts and styles
        wp_register_script('rp-wcec-backend-conditions', RP_WCEC_PLUGIN_URL . '/assets/js/conditions.js', array('jquery', 'jquery-ui-accordion', 'jquery-ui-sortable'), RP_WCEC_VERSION);
        wp_register_script('rp-wcec-backend-datepicker', RP_WCEC_PLUGIN_URL . '/assets/js/datepicker.js', array('jquery', 'jquery-ui-datepicker'), RP_WCEC_VERSION);
        wp_register_script('rp-wcec-backend-schedule', RP_WCEC_PLUGIN_URL . '/assets/js/schedule.js', array('jquery'), RP_WCEC_VERSION);
        wp_register_script('rp-wcec-backend-trigger', RP_WCEC_PLUGIN_URL . '/assets/js/trigger.js', array('jquery'), RP_WCEC_VERSION);
        wp_register_script('rp-wcec-backend-email', RP_WCEC_PLUGIN_URL . '/assets/js/email.js', array('jquery'), RP_WCEC_VERSION);
        wp_register_style('rp-wcec-backend-styles', RP_WCEC_PLUGIN_URL . '/assets/css/backend.css', array(), RP_WCEC_VERSION);

        // Styles
        wp_enqueue_style('rp-wcec-backend-styles');

        // Pass variables to condition control script
        wp_localize_script('rp-wcec-backend-conditions', 'rp_wcec', array(
            'ajaxurl' => RP_WCEC_Ajax::get_url(),
        ));

        // Pass variables to email script
        wp_localize_script('rp-wcec-backend-email', 'rp_wcec', array(
            'labels' => array(
                'type_email'        => __('Type email', 'rp_wcec'),
                'continue_typing'   => __('Continue typing...', 'rp_wcec'),
            ),
        ));

        // Disable autosave for our post types
        wp_dequeue_script('autosave');
    }

    /**
     * Load backend assets unconditionally
     *
     * @access public
     * @return void
     */
    public function enqueue_backend_assets_all()
    {
        // Our own scripts and styles
        wp_register_style('rp-wcec-backend-styles-all', RP_WCEC_PLUGIN_URL . '/assets/css/backend-all.css', array(), RP_WCEC_VERSION);

        // Font awesome (icons)
        wp_register_style('rp-wcec-font-awesome', RP_WCEC_PLUGIN_URL . '/assets/font-awesome/css/font-awesome.min.css', array(), '4.4');

        // Scripts
        wp_enqueue_script('rp-wcec-backend-scripts-all');

        // Styles
        wp_enqueue_style('rp-wcec-backend-styles-all');
        wp_enqueue_style('rp-wcec-font-awesome');
    }

    /**
     * Enqueue jQuery UI Datepicker
     *
     * @access public
     * @return void
     */
    public static function enqueue_datepicker()
    {
        // Enqueue datepicker control script
        wp_enqueue_script('rp-wcec-backend-datepicker');

        // Datepicker configuration
        wp_localize_script('rp-wcec-backend-datepicker', 'rp_wcec_datepicker_config', RP_WCEC::get_datepicker_config());

        // jQuery UI Datepicker styles
        wp_enqueue_style('rp-wcec-jquery-ui-styles', RP_WCEC_PLUGIN_URL . '/assets/jquery-ui/jquery-ui.min.css', array(), '1.11.4');

        // jQuery UI Datepicker language file
        $locale = RightPress_Helper::get_optimized_locale('mixed');
        if (file_exists(RP_WCEC_PLUGIN_PATH . 'assets/jquery-ui/i18n/datepicker-' . $locale . '.js')) {
            wp_enqueue_script('rp-wcec-jquery-ui-language', RP_WCEC_PLUGIN_URL . '/assets/jquery-ui/i18n/datepicker-' . $locale . '.js', array('jquery-ui-datepicker'), RP_WCEC_VERSION);
        }
    }

    /**
     * Dequeue some WooCommerce scripts and styles that interfere with our UI (this is done on our settings page only)
     *
     * @access public
     * @return void
     */
    public function dequeue_woocommerce_scripts()
    {
        if (self::is_our_admin_page()) {
            wp_dequeue_style('woocommerce_admin_styles');
            wp_dequeue_style('woocommerce_admin');
        }
    }

    /**
     * Get admin capability
     *
     * @access public
     * @return string
     */
    public static function get_admin_capability()
    {
        return apply_filters('rp_wcec_capability', 'manage_woocommerce');
    }

    /**
     * Check if current user is admin or it's equivalent (shop manager etc)
     *
     * @access public
     * @return bool
     */
    public static function is_admin()
    {
        return current_user_can(self::get_admin_capability());
    }

    /**
     * Check if post is trashed and return corresponding suffix
     *
     * @access public
     * @param int $id
     * @param bool $is_trashed
     * @return string
     */
    public static function trashed_suffix($id, $is_trashed = null)
    {
        // Do we know if this post is trashed?
        if ($is_trashed === null) {
            $is_trashed = RightPress_Helper::post_is_trashed($id);
        }

        // Check if post is trashed and return appropriate suffix
        return $is_trashed ? ' (' . __('trashed', 'rp_wcec') . ')' : '';
    }

    /**
     * Get post content
     *
     * @access public
     * @param int $post_id
     * @return string
     */
    public static function get_post_content($post_id)
    {
        // Get post
        $post = get_post($post_id);

        // Check if post was found
        if (empty($post) || !isset($post->post_content) || empty($post->post_content)) {
            return '';
        }

        // Treat and return post content
        return str_replace(']]>', ']]&gt;', $post->post_content);
    }

    /**
     * Get jQuery UI Datepicker config
     *
     * Note: currently this is designed to work with the trigger schedule date field only (specific alt field)
     *
     * @access public
     * @return array
     */
    public static function get_datepicker_config()
    {
        return apply_filters('rp_wcec_datepicker_config', array(
            'dateFormat'    => self::get_datepicker_date_format_from_php(self::get_date_format()),
            'altField'      => '#rp_wcec_schedule_date',
            'altFormat'     => 'yy-mm-dd',
        ));
    }

    /**
     * Get WP date format
     *
     * @access public
     * @return string
     */
    public static function get_date_format()
    {
        return apply_filters('rp_wcec_date_format', get_option('date_format'));
    }

    /**
     * Get WP time format
     *
     * @access public
     * @return string
     */
    public static function get_time_format()
    {
        return apply_filters('rp_wcec_time_format', get_option('time_format'));
    }

    /**
     * Get WP date and time format
     *
     * @access public
     * @return string
     */
    public static function get_date_time_format()
    {
        return apply_filters('rp_wcec_date_time_format', (RP_WCEC::get_date_format() . ' ' . RP_WCEC::get_time_format()));
    }

    /**
     * Convert PHP date format to jQuery UI datepicker date format
     *
     * @access public
     * @param string $php_format
     * @return string
     */
    public static function get_datepicker_date_format_from_php($php_format)
    {
        // Symbol matching array
        $sybol_matching = array(
            // Day
            'd' => 'dd',
            'D' => 'D',
            'j' => 'd',
            'l' => 'DD',
            'N' => '',
            'S' => '',
            'w' => '',
            'z' => 'o',
            // Week
            'W' => '',
            // Month
            'F' => 'MM',
            'm' => 'mm',
            'M' => 'M',
            'n' => 'm',
            't' => '',
            // Year
            'L' => '',
            'o' => '',
            'Y' => 'yy',
            'y' => 'y',
            // Time
            'a' => '',
            'A' => '',
            'B' => '',
            'g' => '',
            'G' => '',
            'h' => '',
            'H' => '',
            'i' => '',
            's' => '',
            'u' => ''
        );

        $datepicker_format = '';
        $escaping = false;

        for ($i = 0; $i < strlen($php_format); $i++) {
            $char = $php_format[$i];

            if ($char === '\\') {
                $i++;

                if ($escaping) {
                    $datepicker_format .= $php_format[$i];
                }
                else {
                    $datepicker_format .= '\'' . $php_format[$i];
                }

                $escaping = true;
            }
            else {
                if ($escaping) {
                    $datepicker_format .= '\'';
                    $escaping = false;
                }

                if (isset($sybol_matching[$char])) {
                    $datepicker_format .= $sybol_matching[$char];
                }
                else {
                    $datepicker_format .= $char;
                }
            }
        }

        return $datepicker_format;
    }

    /**
     * Get ISO date (1999-12-31) from WP date format
     *
     * @access public
     * @param string $date
     * @return string
     */
    public static function get_iso_date($date)
    {
        $format = self::get_date_format();
        $datetime = RightPress_Helper::date_create_from_format($format, $date);
        return $datetime->format('Y-m-d');
    }

    /**
     * Get WP format date from ISO date
     *
     * @access public
     * @param string $date
     * @return string
     */
    public static function get_wp_date($date)
    {
        $format = self::get_date_format();
        $datetime = RightPress_Helper::date_create_from_format('Y-m-d', $date);
        return date_i18n($format, $datetime->format('U'));
    }

    /**
     * Get timestamp from WP date
     *
     * @access public
     * @param string $date
     * @return string
     */
    public static function get_timestamp_from_wp_date($date)
    {
        $format = self::get_date_format();
        $datetime = RightPress_Helper::date_create_from_format($format, $date);
        return $datetime->format('U');
    }

    /**
     * Get post types visible in UI
     *
     * @access public
     * @return array
     */
    public static function get_visible_post_types()
    {
        return array('wcec_block', 'wcec_email', 'wcec_trigger', 'wcec_log_entry');
    }

    /**
     * Check if admin page that is being displayed is our own
     *
     * @access public
     * @return bool
     */
    public static function is_our_admin_page()
    {
        global $typenow;
        return in_array($typenow, RP_WCEC::get_visible_post_types());
    }

    /**
     * Convert HTML content into plain text
     *
     * @access public
     * @param string $html
     * @return string
     */
    public static function html_to_text($html = '')
    {
        // Load class if not yet loaded
        if (!class_exists('RP_Html2Text')) {
            include RP_WCEC_PLUGIN_PATH . 'includes/classes/libraries/rp-html2text.class.php';
        }

        // Initialize class
        $html_to_text = new RP_Html2Text($html);

        // Return text
        return $html_to_text->getText();
    }

    /**
     * Apply inline styles
     *
     * @access public
     * @param string $content
     * @param string $styles
     * @return string
     */
    public static function apply_inline_styles($content, $styles = '')
    {
        // Load class if not yet loaded
        if (!class_exists('RP_Emogrifier')) {
            include RP_WCEC_PLUGIN_PATH . 'includes/classes/libraries/rp-emogrifier.class.php';
        }

        // Initialize Emogrifier
        $emogrifier = new RP_Emogrifier($content, $styles);

        // Apply inline styles
        $content = $emogrifier->emogrify();

        // Return content
        return $content;
    }

    /**
     * Get link to user profile
     *
     * @access public
     * @param int $user_id
     * @param string $default
     * @return string
     */
    public static function get_user_profile_link($user_id, $default)
    {
        // User still exists?
        if ($user = get_userdata($user_id)) {
            return '<a href="user-edit.php?user_id=' . $user_id . '">' . $user->user_email . '</a>';
        }

        return '<span class="rp_wcec_none">' . $default . ' (' . __('deleted', 'rp_wcec') . ')</span>';
    }

    /**
     * Get WooCommerce paid statuses
     *
     * @access public
     * @param bool $include_prefix
     * @return array
     */
    public static function get_wc_order_paid_statuses($include_prefix = false)
    {
        // Get paid statuses
        $paid_statuses = apply_filters('woocommerce_order_is_paid_statuses', array('processing', 'completed'));

        // Optionally include prefix
        if ($include_prefix) {
            foreach ($paid_statuses as $key => $value) {
                $paid_statuses[$key] = 'wc-' . $value;
            }
        }

        return $paid_statuses;
    }

    /**
     * Automatically login user after clicking on the auto login link
     *
     * @access public
     * @return void
     */
    public function auto_login()
    {
        // Check if this is our request
        if (!empty($_REQUEST['rp_wcec_auto_login_id']) && !empty($_REQUEST['rp_wcec_auto_login_hash'])) {

            $user_id = $_REQUEST['rp_wcec_auto_login_id'];
            $hash = $_REQUEST['rp_wcec_auto_login_hash'];

            // Get auto login key for user
            if ($key = get_user_meta($user_id, 'rp_wcec_auto_login_key', true)) {

                // Hashes match
                if (wp_check_password($key, $hash)) {

                    // Get user data
                    if ($user = get_userdata($user_id)) {

                        // Login user
                        wp_set_current_user($user_id, $user->user_login);
                        wp_set_auth_cookie($user_id);
                        do_action('wp_login', $user->user_login, $user);

                        // Redirect to hide query args
                        wp_redirect(remove_query_arg(array('rp_wcec_auto_login_id', 'rp_wcec_auto_login_hash')));
                        exit;
                    }
                }
            }
        }
    }



}

RP_WCEC::get_instance();

}
