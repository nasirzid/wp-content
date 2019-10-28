<?php
/**
 * Template Name: Home Template 
 *
 * @package Avada
 * @subpackage Templates
 */
/* if(!session_id()) {
    session_start();
} */
get_header(); ?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

<?php

 $current_user = wp_get_current_user();
// echo "<pre>";
//  print_r($current_user);
// echo "</pre>";

// user update
/*print_r(unserialize('a:1:{s:10:"subscriber";b:1;}'));*/
 if ( ! empty( $_POST['update_user'] ) ) {
		 	$updated_user_id        	  = htmlentities(strip_tags(trim($_POST['current_u_id'])));
		 	$update_user_full_name   	  = htmlentities(strip_tags(trim($_POST['user_disply_name'])));
		 	$update_first_name            = htmlentities(strip_tags(trim($_POST['user_first_name'])));		 	
		 	$update_last_name             = htmlentities(strip_tags(trim($_POST['user_last_name'])));
		 	$update_user_email            = htmlentities(strip_tags(trim($_POST['user_email'])));
		 	$update_user_location         = htmlentities(strip_tags(trim($_POST['user_location'])));

		 	// Update user location
		 	update_user_meta($updated_user_id ,'user_location',$update_user_location);

	// update user data
 	 	
 	$user_data = wp_update_user( 
 		array( 'ID' => $updated_user_id, 
 			'display_name' => $update_user_full_name,
 			'first_name' => $update_first_name,
 			'last_name' => $update_last_name,
 			'user_email' => $update_user_email,
 		) );
 	if ( is_wp_error( $user_data ) ) {
		// There was an error; possibly this user doesn't exist.
 		echo 'Error.';
 	} else {
		// Success!
 		echo '<div class="alert alert-success">
			  <strong>Success!</strong> Successfully Updated.
			</div>'; 
			}	// <!-- Update user data End-->



 	//code for reset password
    $passdata = $_POST;
    unset($_POST,$passdata['update_user']);

    $user = wp_get_current_user(); //trace($user);
    $x = wp_check_password( $passdata['old_password'], $user->user_pass, $user->data->ID );

    if($x)
    {
        if( !empty($passdata['new_password']) && !empty($passdata['confirm_password']))
        {
            if($passdata['new_password'] == $passdata['confirm_password'])
            {
                $udata['ID'] = $user->data->ID;
                $udata['user_pass'] = $passdata['new_password'];
                $uid = wp_update_user( $udata );
                if($uid) 
                {
                    $passupdatemsg = "The password has been updated successfully";
                    $passupdatetype = 'successed';
                    unset($passdata);
                } else {
                    $passupdatemsg = "Sorry! Failed to update your account details.";
                    $passupdatetype = 'errored';
                }
            }
            else
            {
                $passupdatemsg = "Confirm password doesn't match with new password";
                $passupdatetype = 'errored';
            }
        }
        else
        {
            $passupdatemsg = "Please enter new password and confirm password";
            $passupdatetype = 'errored';
        }
    } 
    else 
    {
        $passupdatemsg = "Old Password doesn't match the existing password";
        $passupdatetype = 'errored';
    }
    //code for reset password
 }

// user update End

// GETTING USER ID AND ASN NUMBER
 $asn_field = get_user_meta ($current_user->ID,'asn',true);  
// GETTING USER ID AND ASN NUMBER eND
$User_location = get_user_meta($current_user->ID,'user_location',true);  


