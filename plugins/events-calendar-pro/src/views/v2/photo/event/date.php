<?php
/**
 * View: Photo View - Single Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/photo/event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7.5
 *
 */

use Tribe__Date_Utils as Dates;

$event = $this->get( 'event' );
$event_week_day  = tribe_get_start_date( $event, true, 'D' );
$event_day_num   = tribe_get_start_date( $event, true, 'j' );
$event_date_attr = tribe_get_start_date( $event, true, Dates::DBDATEFORMAT );
?>
<div class="tribe-events-pro-photo__event-date-tag tribe-common-g-col">
	<time class="tribe-events-pro-photo__event-date-tag-datetime" datetime="<?php echo esc_attr( $event_date_attr ); ?>">
		<span class="tribe-events-pro-photo__event-date-tag-weekday">
			<?php echo esc_html( $event_week_day ); ?>
		</span>
		<span class="tribe-events-pro-photo__event-date-tag-daynum tribe-common-h5 tribe-common-h4--min-medium">
			<?php echo esc_html( $event_day_num ); ?>
		</span>
	</time>
</div>
