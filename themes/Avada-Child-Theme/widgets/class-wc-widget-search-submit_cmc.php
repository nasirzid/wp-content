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
class WC_Widget_Search_Submit_CMC extends WC_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce widget_product_search';
		$this->widget_description = __( 'CMC: custom submit button.', 'woocommerce' );
		$this->widget_id          = 'woocommerce_search_submit_cmc';
		$this->widget_name        = __( 'CMC: Search Submit', 'woocommerce' );
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
		global $wp_query;
		//print_r($wp_query->queried_object);
		/*$term_id =  get_queried_object()->term_id;
		echo $term_id;*/
		$action = '/shop/';
		$if_term_id = get_queried_object()->term_id;
		if($if_term_id > 0){
			$term = get_queried_object();	
			$action = '/product-category/sheet-music/'.$term->slug;
		}
		
		echo '<div style="text-align: center;">
			<form action="'.$action.'" class="cmc_product_search_form">
			<button class="fusion-button button-flat fusion-button-round button-large button-default button-5">Submit Search</button>	';
				if( count($_GET)>0 ) {
					foreach($_GET as $k => $val){
						if($k != 'filter_s') {
							?>	
							<input type="hidden" name="<?php echo $k ?>" value="<?php echo $val; ?>">
							<?php
						}
					}
				}
				$keyword = isset($_GET['filter_s']) ? wc_clean($_GET['filter_s']) : '';
				echo '<input type="hidden" name="filter_s" value="'.$keyword.'" class="filter_s_keyword">';
		echo '	</form>
		</div>';

		$this->widget_end( $args );
	}
}
