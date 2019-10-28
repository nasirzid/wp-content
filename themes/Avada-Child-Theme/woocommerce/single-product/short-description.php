<?php
/**
 * Single product short description
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version	 	3.3.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;
/**
if ( ! $post->post_excerpt ) return;
*/
?>
<div class="post-content" itemprop="description">
<?php if(get_field('digital_downloads_google_play'))
	{
	echo '<a href="' . get_field('digital_downloads_google_play') . '" target="_blank"><img src="http://cmccanada.org/wp-content/uploads/2017/06/google-play-badge.png" title="Get it on Google Play" style="margin-right: 10px;"></a>';
	}
?>
<?php if(get_field('digital_downloads_itunes'))
	{
	echo '<a href="' . get_field('digital_downloads_itunes') . '" target="_blank"><img src="http://cmccanada.org/wp-content/uploads/2017/06/itunes-badge.png" title="Get it on iTunes" style="margin-right: 10px;"></a>';
	}
?>
<?php if(get_field('digital_downloads_amazon'))
	{
	echo '<a href="' . get_field('digital_downloads_amazon') . '" target="_blank"><img src="http://cmccanada.org/wp-content/uploads/2017/06/amazon-badge.png" title="Available at Amazon"></a>';
	}
?>
</div>