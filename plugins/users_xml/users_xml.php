<?php

/*
Plugin Name: Users XML
Description: Newly registered users listing.
Auth: Nadeem Iqbal
*/
/* install table for xml*/
function jal_install2 () {
   global $wpdb;

   $table_name = $wpdb->prefix . "users_xml";
   global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name VARCHAR(50) NULL,
      time datetime DEFAULT CURRENT_TIMESTAMP NULL,
      user_id mediumint(10) NOT NULL,
      email varchar(55) DEFAULT '' NOT NULL,
      password varchar(55) DEFAULT '' NOT NULL,
      ported_status TINYINT(1) NULL DEFAULT '0' COMMENT '0 = not ported to sydney, 1 = ported '
      PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'jal_install' );

/** 
* implement do_action('init') to create page
* to run cron to push registered user to
* sydney
*/
function initialize_init_callback() {
  $push_xml = get_page_by_path('push-xml-to-sydney');
  if(!$push_xml) {
    $my_post = array(
      'post_title'    => 'Push xml to sydney',
      'post_status'   => 'publish',
      'post_author'   => 1,
      'post_type'     => 'page'
    );
    // Insert the post into the database
    wp_insert_post( $my_post );
  }
  
  
}
add_action('init', 'initialize_init_callback');

add_filter( 'template_include', 'xml_cron_page_template', 99 );

function xml_cron_page_template( $template ) {

  if ( is_page( 'push-xml-to-sydney' ) ) {
    $plugindir = dirname( __FILE__ );
    
    $new_template = $plugindir. '/templates/push-xml-to-sydney-template.php';
    if ( !empty( $new_template ) ) {
      return $new_template;
    }
  }
  
  return $template;
}

