<?php
/**
 * Manages the toggle to hide recurring events for the Views V2 implementation.
 *
 * @since   4.7.5
 * @package Tribe\Events\Pro\Views\V2\Views\Partials;
 */

namespace Tribe\Events\Pro\Views\V2\Views\Partials;

/**
 * Class Hide_Recurring_Events_Toggle
 *
 * @since   4.7.5
 * @package Tribe\Events\Pro\Views\V2\Views\Partials;
 */
class Hide_Recurring_Events_Toggle {
	/**
	 * Renders the "Hide recurring events" toggle in the View.
	 *
	 * @since  4.7.5
	 *
	 * @param \Tribe__Template $template Current instance of the `Tribe__Template` that's being rendered.
	 *
	 * @return string The rendered partial HTML code.
	 */
	public function render( $template ) {
		return $template->template( 'recurrence/hide-recurring' );
	}
}
