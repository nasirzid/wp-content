<?php
/**
 * View: Map View (Default)
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/map/default.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */

$events = $this->get( 'events' );

?>
<div class="tribe-events-pro-map-default">

	<?php foreach ( $events as $event ) : ?>

		<?php $this->template( 'map/default/month-separator', [ 'event' => $event ] ); ?>

		<?php $this->template( 'map/default/event', [ 'event' => $event ] ); ?>

	<?php endforeach; ?>

</div>

<?php $this->template( 'map/default/nav' ); ?>
