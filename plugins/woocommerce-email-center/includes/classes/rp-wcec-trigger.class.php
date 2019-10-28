<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Trigger object class
 *
 * @class RP_WCEC_Trigger
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_Trigger')) {

class RP_WCEC_Trigger extends RP_WCEC_Post_Object
{
    // Define post type title
    protected static $post_type = 'wcec_trigger';
    protected static $post_type_short = 'trigger';

    // Define meta keys
    protected static $meta_properties = array(
        'title', 'event', 'email_id', 'schedule_method',
        'schedule_value', 'conditions', 'conditions_scheduled'
    );

    // Define object properties
    // Do not create new property 'action' - it was used in earlier versions and may cause issues when loading stored triggers
    protected $id;
    protected $title;
    protected $event;
    protected $email_id;
    protected $schedule_method;
    protected $schedule_value;
    protected $conditions;
    protected $conditions_scheduled;
    protected $status;
    protected $status_title;

    protected $email;

    // Special property to store flag of whether or not checkout is currently being processed
    protected static $is_checkout_processing = false;

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

        // Output hidden templates in admin footer
        add_action('admin_footer', array($this, 'output_hidden_templates'));

        // Load assets conditionally
        add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_assets'), 11);

        // Handle permanent trigger delete
        add_action('before_delete_post', array($this, 'post_deleted'));

