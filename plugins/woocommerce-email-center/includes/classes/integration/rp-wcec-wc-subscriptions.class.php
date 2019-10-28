<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Integration with WooCommerce Subscriptions
 * https://woocommerce.com/products/woocommerce-subscriptions/
 *
 * @class RP_WCEC_WC_Subscriptions
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_WC_Subscriptions')) {

class RP_WCEC_WC_Subscriptions
{
    // Singleton instance
    protected static $instance = false;

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
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Integration is not active - do nothing
        if (!class_exists('WC_Subscriptions')) {
            return;
        }

        // Add conditions
        add_filter('rp_wcec_integration_conditions', array($this, 'add_conditions'), 30);
    }

    /**
     * Add conditions
     *
     * @access public
     * @param array $conditions
     * @return array
     */
    public function add_conditions($conditions)
    {
        $integration_conditions = array();

        // Order is renewal
        $integration_conditions['wc_subscriptions_order_is_renewal'] = array(
            'label'         => __('Order is renewal', 'rp_wcec'),
            'method'        => 'yes_no',
            'uses_fields'   => array(),
            'schedule_type' => array('conditions'),
            'context'       => array(
                'trigger' => array(
                    'woocommerce_order' => true,
                ),
            ),
            'callback'      => array('RP_WCEC_WC_Subscriptions', 'condition_check_wc_subscriptions_order_is_renewal'),
        );

        // Add whole group to integration conditions
        $conditions['wc_subscriptions'] = array(
            'label'     => __('WooCommerce Subscriptions', 'rp_wcec'),
            'children'  => $integration_conditions,
        );

        return $conditions;
    }

    /**
     * Condition check: Order is renewal
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_wc_subscriptions_order_is_renewal($condition, $args = array())
    {
        // Check if order is present
        if (!isset($args['order']) || !is_object($args['order'])) {
            return false;
        }

        // Check via function starting from version 2.0
        if (version_compare(WC_Subscriptions::$version, '2.0', '>=') && function_exists('wcs_order_contains_renewal')) {
            $is_renewal = wcs_order_contains_renewal($args['order']);
        }
        // Check via method in older versions
        else if (class_exists('WC_Subscriptions_Renewal_Order') && method_exists('WC_Subscriptions_Renewal_Order', 'is_renewal')) {
            $is_renewal = WC_Subscriptions_Renewal_Order::is_renewal($args['order']);
        }
        // Check in post meta
        else {
            $is_renewal = (bool) RightPress_WC_Meta::order_get_meta($args['order'], '_original_order', true);
        }

        // Check against condition
        return (($is_renewal && $condition['wc_subscriptions_order_is_renewal_method'] === 'yes') || (!$is_renewal && $condition['wc_subscriptions_order_is_renewal_method'] === 'no'));
    }

}

RP_WCEC_WC_Subscriptions::get_instance();

}
