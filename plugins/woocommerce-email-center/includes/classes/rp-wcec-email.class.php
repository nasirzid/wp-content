<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email object class
 *
 * @class RP_WCEC_Email
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_Email')) {

class RP_WCEC_Email extends RP_WCEC_Post_Object
{
    // Define post type title
    protected static $post_type = 'wcec_email';
    protected static $post_type_short = 'email';

    // Define meta keys
    protected static $meta_properties = array(
        'content_type', 'subject', 'heading', 'note', 'send_to', 'attachments',
        'style', 'send_to_shop_manager', 'send_to_customer', 'send_to_other',
        'other_recipient_list',
    );

    // Define object properties
    protected $id;
    protected $status;
    protected $status_title;
    protected $subject;
    protected $heading;
    protected $note;
    protected $send_to_shop_manager;
    protected $send_to_customer;
    protected $send_to_other;
    protected $other_recipient_list;
    protected $attachments;
    protected $style;
    protected $content;
    protected $content_type;
    protected $trigger;
    protected $args;

    // Deprecated properties
    protected $send_to;

    // Content type mapping
    protected static $content_type_mapping = array(
        'html'      => 'text/html',
        'plain'     => 'text/plain',
        'multipart' => 'multipart/alternative',
    );

    /**
     * Constructor class
     *
     * @access public
     * @param mixed $id
     * @param object $trigger
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
     * On init
     *
     * @access public
     * @return void
     */
    public function on_init_own()
    {
        // Intercept delete file call
        if (!empty($_REQUEST['wcec_file_delete'])) {
            $this->file_delete();
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

        // Add block and macro button to editor
        add_action('admin_head', array($this, 'add_editor_buttons'));
        add_filter('tiny_mce_version', array($this, 'refresh_tinymce'));
        add_filter('mce_external_languages', array($this, 'add_tinymce_locales'), 20);
        add_action('admin_head', array($this, 'print_tinymce_button_config'));

        // Load assets conditionally
        add_action('admin_enqueue_scripts', array($this, 'enqueue_backend_assets'), 11);
    }

    /**
     * Refresh TinyMCE
     *
     * @access public
     * @param int $version
     * @return int
     */
    public function refresh_mce($version)
    {
        $version += 3;
        return $version;
    }

    /**
     * Get subject
     *
     * @access public
     * @return int
     */
    public function get_subject()
    {
        return isset($this->subject) ? $this->subject : null;
    }

    /**
     * Get heading
     *
     * @access public
     * @return int
     */
    public function get_heading()
    {
        return isset($this->heading) ? $this->heading : null;
    }

    /**
     * Get note
     *
     * @access public
     * @return int
     */
    public function get_note()
    {
        return isset($this->note) ? $this->note : null;
    }

    /**
     * Get send to shop manager
     *
     * @access public
     * @return bool
     */
    public function get_send_to_shop_manager()
    {
        return (isset($this->send_to_shop_manager) && $this->send_to_shop_manager);
    }

    /**
     * Get send to customer
     *
     * @access public
     * @return bool
     */
    public function get_send_to_customer()
    {
        return (isset($this->send_to_customer) && $this->send_to_customer);
    }

    /**
     * Get send to other recipient
     *
     * @access public
     * @return bool
     */
    public function get_send_to_other()
    {
        return (isset($this->send_to_other) && $this->send_to_other);
    }

    /**
     * Get send to other recipient list
     *
     * @access public
     * @return array
     */
    public function get_other_recipient_list()
    {
        return (isset($this->other_recipient_list) && !empty($this->other_recipient_list)) ? (array) $this->other_recipient_list : array();
    }

    /**
     * Get style
     *
     * @access public
     * @return string
     */
    public function get_style()
    {
        return isset($this->style) ? $this->style : 'woocommerce';
    }

    /**
     * Set trigger
     *
     * @access public
     * @param object $trigger
     * @return void
     */
    public function set_trigger($trigger)
    {
        $this->trigger = $trigger;
    }

    /**
     * Set args
     *
     * @access public
     * @param object $args
     * @return void
     */
    public function set_args($args)
    {
        $this->args = $args;
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
                'name'               => __('Custom Emails', 'rp_wcec'),
                'singular_name'      => __('Custom Email', 'rp_wcec'),
                'add_new'            => __('Add Email', 'rp_wcec'),
                'add_new_item'       => __('Add New Email', 'rp_wcec'),
                'edit_item'          => __('Edit Email', 'rp_wcec'),
                'new_item'           => __('New Email', 'rp_wcec'),
                'all_items'          => __('Emails', 'rp_wcec'),
                'view_item'          => __('View Email', 'rp_wcec'),
                'search_items'       => __('Search Emails', 'rp_wcec'),
                'not_found'          => __('No Emails Found', 'rp_wcec'),
                'not_found_in_trash' => __('No Emails Found In Trash', 'rp_wcec'),
                'parent_item_colon'  => '',
                'menu_name'          => __('Custom Emails', 'rp_wcec'),
            ),
            'description' => __('Custom Emails', 'rp_wcec'),
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
        return __('Email', 'rp_wcec');
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
                __('Email Content', 'rp_wcec'),
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
        $columns['email_subject']   = __('Subject', 'rp_wcec');
        $columns['email_send_to']   = __('Recipient', 'rp_wcec');
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

            case 'email_subject':
                $note = $this->get_note();
                $note = !empty($note) ? '&nbsp;&nbsp;<span class="rp_wcec_title_note">' . $note . '</span>' : '';
                RightPress_Helper::print_link_to_post($this->get_id(), $this->get_subject(), '<span class="rp_wcec_row_title_cell">', '</span>' . $note);
                $this->print_post_actions();
                break;

            case 'email_send_to':

                $recipients = array();

                if ($this->get_send_to_shop_manager()) {
                    $recipients[] = __('Shop Manager', 'rp_wcec');
                }
                if ($this->get_send_to_customer()) {
                    $recipients[] = __('Customer', 'rp_wcec');
                }
                if ($this->get_send_to_other()) {
                    $recipients[] = __('Email Addresses', 'rp_wcec');
                }

                echo join(', ', $recipients);
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

        // Import recipients from versions earlier than 1.4
        if (!isset($this->send_to_shop_manager) && !isset($this->send_to_customer) && !isset($this->send_to_other)) {
            if (isset($this->send_to) && in_array($this->send_to, array('admin', 'customer', 'both'), true)) {
                if ($this->send_to === 'admin' || $this->send_to === 'both') {
                    $this->send_to_shop_manager = true;
                    update_post_meta($this->id, 'send_to_shop_manager', '1');
                }
                if ($this->send_to === 'customer' || $this->send_to === 'both') {
                    $this->send_to_customer = true;
                    update_post_meta($this->id, 'send_to_customer', '1');
                }
                delete_post_meta($this->id, 'send_to');
            }
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

        // Enqueue other email scripts
        wp_enqueue_script('rp-wcec-backend-email');
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
        array_push($buttons, 'rp_wcec_blocks');
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

            // Add block config
            'blocks' => RP_WCEC_Block::get_list_of_all_items(),
        );

        // Output script element
        echo '<script type="text/javascript">var rp_wcec_custom_button_config = ' . json_encode($items) . ';</script>';
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
        return parent::get_object_list_for_display(self::$post_type, 'subject', 'note', $include_trashed);
    }

