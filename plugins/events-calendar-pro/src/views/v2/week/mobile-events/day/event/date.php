<?php
/**
 * View: Week View - Mobile Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/mobile-events/day/event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7.5
 *
 */

$event       = $this->get( 'event' );
$event_id    = $event->ID;
$is_featured = tribe( 'tec.featured_events' )->is_featured( $event_id );

?>
<div class="tribe-events-pro-week-mobile-events__event-datetime-wrapper">
	<time class="tribe-events-pro-week-mobile-events__event-datetime tribe-common-b2" datetime="1970-01-01T00:00:00+00:00">
		<?php echo tribe_events_event_schedule_details( $event ); ?>
	</time>
	<?php if ( $is_featured ) : ?>
		<em
			class="tribe-events-pro-week-mobile-events__event-datetime-featured-icon tribe-common-svgicon tribe-common-svgicon--featured"
			aria-label="<?php esc_attr_e( 'Featured', 'tribe-events-calendar-pro' ); ?>"
			title="<?php esc_attr_e( 'Featured', 'tribe-events-calendar-pro' ); ?>"
		>
		</em>
	<?php endif; ?>
</div>
