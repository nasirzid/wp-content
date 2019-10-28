<?php
/**
 * View: Photo Event
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/photo/event.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version 4.7.5
 *
 */

$event    = $this->get( 'event' );
$event_id = $event->ID;

$classes = [ 'tribe-common-g-col', 'tribe-events-pro-photo__event' ];

if ( tribe( 'tec.featured_events' )->is_featured( $event_id ) ) {
	$classes[] = 'tribe-events-pro-photo__event--featured';
}

?>
<article class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">

	<?php $this->template( 'photo/event/featured-image', [ 'event' => $event ] ); ?>

	<div class="tribe-events-pro-photo__event-details">
		<?php $this->template( 'photo/event/date', [ 'event' => $event ] ); ?>
		<div class="tribe-events-pro-photo__event-title-wrapper">
			<?php $this->template( 'photo/event/title', [ 'event' => $event ] ); ?>
		</div>
	</div>

</article>