/**
* get user info before registration
*/
function check_before_register($user_id) {
  $email = '';
  $password = '';
  if(isset($_POST['user_email']) && !empty($_POST['user_email'])) {
    $email = sanitize_text_field($_POST['user_email']);
  }elseif(isset($_POST['email']) && !empty($_POST['email'])) {
    $email = sanitize_text_field($_POST['email']);
  } elseif(isset($_POST['billing_email']) && !empty($_POST['billing_email'])) {
    $email = sanitize_text_field($_POST['billing_email']);
  }

  if(isset($_POST['password']) && !empty($_POST['password'])) {
    $password = sanitize_text_field($_POST['password']);
  } elseif(isset($_POST['account_password']) && !empty($_POST['account_password'])) {
    $password = sanitize_text_field($_POST['account_password']);
  }
  if(isset($_POST['account_first_name']) && !empty($_POST['account_first_name'])) {
    $full_name = sanitize_text_field($_POST['account_first_name']).' '.sanitize_text_field($_POST['account_last_name']);
    $account_first_name = sanitize_text_field($_POST['account_first_name']);
    $account_last_name = sanitize_text_field($_POST['account_last_name']);
    update_user_meta($user_id, 'account_first_name', $account_first_name);
    update_user_meta($user_id, 'account_last_name', $account_last_name);
    update_user_meta($user_id, 'first_name', $account_first_name);
    update_user_meta($user_id, 'last_name', $account_last_name);
    /*update_user_meta($user_id, 'billing_first_name', $account_first_name);
    update_user_meta($user_id, 'billing_last_name', $account_last_name);*/
    global $woocommerce;

    $user_first_name = get_user_meta( $user_id, 'first_name', true );
    $user_last_name = get_user_meta( $user_id, 'last_name', true );

    //if( !empty( $woocommerce->customer->get_billing_first_name() ) ) {

        $woocommerce->customer->set_billing_first_name($user_first_name); // a set_billing_first_name method doesn't exist
        $woocommerce->customer->set_billing_last_name($user_last_name); // a set_billing_first_name method doesn't exist
    //}
  } elseif(isset($_POST['billing_account_first_name'])) {
    $full_name = sanitize_text_field($_POST['billing_account_first_name']).' '.sanitize_text_field($_POST['billing_last_name']);
  } elseif(isset($_POST['billing_first_name'])) {
    $full_name = sanitize_text_field($_POST['billing_first_name']).' '.sanitize_text_field($_POST['billing_last_name']);
  } elseif(isset($_POST['comport_register'])) {
    $full_name = sanitize_text_field($_POST['reg_f_name']).' '.sanitize_text_field($_POST['reg_l_name']);
    $email = sanitize_text_field($_POST['reg_email']);
    $password = sanitize_text_field($_POST['reg_password']);
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
  if($password != '') {
    wp_set_password( $password, $user_id );
  }
  if(get_current_blog_id() == 10) {  
    wp_update_user( array ('ID' => $user_id, 'role' => 'composer') ) ;
  }
  global $wpdb;
    $wpdb->insert( 
      'wp_users_xml', 
      array( 
        'name' => $full_name, 
        'email' => $email, 
        'billing_address_1' => $billing_address_1, 
        'billing_city' => $billing_city, 
        'billing_country' => $billing_country, 
        'billing_state' => $billing_state, 
        'billing_postcode' => $billing_postcode, 
        'billing_phone' => $billing_phone, 
        'password' => $password,
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

}
add_action('user_register', 'check_before_register');

//add_filter('wp_pre_insert_user_data', 'check_before_register');
// end 

/**
 * Add the code below to your theme's functions.php file
 * to add a confirm password field on the register form under My Accounts.
 */ 
/* register form and woocommerce register validation*/
function registration_errors_validation($reg_errors, $sanitized_user_login, $user_email) {
    global $woocommerce;
    extract( $_POST );
    $blog_id = get_current_blog_id();
    if($blog_id == 10) {
      if ( isset($password) && strcmp( $password, $password2 ) !== 0 ) {
          return new WP_Error( 'registration-error', __( 'Passwords do not match.', 'woocommerce' ) );
      } else if(isset($account_password) && strcmp( $account_password, $account_password_2 ) !== 0 ) {
          return new WP_Error( 'registration-error', __( 'Passwords do not match.', 'woocommerce' ) );
      }
    }
    $account_first_name = trim($account_first_name);
    if(isset($_POST['account_first_name']) && empty($_POST['account_first_name'])) {
      return new WP_Error( 'registration-error', __( 'Please provide your first name.', 'woocommerce' ) );
    }
    $account_first_name = trim($account_first_name);
    if(isset($_POST['account_first_name']) && empty($_POST['account_first_name'])) {
      return new WP_Error( 'registration-error', __( 'Please provide your last your name.', 'woocommerce' ) );
    }

    return $reg_errors;
}
add_filter('woocommerce_registration_errors', 'registration_errors_validation', 10,3);
/* register form and woocommerce register validation*/

function woocommerce_register_form_customer_name() {
    ?>
    <p class="form-row form-row-wide">
        <label for="reg_name"><?php _e( 'First Name', 'woocommerce' ); ?> <span class="required">*</span></label>
        <input type="text" class="input-text" name="account_first_name" id="reg_name" value="<?php if ( ! empty( $_POST['account_first_name'] ) ) echo esc_attr( $_POST['account_first_name'] ); ?>" />
    </p>
    <p class="form-row form-row-wide">
        <label for="reg_name"><?php _e( 'Last Name', 'woocommerce' ); ?> <span class="required">*</span></label>
        <input type="text" class="input-text" name="account_last_name" id="reg_name" value="<?php if ( ! empty( $_POST['account_last_name'] ) ) echo esc_attr( $_POST['account_last_name'] ); ?>" />
    </p>
    
    <?php
}

//remove_action( 'woocommerce_register_form', 'woocommerce_register_form_customer_name', 1 );
add_action( 'woocommerce_register_form_start', 'woocommerce_register_form_customer_name', -100 );

/* woocommerce checkout register form */
function woocommerce_register_form_password_repeat() {
    $blog_id = get_current_blog_id();
    if($blog_id == 10) {
    ?>
    <p class="form-row form-row-wide">
        <label for="reg_password"><?php _e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
        <input type="password" class="input-text" name="password" id="reg_password" value="<?php if ( ! empty( $_POST['password'] ) ) echo esc_attr( $_POST['password'] ); ?>" />
    </p>
    <p class="form-row form-row-wide">
        <label for="reg_password2"><?php _e( 'Confirm Password', 'woocommerce' ); ?> <span class="required">*</span></label>
        <input type="password" class="input-text" name="password2" id="reg_password2" value="<?php if ( ! empty( $_POST['password2'] ) ) echo esc_attr( $_POST['password2'] ); ?>" />
    </p>
    <?php
  }
}
add_action( 'woocommerce_register_form', 'woocommerce_register_form_password_repeat' );

/* woocommerce checkout register form */


/* woocommerce checkout register form */
// Add a second password field to the checkout page in WC 3.x.
add_filter( 'woocommerce_checkout_fields', 'wc_add_confirm_password_checkout', 10, 1 );
function wc_add_confirm_password_checkout( $checkout_fields ) {
    //if ( get_option( 'woocommerce_registration_generate_password' ) == 'no' ) {
        $checkout_fields['account']['account_password'] = array(
                'type'              => 'password',
                'label'             => __( 'Password', 'woocommerce' ),
                'required'          => true,
                'placeholder'       => _x( 'Password', 'placeholder', 'woocommerce' )
        );
        $checkout_fields['account']['account_password_2'] = array(
                'type'              => 'password',
                'label'             => __( 'Confirm Password', 'woocommerce' ),
                'required'          => true,
                'placeholder'       => _x( 'Confirm Password', 'placeholder', 'woocommerce' )
        );
    //}

    /*echo '<pre>';
    print_r($checkout_fields);
    echo '</pre>';*/
    return $checkout_fields;
}

// Check the password and confirm password fields match before allow checkout to proceed.
add_action( 'woocommerce_after_checkout_validation', 'wc_check_confirm_password_matches_checkout', 10, 2 );
function wc_check_confirm_password_matches_checkout( $posted ) {
    $checkout = WC()->checkout;
    if ( ! is_user_logged_in() && ( $checkout->must_create_account || ! empty( $posted['createaccount'] ) ) ) {
        if ( strcmp( $posted['account_password'], $posted['account_password_2'] ) !== 0 ) {
            wc_add_notice( __( 'Passwords do not match.', 'woocommerce' ), 'error' );
        }
    }
}


function wppb_change_text_login( $translated_text, $text, $domain ) {
    //echo $translated_text;
    // Only on my account registering form
    //if ( is_page( 'my-account' ) ) {
        $original_text = 'Username or email address';

        if ( $text === $original_text )
            $translated_text = esc_html__('Email Address', $domain );
    //}
    return $translated_text;
}
add_filter( 'gettext', 'wppb_change_text_login', 10, 3 );

function bbloomer_add_premium_support_endpoint() {
    add_rewrite_endpoint( 'library-loans', EP_ROOT | EP_PAGES );
}
 
add_action( 'init', 'bbloomer_add_premium_support_endpoint' );
 
 
// ------------------
// 2. Add new query var
 
function bbloomer_premium_support_query_vars( $vars ) {
    
    $vars[] = 'library-loans';
    return $vars;
}
 
add_filter( 'query_vars', 'bbloomer_premium_support_query_vars', 0 );
 
 
// ------------------
// 3. Insert the new endpoint into the My Account menu
 
function cmc_change_tabs_order( $items ) {
    global $wpdb;
    $blog_id = get_current_blog_id();
    /* make sure if user is on main site */
    if($blog_id == 1 ) {
        
      $items['library-loans'] = __('Library Loans');

      unset($items['edit-address']);
      unset($items['edit-account']);
      unset($items['customer-logout']);
      $items['edit-address'] = __('Addresses');
      $items['edit-account'] = __('User Account & Password');
      if(is_user_logged_in()) {
        $user = wp_get_current_user();
        /*echo $user->ID;
        $user_meta=get_userdata($user->ID);
        $user_roles=$user_meta->roles;*/
        

        if ( in_array( 'composer', (array) $user->roles ) ) {
          $items = array_reverse($items);
          $items['composer-portal'] = 'Composer Portal';
          $items = array_reverse($items);
          
        }
      }
      $items['customer-logout'] = 'Logout';
    }
    return $items;
}
 
add_filter( 'woocommerce_account_menu_items', 'cmc_change_tabs_order' );
 

add_filter( 'woocommerce_get_endpoint_url', 'loans_redirect_endpoint', 10, 4 );
function loans_redirect_endpoint ($url, $endpoint, $value, $permalink)
{
    if( $endpoint == 'library-loans') {
      $locale = get_locale();
      //$url = 'http://1443.sydneyplus.com/final/Portal/Music-Library.aspx?lang=en-CA';
      if($locale == 'en_US') {
        $url = '/music-library-redirect';
      } else {
        $url = '/fr/music-library-redirect';
      }
    } elseif( $endpoint == 'composer-portal') {
      $url = 'https://comport.cmccanada.org';
    }
    return $url;
}