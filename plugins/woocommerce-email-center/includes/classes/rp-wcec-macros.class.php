<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Macros replacement class
 *
 * @class RP_WCEC_Macros
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_Macros')) {

class RP_WCEC_Macros
{

    /**
     * Process string - substitute macros with values
     * Non-existing macros will be replace with empty string
     * Curly brackets can be escaped by a backward slash to display them and content
     *
     * @access public
     * @param string $string
     * @param array $args
     * @param string $content_type
     * @param string $recipient_email
     * @return string
     */
    public static function process($string = '', $args = array(), $content_type = 'text/plain', $recipient_email = null)
    {
        // Get all types of macros
        $macros = self::get_all_macros($args);

        // Extract all macro keys used in string
        $macro_keys = self::extract_macro_keys($string);

        // Iterate over macro types
        foreach ($macros as $macro_list) {

            // Filter list by key
            $filtered = self::filter_list_by_keys($macro_list, $macro_keys);

            // Replace macros if list is not empty
            if (!empty($filtered)) {
                $object = (isset($args['order']) && is_object($args['order'])) ? $args['order'] : null;
                $string = self::replace($string, $filtered, $object, $args, $content_type, $recipient_email);
            }
        }

        // Replace remaining non-matched macros so they are not displayed to customer
        $string = preg_replace('/(?<!\\\){.*(?!\\\)}/U', '', $string);

        return $string;
    }

    /**
     * Extract macro keys
     *
     * @access public
     * @param string $string
     * @return array
     */
    public static function extract_macro_keys($string)
    {
        // Perform regex match
        preg_match_all('/(?<!\\\){([^{}]+)(?!\\\)}/U', $string, $keys, PREG_PATTERN_ORDER);

        // Check if any macros were found
        if (is_array($keys) && isset($keys[1]) && !empty($keys[1])) {
            return $keys[1];
        }

        return array();
    }

    /**
     * Filter list by keys
     *
     * @access public
     * @param array $macros
     * @param array $keys
     * @return array
     */
    public static function filter_list_by_keys($macros, $keys)
    {
        // Get regular macros
        $regular_macros = array_diff_key($macros, array_flip(array_diff(array_keys($macros), (array) $keys)));

        // Prepare custom meta macros
        $custom_macros = array();

        // Iterate over keys
        foreach ($keys as $key) {

            // Order meta
            if (RightPress_Helper::string_contains_phrase($key, 'order_meta_')) {
                $custom_macros[$key] = array(
                    'order_meta' => substr($key, 11),
                );
            }
            // User meta
            else if (RightPress_Helper::string_contains_phrase($key, 'user_meta_')) {
                $custom_macros[$key] = array(
                    'userdata' => substr($key, 10),
                );
            }
        }

        // Merge arrays and return
        return array_merge($custom_macros, $regular_macros);
    }

    /**
     * Replace macros
     *
     * @access public
     * @param string $string
     * @param array $macros
     * @param object $object
     * @param array $args
     * @param string $content_type
     * @param string $recipient_email
     * @return string
     */
    public static function replace($string = '', $macros = array(), $object = null, $args = array(), $content_type = 'text/plain', $recipient_email = null)
    {
        // Iterate over macros
        foreach ($macros as $macro_key => $macro) {

            $value = '';

            // Value already in array
            if (!empty($macro['value'])) {
                $value = $macro['value'];
            }
            // Custom method used to retrieve value
            else if (!empty($macro['custom'])) {
                $method = $macro['custom'];
                $value = self::$method($object, $args, $content_type, $recipient_email);
            }
            // Object method used to retrieve value
            else if (!empty($macro['method']) && method_exists($object, $macro['method'])) {
                $method = $macro['method'];
                $value = $object->$method();
            }
            // Object property used to retrieve value
            else if (!empty($macro['property']) && isset($object->{$macro['property']})) {
                $property = $macro['property'];
                $value = $object->$property;
            }
            // Order property used to retrieve value
            else if (!empty($macro['order_property']) && is_a($object, 'WC_Order')) {
                $value = self::get_order_property_value($object, $macro['order_property']);
            }
            // Order meta used to retrieve value
            else if (!empty($macro['order_meta']) && is_a($object, 'WC_Order')) {
                $order_reference = RightPress_Helper::wc_version_gte('3.0') ? $object : RightPress_WC_Legacy::order_get_id($object);
                $value = RightPress_WC_Meta::order_get_meta($order_reference, $macro['order_meta'], true);
            }
            // WordPress user data used to retrieve value
            else if (!empty($macro['userdata'])) {

                // Determine user id
                $user_id = RP_WCEC_Macros::get_user_id_from_args($object, $args);

                // Get property from user data
                $user_data = get_userdata($user_id);
                $property = $macro['userdata'];
                $value = $user_data->$property;

                // In case first/last name is empty, attempt to use WooCommerce customer first/last name
                if (RightPress_Helper::is_empty($value) && in_array($property, array('first_name', 'last_name'), true)) {

                    $billing_property = 'billing_' . $property;

                    if (is_object($object) && !empty($object->$billing_property)) {
                        $value = $object->$billing_property;
                    }
                    else if (!empty($user_data->$billing_property)) {
                        $value = $user_data->$billing_property;
                    }
                }
            }

            // Allow developers to override macro value
            $value = apply_filters('rp_wcec_macro_value', $value, $macro_key, $macro, $string);

            // Perform replacement
            $string = str_replace('{' . $macro_key . '}', $value, $string);
        }

        return $string;
    }