    /**
     * Get content type
     *
     * @access public
     * @param bool $return_mime
     * @return string
     */
    public function get_content_type($return_mime = true)
    {
        if (self::plain_text_only()) {
            return $return_mime ? 'text/plain' : 'plain';
        }
        else if (isset($this->content_type) && isset(self::$content_type_mapping[$this->content_type])) {
            return $return_mime ? self::$content_type_mapping[$this->content_type] : $this->content_type;
        }
        else {
            return $return_mime ? 'text/html' : 'html';
        }
    }

    /**
     * Check if email is plain text
     *
     * @access public
     * @return bool
     */
    public function is_plain_text()
    {
        return $this->get_content_type() === 'text/plain';
    }

    /**
     * Check if html and multipart email types are supported
     *
     * @access public
     * @return bool
     */
    public static function plain_text_only()
    {
        return !class_exists('DOMDocument');
    }

    /**
     * Get headers
     *
     * @access public
     * @param string $customer_email
     * @param bool $send_to_admin
     * @return string
     */
    public function get_email_headers($customer_email = null, $send_to_admin = false)
    {
        $headers = '';

        // Content type header
        $headers .= "Content-Type: " . $this->get_content_type() . "\r\n";

        // Reply to header
        if ($send_to_admin && !empty($customer_email)) {
            $headers .= "Reply-To: " . $customer_email . "\r\n";
        }

        // Allow developers to override headers
        return apply_filters('rp_wcec_email_headers', $headers, $this);
    }

