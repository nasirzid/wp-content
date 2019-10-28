<?php
/**
 * View: Week View Mobile Events
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/mobile-events.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7.5
 *
 */

$events = $this->get( 'events' );

// Dummy data
$days = [
	[
		'datetime'    => '2019-07-22',
		'is_active'   => true,
		'event_times' => [
			[
				'time'     => '9:00 am',
				'datetime' => '09:00',
				'events'   => [ // array of events that start at 9:00 am
					[
						'ID'    => 10,
						'title' => 'Event One',
						'link'  => '#',
						// etc...
					],
					// etc...
				],
			],
			// etc...
		],
	],
	[
		'datetime'    => '2019-07-23',
		'is_active'   => false,
	],
	[
		'datetime'    => '2019-07-24',
		'is_active'   => false,
	],
	[
		'datetime'    => '2019-07-25',
		'is_active'   => false,
	],
	[
		'datetime'    => '2019-07-26',
		'is_active'   => false,
	],
	[
		'datetime'    => '2019-07-27',
		'is_active'   => false,
	],
	[
		'datetime'    => '2019-07-28',
		'is_active'   => false,
	],
];

?>

<section class="tribe-events-pro-week-mobile-events">

	<?php foreach ( $days as $day ) : ?>
		<?php $this->template( 'week/mobile-events/day', [ 'day' => $day ] ); ?>
	<?php endforeach; ?>

	<?php $this->template( 'week/nav', [ 'location' => 'mobile' ] ); ?>

</section>
