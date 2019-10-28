<?php
/**
 * Track Listing tab
 *
 * @author 		Ignitus Marketing Arts
 * @package 	WooCommerce/Templates
 * @version	 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce, $post;

$heading = esc_html( apply_filters( 'woocommerce_product_description_heading', esc_html__( 'Description', 'woocommerce' ) ) );
?>

<div class="post-content">
<?php
// check for rows (parent repeater)
if( have_rows('tracklisting_disc') ): ?>
	<?php
    // loop through rows (parent repeater)
	while( have_rows('tracklisting_disc') ): the_row(); ?>
<strong><?php the_sub_field('tracklisting_disctitle'); ?></strong>
	<?php
    // check for rows (sub repeater)
	if( have_rows('tracklisting_track') ): ?>
	<ol class="tracklisting">
	<?php
    // loop through rows (sub repeater)
	while( have_rows('tracklisting_track') ): the_row();
	// display each item as a list - with a class of completed ( if completed )
	?>
    <li><?php the_sub_field('tracklisting_composer'); ?>: <?php if(get_sub_field('link_to_library_record')): ?><a href="<?php the_sub_field('link_to_library_record'); ?>" target="_blank"><?php the_sub_field('tracklisting_title'); ?></a><?php else : ?><?php the_sub_field('tracklisting_title'); ?><?php endif ?> (<?php the_sub_field('tracklisting_time'); ?>)<span class="trackperformers"><?php the_sub_field('tracklisting_performers'); ?></span></li>
	<?php endwhile; ?>
    </ol>
	<?php endif; //if( get_sub_field('tracklisting_track') ): ?>
	<?php endwhile; // while( has_sub_field('tracklisting_disc') ): ?>
<?php endif; // if( get_field('tracklisting_disc') ): ?>


    
</div>
