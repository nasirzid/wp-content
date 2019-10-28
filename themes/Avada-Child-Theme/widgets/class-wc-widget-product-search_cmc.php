<?php
/**
 * Product Search Widget.
 *
 * @package WooCommerce/Widgets
 * @version 2.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Widget product search class.
 */
class WC_Widget_Product_Search_CMC extends WC_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce widget_product_search';
		$this->widget_description = __( 'A search form for your store.', 'woocommerce' );
		$this->widget_id          = 'woocommerce_product_search_cmc';
		$this->widget_name        = __( 'CMC: Product Search', 'woocommerce' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Title', 'woocommerce' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args     Arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		$this->widget_start( $args, $instance );

		$keyword = isset($_GET['filter_s']) ? wc_clean($_GET['filter_s']) : '';
    	echo '<input type="text" name="filter_s" class="s cmc_product_search" placeholder="Search..." value="'.$keyword.'"/>';

		$this->widget_end( $args );
	}
}