    /**
     * Get user id
     *
     * @access public
     * @param object $object
     * @param array $args
     * @return int|null
     */
    public static function get_user_id_from_args($object, $args)
    {
        if (!empty($args['user_id'])) {
            return $args['user_id'];
        }
        else if (!empty($args['customer_id'])) {
            return $args['customer_id'];
        }
        else if (isset($args['user']) && is_object($args['user']) && !empty($args['user']->ID)) {
            return $args['user']->ID;
        }
        else if (is_a($object, 'WC_Order') && RightPress_WC_Legacy::order_get_customer_id($object)) {
            return RightPress_WC_Legacy::order_get_customer_id($object);
        }
        else if (isset($args['order']) && is_a($args['order'], 'WC_Order') && RightPress_WC_Legacy::order_get_customer_id($args['order'])) {
            return RightPress_WC_Legacy::order_get_customer_id($args['order']);
        }

        return null;
    }

    /**
     * Get all macros
     *
     * @access public
     * @param array $args
     * @return array
     */
    public static function get_all_macros($args = array())
    {
        return array(

            // Global macros
            'global'    => self::get_global_macro_list(),

            // WooCommerce Order macros
            'order'     => self::get_order_macro_list(),

            // WordPress User macros
            'user'      => self::get_user_macro_list(),

            // Allow developers to register their own macros
            // Note: array must be in the following format
            // array('macro_key' => array('value' => 'macro_value'))
            // Note that brackets are not used in macro key
            'custom'    => (array) apply_filters('rp_wcec_custom_macros', array(), $args),
        );
    }

    /**
     * Get all macro list for UI
     *
     * @access public
     * @return array
     */
    public static function get_all_macro_list()
    {
        $list = array();

        foreach (self::get_all_macros() as $macro_group) {
            foreach ($macro_group as $macro => $value) {
                $list[$macro] = '{' . $macro . '}';
            }
        }

        return $list;
    }

    /**
     * Get list of global macros
     *
     * @access public
     * @return array
     */
    public static function get_global_macro_list()
    {
        return array(
            'site_title' => array(
                'custom' => 'macro_global_site_title_value',
            ),
        );
    }

