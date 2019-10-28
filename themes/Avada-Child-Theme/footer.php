<?php
/**
 * The footer template.
 *
 * @package Avada
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
					<?php do_action( 'avada_after_main_content' ); ?>

				</div>  <!-- fusion-row -->
			</main>  <!-- #main -->
			<?php do_action( 'avada_after_main_container' ); ?>

			<?php global $social_icons; ?>

			<?php
			/**
			 * Get the correct page ID.
			 */
			$c_page_id = Avada()->fusion_library->get_page_id();
			?>

			<?php
			/**
			 * Only include the footer.
			 */
			?>
			<?php if ( ! is_page_template( 'blank.php' ) ) : ?>
				<?php $footer_parallax_class = ( 'footer_parallax_effect' === Avada()->settings->get( 'footer_special_effects' ) ) ? ' fusion-footer-parallax' : ''; ?>

				<div class="fusion-footer<?php echo esc_attr( $footer_parallax_class ); ?>">
					<?php get_template_part( 'templates/footer-content' ); ?>
				</div> <!-- fusion-footer -->
			<?php endif; // End is not blank page check. ?>

			<?php
			/**
			 * Add sliding bar.
			 */
			?>
			<?php if ( Avada()->settings->get( 'slidingbar_widgets' ) && ! is_page_template( 'blank.php' ) ) : ?>
				<?php get_template_part( 'sliding_bar' ); ?>
			<?php endif; ?>
		</div> <!-- wrapper -->

		<?php
		/**
		 * Check if boxed side header layout is used; if so close the #boxed-wrapper container.
		 */
		$page_bg_layout = 'default';
		if ( $c_page_id && is_numeric( $c_page_id ) ) {
			$fpo_page_bg_layout = get_post_meta( $c_page_id, 'pyre_page_bg_layout', true );
			$page_bg_layout = ( $fpo_page_bg_layout ) ? $fpo_page_bg_layout : $page_bg_layout;
		}
		?>
		<?php if ( ( ( 'Boxed' === Avada()->settings->get( 'layout' ) && 'default' === $page_bg_layout ) || 'boxed' === $page_bg_layout ) && 'Top' !== Avada()->settings->get( 'header_position' ) ) : ?>
			</div> <!-- #boxed-wrapper -->
		<?php endif; ?>
		<?php if ( ( ( 'Boxed' === Avada()->settings->get( 'layout' ) && 'default' === $page_bg_layout ) || 'boxed' === $page_bg_layout ) && 'framed' === Avada()->settings->get( 'scroll_offset' ) && 0 !== intval( Avada()->settings->get( 'margin_offset', 'top' ) ) ) : ?>
			<div class="fusion-top-frame"></div>
			<div class="fusion-bottom-frame"></div>
			<?php if ( 'None' !== Avada()->settings->get( 'boxed_modal_shadow' ) ) : ?>
				<div class="fusion-boxed-shadow"></div>
			<?php endif; ?>
		<?php endif; ?>
		<a class="fusion-one-page-text-link fusion-page-load-link"></a>
		<?php wp_footer(); ?>
		<script type="text/javascript">
    jQuery( ".single_variation_wrap" ).on( "show_variation", function ( event, variation ) {
    var variation = variation.variation_id;
    if( variation == 58968 ){
    
      jQuery("span.woocommerce-Price-amount.amount").addClass("license");
    }
    //console.log( variation );
    } );
		
		
			(function($){
				$(document).ready(function(){
					if($('.countdown_text').length > 0) {
						var i = 10;
						setInterval(function(){
							if(i > 0) {
								$('.countdown_text').html(i);
							}
							if(i == 1){
								i = 0;
								<?php
									/* check current langugage and redirect accordingly*/
									global $post;
									$slug = $post->post_name;
									if(get_locale() == 'en_US') {
										if($slug == 'music-library-redirect') {
											$link = 'https://www.musiccentre.ca/sheet-music';
										} else {
											$link = 'https://www.musiccentre.ca/composers';
										}
									} else {
										if($slug == 'music-library-redirect') {
											$link = 'https://www.musiccentre.ca/fr/partitions';
										} else {
											$link = 'https://www.musiccentre.ca/fr/compositeurs';
										}
									}
								?>
								window.location = "<?php echo $link; ?>";
							} else if(i < 1) {
								$('.countdown_text').remove();
							} else {
								i = (i-1);
							}
						}, 1000);
					}
					setTimeout(function(){
						$('.launch_bar_wrapper').addClass('show_bar');
						var top = 0;
						var top_start = 0;
						if($('body.admin-bar').length > 0) {
							top_start = $('#wpadminbar').height();
						}
						top = $('.launch_bar_wrapper').height();
						$('body').css({'margin-top':top+'px'});
						$('.launch_bar_wrapper').css({'top': top_start});
					},4000);
				});
			})(jQuery);
		</script>
		<link href="//amp.azure.net/libs/amp/2.2.2/skins/amp-default/azuremediaplayer.min.css" rel="stylesheet">
	<script src="//amp.azure.net/libs/amp/2.2.2/azuremediaplayer.min.js"></script>
	<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				$('body.logged-in #menu-top-menu .fusion-dropdown-submenu').each(function(){
					var txt = $(this).find('a').html();
					if(txt == 'Logout') {
						$(this).find('a').attr('href',"<?php echo site_url('/logout');?>");
					}
					if(txt == 'Library Loans') {
						$(this).find('a').attr('target',"_blank");
					}
				});
				var if_loggedin  = $('body.logged-in .avada-myaccount-user .username a').html();
				if(if_loggedin == 'Sign Out') {
					$('body.logged-in .avada-myaccount-user .username a').attr('href',"<?php echo site_url('/logout');?>");
				}
				var if_loggedin  = $('body.logged-in .woocommerce-MyAccount-navigation-link--customer-logout a').html();
				if(if_loggedin == 'Logout') {
					$('.woocommerce-MyAccount-navigation-link--customer-logout a').attr('href',"<?php echo site_url('/logout');?>");
				}
				if($('body.logged-in .woocommerce-MyAccount-navigation-link--library-loans a') != undefined) {
					$('body.logged-in .woocommerce-MyAccount-navigation-link--library-loans a').attr('target',"_blank");
				}
				if($('.request_product_estimate') != undefined) {

					$('.request_product_estimate').click(function(){
						var fields = JSON.parse($(this).attr('data-info'));
						for (var key in fields) {
						  	$('.'+key).val(fields[key]);
						}
						var subtitle = "<?php echo get_field( "subtitle", get_the_ID() ); ?>";
						var p_title = "<?php echo get_the_title(); ?>";
						$('.product_info').html('<h2 class="overlay_title">Rental Request</h2>');
						$('.product_info').append('<h3 class="overlay_product_title">'+p_title+'</h3>');
						$('.product_info').append('<h3 class="overlay_sub_title">'+subtitle+'</h3>');
						//$('.p_email').val(email);
						//$('.p_rsn_number').val(rsn);
						$('.request_form_overlay').addClass('show_overlay');
					});

					$('.request_form_close').click(function(){
						$('.request_form_overlay').removeClass('show_overlay');
					});
					/* trigger popup if ?request=rent */
					var urlParams = new URLSearchParams(window.location.search);
					if(urlParams.has('request')) {
						var req = urlParams.get('request');
						if(req == 'rent') {
							$('.request_product_estimate').trigger('click');	
						}
						
					}

				}
				
				/* update account */
				if($('.woocommerce-MyAccount-content').length > 0 ) {
					$('.woocommerce-Input--password.input-text').each(function(){
						var id = $(this).attr('id');
						if(id == 'password_current') {
							$(this).siblings('label').html('Current Password <span class="required">*</span>');
							$(this).attr('required', true);
						}
						if(id == 'password_1') {
							$(this).siblings('label').html('New password <span class="required">*</span>');
							$(this).attr('required', true);
						}
						if(id == 'password_2') {
							$(this).siblings('label').html('Confirm password <span class="required">*</span>');
							$(this).attr('required', true);
						}
					});
				}
				
				/* search keyword */
				$('.cmc_product_search').keyup(function(){
					var val = $(this).val();
					$('.cmc_product_search_form .filter_s_keyword').val(val);
				});

				if($('#reg_billing_country').length > 0) {
					$('#reg_billing_country').select2();
					$('body').on('change','#reg_billing_country', function(){
						var val = $(this).val();
						if(val == 'CA') {
							$('.state_province_wrap').show();
						} else {
							$('.state_province_wrap').hide();
						}
					});
				}
				if($('#reg_billing_state').length > 0) {
					$('#reg_billing_state').select2();
				}
				
			});
		})(jQuery);
	</script>
	<script type="text/javascript">

		/*var myOptions = {
				"nativeControlsForTouch": false,
				controls: true,
				autoplay: true,
				width: "640",
				height: "400",
				poster: "//download.blender.org/ED/cover.jpg"
			}
			
			myPlayer2 = amp("azuremediaplayer2", myOptions);
			myPlayer2.src([{ src: "//devmediaservices-cact.streaming.media.azure.net/092c955a-73b5-4ddb-a186-b1a0d7722413/11158_saintmarco.ism/manifest", type: "application/vnd.ms-sstr+xml", protectionInfo: [{ type: "AES" }] 
			}]);*/
			
	</script>
	</body>
</html>
