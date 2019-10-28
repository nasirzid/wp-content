<?php
/**
 * The Map View.
 *
 * @package Tribe\Events\Pro\Views\V2\Views
 * @since TBD
 */

namespace Tribe\Events\Pro\Views\V2\Views;

use Tribe\Events\Views\V2\View;
use Tribe__Events__Main as TEC;
use Tribe__Events__Rewrite as Rewrite;
use Tribe__Utils__Array as Arr;

class Map_View extends View {
	/**
	 * Slug for this view
	 *
	 * @since TBD
	 *
	 * @var string
	 */
	protected $slug = 'map';

	/**
	 * Visibility for this view.
	 *
	 * @since TBD
	 *
	 * @var bool
	 */
	protected $publicly_visible = true;

	/**
	 * {@inheritDoc}
	 */
	protected function setup_repository_args( \Tribe__Context $context = null ) {
		$context = null !== $context ? $context : $this->context;

		$args = parent::setup_repository_args( $context );

		$event_display = $this->context->get( 'event_display_mode', 'current' );
		$date          = $this->context->get( 'event_date', 'now' );

		if ( 'past' !== $event_display ) {
			$args['ends_after'] = $date;
		} else {
			$args['order']       = 'DESC';
			$args['ends_before'] = $date;
		}

		return $args;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function setup_template_vars() {
		$template_vars = parent::setup_template_vars();

		// @todo: @be: determin what's default and what's premium.
		// $api_key = tribe_get_option( 'google_maps_js_api_key' );
		// $has_maps_api_key = ! empty( $api_key ) && is_string( $api_key );
		// $template = $has_maps_api_key ? 'premium' : 'default';
		$template = 'default';

		$template_vars['template'] = $template;

		return $template_vars;
	}
}