    /**
     * Get email attachments
     *
     * @access public
     * @return string
     */
    public function get_email_attachments()
    {
        $attachments = array();

        // Get upload directory
        $upload_directory = self::get_upload_directory();

        // Get list of attachments and prepend full path
        if ($upload_directory) {
            foreach ($this->get_attachments() as $attachment_key => $attachment) {
                $attachments[$attachment_key] = $upload_directory . '/' . $attachment_key . '/' . $attachment;
            }
        }

        // Allow developers to add attachments programmatically
        return apply_filters('rp_wcec_email_attachments', $attachments, $this);
    }

    /**
     * Get email subject
     *
     * @access public
     * @return string
     */
    public function get_email_subject()
    {
        $subject = RP_WCEC_Macros::process($this->subject, $this->args);
        return apply_filters('rp_wcec_email_subject', $subject, $this);
    }

    /**
     * Get email heading
     *
     * @access public
     * @return string
     */
    public function get_email_heading()
    {
        $heading = RP_WCEC_Macros::process($this->heading, $this->args);
        return apply_filters('rp_wcec_email_heading', $heading, $this);
    }

    /**
     * Get content by content type (html or plain text)
     *
     * @access public
     * @param string $content_type
     * @param string $recipient_email
     * @param object $log
     * @return string
     */
    public function get_email_content_by_type($content_type, $recipient_email, $log = null)
    {
        // Process shortcodes
        $email_content = do_shortcode($this->content);

        // Substitute macros with values in content
        $email_content = RP_WCEC_Macros::process($email_content, $this->args, $content_type, $recipient_email);

        // Convert html content to plain text for plain text emails
        if ($content_type === 'text/plain') {
            $email_content = RP_WCEC::html_to_text($email_content);
        }

        // Add content to args
        $this->args['content'] = $email_content;

        // Add email heading to args
        $this->args['email_heading'] = isset($this->args['email_heading']) ? $this->args['email_heading'] : $this->get_email_heading();

        // Add plain text property
        $this->args['plain_text'] = $content_type === 'text/plain';

        // Add style property
        $this->args['style'] = $this->get_style();

        // WooCommerce style template
        if ($this->get_style() === 'woocommerce') {
            $template = $content_type === 'text/html' ? 'emails/rp-wcec-general' : 'emails/plain/rp-wcec-general';
        }
        // Template with no styling
        else {
            $template = $content_type === 'text/html' ? 'emails/rp-wcec-no-styling' : 'emails/plain/rp-wcec-no-styling';
        }

        // Allow developers to use their own templates
        $template = apply_filters('rp_wcec_email_template', $template);

        // Buffer output
        ob_start();

        // Include template
        RightPress_Helper::include_template($template, RP_WCEC_PLUGIN_PATH, 'woocommerce-email-center', $this->args);

        // Get buffer contents and clear buffer
        $content = ob_get_clean();

        // Convert html to plain text if needed
        if ($content_type === 'text/plain') {

            // Replace some special characters
            $content = preg_replace(self::get_characters_to_search(), self::get_characters_to_replace(), $content);
        }

        // Apply inline styles to html if needed
        if ($content_type === 'text/html') {
            $content = $this->apply_inline_styles($content, $log);
        }

        // Apply wordwrap
        $content = wordwrap($content, 70);

        // Allow developers to override content
        $content = apply_filters('rp_wcec_email_content', $content, $content_type, $this);

        return $content;
    }

    /**
     * Apply inline styles to html elements
     *
     * @access public
     * @param string $content
     * @param object $log
     * @return string
     */
    public function apply_inline_styles($content, $log = null)
    {
        // WooCommerce styles
        if ($this->get_style() === 'woocommerce') {

            // Custom styles (backwards compatibility)
            if (RP_WCEC_Styling::are_styling_options_in_use() && RP_WCEC_Styling::is_styler_used()) {
                $styles = RP_WCEC_Styling::get_styles_string();
            }
            // WooCommerce styles
            else {
                ob_start();
                wc_get_template('emails/email-styles.php');
                $styles = apply_filters('woocommerce_email_styles', ob_get_clean());
            }

            // Attempt to apply inline styles
            try {
                $content = RP_WCEC::apply_inline_styles($content, $styles);
            }
            catch (Exception $e) {
                if ($log) {
                    $log->update_note(__('Problems applying inline styles.', 'rp_wcec'));
                    $log->change_status('warning');
                }
            }
        }

        // Return content
        return $content;
    }

