<?php
/**
 * View: Week View - Multiday Events - More Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/grid-body/multiday-events-day/more-events.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

/**
 * @todo: @be: Calculate the "2" more.
 * This should be the difference of the number of events (multiday + all day) for a given day, minus the limit in which we start showing the toggle. We're using 3 as this number for now. But this would come from a variable later on.
 * @todo: @be: the `aria-controls` attribute must include the IDs of the div containing the overflow events for the days that have more than 3 events (3 should be a variable later on). We're hardcoding that to have the IDs of the demo data now.
*/
?>
<div class="tribe-events-pro-week-grid__multiday-more-events" data-js="tribe-events-pro-week-multiday-more-events-wrapper">
	<button
		class="tribe-events-pro-week-grid__multiday-more-events-button tribe-common-h8 tribe-common-h--alt tribe-common-anchor-thin"
		data-js="tribe-events-pro-week-multiday-more-events"
		aria-controls="tribe-events-pro-multiday-toggle-day-3 tribe-events-pro-multiday-toggle-day-4"
		aria-expanded="false"
		aria-selected="false"
	>+ 2 More</button>
</div>
