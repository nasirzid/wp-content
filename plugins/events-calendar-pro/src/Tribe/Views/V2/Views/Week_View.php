<?php
/**
 * Renders the week view
 *
 * @since   4.7.5
 * @package Tribe\Events\PRO\Views\V2\Views
 */

namespace Tribe\Events\Pro\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe__Utils__Array as Arr;
use Tribe__Context as Context;
use Tribe__Events__Main as TEC;

/**
 * Class Week_View
 *
 * @since   4.7.5
 *
 * @package Tribe\Events\PRO\Views\V2\Views
 */
class Week_View extends View {
	/**
	 * Slug for this view
	 *
	 * @since 4.7.5
	 *
	 * @var string
	 */
	protected $slug = 'week';

	/**
	 * Visibility for this view.
	 *
	 * @since 4.7.5
	 *
	 * @var bool
	 */
	protected $publicly_visible = true;

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( Context $context = null ) {
		$context = null !== $context ? $context : $this->context;
		$date = Arr::get( $context, 'eventDate', 'now' );
		if ( 'past' !== Arr::get( $context, 'event_display', 'current' ) ) {
			$args['ends_after'] = $date;
		} else {
			$args['ends_before'] = $date;
		}
		return $args;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_template_vars() {

		$template_vars = parent::setup_template_vars();

		$multiday_min_toggle = 3; // @todo @be: make this value filterable.


		$template_vars['multiday_min_toggle']     = $multiday_min_toggle;
		// @todo @be: Calculate if we need to show the toggle based on:
		// - if any of the days of the week has more multiday + all day events than `$multiday_min_toggle`.
		$template_vars['multiday_display_toggle'] = true;

		return $template_vars;
	}
}
