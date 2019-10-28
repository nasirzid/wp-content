<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Mailer class
 *
 * @class RP_WCEC_Mailer
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_Mailer')) {

class RP_WCEC_Mailer
{
    private $email;
    private $trigger;
    private $subject;
    private $headers;
    private $attachments;
    private $args;
    private $to;
    private $content_type;
    private $content;

    /**
     * Constructor class
     *
     * @access public
     * @param object $email
     * @param object $trigger
     * @param array $args
     * @param string $customer_email
     * @param object $log
     * @param bool $send_to_admin
     * @return void
     */
    public function __construct($email = null, $trigger = null, $args = array(), $customer_email = null, $log = null, $send_to_admin = false)
    {
        // Set up mailer class
        if (!empty($email)) {

            $this->email        = $email;
            $this->trigger      = $trigger;
            $this->subject      = $email->get_email_subject();
            $this->headers      = $email->get_email_headers($customer_email, $send_to_admin);
            $this->attachments  = $email->get_email_attachments();
            $this->args         = $args;
            $this->to           = $this->get_email_to_address($customer_email, $send_to_admin);

            // Get content type
            $this->content_type = $email->get_content_type();

            // Get content
            if ($this->content_type === 'text/plain') {
                $this->content = $email->get_email_content_by_type('text/plain', $this->to, $log);
            }
            else {
                $this->content = $email->get_email_content_by_type('text/html', $this->to, $log);
            }
        }
    }

    /**
     * Send email statically
     *
     * @access public
     * @param object $email
     * @param object $trigger
     * @param array $args
     * @param string $customer_email
     * @param object $log
     * @param bool $send_to_admin
     * @return bool
     */
    public static function send($email, $trigger, $args = array(), $customer_email = null, $log = null, $send_to_admin = false)
    {
        try {

            // Get WC emails instance so head, footer and other action hooks are added
            $wc_emails = WC_Emails::instance();

            // Define custom email dispatch
            $GLOBALS['rp_wcec_dispatching_custom_email'] = 1;

            // Initialize mailer object
            $mailer = new self($email, $trigger, $args, $customer_email, $log, $send_to_admin);

            // Check if we have recipient
            if (empty($mailer->to)) {

                // Add log note
                if ($log) {
                    $log->update_note(__('Unable to get recipient email address.', 'rp_wcec'));
                }

                return false;
            }

            // Generate email hash
            $email_hash = md5(json_encode(array(
                $mailer->to,
                $mailer->subject,
                $mailer->content,
                $mailer->headers,
                $mailer->attachments,
                date('YmdH') . floor(date('i')/4),
            )));

            // Check if such email was already sent. This checks if the same email
            // was sent in the past 4 minutes (cron runs every 5 minutes). This is
            // done to avoid sending duplicate emails to customers in case more
            // than one trigger with the same email runs at the same time or if
            // some technical problem results in multiple emails being dispatched.
            if (get_transient('rp_wcec_s_' . $email_hash)) {

                // Add log note
                if ($log) {
                    $log->change_status('aborted');
                    $log->update_note(__('Email suppressed.', 'rp_wcec') . ' ' . __('The same email was sent to the same recipient in the past few minutes.', 'rp_wcec'));
                }

                // Do not retry this email
                return true;
            }

            // Send email
            $result = $mailer->send_email();

            // Set transient to avoid sending duplicate emails
            set_transient('rp_wcec_s_' . $email_hash, 1, 240);

            // Unset custom email dispatch marker
            unset($GLOBALS['rp_wcec_dispatching_custom_email']);

            // Return result
            return $result;

        }
        catch (Exception $e) {

            // Update log
            if ($log) {
                $log->change_status('error');
                $log->update_note(__('Unexpected Error:', 'rp_wcec') . ' ' . $e->getMessage() . '.');
            }

            // Return result
            return false;
        }
    }

    /**
     * Send email
     *
     * @access public
     * @return bool
     */
    public function send_email()
    {
        // Override from email address, from name and email content type
        add_filter('wp_mail_from', array($this, 'get_email_from_address'));
        add_filter('wp_mail_from_name', array($this, 'get_email_from_name'));
        add_filter('wp_mail_content_type', array($this->email, 'get_content_type'));
        add_filter('phpmailer_init', array($this, 'set_up_multipart'));

        // Send email using WP email function
        $result = wp_mail($this->to, $this->subject, $this->content, $this->headers, $this->attachments);

        // Remove filters used to override values
        remove_filter('wp_mail_from', array($this, 'get_email_from_address'));
        remove_filter('wp_mail_from_name', array($this, 'get_email_from_name'));
        remove_filter('wp_mail_content_type', array($this->email, 'get_content_type'));
        remove_filter('phpmailer_init', array($this, 'set_up_multipart'));

        return $result;
    }

