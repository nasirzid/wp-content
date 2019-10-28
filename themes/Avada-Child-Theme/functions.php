<?php
echo 'gtest';
add_action( 'wpcf7_before_send_mail', 'process_contact_form_data' );
function process_contact_form_data( $contact_data ){

    //var_dump($contact_data->posted_data);
}
//   //////  adding Custom ASN feild
function custom_user_profile_fields($user){ 
    $previous_value = '';
    if( is_object($user) && isset($user->ID) ) {
        $previous_value = get_user_meta( $user->ID, 'asn', true );
    }
    ?>
    <h3>User ASN</h3>
    <table class="form-table">
        <tr>
            <th><label for="asn">ASN</label></th>
            <td>
                <input type="text" class="regular-text" name="asn" value="<?php echo esc_attr( $previous_value ); ?>" id="asn" /><br />
                <span class="description">Please Add ASN Input</span>
            </td>
        </tr>
    </table>
<?php
}
add_action( 'show_user_profile', 'custom_user_profile_fields' );
add_action( 'edit_user_profile', 'custom_user_profile_fields' );
add_action( "user_new_form", "custom_user_profile_fields" );

function save_custom_user_profile_fields($user_id){

    if(!current_user_can('asn'))
        return false;

    # save my custom field
    if( isset($_POST['asn']) ) {
        update_user_meta( $user_id, 'asn', sanitize_text_field( $_POST['asn'] ) );
    } else {
        //Delete the asn field if $_POST['asn'] is not set
        delete_user_meta( $user_id, 'asn', $meta_value );
    }
}
add_action('user_register', 'save_custom_user_profile_fields');
add_action( 'personal_options_update', 'save_custom_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_custom_user_profile_fields' );

//   //////  End here

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css?version=01', array( 'avada-stylesheet' ) );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

/**
* Hides the product's weight and dimension in the single product page.
*/
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

/**
* Replaces product placeholder image.
*/
//add_action( 'init', 'custom_fix_thumbnail' );
 
function custom_fix_thumbnail() {
  add_filter('woocommerce_placeholder_img_src', 'custom_woocommerce_placeholder_img_src');
   
	function custom_woocommerce_placeholder_img_src( $src ) {
	$upload_dir = wp_upload_dir();
	$uploads = untrailingslashit( $upload_dir['baseurl'] );
	$src = $uploads . '/2017/06/sheet-music-global.jpg';
	 
	return $src;
	}
}

/*
 * Hide end time in list, map, photo, and single event view
 * NOTE: This will only hide the end time for events that end on the same day
 */
function tribe_remove_end_time_single( $formatting_details ) {
	$formatting_details['show_end_time'] = 0;
	return $formatting_details;
}
add_filter( 'tribe_events_event_schedule_details_formatting', 'tribe_remove_end_time_single', 10, 2);
/*
 * Hide end time in Week and Month View Tooltips
 * NOTE: This will hide the end time in tooltips for ALL events, not just events that end on the same day
 */
function tribe_remove_end_time_tooltips( $json_array, $event, $additional ) {
	$json_array['endTime'] = '';
	return $json_array;
}
add_filter( 'tribe_events_template_data_array', 'tribe_remove_end_time_tooltips', 10, 3 );
/*
 * Hide endtime for multiday events
 * Note: You will need to uncomment this for it to work
 */
function tribe_remove_endtime_multiday ( $inner, $event ) {
	if ( tribe_event_is_multiday( $event ) && ! tribe_event_is_all_day( $event ) ) {
		$format                   = tribe_get_date_format( true );
		$time_format              = get_option( 'time_format' );
		$format2ndday             = apply_filters( 'tribe_format_second_date_in_range', $format, $event );
		$datetime_separator       = tribe_get_option( 'dateTimeSeparator', ' @ ' );
		$time_range_separator     = tribe_get_option( 'timeRangeSeparator', ' - ' );
		$microformatStartFormat   = tribe_get_start_date( $event, false, 'Y-m-dTh:i' );
		$microformatEndFormat     = tribe_get_end_date( $event, false, 'Y-m-dTh:i' );
		$inner = '<span class="date-start dtstart">';
		$inner .= tribe_get_start_date( $event, false, $format ) . $datetime_separator . tribe_get_start_date( $event, false, $time_format );
		$inner .= '<span class="value-title" title="' . $microformatStartFormat . '"></span>';
		$inner .= '</span>' . $time_range_separator;
		$inner .= '<span class="date-end dtend">';
		$inner .= tribe_get_end_date( $event, false, $format2ndday );
		$inner .= '<span class="value-title" title="' . $microformatEndFormat . '"></span>';
		$inner .= '</span>';
	}
	return $inner;
}
//add_filter( 'tribe_events_event_schedule_details_inner', 'tribe_remove_endtime_multiday', 10, 3 );

//grant_super_admin(1);
// **** user new role
add_role(
    'composer',
    __( 'Composer' ),
    array(
        'read'         => true,  // true allows this capability
        'edit_posts'   => true,
    )
);

// ************ Login User after Log Out Automatically
/*add_action('wp_logout','auto_redirect_after_logout');
function auto_redirect_after_logout(){
wp_redirect( 'https://comport.dev-cmccanada.com/');
exit();
}*/
// Hide admin bar When user login as User
//add_filter('show_admin_bar', '__return_false');
add_filter('show_admin_bar', 'show_admin_bar_for_admin');
function show_admin_bar_for_admin() {
    
    if(current_user_can('manage_options'))
        return true;
    return false;
}

// ************ Login User after Registrator Automatically
function auto_login_new_user( $user_id ) {
  /*  $to = 'c2nadeem@gmail.com';
  $subject = 'test user register email '.$user_id;
  $bod = 'this is test email for user register';
  wp_mail( $to, $subject, $bod );*/
  
    //if(get_current_blog_id() == 1) {
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        $user = get_user_by( 'id', $user_id );
        do_action( 'wp_login', $user->user_login, $user );//`[Codex Ref.][1]
    
        $url = get_current_blog_id() == 10 ? 'https://comport.cmccanada.org/my-account': 'https://cmccanada.org/my-account';
        wp_redirect( $url ); 
    //}
    //exit;
}
add_action( 'user_register', 'auto_login_new_user' );

// ----- validate password match on the registration page


/*// ----- add a confirm password fields match on the registration page
function wc_register_form_password_repeat() {
    ?>
    <p class="form-row form-row-wide">
        <label for="reg_password2"><?php _e( 'Password Repeat', 'woocommerce' ); ?> <span class="required">*</span></label>
        <input type="password" class="input-text" name="password2" id="reg_password2" value="<?php if ( ! empty( $_POST['password2'] ) ) echo esc_attr( $_POST['password2'] ); ?>" />
    </p>
    <?php
}
add_action( 'woocommerce_register_form', 'wc_register_form_password_repeat' );*/



