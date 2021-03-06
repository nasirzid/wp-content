<?php


class Tribe__Events__Pro__Recurrence__Exclusions {

	/**
	 * @var string|null
	 */
	protected $timezone_string;

	/**
	 * @param string|null $timezone_string The event timezone string if any.
	 */
	public function __construct( $timezone_string = null ) {
		$this->timezone_string = $timezone_string;
	}

	/**
	 * @var Tribe__Events__Pro__Recurrence__Exclusions
	 */
	protected static $instance;

	/**
	 * @param string|null $timezone_string The event timezone string if any.
	 *
	 * @return Tribe__Events__Pro__Recurrence__Exclusions
	 */
	public static function instance( $timezone_string = null ) {
		if ( empty( self::$instance ) ) {
			self::$instance = new self( $timezone_string );
		}

		return self::$instance;
	}

	/**
	 * Accepts an array of $date_durations and removes any falling on the dates listed
	 * within $exclusion_dates.
	 *
	 * Both parameters are arrays of arrays, each inner array or "date duration" taking the
	 * following form:
	 *
	 *     [ 'timestamp' => int,
	 *       'duration'  => int  ]
	 *
	 * In the case of exclusions, duration will always be zero as custom exclusions do
	 * not currently support custom durations, so that element is ignored during comparison.
	 *
	 * @param array $date_durations
	 * @param array $exclusion_dates
	 *
	 * @return array
	 */
	public function remove_exclusions( array $date_durations, array $exclusion_dates ) {

		$date_default_timezone = date_default_timezone_get();

		date_default_timezone_set( $this->get_timezone() );

		$exclusion_timestamps = array();

		// 24hrs in seconds -1 second
		$almost_one_day = 86399;

		foreach ( $exclusion_dates as $exclusion ) {
			$start                  = strtotime( 'midnight', $exclusion['timestamp'] );
			$exclusion_timestamps[] = array(
				'start' => $start,
				'end'   => $start + $almost_one_day,
			);
		}

		foreach ( $date_durations as $key => $date_duration ) {
			foreach ( $exclusion_timestamps as $exclusion_timestamp ) {
				if ( $exclusion_timestamp['start'] <= $date_duration['timestamp'] && $date_duration['timestamp'] <= $exclusion_timestamp['end'] ) {
					unset( $date_durations[ $key ] );
				}
			}
		}

		$date_durations = array_values( $date_durations );

		date_default_timezone_set( $date_default_timezone );

		return $date_durations;
	}

	/**
	 * Return the name of the Timezone being modified
	 *
	 * @since 4.4.26
	 *
	 * @return string
	 */
	public function get_timezone() {
		return class_exists( 'Tribe__Timezones' )
			? Tribe__Timezones::generate_timezone_string_from_utc_offset( $this->timezone_string )
			: 'UTC';
	}
}
