<?php
/**
 * View: Week View - Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/grid-body/events-day/event.php
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

$classes = [ 'tribe-events-pro-week-grid__event' ];

if ( isset( $event->featured ) && $event->featured ) {
	$classes[] = 'tribe-events-pro-week-grid__event--featured';
}

// @be: replace the following with template tags or event properties
$start_date   = isset( $event->EventStartDate ) ? $event->EventStartDate : '2019-07-26 06:00:00';
$end_date     = isset( $event->EventEndDate ) ? $event->EventEndDate : '2019-07-26 09:00:00';

// Set positioning
// @be: This is the vertical positioning:
// - the format is: tribe-events-pro-week-grid__event--t-HH and adding -5 if it's half an hour.
// - the star hour goes from `0-5` to `23-5` - if the events starts at 00 then the default class takes care of it.
// examples:
// - 'tribe-events-pro-week-grid__event--t-14 is for an event starting at 2PM.
// - 'tribe-events-pro-week-grid__event--t-6-5 is for an event starting at 6:30AM.
// With the Gutenberg implementations users can set the start time to the minute.
// - We'll be rounding in the following way:
// - event starting 15:00 to 15:14 -> vertical aligned to 15:00
// - event starting 15:16 to 15:44 -> Vertically aligned to 15:30
// - starting 15:46 to 15:59 -> 16:00
$start_hour     = date( 'G', strtotime( $start_date ) );
$start_minutes  = date( 'i', strtotime( $start_date ) );
$start_mid_hour = ( 15 <= $start_minutes && 45 > $start_minutes ) ? '-5' : '';

// Special case for events starting from `hh:46` to `hh:59`.
$start_hour = ( 45 <= $start_minutes ) ? $start_hour++ : $start_hour;

$classes[] = 'tribe-events-pro-week-grid__event--t-' . $start_hour . $start_mid_hour;

// Set duration
// @be: This is the event duration(height):
// - the format is: tribe-events-pro-week-grid__event--h--HH and adding -5 if it's half an hour.
// - the min duration is half an hour, so if that's the case you don't need to add anything.
// - if the duration is more than half an hour, the modifier classes go from `-1` to `-23-5`.
// examples:
// - 'tribe-events-pro-week-grid__event--h-5 is for an event with a duration of 5 hours.
// - 'tribe-events-pro-week-grid__event--h-1-5 is for an event with a duration of 1 and a half hours.
// After Gutenberg, the duration can vary as they can insert the time to the minute
// We'll be rounding that number on those cases.
// @todo: @be : calculate this.
$duration = abs( strtotime( $end_date ) - strtotime( $start_date ) ) / 3600;

if ( is_float( $duration ) ) {

	$duration = round( $duration, 1 );
	$duration_hours = explode( '.', $duration )[0];
	$duration_minutes = explode( '.', $duration )[1];

	// duration minutes goes from 1 to 9
	if ( 3 > $duration_minutes ) {
		$duration_minutes = '0';
	} elseif ( 3 <= $duration_minutes && 7 > $duration_minutes ) {
		$duration_minutes = '5';
	} else {
		$duration_hours++;
		$duration_minutes = '0';
	}

	$duration_string = $duration_hours;
	$duration_string .= ( 0 != $duration_minutes ) ? '-' . $duration_minutes : '';

} else {
	// it's an int
	$duration_string = $duration;
}

// Only add classes if the duration is higher or equal to 0.7 (point in which we round to the hour)
$classes[] = ( 0.7 <= $duration ) ? 'tribe-events-pro-week-grid__event--h-' . $duration_string : '';

?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-event-id="<?php echo esc_attr( $event_id ); ?>">
	<a
		href="#"
		class="tribe-events-pro-week-grid__event-link"
		data-js="tribe-events-tooltip"
		data-tooltip-content="#tribe-events-tooltip-content-<?php echo esc_attr( $event_id ); ?>"
		aria-describedby="tribe-events-tooltip-content-<?php echo esc_attr( $event_id ); ?>"
	>
		<div class="tribe-events-pro-week-grid__event-link-inner">

			<?php $this->template( 'week/grid-body/events-day/event/date', [ 'event' => $event ] ); ?>
			<?php $this->template( 'week/grid-body/events-day/event/title', [ 'event' => $event ] ); ?>

		</div>
	</a>
</article>

<?php $this->template( 'week/grid-body/events-day/event/tooltip', [ 'event' => $event ] ); ?>
