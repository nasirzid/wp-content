<?php
/**
 * View: Week View - Event Tooltip
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events/views/v2/week/grid-body/events-day/event/tooltip.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7.6
 *
 */
$event    = $this->get( 'event' );
$event_id = $event->ID;
?>
<div
	class="tribe-events-pro-week-grid__event-tooltip"
	data-js="tribe-events-tooltip-content"
	role="tooltip"
>
	<div id="tribe-events-tooltip-content-<?php echo esc_attr( $event_id ); ?>">
		<?php $this->template( 'week/grid-body/events-day/event/tooltip/featured-image', [ 'event' => $event ] ); ?>
		<?php $this->template( 'week/grid-body/events-day/event/tooltip/description', [ 'event' => $event ] ); ?>
		<?php $this->template( 'week/grid-body/events-day/event/tooltip/cta', [ 'event' => $event ] ); ?>
	</div>
</div>