/**
 * Add the code below to your theme's functions.php file
 * to add a confirm password field on the register form under My Accounts.
 */ 
/*function woocommerce_registration_errors_validation($reg_errors, $sanitized_user_login, $user_email) {
    global $woocommerce;
    extract( $_POST );
    if ( strcmp( $password, $password2 ) !== 0 ) {
        return new WP_Error( 'registration-error', __( 'Passwords do not match.', 'woocommerce' ) );
    }
    return $reg_errors;
}
add_filter('woocommerce_registration_errors', 'woocommerce_registration_errors_validation', 10, 3);
function woocommerce_register_form_password_repeat() {
    ?>
    <p class="form-row form-row-wide">
        <label for="reg_password"><?php _e( 'password', 'woocommerce' ); ?> <span class="required">*</span></label>
        <input type="password" class="input-text" name="password" id="reg_password" value="<?php if ( ! empty( $_POST['password'] ) ) echo esc_attr( $_POST['password'] ); ?>" />
    </p>
    <p class="form-row form-row-wide">
        <label for="reg_password2"><?php _e( 'Confirm password', 'woocommerce' ); ?> <span class="required">*</span></label>
        <input type="password" class="input-text" name="password2" id="reg_password2" value="<?php if ( ! empty( $_POST['password2'] ) ) echo esc_attr( $_POST['password2'] ); ?>" />
    </p>
    <?php
}
add_action( 'woocommerce_register_form', 'woocommerce_register_form_password_repeat' );*/

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 31 );
function woocommerce_template_single_excerpt() {
    global $post, $woocommerce;
    $user = wp_get_current_user();
    $user_id = $user->ID;
    $show_product = true; 
    /* remove add to cart button if product is centretracks*/
    
    echo '<ul class="retailers_list">';
        $itune = get_field('digital_downloads_itunes', $post->ID);
        $google_play = get_field('digital_downloads_google_play', $post->ID);
        $amazon = get_field('digital_downloads_amazon', $post->ID);
        if($itune != '') {
            $show_product = false; 
            ?>
                <li>
                    <a href="<?php echo $itune ?>"><img src="<?php echo site_url('/wp-content/uploads/2017/06/itunes-badge.png') ?>"></a>
                </li>
           <?php 
        }
        if($google_play != '') {
            $show_product = false; 
            ?>
                <li>
                    <a href="<?php echo $google_play ?>"><img src="<?php echo site_url() ?>/wp-content/uploads/2017/06/google-play-badge.png"></a>
                </li>
           <?php 
        }
        if($amazon != '') {
            $show_product = false; 
            ?>
                <li>
                    <a href="<?php echo $amazon ?>"><img src="<?php echo site_url() ?>/wp-content/uploads/2017/06/amazon-badge.png"></a>
                </li>
           <?php 
        }
    echo '</ul>';
    //if($show_product == false) {
        /* hide produt if any of this is */
        add_filter('woocommerce_is_purchasable', 'woocommerce_cloudways_purchasable', 2, 2);
    //}

    $rsn = $post->post_name;
    $post_title = $post->post_title;
    $post_slug = get_permalink($post->ID);
    
    $call_number = get_field('call_number', $post->ID);
    $if_call_number = strpos($call_number, 'MI 1');
    $call_number_len = strlen($call_number);
    if($if_call_number !== false) {
        $email = is_user_logged_in() ? $user->user_email: '';
        $fields = [];
        $fields['p_name'] = get_user_meta( $user_id, 'billing_first_name', true ).' '.get_user_meta( $user_id, 'billing_last_name', true );
        $fields['p_organization'] =  get_user_meta( $user_id, 'billing_company', true );
        $fields['p_address'] =  get_user_meta( $user_id, 'billing_address_1', true ).' '.get_user_meta( $user_id, 'billing_address_2', true );
        $fields['p_city'] =  get_user_meta( $user_id, 'billing_city', true );
        $fields['p_province'] =  get_user_meta( $user_id, 'billing_state', true );
        $fields['p_postal'] =  get_user_meta( $user_id, 'billing_postcode', true );
        $fields['p_country'] =  get_user_meta( $user_id, 'billing_country', true );
        $fields['p_phone'] =  get_user_meta( $user_id, 'billing_phone', true );
        $fields['p_email'] =  $email;
        $fields['p_rsn_number'] =  $rsn;
        $fields['p_post_title'] =  $post_title;
        $fields['p_post_slug'] =  $post_slug;
        $fields = json_encode($fields);
        //print_r($user);
        //$first_name = $woocommerce->customer->get_billing_first_name();
        $parts = get_locale() == 'fr_FR' ? "Location des parties – " : 'Parts available for rent.';
        $estimate = get_locale() == 'fr_FR' ? "cliquez ici pour obtenir une estimation des coûts" : "Request estimate";
        ?>
        
        
        <p><?php echo $parts ?>
        <a class="request_product_estimate" data-info='<?php echo $fields ?>' ><?php echo $estimate ?></a>
        <div class="request_form_overlay">
            <div class="request_form_wrapper">
                <div class="request_form_close">X</div>
                <div class="product_info"></div>
                <?php echo do_shortcode('[contact-form-7 id="66149" title="Rental Request"]'); ?>
            </div>
        </div>
        <?php
        
    }

}
function cmc_init_callback(){
    if(!session_id()) {
        session_start();
    }
}
add_action('init','cmc_init_callback');

function db_remove_new_site_notification_email( $blog_id, $user_id, $password, $title, $meta ) {
return false;
}
add_filter( 'wpmu_welcome_notification', 'db_remove_new_site_notification_email' );

