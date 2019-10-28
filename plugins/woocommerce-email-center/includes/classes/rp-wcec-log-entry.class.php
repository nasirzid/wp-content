<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Log Class
 *
 * @class RP_WCEC_Log_Entry
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_Log_Entry')) {

class RP_WCEC_Log_Entry extends RP_WCEC_Post_Object
{
    // Define post type title
    protected static $post_type = 'wcec_log_entry';
    protected static $post_type_short = 'log_entry';

    // Define meta keys
    protected static $meta_properties = array(
        'thread_key', 'thread_count', 'customer_id', 'customer_email',
        'trigger_id', 'email_id', 'email_subject', 'order_id', 'status',
        'status_title', 'time', 'iso_date', 'note'
    );

    // Define object properties
    public $id;
    public $thread_key;
    public $thread_count;
    public $log_action;
    public $log_action_title;
    public $customer_id;
    public $customer_email;
    public $trigger_id;
    public $email_id;
    public $email_subject;
    public $order_id;
    public $status;
    public $status_title;
    public $time;
    public $iso_date;
    public $note;

    /**
     * Constructor class
     *
     * @access public
     * @param int $id
     * @param array $params
     * @return void
     */
    public function __construct($id = null, $params = array())
    {
        // Construct parent first
        parent::__construct($id);

        // Start logging
        if ($id === null && !empty($params)) {

            // Insert post
            $this->id = wp_insert_post(array(
                'post_title'        => '',
                'post_name'         => '',
                'post_status'       => 'publish',
                'post_type'         => self::$post_type,
                'ping_status'       => 'closed',
                'comment_status'    => 'closed',
            ));

            // Post not inserted?
            if (is_wp_error($this->id) || empty($this->id)) {
                $this->id = 0;
                return;
            }

            // Generate log thread key
            $this->thread_key = md5(time() . rand());
            update_post_meta($this->id, 'thread_key', $this->thread_key);

            // Set status
            $this->status = 'processing';
            $this->status_title = $this->get_term_title_from_slug('status', $this->status);
            wp_set_object_terms($this->id, $this->status, 'wcec_log_entry_status');

            // Set log action
            $this->log_action = isset($params['log_action']) ? $params['log_action'] : 'sending';
            $this->log_action_title = $this->get_term_title_from_slug('log_action', $this->log_action);
            wp_set_object_terms($this->id, $this->log_action, 'wcec_log_entry_log_action');

            // Get user email address if user id is set but email address is not
            if (isset($params['customer_id']) && !isset($params['customer_email'])) {
                $user_data = get_userdata($params['customer_id']);
                $params['customer_email'] = $user_data->user_email;
            }

            // Add other properties
            foreach ($params as $key => $value) {
                if (in_array($key, $this->get_meta_properties())) {
                    $this->$key = $value;
                    add_post_meta($this->id, $key, $value);
                }
            }

            // Add time
            $this->time = time();
            add_post_meta($this->id, 'time', $this->time);

            // Add ISO date for search
            $this->iso_date = RightPress_Helper::get_iso_datetime();
            add_post_meta($this->id, 'iso_date', $this->iso_date);
        }
    }

    /**
     * Get log action
     *
     * @access public
     * @return string
     */
    public function get_log_action()
    {
        return isset($this->log_action) ? $this->log_action : null;
    }

    /**
     * Get log action title
     *
     * @access public
     * @return string
     */
    public function get_log_action_title()
    {
        return isset($this->log_action_title) ? $this->log_action_title : null;
    }

    /**
     * Get thread key
     *
     * @access public
     * @return string
     */
    public function get_thread_key()
    {
        return isset($this->thread_key) ? $this->thread_key : null;
    }

    /**
     * Set email ID
     *
     * @access public
     * @param int $email_id
     * @return void
     */
    public function set_email_id($email_id)
    {
        $this->update_field('email_id', $email_id);
    }

    /**
     * Set email subject
     *
     * @access public
     * @param string $email_subject
     * @return void
     */
    public function set_email_subject($email_subject)
    {
        $this->update_field('email_subject', $email_subject);
    }

    /**
     * Set thread key
     *
     * @access public
     * @param string $thread_key
     * @return void
     */
    public function set_thread_key($thread_key)
    {
        // Set thread key
        $this->update_field('thread_key', $thread_key);

        // Update thread count
        $count = self::update_thread_count($thread_key);

        // Set new thread count
        $this->update_field('thread_count', $count);
    }

