<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Block object class
 *
 * @class RP_WCEC_Block
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_Block')) {

class RP_WCEC_Block extends RP_WCEC_Post_Object
{
    // Define post type title
    protected static $post_type = 'wcec_block';
    protected static $post_type_short = 'block';

    // Define meta keys
    protected static $meta_properties = array(
        'title'
    );

    // Define object properties
    protected $id;
    protected $status;
    protected $status_title;
    protected $title;
    protected $content;

    /**
     * Constructor class
     *
     * @access public
     * @param mixed $id
     * @return void
     */
    public function __construct($id = null)
    {
        // Construct parent first
        parent::__construct($id);

        // Construct as post type controller
        if ($id === null) {
            add_action('plugins_loaded', array($this, 'set_up_post_type_controller'), 1);
        }
    }

    /**
     * Set up post type controller
     *
     * @access public
     * @return void
     */
    public function set_up_post_type_controller()
    {
        // Check environment
        if (!RP_WCEC::check_environment()) {
            return;
        }

        // Move editor to dedicated meta box
        add_action('add_meta_boxes', array($this, 'move_editor'), 0);

        // Add shortcode to display block content
        add_shortcode('wcec_block', array($this, 'shortcode_block'));

        // Add block and macro button to editor
        add_action('admin_head', array($this, 'add_editor_buttons'));
        add_filter('tiny_mce_version', array($this, 'refresh_tinymce'));
        add_filter('mce_external_languages', array($this, 'add_tinymce_locales'), 20);
        add_action('admin_head', array($this, 'print_tinymce_button_config'));
    }

    /**
     * Get post type labels
     *
     * @access public
     * @return array
     */
    public function get_post_type_labels()
    {
        return array(
            'labels' => array(
                'name'               => __('Email Blocks', 'rp_wcec'),
                'singular_name'      => __('Email Block', 'rp_wcec'),
                'add_new'            => __('Add Block', 'rp_wcec'),
                'add_new_item'       => __('Add New Block', 'rp_wcec'),
                'edit_item'          => __('Edit Block', 'rp_wcec'),
                'new_item'           => __('New Block', 'rp_wcec'),
                'all_items'          => __('Blocks', 'rp_wcec'),
                'view_item'          => __('View Block', 'rp_wcec'),
                'search_items'       => __('Search Blocks', 'rp_wcec'),
                'not_found'          => __('No Blocks Found', 'rp_wcec'),
                'not_found_in_trash' => __('No Blocks Found In Trash', 'rp_wcec'),
                'parent_item_colon'  => '',
                'menu_name'          => __('Blocks', 'rp_wcec'),
            ),
            'description' => __('Email Blocks', 'rp_wcec'),
        );
    }

    /**
     * Get taxonomies
     *
     * @access public
     * @return array
     */
    public function get_taxonomies()
    {
        return array(
            'status' => array(
                'singular'  => __('Status', 'rp_wcec'),
                'plural'    => __('Status', 'rp_wcec'),
                'all'       => __('All statuses', 'rp_wcec'),
            ),
        );
    }

    /**
     * Get object type title
     *
     * @access public
     * @return string
     */
    public function get_object_type_title()
    {
        return __('Block', 'rp_wcec');
    }

    /**
     * Move editor
     *
     * @access public
     * @return void
     */
    public function move_editor()
    {
        global $_wp_post_type_features;

        // Check if editor is set
        if (isset($_wp_post_type_features[self::$post_type]['editor']) && $_wp_post_type_features[self::$post_type]['editor']) {

            // Unset editor
            unset($_wp_post_type_features[self::$post_type]['editor']);

            // Render meta box with editor in another location
            add_meta_box(
                self::$post_type . '_content',
                __('Block Content', 'rp_wcec'),
                array($this, 'render_meta_box_editor'),
                self::$post_type,
                'normal',
                'low'
            );
        }
    }

    /**
     * Render edit page meta box Editor content
     *
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box_editor($post)
    {
        $object = self::cache($post->ID);

        if (!$object) {
            return;
        }

        echo '<div class="wp-editor-wrap">';
        wp_editor($object->get_content(), 'content');
        echo '</div>';
    }

    /**
     * Add list columns
     *
     * @access public
     * @param array $columns
     * @return array
     */
    public function add_list_columns($columns)
    {
        $columns['block_title'] = __('Title', 'rp_wcec');
        $columns['status']      = __('Status', 'rp_wcec');
        return $columns;
    }