add_action( 'profile_update', 'add_info_to_post' ); 
function add_info_to_post( $user_id ) {
    $post = $_POST;
    if($post['action'] == 'save_account_details' || isset($post['update_user'])) {
        global $wpdb;
        if(isset($post['update_user'])) {
            $full_name = sanitize_text_field($post['user_disply_name']);
            $user_email = sanitize_text_field($post['user_email']);
            if($post['new_password'] != '' && $post['new_password'] == $post['confirm_password']) {
                $new_pass = sanitize_text_field($post['new_password']);
            }
            
        } else {
            $fname = sanitize_text_field($post['account_first_name']);
            $lname = sanitize_text_field($post['account_last_name']);
            $full_name = $fname.' '.$lname;
            if(empty($full_name)) {
                $fname = sanitize_text_field($post['billing_first_name']);
                $lname = sanitize_text_field($post['billing_last_name']);
                $full_name = $fname.' '.$lname;
            }
            $user_email = sanitize_text_field($post['account_email']);
            if($post['password_1'] != '' && $post['password_1'] == $post['password_2']) {
                $new_pass = sanitize_text_field($post['password_1']);
            }    
        }
        $billing_address_1 = '';
      $billing_city = '';
      $billing_country = '';
      $billing_state = '';
      $billing_postcode = '';
      $billing_phone = '';
      if(isset($_POST['billing_address_1'])) {
        $billing_address_1 = sanitize_text_field($_POST['billing_address_1']);
      }
      if(isset($_POST['billing_city'])) {
        $billing_city = sanitize_text_field($_POST['billing_city']);
      }
      if(isset($_POST['billing_country'])) {
        $billing_country = sanitize_text_field($_POST['billing_country']);
      }
      if(isset($_POST['billing_state'])) {
        $billing_state = sanitize_text_field($_POST['billing_state']);
      }
      if(isset($_POST['billing_postcode'])) {
        $billing_postcode = sanitize_text_field($_POST['billing_postcode']);
      }
      if(isset($_POST['billing_phone'])) {
        $billing_phone = sanitize_text_field($_POST['billing_phone']);
      }
        
        $wpdb->insert( 
          'wp_users_xml', 
          array( 
            'name' => $full_name, 
            'email' => $user_email, 
            'billing_address_1' => $billing_address_1, 
            'billing_city' => $billing_city, 
            'billing_country' => $billing_country, 
            'billing_state' => $billing_state, 
            'billing_postcode' => $billing_postcode, 
            'billing_phone' => $billing_phone, 
            'password' => $new_pass,
            'user_id' => $user_id,
            'ported_status' => 0,
          ), 
          array( 
            '%s', 
            '%s', 
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d'
          ) 
        );
        $updated = $wpdb->update( 'wp_users_xml', $data,  $where);
            
        
        
    }   
        
}

/**/
add_filter('woocommerce_is_purchasable', 'woocommerce_cloudways_purchasable', 2, 2);

function woocommerce_cloudways_purchasable($cloudways_purchasable, $product) {
    //return ($product->id == 66026) ? false : $cloudways_purchasable;
    $ret = true;
    $id  =  $product->get_id();
    $visibility = get_field('cart_button_visibility', $id);
    
    /*$terms = get_the_terms( $id, 'product_cat' , ['slug' => 'centretracks']);
    if(count($terms) > 0) {
        foreach($terms as $term) {
            if($term->slug == 'centretracks'){
                return false;
                break;
            }
        }
    }*/
    
    if($visibility == 'No') {
        $ret = false;
    } else {
        $ret = true;
    }
    /* elseif(has_term('centretracks', 'product_cat', $id)) {
        $ret = false;
    }  */
    
    return $ret;
    
    //echo $product->id;
    /*echo $itune = get_field('digital_downloads_itunes', $product->id);
    echo $google_play = get_field('digital_downloads_google_play', $product->id);
    echo $amazon = get_field('digital_downloads_amazon', $product->id);*/
    
}

    
/**
 * Remove password strength check.
 */
function cmc_remove_password_strength() {
    wp_dequeue_script( 'wc-password-strength-meter' );
}
add_action( 'wp_print_scripts', 'cmc_remove_password_strength', 10 );

/*
add_action(
  'login_url',
  function($url)
    return str_replace('wp-login.php','mypage',$url);
  }
);
*/


add_action('wp_head', 'beta_launch_bar_callback');
function beta_launch_bar_callback() {
    ?>
    <div class="launch_bar_wrapper">
        <div class="launch_bar_overlay"></div>
        <div class="launch_bar_body">
            <div class="launch_bar_container">
                <?php if(get_locale() == 'fr_FR') {
                    echo '<p> Bienvenue au lancement en version bêta du nouveau site Web du CMC. Nous ajoutons de <a href="https://cmccanada.org/feature-requests/"> nouvelles fonctionnalités</a> chaque jour! <a class="provide_feedback_btn" href="https://cmccanada.org/feature-requests/">Vos commentaires</a></p>';
                } else {
                    echo '<p> Welcome to the Beta Launch of the new CMC Canada website. <a href="https://cmccanada.org/feature-requests/">New features</a> added every day! <a class="provide_feedback_btn" href="https://cmccanada.org/feature-requests/">Provide Feedback</a></p>';
                } 
                ?>
            </div>
        </div>
    </div>
    <?php
}

/*  handle login form  */

add_action('admin_post_cmc_comport_login', 'cmc_comport_login_callback');
add_action('admin_post_nopriv_cmc_comport_login', 'cmc_comport_login_nopriv_callback');
function cmc_comport_login_callback () {
    wp_redirect( '/' );
}
function recaptcha_validated(){
    
    if( empty( $_POST['g-recaptcha-response'] ) ) return FALSE;
    $gglcptch_options = get_option('gglcptch_options');
    $secret_key = '';
    if($gglcptch_options && isset($gglcptch_options['private_key'])) {
        $secret_key = $gglcptch_options['private_key'];
    }
    $response = wp_remote_get( add_query_arg( array(
                                              'secret'   => $secret_key,
                                              'response' => isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '',
                                              'remoteip' => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']
                                          ), 'https://www.google.com/recaptcha/api/siteverify' ) );
    if( is_wp_error( $response ) || empty($response['body']) || ! ($json = json_decode( $response['body'] )) || ! $json->success ) {
        //return new WP_Error( 'validation-error',  __('reCAPTCHA validation failed. Please try again.' ) );
        return FALSE;
    }

    return TRUE;
}
function cmc_comport_login_nopriv_callback () {
    //$locale = get_locale() == 'fr_FR' ? 'fr' : '';
    $locale = sanitize_text_field($_POST['lang']);
    $link = $locale == 'fr_FR' ? '/fr' : '/';
    
    if( isset( $_POST['cmc_comport_login_wpnonce'] ) && wp_verify_nonce( $_POST['cmc_comport_login_wpnonce'], 'cmc_comport_login') ) {
        $credentials = [];
        $credentials['user_login']        = htmlentities(strip_tags(trim($_POST['login_username'])));
        $credentials['user_password']    = htmlentities(strip_tags(trim($_POST['login_password'])));
        $credentials['remember']    = isset($_POST['remember']) ? true : false;
        //switch_to_blog( 1 );
        $if_logged_in = wp_signon($credentials, true);
        //restore_current_blog();
        //print_r($if_logged_in);
        //exit;
        if ( is_wp_error( $if_logged_in  ) ) {
            $error_message = $if_logged_in->get_error_message();
            extract($_POST);
            $data = array(
                'resp_type' => 'error',
                'error_message' => $error_message,
                '_POST' => $_POST,
            );
            $_SESSION['cmc_form_resp'] = $data;
            wp_redirect( $link );
        } else {
            wp_redirect( $link );    
        }
    } else {
        $data = array(
            'resp_type' => 'error',
            'error_message' => __('Invalid request'),
            '_POST' => $_POST,
        );
        $_SESSION['cmc_form_resp'] = $data;
        wp_redirect( $link );
    }
    
}

/*  handle register form  */

