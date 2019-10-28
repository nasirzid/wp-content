<?php
/**
 * View: Week View - Multiday Events Row Header
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/grid-body/multiday-events-row-header.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var bool $multiday_display_toggle bool containing if we should display the multiday toggle.
 *
 */

?>
<div class="tribe-events-pro-week-grid__multiday-events-row-header" role="rowheader">
	<span class="tribe-events-pro-week-grid__multiday-events-tag">
		<?php esc_html_e( 'All Day', 'tribe-events-calendar-pro' ); ?>
	</span>

	<?php if ( $multiday_display_toggle ) : ?>
		<?php $this->template( 'week/grid-body/multiday-events-row-header/multiday-events-row-header-toggle' ); ?>
	<?php endif; ?>
</div>
