<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract post object class
 *
 * @class RP_WCEC_Post_Object
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_Post_Object')) {

class RP_WCEC_Post_Object
{
    // Define main post type
    protected static $main_post_type = 'wcec_email';

    // Define post types that use this class
    protected static $post_types = array(
        'wcec_email'        => 'RP_WCEC_Email',
        'wcec_block'        => 'RP_WCEC_Block',
        'wcec_log_entry'    => 'RP_WCEC_Log_Entry',
        'wcec_trigger'      => 'RP_WCEC_Trigger',
        'wcec_scheduler'    => 'RP_WCEC_Scheduler',
    );

    // Cache objects so they don't need to be retrieved more than once
    protected static $cache = array();

    // Cache lists of all available objects split by trashed / not trashed
    protected static $cache_all = array();

    /**
     * Constructor class
     *
     * @access public
     * @param int $id
     * @return void
     */
    public function __construct($id = null)
    {
        // Construct as specific object
        if ($id !== null) {
            $this->id = $id;
            $this->populate();
        }

        // Construct as post type controller
        if ($id === null) {
            add_action('plugins_loaded', array($this, 'set_up_parent_post_type_controller'), 1);
        }
    }

    /**
     * Set up post type controller
     *
     * @access public
     * @return void
     */
    public function set_up_parent_post_type_controller()
    {
        // Check environment
        if (!RP_WCEC::check_environment()) {
            return;
        }

        // Hook some actions on init
        $init_priority = method_exists($this, 'init_priority') ? $this->init_priority() : 99;
        add_action('init', array($this, 'on_init'), $init_priority);

        // Enable file uploads in admin when fields of type file are used
        add_action('post_edit_form_tag', array($this, 'add_enctype_attribute'));

        // Save object configuration
        add_action('save_post', array($this, 'save_configuration'), 9, 2);

        // Process status change when changed not from within edit page
        add_action('init', array($this, 'process_status_change'), 999);

        // Process duplicate action
        add_action('init', array($this, 'process_duplicate'), 999);

        // Allow filtering by custom taxonomies
        add_action('restrict_manage_posts', array($this, 'add_list_filters'));

        // Handle list filter query
        add_filter('parse_query', array($this, 'handle_list_filter_query'));

        // Remove date filter
        add_filter('months_dropdown_results', array($this, 'remove_date_filter'));

        // Manage list view columns
        add_action('manage_' . $this->get_post_type() . '_posts_columns', array($this, 'manage_list_columns'));

        // Manage list view columns values
        add_action('manage_' . $this->get_post_type() . '_posts_custom_column', array($this, 'manage_list_column_values'), 10, 2);

        // Manage list views
        add_filter('views_edit-' . $this->get_post_type(), array($this, 'manage_list_views'));

        // Manage list bulk actions
        add_filter('bulk_actions-edit-' . $this->get_post_type(), array($this, 'manage_list_bulk_actions'));

        // Expand list search context
        add_filter('posts_join', array($this, 'expand_list_search_context_join'));
        add_filter('posts_where', array($this, 'expand_list_search_context_where'));
        add_filter('posts_groupby', array($this, 'expand_list_search_context_group_by'));

        // Remove default post row actions
        add_filter('post_row_actions', array($this, 'remove_post_row_actions'));

        // Object post trashed
        add_action('trashed_post', array($this, 'post_trashed'));

        // Change default post updated notice
        add_filter('post_updated_messages', array($this, 'change_post_updated_notice'));

        // Other hooks
        add_action('add_meta_boxes', array($this, 'remove_meta_boxes'), 99, 2);

        // Process bulk actions
        add_action('admin_init', array($this, 'process_bulk_actions'));
    }

    /**
     * Get ID
     *
     * @access public
     * @return int
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Get title
     *
     * @access public
     * @return int
     */
    public function get_title()
    {
        return isset($this->title) ? $this->title : null;
    }

    /**
     * Get content
     *
     * @access public
     * @return int
     */
    public function get_content()
    {
        return isset($this->content) ? $this->content : null;
    }

    /**
     * Get status
     *
     * @access public
     * @return int
     */
    public function get_status()
    {
        return isset($this->status) ? $this->status : null;
    }

    /**
     * Get status title
     *
     * @access public
     * @return int
     */
    public function get_status_title()
    {
        return isset($this->status_title) ? $this->status_title : null;
    }

    /**
     * Run on WP init
     *
     * @access public
     * @return void
     */
    public function on_init()
    {
        // Add post type
        $this->add_post_type();

        // Allow children to do their own stuff on init
        if (method_exists($this, 'on_init_own')) {
            $this->on_init_own();
        }
    }

    /**
     * Check if post type is configurable
     *
     * @access public
     * @return bool
     */
    public function is_configurable()
    {
        return true;
    }