add_action('admin_post_cmc_comport_register', 'cmc_comport_register_callback');
add_action('admin_post_nopriv_cmc_comport_register', 'cmc_comport_register_nopriv_callback');
function cmc_comport_register_callback () {
    wp_redirect( '/' );
}

function cmc_comport_register_nopriv_callback () {
     
    $locale = sanitize_text_field($_POST['lang']);
    $link = $locale == 'fr_FR' ? '/fr' : '/';
    if(recaptcha_validated() === FALSE) {
        $data = array(
            'resp_type' => 'error',
            'error_message' => __('Error: You have entered an incorrect reCAPTCHA value.', 'Avada'),
            '_POST' => $_POST,
        );
        $_SESSION['cmc_form_resp'] = $data;
        wp_redirect( $link );
        exit;
    } 
    if( isset( $_POST['cmc_comport_register_wpnonce'] ) && wp_verify_nonce( $_POST['cmc_comport_register_wpnonce'], 'cmc_comport_register') ) {
        $username        = htmlentities(strip_tags(trim($_POST['reg_email'])));
        $user_first_name        = htmlentities(strip_tags(trim($_POST['reg_f_name'])));
        $userlastname    = htmlentities(strip_tags(trim($_POST['reg_l_name'])));
        $useremail       = htmlentities(strip_tags(trim($_POST['reg_email'])));					
        $user_password   = htmlentities(strip_tags($_POST['reg_password']));
        $user_location   = htmlentities(strip_tags($_POST['user_location']));
        
        
        global $wpdb;
        // prepare user insert array
        $userdata = array(
            'user_login'     => $username,
            'first_name' => $user_first_name,
            'last_name' => $userlastname,
            'user_email' => $useremail,
            'user_pass'  => $user_password, 
            // role: default role is set to be composer
        ); 
        
        $if_logged_in =  wp_insert_user( $userdata ) ;
        $user_id = $if_logged_in;
        if ( is_wp_error( $if_logged_in  ) ) {
            $error_message = $if_logged_in->get_error_message();
            //$error_message = "This Email Already Exists";
            $data = array(
                'resp_type' => 'error',
                'error_message' => $error_message,
                '_POST' => $_POST,
            );
            $_SESSION['cmc_form_resp'] = $data;
            wp_redirect( $link );
        } else {
            update_user_meta($user_id, 'wp_capabilities', array('composer' => 1));
            update_user_meta( $if_logged_in, 'user_status', 0);
            update_user_meta( $if_logged_in, 'user_location', $user_location);
            $success_message = "Your account has been created.";
            $to = get_bloginfo('admin_email');
            //$to = 'c2nadeem@gmail.com';
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
            wp_redirect( $link );
        }
    } else {
        $data = array(
            'resp_type' => 'error',
            'error_message' => __('Invalid request'),
            '_POST' => $_POST,
        );
        $_SESSION['cmc_form_resp'] = $data;
        wp_redirect( $link );    
        //wp_redirect(add_query_arg( $data , '/' ));
    }
    
}

function cmc_update_sydney_url_func($content) {
    $pattern = '~href="http://1443.sydneyplus.com/final/(.*)"~isU';
    $contents = $content;
    preg_match($pattern, $content, $matches);
    if(isset($matches[0])) {
        //print_r($matches);
        $link = str_replace('href=', '', $matches[0]);
        $link = str_replace('"', '', $link);
        $content = str_replace($link,"https://cmccanada.org/composer-showcase-redirect/",$content);
        return cmc_update_sydney_url_func($content);
    } else {
        return $contents;
    }
    
}
function cmc_update_sydney_url($content) {
  // assuming you have created a page/post entitled 'debug'
  global $post;
  if($post->post_type == 'page' || $post->post_type == 'product') {
    //print_r($post);
    return  cmc_update_sydney_url_func($content);

  }
  // otherwise returns the database content
  return $content;
}

add_filter( 'the_content', 'cmc_update_sydney_url' );

add_action( 'password_reset', 'update_xml_after_password_reset1', 10, 2 );
function update_xml_after_password_reset1($user, $new_pass) {
    wp_mail( 'nadeem.allshore@gmail.com', 'cmc password reset', 'cmc body reset text' );
    //global $wpdb;
    //$full_name = $user->display_name;
    /*if(isset($_POST['account_first_name']) && !empty($_POST['account_first_name'])) {
        $full_name = sanitize_text_field($_POST['account_first_name']).' '.sanitize_text_field($_POST['account_last_name']);
    }*/
    /*$wpdb->insert( 
      'wp_users_xml', 
      array( 
        'name' => 'nadeem i.', 
        'email' => 'c2nadeem@gmmail.com', 
        'password' => 'password',
        'user_id' => '100',
        'ported_status' => 0,
      ), 
      array( 
        '%s', 
        '%s', 
        '%s',
        '%d',
        '%d'
      ) 
    );*/
    /*$wpdb->insert( 
      'wp_users_xml', 
      array( 
        'name' => $full_name, 
        'email' => $user->user_email, 
        'password' => $new_pass,
        'user_id' => $user->ID,
        'ported_status' => 0,
      ), 
      array( 
        '%s', 
        '%s', 
        '%s',
        '%d',
        '%d'
      ) 
    );*/
    
}
//add_action( 'admin_init', 'redirect_non_admin_users' );
    /**
    * Redirect non-admin users to home page
    *
    * This function is attached to the ‘admin_init’ action hook.
    */
function redirect_non_admin_users() {
    if ( ! current_user_can( 'manage_options' ) && ('/wp-admin/admin-ajax.php' != $_SERVER['PHP_SELF']) ) {
        wp_redirect( home_url() );
        exit;
    }
}

