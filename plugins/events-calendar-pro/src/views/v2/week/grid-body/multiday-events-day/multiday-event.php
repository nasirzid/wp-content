<?php
/**
 * View: Week View - Multiday Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/grid-body/multiday-events-day/multiday-event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

$event    = $this->get( 'event' );
$event_id = $event->ID;

$classes = [ 'tribe-events-pro-week-grid__multiday-event' ];

/**
 * @todo: fix logic once dynamic logic is hooked up
 */
if ( isset( $event->featured ) && $event->featured ) {
	$classes[] = 'tribe-events-pro-week-grid__multiday-event--featured';
}

if ( isset( $event->is_past ) && $event->is_past ) {
	$classes[] = 'tribe-events-pro-week-grid__multiday-event--past';
}

if ( $event->should_display ) {
	$classes[] = 'tribe-events-pro-week-grid__multiday-event--width-' . $event->duration;

	if ( isset( $event->start_this_week ) && $event->start_this_week ) {
		$classes[] = 'tribe-events-pro-week-grid__multiday-event--start';
	}

	if ( isset( $event->end_this_week ) && $event->end_this_week ) {
		$classes[] = 'tribe-events-pro-week-grid__multiday-event--end';
	}
} else {
	$classes[] = 'tribe-events-pro-week-grid__multiday-event--hidden';
}
?>
<div class="tribe-events-pro-week-grid__multiday-event-wrapper">

	<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-event-id="<?php echo esc_attr( $event_id ); ?>">
		<time datetime="<?php echo esc_attr( $event->datetime ); ?>" class="tribe-common-a11y-visual-hide">
			<?php echo esc_html( $event->datetime_text ); ?>
		</time>
		<a href="<?php echo esc_attr( $event->link ); ?>" class="tribe-events-pro-week-grid__multiday-event-inner">
			<?php if ( $event->featured ) : ?>
				<em
					class="tribe-events-pro-week-grid__multiday-event-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
					aria-label="<?php esc_attr_e( 'Featured', 'tribe-events-calendar-pro' ); ?>"
					title="<?php esc_attr_e( 'Featured', 'tribe-events-calendar-pro' ); ?>"
				>
				</em>
			<?php endif; ?>
			<h3 class="tribe-events-pro-week-grid__multiday-event-title tribe-common-h8 tribe-common-h--alt">
				<?php echo esc_html( $event->title ); ?>
			</h3>
		</a>
	</article>

</div>