    /**
     * Add post type
     *
     * @access public
     * @return void
     */
    public function add_post_type()
    {
        // Get admin capability
        $admin_capability = RP_WCEC::get_admin_capability();

        // Set up capabilities
        $capabilities = array_merge(array(
            'edit_post'             => $admin_capability,
            'read_post'             => $admin_capability,
            'delete_post'           => $admin_capability,
            'edit_posts'            => $admin_capability,
            'edit_others_posts'     => $admin_capability,
            'delete_posts'          => $admin_capability,
            'publish_posts'         => $admin_capability,
            'read_private_posts'    => $admin_capability,
        ), $this->get_post_type_custom_capabilities());

        // Define settings
        $args = array_merge($this->get_post_type_labels(), array(
            'public'                => $this->is_post_type_public(),
            'show_ui'               => $this->show_post_type_ui(),
            'show_in_menu'          => $this->show_post_type_in_menu(),
            'menu_position'         => $this->post_type_menu_position(),
            'capabilities'          => $capabilities,
            'supports'              => $this->post_type_supports(),
            'register_meta_box_cb'  => array($this, 'add_meta_boxes'),
        ));

        // Register new post type
        register_post_type($this->get_post_type(), $args);

        // Register custom taxonomies
        $this->register_taxonomies();
    }

    /**
     * Get custom capabilities for custom post type
     *
     * @access public
     * @return array
     */
    public function get_post_type_custom_capabilities()
    {
        return array();
    }

    /**
     * Check if post type is public
     *
     * @access public
     * @return bool
     */
    public function is_post_type_public()
    {
        return false;
    }

    /**
     * Check if post type UI needs to be displayed
     *
     * @access public
     * @return bool
     */
    public function show_post_type_ui()
    {
        return true;
    }

    /**
     * Specify menu to add this post type to
     *
     * @access public
     * @return string
     */
    public function show_post_type_in_menu()
    {
        if ($this->get_post_type() !== self::$main_post_type) {
            return 'edit.php?post_type=' . self::$main_post_type;
        }
    }

    /**
     * Specify position in admin menu
     *
     * @access public
     * @return int
     */
    public function post_type_menu_position()
    {
        return 58;
    }

    /**
     * Define post type support for WP UI elements
     *
     * @access public
     * @return array
     */
    public function post_type_supports()
    {
        return array();
    }

    /**
     * Get taxonomies
     *
     * @access public
     * @return array
     */
    public function get_taxonomies()
    {
        return array();
    }

    /**
     * Register custom taxonomies
     *
     * @access public
     * @return void
     */
    public function register_taxonomies()
    {
        // Iterate over taxonomies
        foreach ($this->get_taxonomies() as $key => $labels) {

            $taxonomy_key = $this->get_post_type() . '_' . $key;

            // Register taxonomy
            register_taxonomy($taxonomy_key, $this->get_post_type(), array(
                'label'             => $labels['singular'],
                'labels'            => array(
                    'name'          => $labels['plural'],
                    'singular_name' => $labels['singular'],
                ),
                'public'            => false,
                'show_admin_column' => true,
                'query_var'         => true,
            ));

            // Register custom terms for this taxonomy
            $method = 'get_' . $key . '_list';

            foreach ($this->$method() as $term_key => $term) {
                if (!term_exists($term_key, $taxonomy_key)) {
                    wp_insert_term($term['title'], $taxonomy_key, array(
                        'slug' => $term_key,
                    ));
                }
            }
        }
    }

    /**
     * Add meta boxes
     *
     * @access public
     * @return void
     */
    public function add_meta_boxes($post)
    {
        // Proceed only if post type is ours
        if ($post->post_type !== $this->get_post_type()) {
            return;
        }

        // Add default meta boxes to configurable objects
        if ($this->is_configurable() && method_exists($this, 'get_object_type_title')) {

            // Settings
            add_meta_box(
                $this->get_post_type() . '_settings',
                $this->get_object_type_title() . ' ' . __('Settings', 'rp_wcec'),
                array($this, 'render_meta_box_settings'),
                $this->get_post_type(),
                'normal',
                'high'
            );

            // Actions
            add_meta_box(
                $this->get_post_type() . '_actions',
                $this->get_object_type_title() . ' ' . __('Actions', 'rp_wcec'),
                array($this, 'render_meta_box_actions'),
                $this->get_post_type(),
                'side',
                'high'
            );

            // Allow child classes to add their own metaboxes
            if (method_exists($this, 'add_own_meta_boxes')) {
                $this->add_own_meta_boxes($post);
            }
        }
    }

    /**
     * Render edit page meta box Settings content
     *
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box_settings($post)
    {
        // Proceed only if post type is ours and it object is configurable
        if ($post->post_type !== $this->get_post_type()) {
            return;
        }

        $object = self::cache($post->ID);

        if (!$object) {
            return;
        }

        // Get additional variables
        if (method_exists($object, 'get_meta_box_variables')) {
            $variables = (array) $object->get_meta_box_variables('settings');
            extract($variables);
        }

        // Load view
        include RP_WCEC_PLUGIN_PATH . 'includes/views/' . $object->get_post_type_short() . '/settings.php';
    }

    /**
     * Render edit page meta box Actions content
     *
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box_actions($post)
    {
        // Proceed only if post type is ours and it object is configurable
        if ($post->post_type !== $this->get_post_type()) {
            return;
        }

        $object = self::cache($post->ID);

        if (!$object) {
            return;
        }

        // Get object id
        $id = $object->get_id();

        // Get actions
        $actions = $object->get_action_list();

        // Load view
        include RP_WCEC_PLUGIN_PATH . 'includes/views/' . $object->get_post_type_short() . '/actions.php';

        // Show preloaded when JS interface is used
        if (in_array($object->get_post_type_short(), array('trigger'), true)) {
            add_action('dbx_post_sidebar', array($this, 'include_preloader'));
        }
    }

    /**
     * Include preloader
     *
     * @access public
     * @return void
     */
    public function include_preloader()
    {
        include RP_WCEC_PLUGIN_PATH . 'includes/views/shared/preloader.php';
    }

