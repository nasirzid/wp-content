<?php
/**
 * View: Map (Default) - Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/map/default/event.php
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

$classes = [ 'tribe-common-g-row', 'tribe-common-g-row--gutters', 'tribe-events-pro-map-default__event-row' ];
$classes['tribe-events-pro-map-default__event-row--featured'] = tribe( 'tec.featured_events' )->is_featured( $event_id );

?>
<div <?php tribe_classes( $classes ) ?>>

	<?php $this->template( 'map/default/event/date-tag', [ 'event' => $event ] ); ?>

	<div class="tribe-events-pro-map-default__event-wrapper tribe-common-g-col">
		<article class="tribe-events-pro-map-default__event tribe-common-g-row tribe-common-g-row--gutters">

			<?php // @todo: @fe: add the map embed here. ?>

			<div class="tribe-events-pro-map-default__event-details tribe-common-g-col">

				<header class="tribe-events-pro-map-default__event-header">
					<?php $this->template( 'map/default/event/date', [ 'event' => $event ] ); ?>
					<?php $this->template( 'map/default/event/title', [ 'event' => $event ] ); ?>
					<?php $this->template( 'map/default/event/venue', [ 'event' => $event ] ); ?>
				</header>

				<?php $this->template( 'map/default/event/description', [ 'event' => $event ] ); ?>

			</div>
		</article>
	</div>

</div>
