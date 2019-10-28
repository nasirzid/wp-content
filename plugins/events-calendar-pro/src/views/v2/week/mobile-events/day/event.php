<?php
/**
 * View: Week View Mobile Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/mobile-events/day/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7.5
 *
 */

$event    = $this->get( 'event' );
$event_id = $event->ID;

$classes = [ 'tribe-common-g-row', 'tribe-common-g-row--gutters', 'tribe-events-pro-week-mobile-events__event' ];

if ( tribe( 'tec.featured_events' )->is_featured( $event_id ) ) {
	$classes[] = 'tribe-events-pro-week-mobile-events__event--featured';
}

?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

	<?php $this->template( 'week/mobile-events/day/event/featured-image', [ 'event' => $event ] ); ?>

	<div class="tribe-events-pro-week-mobile-events__event-details tribe-common-g-col">

		<?php $this->template( 'week/mobile-events/day/event/date', [ 'event' => $event ] ); ?>
		<?php $this->template( 'week/mobile-events/day/event/title', [ 'event' => $event ] ); ?>
		<?php $this->template( 'week/mobile-events/day/event/venue', [ 'event' => $event ] ); ?>

	</div>

</article>