// CHECK CONDITION 
/* check if form was posted and returnd back with errors */
if(isset($_SESSION['cmc_form_resp'])) {
    extract($_SESSION['cmc_form_resp']);
    extract($_POST);
    unset($_SESSION['cmc_form_resp']);
}

	if(!is_user_logged_in()) { ?>		
		<?php

		//login form
			$login_username = '';
			$login_password = '';
			$remember = false;
			/* if ( ! empty( $_POST['login'] ) ) {
				$credentials = [];
				$credentials['user_login']        = htmlentities(strip_tags(trim($_POST['login_username'])));
				$credentials['user_password']    = htmlentities(strip_tags(trim($_POST['login_password'])));
				$credentials['remember']    = isset($_POST['remember']) ? true : false;
                switch_to_blog( 1 );
                    $if_logged_in = wp_signon($credentials, true);
                restore_current_blog();
				//print_r($if_logged_in);
				//exit;
				if ( is_wp_error( $if_logged_in  ) ) {
                    $error_message = $if_logged_in->get_error_message();
				    extract($_POST);
				} else {
					wp_redirect('/');
				}
			} */


			// Register form
			/*$reg_f_name = '';
			$reg_l_name = '';
			$reg_email = '';
			$reg_password = '';*/
			if ( ! empty( $_POST['register'] ) ) {					

				/*$username        = htmlentities(strip_tags(trim($_POST['reg_email'])));
				$user_first_name        = htmlentities(strip_tags(trim($_POST['reg_f_name'])));
				$userlastname    = htmlentities(strip_tags(trim($_POST['reg_l_name'])));
				$useremail       = htmlentities(strip_tags(trim($_POST['reg_email'])));					
				$user_password   = htmlentities(strip_tags($_POST['reg_password']));
				$user_location   = htmlentities(strip_tags($_POST['user_location']));*/
				
				
			// sending to user profile 
				/*global $wpdb;
				$userdata = array(
					'user_login'     => $username,
					'first_name' => $user_first_name,
					'last_name' => $userlastname,
					'user_email' => $useremail,
				    'user_pass'  => $user_password, 
				    // role: default role is set to be composer
				); */
				// print_r($userdata);
				//switch_to_blog( 1 );

				/*$if_logged_in =  wp_insert_user( $userdata ) ;
				$user_id = $if_logged_in;*/
				/*if ( is_wp_error( $if_logged_in  ) ) {
                    $error_message = $if_logged_in->get_error_message();
				    $error_message = "This Email Already Exists";
				    extract($_POST);
				} else {
					//wp_update_user( array ('ID' => $user_id, 'role' => 'composer') ) ;
					//$user = new WP_User( $user_id );
					//$user->set_role( "composer" );
					update_user_meta($user_id, 'wp_capabilities', array('composer' => 1));
					update_user_meta( $if_logged_in, 'user_status', 0);
					update_user_meta( $if_logged_in, 'user_location', $user_location);
					$success_message = "Your account has been created.";
					  //$to = get_bloginfo('admin_email');
				      $to = 'c2nadeem@gmail.com';
				      $subject = 'New Associate Composer Registration';
				      $bod = '';
				      $bod .= 'Greetings admin,<br>';
				      $bod .= '<p>A new user has registered an associate composer account.  Please verify if this user is already a registered CMC member and activate by adding their respective ASN in the ASN field of their user account.</p>';
				      $bod .= '<p><strong>Details below:</strong></p>';
				      $bod .= '<p><strong>First Name:</strong> '.$user_first_name.' '.$userlastname.'</p>';
				      $bod .= '<p><strong>Email:</strong> '.$useremail.'</p>';
				      $bod .= '<p><strong>Desired Location:</strong> '.$user_location.'</p>';
				      $bod .= '<p><br>https://comport.cmccanada.org/wp-admin/user-edit.php?user_id='.$user_id.'</p>';
				      $headers = array('Content-Type: text/html; charset=UTF-8');
                      wp_mail( $to, $subject, $bod, $headers );
                      $creds = array();
                      $creds['user_login'] = $username;
                      $creds['user_password'] = $user_password;
                      $creds['remember'] = true;
                      $user = wp_signon( $creds, true );
					//wp_set_current_user($user_id);
			        //wp_set_auth_cookie($user_id);
			        //$user = get_user_by( 'id', $user_id );
			        //do_action( 'wp_login', $user->user_login, $user );//`[Codex Ref.][1] 
					//wp_redirect( '/' );
					//exit;
				    
				}*/
				//restore_current_blog();
				// print_r($userdata);
				?>				
				
				<?php
			}  //registration end
		?>
		<div class="container form_reg">
			<?php if(isset($error_message)) { ?>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-danger">
							<?php echo $error_message; ?>
						</div>
					</div>
				</div>
			<?php } ?>
			<?php if(isset($success_message)) { ?>
				<div class="row">
					<div class="col-md-12">
						<div class="alert alert-success">
							<?php echo $success_message; ?>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="row">
				<div class="col-md-6 login_form">
					<h2><?php echo __('Login') ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="lang" value="<?php echo get_locale()?>">
                    <input type="hidden" name="action" value="cmc_comport_login">
                    <?php wp_nonce_field( $action = 'cmc_comport_login', $name = 'cmc_comport_login_wpnonce', true, true ) ?>
					<p>
						<label><?php echo __('Username or Email Address', 'Avada') ?>*</label>
						<input type="text" name="login_username" required="" value="<?php echo $login_username; ?>">
					</p>
					<p>
						<label><?php echo __('Password') ?>*</label>
						<input type="password" name="login_password" required="" value="<?php echo $login_password; ?>">
					</p>
					
					<p>
						<input type="submit" name="login" value="<?php echo __('Login') ?>"> <input type="checkbox" name="remember" value="true" <?php echo $remember == true ? 'checked' : '' ?>>
						<span class="rememebr"><?php echo __('Remember me') ?> </span><span class="lost_password"><a href="https://comport.cmccanada.org/wp-login.php?action=lostpassword"><?php echo __('Lost your password?') ?></a></span>
					</p>
					</form>	
				</div>
				<div class="col-md-6 register_form">
					<h2><?php echo __('Register') ?></h2>
					<?php $lang = get_locale() == 'fr_FR' ? 'fr': 'en'; ?>
					<div class="header_msg">
						<img src="/wp-content/uploads/sites/10/2019/04/associate-composer-<?php echo $lang; ?>.jpg">
						<?php echo __('We invite you to join our 900+ strong body of active Canadian composers.'); ?>
					</div>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="cmc_comport_register">
                        <input type="hidden" name="lang" value="<?php echo get_locale()?>">
                        <?php wp_nonce_field( $action = 'cmc_comport_register', $name = 'cmc_comport_register_wpnonce', true, true ) ?>
						<input type="hidden" name="comport_register" value="true">
						<p>
					      <label><?php echo __('First Name'); ?>*</label>
					      <input type="text" name="reg_f_name" required="" value="<?php echo $reg_f_name; ?>">
					    </p>
					    <p>
					      <label><?php echo __('Last Name'); ?>*</label>
					      <input type="text" name="reg_l_name" required="" value="<?php echo $reg_l_name; ?>">	    
					     </p>
					     <p>
					      <label><?php echo __('Email Address'); ?>*</label>
					      <input type="email" name="reg_email" required="" value="<?php echo $reg_email; ?>">
					     </p>
					     <p>
					      	<label><?php echo __('Desired Location'); ?>*</label>
					     	<select name="user_location" class="location" required="" style="width: 100%";>
								<option class=""><?php echo __('Select'); ?></option>
								<option value="Toronto"><?php echo __('Toronto'); ?></option>
								<option value="Montreal"><?php echo __('Montreal'); ?></option>
							</select> 
					     </p>
					     <p>
					      <label><?php echo __('Password') ?>*</label>
					      <input type="password" name="reg_password" required="" value="<?php echo $reg_password; ?>">
					  	</p>
					  	<p><?php echo do_shortcode('[bws_google_captcha]'); ?></p>
					      <input type="submit" name="register" value="<?php echo __('Apply Today') ?>">						
					</form>
				</div>
			</div>
		</div>
<?php 
	} else {
?>
	<section id="content" <?php Avada()->layout->add_style( 'content_style' ); ?>>
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php echo fusion_render_rich_snippets_for_pages(); // WPCS: XSS ok. ?>
			<?php if ( ! post_password_required( $post->ID ) ) : ?>
				<?php if ( Avada()->settings->get( 'featured_images_pages' ) ) : ?>
					<?php if ( 0 < avada_number_of_featured_images() || get_post_meta( $post->ID, 'pyre_video', true ) ) : ?>
						<div class="fusion-flexslider flexslider post-slideshow">
							<ul class="slides">
								<?php if ( get_post_meta( $post->ID, 'pyre_video', true ) ) : ?>
									<li>
										<div class="full-video">
											<?php echo apply_filters( 'privacy_iframe_embed', get_post_meta( $post->ID, 'pyre_video', true ) ); // WPCS: XSS ok. ?>
										</div>
									</li>
								<?php endif; ?>
								<?php if ( has_post_thumbnail() && 'yes' != get_post_meta( $post->ID, 'pyre_show_first_featured_image', true ) ) : ?>
									<?php $attachment_data = Avada()->images->get_attachment_data( get_post_thumbnail_id() ); ?>
									<?php if ( is_array( $attachment_data ) ) : ?>
										<li>
											<a href="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" data-rel="iLightbox[gallery<?php the_ID(); ?>]" title="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>" data-title="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>" data-caption="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>">
												<img src="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" alt="<?php echo esc_attr( $attachment_data['alt'] ); ?>" role="presentation" />
											</a>
										</li>
									<?php endif; ?>
								<?php endif; ?>
								<?php $i = 2; ?>
								<?php while ( $i <= Avada()->settings->get( 'posts_slideshow_number' ) ) : ?>
									<?php $attachment_new_id = fusion_get_featured_image_id( 'featured-image-' . $i, 'page' ); ?>
									<?php if ( $attachment_new_id ) : ?>
										<?php $attachment_data = Avada()->images->get_attachment_data( $attachment_new_id ); ?>
										<?php if ( is_array( $attachment_data ) ) : ?>
											<li>
												<a href="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" data-rel="iLightbox[gallery<?php the_ID(); ?>]" title="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>" data-title="<?php echo esc_attr( $attachment_data['title_attribute'] ); ?>" data-caption="<?php echo esc_attr( $attachment_data['caption_attribute'] ); ?>">
													<img src="<?php echo esc_url_raw( $attachment_data['url'] ); ?>" alt="<?php echo esc_attr( $attachment_data['alt'] ); ?>" role="presentation" />
												</a>
											</li>
										<?php endif; ?>
									<?php endif; ?>
									<?php $i++; ?>
								<?php endwhile; ?>
							</ul>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; // Password check. ?>


			<?php 

				// check condition if ASN set
			
					if (empty($asn_field)) {?>
						<div class="header_wrapper_for_all">
							<div class="container">
								<div class="row">
									<?php
									// User Header Information
									echo $header_user_information = '<div class="col-sm-4"><span> '.__('Hello','Avada').' </span>'. '<span class="names">'.$current_user->display_name.'</span></br>(<span class="not">'.__('not').' </span>'. $current_user->display_name.'? <a href='.wp_logout_url(home_url()).'>'.__('Sign Out').'</a>)</div>';

									echo $header_infor_number = '<div class="col-sm-4"><label> '.__('Customer Service','Avada').':</label></br> <span>1-416-961-6601 (M-F 9-5)</span></div>';
									echo $header_infor_email = '<div class="col-sm-4"><label> '.__('Service Email','Avada').':</label> <span> info@cmccanada.org </span></div>';
									//echo $header_infor_cart = '<div class="col-sm-3"><a href="#">'.__('View Cart','Avada').'</a> </div>';?>
								</div>
							</div>
						</div>
						<!-- CUSTOM TABS -->
						<div class="container for_compp">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#dashboard"><?php echo __('Dashboard','Avada'); ?></a></li>
								<li><a data-toggle="tab" href="#detailz"><?php echo __('Account Details','Avada'); ?></a></li>								
							</ul>

							<div class="tab-content">
								<div id="dashboard" class="tab-pane fade in active">
									<h3><?php echo __('Dashboard','Avada'); ?></h3>
									<!-- GETING PAGE DATA -->
									<?php $the_query = new WP_Query( 'page_id=262' ); ?>
									<?php while ($the_query -> have_posts()) : $the_query -> the_post();  ?>
										<?php the_content(); ?>
									<?php endwhile;
									// <!-- GETING PAGE DATA END -->
									?>									
								</div>
								<div id="detailz" class="tab-pane fade">
									<h3><?php echo __('Account Details','Avada'); ?></h3>
									<form method="post" action="" id="update_user_data_form">
										<p class="locations"><?php echo __('Desired Location','Avada') ?></p>
											<select name="user_location" class="location">
												<option class="defaults"><?php echo __('Select'); ?></option> 
												<option value="Toronto" <?php if($User_location=="Toronto") echo "selected=selected" ?>><?php echo __('Toronto'); ?></option>
												<option value="Montreal" <?php if($User_location=="Montreal") echo "selected=selected" ?>><?php echo __('Montreal'); ?></option>									
											</select> 
										<input type="hidden" name="current_u_id" value="<?php echo $current_user->ID;?>">
										<label><?php echo __('First Name'); ?>*</label>
										<input type="text" name="user_first_name" value="<?php echo $current_user->user_firstname;?>">
										<label><?php echo __('Last Name'); ?>*</label>
										<input type="text" name="user_last_name" value="<?php echo $current_user->user_lastname;?>">
										<label><?php echo __('Display Name'); ?>*</label>
										<input type="text" name="user_disply_name" value="<?php echo $current_user->display_name;?>">
										<label><?php echo __('Email Address'); ?>*</label>
										<input type="text" name="user_email" value="<?php echo $current_user->user_email;?>">
										<label><?php echo __('Current password'); ?> (<?php echo __('leave blank to leave unchanged'); ?>)</label>
										<input type="password" name="old_password">	
										<label><?php echo __('New password'); ?> (<?php echo __('leave blank to leave unchanged'); ?>)</label>
										<input type="password" name="new_password">	
										<label><?php echo __('Confirm new password'); ?></label>
										<input type="password" name="confirm_password">	
										<input type="submit" name="update_user" value="<?php echo __('Save Changes'); ?>">
									</form>
								</div>
							</div>
						</div>
						<!-- CUSTOM TABS ENDs-->

						<?php } else {?>

						<div class="header_wrapper_for_all">
							<div class="container">
								<div class="row">
									<?php

									// User Header Information
									echo $header_user_information = '<div class="col-sm-4"><span> '.__('Hello').' </span>'.'<span class="names">' .$current_user->display_name.'</span></br>(<span class="not">'.__('not').' </span>'.  $current_user->display_name.'? <a href='.wp_logout_url(home_url()).'>'.__('Sign Out').'</a>)</div>';

									echo $header_infor_number = '<div class="col-sm-4"><label> '.__('Customer Service').':</label></br> <span>1-416-961-6601 (M-F 9-5)</span></div>';
									echo $header_infor_email = '<div class="col-sm-4"><label> '.__('Service Email').':</label> <span> info@cmccanada.org </span></div>';
									//echo $header_infor_cart = '<div class="col-sm-3"><a href="#">View Cart</a> </div>';?>
								</div>
							</div>
						</div>
						<!-- CUSTOM TABS -->
						<div class="container for_comp">
							<ul class="nav nav-tabs">
								<li class="active"><a data-toggle="tab" href="#dash"><?php echo __('Dashboard') ?></a></li>		
								<li><a data-toggle="tab" href="#detail"><?php echo __('Account Details'); ?></a></li>								
							</ul>

							<div class="tab-content">
								<div id="dash" class="tab-pane fade in active">	
									<h3><?php echo __('Dashboard') ?></h3>								
									<?php 
										$usr  = wp_get_current_user();
										$user_id = $usr->ID;
										$meta = get_user_meta($user_id,'auth_hash');
										$auth_hash = (is_array($meta) && isset($meta[0])) ? $meta[0] : null;
										if($auth_hash == null) {
											$auth_hash = md5($user_id.'_'.time());
											update_user_meta($user_id,'auth_hash',$auth_hash);
										}
										/*$meta = get_user_meta($user_id,'asn');
										$asn = (is_array($meta) && isset($meta[0])) ? $meta[0] : '';*/
										
										$cont = get_the_content(); 
										$cont = str_replace('{{hash}}', '?hash='.$auth_hash, $cont);
										echo do_shortcode($cont);
									 //}?>									
								</div>
								<div id="detail" class="tab-pane fade">
									<h3><?php echo __('Account Details') ?></h3>
								<form method="post" action="" id="update_user_data_form">
									<p class="locations"><?php echo __('Desired Location') ?></p>
										<select name="user_location" class="location">
											<option class="defaults"><?php echo __('Select'); ?></option> 
											<option value="Toronto" <?php if($User_location=="Toronto") echo "selected=selected" ?>><?php  echo __('Toronto') ?></option>
											<option value="Montreal" <?php if($User_location=="Montreal") echo "selected=selected" ?>><?php  echo __('Montreal') ?></option>									
										</select> 
									<input type="hidden" name="current_u_id" value="<?php echo $current_user->ID;?>">
									<label><?php echo __('First Name') ?>*</label>
									<input type="text" name="user_first_name" value="<?php echo $current_user->user_firstname;?>">
									<label><?php echo __('Last Name'); ?>*</label>
									<input type="text" name="user_last_name" value="<?php echo $current_user->user_lastname;?>">
									<label><?php echo __('Display Name'); ?>*</label>
									<input type="text" name="user_disply_name" value="<?php echo $current_user->display_name;?>">
									<label><?php echo __('Email Address'); ?>*</label>
									<input type="text" name="user_email" value="<?php echo $current_user->user_email;?>">
									<label><?php echo __('Current password'); ?> (<?php echo __('leave blank to leave unchanged'); ?>)</label>
									<input type="password" name="old_password">	
									<label><?php echo __('New password'); ?> (<?php echo __('leave blank to leave unchanged'); ?>)</label>
									<input type="password" name="new_password">	
									<label><?php echo __('Confirm new password'); ?></label>
									<input type="password" name="confirm_password">	
									<input type="submit" name="update_user" value="<?php echo __('Save Changes'); ?>">
								</form>
								</div>								
							</div>
						</div>
						<?php } ?>
						<!-- CUSTOM TABS END-->
					<!-- CHECK END -->

			<div class="post-content">
				<?php //the_content(); ?>
				<?php //fusion_link_pages(); ?>
			</div>
			

			<?php if ( ! post_password_required( $post->ID ) ) : ?>
				<?php do_action( 'avada_before_additional_page_content' ); ?>
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<?php $woo_thanks_page_id = get_option( 'woocommerce_thanks_page_id' ); ?>
					<?php $is_woo_thanks_page = ( ! get_option( 'woocommerce_thanks_page_id' ) ) ? false : is_page( get_option( 'woocommerce_thanks_page_id' ) ); ?>
					<?php if ( Avada()->settings->get( 'comments_pages' ) && ! is_cart() && ! is_checkout() && ! is_account_page() && ! $is_woo_thanks_page ) : ?>
						<?php wp_reset_postdata(); ?>
						<?php comments_template(); ?>
					<?php endif; ?>
				<?php else : ?>
					<?php if ( Avada()->settings->get( 'comments_pages' ) ) : ?>
						<?php wp_reset_postdata(); ?>
						<?php comments_template(); ?>
					<?php endif; ?>
				<?php endif; ?>
				<?php do_action( 'avada_after_additional_page_content' ); ?>
			<?php endif; // Password check. ?>
		</div>
	<?php endwhile; ?>
	<?php wp_reset_postdata(); ?>
</section>
<?php do_action( 'avada_after_content' ); ?>
<?		
	}
get_footer();
/* Omit closing PHP tag to avoid "Headers already sent" issues. */
 
if(empty($asn_field)) {?>
	
	<style type="text/css">
		.fusion-secondary-main-menu {
			display: none;
		}
	</style>
<?php }
else
{?>
<style type="text/css">
		.fusion-secondary-main-menu {
			display: block;
		}
		body .fa, body .fas {
		    font-family: 'Font Awesome 5 Free';
		    font-weight: 900;
		}
	</style>

<?php echo '';}

?>

<script>
	(function($){
		$(document).ready(function() {
		$("select option:first-child").attr("disabled", "true");			
		});	
	})(jQuery);
</script>