<?php
/**
 * View: Map View (Default) - Single Event Description
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/map/default/event/description.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
$event    = $this->get( 'event' );
$event_id = $event->ID;
?>
<div class="tribe-events-pro-map-default__event-description tribe-common-b2">
	<?php echo tribe_events_get_the_excerpt( $event, wp_kses_allowed_html( 'post' ) ); ?>
</div>
