<?php
/**
 * Single Event Template
 * A single event. This displays the event title, description, meta, and
 * optionally, the Google map for the event.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/single-event.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$events_label_singular = tribe_get_event_label_singular();
$events_label_plural = tribe_get_event_label_plural();

$event_id = get_the_ID();

?>

<div id="tribe-events-content" class="tribe-events-single">

	<!-- Notices -->
	<?php
	if ( function_exists( 'tribe_the_notices' ) ) {
		tribe_the_notices();
	} else {
		tribe_events_the_notices();
	}
	?>

	<?php while ( have_posts() ) :  the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php if ( has_post_thumbnail() ) :  ?>
				<div class="fusion-events-featured-image">
					<div class="hover-type-<?php echo Avada()->settings->get( 'ec_hover_type' ); ?>">
						<!-- Event featured image, but exclude link -->
						<?php echo tribe_event_featured_image( $event_id, 'full', false ); ?>

						<?php Avada_EventsCalendar::render_single_event_title(); ?>
					</div>
			<?php else : ?>
				<div class="fusion-events-featured-image fusion-events-single-title">
					<?php Avada_EventsCalendar::render_single_event_title(); ?>
			<?php endif; ?>
				</div>

			<!-- Event content -->
			<?php do_action( 'tribe_events_single_event_before_the_content' ); ?>
			<div class="tribe-events-single-event-description tribe-events-content entry-content description">
            <?php if(ICL_LANGUAGE_CODE=='en'): ?>
				<?php
				if(get_field('ticket_page_link'))
				{
					echo '<div style="margin-bottom: 30px;" class="fusion-clearfix"><a style="background:#e51053; text-transform:uppercase; border-radius: 2px; padding: 17px 40px; line-height: 21px; font-size: 18px; color: #fff; font-family: Roboto Condensed;" href="' . get_field('ticket_page_link') . '" target="_blank">Buy Tickets</a></div>';
				}
				?>
			<?php endif; ?>
			<?php if(ICL_LANGUAGE_CODE=='fr'): ?>
            				<?php
				if(get_field('ticket_page_link'))
				{
					echo '<div style="margin-bottom: 30px;" class="fusion-clearfix"><a style="background:#e51053; text-transform:uppercase; border-radius: 2px; padding: 17px 40px; line-height: 21px; font-size: 18px; color: #fff; font-family: Roboto Condensed;" href="' . get_field('ticket_page_link') . '" target="_blank">Acheter des billets</a></div>';
				}
				?>
			<?php endif; ?>
				<?php the_content(); ?>
				<?php if(get_field('acknowledgements')) { echo '<div style="font-size: 12px; margin-top: 30px;">' . get_field('acknowledgements') . '</div>'; } ?>
				<?php if(get_field('sponsor_logo')) { echo '<div style="margin-top: 20px; margin-bottom: 30px;"><img src="' . get_field('sponsor_logo') .'"></div>'; } ?>
			</div>
			<!-- .tribe-events-single-event-description -->
			<?php do_action( 'tribe_events_single_event_after_the_content' ); ?>

			<!-- Event meta -->
			<?php do_action( 'tribe_events_single_event_after_the_meta' ); ?>
		</div> <!-- #post-x -->

		<?php avada_render_social_sharing( 'events' ); ?>

		<?php
		if ( get_post_type() == Tribe__Events__Main::POSTTYPE && tribe_get_option( 'showComments', false ) ) {

			add_filter( 'comments_template', 'add_comments_template' );

			function add_comments_template() {
				return Avada::$template_dir_path . '/comments.php';
			}

			comments_template();
		}
		?>
	<?php endwhile;
	?>

	<!-- Event footer -->
	<div id="tribe-events-footer">
		<!-- Navigation -->
		<h3 class="tribe-events-visuallyhidden"><?php printf( __( '%s Navigation', 'the-events-calendar' ), $events_label_singular ); ?></h3>
		<ul class="tribe-events-sub-nav">
			<li class="tribe-events-nav-previous"><?php tribe_the_prev_event_link( '%title%' ) ?></li>
			<li class="tribe-events-nav-next"><?php tribe_the_next_event_link( '%title%' ) ?></li>
		</ul>
		<!-- .tribe-events-sub-nav -->
	</div>
	<!-- #tribe-events-footer -->

</div><!-- #tribe-events-content -->
