<?php
/**
 * View: Photo View - Single Event Featured Image
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/photo/event/featured-image.php
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

$placeholder = trailingslashit( Tribe__Events__Pro__Main::instance()->pluginUrl ) . 'src/resources/images/tribe-event-placeholder-image.svg';
$event_image = has_post_thumbnail( $event_id ) ? get_the_post_thumbnail_url( $event_id, 'large' ) : $placeholder;

?>
<div class="tribe-events-pro-photo__event-featured-image-wrapper">
	<a
		href="<?php echo esc_url( tribe_get_event_link( $event_id ) ); ?>"
		title="<?php echo esc_attr( get_the_title( $event_id ) ); ?>"
		rel="bookmark"
		class="tribe-events-pro-photo__event-featured-image-link"
	>
		<div class="tribe-events-pro-photo__event-featured-image tribe-common-c-image tribe-common-c-image--bg">
			<div
				class="tribe-common-c-image__bg"
				style="background-image: url('<?php echo esc_attr( $event_image ); ?>');"
				role="img"
				aria-label="alt text here"
			>
			</div>
		</div>
	</a>
</div>
