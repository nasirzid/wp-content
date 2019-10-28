<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Scheduler Class
 *
 * @class RP_WCEC_Scheduler
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_Scheduler')) {

class RP_WCEC_Scheduler extends RP_WCEC_Post_Object
{
    // Define post type title
    protected static $post_type = 'wcec_scheduler';
    protected static $post_type_short = 'scheduler';

    // Define meta keys
    protected static $meta_properties = array(
        'trigger_id', 'args', 'customer_email', 'customer_id', 'timestamp',
        'log_thread_key', 'attempts', 'last_attempt'
    );

    // Define object properties
    protected $id;
    protected $trigger_id;
    protected $args;
    protected $customer_email;
    protected $customer_id;
    protected $timestamp;
    protected $log_thread_key;
    protected $attempts;
    protected $last_attempt;

    /**
     * Constructor class
     *
     * @access public
     * @param int $id
     * @return void
     */
    public function __construct($id = null)
    {
        // Construct parent first
        parent::__construct($id);

        // Construct as post type controller
        if ($id === null) {

            // Set up post type controller
            add_action('plugins_loaded', array($this, 'set_up_post_type_controller'), 1);

            // Add custom WordPress cron schedule
            add_filter('cron_schedules', array($this, 'add_custom_schedule'), 99);
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

        // Add event handler
        add_action('rp_wcec_scheduled_event', array($this, 'scheduled_event'));
    }

    /**
     * Own methods run on WP init
     *
     * @access public
     * @return void
     */
    public function on_init_own()
    {
        // Check cron
        $this->check_cron();
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
                'name'               => __('Scheduler Entries', 'rp_wcec'),
                'singular_name'      => __('Scheduler Entry', 'rp_wcec'),
                'add_new'            => __('Add Scheduler Entry', 'rp_wcec'),
                'add_new_item'       => __('Add New Scheduler Entry', 'rp_wcec'),
                'edit_item'          => __('Edit Scheduler Entry', 'rp_wcec'),
                'new_item'           => __('New Scheduler Entry', 'rp_wcec'),
                'all_items'          => __('Scheduler Entries', 'rp_wcec'),
                'view_item'          => __('View Scheduler Entry', 'rp_wcec'),
                'search_items'       => __('Search Scheduler Entries', 'rp_wcec'),
                'not_found'          => __('No Scheduler Entries Found', 'rp_wcec'),
                'not_found_in_trash' => __('No Scheduler Entries Found In Trash', 'rp_wcec'),
                'parent_item_colon'  => '',
                'menu_name'          => __('Scheduler Entries', 'rp_wcec'),
            ),
            'description' => __('Scheduler Entries', 'rp_wcec'),
        );
    }

    /**
     * Check if post type UI needs to be displayed
     *
     * @access public
     * @return bool
     */
    public function show_post_type_ui()
    {
        return false;
    }

    /**
     * Specify menu to add this post type to
     *
     * @access public
     * @return string
     */
    public function show_post_type_in_menu()
    {
        return false;
    }

    /**
     * Get object type title
     *
     * @access public
     * @return string
     */
    public function get_object_type_title()
    {
        return __('Scheduler Entry', 'rp_wcec');
    }

    /**
     * Populate existing object with properties unique to this object type
     *
     * @access public
     * @return void
     */
    public function populate_own_properties()
    {
        // Maybe update user email
        if (!empty($this->customer_id) && ($user_data = get_userdata($this->customer_id))) {
            $this->customer_email = $user_data->user_email;
        }

        // Make sure args property is always array
        if (!is_array($this->args)) {
            $this->args = array();
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
     * Add custom WordPress cron schedule
     *
     * @access public
     * @return void
     */
    public function add_custom_schedule($schedules)
    {
        $schedules['rp_wcec_five_minutes'] = array(
            'interval'  => 300,
            'display'   => __('Once every five minutes', 'rp_wcec'),
        );

        return $schedules;
    }

    /**
     * Check cron entry and set up one if it does not exist or is invalid
     *
     * @access public
     * @return void
     */
    public function check_cron()
    {
        // Get next scheduled event timestamp
        $scheduled = wp_next_scheduled('rp_wcec_scheduled_event');

        // Get current timestamp
        $timestamp = time();

        // Cron is set and is valid
        if ($scheduled && $scheduled <= ($timestamp + 600)) {
            return;
        }

        // Remove all cron entries by key
        wp_clear_scheduled_hook('rp_wcec_scheduled_event');

        // Add new cron entry
        wp_schedule_event(time(), 'rp_wcec_five_minutes', 'rp_wcec_scheduled_event');
    }

    /**
     * Get scheduler objects to process
     * We limit scheduler entries processed per one request so we don't exceed PHP execution time limits
     *
     * @access public
     * @param int $limit
     * @return array
     */
    public static function get_scheduler_objects($limit = 10)
    {
        $meta_query = array();

        // Set up meta query to retrieve entries by timestamp
        $meta_query[] = array(
            'key'       => 'timestamp',
            'value'     => time(),
            'compare'   => '<=',
        );

        // Get objects and return
        return self::get_all_objects(self::$post_type, $meta_query);
    }

    /**
     * Scheduled event handler
     *
     * @access public
     * @return void
     */
    public function scheduled_event()
    {
        // Attempt to get cron lock
        if (!self::lock()) {
            return;
        }

        // Schedule next event
        $this->check_cron();

        // Get objects to process
        $objects = self::get_scheduler_objects();

        // Iterate over objects
        foreach ($objects as $object) {

            // Process object
            $object->process();
        }

        // Fix log entries from previous run
        $this->fix_log_entries();

        // Unlock cron lock
        self::unlock();
    }

    /**
     * Process single scheduler item
     *
     * @access public
     * @return void
     */
    public function process()
    {
        // Check transient to make sure we don't accidentally run multiple copies
        if (get_transient('rp_wcec_processing_' . $this->get_id())) {
            return;
        }

        // Set transient for 4 minutes (cron runs every 5 minutes)
        set_transient('rp_wcec_processing_' . $this->get_id(), 1, 240);

        // Start logging
        $log = RP_WCEC_Log_Entry::create(array(
            'log_action'        => 'scheduled',
            'customer_id'       => $this->customer_id,
            'customer_email'    => $this->customer_email,
            'trigger_id'        => $this->trigger_id,
            'order_id'          => (isset($this->args['order']) ? RightPress_WC_Legacy::order_get_id($this->args['order']) : ''),
        ));

        // Set logging thread key
        if ($log && !empty($this->log_thread_key)) {
            $log->set_thread_key($this->log_thread_key);
        }

        // Make sure trigger exists
        if (!RP_WCEC_Trigger::exists($this->trigger_id)) {

            // Update log
            if ($log) {
                $log->change_status('aborted');
                $log->update_note(__('Trigger no longer exists.', 'rp_wcec'));
            }

            // Delete scheduler object
            $this->delete();

            // Stop execution
            return;
        }

        // Get trigger object
        $trigger = self::cache($this->trigger_id);

        // Make sure that trigger object was loaded
        if (!$trigger) {

            // Update log
            if ($log) {
                $log->change_status('error');
                $log->update_note(__('Unable to load trigger object.', 'rp_wcec'));
            }

            // Delete scheduler object
            $this->delete();

            // Stop execution
            return;
        }

        // Check if conditions are matched
        if (!RP_WCEC_Conditions::trigger_matches_conditions($trigger, $this->args, 'conditions_scheduled')) {

            // Update log
            if ($log) {
                $log->change_status('aborted');
                $log->update_note(__('Trigger does not match conditions.', 'rp_wcec'));
            }

            // Delete scheduler object
            $this->delete();

            // Stop execution
            return;
        }

        // Send emails by trigger
        if (apply_filters('rp_wcec_send_scheduled_email', true, $trigger, $this->args, $this->customer_email, $this->customer_id)) {
            $result = RP_WCEC_Mailer::send_email_by_trigger($trigger, $this->args, $this->customer_email, $log);
        }
        else {

            // Update log
            if ($log) {
                $log->change_status('aborted');
                $log->update_note(__('Aborted by developer via filter hook.', 'rp_wcec'));
            }

            $result = true;
        }

        // Sending failed
        if (!$result) {

            // Increase attempts count
            $attempts = !empty($this->attempts) ? ($this->attempts + 1) : 1;
            $this->update_field('attempts', $attempts);

            // Set last attempt timestamp
            $this->update_field('last_attempt', time());

            // Attempt limit reached?
            if ($attempts > 5) {

                // Update log note
                if ($log) {
                    $log->update_note(__('Attempt limit reached.', 'rp_wcec') . ' ' . __('Email will not be sent.', 'rp_wcec'));
                }

                // Add admin notice
                $this->add_admin_notice();

                // Delete scheduler object
                $this->delete();
            }

            // Attempt limit not yet reached
            else {

                // Update log note
                if ($log) {
                    $log->update_note(__('Sending will be reattempted.', 'rp_wcec'));
                }
            }
        }

        // Sending was either aborted (e.g. disabled trigger, deleted email) or succeeded
        if ($result) {

            // Delete scheduler object
            $this->delete();
        }

        // Finished processing, delete transient
        delete_transient('rp_wcec_processing_' . $this->get_id());
    }

    /**
     * Delete scheduled object
     *
     * @access public
     * @return void
     */
    public function delete()
    {
        wp_delete_post($this->id, true);
    }

    /**
     * Fix log entries from previous scheduled event
     * If PHP execution stopped because of some fatal error, we need to
     * add a note about that and change status to error
     *
     * @access public
     * @return void
     */
    public function fix_log_entries()
    {
        // Get log entries older than 4 minutes that do not have finite status
        $meta_query = array(array(
            'key'       => 'time',
            'value'     => (time() - 240),
            'compare'   => '<',
        ));
        $tax_query = array(array(
            'taxonomy'  => 'wcec_log_entry_status',
            'field'     => 'slug',
            'terms'     => array('processing', 'sending'),
        ));

        // Process query
        $objects = RP_WCEC_Log_Entry::get_all_objects('wcec_log_entry', $meta_query, $tax_query);

        // Iterate over objects
        foreach ($objects as $log) {

            // Update note
            $log->update_note(__('Execution stopped prematurely (e.g. PHP fatal error).', 'rp_wcec'));

            // Change status
            $log->change_status('error');
        }
    }

    /**
     * Add admin notice regarding failing emails
     *
     * @access public
     * @return void
     */
    public function add_admin_notice()
    {
        // Show admin notice here in future versions
    }

    /**
     * Main scheduling function
     *
     * @access public
     * @param object $trigger
     * @param array $args
     * @param string $customer_email
     * @param int $customer_id
     * @param object $log
     * @return bool
     */
    public static function schedule($trigger, $args = array(), $customer_email = null, $customer_id = null, $log = null)
    {
        // First check if we can get valid timestamp to schedule
        $schedule = $trigger->get_schedule();
        $timestamp = self::get_timestamp_from_schedule($schedule, $log);

        // No valid timestamp?
        if (!$timestamp) {

            // Update log
            if ($log) {
                $log->change_status('error');
                $log->update_note(__('Unable to get timestamp from schedule.', 'rp_wcec'));
            }

            return false;
        }

        // Add new post
        $id = wp_insert_post(array(
            'post_title'        => '',
            'post_name'         => '',
            'post_status'       => 'publish',
            'post_type'         => self::$post_type,
            'ping_status'       => 'closed',
            'comment_status'    => 'closed',
        ));

        // Check if post was created
        if (is_wp_error($id) || empty($id)) {

            // Update log
            if ($log) {
                $log->change_status('error');
                $log->update_note(__('Unable to create scheduled email object.', 'rp_wcec'));
            }

            return false;
        }

        // Trigger ID
        update_post_meta($id, 'trigger_id', $trigger->get_id());

        // Args
        update_post_meta($id, 'args', $args);

        // Timestamp
        update_post_meta($id, 'timestamp', $timestamp);

        // Customer email
        if (!empty($customer_email)) {
            update_post_meta($id, 'customer_email', $customer_email);
        }

        // Customer id
        if (!empty($customer_id)) {
            update_post_meta($id, 'customer_id', $customer_id);
        }

        // Check if logging is active
        if ($log) {

            // Add log thread key to scheduler
            update_post_meta($id, 'log_thread_key', $log->get_thread_key());

            // Update log
            $log->change_status('success');
            $log->update_note(sprintf(__('Scheduled for %s.', 'rp_wcec'), RightPress_Helper::get_adjusted_datetime($timestamp, RP_WCEC::get_date_time_format())));
        }

        return true;
    }

    /**
     * Get timestamp from schedule
     *
     * @access public
     * @param array $schedule
     * @param object $log
     * @return mixed
     */
    public static function get_timestamp_from_schedule($schedule, $log = null)
    {
        // Send immediately? We shouldn't be here...
        if ($schedule['method'] === 'send_immediately') {

            // Update log
            if ($log) {
                $log->update_note(__('Email is supposed to be sent immediately, something went wrong.', 'rp_wcec'));
            }

            return false;
        }

        // Specific date
        if ($schedule['method'] === 'specific_date') {

            // Make sure schedule value is set
            if (!empty($schedule['value'])) {

                // Get timestamp from date
                $timestamp = RightPress_Helper::get_timestamp_from_date_string($schedule['value']);
                $time = time();

                // Make sure that timestamp is not in the past and not more
                // than 10 years in the future (to detect if something went wrong)
                if ($timestamp > $time && $timestamp < ($time + 315360000)) {

                    // Return timestamp
                    return $timestamp;
                }
            }
        }

        // Next weekday
        if ($schedule['method'] === 'next') {

            // Get list of weekdays
            $weekdays = self::get_weekdays();

            // Make sure that value is set and is valid
            if (!empty($schedule['value']) && isset($weekdays[$schedule['value']])) {

                // Get timestamp from string
                return RightPress_Helper::get_timestamp_from_date_string('next ' . $schedule['value'], true);
            }
        }

        // Get time methods
        $methods = self::get_methods();

        // Check if such method exists and operation is defined
        if (isset($methods[$schedule['method']]) && !empty($methods[$schedule['method']]['operation'])) {

            // Make sure that value is set and is valid
            if (!empty($schedule['value']) && is_numeric($schedule['value']) && $schedule['value'] > 0) {

                // Format operation string
                $operation = sprintf($methods[$schedule['method']]['operation'], $schedule['value']);

                // Get timestamp from string
                return RightPress_Helper::get_timestamp_from_date_string($operation);
            }
        }

        return false;
    }

    /**
     * Unschedule single scheduled event
     *
     * @access public
     * @param int $scheduler_id
     * @return void
     */
    public static function unschedule($scheduler_id)
    {
        wp_delete_post($scheduler_id, true);
    }

    /**
     * Unschedule all scheduled events by trigger id
     *
     * @access public
     * @param int $trigger_id
     * @param bool $cleared_manually
     * @return void
     */
    public static function unschedule_by_trigger($trigger_id, $cleared_manually = false)
    {
        // Set up meta query to retrieve entries by trigger_id
        $meta_query = array(array(
            'key'       => 'trigger_id',
            'value'     => $trigger_id,
            'compare'   => '=',
        ));

        // Get scheduler objects
        $object_ids = self::get_list_of_all_ids(self::$post_type, true, $meta_query);

        // Trach which log threads have been updated
        $updated_log_threads = array();

        // Iterate over all object ids
        foreach ($object_ids as $object_id) {

            // Get log thread key
            $log_thread_key = get_post_meta($object_id, 'log_thread_key', true);

            // Add log entry
            if ($log_thread_key && !in_array($log_thread_key, $updated_log_threads, true)) {

                // Get earlier log entry from the thread to copy details from
                $log_entries = RP_WCEC_Log_Entry::get_all_objects('wcec_log_entry', array(array(
                    'key'       => 'thread_key',
                    'value'     => $log_thread_key,
                    'compare'   => '=',
                )), array(), 1);

                // Get log entry object or mockup object if it does not exist
                $log_entry = !empty($log_entries) ? array_pop($log_entries) : new stdClass();

                // Log note
                if ($cleared_manually) {
                    $log_note = __('Email unscheduled manually by admin.', 'rp_wcec');
                }
                else {
                    $log_note = __('Corresponding trigger deleted.', 'rp_wcec') . ' ' . __('Email unscheduled.', 'rp_wcec');
                }

                // Add log entry
                $log = RP_WCEC_Log_Entry::create(array(
                    'log_action'        => 'unscheduling',
                    'trigger_id'        => $trigger_id,
                    'customer_id'       => (isset($log_entry->customer_id) ? $log_entry->customer_id : null),
                    'customer_email'    => (isset($log_entry->customer_email) ? $log_entry->customer_email : null),
                    'email_id'          => (isset($log_entry->email_id) ? $log_entry->email_id : null),
                    'email_subject'     => (isset($log_entry->email_subject) ? $log_entry->email_subject : null),
                    'order_id'          => (isset($log_entry->order_id) ? $log_entry->order_id : null),
                    'note'              => $log_note,
                ));

                // Check if log object was successfully created
                if ($log) {

                    // Set thread key
                    $log->set_thread_key($log_thread_key);

                    // Update status
                    $log->change_status('success');
                }

                // Track which log threads have been updated
                $updated_log_threads[] = $log_thread_key;
            }

            // Delete post
            wp_delete_post($object_id, true);
        }
    }

    /**
     * Get methods
     *
     * @access public
     * @return array
     */
    public static function get_methods()
    {
        // Define and return methods
        return array(

            // Send Immediately
            'send_immediately' => array(
                'label'         => __('Send immediately', 'rp_wcec'),
            ),

            // Specific date
            'specific_date'   => array(
                'label'         => __('Specific date', 'rp_wcec'),
            ),

            // Next
            'next'  => array(
                'label'         => __('Next', 'rp_wcec'),
                'operation'     => 'next %s',
            ),

            // Minutes
            'minutes'   => array(
                'label'         => __('Minutes', 'rp_wcec'),
                'operation'     => '+ %d minutes',
            ),

            // Hours
            'hours'   => array(
                'label'         => __('Hours', 'rp_wcec'),
                'operation'     => '+ %d hours',
            ),

            // Days
            'days'   => array(
                'label'         => __('Days', 'rp_wcec'),
                'operation'     => '+ %d days',
            ),

            // Weeks
            'weeks'   => array(
                'label'         => __('Weeks', 'rp_wcec'),
                'operation'     => '+ %d weeks',
            ),

            // Months
            'months'   => array(
                'label'         => __('Months', 'rp_wcec'),
                'operation'     => '+ %d months',
            ),

            // Years
            'years'   => array(
                'label'         => __('Years', 'rp_wcec'),
                'operation'     => '+ %d years',
            ),
        );
    }

    /**
     * Get scheduler methods
     *
     * @access public
     * @return array
     */
    public static function get_method_list_for_display()
    {
        $result = array();

        // Iterate over all methods
        foreach (self::get_methods() as $method_key => $method) {
            $result[$method_key] = $method['label'];
        }

        return $result;
    }

    /**
     * Get weekdays
     *
     * @access public
     * @return array
     */
    public static function get_weekdays()
    {
        $weekdays = array();

        // List of weekdays
        $weekdays_unsorted = array(
            'sunday'    => __('Sunday', 'rp_wcec'),
            'monday'    => __('Monday', 'rp_wcec'),
            'tuesday'   => __('Tuesday', 'rp_wcec'),
            'wednesday' => __('Wednesday', 'rp_wcec'),
            'thursday'  => __('Thursday', 'rp_wcec'),
            'friday'    => __('Friday', 'rp_wcec'),
            'saturday'  => __('Saturday', 'rp_wcec'),
        );

        // Sort weekdays by start of week day
        $sort_list = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
        $first_day = intval(get_option('start_of_week', 0));
        $sort_list = $first_day == 0 ? $sort_list : array_merge(array_slice($sort_list, $first_day), array_slice($sort_list, 0, $first_day));

        foreach ($sort_list as $weekday) {
            $weekdays[$weekday] = $weekdays_unsorted[$weekday];
        }

        return $weekdays;
    }

    /**
     * Cron lock
     *
     * @access public
     * @return bool
     */
    public static function lock()
    {
        global $wpdb;

        // Attempt to acquire lock
        $locked = $wpdb->query("
            UPDATE $wpdb->options
            SET option_name = 'rp_wcec_cron_locked'
            WHERE option_name = 'rp_wcec_cron_unlocked'
        ");

        // Failed acquiring lock
        if (!$locked && !self::release_lock()) {
            return false;
        }

        // Set last lock time
        update_option('rp_wcec_cron_lock_time', time(), false);

        // Lock was acquired successfully
        return true;
    }

    /**
     * Cron unlock
     *
     * @access public
     * @return bool
     */
    public static function unlock()
    {
        global $wpdb;

        // Attempt to release lock
        $unlocked = $wpdb->query("
            UPDATE $wpdb->options
            SET option_name = 'rp_wcec_cron_unlocked'
            WHERE option_name = 'rp_wcec_cron_locked'
        ");

        // Failed releasing lock
        if (!$unlocked) {
            return false;
        }

        // Lock was released successfully
        return true;
    }

    /**
     * Checks if lock is stuck and releases it if needed
     * Also checks if lock option exists and creates it if not
     *
     * @access public
     * @return bool
     */
    public static function release_lock()
    {
        global $wpdb;

        // Get lock option entry
        $result = $wpdb->query("
            SELECT option_id
            FROM $wpdb->options
            WHERE option_name = 'rp_wcec_cron_locked'
            OR option_name = 'rp_wcec_cron_unlocked'
        ");

        // No lock entry - add it and skip this scheduler run
        if (!$result) {
            update_option('rp_wcec_cron_unlocked', 1, false);
            return false;
        }

        // Attempt to reset lock time if four minutes passed
        $reset = $wpdb->query($wpdb->prepare("
            UPDATE $wpdb->options
            SET option_value = %d
            WHERE option_name = 'rp_wcec_cron_lock_time'
            AND option_value <= %d
        ", time(), (time() - 240)));

        // Return reset result
        return (bool) $reset;
    }


}

new RP_WCEC_Scheduler();

}
