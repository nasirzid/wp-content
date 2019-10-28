<?php
/**
 * View: Week View Mobile Events Day
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/mobile-events/day.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var array $day Array of data of the day
 *
 * @version 4.7.6
 *
 */

$hidden      = 'true';
$day_classes = [ 'tribe-events-pro-week-mobile-events__day' ];

if ( $day[ 'is_active' ] ) {
	$hidden        = 'false';
	$day_classes[] = 'tribe-events-pro-week-mobile-events__day--active';
}
?>
<div
	class="<?php echo esc_attr( implode( ' ', $day_classes ) ); ?>"
	id="tribe-events-pro-week-mobile-events-day-<?php echo esc_attr( $day[ 'datetime' ] ); ?>"
	aria-hidden="<?php echo esc_attr( $hidden ); ?>"
>

	<?php
	/**
	 * @todo: Written by Paul.
	 *        Hook this up with the following logic (or similar).
	 *        Luca or Gustavo.
	 */
	// foreach ( $day[ 'event_times' ] as $event_time ) {
	// 	$this->template( 'week/mobile-events/day/time-separator', [ 'time' => $event_time[ 'time' ], 'datetime' => $event_time[ 'datetime' ] ] );

	// 	foreach( $event_time[ 'events' ] as $event ) {
	// 		$this->template( 'week/mobile-events/day/event', [ 'event' => $event ] );
	// 	}
	// }
	?>

	<?php
	/**
	 * @todo: Written by Paul.
	 *        The code below is for visual purposes only. Use logic above when hooking in data.
	 *        When hooking up dynamic data, the stuff below can be scrapped.
	 *        Luca or Gustavo.
	 */
	?>
	<?php $this->template( 'week/mobile-events/day/time-separator' ); ?>

	<?php foreach ( $events as $event ) : ?>

		<?php $this->template( 'week/mobile-events/day/event', [ 'event' => $event ] ); ?>

	<?php endforeach; ?>

</div>
