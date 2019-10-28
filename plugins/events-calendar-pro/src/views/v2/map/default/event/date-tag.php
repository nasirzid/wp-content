<?php
/**
 * View: Map View (Default) - Single Event Date Tag
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/map/default/event/date-tag.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 */

use Tribe__Date_Utils as Date;

$event = $this->get( 'event' );
$event_week_day  = tribe_get_start_date( $event, true, 'D' );
$event_day_num   = tribe_get_start_date( $event, true, 'j' );
$event_date_attr = tribe_get_start_date( $event, true, Date::DBDATEFORMAT );
?>
<div class="tribe-events-pro-map-default__event-date-tag tribe-common-g-col">
	<time class="tribe-events-pro-map-default__event-date-tag-datetime" datetime="<?php echo esc_attr( $event_date_attr ); ?>">
		<span class="tribe-events-pro-map-default__event-date-tag-weekday">
			<?php echo esc_html( $event_week_day ); ?>
		</span>
		<span class="tribe-events-pro-map-default__event-date-tag-daynum tribe-common-h5 tribe-common-h4--min-medium">
			<?php echo esc_html( $event_day_num ); ?>
		</span>
	</time>
</div>