    /**
     * Get array of actions available
     *
     * @access public
     * @return array
     */
    public function get_action_list()
    {
        $actions = array();

        // Make sure we know object type title
        if (!method_exists($this, 'get_object_type_title')) {
            return $actions;
        }

        // Save
        $actions['save'] = __('Save', 'rp_wcec') . ' ' . $this->get_object_type_title();

        // Get status
        $status = $this->get_status();

        // New item?
        if (empty($status)) {
            return $actions;
        }

        // Enable
        if ($status == 'disabled') {
            $actions['enable'] = __('Enable', 'rp_wcec') . ' ' . $this->get_object_type_title();
        }

        // Disable
        if ($status == 'enabled') {
            $actions['disable'] = __('Disable', 'rp_wcec') . ' ' . $this->get_object_type_title();
        }

        return $actions;
    }

    /**
     * Add enctype attribute to form tag
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function add_enctype_attribute($post)
    {
        // Check if this is our post type
        if (RightPress_Helper::post_type_is($post, $this->get_post_type())) {
            echo ' enctype="multipart/form-data" ';
        }
    }

    /**
     * Save post object field values
     *
     * @access public
     * @param int $post_id
     * @param object $post
     * @param array $posted
     * @return void
     */
    public function save_configuration($post_id, $post, $posted = array())
    {
        // Get posted values
        $posted = !empty($posted) ? $posted : $_POST;

        // Check if required properties were passed in
        if (empty($post_id) || empty($post)) {
            return;
        }

        // Proceed only if post type is ours and it is configurable
        if ($post->post_type !== $this->get_post_type() || !$this->is_configurable()) {
            return;
        }

        // Make sure user has permissions to edit config
        if (!RP_WCEC::is_admin()) {
            return;
        }

        // Make sure the correct post ID was passed from form
        if (empty($posted['post_ID']) || $posted['post_ID'] != $post_id) {
            return;
        }

        // Make sure it is not a draft save action
        if (defined('DOING_AUTOSAVE') || is_int(wp_is_post_autosave($post)) || is_int(wp_is_post_revision($post))) {
            return;
        }

        $object = self::cache($post_id);

        if (!$object) {
            return;
        }

        // Get object post type
        $object_post_type = $object->get_post_type();

        // Get action
        if (!empty($posted['rp_' . $object_post_type . '_button']) && $posted['rp_' . $object_post_type . '_button'] == 'actions' && !empty($posted['rp_' . $object_post_type . '_actions'])) {
            $action = $posted['rp_' . $object_post_type . '_actions'];
        }
        else {
            $action = 'save';
        }

        // Proceed depending on action
        switch ($action) {

            // Save
            case 'save':

                // Prevent infinite loop
                remove_action('save_post', array($this, 'save_configuration'), 9, 2);

                // New post?
                if ($post->post_status == 'draft') {

                    // Publish post
                    wp_publish_post($post_id);

                    // Enable
                    $object->enable();

                    // New post published
                    $object->new_post_published();
                }

                // Save meta fields
                foreach ($object->get_meta_properties() as $property) {

                    // Get value
                    if (method_exists($object, 'save_configuration_value')) {
                        $value = $object->save_configuration_value($property, $posted['rp_wcec']);
                    }
                    else {
                        $value = isset($posted['rp_wcec'][$property]) ? $posted['rp_wcec'][$property] : '';
                    }

                    // Save field
                    $object->update_field($property, $value);
                }

                add_action('save_post', array($this, 'save_configuration'), 9, 2);

                break;

            // Enable
            case 'enable':
                $object->enable();
                break;

            // Disable
            case 'disable':
                $object->disable();
                break;

            // Clear scheduled emails
            case 'clear_scheduled':
                $object->clear_scheduled_emails();

            default:
                break;
        }
    }

    /**
     * Update single field
     *
     * @access public
     * @return void
     */
    protected function update_field($field, $value)
    {
        switch ($field) {

            // Update status
            case 'status':

                // Get list of known statuses
                $statuses = $this->get_status_list();

                if (isset($statuses[$value])) {
                    $this->status = $value;
                    $this->status_title = $statuses[$value]['title'];
                    wp_set_object_terms($this->id, $value, $this->get_post_type() . '_status');
                }
                break;

            // Update post meta field
            default:
                if (method_exists($this, 'update_own_field') && $this->update_own_field($field, $value)) {
                    // Do nothing, executed one line above
                }
                else if (in_array($field, $this->get_meta_properties())) {
                    $this->$field = $value;
                    update_post_meta($this->id, $field, $value);
                }
                break;
        }
    }