    /**
     * Get list of characters to replace
     *
     * @access public
     * @return array
     */
    public static function get_characters_to_search()
    {
        return array(
            "/\r/",
            '/&(nbsp|#160);/i',
            '/&(quot|rdquo|ldquo|#8220|#8221|#147|#148);/i',
            '/&(apos|rsquo|lsquo|#8216|#8217);/i',
            '/&gt;/i',
            '/&lt;/i',
            '/&#38;/i',
            '/&#038;/i',
            '/&amp;/i',
            '/&(copy|#169);/i',
            '/&(trade|#8482|#153);/i',
            '/&(reg|#174);/i',
            '/&(mdash|#151|#8212);/i',
            '/&(ndash|minus|#8211|#8722);/i',
            '/&(bull|#149|#8226);/i',
            '/&(pound|#163);/i',
            '/&(euro|#8364);/i',
            '/&#36;/',
            '/&[^&\s;]+;/i',
            '/[ ]{2,}/'
        );
    }

    /**
     * Get list of characters to replace
     *
     * @access public
     * @return array
     */
    public static function get_characters_to_replace()
    {
        return array(
            '',
            ' ',
            '"',
            "'",
            '>',
            '<',
            '&',
            '&',
            '&',
            '(c)',
            '(tm)',
            '(R)',
            '--',
            '-',
            '*',
            '£',
            'EUR',
            '$',
            '',
            ' ',
        );
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
        // Subject
        if ($property === 'subject') {
            return array('subject' => __('Copy of', 'rp_wcec') . ' ' . (isset($this->subject) ? $this->subject : ''));
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
            'contexts' => array(),
            'meta_whitelist' => array(
                'subject', 'heading', 'note'
            ),
        );
    }

    /**
     * Get attachments
     *
     * @access public
     * @return array
     */
    public function get_attachments()
    {
        return (array) $this->attachments;
    }

    /**
     * Check if email has attachments
     *
     * @access public
     * @return array
     */
    public function has_attachments()
    {
        $attachments = $this->get_attachments();
        return !empty($attachments);
    }

    /**
     * Get file upload directory
     *
     * @access public
     * @return mixed
     */
    public static function get_upload_directory($context = 'attachments')
    {
        // Allow developers to change file storage location
        $wp_upload_dir = wp_upload_dir();
        $file_path = untrailingslashit(apply_filters('rp_wcec_file_path', $wp_upload_dir['basedir'], $context));
        $file_path .= '/rp_wcec_' . $context;

        // Set up upload directory
        if (!self::set_up_upload_directory($file_path)) {
            return false;
        }

        // Return directory path
        return $file_path;
    }

    /**
     * Set up file upload directory
     *
     * @access public
     * @return bool
     */
    public static function set_up_upload_directory($file_path)
    {
        $result = true;

        // Create directory if it does not exist yet
        if (!file_exists($file_path)) {
            $result = mkdir($file_path, 0755, true);
        }

        // Protect files from directory listing
        if (!file_exists($file_path . '/index.php')) {
            touch($file_path . '/index.php');
        }

        return $result;
    }

    /**
     * Save configuration value
     *
     * @access public
     * @param string $property
     * @param array $posted
     * @return mixed
     */
    public function save_configuration_value($property, $posted)
    {
        // Save new attachment
        if ($property === 'attachments') {

            // Get all attachments
            $attachments = $this->get_attachments();

            // New attachment uploaded
            if (!empty($_FILES['rp_wcec']) && !empty($_FILES['rp_wcec']['tmp_name']) && !empty($_FILES['rp_wcec']['tmp_name']['attachment'])) {

                // Get file name
                $file_name = $_FILES['rp_wcec']['name']['attachment'];

                // Generate random file key to avoid collisions
                $file_key = md5(time() . rand());

                // Get upload directory
                $upload_directory = self::get_upload_directory();

                // Get directory path and file path
                $directory_path = $upload_directory . '/' . $file_key;
                $file_path = $directory_path . '/' . $file_name;

                // Set up dedicated directory for this file
                if ($upload_directory && self::set_up_upload_directory($directory_path)) {

                    // Move file from temp files to dedicated directory
                    if (move_uploaded_file($_FILES['rp_wcec']['tmp_name']['attachment'], $file_path)) {

                        // Push to attachments
                        $attachments[$file_key] = $file_name;
                    }
                }
            }

            // Return list of attachments
            return $attachments;
        }
        // Other fields
        else {
            return (isset($posted[$property]) ? $posted[$property] : '');
        }
    }

