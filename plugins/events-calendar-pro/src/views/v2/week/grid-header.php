<?php
/**
 * View: Week View - Grid Header
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/grid-header.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7.5
 *
 */

/**
 * @todo: replace with actual data, this is just a placeholder
 */
$days_of_week = [
	[
		'weekday'   => 'Sun',
		'daynum'    => 22,
		'full_date' => 'Sunday July 22, 2019',
		'datetime'  => '2019-07-22',
	], [
		'weekday'   => 'Mon',
		'daynum'    => 23,
		'full_date' => 'Monday July 23, 2019',
		'datetime'  => '2019-07-23',
	], [
		'weekday'   => 'Tue',
		'daynum'    => 24,
		'full_date' => 'Tuesday July 24, 2019',
		'datetime'  => '2019-07-24',
	], [
		'weekday'   => 'Wed',
		'daynum'    => 25,
		'full_date' => 'Wednesday July 25, 2019',
		'datetime'  => '2019-07-25',
	], [
		'weekday'   => 'Thu',
		'daynum'    => 26,
		'full_date' => 'Thursday July 26, 2019',
		'datetime'  => '2019-07-26',
	], [
		'weekday'   => 'Fri',
		'daynum'    => 27,
		'full_date' => 'Friday July 27, 2019',
		'datetime'  => '2019-07-27',
	], [
		'weekday'   => 'Sat',
		'daynum'    => 28,
		'full_date' => 'Saturday July 28, 2019',
		'datetime'  => '2019-07-28',
	],
];
?>
<header class="tribe-events-pro-week-grid__header" role="rowgroup">

	<h2 class="tribe-common-a11y-visual-hide" id="tribe-events-pro-week-header">
		<?php printf( esc_html__( 'Week of %s', 'tribe-events-calendar-pro' ), tribe_get_event_label_plural() ); ?>
	</h2>

	<div class="tribe-events-pro-week-grid__header-row" role="row">

		<div
			class="tribe-events-pro-week-grid__header-column tribe-events-pro-week-grid__header-column--empty"
			role="gridcell"
		>
		</div>

		<?php foreach ( $days_of_week as $day ) : ?>

			<?php $this->template( 'week/grid-header/header-column', [ 'day' => $day ] ); ?>

		<?php endforeach; ?>
	</div>
</header>
