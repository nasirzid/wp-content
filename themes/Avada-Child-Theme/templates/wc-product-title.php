<?php
/**
 * The product title.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       http://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.1.0
 */

?>
<h3 class="product-title">
	<a href="<?php echo esc_url_raw( get_the_permalink() ); ?>">
		<?php echo get_the_title(); ?>
	</a>
</h3>
<?php

	$subtitle = get_post_meta(get_the_ID(),'subtitle', true);
	
	echo '<h4 class="product_subtitle">'.$subtitle.'</h4>';
	

?>
<div class="fusion-price-rating">