    /**
     * Check if post type is configurable
     *
     * @access public
     * @return bool
     */
    public function is_configurable()
    {
        return false;
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
                'name'               => __('Log Entries', 'rp_wcec'),
                'singular_name'      => __('Log Entry', 'rp_wcec'),
                'add_new'            => __('Add Entry', 'rp_wcec'),
                'add_new_item'       => __('Add New Entry', 'rp_wcec'),
                'edit_item'          => __('Edit Entry', 'rp_wcec'),
                'new_item'           => __('New Entry', 'rp_wcec'),
                'all_items'          => __('Log', 'rp_wcec'),
                'view_item'          => __('View Entry', 'rp_wcec'),
                'search_items'       => __('Search Entries', 'rp_wcec'),
                'not_found'          => __('No Log Entries Found', 'rp_wcec'),
                'not_found_in_trash' => __('No Log Entries Found In Trash', 'rp_wcec'),
                'parent_item_colon'  => '',
                'menu_name'          => __('Log', 'rp_wcec'),
            ),
            'description' => __('Email Log', 'rp_wcec'),
        );
    }

    /**
     * Get custom capabilities for custom post type
     *
     * @access public
     * @return array
     */
    public function get_post_type_custom_capabilities()
    {
        return array(
            'create_posts' => false,
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
            'log_action' => array(
                'singular'  => __('Action', 'rp_wcec'),
                'plural'    => __('Action', 'rp_wcec'),
                'all'       => __('All actions', 'rp_wcec'),
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
        return __('Log Entry', 'rp_wcec');
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
        $columns['log_time']        = __('Timestamp', 'rp_wcec');
        $columns['log_action']      = __('Action', 'rp_wcec');
        $columns['log_status']      = __('Status', 'rp_wcec');
        $columns['log_customer']    = __('Customer', 'rp_wcec');
        $columns['log_email']       = __('Email', 'rp_wcec');
        $columns['log_trigger']     = __('Trigger', 'rp_wcec');
        $columns['log_order']       = __('Order', 'rp_wcec');
        $columns['log_note']        = __('Note', 'rp_wcec');
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
        // Proceed depending on column
        switch ($column_key) {

            case 'log_time':

                // Get log date and time string
                $date_time = RightPress_Helper::get_adjusted_datetime($this->time, RP_WCEC::get_date_time_format());

                // Maybe display as a link to related entries
                if (!empty($this->thread_key) && $this->thread_count > 1) {
                    echo '<a href="edit.php?post_type=wcec_log_entry&amp;s=thread_key:' . $this->thread_key . '" title="' . __('Show related entries', 'rp_wcec') . '">' . $date_time . '</a>';
                }
                else {
                    echo $date_time;
                }

                break;

            case 'log_action':
                echo '<a href="edit.php?post_type=wcec_log_entry&amp;wcec_log_entry_log_action=' . $this->log_action . '">' . $this->log_action_title . '</a>';
                break;

            case 'log_status':
                echo '<a class="rp_wcec_status_' . $this->status . '" href="edit.php?post_type=wcec_log_entry&amp;wcec_log_entry_status=' . $this->status . '">' . $this->status_title . '</a>';
                break;

            case 'log_customer':
                if (!empty($this->customer_id)) {
                    echo $user_link = RP_WCEC::get_user_profile_link($this->customer_id, $this->customer_email);
                }
                else if (!empty($this->customer_email)) {
                    echo $this->customer_email;
                }
                else {
                    echo '<span class="rp_wcec_none">' . __('none', 'rp_wcec') . '</span>';
                }
                break;

            case 'log_trigger':
                if ($this->trigger_id) {
                    if (!RightPress_Helper::post_is_trashed($this->trigger_id)) {
                        RightPress_Helper::print_link_to_post($this->trigger_id, RP_WCEC_Trigger::get_trigger_title_statically($this->trigger_id), '', '', 10);
                    }
                    else {
                        echo '<span class="rp_wcec_none">#' . $this->trigger_id . ' (' . __('deleted', 'rp_wcec') . ')</span>';
                    }
                }
                break;

            case 'log_email':
                if ($this->email_id) {
                    if (!RightPress_Helper::post_is_trashed($this->email_id)) {
                        RightPress_Helper::print_link_to_post($this->email_id, $this->email_subject, '', '', 10);
                    }
                    else {
                        echo '<span class="rp_wcec_none">#' . $this->email_id . ' (' . __('deleted', 'rp_wcec') . ')</span>';
                    }
                }
                break;

            case 'log_order':
                if ($this->order_id) {
                    // WC31: Orders will no longer be posts
                    if (!RightPress_Helper::post_is_trashed($this->order_id) && RightPress_Helper::post_type_is($this->order_id, 'shop_order')) {
                        $order = wc_get_order($this->order_id);
                        // WC31: Orders will no longer be posts
                        RightPress_Helper::print_link_to_post($this->order_id, ('#' . $order->get_order_number()));
                    }
                    else {
                        echo '<span class="rp_wcec_none">#' . $this->order_id . ' (' . __('deleted', 'rp_wcec') . ')</span>';
                    }
                }
                else {
                    echo '<span class="rp_wcec_none">' . __('none', 'rp_wcec') . '</span>';
                }
                break;

            case 'log_note':
                if (!empty($this->note)) {
                    echo $this->note;
                }
                else {
                    echo '<span class="rp_wcec_none">' . __('none', 'rp_wcec') . '</span>';
                }
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
        // Populate status and log action
        foreach (array('status', 'log_action') as $taxonomy) {

            // Get post terms
            $post_terms = wp_get_post_terms($this->id, $this->get_post_type() . '_' . $taxonomy);

            // Set property
            $this->$taxonomy = RightPress_Helper::clean_term_slug($post_terms[0]->slug);

            // Set title property
            $title_key = $taxonomy . '_title';
            $this->$title_key = $this->get_term_title_from_slug($taxonomy, $this->$taxonomy);
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
     * Update field
     *
     * @access public
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function update_own_field($field, $value)
    {
        // Save log action
        if ($field === 'log_action') {

            // Get list of known log actions
            $actions = $this->get_log_action_list();

            if (isset($actions[$value])) {
                $this->log_action = $value;
                $this->log_action_title = $this->get_term_title_from_slug('log_action', $this->log_action);
                wp_set_object_terms($this->id, $value, $this->get_post_type() . '_log_action');
            }

            return true;
        }

        return false;
    }

    /**
     * Define and return all statuses
     *
     * @access public
     * @return array
     */
    public function get_status_list()
    {
        return array(
            'processing' => array(
                'title' => __('processing', 'rp_wcec'),
            ),
            'aborted' => array(
                'title' => __('aborted', 'rp_wcec'),
            ),
            'sending' => array(
                'title' => __('sending', 'rp_wcec'),
            ),
            'success' => array(
                'title' => __('success', 'rp_wcec'),
            ),
            'warning' => array(
                'title' => __('warning', 'rp_wcec'),
            ),
            'error' => array(
                'title' => __('error', 'rp_wcec'),
            ),
        );
    }

    /**
     * Define and return log actions
     *
     * @access public
     * @return array
     */
    public function get_log_action_list()
    {
        return array(
            'scheduling' => array(
                'title' => __('Scheduling Email', 'rp_wcec'),
            ),
            'sending' => array(
                'title' => __('Sending Email', 'rp_wcec'),
            ),
            'scheduled' => array(
                'title' => __('Sending Scheduled', 'rp_wcec'),
            ),
            'unscheduling' => array(
                'title' => __('Unscheduling Email', 'rp_wcec'),
            ),
        );
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
        if (is_numeric($id)) {
            $post = get_post($id);

            if ($post && $post->post_type == self::$post_type) {
                return new self($id);
            }
        }

        return false;
    }

    /**
     * Update note
     *
     * @access public
     * @param string $note
     * @param bool $append
     * @return void
     */
    public function update_note($note, $append = true)
    {
        $old = $append ? get_post_meta($this->id, 'note', true) : '';
        $this->note = $old . ($old ? PHP_EOL : '') . (string) $note;
        update_post_meta($this->id, 'note', $this->note);
    }

    /**
     * Create log entry statically
     *
     * @access public
     * @param array $params
     * @return mixed
     */
    public static function create($params = array())
    {
        // No params sent?
        if (empty($params)) {
            return false;
        }

        // Create new object
        $log = new RP_WCEC_Log_Entry(null, $params);

        // Failed creating new log object?
        if (empty($log->id)) {
            return false;
        }

        return $log;
    }

    /**
     * Update thread count
     *
     * @access public
     * @param string $thread_key
     * @return int
     */
    public static function update_thread_count($thread_key)
    {
        // Build meta query
        $meta_query = array(array(
            'key'       => 'thread_key',
            'value'     => $thread_key,
            'compare'   => '=',
        ));

        // Get all log entries with this thread key
        $ids = self::get_list_of_all_ids(self::$post_type, false, $meta_query);

        // Get count of items in thread
        $count = count($ids);

        // Update each log entry with new count
        foreach ($ids as $id) {
            update_post_meta($id, 'thread_count', $count);
        }

        // Return count
        return $count;
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
                'thread_key'        => 'thread_key',
                'customer'          => 'customer_id',
                'trigger'           => 'trigger_id',
                'email'             => 'email_id',
                'subject'           => 'email_subject',
                'order'             => 'order_id',
            ),
            'meta_whitelist' => array(
                'customer_email', 'email_subject', 'iso_date', 'note'
            ),
        );
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
        // Remove regular items
        $actions = parent::manage_list_bulk_actions($actions);

        // Add "Clear Log"
        $actions['rp_wcec_clear_log'] = __('Clear Log', 'rp_wcec');

        return $actions;
    }

    /**
     * Process bulk actions
     *
     * @access public
     * @return void
     */
    public function process_bulk_actions()
    {
        // Not our request
        if (empty($_REQUEST['action']) && empty($_REQUEST['action2'])) {
            return;
        }

        // Get action
        $action = ($_REQUEST['action'] != -1) ? $_REQUEST['action'] : $_REQUEST['action2'];

        // Clear log
        if ($action === 'rp_wcec_clear_log') {

            // Prepare config
            $config = array(
                'post_type'         => $this->get_post_type(),
                'posts_per_page'    => -1,
                'fields'            => 'ids',
            );

            // Get post ids
            foreach (get_posts($config) as $post_id) {

                // Delete post
                wp_delete_post($post_id, true);
            }
        }
    }

}

new RP_WCEC_Log_Entry();

}
