<?php
/**
 * View: Week View - Grid Body
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/grid-body.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

/**
 * @todo: replace with actual data, this is just a placeholder
 */
/**
 * Multiday events:
 * 1. Multiday event one:   July 20 - July 25
 * 2. Multiday event two:   July 23 - July 27
 * 3. Multiday event three: July 25 - July 26
 * 4. Multiday event four:  July 27 - July 30
 */
$multiday_events = [
	0 => [ // July 22, 2019
		[
			'ID'   => 1,
			'type' => 'event',
			'title' => 'Multiday event one',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 20 to July 25',
			'duration' => 4,
			'should_display' => true,
			'start_this_week' => false,
			'end_this_week' => true,
			'featured' => true,
		],
	],
	1 => [ // July 23, 2019
		[
			'ID'   => 1,
			'type' => 'event',
			'title' => 'Multiday event one',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 20 to July 25',
			'duration' => 4,
			'should_display' => false,
			'start_this_week' => false,
			'end_this_week' => true,
			'featured' => true,
		], [
			'ID'   => 2,
			'type' => 'event',
			'title' => 'Multiday event two',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 23 to July 27',
			'duration' => 5,
			'should_display' => true,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		],
	],
	2 => [ // July 24, 2019
		[
			'ID'   => 1,
			'type' => 'event',
			'title' => 'Multiday event one',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 20 to July 25',
			'duration' => 4,
			'should_display' => false,
			'start_this_week' => false,
			'end_this_week' => true,
			'featured' => true,
		], [
			'ID'   => 2,
			'type' => 'event',
			'title' => 'Multiday event two',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 23 to July 27',
			'duration' => 5,
			'should_display' => false,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		],
	],
	3 => [ // July 25, 2019
		[
			'ID'   => 1,
			'type' => 'event',
			'title' => 'Multiday event one',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 20 to July 25',
			'duration' => 4,
			'should_display' => false,
			'start_this_week' => false,
			'end_this_week' => true,
			'featured' => true,
		], [
			'ID'   => 2,
			'type' => 'event',
			'title' => 'Multiday event two',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 23 to July 27',
			'duration' => 5,
			'should_display' => false,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		], [
			'ID'   => 3,
			'type' => 'event',
			'title' => 'Multiday event three',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 25 to July 26',
			'duration' => 2,
			'should_display' => true,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		],
		 [
			'ID'   => 10,
			'type' => 'event',
			'title' => 'Multiday event whatever',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 25 to July 26',
			'duration' => 2,
			'should_display' => true,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		],
		[
			'ID'   => 11,
			'type' => 'event',
			'title' => 'WordPress Meetup',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 25 to July 26',
			'duration' => 2,
			'should_display' => true,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		],
		[
			'ID'   => 12,
			'type' => 'event',
			'title' => 'Ted X Winnipeg',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 25 to July 26',
			'duration' => 2,
			'should_display' => true,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		],
	],
	4 => [ // July 26, 2019
		[
			'type' => 'spacer',
		], [
			'ID'   => 2,
			'type' => 'event',
			'title' => 'Multiday event two',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 23 to July 27',
			'duration' => 5,
			'should_display' => false,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		], [
			'ID'   => 3,
			'type' => 'event',
			'title' => 'Multiday event three',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 25 to July 26',
			'duration' => 2,
			'should_display' => false,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		],
		 [
			'ID'   => 10,
			'type' => 'event',
			'title' => 'Multiday event whatever',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 25 to July 26',
			'duration' => 2,
			'should_display' => false,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		],
		[
			'ID'   => 11,
			'type' => 'event',
			'title' => 'WordPress Meetup',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 25 to July 26',
			'duration' => 2,
			'should_display' => false,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		],
		[
			'ID'   => 12,
			'type' => 'event',
			'title' => 'Ted X Winnipeg',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 25 to July 26',
			'duration' => 2,
			'should_display' => false,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		],
	],
	5 => [ // July 27, 2019
		[
			'ID'   => 4,
			'type' => 'event',
			'title' => 'Multiday event four',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 27 to July 30',
			'duration' => 2,
			'should_display' => true,
			'start_this_week' => true,
			'end_this_week' => false,
			'featured' => false,
		], [
			'ID'   => 2,
			'type' => 'event',
			'title' => 'Multiday event two',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 23 to July 27',
			'duration' => 5,
			'should_display' => false,
			'start_this_week' => true,
			'end_this_week' => true,
			'featured' => false,
		],
	],
	6 => [ // July 28, 2019
		[
			'ID'   => 4,
			'type' => 'event',
			'title' => 'Multiday event four',
			'link' => '#',
			'datetime' => 'the-date-range',
			'datetime_text' => 'July 27 to July 30',
			'duration' => 2,
			'should_display' => false,
			'start_this_week' => true,
			'end_this_week' => false,
			'featured' => false,
		],
	],
];
/**
 * Events:
 * 1. Event one
 * 2. Event two
 * 3. Event three
 */
$events = [
	0 => [],
	1 => [],
	2 => [],
	3 => [],
	4 => [],
	5 => [],
	6 => [],
];
?>
<div class="tribe-events-pro-week-grid__body" role="rowgroup">

	<?php // @todo @be: check if we have some events for the multiday section ?>
	<?php if ( $multiday_events ) : ?>

		<div class="tribe-events-pro-week-grid__multiday-events-row-outer-wrapper">
			<div class="tribe-events-pro-week-grid__multiday-events-row-wrapper">
				<div
					class="tribe-events-pro-week-grid__multiday-events-row"
					data-js="tribe-events-pro-week-multiday-events-row"
					role="row"
				>

					<?php $this->template( 'week/grid-body/multiday-events-row-header' ); ?>

					<?php for ( $day = 0; $day < 7; $day++ ) : ?>
						<?php
							/*
								@be: we're sending the day here just to set an ID for a div.
								If we have the day later as a template var we can avoid sending it.
							*/
						?>
						<?php $this->template( 'week/grid-body/multiday-events-day', [ 'events' => $multiday_events[ $day ], 'day' => $day ] ); ?>
					<?php endfor; ?>

				</div>
			</div>
		</div>

	<?php endif; ?>

	<div class="tribe-events-pro-week-grid__events-scroll-wrapper">
		<div class="tribe-events-pro-week-grid__events-row-outer-wrapper" data-js="tribe-events-pro-week-grid-events-row-outer-wrapper">
			<div class="tribe-events-pro-week-grid__events-row-wrapper" data-js="tribe-events-pro-week-grid-events-row-wrapper">
				<div class="tribe-events-pro-week-grid__events-row" role="row">

					<?php $this->template( 'week/grid-body/events-row-header' ); ?>

					<?php for ( $day = 0; $day < 7; $day++ ) : ?>
						<?php $this->template( 'week/grid-body/events-day', [ 'events' => $events[ $day ] ] ); ?>
					<?php endfor; ?>

				</div>
			</div>
		</div>
	</div>

</div>
