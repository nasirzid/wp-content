<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to conditions
 *
 * @class RP_WCEC_Conditions
 * @package WooCommerce Email Center
 * @author RightPress
 */
if (!class_exists('RP_WCEC_Conditions')) {

class RP_WCEC_Conditions
{
    private static $conditions = array();
    private static $timeframes = array();
    private static $multiselect_field_keys = array(
        'order_statuses', 'coupons', 'payment_methods', 'products',
        'product_categories', 'attributes', 'tags', 'countries', 'states',
        'shipping_zones', 'roles', 'capabilities', 'users'
    );

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Make sure some actions run after all classes are initiated
        add_action('init', array($this, 'on_init'));

        // Ajax handlers
        add_action('wp_ajax_rp_wcec_load_multiselect_items', array($this, 'ajax_load_multiselect_items'));
    }

    /**
     * On init action
     *
     * @access public
     * @return void
     */
    public function on_init()
    {
        // Define default conditions (more conditions are available via integration with other extensions)
        self::$conditions = array(

            // Cart
            'cart' => array(
                'label'     => __('Cart', 'rp_wcec'),
                'children'  => array(

                    // Subtotal
                    'cart_subtotal' => array(
                        'label'         => __('Cart subtotal', 'rp_wcec'),
                        'method'        => 'at_least_less_than',
                        'uses_fields'   => array('decimal'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart' => true,
                            ),
                        ),
                    ),

                    // Coupons applied
                    'cart_coupons' => array(
                        'label'         => __('Coupons applied', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('coupons'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart' => true,
                            ),
                        ),
                    ),
                ),
            ),

            // Products In Cart
            'cart_products' => array(
                'label'     => __('Products In Cart', 'rp_wcec'),
                'children'  => array(

                    // Products
                    'cart_product' => array(
                        'label'         => __('Products', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('products'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart' => true,
                            ),
                        ),
                    ),

                    // Product categories
                    'cart_product_category' => array(
                        'label'         => __('Product categories', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('product_categories'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart' => true,
                            ),
                        ),
                    ),

                    // Product attributes
                    'cart_product_attribute' => array(
                        'label'         => __('Product attributes', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('attributes'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart' => true,
                            ),
                        ),
                    ),

                    // Product tags
                    'cart_product_tag' => array(
                        'label'         => __('Product tags', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('tags'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart' => true,
                            ),
                        ),
                    ),
                ),
            ),

            // Event Product
            'event_product' => array(
                'label'     => __('Event Product', 'rp_wcec'),
                'children'  => array(

                    // Product
                    'product' => array(
                        'label'         => __('Product', 'rp_wcec'),
                        'method'        => 'in_list_not_in_list',
                        'uses_fields'   => array('products'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart' => array(
                                    'add_to_cart' => true,
                                ),
                            ),
                        ),
                    ),

                    // Product categories
                    'product_category' => array(
                        'label'         => __('Product category', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('product_categories'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart' => array(
                                    'add_to_cart' => true,
                                ),
                            ),
                        ),
                    ),

                    // Product attributes
                    'product_attribute' => array(
                        'label'         => __('Product attributes', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('attributes'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart' => array(
                                    'add_to_cart' => true,
                                ),
                            ),
                        ),
                    ),

                    // Product tags
                    'product_tag' => array(
                        'label'         => __('Product tags', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('tags'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart' => array(
                                    'add_to_cart' => true,
                                ),
                            ),
                        ),
                    ),
                ),
            ),

            // Order
            'order' => array(
                'label'     => __('Order', 'rp_wcec'),
                'children'  => array(

                    // Order total
                    'order_total' => array(
                        'label'         => __('Order total', 'rp_wcec'),
                        'method'        => 'at_least_less_than',
                        'uses_fields'   => array('decimal'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Shipping total
                    // Currently disabled to make the conditions list less crowded
                    // Will be enabled if requested by a few clients
                    /*
                    'order_shipping' => array(
                        'label'         => __('Shipping total', 'rp_wcec'),
                        'method'        => 'at_least_less_than',
                        'uses_fields'   => array('decimal'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),
                    */

                    // Order discount
                    // Currently disabled to make the conditions list less crowded
                    // Will be enabled if requested by a few clients
                    /*
                    'order_discount' => array(
                        'label'         => __('Order discount', 'rp_wcec'),
                        'method'        => 'at_least_less_than',
                        'uses_fields'   => array('decimal'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),
                    */

                    // Order status
                    'order_status' => array(
                        'label'         => __('Order status', 'rp_wcec'),
                        'method'        => 'in_list_not_in_list',
                        'uses_fields'   => array('order_statuses'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Coupons applied
                    'order_coupons' => array(
                        'label'         => __('Coupons applied', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('coupons'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Payment method
                    'payment_method' => array(
                        'label'         => __('Payment method', 'rp_wcec'),
                        'method'        => 'in_list_not_in_list',
                        'uses_fields'   => array('payment_methods'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),
                ),
            ),

            // Products In Order
            'order_products' => array(
                'label'     => __('Products In Order', 'rp_wcec'),
                'children'  => array(

                    // Products
                    'order_product' => array(
                        'label'         => __('Products', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('products'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Product categories
                    'order_product_category' => array(
                        'label'         => __('Product categories', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('product_categories'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Product attributes
                    'order_product_attribute' => array(
                        'label'         => __('Product attributes', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('attributes'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Product tags
                    'order_product_tag' => array(
                        'label'         => __('Product tags', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('tags'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),
                ),
            ),

            // Billing Address
            'billing_address' => array(
                'label'     => __('Billing Address', 'rp_wcec'),
                'children'  => array(

                    // Billing Country
                    'billing_country' => array(
                        'label'         => __('Billing country', 'rp_wcec'),
                        'method'        => 'in_list_not_in_list',
                        'uses_fields'   => array('countries'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Billing State
                    'billing_state' => array(
                        'label'         => __('Billing state', 'rp_wcec'),
                        'method'        => 'in_list_not_in_list',
                        'uses_fields'   => array('states'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Billing City
                    'billing_city' => array(
                        'label'         => __('Billing city', 'rp_wcec'),
                        'method'        => 'text_comparison',
                        'uses_fields'   => array('text'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Billing Postcode
                    'billing_postcode' => array(
                        'label'         => __('Billing postcode', 'rp_wcec'),
                        'method'        => 'matches_does_not_match',
                        'uses_fields'   => array('text'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),
                ),
            ),

            // Shipping Address
            'shipping_address' => array(
                'label'     => __('Shipping Address', 'rp_wcec'),
                'children'  => array(

                    // Shipping Country
                    'shipping_country' => array(
                        'label'         => __('Shipping country', 'rp_wcec'),
                        'method'        => 'in_list_not_in_list',
                        'uses_fields'   => array('countries'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Shipping State
                    'shipping_state' => array(
                        'label'         => __('Shipping state', 'rp_wcec'),
                        'method'        => 'in_list_not_in_list',
                        'uses_fields'   => array('states'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Shipping City
                    'shipping_city' => array(
                        'label'         => __('Shipping city', 'rp_wcec'),
                        'method'        => 'text_comparison',
                        'uses_fields'   => array('text'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),

                    // Shipping Postcode
                    'shipping_postcode' => array(
                        'label'         => __('Shipping postcode', 'rp_wcec'),
                        'method'        => 'matches_does_not_match',
                        'uses_fields'   => array('text'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_order' => true,
                            ),
                        ),
                    ),
                ),
            ),

            // Customer
            'customer' => array(
                'label'     => __('Customer', 'rp_wcec'),
                'children'  => array(

                    // Role
                    'role' => array(
                        'label'         => __('Role', 'rp_wcec'),
                        'method'        => 'in_list_not_in_list',
                        'uses_fields'   => array('roles'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Capability
                    'capability' => array(
                        'label'         => __('Capability', 'rp_wcec'),
                        'method'        => 'in_list_not_in_list',
                        'uses_fields'   => array('capabilities'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Specific customer
                    'customer' => array(
                        'label'         => __('Specific customer', 'rp_wcec'),
                        'method'        => 'in_list_not_in_list',
                        'uses_fields'   => array('users'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Customer meta field
                    'customer_meta_field' => array(
                        'label'         => __('Customer meta', 'rp_wcec'),
                        'method'        => 'meta_field',
                        'uses_fields'   => array('meta_key', 'text'),
                        'schedule_type' => array('conditions'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),
                ),
            ),

            // Customer Value
            'history' => array(
                'label'     => __('Customer Value', 'rp_wcec'),
                'children'  => array(

                    // Amount spent
                    'amount_spent' => array(
                        'label'         => __('Amount spent', 'rp_wcec'),
                        'method'        => 'at_least_less_than',
                        'uses_fields'   => array('timeframe_all_time', 'decimal'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Order count
                    'order_count' => array(
                        'label'         => __('Order count', 'rp_wcec'),
                        'method'        => 'at_least_less_than',
                        'uses_fields'   => array('timeframe_all_time', 'number'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),
                ),
            ),

            // Customer Purchase History
            'products_purchased' => array(
                'label'     => __('Customer Purchase History', 'rp_wcec'),
                'children'  => array(

                    // Products within
                    'product_within' => array(
                        'label'         => __('Products within', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('timeframe_all_time', 'products'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Product categories within
                    'product_category_within' => array(
                        'label'         => __('Product categories within', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('timeframe_all_time', 'product_categories'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Product attributes within
                    'product_attribute_within' => array(
                        'label'         => __('Product attributes within', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('timeframe_all_time', 'attributes'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Product tags within
                    'product_tag_within' => array(
                        'label'         => __('Product tags within', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('timeframe_all_time', 'tags'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),
                ),
            ),

            // Last Order
            /**
             * Currently disabled to make the conditions list less crowded
             * Will be enabled if requested by a few clients
            'last_order' => array(
                'label'     => __('Last Order', 'rp_wcec'),
                'children'  => array(

                    // Last order amount
                    'last_order_amount' => array(
                        'label'         => __('Last order amount', 'rp_wcec'),
                        'method'        => 'at_least_less_than',
                        'uses_fields'   => array('decimal'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Last paid order amount
                    'last_paid_order_amount' => array(
                        'label'         => __('Last paid order amount', 'rp_wcec'),
                        'method'        => 'at_least_less_than',
                        'uses_fields'   => array('decimal'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Last order
                    'last_order_time' => array(
                        'label'         => __('Last order', 'rp_wcec'),
                        'method'        => 'within_past_earlier_than',
                        'uses_fields'   => array('timeframe'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Last paid order
                    'last_paid_order_time' => array(
                        'label'         => __('Last paid order', 'rp_wcec'),
                        'method'        => 'within_past_earlier_than',
                        'uses_fields'   => array('timeframe'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),
                ),
            ),

            // Products In Last Order
            'last_order_products' => array(
                'label'     => __('Products In Last Order', 'rp_wcec'),
                'children'  => array(

                    // Products
                    'last_order_product' => array(
                        'label'         => __('Products', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('products'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Product categories
                    'last_order_product_category' => array(
                        'label'         => __('Product categories', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('product_categories'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Product attributes
                    'last_order_product_attribute' => array(
                        'label'         => __('Product attributes', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('attributes'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),

                    // Product tags
                    'last_order_product_tag' => array(
                        'label'         => __('Product tags', 'rp_wcec'),
                        'method'        => 'at_least_one_all_none',
                        'uses_fields'   => array('tags'),
                        'schedule_type' => array('conditions', 'conditions_scheduled'),
                        'context'       => array(
                            'trigger' => array(
                                'woocommerce_cart'  => true,
                                'woocommerce_order' => true,
                                'customer'          => true,
                            ),
                        ),
                    ),
                ),
            ),
            */
        );

        // Conditions available from WC 2.6
        if (RightPress_Helper::wc_version_gte('2.6')) {

            // Order shipping zone condition
            self::$conditions['shipping_address']['children']['shipping_zone'] = array(
                'label'         => __('Shipping zone', 'rp_wcec'),
                'method'        => 'in_list_not_in_list',
                'uses_fields'   => array('shipping_zones'),
                'schedule_type' => array('conditions'),
                'context'       => array(
                    'trigger' => array(
                        'woocommerce_order' => true,
                    ),
                ),
            );
        }

        // Add custom conditions
        self::$conditions = RP_WCEC_Conditions::add_integration_conditions(self::$conditions);
        self::$conditions = RP_WCEC_Conditions::add_custom_conditions(self::$conditions);

        // Generate timeframes
        for ($i = 1; $i <= 6; $i++) {
            self::$timeframes[$i . '_day'] = array(
                'label' => $i . ' ' . _n('day', 'days', $i, 'rp_wcec'),
                'value' => $i . ($i === 1 ? ' day' : ' days'),
            );
        }
        for ($i = 1; $i <= 4; $i++) {
            self::$timeframes[$i . '_week'] = array(
                'label' => $i . ' ' . _n('week', 'weeks', $i, 'rp_wcec'),
                'value' => $i . ($i === 1 ? ' week' : ' weeks'),
            );
        }
        for ($i = 1; $i <= 24; $i++) {
            self::$timeframes[$i . '_month'] = array(
                'label' => $i . ' ' . _n('month', 'months', $i, 'rp_wcec'),
                'value' => $i . ($i === 1 ? ' month' : ' months'),
            );
        }
        for ($i = 3; $i <= 10; $i++) {
            self::$timeframes[$i . '_year'] = array(
                'label' => $i . ' ' . _n('year', 'years', $i, 'rp_wcec'),
                'value' => $i . ($i === 1 ? ' year' : ' years'),
            );
        }
    }

    /**
     * Add integration conditions
     *
     * @access public
     * @param array $conditions
     * @return array
     */
    public static function add_integration_conditions($conditions)
    {
        // DEVELOPERS: Do not use this filter, this is for internal purposes only, use rp_wcec_custom_conditions instead
        return array_merge($conditions, apply_filters('rp_wcec_integration_conditions', array()));
    }

    /**
     * Add custom conditions
     *
     * @access public
     * @param array $conditions
     * @return array
     */
    public static function add_custom_conditions($conditions)
    {
        // Get custom conditions
        $custom_conditions = apply_filters('rp_wcec_custom_conditions', array());

        // Remove invalid conditions
        foreach ($custom_conditions as $condition_group_key => $condition_group) {

            // No children
            if (empty($condition_group['children']) || !is_array($condition_group['children'])) {
                unset($custom_conditions[$condition_group_key]);
                continue;
            }

            // Iterate over children
            foreach ($condition_group['children'] as $condition_key => $condition) {

                // Make sure condition key does not exist in main conditions array
                foreach (self::$conditions as $main_condition_group) {
                    if (isset($main_condition_group['children'][$condition_key])) {
                        unset($custom_conditions[$condition_group_key]['children'][$condition_key]);
                        break;
                    }
                }

                // No conditions left? Remove group
                if (empty($custom_conditions[$condition_group_key]['children'])) {
                    unset($custom_conditions[$condition_group_key]);
                    break;
                }
            }
        }

        // Merge remaining conditions
        $conditions = array_merge($conditions, $custom_conditions);

        // Return full conditions list
        return $conditions;
    }

    /**
     * Get condition list
     *
     * @access public
     * @return void
     */
    public static function get_condition_list()
    {
        return self::$conditions;
    }

    /**
     * Return conditions for display in admin ui
     *
     * @access public
     * @return array
     */
    public static function conditions($context = 'trigger')
    {
        $result = array();

        // Iterate over all conditions groups
        foreach (self::get_condition_list() as $group_key => $group) {

            // Iterate over conditions
            foreach ($group['children'] as $condition_key => $condition) {

                // Check if this condition is used in given context
                if (!isset($condition['context'][$context])) {
                    continue;
                }

                // Add group if needed
                if (!isset($result[$group_key])) {
                    $result[$group_key] = array(
                        'label'     => $group['label'],
                        'options'  => array(),
                    );
                }

                // Push condition to group
                $result[$group_key]['options'][$condition_key] = $condition['label'];
            }
        }

        return $result;
    }

    /**
     * Check if condition uses field
     *
     * @access public
     * @param string $condition
     * @param string $field
     * @return bool
     */
    public static function uses_field($condition, $field)
    {
        foreach (self::get_condition_list() as $condition_group) {
            if (isset($condition_group['children'][$condition])) {
                return in_array($field, $condition_group['children'][$condition]['uses_fields']);
            }
        }

        return false;
    }

    /**
     * Get field size
     *
     * @access public
     * @param string $group
     * @param string $condition
     * @return string
     */
    public static function field_size($group, $condition, $method_field = false)
    {
        // Get conditions
        $conditions = self::get_condition_list();

        // Special case for yes/no methods
        if ($method_field) {
            return $conditions[$group]['children'][$condition]['method'] === 'yes_no' ? 'triple' : 'single';
        }

        // Special case for meta fields (width changed dynamically via JS)
        if (in_array($condition, array('customer_meta_field'))) {
            return 'single';
        }

        // All other cases
        switch (count($conditions[$group]['children'][$condition]['uses_fields'])) {
            case 2:
                return 'single';
            case 1:
                return 'double';
            default:
                return 'triple';
        }
    }

    /**
     * Return methods of particular condition for display in admin ui
     *
     * @access public
     * @param string $condition
     * @return array
     */
    public static function methods($condition)
    {
        foreach (self::get_condition_list() as $condition_group) {
            if (isset($condition_group['children'][$condition])) {
                switch ($condition_group['children'][$condition]['method']) {

                    // yes, no
                    case 'yes_no':
                        return array(
                            'yes'   => __('yes', 'rp_wcec'),
                            'no'    => __('no', 'rp_wcec'),
                        );

                    // in list, not in list
                    case 'in_list_not_in_list':
                        return array(
                            'in_list'       => __('in list', 'rp_wcec'),
                            'not_in_list'   => __('not in list', 'rp_wcec'),
                        );

                    // at least, less than
                    case 'at_least_less_than':
                        return array(
                            'at_least'  => __('at least', 'rp_wcec'),
                            'less_than' => __('less than', 'rp_wcec'),
                        );

                    // at least one, all, none
                    case 'at_least_one_all_none':
                        return array(
                            'at_least_one'  => __('at least one of selected', 'rp_wcec'),
                            'all'           => __('all of selected', 'rp_wcec'),
                            'none'          => __('none of selected', 'rp_wcec'),
                        );

                    // within past, earlier than
                    case 'within_past_earlier_than':
                        return array(
                            'later'     => __('within past', 'rp_wcec'),
                            'earlier'   => __('earlier than', 'rp_wcec'),
                        );

                    // matches, does not match
                    case 'matches_does_not_match':
                        return array(
                            'matches'           => __('matches', 'rp_wcec'),
                            'does_not_match'    => __('does not match', 'rp_wcec'),
                        );

                    // equals, does not equal, contains, does not contain
                    case 'text_comparison':
                        return array(
                            'equals'            => __('equals', 'rp_wcec'),
                            'does_not_equal'    => __('does not equal', 'rp_wcec'),
                            'contains'          => __('contains', 'rp_wcec'),
                            'does_not_contain'  => __('does not contain', 'rp_wcec'),
                            'begins_with'       => __('begins with', 'rp_wcec'),
                            'ends_with'         => __('ends with', 'rp_wcec'),
                        );

                    // is empty, is not empty, contains, does not contain, equals, does not equal etc
                    case 'meta_field':
                        return array(
                            'is_empty'          => __('is empty', 'rp_wcec'),
                            'is_not_empty'      => __('is not empty', 'rp_wcec'),
                            'contains'          => __('contains', 'rp_wcec'),
                            'does_not_contain'  => __('does not contain', 'rp_wcec'),
                            'begins_with'       => __('begins with', 'rp_wcec'),
                            'ends_with'         => __('ends with', 'rp_wcec'),
                            'equals'            => __('equals', 'rp_wcec'),
                            'does_not_equal'    => __('does not equal', 'rp_wcec'),
                            'less_than'         => __('less than', 'rp_wcec'),
                            'less_or_equal_to'  => __('less or equal to', 'rp_wcec'),
                            'more_than'         => __('more than', 'rp_wcec'),
                            'more_or_equal'     => __('more or equal to', 'rp_wcec'),
                            'is_checked'        => __('is checked', 'rp_wcec'),
                            'is_not_checked'    => __('is not checked', 'rp_wcec'),
                        );

                    default:
                        return array();
                }
            }
        }
    }

    /**
     * Return timeframes for display in admin ui
     *
     * @access public
     * @param bool $include_all_time
     * @return array
     */
    public static function timeframes($include_all_time = false)
    {
        $result = array();

        // Add all time timeframe for some conditions
        if ($include_all_time) {
            $result['all_time'] = __('all time', 'rp_wcec');
        }

        // Iterate over all timeframes
        foreach (self::$timeframes as $timeframe_key => $timeframe) {
            $result[$timeframe_key] = $timeframe['label'];
        }

        return apply_filters('rp_wcec_timeframes', $result);
    }

    /**
     * Load multiselect items
     *
     * @access public
     * @return void
     */
    public function ajax_load_multiselect_items()
    {
        // Define data types that we are aware of
        $types = array(
            'order_statuses', 'coupons', 'payment_methods',
            'product_categories', 'products', 'attributes', 'tags', 'countries',
            'states', 'shipping_zones', 'roles', 'capabilities', 'users'
        );

        // Make sure we know the type which is requested and query is not empty
        if (!in_array($_POST['type'], $types) || empty($_POST['query'])) {
            $items[] = array(
                'id'        => 0,
                'text'      => __('No search query sent', 'rp_wcec'),
                'disabled'  => 'disabled'
            );
        }
        else {

            // Get items
            $selected = isset($_POST['selected']) && is_array($_POST['selected']) ? $_POST['selected'] : array();
            $items = $this->get_items($_POST['type'], $_POST['query'], $selected);

            // No items?
            if (empty($items)) {
                $items[] = array(
                    'id'        => 0,
                    'text'      => __('Nothing found', 'rp_wcec'),
                    'disabled'  => 'disabled'
                );
            }
        }

        // Return data and exit
        echo RightPress_Helper::json_encode_multiselect_options(array(
            'result'    => 'success',
            'items'     => $items,
        ));
        exit;
    }

    /**
     * Load items for multiselect fields based on search criteria and item type
     *
     * @access public
     * @param string $type
     * @param string $query
     * @param array $selected
     * @return array
     */
    public function get_items($type, $query, $selected)
    {
        $items = array();

        // Get items by type
        $method = 'get_items_' . $type;
        $all_items = $this->$method(array(), $query);

        // Iterate over returned items
        foreach ($all_items as $item_key => $item) {

            // Filter items that match search criteria
            if (RightPress_Helper::string_contains_phrase($item['text'], $query)) {

                // Filter items that are not yet selected
                if (empty($selected) || !in_array($item['id'], $selected)) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * Load already selected multiselect field items by their ids
     *
     * @access public
     * @param string $type
     * @param array $ids
     * @return array
     */
    public static function get_items_by_ids($type, $ids = array())
    {
        $method = 'get_items_' . $type;
        $object = new self();
        return $object->$method($ids);
    }

    /**
     * Load order statuses for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_order_statuses($ids = array(), $query = '')
    {
        $items = array();

        // Get order statuses
        $statuses = wc_get_order_statuses();

        // Remove wc- prefix
        if (is_array($statuses)) {
            foreach ($statuses as $status_key => $status_title) {

                // Get status
                $status = (substr($status_key, 0, 3) === 'wc-' ? substr($status_key, 3) : $status_key);

                // Check if specific statuses were requested
                if (empty($ids) || in_array($status, $ids)) {
                    $items[] = array(
                        'id'    => $status,
                        'text'  => $status_title,
                    );
                }
            }
        }

        return $items;
    }

    /**
     * Load coupons for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_coupons($ids = array(), $query = '')
    {
        $items = array();

        // WC31: coupons may no longer be posts

        // Get all coupon ids
        $args = array(
            'posts_per_page'    => -1,
            'post_type'         => 'shop_coupon',
            'post_status'       => array('publish'),
            'fields'            => 'ids',
        );

        // Query passed in
        if (!empty($query)) {
            $args['rp_wcec_title_query'] = $query;
        }

        // Specific coupons requested
        if (!empty($ids)) {
            $args['post__in'] = $ids;
        }

        $posts_raw = get_posts($args);

        // Format results array
        foreach ($posts_raw as $post_id) {
            $items[] = array(
                'id'    => $post_id,
                'text'  => get_the_title($post_id)
            );
        }

        return $items;
    }

    /**
     * Load payment methods for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_payment_methods($ids = array(), $query = '')
    {
        $items = array();

        // Get payment gateways
        $payment_gateways = WC()->payment_gateways->payment_gateways();

        // Iterate over payment gateways
        foreach ($payment_gateways as $payment_gateway) {
            if (empty($ids) || in_array(esc_attr($payment_gateway->id), $ids)) {
                $items[] = array(
                    'id'    => esc_attr($payment_gateway->id),
                    'text'  => $payment_gateway->get_title(),
                );
            }
        }

        return $items;
    }

    /**
     * Load product categories for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_product_categories($ids = array(), $query = '')
    {
        $items = array();

        // WC31: Product categories may no longer be post terms

        $post_categories_raw = get_terms(array('product_cat'), array('hide_empty' => 0));
        $post_categories_raw_count = count($post_categories_raw);

        foreach ($post_categories_raw as $post_cat_key => $post_cat) {
            $category_name = $post_cat->name;

            if ($post_cat->parent) {
                $parent_id = $post_cat->parent;
                $has_parent = true;

                // Make sure we don't have an infinite loop here (happens with some kind of "ghost" categories)
                $found = false;
                $i = 0;

                while ($has_parent && ($i < $post_categories_raw_count || $found)) {

                    // Reset each time
                    $found = false;
                    $i = 0;

                    foreach ($post_categories_raw as $parent_post_cat_key => $parent_post_cat) {

                        $i++;

                        if ($parent_post_cat->term_id == $parent_id) {
                            $category_name = $parent_post_cat->name . ' → ' . $category_name;
                            $found = true;

                            if ($parent_post_cat->parent) {
                                $parent_id = $parent_post_cat->parent;
                            }
                            else {
                                $has_parent = false;
                            }

                            break;
                        }
                    }
                }
            }

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($post_cat->term_id, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $post_cat->term_id,
                'text'  => $category_name
            );
        }

        return $items;
    }

    /**
     * Load products for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_products($ids = array(), $query = '')
    {
        $items = array();

        // WC31: Products will no longer be posts

        // Get all product ids
        $args = array(
            'posts_per_page'    => -1,
            'post_type'         => 'product',
            'post_status'       => array('publish', 'pending', 'draft', 'future', 'private', 'inherit'),
            'fields'            => 'ids',
        );

        // Query passed in
        if (!empty($query)) {
            $args['rp_wcec_title_query'] = $query;
        }

        // Specific products requested
        if (!empty($ids)) {
            $args['post__in'] = $ids;
        }

        $posts_raw = get_posts($args);

        // Format results array
        foreach ($posts_raw as $post_id) {
            $items[] = array(
                'id'    => $post_id,
                'text'  => '#' . $post_id . ' ' . get_the_title($post_id)
            );
        }

        return $items;
    }

    /**
     * Load product attributes for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_attributes($ids = array(), $query = '')
    {
        $items = array();
        global $wc_product_attributes;

        // WC31: Check if this still works correctly

        // Iterate over product attributes
        foreach ($wc_product_attributes as $attribute_key => $attribute) {

            $attribute_name = !empty($attribute->attribute_label) ? $attribute->attribute_label : $attribute->attribute_name;

            $subitems = array();

            $children_raw = get_terms(array($attribute_key), array('hide_empty' => 0));
            $children_raw_count = count($children_raw);

            foreach ($children_raw as $child_key => $child) {
                $child_name = $child->name;

                if ($child->parent) {
                    $parent_id = $child->parent;
                    $has_parent = true;

                    // Make sure we don't have an infinite loop here
                    $found = false;
                    $i = 0;

                    while ($has_parent && ($i < $children_raw_count || $found)) {

                        // Reset each time
                        $found = false;
                        $i = 0;

                        foreach ($children_raw as $parent_child_key => $parent_child) {

                            $i++;

                            if ($parent_child->term_id == $parent_id) {
                                $child_name = $parent_child->name . ' → ' . $child_name;
                                $found = true;

                                if ($parent_child->parent) {
                                    $parent_id = $parent_child->parent;
                                }
                                else {
                                    $has_parent = false;
                                }

                                break;
                            }
                        }
                    }
                }

                // Skip this item if we don't need it
                if (!empty($ids) && !in_array($child->term_id, $ids)) {
                    continue;
                }

                // Add item
                $subitems[] = array(
                    'id'    => $child->term_id,
                    'text'  => $child_name
                );
            }

            // Iterate over subitems and make a list of item/subitem pairs
            foreach ($subitems as $subitem) {
                $items[] = array(
                    'id'    => $subitem['id'],
                    'text'  => $attribute_name . ': ' . $subitem['text'],
                );
            }
        }

        return $items;
    }

    /**
     * Load product tags for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_tags($ids = array(), $query = '')
    {
        $items = array();

        $tags_raw = get_terms(array('product_tag'), array('hide_empty' => 0));

        // Iterate over all tags
        foreach ($tags_raw as $tag_key => $tag) {

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($tag->term_id, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $tag->term_id,
                'text'  => $tag->name,
            );
        }

        return $items;
    }

    /**
     * Load countries for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_countries($ids = array(), $query = '')
    {
        $items = array();

        $countries = new WC_Countries();

        // Iterate over all countries
        if ($countries && is_array($countries->countries)) {
            foreach ($countries->countries as $country_code => $country_name) {

                // Add item
                $items[] = array(
                    'id'    => $country_code,
                    'text'  => $country_name,
                );
            }
        }

        return $items;
    }

    /**
     * Load states for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_states($ids = array(), $query = '')
    {
        $items = array();

        $countries = new WC_Countries();
        $all_states = $countries->get_states();

        // Iterate over all countries
        if ($countries && is_array($countries->countries) && is_array($all_states)) {
            foreach ($all_states as $country_key => $states) {
                if (is_array($states) && !empty($states)) {

                    // Get country name
                    $country_name = !empty($countries->countries[$country_key]) ? $countries->countries[$country_key] : $country_key;

                    // Iterate over all states
                    foreach ($states as $state_key => $state) {

                        // Add item
                        $items[] = array(
                            'id'    => $country_key . '_' . $state_key,
                            'text'  => $country_name . ': ' . $state,
                        );
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Load shipping zones for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_shipping_zones($ids = array(), $query = '')
    {
        $items = array();

        // Iterate over shipping zones
        foreach (WC_Shipping_Zones::get_zones() as $shipping_zone) {

            // Add item
            $items[] = array(
                'id'    => 'wc_' . $shipping_zone['zone_id'],
                'text'  => $shipping_zone['zone_name'],
            );
        }

        // Get Rest of the World shipping zone
        $shipping_zone = WC_Shipping_Zones::get_zone(0);

        // Add to array
        $items = array_merge(array(array(
            'id'    => 'wc_' . RightPress_WC_Legacy::shipping_zone_get_id($shipping_zone),
            'text'  => $shipping_zone->get_zone_name(),
        )), $items);

        return $items;
    }

    /**
     * Load roles for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_roles($ids = array(), $query = '')
    {
        $items = array();

        // Get roles
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        // Iterate over roles and format results array
        foreach ($wp_roles->get_names() as $role_key => $role) {

            // Skip this item if we don't need it
            if (!empty($ids) && !in_array($role_key, $ids)) {
                continue;
            }

            // Add item
            $items[] = array(
                'id'    => $role_key,
                'text'  => $role . ' (' . $role_key . ')',
            );
        }

        return $items;
    }

    /**
     * Load capabilities for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_capabilities($ids = array(), $query = '')
    {
        $items = array();

        // Groups plugin active?
        if (class_exists('Groups_User') && class_exists('Groups_Wordpress') && function_exists('_groups_get_tablename')) {

            global $wpdb;
            $capability_table = _groups_get_tablename('capability');
            $all_capabilities = $wpdb->get_results('SELECT capability FROM ' . $capability_table);

            if ($all_capabilities) {
                foreach ($all_capabilities as $capability) {

                    // Skip this item if we don't need it
                    if (!empty($ids) && !in_array($capability, $ids)) {
                        continue;
                    }

                    // Add item
                    $items[] = array(
                        'id'    => $capability->capability,
                        'text'  => $capability->capability
                    );
                }
            }
        }

        // Get standard WP capabilities
        else {
            global $wp_roles;

            if (!isset($wp_roles)) {
                get_role('administrator');
            }

            $roles = $wp_roles->roles;

            $already_added = array();

            if (is_array($roles)) {
                foreach ($roles as $rolename => $atts) {
                    if (isset($atts['capabilities']) && is_array($atts['capabilities'])) {
                        foreach ($atts['capabilities'] as $capability => $value) {
                            if (!in_array($capability, $already_added)) {

                                // Skip this item if we don't need it
                                if (!empty($ids) && !in_array($capability, $ids)) {
                                    continue;
                                }

                                // Add item
                                $items[] = array(
                                    'id'    => $capability,
                                    'text'  => $capability
                                );
                                $already_added[] = $capability;
                            }
                        }
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Load users for multiselect fields based on search criteria
     *
     * @access public
     * @param array $ids
     * @param string $query
     * @return array
     */
    public function get_items_users($ids = array(), $query = '')
    {
        $items = array();

        // Get users
        $users = get_users(array(
            'fields' => array('ID', 'user_email'),
        ));

        // Iterate over users
        foreach ($users as $user) {

            // Add item
            $items[] = array(
                'id'    => $user->ID,
                'text'  => '#' . $user->ID . ' ' . $user->user_email,
            );
        }

        return $items;
    }

    /**
     * Get condition group and option from group_option string
     *
     * @access public
     * @param string $group_and_option
     * @return mixed
     */
    public static function extract_group_and_option($group_and_option)
    {
        $group_key = null;

        foreach (self::get_condition_list() as $potential_group_key => $potential_group) {
            if (strpos($group_and_option, $potential_group_key) === 0) {
                $group_key = $potential_group_key;
            }
        }

        if ($group_key === null) {
            return false;
        }

        $option_key = preg_replace('/^' . $group_key . '_/i', '', $group_and_option);

        return array($group_key, $option_key);
    }

    /**
     * Check if trigger matches conditions
     *
     * @access public
     * @param object $trigger
     * @param array $args
     * @param string $type
     * @return bool
     */
    public static function trigger_matches_conditions($trigger, $args = array(), $type = 'conditions')
    {
        $matches = true;
        $method = 'get_' . $type;
        $conditions = $trigger->$method();

        // Get all conditions
        $all_conditions = RP_WCEC_Conditions::get_condition_list();

        // Iterate over trigger conditions
        if (!empty($conditions) && is_array($conditions)) {
            foreach ($conditions as $condition) {

                $condition_found = false;

                // Find it in conditions list
                foreach ($all_conditions as $condition_group_key => $condition_group) {
                    foreach ($condition_group['children'] as $condition_config_key => $condition_config) {

                        // Check if condition was found
                        if ($condition['type'] === $condition_config_key) {

                            // Custom callback
                            if (!empty($condition_config['callback'])) {
                                if (!call_user_func($condition_config['callback'], $condition, $args)) {
                                    return false;
                                }
                            }
                            // Default handling
                            else if (!self::condition_is_matched($condition, $args)) {
                                return false;
                            }

                            $condition_found = true;
                            break;
                        }
                    }

                    // Break from loop if condition was found
                    if ($condition_found) {
                        break;
                    }
                }

                // Condition was not found - do not proceed
                if (!$condition_found) {
                    return false;
                }
            }
        }

        return $matches;
    }

    /**
     * Check if condition is matched
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_is_matched($condition, $args = array())
    {
        $method = 'condition_check_' . $condition['type'];
        return self::$method($condition, $args);
    }

    /**
     * Condition check: Cart - Subtotal
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_cart_subtotal($condition, $args = array())
    {
        global $woocommerce;

        // Get cart subtotal
        $cart_subtotal = $woocommerce->cart->subtotal;

        // Check condition
        return self::compare_at_least_less_than($condition['cart_subtotal_method'], $cart_subtotal, $condition['decimal']);
    }

    /**
     * Condition check: Cart - Coupons
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_cart_coupons($condition, $args = array())
    {
        global $woocommerce;

        // Get applied coupon ids
        $cart_coupons = array();

        if (isset($woocommerce->cart->applied_coupons) && is_array($woocommerce->cart->applied_coupons)) {
            foreach ($woocommerce->cart->applied_coupons as $applied_coupon) {
                $cart_coupons[] = RightPress_Helper::get_wc_coupon_id_from_code($applied_coupon);
            }
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_coupons_method'], $cart_coupons, $condition['coupons']);
    }

    /**
     * Condition check: Cart - Product
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_cart_product($condition, $args = array())
    {
        global $woocommerce;
        $product_ids = array();

        // Iterate over cart items and pick product ids
        foreach ($woocommerce->cart->cart_contents as $item) {
            if (isset($item['data']) && is_object($item['data'])) {
                $product_ids[] = $item['data']->is_type('variation') ? RightPress_WC_Legacy::product_variation_get_parent_id($item['data']) : RightPress_WC_Legacy::product_get_id($item['data']);
            }
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_product_method'], $product_ids, $condition['products']);
    }

    /**
     * Condition check: Cart - Product category
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_cart_product_category($condition, $args = array())
    {
        global $woocommerce;
        $product_category_ids = array();

        // Get list of category ids
        foreach ($woocommerce->cart->cart_contents as $item) {
            if (isset($item['data']) && is_object($item['data'])) {

                $product_id = $item['data']->is_type('variation') ? RightPress_WC_Legacy::product_variation_get_parent_id($item['data']) : RightPress_WC_Legacy::product_get_id($item['data']);
                // WC31: Product categories may no longer be post terms
                $item_categories = wp_get_post_terms($product_id, 'product_cat');

                foreach ($item_categories as $category) {
                    if (!in_array($category->term_id, $product_category_ids)) {
                        $product_category_ids[] = $category->term_id;
                    }
                }
            }
        }

        // Get condition category IDs with all children
        $condition_categories_split = self::get_product_category_ids_with_children($condition);
        $condition_categories = self::merge_all_children($condition_categories_split);

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_product_category_method'], $product_category_ids, $condition_categories, $condition_categories_split);
    }

    /**
     * Condition check: Cart - Product attribute
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_cart_product_attribute($condition, $args = array())
    {
        global $woocommerce;
        $attribute_ids = array();

        // Iterate over cart items
        foreach($woocommerce->cart->cart_contents as $item) {

            // Get selected variable product attributes
            $selected = (!empty($item['variation'])) ? $item['variation'] : array();

            // Get attribute ids
            if ($current_ids = RightPress_Helper::get_wc_product_attribute_ids($item['product_id'], $selected)) {
                $attribute_ids = array_unique(array_merge($attribute_ids, $current_ids));
            }
        }

        // Get condition attribute IDs with all children
        $condition_attributes_split = self::get_product_attribute_ids_with_children($condition);
        $condition_attributes = self::merge_all_children($condition_attributes_split);

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_product_attribute_method'], $attribute_ids, $condition_attributes, $condition_attributes_split);
    }

    /**
     * Condition check: Cart - Product tag
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_cart_product_tag($condition, $args = array())
    {
        global $woocommerce;
        $tag_ids = array();

        // Iterate over cart items
        foreach($woocommerce->cart->cart_contents as $item) {
            if (isset($item['product_id'])) {

                // Get tag ids
                if ($current_ids = RightPress_Helper::get_wc_product_tag_ids($item['product_id'])) {
                    $tag_ids = array_unique(array_merge($tag_ids, $current_ids));
                }
            }
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['cart_product_tag_method'], $tag_ids, $condition['tags']);
    }

    /**
     * Condition check: Event Product - Product
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product($condition, $args = array())
    {
        // Check if product ID is set
        if (isset($args['product_id'])) {

            // Check condition
            return self::compare_at_least_one_all_none($condition['product_method'], $args['product_id'], $condition['products']);
        }

        return false;
    }

    /**
     * Condition check: Event Product - Product Category
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product_category($condition, $args = array())
    {
        // Check if product ID is set
        if (isset($args['product_id'])) {

            // Get product categories
            // WC31: Product categories may no longer be post terms
            $product_categories = wp_get_post_terms($args['product_id'], 'product_cat');
            $product_category_ids = array();

            // Make list of category IDs
            foreach ($product_categories as $category) {
                $product_category_ids[] = $category->term_id;
            }

            // Get condition category IDs with all children
            $condition_categories_split = self::get_product_category_ids_with_children($condition);
            $condition_categories = self::merge_all_children($condition_categories_split);

            // Check condition
            return self::compare_at_least_one_all_none($condition['product_category_method'], $product_category_ids, $condition_categories, $condition_categories_split);
        }

        return false;
    }

    /**
     * Condition check: Event Product - Product Attribute
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product_attribute($condition, $args = array())
    {
        // Check if product ID is set
        if (isset($args['product_id'])) {

            // Get selected attributes
            $selected = array();

            // Iterate over variation data
            if (isset($args['variation']) && is_array($args['variation'])) {
                foreach ($args['variation'] as $meta_key => $meta_value) {
                    if (RightPress_Helper::string_begins_with_substring($meta_key, 'pa_')) {
                        $selected[$meta_key] = $meta_value;
                    }
                }
            }

            // Get attribute IDs
            $attribute_ids = RightPress_Helper::get_wc_product_attribute_ids($args['product_id'], $selected);

            // Get condition attribute IDs with all children
            $condition_attributes_split = self::get_product_attribute_ids_with_children($condition);
            $condition_attributes = self::merge_all_children($condition_attributes_split);

            // Check condition
            return self::compare_at_least_one_all_none($condition['product_attribute_method'], $attribute_ids, $condition_attributes, $condition_attributes_split);
        }

        return false;
    }

    /**
     * Condition check: Event Product - Product Attribute
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product_tag($condition, $args = array())
    {
        // Check if product ID is set
        if (isset($args['product_id'])) {

            // Get tag IDs
            $tag_ids = RightPress_Helper::get_wc_product_tag_ids($args['product_id']);

            // Check condition
            return self::compare_at_least_one_all_none($condition['product_tag_method'], $tag_ids, $condition['tags']);
        }

        return false;
    }

    /**
     * Condition check: Product - Product
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product_product($condition, $args = array())
    {
        // Check if product ID is specified
        if (isset($args['product_id']) && is_numeric($args['product_id'])) {
            return false;
        }

        // Check condition
        return self::compare_in_list_not_in_list($condition['product_product_method'], $args['product_id'], $condition['products']);
    }

    /**
     * Condition check: Product - Product category
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product_product_category($condition, $args = array())
    {
        // Check if product ID is specified
        if (isset($args['product_id']) && is_numeric($args['product_id'])) {
            return false;
        }

        // Get product category IDs
        $product_category_ids = self::get_order_product_category_ids(null, $args['product_id']);

        // Get condition category IDs with all children
        $condition_categories_split = self::get_product_category_ids_with_children($condition);
        $condition_categories = self::merge_all_children($condition_categories_split);

        // Check condition
        return self::compare_in_list_not_in_list($condition['product_product_category_method'], $product_category_ids, $condition_categories, $condition_categories_split);
    }

    /**
     * Condition check: Product - Product attribute
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product_product_attribute($condition, $args = array())
    {
        // Check if product ID is specified
        if (isset($args['product_id']) && is_numeric($args['product_id'])) {
            return false;
        }

        // Get product variation attributes
        $variation_attributes = !empty($args['variation']) ? $args['variation'] : array();

        // Get product attribute IDs
        $attribute_ids = self::get_order_product_attribute_ids(null, $args['product_id'], $variation_attributes);

        // Get condition attribute IDs with all children
        $condition_attributes_split = self::get_product_attribute_ids_with_children($condition);
        $condition_attributes = self::merge_all_children($condition_attributes_split);

        // Check condition
        return self::compare_at_least_one_all_none($condition['product_product_attribute_method'], $attribute_ids, $condition_attributes, $condition_attributes_split);
    }

    /**
     * Condition check: Product - Product tag
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product_product_tag($condition, $args = array())
    {
        // Check if product ID is specified
        if (isset($args['product_id']) && is_numeric($args['product_id'])) {
            return false;
        }

        // Get product tag IDs
        $tag_ids = self::get_order_product_tag_ids(null, $args['product_id']);

        // Check condition
        return self::compare_at_least_one_all_none($condition['product_product_tag_method'], $tag_ids, $condition['tags']);
    }

    /**
     * Condition check: Order - Total
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_order_total($condition, $args = array())
    {
        // Check if order is present
        if (!isset($args['order']) || !is_object($args['order'])) {
            return false;
        }

        // Get order total
        $order_total = $args['order']->get_total();

        // Check condition
        return self::compare_at_least_less_than($condition['order_total_method'], $order_total, $condition['decimal']);
    }

    /**
     * Condition check: Order - Shipping
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_order_shipping($condition, $args = array())
    {
        // Check if order is present
        if (!isset($args['order']) || !is_object($args['order'])) {
            return false;
        }

        // Get order shipping
        $order_shipping = RightPress_WC_Legacy::order_get_shipping_total($args['order']);

        // Check condition
        return self::compare_at_least_less_than($condition['order_shipping_method'], $order_shipping, $condition['decimal']);
    }

    /**
     * Condition check: Order - Discount
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_order_discount($condition, $args = array())
    {
        // Check if order is present
        if (!isset($args['order']) || !is_object($args['order'])) {
            return false;
        }

        // Get order discount
        $order_discount = $args['order']->get_total_discount();

        // Check condition
        return self::compare_at_least_less_than($condition['order_discount_method'], $order_discount, $condition['decimal']);
    }

    /**
     * Condition check: Order - Status
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_order_status($condition, $args = array())
    {
        // Check if order is present
        if (!isset($args['order']) || !is_object($args['order'])) {
            return false;
        }

        // Get order status
        $order_status = $args['order']->get_status();

        // Get condition statuses
        $condition_statuses = isset($condition['order_statuses']) ? (array) $condition['order_statuses'] : array();

        // Check condition
        return self::compare_in_list_not_in_list($condition['order_status_method'], $order_status, $condition_statuses);
    }

    /**
     * Condition check: Order - Coupons
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_order_coupons($condition, $args = array())
    {
        // Check if order is present
        if (!isset($args['order']) || !is_a($args['order'], 'WC_Order')) {
            return false;
        }

        // Get order coupons
        $order_coupons = $args['order']->get_used_coupons();

        // Get coupon ids
        foreach ($order_coupons as $order_coupon_key => $order_coupon) {
            if ($coupon_id = RightPress_Helper::get_wc_coupon_id_from_code($order_coupon)) {
                $order_coupons[$order_coupon_key] = $coupon_id;
            }
            else {
                unset($order_coupons[$order_coupon_key]);
            }
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_coupons_method'], $order_coupons, $condition['coupons']);
    }

    /**
     * Condition check: Order - Payment method
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_payment_method($condition, $args = array())
    {
        // Check if order is present
        if (!isset($args['order']) || !is_a($args['order'], 'WC_Order')) {
            return false;
        }

        // Get payment method
        $payment_method = RightPress_WC_Legacy::order_get_payment_method($args['order']);

        // No payment method set
        if (empty($payment_method)) {
            return false;
        }

        // Check condition
        return self::compare_in_list_not_in_list($condition['payment_method_method'], esc_attr($payment_method), $condition['payment_methods']);
    }

    /**
     * Condition check: Products - Product
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_order_product($condition, $args = array())
    {
        // Check if order is present
        if (!isset($args['order']) || !is_object($args['order'])) {
            return false;
        }

        // Get order product IDs
        $ids = self::get_order_product_property_ids('', $args['order']);

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_product_method'], $ids, $condition['products']);
    }

    /**
     * Condition check: Products - Product category
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_order_product_category($condition, $args = array())
    {
        // Check if order is present
        if (!isset($args['order']) || !is_object($args['order'])) {
            return false;
        }

        // Get order product category IDs
        $ids = self::get_order_product_property_ids('category', $args['order']);

        // Get category IDs with all children
        $condition_categories_split = self::get_product_category_ids_with_children($condition);
        $condition_categories = self::merge_all_children($condition_categories_split);

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_product_category_method'], $ids, $condition_categories, $condition_categories_split);
    }

    /**
     * Condition check: Products - Product attribute
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_order_product_attribute($condition, $args = array())
    {
        // Check if order is present
        if (!isset($args['order']) || !is_object($args['order'])) {
            return false;
        }

        // Get order product attribute IDs
        $ids = self::get_order_product_property_ids('attribute', $args['order']);

        // Get condition attribute IDs with all children
        $condition_attributes_split = self::get_product_attribute_ids_with_children($condition);
        $condition_attributes = self::merge_all_children($condition_attributes_split);

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_product_attribute_method'], $ids, $condition_attributes, $condition_attributes_split);
    }

    /**
     * Condition check: Products - Product tag
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_order_product_tag($condition, $args = array())
    {
        // Check if order is present
        if (!isset($args['order']) || !is_object($args['order'])) {
            return false;
        }

        // Get order product tag IDs
        $ids = self::get_order_product_property_ids('tag', $args['order']);

        // Check condition
        return self::compare_at_least_one_all_none($condition['order_product_tag_method'], $ids, $condition['tags']);
    }

    /**
     * Condition check: Billing - Country
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_billing_country($condition, $args = array())
    {
        return self::condition_check_country($condition, $args, 'billing');
    }

    /**
     * Condition check: Shipping - Country
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_shipping_country($condition, $args = array())
    {
        return self::condition_check_country($condition, $args, 'shipping');
    }

    /**
     * Condition check: Order Country
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @param string $context
     * @return bool
     */
    public static function condition_check_country($condition, $args = array(), $context = 'billing')
    {
        // Check if order is present
        if (!isset($args['order']) || !is_a($args['order'], 'WC_Order')) {
            return false;
        }

        // Get country
        $method = 'order_get_' . $context . '_country';
        $country = RightPress_WC_Legacy::$method($args['order']);

        // Country is not set
        if (empty($country)) {
            return false;
        }

        // Get condition countries
        $condition_countries = isset($condition['countries']) ? (array) $condition['countries'] : array();

        // Check condition
        return self::compare_in_list_not_in_list($condition[$context . '_country_method'], $country, $condition_countries);
    }

    /**
     * Condition check: Billing - State
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_billing_state($condition, $args = array())
    {
        return self::condition_check_state($condition, $args, 'billing');
    }

    /**
     * Condition check: Shipping - State
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_shipping_state($condition, $args = array())
    {
        return self::condition_check_state($condition, $args, 'shipping');
    }

    /**
     * Condition check: Order State
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @param string $context
     * @return bool
     */
    public static function condition_check_state($condition, $args = array(), $context = 'billing')
    {
        // Check if order is present
        if (!isset($args['order']) || !is_a($args['order'], 'WC_Order')) {
            return false;
        }

        // Get state
        $method = 'order_get_' . $context . '_state';
        $state = RightPress_WC_Legacy::$method($args['order']);

        // State is not set
        if (empty($state)) {
            return false;
        }

        // Get country
        $method = 'order_get_' . $context . '_country';
        $country = RightPress_WC_Legacy::$method($args['order']);

        // Country is not set
        if (empty($country)) {
            return false;
        }

        // Prepend country code to state name
        $state = $country . '_' . $state;

        // Get condition states
        $condition_states = isset($condition['states']) ? (array) $condition['states'] : array();

        // Check condition
        return self::compare_in_list_not_in_list($condition[$context . '_state_method'], $state, $condition_states);
    }

    /**
     * Condition check: Billing - City
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_billing_city($condition, $args = array())
    {
        return self::condition_check_city($condition, $args, 'billing');
    }

    /**
     * Condition check: Shipping - City
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_shipping_city($condition, $args = array())
    {
        return self::condition_check_city($condition, $args, 'shipping');
    }

    /**
     * Condition check: Order City
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @param string $context
     * @return bool
     */
    public static function condition_check_city($condition, $args = array(), $context = 'billing')
    {
        // Check if order is present
        if (!isset($args['order']) || !is_a($args['order'], 'WC_Order')) {
            return false;
        }

        // Get city name
        $method = 'order_get_' . $context . '_city';
        $city = RightPress_WC_Legacy::$method($args['order']);

        // Get condition city
        $condition_city = !empty($condition['text']) ? $condition['text'] : '';

        // Check condition
        return self::compare_text_comparison($condition[$context . '_city_method'], $city, $condition_city);
    }

    /**
     * Condition check: Billing - Postcode
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_billing_postcode($condition, $args = array())
    {
        return self::condition_check_postcode($condition, $args, 'billing');
    }

    /**
     * Condition check: Shipping - Postcode
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_shipping_postcode($condition, $args = array())
    {
        return self::condition_check_postcode($condition, $args, 'shipping');
    }

    /**
     * Condition check: Order Postcode
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @param string $context
     * @return bool
     */
    public static function condition_check_postcode($condition, $args = array(), $context = 'billing')
    {
        // Check if order is present
        if (!isset($args['order']) || !is_a($args['order'], 'WC_Order')) {
            return false;
        }

        // Get postcode
        $postcode_method = 'order_get_' . $context . '_postcode';
        $postcode_value = RightPress_WC_Legacy::$postcode_method($args['order']);

        // Postcode is not set - nothing to compare against
        if (empty($postcode_value)) {
            return false;
        }

        // Get method
        $method = $condition[$context . '_postcode_method'];

        // No postcode set in conditions
        if (empty($condition['text'])) {
            return $method === 'matches' ? false : true;
        }

        // Break up postcode string
        $postcodes = explode(',', $condition['text']);

        // Iterate over postcodes
        foreach ($postcodes as $postcode) {

            // Clean value
            $postcode = trim($postcode);

            // Postcode is empty
            if (empty($postcode) && $postcode !== '0') {
                continue;
            }

            // Postcode with wildcards
            if (strpos($postcode, '*') !== false) {

                // Prepare regex string
                $regex = '/^' . str_replace('\*', '.', preg_quote($postcode)) . '$/i';

                // Compare
                if (preg_match($regex, $postcode_value) === 1 && $method === 'matches') {
                    return true;
                }
            }

            // Postcode range
            else if (strpos($postcode, '-') !== false) {

                // Split range
                $ranges = explode('-', $postcode);
                $ranges[0] = trim($ranges[0]);
                $ranges[1] = trim($ranges[1]);

                // Check if ranges are valid
                if (count($ranges) !== 2 || (empty($ranges[0]) && $ranges[0] !== '0') || (empty($ranges[1]) && $ranges[1] !== '0') || !is_numeric($ranges[0]) || !is_numeric($ranges[1]) || $ranges[0] >= $ranges[1]) {
                    continue;
                }

                // Check if post code is within ranges
                if ($ranges[0] <= $postcode_value && $postcode_value <= $ranges[1]) {
                    return $method === 'matches' ? true : false;
                }
                else {
                    return $method === 'matches' ? false : true;
                }
            }

            // Full postcode
            else if ($postcode === $postcode_value && $method === 'matches') {
                return true;
            }
        }

        return $method === 'matches' ? false : true;
    }

    /**
     * Condition check: Shipping - Zone
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_shipping_zone($condition, $args = array())
    {
        // This functionality is only available in WC 2.6+
        if (!RightPress_Helper::wc_version_gte('2.6')) {
            return false;
        }

        // Check if order is present
        if (!isset($args['order']) || !is_object($args['order'])) {
            return false;
        }

        // Get data for package
        $package = array(
            'destination' => array(
                'country'   => RightPress_WC_Legacy::order_get_shipping_country($args['order']),
                'state'     => RightPress_WC_Legacy::order_get_shipping_state($args['order']),
                'postcode'  => RightPress_WC_Legacy::order_get_shipping_postcode($args['order']),
            )
        );

        // Determine WC Shipping Zone
        $zone = WC_Shipping_Zones::get_zone_matching_package($package);

        // Get condition zones
        $condition_zones = isset($condition['shipping_zones']) ? (array) $condition['shipping_zones'] : array();

        // Check condition
        return self::compare_in_list_not_in_list($condition['shipping_zone_method'], 'wc_' . RightPress_WC_Legacy::shipping_zone_get_id($zone), $condition_zones);
    }

    /**
     * Condition check: Customer - Role
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_role($condition, $args = array())
    {
        // Get user ID
        $user_id = self::determine_user_id($args);

        // Check if user ID was returned
        if (!isset($user_id)) {
            return ($condition['role_method'] === 'not_in_list');
        }

        // Get user roles
        $user_roles = RightPress_Helper::user_roles($user_id);

        // Get condition roles
        $condition_roles = isset($condition['roles']) ? (array) $condition['roles'] : array();

        // Check condition
        return self::compare_in_list_not_in_list($condition['role_method'], $user_roles, $condition_roles);
    }

    /**
     * Condition check: Customer - Capability
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_capability($condition, $args = array())
    {
        // Get user ID
        $user_id = self::determine_user_id($args);

        // Check if user ID was returned
        if (!isset($user_id)) {
            return ($condition['capability_method'] === 'not_in_list');
        }

        // Get user capabilities
        $user_capabilities = RightPress_Helper::user_capabilities($user_id);

        // Get condition capabilities
        $condition_capabilities = isset($condition['capabilities']) ? (array) $condition['capabilities'] : array();

        // Check condition
        return self::compare_in_list_not_in_list($condition['capability_method'], $user_capabilities, $condition_capabilities);
    }

    /**
     * Condition check: Customer - Customer
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_customer($condition, $args = array())
    {
        // Get user ID
        $user_id = self::determine_user_id($args);

        // Check if user ID was returned
        if (!isset($user_id)) {
            return ($condition['customer_method'] === 'not_in_list');
        }

        // Get condition users
        $condition_users = isset($condition['users']) ? (array) $condition['users'] : array();

        // Check condition
        return self::compare_in_list_not_in_list($condition['customer_method'], $user_id, $condition_users);
    }

    /**
     * Condition check: Customer - Customer meta field
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_customer_meta_field($condition, $args = array())
    {
        // No meta key set
        if (empty($condition['meta_key'])) {
            return false;
        }

        // Get user ID
        $user_id = self::determine_user_id($args);

        // Get method
        $method = $condition['customer_meta_field_method'];

        // Proceed if user ID is known
        if ($user_id) {

            // Get user meta
            $meta = RightPress_Helper::unwrap_post_meta(get_user_meta($user_id, $condition['meta_key']));

            // Iterate over post meta
            if (!empty($meta) && is_array($meta)) {
                foreach ($meta as $single) {

                    // Proceed depending on condition method
                    switch ($method) {

                        // Is Empty
                        case 'is_empty':
                            return self::is_empty($single);

                        // Is Not Empty
                        case 'is_not_empty':
                            return !self::is_empty($single);

                        // Contains
                        case 'contains':
                            return self::contains($single, $condition['text']);

                        // Does Not Contain
                        case 'does_not_contain':
                            return !self::contains($single, $condition['text']);

                        // Begins with
                        case 'begins_with':
                            return self::begins_with($single, $condition['text']);

                        // Ends with
                        case 'ends_with':
                            return self::ends_with($single, $condition['text']);

                        // Equals
                        case 'equals':
                            return self::equals($single, $condition['text']);

                        // Does Not Equal
                        case 'does_not_equal':
                            return !self::equals($single, $condition['text']);

                        // Less Than
                        case 'less_than':
                            return self::less_than($single, $condition['text']);

                        // Less Or Equal To
                        case 'less_or_equal_to':
                            return !self::more_than($single, $condition['text']);

                        // More Than
                        case 'more_than':
                            return self::more_than($single, $condition['text']);

                        // More Or Equal
                        case 'more_or_equal':
                            return !self::less_than($single, $condition['text']);

                        // Is Checked
                        case 'is_checked':
                            return self::is_checked($single);

                        // Is Not Checked
                        case 'is_not_checked':
                            return !self::is_checked($single);

                        default:
                            return false;
                    }
                }
            }
        }

        // Nothing matched, proceed depending on condition method
        return in_array($method, array('is_empty', 'does_not_contain', 'does_not_equal', 'is_not_checked')) ? true : false;
    }

    /**
     * Condition check: Customer Value - Amount spent
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_amount_spent($condition, $args = array())
    {
        // Amount is not specified in condition
        if ((empty($condition['decimal']) && $condition['decimal'] !== '0')) {
            return false;
        }

        // Get order ids
        $all_order_ids = self::get_matching_order_ids($condition['timeframe'], $args, RP_WCEC::get_wc_order_paid_statuses(true));

        $amount_spent = 0;

        // Iterate over all order ids and sum up totals
        foreach ($all_order_ids as $order_id) {
            // WC31: Orders will no longer be posts (and total won't be post meta)
            $amount_spent += (float) RightPress_WC_Meta::order_get_meta($order_id, '_order_total', true);
        }

        // Check condition
        return self::compare_at_least_less_than($condition['amount_spent_method'], $amount_spent, $condition['decimal']);
    }

    /**
     * Condition check: Customer Value - Order count
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_order_count($condition, $args = array())
    {
        // Amount is not specified in condition
        if ((empty($condition['number']) && $condition['number'] !== '0')) {
            return false;
        }

        // Get order ids
        $all_order_ids = self::get_matching_order_ids($condition['timeframe'], $args);

        $order_count = 0;

        // Get order count
        if ($all_order_ids && is_array($all_order_ids)) {
            $order_count = count($all_order_ids);
        }

        // Check condition
        return self::compare_at_least_less_than($condition['order_count_method'], $order_count, $condition['number']);
    }

    /**
     * Condition check: Last Order - Last order amount
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @param bool $paid_only
     * @return bool
     */
    public static function condition_check_last_order_amount($condition, $args = array(), $paid_only = false)
    {
        // Get last order ID
        if ($paid_only) {
            $last_order_id = RP_WCEC_Conditions::get_last_paid_customer_order_id($args);
        }
        else {
            $last_order_id = RP_WCEC_Conditions::get_last_active_customer_order_id($args);
        }

        // Get last order amount
        // WC31: Orders will no longer be posts (and total won't be post meta)
        $last_order_amount = $last_order_id ? (float) RightPress_WC_Meta::order_get_meta($last_order_id, '_order_total', true) : 0;

        // Check condition
        $method_key = ($paid_only ? 'last_paid_order_amount_method' : 'last_order_amount_method');
        return self::compare_at_least_less_than($condition[$method_key], $last_order_amount, $condition['decimal']);
    }

    /**
     * Condition check: Last Order - Last paid order amount
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_last_paid_order_amount($condition, $args = array())
    {
        return self::condition_check_last_order_amount($condition, $args, true);
    }

    /**
     * Condition check: Last Order - Last order time
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @param bool $paid_only
     * @return bool
     */
    public static function condition_check_last_order_time($condition, $args = array(), $paid_only = false)
    {
        // Check if we have correct timeframe set
        if (!isset($condition['timeframe']) || !isset(self::$timeframes[$condition['timeframe']])) {
            return false;
        }

        // Get last order ID
        if ($paid_only) {
            $last_order_id = RP_WCEC_Conditions::get_last_paid_customer_order_id($args);
        }
        else {
            $last_order_id = RP_WCEC_Conditions::get_last_active_customer_order_id($args);
        }

        // Customer has no orders
        if (!$last_order_id) {
            return false;
        }

        // Get last order date
        $order = wc_get_order($last_order_id);
        $order_date = RightPress_WC_Legacy::order_get_formatted_date_created($order, 'Y-m-d');

        // Check if date is within timeframe
        $within_timeframe = self::date_is_within_timeframe($order_date, self::$timeframes[$condition['timeframe']]);

        // Check against method
        $method_key = ($paid_only ? 'last_paid_order_time_method' : 'last_order_time_method');
        return ($condition[$method_key] === 'earlier' ? !$within_timeframe : $within_timeframe);
    }

    /**
     * Condition check: Last Order - Last paid order time
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_last_paid_order_time($condition, $args = array())
    {
        return self::condition_check_last_order_time($condition, $args, true);
    }

    /**
     * Condition check: Last Order - Product
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_last_order_product($condition, $args = array())
    {
        // Get last order ID
        $last_order_id = RP_WCEC_Conditions::get_last_active_customer_order_id($args);

        // Customer has no orders
        if (!$last_order_id) {
            return ($condition['last_order_product_method'] === 'none');
        }

        // Get order object
        $order = wc_get_order($last_order_id);

        // Check if order is present
        if (!$order) {
            return false;
        }

        // Get order product IDs
        $ids = self::get_order_product_property_ids('', $order);

        // Check condition
        return self::compare_at_least_one_all_none($condition['last_order_product_method'], $ids, $condition['products']);
    }

    /**
     * Condition check: Last Order - Product category
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_last_order_product_category($condition, $args = array())
    {
        // Get last order ID
        $last_order_id = RP_WCEC_Conditions::get_last_active_customer_order_id($args);

        // Customer has no orders
        if (!$last_order_id) {
            return ($condition['last_order_product_category_method'] === 'none');
        }

        // Get order object
        $order = wc_get_order($last_order_id);

        // Check if order is present
        if (!$order) {
            return false;
        }

        // Get order product category IDs
        $ids = self::get_order_product_property_ids('category', $order);

        // Get category IDs with all children
        $condition_categories_split = self::get_product_category_ids_with_children($condition);
        $condition_categories = self::merge_all_children($condition_categories_split);

        // Check condition
        return self::compare_at_least_one_all_none($condition['last_order_product_category_method'], $ids, $condition_categories, $condition_categories_split);
    }

    /**
     * Condition check: Last Order - Product attribute
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_last_order_product_attribute($condition, $args = array())
    {
        // Get last order ID
        $last_order_id = RP_WCEC_Conditions::get_last_active_customer_order_id($args);

        // Customer has no orders
        if (!$last_order_id) {
            return ($condition['last_order_product_attribute_method'] === 'none');
        }

        // Get order object
        $order = wc_get_order($last_order_id);

        // Check if order is present
        if (!$order) {
            return false;
        }

        // Get order product attribute IDs
        $ids = self::get_order_product_property_ids('attribute', $order);

        // Get condition attribute IDs with all children
        $condition_attributes_split = self::get_product_attribute_ids_with_children($condition);
        $condition_attributes = self::merge_all_children($condition_attributes_split);

        // Check condition
        return self::compare_at_least_one_all_none($condition['last_order_product_attribute_method'], $ids, $condition_attributes, $condition_attributes_split);
    }

    /**
     * Condition check: Last Order - Product tag
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_last_order_product_tag($condition, $args = array())
    {
        // Get last order ID
        $last_order_id = RP_WCEC_Conditions::get_last_active_customer_order_id($args);

        // Customer has no orders
        if (!$last_order_id) {
            return ($condition['last_order_product_tag_method'] === 'none');
        }

        // Get order object
        $order = wc_get_order($last_order_id);

        // Check if order is present
        if (!$order) {
            return false;
        }

        // Get order product tag IDs
        $ids = self::get_order_product_property_ids('tag', $order);

        // Check condition
        return self::compare_at_least_one_all_none($condition['last_order_product_tag_method'], $ids, $condition['tags']);
    }

    /**
     * Condition check: Products Purchased Within - Product
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product_within($condition, $args = array())
    {
        // Get order ids
        $all_order_ids = self::get_matching_order_ids($condition['timeframe'], $args);

        // Customer has no matching orders
        if (empty($all_order_ids)) {
            return ($condition['product_within_method'] === 'none');
        }

        // Store Ids
        $ids = array();

        // Iterate over matching order IDs
        foreach ($all_order_ids as $order_id) {

            // Get order object
            $order = wc_get_order($order_id);

            // Check if order is present
            if (!$order) {
                continue;
            }

            // Get order product IDs
            $current_ids = self::get_order_product_property_ids('', $order);
            $ids = array_unique(array_merge($ids, $current_ids));
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['product_within_method'], $ids, $condition['products']);
    }

    /**
     * Condition check: Products Purchased Within - Product category
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product_category_within($condition, $args = array())
    {
        // Get order ids
        $all_order_ids = self::get_matching_order_ids($condition['timeframe'], $args);

        // Customer has no matching orders
        if (empty($all_order_ids)) {
            return ($condition['product_category_within_method'] === 'none');
        }

        // Store Ids
        $ids = array();

        // Iterate over matching order IDs
        foreach ($all_order_ids as $order_id) {

            // Get order object
            $order = wc_get_order($order_id);

            // Check if order is present
            if (!$order) {
                continue;
            }

            // Get order product category IDs
            $current_ids = self::get_order_product_property_ids('category', $order);
            $ids = array_unique(array_merge($ids, $current_ids));
        }

        // Get category IDs with all children
        $condition_categories_split = self::get_product_category_ids_with_children($condition);
        $condition_categories = self::merge_all_children($condition_categories_split);

        // Check condition
        return self::compare_at_least_one_all_none($condition['product_category_within_method'], $ids, $condition_categories, $condition_categories_split);
    }

    /**
     * Condition check: Products Purchased Within - Product attribute
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product_attribute_within($condition, $args = array())
    {
        // Get order ids
        $all_order_ids = self::get_matching_order_ids($condition['timeframe'], $args);

        // Customer has no matching orders
        if (empty($all_order_ids)) {
            return ($condition['product_attribute_within_method'] === 'none');
        }

        // Store Ids
        $ids = array();

        // Iterate over matching order IDs
        foreach ($all_order_ids as $order_id) {

            // Get order object
            $order = wc_get_order($order_id);

            // Check if order is present
            if (!$order) {
                continue;
            }

            // Get order product attribute IDs
            $current_ids = self::get_order_product_property_ids('attribute', $order);
            $ids = array_unique(array_merge($ids, $current_ids));
        }

        // Get condition attribute IDs with all children
        $condition_attributes_split = self::get_product_attribute_ids_with_children($condition);
        $condition_attributes = self::merge_all_children($condition_attributes_split);

        // Check condition
        return self::compare_at_least_one_all_none($condition['product_attribute_within_method'], $ids, $condition_attributes, $condition_attributes_split);
    }

    /**
     * Condition check: Products Purchased Within - Product tag
     *
     * @access public
     * @param array $condition
     * @param array $args
     * @return bool
     */
    public static function condition_check_product_tag_within($condition, $args = array())
    {
        // Get order ids
        $all_order_ids = self::get_matching_order_ids($condition['timeframe'], $args);

        // Customer has no matching orders
        if (empty($all_order_ids)) {
            return ($condition['product_tag_within_method'] === 'none');
        }

        // Store Ids
        $ids = array();

        // Iterate over matching order IDs
        foreach ($all_order_ids as $order_id) {

            // Get order object
            $order = wc_get_order($order_id);

            // Check if order is present
            if (!$order) {
                continue;
            }

            // Get order product tag IDs
            $current_ids = self::get_order_product_property_ids('tag', $order);
            $ids = array_unique(array_merge($ids, $current_ids));
        }

        // Check condition
        return self::compare_at_least_one_all_none($condition['product_tag_within_method'], $ids, $condition['tags']);
    }

    /**
     * Check if value is empty (but not zero)
     *
     * @access public
     * @param mixed $value
     * @return bool
     */
    public static function is_empty($value)
    {
        return ($value === '' || $value === null || count($value) === 0);
    }

    /**
     * Check if value contains string
     *
     * @access public
     * @param mixed $value
     * @param string $string
     * @return bool
     */
    public static function contains($value, $string)
    {
        if (gettype($value) === 'array') {
            return in_array($string, $value);
        }
        else {
            return (strpos($value, $string) !== false);
        }

        return false;
    }

    /**
     * Check if value begins with string
     *
     * @access public
     * @param mixed $value
     * @param string $string
     * @return bool
     */
    public static function begins_with($value, $string)
    {
        if (gettype($value) === 'array') {
            $first = array_shift($value);
            return $first == $string;
        }
        else {
            return RightPress_Helper::string_begins_with_substring($value, $string);
        }

        return false;
    }

    /**
     * Check if value ends with string
     *
     * @access public
     * @param mixed $value
     * @param string $string
     * @return bool
     */
    public static function ends_with($value, $string)
    {
        if (gettype($value) === 'array') {
            $last = array_pop($value);
            return $last == $string;
        }
        else {
            return RightPress_Helper::string_ends_with_substring($value, $string);
        }

        return false;
    }

    /**
     * Check if value equals string
     *
     * @access public
     * @param mixed $value
     * @param string $string
     * @return bool
     */
    public static function equals($value, $string)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value === $string) {
                    return true;
                }
            }
        }
        else {
            return ($value === $string);
        }

        return false;
    }

    /**
     * Check if value is less than number
     *
     * @access public
     * @param mixed $value
     * @param string $number
     * @return bool
     */
    public static function less_than($value, $number)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value < $number) {
                    return true;
                }
            }
        }
        else {
            return ($value < $number);
        }

        return false;
    }

    /**
     * Check if value is more than number
     *
     * @access public
     * @param mixed $value
     * @param string $number
     * @return bool
     */
    public static function more_than($value, $number)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value > $number) {
                    return true;
                }
            }
        }
        else {
            return ($value > $number);
        }

        return false;
    }

    /**
     * Check if value represents field being checked
     *
     * @access public
     * @param mixed $value
     * @return bool
     */
    public static function is_checked($value)
    {
        if (gettype($value) === 'array') {
            foreach ($value as $single_value) {
                if ($single_value) {
                    return true;
                }
            }
        }
        else if ($value) {
            return true;
        }

        return false;
    }

    /**
     * Get matching order ids
     *
     * @access public
     * @param array $timeframe
     * @param array $args
     * @param array $order_status
     * @return array
     */
    public static function get_matching_order_ids($timeframe, $args, $order_status = null)
    {
        // WC31: Orders will no longer be posts

        // Start configuring query
        $config = array(
            'numberposts'   => -1,
            'post_type'     => 'shop_order',
            'fields'        => 'ids',
        );

        // Get orders by customer user ID
        if ($user_id = self::determine_user_id($args)) {
            $config['meta_key']     = '_customer_user';
            $config['meta_value']   = $user_id;
        }
        // Get orders by customer email
        else if ($email = self::determine_user_email($args)) {
            $config['meta_key']     = '_billing_email';
            $config['meta_value']   = $email;
        }
        // Unable to identify customer
        else {
            return array();
        }

        // Only load orders that are marked processing or completed (i.e. paid)
        $config['post_status'] = ($order_status !== null ? $order_status : array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed'));

        // Check if timeframe was set
        if ($timeframe !== 'all_time') {

            // Check if we have such timeframe
            if (!isset(self::$timeframes[$timeframe])) {
                return false;
            }

            // Get year, month and day
            list($year, $month, $day) = self::get_timeframe_date(self::$timeframes[$timeframe]);

            // Update config
            $config['date_query'] = array(
                'after' => array(
                    'year'  => $year,
                    'month' => $month,
                    'day'   => $day,
                ),
                'inclusive' => true,
            );
        }

        // Get order ids
        $all_order_ids = get_posts($config);

        return ($all_order_ids && !is_wp_error($all_order_ids) && is_array($all_order_ids)) ? $all_order_ids : array();
    }

    /**
     * Get timeframe date
     *
     * @access public
     * @param array $timeframe
     * @return array
     */
    public static function get_timeframe_date($timeframe)
    {
        // Subtract time
        $timestamp = self::get_timeframe_timestamp($timeframe);

        // Return year, month and day
        return array(
            date('Y', $timestamp),
            date('m', $timestamp),
            date('d', $timestamp),
        );
    }

    /**
     * Get timeframe timestamp
     *
     * @access public
     * @param array $timeframe
     * @return int
     */
    public static function get_timeframe_timestamp($timeframe)
    {
        return strtotime('-' . $timeframe['value']);
    }

    /**
     * Check if date is within timeframe
     *
     * @access public
     * @param string $date
     * @param array $timeframe
     * @return bool
     */
    public static function date_is_within_timeframe($date, $timeframe)
    {
        // Get timestamps
        $date_timestamp = strtotime($date);
        $condition_timestamp = self::get_timeframe_timestamp($timeframe);

        // Compare timestamps
        return $date_timestamp >= $condition_timestamp ? true : false;
    }

    /**
     * Process submitted conditions
     *
     * @access public
     * @param array $conditions
     * @param string $context
     * @return mixed
     */
    public static function process_submitted_conditions($conditions = array(), $context = 'trigger')
    {
        // Get condition types and timeframes
        $condition_types = self::conditions($context);
        $timeframes = self::timeframes(true);

        // Store processed conditions
        $current = array();

        if (!empty($conditions) && is_array($conditions)) {

            // Iterate over conditions
            foreach ($conditions as $condition) {

                // Validate and sanitize condition
                if ($processed_condition = self::process_submitted_condition($condition, $condition_types, $timeframes)) {
                    $current[] = $processed_condition;
                }
            }
        }

        return $current;
    }

    /**
     * Process single submitted condition
     *
     * @access public
     * @param array $condition
     * @param array $condition_types
     * @param array $timeframes
     * @return mixed
     */
    public static function process_submitted_condition($condition, $condition_types, $timeframes)
    {
        $current = array();

        // Type
        if (!isset($condition['type'])) {
            return false;
        }

        $current['type'] = $condition['type'];
        $option_key_exists = false;

        foreach ($condition_types as $condition_type) {
            if (isset($condition_type['options'][$current['type']])) {
                $option_key_exists = true;
            }
        }

        if (!$option_key_exists) {
            return false;
        }


        // Method
        $method_key = $current['type'] . '_method';

        if (isset($condition[$method_key])) {

            // Get all condition methods for current condition
            $condition_methods = self::methods($current['type']);

            // Check if selected condition method exists
            if (isset($condition_methods[$condition[$method_key]])) {
                $current[$method_key] = $condition[$method_key];
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }

        // Text
        if (self::uses_field($current['type'], 'text')) {
            if (!empty($condition['text'])) {
                $current['text'] = $condition['text'];
            }
            else {
                $current['text'] = '';
            }
        }

        // Decimal
        if (self::uses_field($current['type'], 'decimal')) {
            if (isset($condition['decimal']) && is_string($condition['decimal'])) {
                $current['decimal'] = preg_replace('/[^0-9.]+/', '', $condition['decimal']);
            }
            else {
                $current['decimal'] = '';
            }
        }

        // Number
        if (self::uses_field($current['type'], 'number')) {
            if (isset($condition['number']) && is_string($condition['number'])) {
                $current['number'] = preg_replace('/[^0-9]+/', '', $condition['number']);
            }
            else {
                $current['number'] = '';
            }
        }

        // Meta key
        if (self::uses_field($current['type'], 'meta_key')) {
            if (isset($condition['meta_key']) && is_string($condition['meta_key'])) {
                $current['meta_key'] = $condition['meta_key'];
            }
            else {
                $current['meta_key'] = '';
            }
        }

        // Timeframe
        if (self::uses_field($current['type'], 'timeframe') || self::uses_field($current['type'], 'timeframe_all_time')) {

            // Check if selected timeframe exists
            if (isset($condition['timeframe']) && isset($timeframes[$condition['timeframe']])) {
                $current['timeframe'] = $condition['timeframe'];
            }
            else {
                return false;
            }
        }

        // Multiselect fields
        foreach (self::$multiselect_field_keys as $multiselect_field) {
            if (self::uses_field($current['type'], $multiselect_field)) {
                if (isset($condition[$multiselect_field])) {
                    $current[$multiselect_field] = (array) $condition[$multiselect_field];
                }
                else {
                    $current[$multiselect_field] = array();
                }
            }
        }

        return !empty($current) ? $current : false;
    }

    /**
     * Get multiselect fields
     *
     * @access public
     * @return array
     */
    public static function get_multiselect_field_keys()
    {
        return self::$multiselect_field_keys;
    }

    /**
     * Compare list of items with list of elements in conditions
     *
     * @access public
     * @param string $method
     * @param array $items
     * @param array $condition_items
     * @param array $condition_items_split
     * @return bool
     */
    public static function compare_at_least_one_all_none($method, $items, $condition_items, $condition_items_split = array())
    {
        // Make sure items was passed as array and does not contain duplicates
        $items = array_unique((array) $items);

        // None
        if ($method === 'none') {
            if (count(array_intersect($items, $condition_items)) == 0) {
                return true;
            }
        }

        // All - regular check
        else if ($method === 'all' && empty($condition_items_split)) {
            if (count(array_intersect($items, $condition_items)) == count($condition_items)) {
                return true;
            }
        }

        // All - special case
        // Check with respect to parent items (e.g. parent categories)
        // This is a special case - we can't simply compare against
        // $condition_items which include child items since this would
        // require for them to also be present in $items
        else if ($method === 'all') {

            // Iterate over all condition items split by parent
            foreach ($condition_items_split as $parent_with_children) {

                // At least one item must match at least one item in parent/children array
                if (count(array_intersect($items, $parent_with_children)) == 0) {
                    return false;
                }
            }

            return true;
        }

        // At least one
        else if (count(array_intersect($items, $condition_items)) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Check if item is in list of items
     *
     * @access public
     * @param string $method
     * @param mixed $items
     * @param array $condition_items
     * @return bool
     */
    public static function compare_in_list_not_in_list($method, $items, $condition_items)
    {
        // Make sure items was passed as array
        $items = (array) $items;

        // Proceed depending on method
        if ($method === 'not_in_list') {
            if (count(array_intersect($items, $condition_items)) == 0) {
                return true;
            }
        }
        else if (count(array_intersect($items, $condition_items)) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Text comparison
     *
     * @access public
     * @param string $method
     * @return bool
     */
    public static function compare_text_comparison($method, $text, $condition_text)
    {
        // Text must be set, otherwise there's nothing to compare against
        if (empty($text)) {
            return false;
        }

        // No text set in conditions
        if (empty($condition_text)) {
            return in_array($method, array('equals', 'does_not_contain')) ? false : true;
        }

        // Proceed depending on condition method
        switch ($method) {

            // Equals
            case 'equals':
                return self::equals($text, $condition_text);

            // Does Not Equal
            case 'does_not_equal':
                return !self::equals($text, $condition_text);

            // Contains
            case 'contains':
                return self::contains($text, $condition_text);

            // Does Not Contain
            case 'does_not_contain':
                return !self::contains($text, $condition_text);

            // Begins with
            case 'begins_with':
                return self::begins_with($text, $condition_text);

            // Ends with
            case 'ends_with':
                return self::ends_with($text, $condition_text);

            default:
                return true;
        }
    }

    /**
     * Compare numbar with another number
     *
     * @access public
     * @param string $method
     * @param int $number
     * @param int $condition_number
     * @return bool
     */
    public static function compare_at_least_less_than($method, $number, $condition_number)
    {
        if ($method === 'less_than') {
            if ($number < $condition_number) {
                return true;
            }
        }
        else if ($number >= $condition_number) {
            return true;
        }

        return false;
    }

    /**
     * Get array of product property IDs
     *
     * @access public
     * @param string $property
     * @param object $order
     * @return array
     */
    public static function get_order_product_property_ids($property, $order)
    {
        $ids = array();

        // Get order items
        $order_items = $order->get_items();

        // Iterate over order items
        foreach ($order_items as $item) {

            // Get list of current item property IDs
            $method = 'get_order_product_' . (empty($property) ? '' : $property . '_') . 'ids';
            $current_ids = self::$method($item);

            // Add to list of all IDs
            if (!empty($current_ids)) {
                $ids = array_merge($ids, $current_ids);
            }
        }

        return array_unique($ids);
    }

    /**
     * Get product ID from order item
     *
     * @access public
     * @param array $item
     * @return int
     */
    public static function get_order_product_ids($item)
    {
        if ($product_id = RightPress_WC_Legacy::order_item_get_product_id($item)) {
            return (array) $product_id;
        }

        return null;
    }

    /**
     * Get list of product category IDs from order item
     *
     * @access public
     * @param array $item
     * @param int $product_id
     * @return array
     */
    public static function get_order_product_category_ids($item = null, $product_id = null)
    {
        $ids = array();

        // Get product id
        $product_id = ($product_id !== null ? $product_id : RightPress_WC_Legacy::order_item_get_product_id($item));

        // Check if product ID is set
        if ($product_id) {

            // Get category post terms
            // WC31: Product categories may no longer be post terms
            $item_categories = wp_get_post_terms($product_id, 'product_cat');

            // Iterate over product categories and add categories that do not exist yet
            foreach ($item_categories as $category) {
                if (!in_array($category->term_id, $ids)) {
                    $ids[] = $category->term_id;
                }
            }
        }

        return $ids;
    }

    /**
     * Get list of product attribute IDs from order item
     *
     * @access public
     * @param array $item
     * @param int $product_id
     * @param array $item_meta
     * @return array
     */
    public static function get_order_product_attribute_ids($item = null, $product_id = null, $item_meta = null)
    {
        $ids = array();

        // Get product id
        $product_id = ($product_id !== null ? $product_id : RightPress_WC_Legacy::order_item_get_product_id($item));

        // Check if product ID is set
        if ($product_id) {

            // WC31: Check if $item['item_meta'] still works correctly
            $item_meta = ($item_meta !== null ? $item_meta : RightPress_Helper::unwrap_post_meta($item['item_meta']));

            // Get selected attributes
            $selected = array();

            // Iterate over item meta
            if (is_array($item_meta) && !empty($item_meta)) {
                foreach ($item_meta as $meta_key => $meta_value) {
                    if (RightPress_Helper::string_begins_with_substring($meta_key, 'pa_')) {
                        $selected[$meta_key] = $meta_value;
                    }
                }
            }

            // Get attribute ids and return
            $ids = RightPress_Helper::get_wc_product_attribute_ids($product_id, $selected);
        }

        return $ids;
    }

    /**
     * Get list of product tag IDs from order item
     *
     * @access public
     * @param array $item
     * @param int $product_id
     * @return array
     */
    public static function get_order_product_tag_ids($item = null, $product_id = null)
    {
        $ids = array();

        // Get product id
        $product_id = ($product_id !== null ? $product_id : RightPress_WC_Legacy::order_item_get_product_id($item));

        // Check product id
        if ($product_id) {
            $ids = RightPress_Helper::get_wc_product_tag_ids($product_id);
        }

        return $ids;
    }

    /**
     * Get parent and all child product category ids
     *
     * @access public
     * @param array $condition
     * @return array
     */
    public static function get_product_category_ids_with_children($condition)
    {
        $all_category_ids = array();

        // Check if categories are set
        if (!empty($condition['product_categories']) && is_array($condition['product_categories'])) {

            // Iterate over categories
            foreach ($condition['product_categories'] as $category_id) {

                // Add parent/children to main array
                $all_category_ids[$category_id] = RightPress_Helper::get_term_with_children($category_id, 'product_cat');
            }
        }

        return $all_category_ids;
    }

    /**
     * Get parent and all child product attribute ids
     *
     * @access public
     * @param array $condition
     * @return array
     */
    public static function get_product_attribute_ids_with_children($condition)
    {
        global $wpdb;
        $all_attribute_ids = array();

        // WC31: Product attributes may no longer be post terms

        // Check if attributes are set
        if (!empty($condition['attributes']) && is_array($condition['attributes'])) {

            // Iterate over attributes
            foreach ($condition['attributes'] as $attribute_id) {

                // Determine taxonomy of this attribute
                $taxonomy = $wpdb->get_var('SELECT taxonomy FROM ' . $wpdb->term_taxonomy . ' WHERE term_id = ' . absint($attribute_id));

                // No taxonomy?
                if (!is_string($taxonomy) && !empty($taxonomy)) {
                    continue;
                }

                // Add parent/children to main array
                $all_attribute_ids[$attribute_id] = RightPress_Helper::get_term_with_children($attribute_id, $taxonomy);
            }
        }

        return $all_attribute_ids;
    }

    /**
     * Merge all child taxonomy terms from a list split by parent
     *
     * @access public
     * @param array $items_split
     * @return array
     */
    public static function merge_all_children($items_split)
    {
        $items = array();

        // Iterate over parents
        foreach ($items_split as $parent_id => $children) {

            // Add parent to children array
            $children[] = (int) $parent_id;

           // Add unique parent/children to main array
            $items = array_unique(array_merge($items, $children));
        }

        return $items;
    }

    /**
     * Determine customer user ID from args and check if such user exists
     *
     * @access public
     * @param array $args
     * @return mixed
     */
    public static function determine_user_id($args = array())
    {
        // Get user ID from order
        if (isset($args['order']) && is_object($args['order'])) {

            $customer_id = RightPress_WC_Legacy::order_get_customer_id($args['order']);

            if (!empty($customer_id) && get_userdata($customer_id)) {
                return $customer_id;
            }
        }

        // Get user ID from args array
        if (!empty($args['customer_id']) && is_numeric($args['customer_id']) && get_userdata($args['customer_id'])) {
            return $args['customer_id'];
        }

        // Get user from user object
        if (!empty($args['user']) && is_numeric($args['user']->data->ID) && get_userdata($args['user']->data->ID)) {
            return $args['user']->data->ID;
        }

        return false;
    }

    /**
     * Determine customer email from args
     *
     * @access public
     * @param array $args
     * @return mixed
     */
    public static function determine_user_email($args = array())
    {
        // Check order object
        if (isset($args['order']) && is_a($args['order'], 'WC_Order')) {

            // Get customer email from order
            $billing_email = RightPress_WC_Legacy::order_get_billing_email($args['order']);

            // Return valid customer email
            if (!empty($billing_email) && filter_var($billing_email, FILTER_VALIDATE_EMAIL)) {
                return $billing_email;
            }
        }

        return false;
    }

    /**
     * Get last customer not trashed order ID
     *
     * @access public
     * @param array $args
     * @param array $post_status
     * @return mixed
     */
    public static function get_last_customer_order_id($args, $post_status = null)
    {
        // WC31: Orders will no longer be posts

        // Get post status list
        $post_status = ($post_status !== null ? $post_status : array(array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed')));

        // Configure query
        $config = array(
            'numberposts'   => 1,
            'post_type'     => 'shop_order',
            'post_status'   => $post_status,
            'fields'        => 'ids',
        );

        // Get order by customer user ID
        if ($user_id = self::determine_user_id($args)) {
            $config['meta_key']     = '_customer_user';
            $config['meta_value']   = $user_id;
        }
        // Get order by customer email
        else if ($email = self::determine_user_email($args)) {
            $config['meta_key']     = '_billing_email';
            $config['meta_value']   = $email;
        }
        // Unable to identify customer
        else {
            return false;
        }

        // Run query
        $all_order_ids = get_posts($config);

        // Get last order ID and return
        return (!empty($all_order_ids) && is_array($all_order_ids)) ? array_shift($all_order_ids) : false;
    }

    /**
     * Get last active (not cancelled, failed or refunded) customer order ID
     *
     * @access public
     * @param array $args
     * @return mixed
     */
    public static function get_last_active_customer_order_id($args)
    {
        return RP_WCEC_Conditions::get_last_customer_order_id($args, array('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed'));
    }

    /**
     * Get last paid (processing or completed) customer order ID
     *
     * @access public
     * @param array $args
     * @return mixed
     */
    public static function get_last_paid_customer_order_id($args)
    {
        return RP_WCEC_Conditions::get_last_customer_order_id($args, RP_WCEC::get_wc_order_paid_statuses(true));
    }






}

new RP_WCEC_Conditions();

}