    /**
     * Get from email address
     *
     * @access public
     * @return string
     */
    public function get_email_from_address()
    {
        // Get email address set in WooCommerce
        $from = sanitize_email(get_option('woocommerce_email_from_address'));

        // No address? Use admin email address
        $from = !empty($from) ? $from : self::get_admin_email();

        // Allow developers to override
        $from = apply_filters('rp_wcec_from_email', $from, $this->email, $this->trigger);

        // Return email address
        return $from;
    }

    /**
     * Get from name
     *
     * @access public
     * @return string
     */
    public function get_email_from_name()
    {
        // Get name set in WooCommerce
        $name = wp_specialchars_decode(esc_html(get_option('woocommerce_email_from_name')), ENT_QUOTES);

        // Allow developers to override
        $name = apply_filters('rp_wcec_from_name', $name, $this->email, $this->trigger);

        // Return email address
        return $name;
    }

    /**
     * Get "to" address
     *
     * @access public
     * @param string $to
     * @param bool $send_to_admin
     * @return void
     */
    public function get_email_to_address($to, $send_to_admin = false)
    {
        if ($send_to_admin) {
            $to = self::get_admin_email();
        }

        return apply_filters('rp_wcec_to_email', sanitize_email($to), $this->email, $this->trigger);
    }

    /**
     * Set up multipart message - add alternative content
     *
     * @access public
     * @param object $mailer
     * @return object
     */
    public function set_up_multipart($mailer)
    {
        if ($this->email->get_content_type() === 'multipart/alternative') {
            $mailer->AltBody = $this->email->get_email_content_by_type('text/plain', $this->to);
        }

        return $mailer;
    }

