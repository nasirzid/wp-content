<?php
/**
 * View: Week View - Multiday Events Row Header Toggle Button
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/grid-body/multiday-events-row-header/multiday-events-row-header-toggle.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

/**
 * @todo: @be: the `aria-controls` attribute must include the IDs of the div containing the overflow events for the days that have more than 3 events (3 should be a variable later on). We're hardcoding that to have the IDs of the demo data now.
 *
 */

?>
<button
	class="tribe-events-pro-week-grid__multiday-toggle-button"
	aria-controls="tribe-events-pro-multiday-toggle-day-3 tribe-events-pro-multiday-toggle-day-4"
	aria-expanded="false"
	aria-selected="false"
	data-js="tribe-events-pro-week-multiday-toggle-button"
>
	<span class="tribe-common-a11y-visual-hide">
		<?php esc_html_e( 'Toggle multiday events', 'tribe-events-calendar-pro' ); ?>
	</span>
</button>