    /**
     * Get list of order macros
     *
     * @access public
     * @return array
     */
    public static function get_order_macro_list()
    {
        return array(
            'billing_address_1' => array(
                'order_property' => 'billing_address_1',
            ),
            'billing_address_2' => array(
                'order_property' => 'billing_address_2',
            ),
            'billing_city' => array(
                'order_property' => 'billing_city',
            ),
            'billing_company' => array(
                'order_property' => 'billing_company',
            ),
            'billing_country' => array(
                'custom' => 'macro_order_billing_country_value',
            ),
            'billing_email' => array(
                'order_property' => 'billing_email',
            ),
            'billing_first_name' => array(
                'order_property' => 'billing_first_name',
            ),
            'billing_last_name' => array(
                'order_property' => 'billing_last_name',
            ),
            'billing_phone' => array(
                'order_property' => 'billing_phone',
            ),
            'billing_postcode' => array(
                'order_property' => 'billing_postcode',
            ),
            'billing_state' => array(
                'custom' => 'macro_order_billing_state_value',
            ),
            'customer_id' => array(
                'order_property' => 'customer_id',
            ),
            'customer_note' => array(
                'custom' => 'macro_order_customer_note_value',
            ),
            'formatted_billing_address' => array(
                'method' => 'get_formatted_billing_address',
            ),
            'formatted_shipping_address' => array(
                'method' => 'get_formatted_shipping_address',
            ),
            'order_customer_details' => array(
                'custom' => 'macro_order_order_customer_details_value',
            ),
            'order_currency' => array(
                'custom' => 'macro_order_currency_value',
            ),
            'order_date' => array(
                'custom' => 'macro_order_order_date_value',
            ),
            'order_items_table' => array(
                'custom' => 'macro_order_order_items_table_value',
            ),
            'order_notes' => array(
                'order_property' => 'customer_note',
            ),
            'order_number' => array(
                'method' => 'get_order_number',
            ),
            'order_payment_link' => array(
                'method' => 'get_checkout_payment_url',
            ),
            'order_shipping' => array(
                'custom' => 'macro_order_shipping_value',
            ),
            'order_status' => array(
                'custom' => 'macro_order_order_status_value',
            ),
            'order_total' => array(
                'method' => 'get_total',
            ),
            'payment_method_title' => array(
                'order_property' => 'payment_method_title',
            ),
            'shipping_address_1' => array(
                'order_property' => 'shipping_address_1',
            ),
            'shipping_address_2' => array(
                'order_property' => 'shipping_address_2',
            ),
            'shipping_city' => array(
                'order_property' => 'shipping_city',
            ),
            'shipping_company' => array(
                'order_property' => 'shipping_company',
            ),
            'shipping_country' => array(
                'custom' => 'macro_order_shipping_country_value',
            ),
            'shipping_first_name' => array(
                'order_property' => 'shipping_first_name',
            ),
            'shipping_last_name' => array(
                'order_property' => 'shipping_last_name',
            ),
            'shipping_method_title' => array(
                'method' => 'get_shipping_method',
            ),
            'shipping_postcode' => array(
                'order_property' => 'shipping_postcode',
            ),
            'shipping_state' => array(
                'custom' => 'macro_order_shipping_state_value',
            ),
            'total_discount' => array(
                'method' => 'get_total_discount',
            ),
        );
    }

    /**
     * Get list of user macros
     *
     * @access public
     * @return array
     */
    public static function get_user_macro_list()
    {
        return array(
            'user_id' => array(
                'userdata' => 'ID',
            ),
            'user_email' => array(
                'userdata' => 'user_email',
            ),
            'user_login' => array(
                'userdata' => 'user_login',
            ),
            'user_nickname' => array(
                'userdata' => 'nickname',
            ),
            'user_display_name' => array(
                'userdata' => 'display_name',
            ),
            'user_first_name' => array(
                'userdata' => 'first_name',
            ),
            'user_last_name' => array(
                'userdata' => 'last_name',
            ),
            'auto_login_link' => array(
                'custom' => 'macro_user_auto_login_link_value',
            ),
            'password_reset_link' => array(
                'custom' => 'macro_user_password_reset_link_value',
            ),
        );
    }

    /**
     * Get macro site_title value
     *
     * @access public
     * @return string
     */
    public static function macro_global_site_title_value()
    {
        return wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    }

    /**
     * Get macro order_status value
     *
     * @access public
     * @param object $order
     * @return string
     */
    public static function macro_order_order_status_value($order)
    {
        if (is_a($order, 'WC_Order')) {
            return wc_get_order_status_name($order->get_status());
        }

        return '';
    }

    /**
     * Get macro customer_note value
     *
     * @access public
     * @param object $order
     * @param array $args
     * @return string
     */
    public static function macro_order_customer_note_value($order, $args)
    {
        return !empty($args['customer_note']) ? $args['customer_note'] : '';
    }

