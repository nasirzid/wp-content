<?php
/**
 * Product Details tab
 *
 * @author 		Ignitus Marketing Arts
 * @package 	WooCommerce/Templates
 * @version	 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

$heading = esc_html( apply_filters( 'woocommerce_product_additional_information_heading', __( 'Additional information', 'woocommerce' ) ) );

?>
<?php if(get_field('subtitle')): ?>
<h2 style="margin: 0px;"><?php the_title(); ?></h2>
<h3><?php the_field('subtitle'); ?></h3>
<?php else : ?>
<h2><?php the_title(); ?></h2>
<?php endif ?>
<?php do_action( 'woocommerce_product_additional_information', $product ); ?>
<strong>SKU:</strong> <?php echo $product->get_sku(); ?><br />
<?php
if(get_field('call_number'))
{
	echo '<strong>Call Number:</strong> ' . get_field('call_number') . '<br />';
}
?>
<?php
if(get_field('media_type'))
{
	echo '<strong>Format:</strong> ' . get_field('media_type') . '<br />';
}
?>
<?php
if(get_field('number_discs'))
{
	echo '<strong>Number of Discs:</strong> ' . get_field('number_discs') . '<br />';
}
?>
<?php
if(get_field('year_of_release'))
{
	echo '<strong>Release Date:</strong> ' . get_field('year_of_release') . '<br />';
}
?>
<?php
if(get_field('year_of_composition'))
{
	echo '<strong>Composition Date:</strong> ' . get_field('year_of_composition') . '<br />';
}
?>
<?php
if(get_field('duration'))
{
	echo '<strong>Duration:</strong> ' . get_field('duration') . '<br />';
}
?>
<?php
if(get_field('instrumentation'))
{
	echo '<strong>Instrumentation:</strong> ' . get_field('instrumentation') . '<br />';
}
?>
<?php
if(get_field('label'))
{
	echo '<strong>Label:</strong> ' . get_field('label') . '<br />';
}
?>
<?php
if(get_field('publisher'))
{
	echo '<strong>Publisher:</strong> ' . get_field('publisher') . '<br />';
}
?>
<?php
if(get_field('library_record_url'))
{
	//echo '<div style="margin-top: 10px;" class="fusion-clearfix"></div><a class="fusion-read-more-button fusion-content-box-button fusion-button button-default button-large button-round button-flat" href="' . get_field('library_record_url') . '" target="_blank">View Library Record</a>';
	echo '<div style="margin-top: 10px;" class="fusion-clearfix"></div><a class="fusion-read-more-button fusion-content-box-button fusion-button button-default button-large button-round button-flat" href="' . site_url('/music-library-redirect') . '" target="_blank">View Library Record</a>';
}
?>