    /**
     * Print list column value
     *
     * @access public
     * @param array $column_key
     * @return array
     */
    public function print_list_column_value($column_key)
    {
        switch ($column_key) {

            case 'block_title':
                RightPress_Helper::print_link_to_post($this->get_id(), $this->get_title(), '<span class="rp_wcec_row_title_cell">', '</span>');
                $this->print_post_actions();
                break;

            case 'status':
                echo '<a class="rp_wcec_status_' . $this->get_status() . '" href="edit.php?post_type=' . self::$post_type . '&amp;' . self::$post_type . '_status=' . $this->get_status() . '">' . $this->get_status_title() . '</a>';
                break;

            default:
                break;
        }
    }

    /**
     * Populate existing object with properties unique to this object type
     *
     * @access public
     * @return void
     */
    public function populate_own_properties()
    {
        // Get content
        $this->content = RP_WCEC::get_post_content($this->id);
    }

    /**
     * Get post type
     *
     * @access public
     * @return string
     */
    public function get_post_type()
    {
        return self::$post_type;
    }

    /**
     * Get post type short version
     *
     * @access public
     * @return string
     */
    public function get_post_type_short()
    {
        return self::$post_type_short;
    }

    /**
     * Get meta properties
     *
     * @access public
     * @return array
     */
    public function get_meta_properties()
    {
        return self::$meta_properties;
    }

    /**
     * Add custom editor buttons
     *
     * @access public
     * @return void
     */
    public function add_editor_buttons()
    {
        global $typenow;

        // Check if we are on our own page
        if ($typenow !== self::$post_type) {
            return;
        }

        if (get_user_option('rich_editing') == 'true') {
            add_filter('mce_external_plugins', array($this, 'add_tinymce_plugin'));
            add_filter('mce_buttons', array($this, 'register_custom_editor_buttons'));
            add_filter('tiny_mce_before_init', array($this, 'modify_tinymce_config'));
        }
    }

    /**
     * Add TinyMCE plugin
     *
     * @access public
     * @param array $plugins
     * @return array
     */
    public function add_tinymce_plugin($plugins)
    {
        $plugins['rp_wcec_editor_buttons'] = RP_WCEC_PLUGIN_URL . '/assets/js/rp-wcec-tinymce.js';
        return $plugins;
    }

    /**
     * Register custom TinyMCE editor buttons
     *
     * @access public
     * @param array $buttons
     * @return array
     */
    public function register_custom_editor_buttons($buttons)
    {
        array_push($buttons, 'rp_wcec_macros');
        return $buttons;
    }

    /**
     * Modify TinyMCE config
     *
     * @access public
     * @param array $config
     * @return array
     */
    public function modify_tinymce_config($config)
    {
        $config['init_instance_callback'] = 'function(ed) { rp_wcec_change_custom_editor_button_positions(); }';
        return $config;
    }

    /**
     * Add TinyMCE locales
     *
     * @access public
     * @param array $locales
     * @return array
     */
    public function add_tinymce_locales($locales)
    {
        $locales['rp_wcec_editor_buttons'] = RP_WCEC_PLUGIN_PATH . 'includes/lazy/rp-wcec-tinymce-locales.inc.php';
        return $locales;
    }

    /**
     * Print TinyMCE custom button config in footer
     *
     * @access public
     * @return array
     */
    public function print_tinymce_button_config()
    {
        global $typenow;

        if ($typenow !== $this->get_post_type()) {
            return;
        }

        // Set up config items
        $items = array(

            // Add macro config
            'macros' => RP_WCEC_Macros::get_all_macro_list(),
        );

        // Output script element
        echo '<script type="text/javascript">var rp_wcec_custom_button_config = ' . json_encode($items) . ';</script>';
    }

    /**
     * Display block content via shortcode
     *
     * @access public
     * @param array $atts
     * @return void
     */
    public function shortcode_block($atts = array())
    {
        // Extract attributes
        $atts = shortcode_atts(array('id' => ''), $atts);

        // No block ID passed or block does not exist
        if (empty($atts['id']) || !RightPress_Helper::post_type_is($atts['id'], self::$post_type)) {
            return '';
        }

        // Instantiate block object
        $block = self::cache($atts['id']);

        // Process any inner shortocodes and return block content
        return do_shortcode($block->content);
    }

    /**
     * Get list of all items for admin display
     *
     * @access public
     * @param bool $include_trashed
     * @return array
     */
    public static function get_list_of_all_items($include_trashed = false)
    {
        return parent::get_object_list_for_display(self::$post_type, 'title', null, $include_trashed);
    }

    /**
     * Add search contexts and meta whitelist
     *
     * @access public
     * @return array
     */
    public function expand_list_search_context_where_properties()
    {
        return array(
            'contexts' => array(),
            'meta_whitelist' => array(
                'title',
            ),
        );
    }


}

new RP_WCEC_Block();

}