add_filter ('wp_nav_menu_items', 'cmc_update_hash', 30, 2) ;
function cmc_update_hash ($item, $rgs) {

    $usr  = wp_get_current_user();
    if($usr) {
        $user_id = $usr->ID;
        $meta = get_user_meta($user_id,'auth_hash');
        $auth_hash = (is_array($meta) && isset($meta[0])) ? $meta[0] : null;
        if($auth_hash == null) {
            $auth_hash = md5($user_id.'_'.time());
            update_user_meta($user_id,'auth_hash',$auth_hash);
        }
        /*$meta = get_user_meta($user_id,'asn');
        $asn = (is_array($meta) && isset($meta[0])) ? $meta[0] : '';*/
        
        $item = str_replace('=hash=', '?hash='.$auth_hash, $item);
    }
    return $item;
}
add_filter ('wp_nav_menu', 'cmc_hide_for_non_composer', 30, 2) ;
function cmc_hide_for_non_composer ($item, $args) {

    /*$if_debug  = isset($_GET['if_debug']) ? true : false;
    if($if_debug == true) {
        $gglcptch_options = get_option('gglcptch_options');
        if($gglcptch_options && isset($gglcptch_options['private_key'])) {
            echo $gglcptch_options['private_key'];
        }
        
    }*/
        $blog_id = get_current_blog_id();
        if($blog_id == 10 && $args->menu->name == 'Main Menu') {
            $usr  = wp_get_current_user();
            if($usr) {
                $user_id = $usr->ID;
                $meta = get_user_meta($user_id,'asn');
                $asn = (is_array($meta) && isset($meta[0])) ? $meta[0] : '';
                if($asn == '' ||  !in_array( 'composer', (array) $usr->roles )) {
                    $item = '';
                }
            } else {
                $item = '';
            }
        }
    //}
    // to b remove
    return $item;
}


   /* add_filter( 'get_product_search_form' , 'woo_custom_product_searchform' );
    function woo_custom_product_searchform( $form ) {
        $form = '<form role="search" method="get" id="searchform" action="' . esc_url( home_url( '/'  ) ) . '">
        <div>
          <label class="screen-reader-text" for="s">' . __( 'Search for:', 'woocommerce' ) . '</label>
          <input type="text" value="' . get_search_query() . '" name="s" id="s" placeholder="' . __( 'My Search form', 'woocommerce' ) . '" />
          <input type="submit" id="searchsubmit" value="'. esc_attr__( 'Search', 'woocommerce' ) .'" />
          <input type="hidden" name="post_type" value="product" />
        </div>
      </form>';
        return $form;
    }*/
add_filter( 'posts_search', function( $search, $q )
{

    // Target all search queries in the front-end:
    if( is_admin() || ! $q->is_search() ) return $search;

    global $wpdb;

    $exclude_words = [ 'travel' ]; // <-- Modify this to your needs!

    $sql = " AND {$wpdb->posts}.post_title   LIKE '%%%s%%' 
             AND {$wpdb->posts}.post_content LIKE '%%%s%%' ";

    foreach( (array) $exclude_words as $word )
        $search .= $wpdb->prepare(
           $sql,
           $wpdb->esc_like( $word ),
           $wpdb->esc_like( $word )
        );

    return $search;
}, 20, 2 );

function cmc_update_product_search_main_query( $query ) {
    if ( isset($_GET['filter_s'])  && $query->is_main_query() ) { // Run only on the homepage
        $query->query_vars['s'] = wc_clean($_GET['filter_s']); // Exclude my featured category because I display that elsewhere
    }
}
// Hook my above function to the pre_get_posts action
add_action( 'pre_get_posts', 'cmc_update_product_search_main_query' );

if(get_current_blog_id() == 1) {

    require_once dirname( __FILE__ ) . '/widgets/class-wc-widget-layered-nav_cmc.php';
    require_once dirname( __FILE__ ) . '/widgets/class-wc-widget-search-submit_cmc.php';
    require_once dirname( __FILE__ ) . '/widgets/class-wc-widget-product-search_cmc.php';
    require_once dirname( __FILE__ ) . '/widgets/class-wc-widget-product-categories_cmc.php';
    require_once dirname( __FILE__ ) . '/widgets/class-wc-widget-layered-nav-filters_cmc.php';
}

/**
 * Register Widgets.
 *
 * @since 2.3.0
 */
function cmc_register_widgets() {
    if(get_current_blog_id() == 1) {
        register_widget( 'WC_Widget_Layered_Nav_CMC' );
        register_widget( 'WC_Widget_Search_Submit_CMC' );
        register_widget( 'WC_Widget_Product_Search_CMC' );
        register_widget( 'WC_Widget_Product_Categories_CMC' );
        register_widget( 'WC_Widget_Layered_Nav_Filters_CMC' );
    }
    
}
add_action( 'widgets_init', 'cmc_register_widgets' );
add_filter( 'widget_text', 'do_shortcode' );


function wooc_extra_register_fields() {?>
       <p class="form-row form-row-wide">
       <label for="reg_billing_address_1"><?php _e( 'Street Address', 'woocommerce' ); ?></label>
       <input type="text" class="input-text" name="billing_address_1" id="reg_billing_address_1" value="<?php esc_attr_e( $_POST['billing_address_1'] ); ?>" />
       </p>
       <p class="form-row form-row-wide">
       <label for="reg_billing_city"><?php _e( 'City', 'woocommerce' ); ?></label>
       <input type="text" class="input-text" name="billing_city" id="reg_billing_city" value="<?php esc_attr_e( $_POST['billing_city'] ); ?>" />
       </p>
       <p class="form-row form-row-first fullwidth_select">
           <label for="reg_billing_country"><?php _e( 'Country', 'woocommerce' ); ?><span class="required">*</span></label>
           <select name="billing_country" id="reg_billing_country">
                <option value=""> Select Country </option>
                <?php echo get_country_dropdown('CA'); ?>
            </select>
       </p>
       <?php $country =  isset($_POST['billing_country']) ? $_POST['billing_country'] : 'CA'; ?>
       <?php 
            $state = isset($_POST['billing_state']) ? $_POST['billing_state'] : ''; 

        ?>
        <p class="form-row form-row-first fullwidth_select state_province_wrap" <?php echo ($country == 'CA' ) ? '' : 'style="display: none;"'; ?>>
           <label for="reg_billing_state"><?php _e( 'State/Province', 'woocommerce' ); ?><span class="required">*</span></label>
        
                <select name="billing_state" id="reg_billing_state">
                    <option value="">Prov/State*</option>
                    <option value="AB" <?php echo ($state == 'AB') ? 'selected="selected"' : ''; ?>>Alberta</option>
                    <option value="BC" <?php echo ($state == 'BC') ? 'selected="selected"' : ''; ?>>British Columbia</option>
                    <option value="MB" <?php echo ($state == 'MB') ? 'selected="selected"' : ''; ?>>Manitoba</option>
                    <option value="NB" <?php echo ($state == 'NB') ? 'selected="selected"' : ''; ?>>New Brunswick</option>
                    <option value="NL" <?php echo ($state == 'NL') ? 'selected="selected"' : ''; ?>>Newfoundland</option>
                    <option value="NT" <?php echo ($state == 'NT') ? 'selected="selected"' : ''; ?>>Northwest Territories</option>
                    <option value="NS" <?php echo ($state == 'NS') ? 'selected="selected"' : ''; ?>>Nova Scotia</option>
                    <option value="NU" <?php echo ($state == 'NU') ? 'selected="selected"' : ''; ?>>Nunavut</option>
                    <option value="ON" <?php echo ($state == 'ON') ? 'selected="selected"' : ''; ?>>Ontario</option>
                    <option value="PE" <?php echo ($state == 'PE') ? 'selected="selected"' : ''; ?>>Prince Edward Island</option>
                    <option value="QC" <?php echo ($state == 'QC') ? 'selected="selected"' : ''; ?>>Quebec</option>
                    <option value="SK" <?php echo ($state == 'SK') ? 'selected="selected"' : ''; ?>>Saskatchewan</option>
                    <option value="YT" <?php echo ($state == 'YT') ? 'selected="selected"' : ''; ?>>Yukon</option>
                </select>
       </p>
       <p class="form-row form-row-wide">
       <label for="reg_billing_postcode"><?php _e( 'Postal/Zipcode', 'woocommerce' ); ?></label>
       <input type="text" class="input-text" name="billing_postcode" id="reg_billing_postcode" value="<?php esc_attr_e( $_POST['billing_postcode'] ); ?>" />
       </p>
        <p class="form-row form-row-wide">
            <label for="reg_billing_phone"><?php _e( 'Telephone', 'woocommerce' ); ?></label>
            <input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e( $_POST['billing_phone'] ); ?>" />
       </p>
       <div class="clear"></div>
       <?php
 }
 add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );

 /* woocommerce registration form validation */
