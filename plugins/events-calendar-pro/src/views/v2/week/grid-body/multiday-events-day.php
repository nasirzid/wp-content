<?php
/**
 * View: Week View - Multiday Events Day
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/week/grid-body/multiday-events-day.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 *
 * @var int $multiday_min_toggle The number we should be displaying the toggle on.
 *
 */

$events = $this->get( 'events' );

/**
 * @todo: @be: We're getting the day here to set a div ID.
 * If we can get this from template vars it shouldn't be necessary to receive it.
 * And we could get rid of this.
*/
$day = $this->get( 'day' );

/**
 * THIS IS DAY BASED.
 * @todo: @BE: Determin if we should include the read more button.
 * It has the same condition as the "Toggle button" but on a day basis.
 * The button should be shown if we have more multiday + full-day events than the X number we want to display.
 */
$should_display_more_link = count( $events ) >= $multiday_min_toggle;

/**
 * This will be the toggle id.
 *
*/
$mutiday_day_toggle_id = 'tribe-events-pro-multiday-toggle-day-' . $day;

?>
<div class="tribe-events-pro-week-grid__multiday-events-day" role="gridcell">

	<?php foreach ( $events as $key => $event ) : ?>

		<?php if ( $should_display_more_link && ( $multiday_min_toggle === $key ) ) : ?>
			<?php
			/*
				@be: This div opener is for the div containing the events that are supposed to be toggled.
				So, if we should display the "more link" and the key for the events is "3" (meaning that is the event after 3), we should open the div.
				The ID used here, would be part of the `aria-controls` of
					- The button of `multiday-events-row-header-toggle.php`
					- The button of `multiday-events-day/more-events.php`
			*/
			?>
			<div
				id="<?php echo esc_attr( $mutiday_day_toggle_id ); ?>"
				data-js="tribe-events-pro-week-multiday-accordion"
				class="tribe-events-pro-week-grid__multiday-overflow-events"
			>
		<?php endif; ?>

		<?php
		if ( 'spacer' === $event[ 'type' ] ) {
			$this->template( 'week/grid-body/multiday-events-day/multiday-event-spacer' );
			continue;
		}

		$this->template( 'week/grid-body/multiday-events-day/multiday-event', [ 'event' => (object) $event ] );
		?>
	<?php endforeach; ?>

	<?php if ( $should_display_more_link ) : ?>
		<?php /* @be: This closing tag `</div> corresponds to the one opened on line 54. */ ?>
		</div>
		<?php $this->template( 'week/grid-body/multiday-events-day/more-events', [ 'day' => $day ] ); ?>
	<?php endif; ?>

</div>
