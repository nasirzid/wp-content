/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 4.7.5
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Week Grid Scroller Object in the Global Tribe variable
 *
 * @since 4.7.5
 *
 * @type  {PlainObject}
 */
tribe.events.views.weekGridScroller = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since 4.7.5
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.views.manager
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	var $document = $( document );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 4.7.5
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		weekGridEventsRowOuterWrapper: '[data-js="tribe-events-pro-week-grid-events-row-outer-wrapper"]',
		weekGridEventsRowWrapper: '[data-js="tribe-events-pro-week-grid-events-row-wrapper"]',
		weekGridEventsRowWrapperClass: '.tribe-events-pro-week-grid__events-row-wrapper',
		weekGridEventsRowWrapperActiveClass: '.tribe-events-pro-week-grid__events-row-wrapper--active',
		weekGridEventsPaneClass: '.tribe-events-pro-week-grid__events-row-scroll-pane',
		weekGridEventsSliderClass: '.tribe-events-pro-week-grid__events-row-scroll-slider',
	};

	/**
	 * Deinitialize scroller
	 *
	 * @since 4.7.5
	 *
	 * @param {jQuery} $container jQuery object of view container.
	 *
	 * @return {void}
	 */
	obj.deinitScroller = function( $container ) {
		$container
			.find( obj.selectors.weekGridEventsRowOuterWrapper )
			.nanoScroller( { destroy: true } );
	};

	/**
	 * Initialize scroller
	 *
	 * @since 4.7.5
	 *
	 * @param {jQuery} $container jQuery object of view container.
	 *
	 * @return {void}
	 */
	obj.initScroller = function( $container ) {
		$container
			.find( obj.selectors.weekGridEventsRowOuterWrapper )
			.nanoScroller( {
				paneClass: obj.selectors.weekGridEventsPaneClass.className(),
				sliderClass: obj.selectors.weekGridEventsSliderClass.className(),
				contentClass: obj.selectors.weekGridEventsRowWrapperClass.className(),
				iOSNativeScrolling: true,
				alwaysVisible: false,
				/**
				 * @todo: implement scrollTo when events are available.
				 */
				// scrollTo: $first_event
			} )
			.find( obj.selectors.weekGridEventsRowWrapper )
			.addClass( obj.selectors.weekGridEventsRowWrapperActiveClass.className() );
	};

	/**
	 * Deinitialize week grid scroller.
	 *
	 * @since 4.7.5
	 *
	 * @param  {Event}       event    event object for 'afterSetup.tribeEvents' event
	 * @param  {jqXHR}       jqXHR    Request object
	 * @param  {PlainObject} settings Settings that this request was made with
	 *
	 * @return {void}
	 */
	obj.deinit = function( event, jqXHR, settings ) {
		var $container = event.data.container;
		obj.deinitScroller( $container );
	};

	/**
	 * Initialize week grid scroller.
	 *
	 * @since 4.7.5
	 *
	 * @param {Event}   event      JS event triggered.
	 * @param {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event.
	 * @param {jQuery}  $container jQuery object of view container.
	 * @param {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		obj.initScroller( $container );
		$container.on( 'beforeAjaxSuccess.tribeEvents', { container: $container }, obj.deinit );
	};

	/**
	 * Handles the initialization of the week grid scroller when Document is ready
	 *
	 * @since 4.7.5
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.init );
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.weekGridScroller );