/**
* register fields Validating.
*/
function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {
      if ( isset( $_POST['billing_address_1'] ) && empty( $_POST['billing_address_1'] ) ) {
             $validation_errors->add( 'billing_address_1_name_error', __( '<strong>Error</strong>: Address is required!', 'woocommerce' ) );
      }
      if ( isset( $_POST['billing_city'] ) && empty( $_POST['billing_city'] ) ) {
             $validation_errors->add( 'billing_city_name_error', __( '<strong>Error</strong>: City is required!.', 'woocommerce' ) );
      }
      if ( isset( $_POST['billing_country'] ) && empty( $_POST['billing_country'] ) ) {
             $validation_errors->add( 'billing_country_name_error', __( '<strong>Error</strong>: Country is required!.', 'woocommerce' ) );
      }
      if ( isset( $_POST['billing_state'] ) && empty( $_POST['billing_state'] ) ) {
             $validation_errors->add( 'billing_state_name_error', __( '<strong>Error</strong>: State is required!.', 'woocommerce' ) );
      }
      if ( isset( $_POST['billing_postcode'] ) && empty( $_POST['billing_postcode'] ) ) {
             $validation_errors->add( 'billing_postcode_name_error', __( '<strong>Error</strong>: Postcode is required!.', 'woocommerce' ) );
      }
      if ( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) ) {
             $validation_errors->add( 'billing_phone_name_error', __( '<strong>Error</strong>: Phone is required!.', 'woocommerce' ) );
      }

         return $validation_errors;
}
add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );

/**
* Below code save extra fields.
*/
function wooc_save_extra_register_fields( $customer_id ) {

    if ( isset( $_POST['billing_address_1'] ) ) {
        update_user_meta( $customer_id, 'billing_address_1', sanitize_text_field( $_POST['billing_address_1'] ) );
      }
    if ( isset( $_POST['billing_city'] ) ) {
        update_user_meta( $customer_id, 'billing_city', sanitize_text_field( $_POST['billing_city'] ) );
         
    }
    if ( isset( $_POST['billing_country'] ) ) {
        update_user_meta( $customer_id, 'billing_country', sanitize_text_field( $_POST['billing_country'] ) );
    }
    if ( isset( $_POST['billing_state'] ) ) {
        update_user_meta( $customer_id, 'billing_state', sanitize_text_field( $_POST['billing_state'] ) );
    }
    if ( isset( $_POST['billing_postcode'] ) ) {
        update_user_meta( $customer_id, 'billing_postcode', sanitize_text_field( $_POST['billing_postcode'] ) );
    }
    if ( isset( $_POST['billing_phone'] ) ) {
        update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }
}
add_action( 'woocommerce_created_customer', 'wooc_save_extra_register_fields' );


// Add the custom field "favorite_color"
add_action( 'woocommerce_edit_account_form', 'add_favorite_color_to_edit_account_form' );
function add_favorite_color_to_edit_account_form() {
    $user = wp_get_current_user();
    ?>
        <p class="form-row form-row-wide">
           <label for="reg_billing_address_1"><?php _e( 'Street Address', 'woocommerce' ); ?><span class="required">*</span></label>
           <input type="text" class="input-text" name="billing_address_1" id="reg_billing_address_1" value="<?php esc_attr_e( isset($_POST['billing_address_1']) ? $_POST['billing_address_1'] : $user->billing_address_1 ); ?>" required/>
        </p>
        <p class="form-row form-row-wide">
            <label for="reg_billing_city"><?php _e( 'City', 'woocommerce' ); ?><span class="required">*</span></label>
            <input type="text" class="input-text" name="billing_city" id="reg_billing_city" value="<?php esc_attr_e( isset($_POST['billing_address_1']) ? $_POST['billing_city'] : $user->billing_city ); ?>" required />
        </p>
        <?php 
            $country = isset($_POST['billing_country']) ? $_POST['billing_country'] : $user->billing_country; 

        ?>
        <p class="form-row form-row-first fullwidth_select">
            <label for="reg_billing_country"><?php _e( 'Country', 'woocommerce' ); ?><span class="required">*</span></label>
           <select name="billing_country" id="reg_billing_country" required>
                <option value=""> Select Country </option>
                <?php echo get_country_dropdown($country); ?>
            </select>
        </p>
        <?php
            if($user->billing_state) {
                $state = $user->billing_state; 
            } else if(isset($_POST['billing_state'])){
                $state = isset($_POST['billing_state']);
            } else {
                $state = '';
            }
            

        ?>
        <p class="form-row form-row-first fullwidth_select state_province_wrap" <?php echo ($country == 'CA' ) ? '' : 'style="display: none;"'; ?>>
            <label for="reg_billing_state"><?php _e( 'State/Province', 'woocommerce' ); ?><span class="required">*</span></label>
           <select name="billing_state" id="reg_billing_state">
                <option value="">Prov/State*</option>
                <option value="AB" <?php echo ($state == 'AB') ? 'selected="selected"' : ''; ?>>Alberta</option>
                <option value="BC" <?php echo ($state == 'BC') ? 'selected="selected"' : ''; ?>>British Columbia</option>
                <option value="MB" <?php echo ($state == 'MB') ? 'selected="selected"' : ''; ?>>Manitoba</option>
                <option value="NB" <?php echo ($state == 'NB') ? 'selected="selected"' : ''; ?>>New Brunswick</option>
                <option value="NL" <?php echo ($state == 'NL') ? 'selected="selected"' : ''; ?>>Newfoundland</option>
                <option value="NT" <?php echo ($state == 'NT') ? 'selected="selected"' : ''; ?>>Northwest Territories</option>
                <option value="NS" <?php echo ($state == 'NS') ? 'selected="selected"' : ''; ?>>Nova Scotia</option>
                <option value="NU" <?php echo ($state == 'NU') ? 'selected="selected"' : ''; ?>>Nunavut</option>
                <option value="ON" <?php echo ($state == 'ON') ? 'selected="selected"' : ''; ?>>Ontario</option>
                <option value="PE" <?php echo ($state == 'PE') ? 'selected="selected"' : ''; ?>>Prince Edward Island</option>
                <option value="QC" <?php echo ($state == 'QC') ? 'selected="selected"' : ''; ?>>Quebec</option>
                <option value="SK" <?php echo ($state == 'SK') ? 'selected="selected"' : ''; ?>>Saskatchewan</option>
                <option value="YT" <?php echo ($state == 'YT') ? 'selected="selected"' : ''; ?>>Yukon</option>
            </select>
        </p>
        <p class="form-row form-row-wide">
            <label for="reg_billing_postcode"><?php _e( 'Postal/Zipcode', 'woocommerce' ); ?><span class="required">*</span></label>
            <input type="text" class="input-text" name="billing_postcode" id="reg_billing_postcode" value="<?php esc_attr_e( isset($_POST['billing_address_1']) ? $_POST['billing_postcode'] : $user->billing_postcode ); ?>"  required/>
        </p>
        <p class="form-row form-row-wide">
            <label for="reg_billing_phone"><?php _e( 'Telephone', 'woocommerce' ); ?><span class="required">*</span></label>
            <input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e( isset($_POST['billing_address_1']) ? $_POST['billing_phone'] : $user->billing_phone ); ?>" required />
        </p>
       <div class="clear"></div>
    <?php
}

