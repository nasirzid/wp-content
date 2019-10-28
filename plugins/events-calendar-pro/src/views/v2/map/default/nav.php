<?php
/**
 * View: Map View (Default) Nav Template
 *
 * Override this template in your own theme by creating a file at:
 * [your-theme]/tribe/events-pro/views/v2/map/default/nav.php
 *
 * See more documentation about our views templating system.
 *
 * @link {INSERT_ARTCILE_LINK_HERE}
 *
 * @var string $prev_url The URL to the previous page, if any, or an empty string.
 * @var string $next_url The URL to the next page, if any, or an empty string.
 * @var string $today_url The URL to the today page, if any, or an empty string.
 *
 * @version TBD
 *
 */
?>
<nav class="tribe-events-pro-map-default-nav tribe-events-c-nav">
	<ul class="tribe-events-c-nav__list">
		<?php
		if ( ! empty( $prev_url ) ) {
			$this->template( 'map/default/nav/prev', [ 'link' => $prev_url ] );
		} else {
			$this->template( 'map/default/nav/prev-disabled' );
		}
		?>

		<?php $this->template( 'map/default/nav/today', [ 'link' => '#' ] ); ?>

		<?php
		if ( ! empty( $next_url ) ) {
			$this->template( 'map/default/nav/next', [ 'link' => $next_url ] );
		} else {
			$this->template( 'map/default/nav/next-disabled' );
		}
		?>
	</ul>
</nav>