    /**
     * Send email by trigger
     * When this method returns true it just means that email dispatch has
     * not failed (but it may have not been attempted at all)
     *
     * @access public
     * @param object $trigger
     * @param array $args
     * @param string $customer_email
     * @param object $log
     * @return bool
     */
    public static function send_email_by_trigger($trigger, $args = array(), $customer_email = null, $log = null)
    {
        // Trigger must be enabled in order to send an email
        if (!$trigger->is_enabled()) {

            // Update log
            if ($log) {
                $log->change_status('aborted');
                $log->update_note(__('Trigger is disabled.', 'rp_wcec'));
            }

            return true;
        }

        // Get email ID
        $email_id = $trigger->get_email_id();

        // Update logger with email ID
        if ($log) {
            $log->set_email_id($email_id);
        }

        // Check if email is set and exists
        if (!is_numeric($email_id) || !RP_WCEC_Email::exists($email_id)) {

            // Update log
            if ($log) {
                $log->change_status('aborted');
                $log->update_note(__('Email no longer exists.', 'rp_wcec'));
            }

            return true;
        }

        // Get email
        $email = RP_WCEC_Email::get_by_id($email_id);

        // Make sure that email object was loaded
        if (!$email) {

            // Update log
            if ($log) {
                $log->change_status('error');
                $log->update_note(__('Unable to load trigger object.', 'rp_wcec'));
            }

            return false;
        }

        // Set email and trigger ids as arguments
        $args['rp_wcec_email_id'] = $email->get_id();
        $args['rp_wcec_trigger_id'] = $trigger->get_id();

        // Set trigger
        $email->set_trigger($trigger);

        // Set args
        $email->set_args($args);

        // Update logger with email subject
        if ($log) {
            $log->set_email_subject($email->get_email_subject());
        }

        // Check if email is enabled
        if (!$email->is_enabled()) {

            // Update log
            if ($log) {
                $log->change_status('aborted');
                $log->update_note(__('Email is disabled.', 'rp_wcec'));
            }

            return true;
        }

        // No one to send to
        if (!$email->get_send_to_shop_manager() && !$email->get_send_to_customer() && !$email->get_send_to_other()) {
            $message = __('No recipients selected for this email.', 'rp_wcec');
            $log->update_note($message);
            $log->change_status('aborted');
            return true;
        }

        // Change status to sending
        if ($log) {
            $log->change_status('sending');
        }

        // Send to shop manager
        if ($email->get_send_to_shop_manager()) {

            // Update args
            $args['sent_to_admin'] = true;
            $email->set_args($args);

            // Send email
            $admin_result = self::send($email, $trigger, $args, $customer_email, $log, true);

            // Add log note
            if ($log) {

                // Check if sending was aborted
                if ($log->get_status() === 'aborted') {
                    if ($email->get_send_to_customer()) {
                        $log->update_note(__('Not sent to shop manager.', 'rp_wcec'));
                    }
                }
                else {
                    $message = $admin_result ? __('Sent to shop manager.', 'rp_wcec') : __('Failed sending to shop manager.', 'rp_wcec');
                    $log->update_note($message);
                }
            }
        }

        // Send to customer
        if ($email->get_send_to_customer()) {

            // Get log current status and reset it
            if ($log) {
                $previous_status = $log->get_status();
                $log->change_status('sending');
            }

            // Update args
            $args['sent_to_admin'] = false;
            $email->set_args($args);

            // Send email
            $customer_result = self::send($email, $trigger, $args, $customer_email, $log);

            // Add log note
            if ($log) {

                // Check if sending was aborted
                if ($log->get_status() === 'aborted') {
                    if ($email->get_send_to_shop_manager()) {

                        // Update log note
                        $log->update_note(__('Not sent to customer.', 'rp_wcec'));

                        // Check if admin email was not aborted
                        if (isset($admin_result) && $previous_status !== 'aborted') {
                            $log->change_status('success');
                        }
                    }
                }
                else {
                    $message = $customer_result ? __('Sent to customer.', 'rp_wcec') : __('Failed sending to customer.', 'rp_wcec');
                    $log->update_note($message);
                }
            }
        }

        // Send to other recipients
        if ($email->get_send_to_other()) {

            // Get other recipient list
            $other_recipients = $email->get_other_recipient_list();

            // Sanitize emails
            foreach ($other_recipients as $other_recipient_key => $other_recipient) {
                if (!filter_var($other_recipient, FILTER_VALIDATE_EMAIL)) {
                    unset($other_recipients[$other_recipient_key]);
                }
            }

            // Proceed if we have at least one valid email
            if (!empty($other_recipients)) {

                // Track which recipients this email was successfully sent to
                $recipients_success = array();
                $recipients_error = array();

                // Iterate over recipients
                foreach ($other_recipients as $other_recipient) {

                    // Get log current status and reset it
                    if ($log) {
                        $previous_status = $log->get_status();
                        $log->change_status('sending');
                    }

                    // Update args
                    $args['sent_to_admin'] = false;
                    $email->set_args($args);

                    // Send email
                    $other_recipient_result = self::send($email, $trigger, $args, $other_recipient, $log);

                    // Check if sending succeeded
                    if ($log->get_status() !== 'aborted' && $other_recipient_result) {
                        $recipients_success[] = $other_recipient;
                    }
                    else {
                        $recipients_error[] = $other_recipient;
                    }
                }

                // Add log note
                if ($log) {

                    // Succeeded
                    if (!empty($recipients_success)) {
                        $message = sprintf(_n('Sent to recipient %s.', 'Sent to recipients %s.', count($recipients_success), 'rp_wcec'), join(', ', $recipients_success));
                        $log->update_note($message);
                        $other_recipient_result = empty($recipients_error) ? 'success' : 'warning';
                    }

                    // Failed
                    if (!empty($recipients_error)) {
                        $message = sprintf(_n('Failed sending to recipient %s.', 'Failed sending to recipients %s.', count($recipients_error), 'rp_wcec'), join(', ', $recipients_error));
                        $log->update_note($message);
                        $other_recipient_result = empty($recipients_success) ? 'error' : 'warning';
                    }
                }
            }
            else if ($log) {
                $message = __('Failed sending to other recipients - no valid email addresses found.', 'rp_wcec');
                $log->update_note($message);
                $other_recipient_result = 'error';
            }
        }

        // Update log status
        if ($log) {

            // Status already set to success
            if ($log->get_status() === 'success') {
                $new_status = 'success';
            }
            // Sent to admin only
            else if (isset($admin_result) && !isset($customer_result)) {
                $new_status = $admin_result ? 'success' : 'error';
            }
            // Sent to customer only
            else if (!isset($admin_result) && isset($customer_result)) {
                $new_status = $customer_result ? 'success' : 'error';
            }
            // Send to both admin and customer
            else if (isset($admin_result) && isset($customer_result)) {

                // Check if status needs to be set to warning
                if ($admin_result !== $customer_result) {
                    $new_status = 'warning';
                }
                else {
                    $new_status = $admin_result ? 'success' : 'error';
                }
            }

            // Sent to other recipients
            if (isset($other_recipient_result)) {
                if (isset($new_status)) {
                    if ($new_status === 'success' && in_array($other_recipient_result, array('warning', 'error'), true)) {
                        $new_status = 'warning';
                    }
                    else if ($new_status === 'error' && in_array($other_recipient_result, array('success', 'warning'), true)) {
                        $new_status = 'warning';
                    }
                }
                else {
                    $new_status = $other_recipient_result;
                }
            }

            // Change status, but do not change to success if it was already changed by some other method
            if (!($new_status === 'success' && $log->get_status() !== 'sending')) {
                $log->change_status($new_status);
            }
        }

        // Please note that we base success/failure on whether an email was
        // sent to customer if sending to both customer and admin in order
        // not to repeat sending the same email to customer
        return isset($customer_result) ? $customer_result : $admin_result;
    }

    /**
     * Get admin email
     *
     * @access public
     * @return string
     */
    public static function get_admin_email()
    {
        return apply_filters('rp_wcec_admin_email', get_option('admin_email'));
    }

}
}
