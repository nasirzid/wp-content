<?php
/**
 * View: Week View - Event Date
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/grid-body/events-day/event/date.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 */
$event = $this->get( 'event' );

// @BE: Replace these with template tags and/or event data
$is_featured  = isset( $event->featured ) && $event->featured;
$is_recurring = isset( $event->recurring ) && $event->recurring;
// @todo: @be: This data is hardcoded for demo purposes. Please use real data.
$start_date   = isset( $event->EventStartDate ) ? $event->EventStartDate : '2019-07-26 06:00:00';
$end_date     = isset( $event->EventEndDate ) ? $event->EventEndDate : '2019-07-26 09:00:00';

?>
<div class="tribe-events-pro-week-grid__event-datetime">
	<time datetime="14:00"><?php echo date( 'g:i a', strtotime( $start_date ) ); ?></time>
	<span class="tribe-events-pro-week-grid__event-datetime-separator"> &mdash; </span>
	<time datetime="18:00"><?php echo date( 'g:i a', strtotime( $end_date ) ); ?></time>
	<?php if ( $is_recurring ) : ?>
		<em
			class="tribe-events-pro-week-grid__event-datetime-recurring tribe-common-svgicon tribe-common-svgicon--recurring"
			aria-label="<?php esc_attr_e( 'Recurring', 'tribe-events-calendar-pro' ) ?>"
			title="<?php esc_attr_e( 'Recurring', 'tribe-events-calendar-pro' ) ?>"
		>
		</em>
	<?php endif; ?>
</div>
