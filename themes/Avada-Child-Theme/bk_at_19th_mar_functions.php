<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'avada-stylesheet' ) );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

/**
* Hides the product's weight and dimension in the single product page.
*/
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

/**
* Replaces product placeholder image.
*/
add_action( 'init', 'custom_fix_thumbnail' );
 
function custom_fix_thumbnail() {
  add_filter('woocommerce_placeholder_img_src', 'custom_woocommerce_placeholder_img_src');
   
	function custom_woocommerce_placeholder_img_src( $src ) {
	$upload_dir = wp_upload_dir();
	$uploads = untrailingslashit( $upload_dir['baseurl'] );
	$src = $uploads . '/2017/06/sheet-music-global.jpg';
	 
	return $src;
	}
}

/*
 * Hide end time in list, map, photo, and single event view
 * NOTE: This will only hide the end time for events that end on the same day
 */
function tribe_remove_end_time_single( $formatting_details ) {
	$formatting_details['show_end_time'] = 0;
	return $formatting_details;
}
add_filter( 'tribe_events_event_schedule_details_formatting', 'tribe_remove_end_time_single', 10, 2);
/*
 * Hide end time in Week and Month View Tooltips
 * NOTE: This will hide the end time in tooltips for ALL events, not just events that end on the same day
 */
function tribe_remove_end_time_tooltips( $json_array, $event, $additional ) {
	$json_array['endTime'] = '';
	return $json_array;
}
add_filter( 'tribe_events_template_data_array', 'tribe_remove_end_time_tooltips', 10, 3 );
/*
 * Hide endtime for multiday events
 * Note: You will need to uncomment this for it to work
 */
function tribe_remove_endtime_multiday ( $inner, $event ) {
	if ( tribe_event_is_multiday( $event ) && ! tribe_event_is_all_day( $event ) ) {
		$format                   = tribe_get_date_format( true );
		$time_format              = get_option( 'time_format' );
		$format2ndday             = apply_filters( 'tribe_format_second_date_in_range', $format, $event );
		$datetime_separator       = tribe_get_option( 'dateTimeSeparator', ' @ ' );
		$time_range_separator     = tribe_get_option( 'timeRangeSeparator', ' - ' );
		$microformatStartFormat   = tribe_get_start_date( $event, false, 'Y-m-dTh:i' );
		$microformatEndFormat     = tribe_get_end_date( $event, false, 'Y-m-dTh:i' );
		$inner = '<span class="date-start dtstart">';
		$inner .= tribe_get_start_date( $event, false, $format ) . $datetime_separator . tribe_get_start_date( $event, false, $time_format );
		$inner .= '<span class="value-title" title="' . $microformatStartFormat . '"></span>';
		$inner .= '</span>' . $time_range_separator;
		$inner .= '<span class="date-end dtend">';
		$inner .= tribe_get_end_date( $event, false, $format2ndday );
		$inner .= '<span class="value-title" title="' . $microformatEndFormat . '"></span>';
		$inner .= '</span>';
	}
	return $inner;
}
//add_filter( 'tribe_events_event_schedule_details_inner', 'tribe_remove_endtime_multiday', 10, 3 );