    /**
     * Get macro billing_state value
     *
     * @access public
     * @param object $order
     * @return string
     */
    public static function macro_order_billing_state_value($order)
    {
        return self::macro_order_state_value('billing_state', $order);
    }

    /**
     * Get macro shipping_state value
     *
     * @access public
     * @param object $order
     * @return string
     */
    public static function macro_order_shipping_state_value($order)
    {
        return self::macro_order_state_value('shipping_state', $order);
    }

    /**
     * Get macro state value
     *
     * @access public
     * @param string $property
     * @param object $order
     * @return string
     */
    public static function macro_order_state_value($property, $order)
    {
        // Check order object
        if (is_a($order, 'WC_Order')) {

            $method = 'order_get_' . $property;

            // Get state
            if ($state = RightPress_WC_Legacy::$method($order)) {

                $method = 'order_get_' . ($property === 'billing_state' ? 'billing_country' : 'shipping_country');

                // Get country
                if ($country = RightPress_WC_Legacy::$method($order)) {

                    // Instantiate countries object
                    $countries = new WC_Countries();

                    // Iterate over all states
                    if ($countries && is_array($countries->states) && isset($countries->states[$country]) && isset($countries->states[$country][$state])) {

                        // Return match
                        return $countries->states[$country][$state];
                    }
                }
            }
        }

        return '';
    }

    /**
     * Get macro billing_country value
     *
     * @access public
     * @param object $order
     * @return string
     */
    public static function macro_order_billing_country_value($order)
    {
        return self::macro_order_country_value('billing_country', $order);
    }

    /**
     * Get macro shipping_country value
     *
     * @access public
     * @param object $order
     * @return string
     */
    public static function macro_order_shipping_country_value($order)
    {
        return self::macro_order_country_value('shipping_country', $order);
    }

    /**
     * Get macro country value
     *
     * @access public
     * @param string $property
     * @param object $order
     * @return string
     */
    public static function macro_order_country_value($property, $order)
    {
        // Check order object
        if (is_a($order, 'WC_Order')) {

            $method = 'order_get_' . $property;

            // Get country
            if ($country = RightPress_WC_Legacy::$method($order)) {

                // Instantiate countries object
                $countries = new WC_Countries();

                // Return corresponding country name
                if ($countries && is_array($countries->countries) && isset($countries->countries[$country])) {
                    return $countries->countries[$country];
                }
            }
        }

        return '';
    }

    /**
     * Get macro order_date value
     *
     * @access public
     * @param object $order
     * @return string
     */
    public static function macro_order_order_date_value($order)
    {
        if (is_a($order, 'WC_Order')) {
            if ($order_date = RightPress_WC_Legacy::order_get_formatted_date_created($order)) {
                return $order_date;
            }
        }

        return '';
    }

    /**
     * Get macro order_customer_details value
     *
     * @access public
     * @param object $order
     * @param array $args
     * @param string $content_type
     * @return string
     */
    public static function macro_order_order_customer_details_value($order, $args = array(), $content_type = 'text/plain')
    {
        // Check if order was passed
        if (is_a($order, 'WC_Order')) {

            // Check if we need to load custom templates (legacy functionality)
            if (RP_WCEC_Styling::are_styling_options_in_use() && RP_WCEC_Styling::are_wc_templates_overriden()) {

                // Determine template name
                $template = $content_type === 'text/html' ? 'emails/rp-wcec-customer-details' : 'emails/plain/rp-wcec-customer-details';

                // Allow developers to use their own templates
                $template = apply_filters('rp_wcec_email_template', $template);

                // Buffer output
                ob_start();

                // Include template
                RightPress_Helper::include_template($template, RP_WCEC_PLUGIN_PATH, 'woocommerce-email-center', array(
                    'order'         => $order,
                    'sent_to_admin' => (isset($args['sent_to_admin']) && $args['sent_to_admin']),
                    'plain_text'    => ($content_type === 'text/plain')
                ));

                // Get buffer contents and clear buffer
                return ob_get_clean();
            }
            else {

                // Buffer output
                ob_start();

                // Print customer details
                do_action('woocommerce_email_customer_details', $order, (isset($args['sent_to_admin']) && $args['sent_to_admin']), ($content_type === 'text/plain'), null);

                // Get buffer contents and clear buffer
                return ob_get_clean();
            }
        }

        return '';
    }