        // Change default post updated notice
        add_action('draft_to_publish', array($this, 'trigger_was_published'));
        add_filter('post_updated_messages', array($this, 'change_post_updated_notice'));
    }

    /**
     * Get event
     *
     * @access public
     * @return int
     */
    public function get_event()
    {
        return isset($this->event) ? $this->event : null;
    }

    /**
     * Get email ID
     *
     * @access public
     * @return int
     */
    public function get_email_id()
    {
        return isset($this->email_id) ? $this->email_id : null;
    }

    /**
     * Get schedule value
     *
     * @access public
     * @return int
     */
    public function get_schedule_value()
    {
        return isset($this->schedule_value) ? $this->schedule_value : null;
    }

    /**
     * Get schedule method
     *
     * @access public
     * @return int
     */
    public function get_schedule_method()
    {
        return isset($this->schedule_method) ? $this->schedule_method : null;
    }

    /**
     * Declare custom init priority (default is 99)
     *
     * @access public
     * @return int
     */
    public function init_priority()
    {
        return 1;
    }

    /**
     * Own methods run on WP init
     *
     * @access public
     * @return void
     */
    public function on_init_own()
    {
        // Set up action hooks
        $this->set_up_action_hooks();
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
                'name'               => __('Email Triggers', 'rp_wcec'),
                'singular_name'      => __('Email Trigger', 'rp_wcec'),
                'add_new'            => __('Add Trigger', 'rp_wcec'),
                'add_new_item'       => __('Add New Trigger', 'rp_wcec'),
                'edit_item'          => __('Edit Trigger', 'rp_wcec'),
                'new_item'           => __('New Trigger', 'rp_wcec'),
                'all_items'          => __('Triggers', 'rp_wcec'),
                'view_item'          => __('View Trigger', 'rp_wcec'),
                'search_items'       => __('Search Triggers', 'rp_wcec'),
                'not_found'          => __('No Triggers Found', 'rp_wcec'),
                'not_found_in_trash' => __('No Triggers Found In Trash', 'rp_wcec'),
                'parent_item_colon'  => '',
                'menu_name'          => __('Triggers', 'rp_wcec'),
            ),
            'description' => __('Email Triggers', 'rp_wcec'),
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
        return __('Trigger', 'rp_wcec');
    }

    /**
     * Send variables to default meta box
     *
     * @access public
     * @param string $context
     * @return array
     */
    public function get_meta_box_variables($context)
    {
        // Settings metabox
        if ($context === 'settings') {

            // Get schedule value
            $schedule_value = $this->get_schedule_value();
            $schedule_value = (!empty($schedule_value) ? $schedule_value : '');

            // Return arrays
            return array(
                'schedule_value'            => $schedule_value,
                'schedule_number_value'     => (!in_array($this->get_schedule_method(), array('send_immediately', 'specific_date', 'next')) ? $schedule_value : ''),
                'schedule_date_value'       => ($this->get_schedule_method() === 'specific_date' ? $schedule_value : ''),
                'schedule_weekday_value'    => ($this->get_schedule_method() === 'next' ? $schedule_value : ''),
            );
        }

        return array();
    }

    /**
     * Process single configuration value
     *
     * @access public
     * @param string $property
     * @return mixed
     */
    public function save_configuration_value($property, $posted)
    {
        $value = '';

        // Schedule value field
        if ($property === 'schedule_value' && isset($posted['schedule_method'])) {

            switch ($posted['schedule_method']) {

                case 'send_immediately':
                    $value = '';
                    break;

                case 'specific_date':
                    $value = !empty($posted['schedule_date']) ? $posted['schedule_date'] : '';
                    break;

                case 'next':
                    $value = !empty($posted['schedule_weekday']) ? $posted['schedule_weekday'] : '';
                    break;

                default:
                    $value = isset($posted['schedule_number']) ? $posted['schedule_number'] : '';
                    break;
            }
        }
        // Conditions
        else if ($property === 'conditions') {
            if (!empty($posted['conditions'])) {
                $value = RP_WCEC_Conditions::process_submitted_conditions($posted['conditions'], self::$post_type_short);
            }
            else {
                $value = array();
            }
        }
        // Conditions that run on scheduled event
        else if ($property === 'conditions_scheduled') {

            if (!empty($posted['conditions_scheduled'])) {
                $value = RP_WCEC_Conditions::process_submitted_conditions($posted['conditions_scheduled'], self::$post_type_short);
            }
            else {
                $value = array();
            }
        }
        // Other fields
        else if (isset($posted[$property])) {
            $value = $posted[$property];
        }

        return $value;
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
        $columns['trigger_title']   = __('Title', 'rp_wcec');
        $columns['trigger_event']   = __('Event', 'rp_wcec');
        $columns['trigger_email']   = __('Email', 'rp_wcec');
        $columns['status']          = __('Status', 'rp_wcec');
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

            case 'trigger_title':
                RightPress_Helper::print_link_to_post($this->get_id(), $this->get_title(), '<span class="rp_wcec_row_title_cell">', '</span>');
                $this->print_post_actions();
                break;

            case 'trigger_event':
                echo self::get_trigger_event_title($this->get_event());
                break;

            case 'trigger_email':

                // Get email object
                if (!empty($this->email_id) && ($email = RP_WCEC_Post_Object::cache($this->email_id))) {
                    RightPress_Helper::print_link_to_post($email->get_id(), $email->get_subject());
                }
                else {
                    _e('Not available', 'rp_wcec');
                }

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
        // Convert scheduler date
        if ($this->schedule_method === 'specific_date') {
            $this->schedule_value = !empty($this->schedule_value) ? $this->schedule_value : '';
        }

        // Make sure conditions property is always array
        if (!is_array($this->conditions)) {
            $this->conditions = array();
        }
        if (!is_array($this->conditions_scheduled)) {
            $this->conditions_scheduled = array();
        }

        // Fix event for triggers stored in versions < 1.3
        if ($this->event === null) {
            $trigger_event = get_post_meta($this->id, 'action', true);
            $this->event = !empty($trigger_event) ? $trigger_event : null;
        }
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
     * Check if object exists by id
     *
     * @access public
     * @param int $id
     * @return bool
     */
    public static function exists($id)
    {
        return RightPress_Helper::post_type_is($id, self::$post_type);
    }

    /**
     * Output hidden templates for user interface
     *
     * @access public
     * @return void
     */
    public function output_hidden_templates()
    {
        global $pagenow;
        global $typenow;

        // Check if we are on our own page
        if (!in_array($pagenow, array('post.php', 'post-new.php')) || $typenow !== self::$post_type) {
            return;
        }

        // Include templates
        include RP_WCEC_PLUGIN_PATH . 'includes/views/shared/conditions_templates.php';
    }

    /**
     * Load backend assets conditionally
     *
     * @access public
     * @return bool
     */
    public function enqueue_backend_assets()
    {
        global $pagenow;
        global $typenow;
        global $post;

        // Check if we are on our own page
        if (!in_array($pagenow, array('post.php', 'post-new.php')) || $typenow !== self::$post_type) {
            return;
        }

        // Enqueue conditions control scripts
        wp_enqueue_script('rp-wcec-backend-conditions');

        // Enqueue schedule control scripts
        wp_enqueue_script('rp-wcec-backend-schedule');

        // Enqueue other trigger scripts
        wp_enqueue_script('rp-wcec-backend-trigger');

        // Pass configuration values to JS
        wp_localize_script('rp-wcec-backend-conditions', 'rp_wcec_object_config', self::get_object_properties_js($post));
        wp_localize_script('rp-wcec-backend-conditions', 'rp_wcec_object_multiselect_options', self::get_selected_option_labels($post));
        wp_localize_script('rp-wcec-backend-conditions', 'rp_wcec_event_supported_conditions', self::get_event_supported_conditions());
        wp_localize_script('rp-wcec-backend-trigger', 'rp_wcec_trigger', array(
            'notices' => array(
                'clear_scheduled_confirmation'  => __('Are you sure you want to clear all scheduled emails that are based on this trigger?', 'rp_wcec'),
                'condition_not_available'       => __('<strong>Warning:</strong> This condition is no longer available. Previously it was available due to integration with other plugin which is no longer active or due to custom code on your site which is no longer present. As a precaution, this placeholder prevents emails from being sent. Saving settings on this page will automatically remove this placeholder and it will no longer prevent emails from being sent. Please review other settings of this trigger to make sure emails are not sent to wrong people at a wrong time because of missing condition.', 'rp_wcec'),
            ),
        ));

        // Enqueue jQuery UI Datepicker
        RP_WCEC::enqueue_datepicker();
    }

    /**
     * Get object properties for use in JS interface builder
     *
     * @access public
     * @param object $post
     * @return array
     */
    public static function get_object_properties_js($post)
    {
        $properties = array();

        // Check if post already exists and is our post
        if (is_object($post) && !empty($post->ID) && RightPress_Helper::post_type_is($post->ID, self::$post_type)) {

            // Get object
            $object = self::cache($post->ID);

            // Property keys
            $keys = array(
                'id', 'title', 'event', 'email_id', 'schedule_method',
                'schedule_value', 'conditions', 'conditions_scheduled',
                'status', 'status_title'
            );

            // Iterate over keys and extract them
            foreach ($keys as $key) {
                $default = in_array($key, array('conditions', 'conditions_scheduled')) ? array() : '';
                $properties[$key] = isset($object->$key) ? $object->$key : $default;
            }
        }

        return $properties;
    }

    /**
     * Get selected multiselect option labels
     *
     * @access public
     * @param object $post
     * @return array
     */
    public static function get_selected_option_labels($post)
    {
        $labels = array();

        // Get trigger
        if (is_object($post) && isset($post->ID) && RightPress_Helper::post_type_is($post, self::$post_type)) {

            // Get object
            $object = self::cache($post->ID);

            // Get labels for both initial and scheduled checks
            foreach (array('conditions', 'conditions_scheduled') as $type) {

                // Get conditions
                $method = 'get_' . $type;
                $conditions = $object->$method();

                if (!empty($conditions) && is_array($conditions)) {
                    foreach ($conditions as $condition_key => $condition) {
                        foreach (RP_WCEC_Conditions::get_multiselect_field_keys() as $key) {
                            if (!empty($condition[$key]) && is_array($condition[$key])) {
                                $labels[$type][$condition_key][$key] = RP_WCEC_Conditions::get_items_by_ids($key, $condition[$key]);
                            }
                        }
                    }
                }
            }
        }

        return $labels;
    }

    /**
     * Get array with objects by event
     *
     * @access public
     * @param string $event
     * @return array
     */
    public static function get_trigger_objects($event = null)
    {
        $meta_query = array();

        // Set up meta query if we need to retrieve triggers by event
        if ($event !== null) {
            $meta_query[] = array(
                'key'       => 'event',
                'value'     => $event,
                'compare'   => '=',
            );

            // Ensure compatibility for triggers stored in pre-1.3
            $meta_query['relation'] = 'OR';
            $meta_query[] = array(
                'key'       => 'action',
                'value'     => $event,
                'compare'   => '=',
            );
        }

        // Get objects and return
        return self::get_all_objects(self::$post_type, $meta_query);
    }

    /**
     * Get trigger events
     *
     * @access public
     * @return void
     */
    public static function get_trigger_event_list()
    {
        // Define trigger events and return
        // Developers: make sure your event key is unique globally, not just in its own group
        // Using two filters to ensure compatibility as events were called actions pre-1.3
        return apply_filters('rp_wcec_trigger_actions', apply_filters('rp_wcec_trigger_events', array(

            // WooCommerce Cart
            'woocommerce_cart' => array(
                'label'     => __('WooCommerce Cart', 'rp_wcec'),
                'children'  => array(

                    // Add to cart
                    'add_to_cart' => array(
                        'label'         => __('Add to cart', 'rp_wcec'),
                        'hooks'         => 'woocommerce_add_to_cart',
                        'callback'      => array('RP_WCEC_Trigger', 'event_add_to_cart'),
                        'accepted_args' => 6,
                        'priority'      => 99,
                        'conditions'    => self::get_default_event_supported_conditions('woocommerce_cart', 'add_to_cart'),
                    ),
                ),
            ),

            // WooCommerce Order
            'woocommerce_order' => array(
                'label'     => __('WooCommerce Order', 'rp_wcec'),
                'children'  => array(

                    // New Order
                    'new_order' => array(
                        'label'         => __('New order', 'rp_wcec'),
                        'hooks'         => 'woocommerce_checkout_update_order_meta',
                        'callback'      => array('RP_WCEC_Trigger', 'event_new_order'),
                        'conditions'    => self::get_default_event_supported_conditions('woocommerce_order', 'new_order'),
                    ),

                    // Payment Complete
                    'payment_complete' => array(
                        'label'         => __('Payment complete', 'rp_wcec'),
                        'hooks'         => 'woocommerce_payment_complete',
                        'callback'      => array('RP_WCEC_Trigger', 'event_payment_complete'),
                        'conditions'    => self::get_default_event_supported_conditions('woocommerce_order', 'payment_complete'),
                    ),

                    // Status Change
                    'status_change' => array(
                        'label'         => __('Status change', 'rp_wcec'),
                        'hooks'         => 'woocommerce_order_status_changed',
                        'callback'      => array('RP_WCEC_Trigger', 'event_status_change'),
                        'accepted_args' => 3,
                        'conditions'    => self::get_default_event_supported_conditions('woocommerce_order', 'status_change'),
                    ),

                    // Customer Note
                    'customer_note'     => array(
                        'label'         => __('Customer note', 'rp_wcec'),
                        'hooks'         => 'woocommerce_new_customer_note',
                        'callback'      => array('RP_WCEC_Trigger', 'event_customer_note'),
                        'conditions'    => self::get_default_event_supported_conditions('woocommerce_order', 'customer_note'),
                    ),

                    // Refund Issued
                    'refund_issued' => array(
                        'label'         => __('Refund Issued', 'rp_wcec'),
                        'hooks'         => 'woocommerce_refund_created',
                        'callback'      => array('RP_WCEC_Trigger', 'event_refund_issued'),
                        'conditions'    => self::get_default_event_supported_conditions('woocommerce_order', 'refund_issued'),
                    ),
                ),
            ),

            // Customer
            'customer' => array(
                'label'     => __('Customer', 'rp_wcec'),
                'children'  => array(

                    // New Account
                    'new_account' => array(
                        'label'         => __('New account', 'rp_wcec'),
                        'hooks'         => array('user_register', 'profile_update'),
                        'callback'      => array('RP_WCEC_Trigger', 'event_new_account'),
                        'conditions'    => self::get_default_event_supported_conditions('customer', 'new_account'),
                    ),

                    // User Login
                    'user_login' => array(
                        'label'         => __('User login', 'rp_wcec'),
                        'hooks'         => 'wp_login',
                        'callback'      => array('RP_WCEC_Trigger', 'event_user_login'),
                        'accepted_args' => 2,
                        'conditions'    => self::get_default_event_supported_conditions('customer', 'user_login'),
                    ),

                    // Password Reset Request
                    'password_reset_request' => array(
                        'label'         => __('Password reset request', 'rp_wcec'),
                        'hooks'         => 'woocommerce_reset_password_notification',
                        'callback'      => array('RP_WCEC_Trigger', 'event_password_reset_request'),
                        'accepted_args' => 2,
                        'conditions'    => self::get_default_event_supported_conditions('customer', 'password_reset_request'),
                    ),

                    // Password Reset
                    'password_reset' => array(
                        'label'         => __('Password reset', 'rp_wcec'),
                        'hooks'         => 'password_reset',
                        'callback'      => array('RP_WCEC_Trigger', 'event_password_reset'),
                        'accepted_args' => 2,
                        'conditions'    => self::get_default_event_supported_conditions('customer', 'password_reset'),
                    ),

                    // Role Change
                    'role_change' => array(
                        'label'         => __('Role Change', 'rp_wcec'),
                        'hooks'         => 'set_user_role',
                        'callback'      => array('RP_WCEC_Trigger', 'event_role_change'),
                        'accepted_args' => 3,
                        'conditions'    => self::get_default_event_supported_conditions('customer', 'role_change'),
                    ),
                ),
            ),
        )));
    }

    /**
     * Get trigger events list
     *
     * @access public
     * @return array
     */
    public static function get_trigger_event_list_for_display()
    {
        $events = array();

        // Iterate over all trigger event groups
        foreach (self::get_trigger_event_list() as $trigger_event_group_key => $trigger_event_group) {

            // Iterate over trigger events
            foreach ($trigger_event_group['children'] as $trigger_event_key => $trigger_event) {

                // Add group if needed
                if (!isset($events[$trigger_event_group_key])) {
                    $events[$trigger_event_group_key] = array(
                        'label'     => $trigger_event_group['label'],
                        'options'   => array(),
                    );
                }

                // Push event to group
                $events[$trigger_event_group_key]['options'][$trigger_event_key] = $trigger_event['label'];
            }
        }

        return $events;
    }

    /**
     * Get trigger event name
     *
     * @access public
     * @param string $event_key
     * @return string
     */
    public static function get_trigger_event_title($event_key = '')
    {
        // Iterate over all trigger event groups
        foreach (self::get_trigger_event_list() as $trigger_event_group_key => $trigger_event_group) {

            // Iterate over trigger events
            foreach ($trigger_event_group['children'] as $trigger_event_key => $trigger_event) {

                // Check if current event is the one that we are after
                if ($trigger_event_key === $event_key) {
                    return $trigger_event_group['label'] . ': ' . $trigger_event['label'];
                }
            }
        }

        return '';
    }

    /**
     * Define support for conditions
     *
     * @access public
     * @param string $group_key
     * @param string $event_key
     * @return array
     */
    public static function get_default_event_supported_conditions($group_key, $event_key)
    {
        $conditions = array();

        // Add cart related conditions
        if ($group_key === 'woocommerce_cart') {
            $conditions = array_merge($conditions, array(
                'cart_subtotal', 'cart_coupons', 'cart_product',
                'cart_product_category', 'cart_product_attribute',
                'cart_product_tag'
            ));
        }

        // Add order related conditions
        if ($group_key === 'woocommerce_order') {
            $conditions = array_merge($conditions, array(
                'order_total', 'order_shipping', 'order_discount',
                'order_status', 'order_coupons', 'payment_method',
                'order_product', 'order_product_category',
                'order_product_attribute', 'order_product_tag',
                'billing_country', 'billing_state', 'billing_city',
                'billing_postcode', 'shipping_country', 'shipping_state',
                'shipping_city', 'shipping_postcode', 'shipping_zone'
            ));
        }

        // Add product related conditions
        if ($group_key === 'woocommerce_cart' && $event_key === 'add_to_cart') {
            $conditions = array_merge($conditions, array(
                'product', 'product_category', 'product_attribute',
                'product_tag'
            ));
        }

        // Add customer related conditions
        if (in_array($group_key, array('woocommerce_cart', 'woocommerce_order', 'customer'))) {
            $conditions = array_merge($conditions, array(
                'role', 'capability', 'customer', 'customer_meta_field',
                'amount_spent', 'order_count', 'last_order_amount',
                'last_paid_order_amount', 'last_order_time',
                'last_paid_order_time'
            ));
        }

        // Add last order related conditions
        if (in_array($group_key, array('woocommerce_cart', 'woocommerce_order', 'customer'))) {
            $conditions = array_merge($conditions, array(
                'last_order_product', 'last_order_product_category',
                'last_order_product_attribute', 'last_order_product_tag'
            ));
        }

        // Add products purchased within conditions
        if (in_array($group_key, array('woocommerce_cart', 'woocommerce_order', 'customer'))) {
            $conditions = array_merge($conditions, array(
                'product_within', 'product_category_within',
                'product_attribute_within', 'product_tag_within'
            ));
        }

        return $conditions;
    }

    /**
     * Set up action hooks
     *
     * @access public
     * @return array
     */
    public function set_up_action_hooks()
    {
        // Track which hooks have been added
        $added = array();

        // Iterate over all triggers
        foreach (self::get_trigger_objects() as $trigger) {

            // Get trigger event
            $current_trigger_event = $trigger->get_event();

            // Check if event is configured
            if (!empty($current_trigger_event)) {

                // Iterate over all available trigger events
                foreach (self::get_trigger_event_list() as $trigger_event_group_key => $trigger_event_group) {
                    foreach ($trigger_event_group['children'] as $trigger_event_key => $trigger_event) {

                        // Iterate over hooks
                        foreach ((array) $trigger_event['hooks'] as $hook) {

                            // Make sure we do not add the multiple times
                            if (in_array($hook, $added)) {
                                continue;
                            }

                            // Check if current trigger event matches current trigger
                            if ($current_trigger_event === $trigger_event_key) {

                                // Get hook type
                                $type = isset($trigger_event['type']) ? $trigger_event['type'] : 'action';

                                // Get hook name
                                if ($method = self::get_trigger_event_method_name($type)) {

                                    // Get callback priority and accepted argument number
                                    $priority = isset($trigger_event['priority']) ? $trigger_event['priority'] : 10;
                                    $accepted_args = isset($trigger_event['accepted_args']) ? $trigger_event['accepted_args'] : 1;

                                    // Add hook
                                    $method($hook, $trigger_event['callback'], $priority, $accepted_args);

                                    // Track added hooks
                                    $added[] = $hook;
                                }
                            }
                        }
                    }
                }
            }
        }

        // New Account event handler needs to listen for an additional event
        if (in_array('user_register', $added) && in_array('profile_update', $added)) {
            add_filter('woocommerce_checkout_customer_id', array($this, 'woocommerce_checkout_customer_id'), 99);
            add_action('woocommerce_checkout_order_processed', array($this, 'woocommerce_checkout_order_processed'));
        }
    }

    /**
     * Get event method name
     *
     * @access public
     * @param string $type
     * @return mixed
     */
    public static function get_trigger_event_method_name($type)
    {
        $names = array(
            'action'    => 'add_action',
            'filter'    => 'apply_filters',
        );

        return isset($names[$type]) ? $names[$type] : false;
    }

    /**
     * Add To Cart event callback
     *
     * @access public
     * @param string $cart_item_key
     * @param int $product_id
     * @param int $quantity
     * @param int $variation_id
     * @param array $variation
     * @param array $cart_item_data
     * @return void
     */
    public static function event_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
    {
        self::woocommerce_cart_event_handler('add_to_cart', array(
            'cart_item_key'     => $cart_item_key,
            'product_id'        => $product_id,
            'quantity'          => $quantity,
            'variation_id'      => $variation_id,
            'variation'         => $variation,
            'cart_item_data'    => $cart_item_data
        ));
    }

    /**
     * Handle all WooCommerce Cart events
     *
     * @access public
     * @param string $event
     * @param int $order
     * @return void
     */
    public static function woocommerce_cart_event_handler($event, $args = array())
    {
        // Get customer ID
        $customer_id = is_user_logged_in() ? get_current_user_id() : null;

        // Add customer ID to args
        if (!empty($customer_id) && !isset($args['customer_id'])) {
            $args['customer_id'] = $customer_id;
        }

        // Get user data
        if ($customer_id) {
            $user_data = get_userdata($customer_id);
        }

        // Get customer email address
        $customer_email = (isset($user_data) && is_object($user_data)) ? $user_data->user_email : null;

        // Add customer email to args
        if (!empty($customer_email) && !isset($args['customer_email'])) {
            $args['customer_email'] = $customer_email;
        }

        // Call general event handler
        self::general_event_handler($event, $args, $customer_email, $customer_id);
    }

    /**
     * New Order event callback
     *
     * @access public
     * @param int $order_id
     * @return void
     */
    public static function event_new_order($order_id)
    {
        self::woocommerce_order_event_handler('new_order', $order_id);
    }

    /**
     * Payment Complete event callback
     *
     * @access public
     * @param int $order_id
     * @return void
     */
    public static function event_payment_complete($order_id)
    {
        self::woocommerce_order_event_handler('payment_complete', $order_id);
    }

    /**
     * Status Change event callback
     *
     * @access public
     * @param int $order_id
     * @param string $old_status
     * @param string $new_status
     * @return void
     */
    public static function event_status_change($order_id, $old_status, $new_status)
    {
        self::woocommerce_order_event_handler('status_change', $order_id, array(
            'old_status' => $old_status,
            'new_status' => $new_status,
        ));
    }

    /**
     * Customer Note event callback
     *
     * @access public
     * @param array $params
     * @return void
     */
    public static function event_customer_note($params)
    {
        self::woocommerce_order_event_handler('customer_note', $params['order_id'], array(
            'customer_note' => $params['customer_note'],
        ));
    }

    /**
     * Refund Issued event callback
     *
     * @access public
     * @param int $refund_id
     * @return void
     */
    public static function event_refund_issued($refund_id)
    {
        // Get parent order id
        if (RightPress_Helper::wc_version_gte('3.0')) {
            $refund = wc_get_order($refund_id);
            $order_id = $refund->get_parent_id();
        }
        else {
            $order_id = wp_get_post_parent_id($refund_id);
        }

        // Make sure that order is known
        if (!$order_id) {
            return;
        }

        // Handle event
        self::woocommerce_order_event_handler('refund_issued', $order_id, array(
            'refund_id' => $refund_id,
        ));
    }

    /**
     * Handle all WooCommerce Order events
     *
     * @access public
     * @param string $event
     * @param int $order
     * @param array $args
     * @return void
     */
    public static function woocommerce_order_event_handler($event, $order, $args = array())
    {
        // Load order
        if (is_numeric($order)) {
            $order = wc_get_order($order);
        }

        // No order?
        if (!$order) {
            return;
        }

        // Get customer email address in case we need to send email to customer
        $customer_email = RightPress_WC_Legacy::order_get_billing_email($order);
        $customer_id    = RightPress_WC_Legacy::order_get_customer_id($order);

        // Set args
        $args['order']          = $order;
        $args['customer_id']    = $customer_id;

        // Call general event handler
        self::general_event_handler($event, $args, $customer_email, $customer_id);
    }

    /**
     * New Account event callback
     *
     * This event has special handling - it runs on both user_register and
     * profile_update hooks. This has to do with availability of data for
     * specific user macros (e.g. first_name) and the way
     * WC_Checkout::process_checkout() saves user data for new accounts
     *
     * @access public
     * @param int $user_id
     * @return void
     */
    public static function event_new_account($user_id)
    {
        // Check if this is a user_register request that came via WC_Checkout::process_checkout()
        if (current_filter() === 'user_register' && self::$is_checkout_processing) {

            // Make sure developers are not using the filter hook below since we
            // can't pass in the all the required properties - can't set customer_id
            // for $wc_checkout since it's a private property and developers may
            // attempt to read it OR developers may hook into this filter to do some
            // custom operations and expect this hook to only run once
            if (!has_filter('woocommerce_checkout_update_customer_data')) {

                // Get checkout instance
                $wc_checkout = WC_Checkout::instance();

                // Check if profile_update hook will be called shortly and skip current hook if it will
                if ($_POST['billing_first_name']) {
                    define('RP_WCEC_USER_REGISTER_SUPPRESSED', true);
                    return;
                }
            }
        }

        // Hook is profile_update but this is not actually a new account
        if (current_filter() === 'profile_update' && !defined('RP_WCEC_USER_REGISTER_SUPPRESSED')) {
            return;
        }

        // Add args
        $args = array(
            'user_id' => $user_id,
        );

        // Get user email address
        $user_data = get_userdata($user_id);
        $user_email = $user_data->user_email;

        // Call general event handler
        self::general_event_handler('new_account', $args, $user_email, $user_id);
    }

    /**
     * User Login event callback
     *
     * @access public
     * @param string $user_login
     * @param object $user
     * @return void
     */
    public static function event_user_login($user_login, $user)
    {
        // Add args
        $args = array(
            'user_login'    => $user_login,
            'user'          => $user,
        );

        // Get customer email address and ID
        $customer_email = $user->user_email;
        $customer_id = $user->ID;

        // Call general event handler
        self::general_event_handler('user_login', $args, $customer_email, $customer_id);
    }

    /**
     * Password Reset Request event callback
     *
     * @access public
     * @param object $user_login
     * @param string $key
     * @return void
     */
    public static function event_password_reset_request($user_login, $key)
    {
        // Get user
        $user = get_user_by('login', $user_login);

        // Add args
        $args = array(
            'user_login'            => $user_login,
            'password_reset_key'    => $key,
            'user'                  => $user,
        );

        // Get customer email address and ID
        $customer_email = $user->user_email;
        $customer_id = $user->ID;

        // Call general event handler
        self::general_event_handler('password_reset_request', $args, $customer_email, $customer_id);
    }

    /**
     * Password Reset event callback
     *
     * @access public
     * @param object $user
     * @param string $new_password
     * @return void
     */
    public static function event_password_reset($user, $new_password)
    {
        // Add args
        $args = array(
            'user'          => $user,
            'new_password'  => $new_password,
        );

        // Get customer email address and ID
        $customer_email = $user->user_email;
        $customer_id = $user->ID;

        // Call general event handler
        self::general_event_handler('password_reset', $args, $customer_email, $customer_id);
    }

    /**
     * Role Change event callback
     *
     * @access public
     * @param int $user_id
     * @param string $role
     * @param array $old_roles
     * @return void
     */
    public static function event_role_change($user_id, $role, $old_roles)
    {
        // Get user
        $user = get_userdata($user_id);

        // Check if user object was loaded
        if (!$user) {
            return;
        }

        // Add args
        $args = array(
            'user_id'   => $user_id,
            'role'      => $role,
            'old_roles' => $old_roles,
        );

        // Get customer email address and ID
        $customer_email = $user->user_email;
        $customer_id = $user->ID;

        // Call general event handler
        self::general_event_handler('role_change', $args, $customer_email, $customer_id);
    }

    /**
     * General event handler for all events
     * @param string $event
     * @param array $args
     * @param string $customer_email
     * @param int $customer_id
     * @return void
     */
    public static function general_event_handler($event, $args = array(), $customer_email = null, $customer_id = null)
    {
        // Get triggers that have this event
        $triggers = self::get_trigger_objects($event);

        // Iterate over triggers
        foreach ($triggers as $trigger) {

            // Check if trigger is enabled
            if ($trigger->is_enabled()) {

                // Pull the trigger :)
                $trigger->pull($args, $customer_email, $customer_id);
            }
        }
    }

    /**
     * Trigger activated by event
     *
     * @access public
     * @param array $args
     * @param string $customer_email
     * @param int $customer_id
     * @return void
     */
    public function pull($args = array(), $customer_email = null, $customer_id = null)
    {
        // Check if conditions are matched
        if (!RP_WCEC_Conditions::trigger_matches_conditions($this, $args)) {
            return;
        }

        // Check if trigger has a schedule
        $has_schedule = $this->has_schedule();

        // Start logging
        $log = RP_WCEC_Log_Entry::create(array(
            'log_action'        => ($has_schedule ? 'scheduling' : 'sending'),
            'customer_id'       => $customer_id,
            'customer_email'    => $customer_email,
            'trigger_id'        => $this->id,
            'order_id'          => (isset($args['order']) ? RightPress_WC_Legacy::order_get_id($args['order']) : ''),
        ));

        // Allow developers to skip
        $filter_hook = $has_schedule ? 'rp_wcec_schedule_email' : 'rp_wcec_send_email_immediately';

        if (!apply_filters($filter_hook, true, $this, $args, $customer_email, $customer_id)) {
            $log->change_status('aborted');
            $log->update_note(__('Aborted by developer via filter hook.', 'rp_wcec'));
            return;
        }

        // Check if email needs to be scheduled for later
        if ($has_schedule) {
            RP_WCEC_Scheduler::schedule($this, $args, $customer_email, $customer_id, $log);
        }
        // Send instantly
        else {
            RP_WCEC_Mailer::send_email_by_trigger($this, $args, $customer_email, $log);
        }
    }

    /**
     * Get conditions
     *
     * @access public
     * @return array
     */
    public function get_conditions()
    {
        return $this->conditions;
    }

    /**
     * Get conditions used for scheduled events
     *
     * @access public
     * @return array
     */
    public function get_conditions_scheduled()
    {
        return $this->conditions_scheduled;
    }

    /**
     * Get schedule
     *
     * @access public
     * @return mixed
     */
    public function get_schedule()
    {
        // No schedule?
        if ($this->schedule_method === 'send_immediately') {
            return false;
        }

        // Return schedule
        return array(
            'method'    => $this->schedule_method,
            'value'     => $this->schedule_value,
        );
    }

    /**
     * Check if object has scheduler configuration and it is valid
     *
     * @access public
     * @param object $object
     * @return bool
     */
    public function has_schedule()
    {
        return (bool) $this->get_schedule();
    }

    /**
     * Post deleted
     *
     * @access public
     * @param int $post_id
     * @return void
     */
    public function post_deleted($post_id)
    {
        // Remove all scheduled emails
        if (RightPress_Helper::post_type_is($post_id, self::$post_type)) {
            RP_WCEC_Scheduler::unschedule_by_trigger($post_id);
        }
    }

    /**
     * Get trigger title statically
     *
     * @access public
     * @param int $trigger_id
     * @return string
     */
    public static function get_trigger_title_statically($trigger_id)
    {
        return get_post_meta($trigger_id, 'title', true);
    }

    /**
     * Get array that defines which events support which conditions
     *
     * @access public
     * @return array
     */
    public static function get_event_supported_conditions()
    {
        $support = array();

        // Iterate over all trigger event groups
        foreach (self::get_trigger_event_list() as $trigger_event_group_key => $trigger_event_group) {

            // Iterate over trigger events
            foreach ($trigger_event_group['children'] as $trigger_event_key => $trigger_event) {

                // Iterate over all condition groups
                foreach (RP_WCEC_Conditions::get_condition_list() as $condition_group_key => $condition_group) {

                    // Iterate over all conditions
                    foreach ($condition_group['children'] as $condition_key => $condition) {

                        // Trigger event group not supported
                        if (empty($condition['context']['trigger']) || empty($condition['context']['trigger'][$trigger_event_group_key])) {
                            continue;
                        }

                        // Trigger event not supported
                        if (is_array($condition['context']['trigger'][$trigger_event_group_key]) && empty($condition['context']['trigger'][$trigger_event_group_key][$trigger_event_key])) {
                            continue;
                        }

                        // Get schedule type
                        $schedule_type = $condition['schedule_type'];

                        // Special Case: Customer history conditions on New Account event
                        if (in_array($condition_group_key, array('history', 'products_purchased', 'last_order', 'last_order_products'))) {
                            if ($trigger_event_key === 'new_account') {
                                if (($key = array_search('conditions', $schedule_type)) !== false) {
                                    unset($schedule_type[$key]);
                                }
                            }
                        }

                        // Special Case: Order status on New Order event
                        if ($condition_key === 'order_status') {
                            if ($trigger_event_key === 'new_order') {
                                if (($key = array_search('conditions', $schedule_type)) !== false) {
                                    unset($schedule_type[$key]);
                                }
                            }
                        }

                        // Update condition in the main list
                        if (!empty($schedule_type)) {
                            $support[$trigger_event_key][$condition_key] = array_values($schedule_type);
                        }
                    }
                }
            }
        }

        return $support;
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
        // Schedule value
        if ($property === 'schedule_value' && !empty($this->schedule_method)) {

            // Proceed depending on schedule method
            switch ($this->schedule_method) {

                case 'send_immediately':
                    return array('schedule_value' => null);

                case 'specific_date':
                    return array('schedule_date' => $this->schedule_value);

                case 'next':
                    return array('schedule_weekday' => $this->schedule_value);

                default:
                    return array('schedule_number' => $this->schedule_value);
            }
        }

        return false;
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
            'contexts' => array(
                'email' => 'email_id',
            ),
            'meta_whitelist' => array(
                'title',
            ),
        );
    }

    /**
     * Trigger was published
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function trigger_was_published($post)
    {
        if ($post->post_type === self::$post_type) {
            add_filter('redirect_post_location', array($this, 'add_publish_notice_query_var'), 99);
        }
    }

    /**
     * Add query var to identify new posts
     *
     * @access public
     * @return string
     */
    public function add_publish_notice_query_var($location)
    {
        remove_filter('redirect_post_location', array($this, 'add_publish_notice_query_var'), 99);
        return add_query_arg(array('rp_wcec_post_published' => '1'), $location);
    }

    /**
     * Remove query var to identify new posts
     *
     * @access public
     * @return string
     */
    public function remove_publish_notice_query_var($location)
    {
        remove_filter('redirect_post_location', array($this, 'remove_publish_notice_query_var'), 99);
        return remove_query_arg('rp_wcec_post_published', $location);
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

            // Published
            if (isset($_REQUEST['rp_wcec_post_published'])) {

                // Change message
                $messages['post'][4] = __('Trigger saved and status set to <strong>disabled</strong>. Make sure to manually enable this trigger when ready.', 'rp_wcec');

                // Remove publish notice query var on subsequent updates
                add_filter('redirect_post_location', array($this, 'remove_publish_notice_query_var'), 99);
            }
            // Updated
            else {
                $messages['post'][4] = sprintf(__('%s updated.', 'rp_wcec'), $this->get_object_type_title());
            }
        }

        return $messages;
    }

    /**
     * Disable triggers that were just published
     * This is to prevent incomplete setups being active on live servers
     *
     * @access public
     * @return void
     */
    public function new_post_published()
    {
        $this->disable();
    }

    /**
     * Additional hook to help the New Account handler
     *
     * @access public
     * @param int $customer_id
     * @return int
     */
    public function woocommerce_checkout_customer_id($customer_id)
    {
        // Indicate that checkout processing is taking place
        self::$is_checkout_processing = true;

        // Return value that was passed in
        return $customer_id;
    }

    /**
     * Additional hook to help the New Account handler
     *
     * @access public
     * @return void
     */
    public function woocommerce_checkout_order_processed()
    {
        // Indicate that checkout processing is complete
        self::$is_checkout_processing = false;
    }

    /**
     * Get array of actions available
     *
     * @access public
     * @return array
     */
    public function get_action_list()
    {
        $actions = parent::get_action_list();

        // Clear Scheduled Emails
        $actions['clear_scheduled'] = __('Clear Scheduled Emails', 'rp_wcec');

        return $actions;
    }

    /**
     * Clear scheduled emails
     *
     * @access public
     * @return void
     */
    public function clear_scheduled_emails()
    {
        // Unschedule emails by trigger
        RP_WCEC_Scheduler::unschedule_by_trigger($this->id, true);
    }


}

new RP_WCEC_Trigger();

}