    /**
     * Define and return default statuses
     *
     * @access public
     * @return array
     */
    public function get_status_list()
    {
        return array(
            'enabled'   => array(
                'title' => __('enabled', 'rp_wcec'),
            ),
            'disabled'    => array(
                'title' => __('disabled', 'rp_wcec'),
            ),
        );
    }

    /**
     * Get term title from slug
     *
     * @access public
     * @param string $taxonomy
     * @param string $slug
     * @return string
     */
    public function get_term_title_from_slug($taxonomy, $slug)
    {
        $method = 'get_' . $taxonomy . '_list';
        $list = $this->$method();
        return isset($list[$slug]) ? $list[$slug]['title'] : '';
    }

    /**
     * Change object status
     *
     * @access public
     * @param string $new_status
     * @return void
     */
    public function change_status($new_status)
    {
        $this->update_field('status', $new_status);
    }

    /**
     * Enable object
     *
     * @access public
     * @return void
     */
    public function enable()
    {
        $this->update_field('status', 'enabled');
    }

    /**
     * Enable object
     *
     * @access public
     * @return void
     */
    public function disable()
    {
        $this->update_field('status', 'disabled');
    }

    /**
     * Check if object is enabled
     *
     * @access public
     * @return bool
     */
    public function is_enabled()
    {
        $is_enabled = $this->get_status() === 'enabled';
        return apply_filters('rp_' . $this->get_post_type() . '_is_enabled', $is_enabled, $this);
    }

    /**
     * Check list action request and return object if action request is valid
     *
     * @access public
     * @param string $action
     * @return mixed
     */
    public function get_valid_list_action_object($action)
    {
        // Check action
        if (!isset($_REQUEST[$action])) {
            return false;
        }

        // Make sure this is our post type
        if (!isset($_REQUEST['post_type']) || $_REQUEST['post_type'] !== $this->get_post_type()) {
            return false;
        }

        // Make sure user is allowed to execute this action
        if (!RP_WCEC::is_admin()) {
            return false;
        }

        // Make sure object is of this type
        if (!isset($_REQUEST['wcec_object_id']) || !RightPress_Helper::post_type_is($_REQUEST['wcec_object_id'], $this->get_post_type())) {
            return false;
        }

        // Load object
        $object = self::cache($_REQUEST['wcec_object_id']);

        // No such object
        if (!$object) {
            return false;
        }

        // Return object
        return $object;
    }