// Save the custom field 'favorite_color' 
add_action( 'woocommerce_save_account_details', 'woocommerce_edit_my_account_extra_fields', 12, 1 );
function woocommerce_edit_my_account_extra_fields( $user_id ) {
    if ( isset( $_POST['billing_address_1'] ) ) {
        update_user_meta( $user_id, 'billing_address_1', sanitize_text_field( $_POST['billing_address_1'] ) );
      }
    if ( isset( $_POST['billing_city'] ) ) {
        update_user_meta( $user_id, 'billing_city', sanitize_text_field( $_POST['billing_city'] ) );
         
    }
    if ( isset( $_POST['billing_country'] ) ) {
        update_user_meta( $user_id, 'billing_country', sanitize_text_field( $_POST['billing_country'] ) );
    }
    if ( isset( $_POST['billing_state'] ) ) {
        update_user_meta( $user_id, 'billing_state', sanitize_text_field( $_POST['billing_state'] ) );
    }
    if ( isset( $_POST['billing_postcode'] ) ) {
        update_user_meta( $user_id, 'billing_postcode', sanitize_text_field( $_POST['billing_postcode'] ) );
    }
    if ( isset( $_POST['billing_phone'] ) ) {
        update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
    }
  
    /*// For Billing email (added related to your comment)
    if( isset( $_POST['account_email'] ) )
        update_user_meta( $user_id, 'billing_email', sanitize_text_field( $_POST['account_email'] ) );*/
}
/*function woocommerce_edit_my_account_page() {
    return apply_filters( 'woocommerce_forms_field', array(
        'woocommerce_my_account_page' => array(
            'type'        => 'text',
            'label'       => __( 'Socail Media Profile Link', ' cloudways' ),
            'placeholder' => __( 'Profile Link', 'cloudways' ),
            'required'    => false,
        ),
    ) );
}
function edit_my_account_page_woocommerce() {
    $fields = woocommerce_edit_my_account_page();
    foreach ( $fields as $key => $field_args ) {
        woocommerce_form_field( $key, $field_args );
    }
}
add_action( 'woocommerce_register_form', 'edit_my_account_page_woocommerce', 15 );*/

