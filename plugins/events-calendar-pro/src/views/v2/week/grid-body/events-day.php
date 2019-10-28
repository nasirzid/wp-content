<?php
/**
 * View: Week View - Events Day
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/grid-body/events-day.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7.5
 *
 */
?>
<div class="tribe-events-pro-week-grid__events-day" role="gridcell">
	<?php
	/*
	for each event, print it out
	*/
	?>
	<?php
		// @todo: Populate this with the events and add the logic to position them.
		$event = [
			'title' => 'I\'m an event',
			'ID' => 2,
			'recurring' => true,
		]
	?>
	<?php $this->template( 'week/grid-body/events-day/event', [ 'event' => (object) $event ] ); ?>

</div>