    /**
     * Get macro order_items_table value
     *
     * @access public
     * @param object $order
     * @param array $args
     * @param string $content_type
     * @return string
     */
    public static function macro_order_order_items_table_value($order, $args, $content_type = 'text/plain')
    {
        // Check if order was passed
        if (is_a($order, 'WC_Order')) {

            // Check if we need to load custom templates (legacy functionality)
            if (RP_WCEC_Styling::are_styling_options_in_use() && RP_WCEC_Styling::are_wc_templates_overriden()) {

                // Determine template name
                $template = $content_type === 'text/html' ? 'emails/rp-wcec-order-items-table' : 'emails/plain/rp-wcec-order-items-table';

                // Allow developers to use their own templates
                $template = apply_filters('rp_wcec_email_template', $template);

                // Buffer output
                ob_start();

                // Include template
                RightPress_Helper::include_template($template, RP_WCEC_PLUGIN_PATH, 'woocommerce-email-center', array(
                    'order'         => $order,
                    'sent_to_admin' => (isset($args['sent_to_admin']) && $args['sent_to_admin']),
                    'plain_text'    => ($content_type === 'text/plain')
                ));

                // Get buffer contents and clear buffer
                return ob_get_clean();
            }
            else {

                // Buffer output
                ob_start();

                // Print order details
                do_action('woocommerce_email_order_details', $order, (isset($args['sent_to_admin']) && $args['sent_to_admin']), ($content_type === 'text/plain'), null);

                // Get buffer contents and clear buffer
                return ob_get_clean();
            }
        }

        return '';
    }

    /**
     * Get macro auto_login_link value
     *
     * @access public
     * @param object $object
     * @param array $args
     * @param string $content_type
     * @param string $recipient_email
     * @return string
     */
    public static function macro_user_auto_login_link_value($object, $args, $content_type = 'text/plain', $recipient_email = null)
    {
        // Get user
        if ($user = get_user_by('email', $recipient_email)) {

            // Get auto login key
            $key = get_user_meta($user->ID, 'rp_wcec_auto_login_key', true);

            // Generate key if it does not exist yet
            if (!$key) {
                $key = RightPress_Helper::get_hash(true);
                update_user_meta($user->ID, 'rp_wcec_auto_login_key', $key);
            }

            // Generate public hash
            $public_hash = wp_hash_password($key);

            // Return auto login link
            return esc_url(add_query_arg(array('rp_wcec_auto_login_id' => $user->ID, 'rp_wcec_auto_login_hash' => $public_hash), home_url()));
        }

        return '';
    }

    /**
     * Get macro password_reset_link value
     *
     * @access public
     * @param object $object
     * @param array $args
     * @param string $content_type
     * @return string
     */
    public static function macro_user_password_reset_link_value($object, $args, $content_type = 'text/plain')
    {
        // Check if reset key and user login are set
        if (empty($args['password_reset_key']) || empty($args['user_login'])) {
            return '';
        }

        // Return password reset url
        $wc_endpoint_url = wc_get_endpoint_url('lost-password', '', wc_get_page_permalink('myaccount'));
        return esc_url(add_query_arg(array('key' => $args['password_reset_key'], 'login' => rawurlencode($args['user_login'])), $wc_endpoint_url));
    }

    /**
     * Get order property value
     *
     * @access public
     * @param object $order
     * @param string $property
     * @return mixed
     */
    public static function get_order_property_value($order, $property)
    {
        $method = 'order_get_' . $property;
        return RightPress_WC_Legacy::$method($order);
    }

    /**
     * Get macro order_currency value
     *
     * @access public
     * @param object $order
     * @return string
     */
    public static function macro_order_currency_value($order)
    {
        return RightPress_WC_Legacy::order_get_currency($order);
    }

    /**
     * Get macro order_shipping value
     *
     * @access public
     * @param object $order
     * @return string
     */
    public static function macro_order_shipping_value($order)
    {
        return strip_tags(wc_price(RightPress_WC_Legacy::order_get_shipping_total($order), array('currency' => RightPress_WC_Legacy::order_get_currency($order))));
    }




}
}