    /**
     * Process status change request
     *
     * @access public
     * @return void
     */
    public function process_status_change()
    {
        // Get object
        $object = $this->get_valid_list_action_object('wcec_status_change');

        // Check if request is valid
        if (!$object) {
            return;
        }

        // Get status list
        $status_list = $this->get_status_list();
        $new_status = $_REQUEST['wcec_status_change'];

        // Make sure status is valid
        if (!isset($status_list[$new_status])) {
            return;
        }

        // Change status
        if ($new_status === 'enabled') {
            $object->enable();
        }
        else if ($new_status === 'disabled') {
            $object->disable();
        }

        // Get redirect URL
        $redirect_url = remove_query_arg(array('wcec_status_change', 'wcec_object_id'));

        // Redirect user and exit
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Process status change request
     *
     * @access public
     * @return void
     */
    public function process_duplicate()
    {
        // Get object
        $object = $this->get_valid_list_action_object('wcec_duplicate');

        // Check if request is valid
        if (!$object) {
            return;
        }

        // Make sure that object is configurable
        if (!$this->is_configurable()) {
            return;
        }

        // Duplicate object
        $object->duplicate();

        // Get redirect URL (redirecting to default list page without any
        // filters to make sure duplicate is displayed and not filtered out)
        $redirect_url = admin_url('/edit.php?post_type=' . $this->get_post_type());

        // Redirect user and exit
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Add filtering capabilities
     *
     * @access public
     * @return void
     */
    public function add_list_filters()
    {
        global $typenow;
        global $wp_query;

        if ($typenow != $this->get_post_type()) {
            return;
        }

        // Iterate over taxonomies
        foreach ($this->get_taxonomies() as $key => $labels) {

            $taxonomy_key = $this->get_post_type() . '_' . $key;

            // Extract selected filter options
            $selected = array();

            if (!empty($wp_query->query[$taxonomy_key]) && is_numeric($wp_query->query[$taxonomy_key])) {
                $selected[$taxonomy_key] = $wp_query->query[$taxonomy_key];
            }
            else if (!empty($wp_query->query[$taxonomy_key])) {
                $term = get_term_by('slug', $wp_query->query[$taxonomy_key], $taxonomy_key);
                $selected[$taxonomy_key] = $term ? $term->term_id : 0;
            }
            else {
                $selected[$taxonomy_key] = 0;
            }

            // Add options
            wp_dropdown_categories(array(
                'show_option_all'   =>  $labels['all'],
                'taxonomy'          =>  $taxonomy_key,
                'name'              =>  $taxonomy_key,
                'selected'          =>  $selected[$taxonomy_key],
                'show_count'        =>  true,
                'hide_empty'        =>  false,
            ));
        }
    }

    /**
     * Handle list filter query
     *
     * @access public
     * @param object $query
     * @return void
     */
    public function handle_list_filter_query($query)
    {
        global $pagenow;
        global $typenow;

        if ($pagenow != 'edit.php' || $typenow != $this->get_post_type()) {
            return;
        }

        $qv = &$query->query_vars;

        // Iterate over taxonomies
        foreach ($this->get_taxonomies() as $key => $labels) {

            $taxonomy_key = $this->get_post_type() . '_' . $key;

            if (isset($qv[$taxonomy_key]) && is_numeric($qv[$taxonomy_key]) && $qv[$taxonomy_key] != 0) {
                $term = get_term_by('id', $qv[$taxonomy_key], $taxonomy_key);
                $qv[$taxonomy_key] = $term->slug;
            }
        }
    }

    /**
     * Remove date filter
     *
     * @access public
     * @param array $months
     * @return void
     */
    public function remove_date_filter($months)
    {
        global $typenow;

        // Only proceed if this call is for our post type and our object is not date dependent
        if ($typenow === $this->get_post_type() && $this->is_configurable()) {
            return array();
        }

        return $months;
    }

    /**
     * Manage list columns
     *
     * @access public
     * @param array $columns
     * @return array
     */
    public function manage_list_columns($columns)
    {
        global $typenow;

        $new_columns = array();

        foreach ($columns as $column_key => $column) {
            $allowed_columns = array();

            if ($this->is_configurable()) {
                $allowed_columns[] = 'cb';
            }

            if (in_array($column_key, $allowed_columns)) {
                $new_columns[$column_key] = $column;
            }
        }

        // Allow children to add more columns
        if (method_exists($this, 'add_list_columns')) {
            $new_columns = $this->add_list_columns($new_columns);
        }

        return $new_columns;
    }

    /**
     * Manage list column values
     *
     * @access public
     * @param array $column
     * @param int $post_id
     * @return void
     */
    public function manage_list_column_values($column, $post_id)
    {
        // Load object
        $object = self::cache($post_id);

        // No object?
        if (!$object) {
            return;
        }

        // Allow children to add their own column values
        if (method_exists($object, 'print_list_column_value')) {
            $object->print_list_column_value($column);
        }
    }

    /**
     * Print post actions
     *
     * @access public
     * @return void
     */
    public function print_post_actions()
    {
        global $post;
        $post_type_object = get_post_type_object($this->get_post_type());

        // Store actions
        $actions = array();

        // Edit
        if ($post->post_status !== 'trash') {
            $actions['edit'] = '<a href="' . get_edit_post_link($post->ID, true) . '" title="' . esc_attr(__('Edit', 'rp_wcec')) . '">' . __('Edit', 'rp_wcec') . '</a>';
        }

        // Duplicate
        if ($post->post_status !== 'trash' && $this->is_configurable()) {
            $url = add_query_arg(array(
                'wcec_duplicate'    => '1',
                'wcec_object_id'    => $this->get_id(),
            ));
            $actions['duplicate'] = '<a href="' . $url . '" title="' .  __('Duplicate', 'rp_wcec') . '">' . __('Duplicate', 'rp_wcec') . '</a>';
        }

        // Add status change action if this object supports it
        if ($post->post_status !== 'trash' && $this->is_configurable()) {

            // Enable
            if (!$this->is_enabled()) {
                $url = add_query_arg(array(
                    'wcec_status_change'    => 'enabled',
                    'wcec_object_id'        => $this->get_id(),
                ));
                $actions['enable'] = '<a href="' . $url . '" title="' .  __('Enable', 'rp_wcec') . '">' . __('Enable', 'rp_wcec') . '</a>';
            }

            // Disable
            if ($this->is_enabled()) {
                $url = add_query_arg(array(
                    'wcec_status_change'    => 'disabled',
                    'wcec_object_id'        => $this->get_id(),
                ));
                $actions['disable'] = '<a href="' . $url . '" title="' .  __('Disable', 'rp_wcec') . '">' . __('Disable', 'rp_wcec') . '</a>';
            }
        }

        // Trash
        if ($post->post_status !== 'trash' && EMPTY_TRASH_DAYS) {
            $actions['trash'] = '<a class="submitdelete" title="' . esc_attr(__('Trash', 'rp_wcec')) . '" href="' . get_delete_post_link($post->ID) . '">' . __('Trash', 'rp_wcec') . '</a>';
        }

        // Delete
        if ($post->post_status !== 'trash' && !EMPTY_TRASH_DAYS) {
            $actions['delete'] = '<a class="submitdelete" title="' . esc_attr(__('Delete Permanently', 'rp_wcec')) . '" href="' . get_delete_post_link($post->ID, '', true) . '">' . __('Delete Permanently', 'rp_wcec') . '</a>';
        }

        // Untrash
        if ($post->post_status === 'trash') {
            $actions['untrash'] = '<a title="' . esc_attr(__('Restore', 'rp_wcec')) . '" href="' . wp_nonce_url(admin_url(sprintf($post_type_object->_edit_link . '&amp;action=untrash', $post->ID)), 'untrash-post_' . $post->ID) . '">' . __('Restore', 'rp_wcec') . '</a>';
        }

        // Style action links
        foreach ($actions as $action_key => $action_link) {
            $actions[$action_key] = '<span class="' . $action_key . '">' . $action_link . '</span>';
        }

        // Print post actions row
        echo '<div class="row-actions">' . join(' | ', $actions) . '</div>';
    }

    /**
     * Manage list views
     *
     * @access public
     * @param array $views
     * @return array
     */
    public function manage_list_views($views)
    {
        $new_views = array();

        foreach ($views as $view_key => $view) {
            if (in_array($view_key, array('all', 'trash'))) {
                $new_views[$view_key] = $view;
            }
        }

        return $new_views;
    }

    /**
     * Manage list bulk actions
     *
     * @access public
     * @param array $actions
     * @return array
     */
    public function manage_list_bulk_actions($actions)
    {
        $new_actions = array();

        if ($this->is_configurable()) {
            foreach ($actions as $action_key => $action) {
                if (in_array($action_key, array('trash', 'untrash', 'delete'))) {
                    $new_actions[$action_key] = $action;
                }
            }
        }

        return $new_actions;
    }

    /**
     * Expand list search context
     *
     * @access public
     * @param string $join
     * @return string
     */
    public function expand_list_search_context_join($join)
    {
        global $typenow;
        global $pagenow;
        global $wpdb;

        if ($pagenow == 'edit.php' && $typenow == $this->get_post_type() && isset($_GET['s']) && $_GET['s'] != '') {
            $join .= 'LEFT JOIN ' . $wpdb->postmeta . ' pm ON ' . $wpdb->posts . '.ID = pm.post_id ';
        }

        return $join;
    }

    /**
     * Expand list search context with more fields
     *
     * @access public
     * @param string $where
     * @return string
     */
    public function expand_list_search_context_where($where)
    {
        global $typenow;
        global $pagenow;
        global $wpdb;

        // Define post types with search contexts, meta field whitelist (searchable meta fields) etc
        if (method_exists($this, 'expand_list_search_context_where_properties')) {
            $post_types = array(
                $this->get_post_type() => $this->expand_list_search_context_where_properties(),
            );
        }
        else {
            $post_types = array();
        }

        // Search
        if ($pagenow == 'edit.php' && isset($_GET['post_type']) && isset($post_types[$_GET['post_type']]) && !empty($_GET['s'])) {

            $search_phrase = trim($_GET['s']);
            $exact_match = false;
            $context = null;

            // Exact match?
            if (preg_match('/^\".+\"$/', $search_phrase) || preg_match('/^\'.+\'$/', $search_phrase)) {
                $exact_match = true;
                $search_phrase = substr($search_phrase, 1, -1);
            }
            else if (preg_match('/^\\\\\".+\\\\\"$/', $search_phrase) || preg_match('/^\\\\\'.+\\\\\'$/', $search_phrase)) {
                $exact_match = true;
                $search_phrase = substr($search_phrase, 2, -2);
            }
            // Or search with context?
            else {
                foreach ($post_types[$_GET['post_type']]['contexts'] as $context_key => $context_value) {
                    if (preg_match('/^' . $context_key . '\:/i', $search_phrase)) {
                        $context = $context_value;
                        $search_phrase = trim(preg_replace('/^' . $context_key . '\:/i', '', $search_phrase));
                        break;
                    }
                }
            }

            // Search by ID?
            if ($context == 'ID') {
                $replacement = $wpdb->prepare(
                    '(' . $wpdb->posts . '.ID LIKE %s)',
                    $search_phrase
                );
            }

            // Search within other context
            else if ($context) {
                $replacement = $wpdb->prepare(
                    '(pm.meta_key LIKE %s) AND (pm.meta_value LIKE %s)',
                    $context,
                    $search_phrase
                );
            }

            // Regular search
            else {
                $whitelist = 'pm.meta_key IN (\'' . join('\', \'', $post_types[$_GET['post_type']]['meta_whitelist']) . '\')';

                // Exact match?
                if ($exact_match) {
                    $replacement = $wpdb->prepare(
                        '(' . $wpdb->posts . '.ID LIKE %s) OR (pm.meta_value LIKE %s)',
                        $search_phrase,
                        $search_phrase
                    );
                    $replacement = '(' . $whitelist . ' AND ' . $replacement . ')';

                }

                // Regular match
                else {
                    $replacement = '(' . $whitelist . ' AND ((' . $wpdb->posts . '.ID LIKE $1) OR (pm.meta_value LIKE $1)))';
                }
            }

            $where = preg_replace('/\(\s*' . $wpdb->posts . '.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/', $replacement, $where);
        }

        return $where;
    }

    /**
     * Expand list search context with more fields - group results by id
     *
     * @access public
     * @param string $groupby
     * @return string
     */
    public function expand_list_search_context_group_by($groupby)
    {
        global $typenow;
        global $pagenow;
        global $wpdb;

        if ($pagenow == 'edit.php' && $typenow == $this->get_post_type() && isset($_GET['s']) && $_GET['s'] != '') {
            $groupby = $wpdb->posts . '.ID';
        }

        return $groupby;
    }

    /**
     * Remove default post row actions
     *
     * @access public
     * @param array $actions
     * @return array
     */
    public function remove_post_row_actions($actions)
    {
        global $post;

        // Make sure it's our post type
        if (RightPress_Helper::post_type_is($post, $this->get_post_type())) {
            return array();
        }

        return $actions;
    }

    /**
     * Object trashed - disable it if object uses statuses
     *
     * @access public
     * @param int $post_id
     * @return void
     */
    public function post_trashed($post_id)
    {
        // Make sure it's our post type
        if (!RightPress_Helper::post_type_is($post_id, $this->get_post_type())) {
            return;
        }

        // Load object
        $object = self::cache($post_id);

        // No object?
        if (!$object) {
            return;
        }

        // Disable object if it is configurable and has method disable
        if ($object->is_configurable()) {
            $object->disable();
        }
    }

    /**
     * Load object from cache
     *
     * @access public
     * @param string $type
     * @param int $id
     * @return object
     */
    public static function cache($id)
    {
        // Check if object exists in cache
        if (!isset(self::$cache[$id])) {

            // Get object by ID
            $object = self::get_by_id($id);

            // Check if object was retrieved
            if (!$object) {
                return false;
            }

            // Store in cache
            self::$cache[$id] = $object;
        }

        // Return from cache
        return self::$cache[$id];
    }

    /**
     * Get object by it's id
     *
     * @access public
     * @param int $id
     * @return object
     */
    public static function get_by_id($id)
    {
        // Check if post ID is numeric
        if (is_numeric($id)) {

            // Retrieve post
            $post = get_post($id);

            // Check if post is of known type
            if ($post && isset(self::$post_types[$post->post_type])) {

                // Retrieve class name by post type
                $class_name = self::$post_types[$post->post_type];

                // Initialize and return object
                return new $class_name($id);
            }
        }

        // Nothing found
        return false;
    }

    /**
     * Get list of all items for admin display
     *
     * @access public
     * @param string $post_type
     * @param string $title_key
     * @param string $subtitle_key
     * @param bool $include_trashed
     * @return array
     */
    public static function get_object_list_for_display($post_type, $title_key = 'title', $subtitle_key = null, $include_trashed = false)
    {
        $items = array();

        // Check if list of objects with this post type exists in cache
        if (!isset(self::$cache_all[$post_type])) {

            // Define object list for this post type
            self::$cache_all[$post_type] = array(
                'trashed'       => array(),
                'not_trashed'   => array(),
            );

            // Retrieve and iterate over all objects of this type
            foreach (self::get_all_objects($post_type) as $object) {

                // Check if corresponding post is trashed
                $is_trashed = RightPress_Helper::post_is_trashed($object->id);
                $trashed_key = $is_trashed ? 'trashed' : 'not_trashed';

                // Get suffix to display
                $trashed_suffix = ((!empty($subtitle_key) && !empty($object->$subtitle_key)) ? (' (' . $object->$subtitle_key . ')') : '');
                $trashed_suffix .= RP_WCEC::trashed_suffix($object->id, $is_trashed);

                // Add to list
                self::$cache_all[$post_type][$trashed_key][$object->id] = $object->$title_key . $trashed_suffix;
            }
        }

        // Add not trashed items to results array
        $items = self::$cache_all[$post_type]['not_trashed'];

        // Maybe add trashed items to results array
        if ($include_trashed) {
            $items = $items + self::$cache_all[$post_type]['trashed'];
        }

        // Sort items by object id
        ksort($items);

        // Return items
        return $items;
    }

    /**
     * Get array with all objects
     *
     * @access public
     * @param string $post_type
     * @param array $meta_query
     * @param array $tax_query
     * @param int $limit
     * @return array
     */
    public static function get_all_objects($post_type, $meta_query = array(), $tax_query = array(), $limit = -1)
    {
        $objects = array();

        // Iterate list of all IDs and iterate over them
        foreach (self::get_list_of_all_ids($post_type, true, $meta_query, $tax_query, $limit) as $id) {

            // Try to get object from cache
            if ($object = self::cache($id)) {

                // Add object to list
                $objects[$id] = $object;
            }
        }

        // Return objects
        return $objects;
    }

    /**
     * Get list of all object ids
     *
     * @access public
     * @param string $post_type
     * @param bool $include_trashed
     * @param array $meta_query
     * @param array $tax_query
     * @param int $limit
     * @return array
     */
    public static function get_list_of_all_ids($post_type, $include_trashed = false, $meta_query = array(), $tax_query = array(), $limit = -1)
    {
        // Set up query
        $args = array(
            'post_type'         => $post_type,
            'post_status'       => array('publish', 'pending', 'draft', 'future', 'private'),
            'posts_per_page'    => $limit,
            'fields'            => 'ids',
        );

        // Maybe search for trashed objects
        if ($include_trashed) {
            $args['post_status'][] = 'trash';
        }

        // Maybe add meta query
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        // Maybe add tax query
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        // Run query
        $query = new WP_Query($args);

        // Return ids
        return $query->posts;
    }

    /**
     * Popuplate existing object with properties
     *
     * @access public
     * @return void
     */
    public function populate()
    {
        // Check if ID is set
        if (!$this->id) {
            return false;
        }

        // Get post
        $post = get_post($this->id);

        // All our posts must be either published or trashed
        if (!in_array($post->post_status, array('publish', 'trash'))) {
            return;
        }

        // Check if child uses status taxonomy
        if ($this->is_configurable() && method_exists($this, 'get_post_type')) {

            // Get post terms
            $post_terms = wp_get_post_terms($this->id, $this->get_post_type() . '_status');

            // Set status and status title
            $this->status = (isset($post_terms[0]) && is_object($post_terms[0])) ? RightPress_Helper::clean_term_slug($post_terms[0]->slug) : null;
            $this->status_title = $this->status ? $this->get_term_title_from_slug('status', $this->status) : null;
        }

        // Check if child stores some of its properties as post meta
        if (method_exists($this, 'get_meta_properties')) {

            // Get post meta
            $post_meta = RightPress_Helper::unwrap_post_meta(get_post_meta($this->id));

            // Set other properties
            foreach ($this->get_meta_properties() as $property) {
                $this->$property = isset($post_meta[$property]) ? maybe_unserialize($post_meta[$property]) : null;
            }
        }

        // Populate child-specific properties
        if (method_exists($this, 'populate_own_properties')) {
            $this->populate_own_properties();
        }
    }

    /**
     * Duplicate object
     *
     * @access public
     * @return void
     */
    public function duplicate()
    {
        // Get raw content of current object
        $current_post = get_post($this->id);
        $current_content = ((!empty($current_post) && !empty($current_post->post_content)) ? $current_post->post_content : '');

        // Add new post
        $post_id = wp_insert_post(array(
            'post_title'        => '',
            'post_content'      => $current_content,
            'post_name'         => '',
            'post_status'       => 'publish',
            'post_type'         => $this->get_post_type(),
            'ping_status'       => 'closed',
            'comment_status'    => 'closed',
        ));

        // Check if post was created
        if (!is_wp_error($post_id) && !empty($post_id)) {

            // Disable
            wp_set_object_terms($post_id, 'disabled', $this->get_post_type() . '_status');

            // Get post
            $post = get_post($post_id);

            // Check if post was loaded
            if (!$post) {
                return;
            }

            // Save object configuration
            $this->save_configuration($post_id, $post, array(
                'post_ID' => $post_id,
                'rp_wcec' => $this->get_duplicate_values(),
            ));
        }
    }

    /**
     * Get values for fields in duplicate object
     *
     * @access public
     * @return array
     */
    public function get_duplicate_values()
    {
        $values = array();

        // Iterate over meta properties
        foreach ($this->get_meta_properties() as $property) {

            // Allow child classes to modify values
            $value = $this->get_single_duplicate_value($property);

            // Check if custom value was returned
            if ($value) {
                $values = array_merge($values, $value);
            }
            else {

                // Prepend "Copy of" to the title field
                if ($property === 'title') {
                    $values[$property] = __('Copy of', 'rp_wcec') . ' ' . (isset($this->title) ? $this->title : '');
                }
                else {
                    $values[$property] = isset($this->$property) ? $this->$property : null;
                }
            }
        }

        return $values;
    }

    /**
     * Get single value for duplicate object
     *
     * @access public
     * @param string $property
     * @return mixed
     */
    public function get_single_duplicate_value($property)
    {
        return false;
    }

    /**
     * Redirect user to post edit page
     *
     * @access public
     * @return void
     */
    public function redirect_to_edit_page()
    {
        wp_redirect(admin_url('/post.php?post=' . $this->id . '&action=edit'));
        exit;
    }

    /**
     * New post published
     *
     * @access public
     * @return void
     */
    public function new_post_published()
    {
    }

    /**
     * Change default post updated message
     *
     * @access public
     * @param array $messages
     * @return array
     */
    public function change_post_updated_notice($messages)
    {
        global $post_ID;

        // Check if this is our post type
        if (get_post_type($post_ID) === $this->get_post_type()) {
            $messages['post'][4] = sprintf(__('%s updated.', 'rp_wcec'), $this->get_object_type_title());
        }

        return $messages;
    }

    /**
     * Remove meta boxes from own pages
     *
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function remove_meta_boxes($post_type, $post)
    {
        // Remove third party metaboxes from own pages
        if ($post_type === $this->get_post_type()) {

            // Allow developers to leave specific meta boxes
            $meta_boxes_to_leave = apply_filters('rp_wcec_meta_boxes_whitelist', array($this->get_post_type() . '_content'));

            foreach (self::get_meta_boxes() as $context => $meta_boxes_by_context) {
                foreach ($meta_boxes_by_context as $subcontext => $meta_boxes_by_subcontext) {
                    foreach ($meta_boxes_by_subcontext as $meta_box_id => $meta_box) {
                        if (!in_array($meta_box_id, $meta_boxes_to_leave)) {
                            remove_meta_box($meta_box_id, $post_type, $context);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get list of meta boxes for current screent
     *
     * @access public
     * @return array
     */
    public static function get_meta_boxes()
    {
        global $wp_meta_boxes;

        $screen = get_current_screen();
        $page = $screen->id;

        return isset($wp_meta_boxes[$page]) ? $wp_meta_boxes[$page] : array();
    }

    /**
     * Process bulk actions
     *
     * @access public
     * @return void
     */
    public function process_bulk_actions()
    {
        return;
    }


}
}
