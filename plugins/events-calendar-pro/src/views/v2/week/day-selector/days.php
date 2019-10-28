<?php
/**
 * View: Week View - Day Selector Days
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/day-selector/days.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7.6
 *
 */

$days = [
	[
		'weekday'    => 'Sun',
		'daynum'     => 22,
		'datetime'   => '2019-07-22',
		'has_events' => true,
		'is_active'  => true,
	],
	[
		'weekday'    => 'Mon',
		'daynum'     => 23,
		'datetime'   => '2019-07-23',
		'has_events' => true,
		'is_active'  => false,
	],
	[
		'weekday'    => 'Tue',
		'daynum'     => 24,
		'datetime'   => '2019-07-24',
		'has_events' => true,
		'is_active'  => false,
	],
	[
		'weekday'    => 'Wed',
		'daynum'     => 25,
		'datetime'   => '2019-07-25',
		'has_events' => true,
		'is_active'  => false,
	],
	[
		'weekday'    => 'Thu',
		'daynum'     => 26,
		'datetime'   => '2019-07-26',
		'has_events' => true,
		'is_active'  => false,
	],
	[
		'weekday'    => 'Fri',
		'daynum'     => 27,
		'datetime'   => '2019-07-27',
		'has_events' => true,
		'is_active'  => false,
	],
	[
		'weekday'    => 'Sat',
		'daynum'     => 28,
		'datetime'   => '2019-07-28',
		'has_events' => true,
		'is_active'  => false,
	],
];

?>
<ul class="tribe-events-pro-week-day-selector__days-list">

	<?php foreach ( $days as $day ) : ?>
		<?php $this->template( 'week/day-selector/days/day', [ 'day' => $day ] ); ?>
	<?php endforeach; ?>

</ul>