    /**
     * Delete file handler
     *
     * @access public
     * @return void
     */
    public function file_delete()
    {
        // Check user
        if (!RP_WCEC::is_admin()) {
            exit;
        }

        // No object id or different post type
        if (empty($_REQUEST['object']) && !RightPress_Helper::post_type_is($_REQUEST['object'], $this->get_post_type())) {
            return;
        }

        // Load object
        $object = self::cache($_REQUEST['object']);

        // Unable to load object
        if (!$object) {
            return;
        }

        // Load attachments
        $attachments = $object->get_attachments();

        // Remove attachment by key
        if (isset($attachments[$_REQUEST['wcec_file_delete']])) {
            unset($attachments[$_REQUEST['wcec_file_delete']]);
        }

        // Save new list of attachments
        $object->update_field('attachments', $attachments);

        // Redirect user back to edit page
        $object->redirect_to_edit_page();
    }

    /**
     * Add meta boxes related to this post type
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function add_own_meta_boxes($post)
    {
        // Recipients
        add_meta_box(
            $this->get_post_type() . '_recipients',
            __('Recipients', 'rp_wcec'),
            array($this, 'render_meta_box_recipients'),
            $this->get_post_type(),
            'side',
            'low'
        );

        // Attachments
        add_meta_box(
            $this->get_post_type() . '_attachments',
            __('Attachments', 'rp_wcec'),
            array($this, 'render_meta_box_attachments'),
            $this->get_post_type(),
            'side',
            'low'
        );

        // Formatting
        add_meta_box(
            $this->get_post_type() . '_formatting',
            __('Format & Style', 'rp_wcec'),
            array($this, 'render_meta_box_formatting'),
            $this->get_post_type(),
            'side',
            'low'
        );

        // Formatting
        add_meta_box(
            $this->get_post_type() . '_private_note',
            __('Private Note', 'rp_wcec'),
            array($this, 'render_meta_box_private_note'),
            $this->get_post_type(),
            'side',
            'low'
        );
    }

    /**
     * Render Recipients meta box content
     *
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box_recipients($post)
    {
        // Get object
        $object = self::cache($post->ID);

        if (!$object) {
            return;
        }

        // Get other recipient list
        $other_recipient_list = $object->get_other_recipient_list();

        // Load view
        include RP_WCEC_PLUGIN_PATH . 'includes/views/' . $object->get_post_type_short() . '/recipients.php';
    }

    /**
     * Render Attachments meta box content
     *
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box_attachments($post)
    {
        // Get object
        $object = self::cache($post->ID);

        if (!$object) {
            return;
        }

        // Load view
        include RP_WCEC_PLUGIN_PATH . 'includes/views/' . $object->get_post_type_short() . '/attachments.php';
    }

    /**
     * Render Formatting meta box content
     *
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box_formatting($post)
    {
        // Get object
        $object = self::cache($post->ID);

        if (!$object) {
            return;
        }

        // Load view
        include RP_WCEC_PLUGIN_PATH . 'includes/views/' . $object->get_post_type_short() . '/formatting.php';
    }

    /**
     * Render Private Note meta box content
     *
     * @access public
     * @param mixed $post
     * @return void
     */
    public function render_meta_box_private_note($post)
    {
        // Get object
        $object = self::cache($post->ID);

        if (!$object) {
            return;
        }

        // Load view
        include RP_WCEC_PLUGIN_PATH . 'includes/views/' . $object->get_post_type_short() . '/private-note.php';
    }

    /**
     * Get options for the style field
     *
     * @access public
     * @return array
     */
    public static function get_styles()
    {
        return array(
            'woocommerce'   => __('WooCommerce Style', 'rp_wcec'),
            'no_styling'    => __('No Style / Plain', 'rp_wcec'),
        );
    }

    /**
     * Get options for the content type field
     *
     * @access public
     * @return array
     */
    public static function get_content_types()
    {
        return array(
            'html'      => __('HTML', 'rp_wcec'),
            'plain'     => __('Plain Text', 'rp_wcec'),
            'multipart' => __('Multipart', 'rp_wcec'),
        );
    }





}

new RP_WCEC_Email();

}