function get_country_list($key = null) {
    $countries = array(
        "AX" =>"Åland Islands",
        "AF" =>"Afghanistan",
        "AL" =>"Albania",
        "DZ" =>"Algeria",
        "AS" =>"American Samoa",
        "AD" =>"Andorra",
        "AO" =>"Angola",
        "AI" =>"Anguilla",
        "AQ" =>"Antarctica",
        "AG" =>"Antigua and Barbuda",
        "AR" =>"Argentina",
        "AM" =>"Armenia",
        "AW" =>"Aruba",
        "AU" =>"Australia",
        "AT" =>"Austria",
        "AZ" =>"Azerbaijan",
        "BS" =>"Bahamas",
        "BH" =>"Bahrain",
        "BD" =>"Bangladesh",
        "BB" =>"Barbados",
        "BY" =>"Belarus",
        "PW" =>"Belau",
        "BE" =>"Belgium",
        "BZ" =>"Belize",
        "BJ" =>"Benin",
        "BM" =>"Bermuda",
        "BT" =>"Bhutan",
        "BO" =>"Bolivia",
        "BQ" =>"Bonaire, Saint Eustatius and Saba",
        "BA" =>"Bosnia and Herzegovina",
        "BW" =>"Botswana",
        "BV" =>"Bouvet Island",
        "BR" =>"Brazil",
        "IO" =>"British Indian Ocean Territory",
        "BN" =>"Brunei",
        "BG" =>"Bulgaria",
        "BF" =>"Burkina Faso",
        "BI" =>"Burundi",
        "KH" =>"Cambodia",
        "CM" =>"Cameroon",
        "CA" => "Canada",
        "CV" =>"Cape Verde",
        "KY" =>"Cayman Islands",
        "CF" =>"Central African Republic",
        "TD" =>"Chad",
        "CL" =>"Chile",
        "CN" =>"China",
        "CX" =>"Christmas Island",
        "CC" =>"Cocos (Keeling) Islands",
        "CO" =>"Colombia",
        "KM" =>"Comoros",
        "CG" =>"Congo (Brazzaville)",
        "CD" =>"Congo (Kinshasa)",
        "CK" =>"Cook Islands",
        "CR" =>"Costa Rica",
        "HR" =>"Croatia",
        "CU" =>"Cuba",
        "CW" =>"Curaçao",
        "CY" =>"Cyprus",
        "CZ" =>"Czech Republic",
        "DK" =>"Denmark",
        "DJ" =>"Djibouti",
        "DM" =>"Dominica",
        "DO" =>"Dominican Republic",
        "EC" =>"Ecuador",
        "EG" =>"Egypt",
        "SV" =>"El Salvador",
        "GQ" =>"Equatorial Guinea",
        "ER" =>"Eritrea",
        "EE" =>"Estonia",//
        "ET" =>"Ethiopia",
        "FK" =>"Falkland Islands",
        "FO" =>"Faroe Islands",
        "FJ" =>"Fiji",
        "FI" =>"Finland",
        "FR" =>"France",
        "GF" =>"French Guiana",
        "PF" =>"French Polynesia",
        "TF" =>"French Southern Territories",
        "GA" =>"Gabon",
        "GM" =>"Gambia",
        "GE" =>"Georgia",
        "DE" =>"Germany",
        "GH" =>"Ghana",
        "GI" =>"Gibraltar",
        "GR" =>"Greece",
        "GL" =>"Greenland",
        "GD" =>"Grenada",
        "GP" =>"Guadeloupe",
        "GU" =>"Guam",
        "GT" =>"Guatemala",
        "GG" =>"Guernsey",
        "GN" =>"Guinea",
        "GW" =>"Guinea-Bissau",
        "GY" =>"Guyana",
        "HT" =>"Haiti",
        "HM" =>"Heard Island and McDonald Islands",
        "HN" =>"Honduras",
        "HK" =>"Hong Kong",
        "HU" =>"Hungary",
        "IS" =>"Iceland",
        "IN" =>"India",
        "ID" =>"Indonesia",
        "IR" =>"Iran",
        "IQ" =>"Iraq",
        "IE" =>"Ireland",
        "IM" =>"Isle of Man",
        "IL" =>"Israel",
        "IT" =>"Italy",
        "CI" =>"Ivory Coast",
        "JM" =>"Jamaica",
        "JP" =>"Japan",
        "JE" =>"Jersey",
        "JO" =>"Jordan",
        "KZ" =>"Kazakhstan",
        "KE" =>"Kenya",
        "KI" =>"Kiribati",
        "KW" =>"Kuwait",
        "KG" =>"Kyrgyzstan",
        "LA" =>"Laos",
        "LV" =>"Latvia",
        "LB" =>"Lebanon",
        "LS" =>"Lesotho",
        "LR" =>"Liberia",
        "LY" =>"Libya",
        "LI" =>"Liechtenstein",
        "LT" =>"Lithuania",
        "LU" =>"Luxembourg",
        "MO" =>"Macao S.A.R., China",
        "MG" =>"Madagascar",
        "MW" =>"Malawi",
        "MY" =>"Malaysia",
        "MV" =>"Maldives",
        "ML" =>"Mali",
        "MT" =>"Malta",
        "MH" =>"Marshall Islands",
        "MQ" =>"Martinique",
        "MR" =>"Mauritania",
        "MU" =>"Mauritius",
        "YT" =>"Mayotte",
        "MX" =>"Mexico",
        "FM" =>"Micronesia",
        "MD" =>"Moldova",
        "MC" =>"Monaco",
        "MN" =>"Mongolia",
        "ME" =>"Montenegro",
        "MS" =>"Montserrat",
        "MA" =>"Morocco",
        "MZ" =>"Mozambique",
        "MM" =>"Myanmar",
        "NA" =>"Namibia",
        "NR" =>"Nauru",
        "NP" =>"Nepal",
        "NL" =>"Netherlands",
        "NC" =>"New Caledonia",
        "NZ" =>"New Zealand",
        "NI" =>"Nicaragua",
        "NE" =>"Niger",
        "NG" =>"Nigeria",
        "NU" =>"Niue",
        "NF" =>"Norfolk Island",
        "KP" =>"North Korea",
        "MK" =>"North Macedonia",
        "MP" =>"Northern Mariana Islands",
        "NO" =>"Norway",
        "OM" =>"Oman",
        "PK" =>"Pakistan",
        "PS" =>"Palestinian Territory",
        "PA" =>"Panama",
        "PG" =>"Papua New Guinea",
        "PY" =>"Paraguay",
        "PE" =>"Peru",
        "PH" =>"Philippines",
        "PN" =>"Pitcairn",
        "PL" =>"Poland",
        "PT" =>"Portugal",
        "PR" =>"Puerto Rico",
        "QA" =>"Qatar",
        "RE" =>"Reunion",
        "RO" =>"Romania",
        "RU" =>"Russia",
        "RW" =>"Rwanda",
        "ST" =>"São Tomé and Príncipe",
        "BL" =>"Saint Barthélemy",
        "SH" =>"Saint Helena",
        "KN" =>"Saint Kitts and Nevis",
        "LC" =>"Saint Lucia",
        "SX" =>"Saint Martin (Dutch part)",
        "MF" =>"Saint Martin (French part)",
        "PM" =>"Saint Pierre and Miquelon",
        "VC" =>"Saint Vincent and the Grenadines",
        "WS" =>"Samoa",
        "SM" =>"San Marino",
        "SA" =>"Saudi Arabia",
        "SN" =>"Senegal",
        "RS" =>"Serbia",
        "SC" =>"Seychelles",
        "SL" =>"Sierra Leone",
        "SG" =>"Singapore",
        "SK" =>"Slovakia",
        "SI" =>"Slovenia",
        "SB" =>"Solomon Islands",
        "SO" =>"Somalia",
        "ZA" =>"South Africa",
        "GS" =>"South Georgia/Sandwich Islands",
        "KR" =>"South Korea",
        "SS" =>"South Sudan",
        "ES" =>"Spain",
        "LK" =>"Sri Lanka",
        "SD" =>"Sudan",
        "SR" =>"Suriname",
        "SJ" =>"Svalbard and Jan Mayen",
        "SZ" =>"Swaziland",
        "SE" =>"Sweden",
        "CH" =>"Switzerland",
        "SY" =>"Syria",
        "TW" =>"Taiwan",
        "TJ" =>"Tajikistan",
        "TZ" =>"Tanzania",
        "TH" =>"Thailand",
        "TL" =>"Timor-Leste",
        "TG" =>"Togo",
        "TK" =>"Tokelau",
        "TO" =>"Tonga",
        "TT" =>"Trinidad and Tobago",
        "TN" =>"Tunisia",
        "TR" =>"Turkey",
        "TM" =>"Turkmenistan",
        "TC" =>"Turks and Caicos Islands",
        "TV" =>"Tuvalu",
        "UG" =>"Uganda",
        "UA" =>"Ukraine",
        "AE" =>"United Arab Emirates",
        "GB" =>"United Kingdom (UK)",
        "US" =>"United States (US)",
        "UM" =>"United States (US) Minor Outlying Islands",
        "UY" =>"Uruguay",
        "UZ" =>"Uzbekistan",
        "VU" =>"Vanuatu",
        "VA" =>"Vatican",
        "VE" =>"Venezuela",
        "VN" =>"Vietnam",
        "VG" =>"Virgin Islands (British)",
        "VI" =>"Virgin Islands (US)",
        "WF" =>"Wallis and Futuna",
        "EH" =>"Western Sahara",
        "YE" =>"Yemen",
        "ZM" =>"Zambia",
        "ZW" =>"Zimbabwe",
    );
    return ($key != null && isset($countries[$key])) ? $countries[$key] : $countries;
}
function get_country_dropdown($default = null) {
    $options = '';
    $countries = get_country_list();
    $default_value = ($default == null) ? 'CA' : $default ;
    foreach ($countries as $key => $country) {
        $selected = ($key == $default_value) ? 'selected="selected"' : '';
        $options .= '<option value="'.$key.'" '.$selected.'>'.$country.'</option>';
    }
    return $options;
}