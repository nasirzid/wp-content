<?php
/**
 * View: Map View (Default) - Month separator
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/map/default/month-separator.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @version TBD
 */

use Tribe\Events\Views\V2\Utils;

$event = $this->get( 'event' );
$should_have_month_separator = Utils\Separators::should_have_month( $this->get( 'events' ), $event );

if ( ! $should_have_month_separator ) {
	return;
}

$separator_text = tribe_get_start_date( $event->ID, true, 'F Y' );
?>
<div class="tribe-events-pro-map-default__month-separator">
	<time
		class="tribe-events-pro-map-default__month-separator-text tribe-common-h7 tribe-common-h--alt"
		datetime="<?php echo esc_attr( tribe_get_start_date( $event->ID, true, 'Y-m' ) ); ?>"
	>
		<?php echo esc_html( $separator_text ); ?>
	</time>
</div>
