<?php
/**
 * Description tab
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version		3.3.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $post;

$heading = esc_html( apply_filters( 'woocommerce_product_description_heading', esc_html__( 'Description', 'woocommerce' ) ) );
?>
<?php if(get_field('subtitle')): ?>
<h2 style="margin: 0px; font-size: 28px;"><?php the_title(); ?></h2>
<h3><?php the_field('subtitle'); ?></h3>
<?php else : ?>
<h2 style="font-size: 28px;"><?php the_title(); ?></h2>
<?php endif ?>
<div class="post-content">
<?php the_content(); ?>
</div>
