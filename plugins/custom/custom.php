<?php
/*
Plugin Name: Custom
Description: custom work for API and woocommerce product link to Azure
Auth: Nadeem Iqbal
*/
/* install table for xml*/


add_filter( 'template_include', 'custom_page_template', 99 );

function custom_page_template( $template ) {
  if ( is_page( 'logout' ) ) {
      wp_logout();
      delete_user_meta(get_current_user_id(), 'session_tokens');
      wp_safe_redirect('https://cmccanada.org/');
    
  }
	if ( is_page( 'file-download' ) ) {
    $plugindir = dirname( __FILE__ );
    
    $new_template = $plugindir. '/file-download-page-template.php';
    if ( !empty( $new_template ) ) {
      return $new_template;
    }
  }
  if ( is_page( '/adminal-api' ) ) {
		$plugindir = dirname( __FILE__ );
		
		$new_template = $plugindir. '/laravel-api-template.php';
		if ( !empty( $new_template ) ) {
			return $new_template;
		}
	}
  if(!is_user_logged_in()) {
      global $post;
      $slug = $post->post_name;
      $to_be_redirect = array(
                      'pay-your-annual-composer-fee',
                      'update-contact-information',
                      'update-your-bio',
                      'withdraw-composition',
                      'submit-audio-recording',
                      'update-your-bio',
                      'soumettre-un-enregistrement-sonore',
                      'retirer-une-oeuvre',
                      'mettez-votre-bio-a-jour',
                      'mettre-jour-les-coordonnees',
                      'payer-votre-cotisation-annuelle-de-compositeur-agree',
                  );
      if(in_array($slug, $to_be_redirect)) {
          wp_safe_redirect(site_url());
      }
  }
	return $template;
}

add_action( 'rest_api_init', function () {
    //header("Access-Control-Allow-Origin: *");
  register_rest_route( 'custom/v1', '/(?P<hash>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => 'authenticate_user',
  ) );
  register_rest_route( 'custom/v1', '/remove/(?P<hash>[a-zA-Z0-9-]+)', array(
    'methods' => 'GET',
    'callback' => 'remove_user_hash',
  ) );
  register_rest_route( 'custom/v1', '/create-product/(?P<key>[a-zA-Z0-9-]+)', array(
  	// key is manually created hash key to check whether the request is valid or not.
    'methods' => 'POST',
    'callback' => 'create_product',
  ) );
  register_rest_route( 'custom/v1', '/if-product-exist/(?P<key>[a-zA-Z0-9-]+)', array(
    // key is manually created hash key to check whether the request is valid or not.
    'methods' => 'POST',
    'callback' => 'if_product_exist',
  ) );
  register_rest_route( 'custom/v1', '/get-xml-users', array(
    // key is manually created hash key to check whether the request is valid or not.
    'methods' => 'POST',
    'callback' => 'get_xml_users',
    
  ) );
  register_rest_route( 'custom/v1', '/update-xml-users', array(
    // key is manually created hash key to check whether the request is valid or not.
    'methods' => 'POST',
    'callback' => 'update_xml_users',
    
  ) );
  register_rest_route( 'custom/v1', '/update-ported-xml-users', array(
  	// key is manually created hash key to check whether the request is valid or not.
    'methods' => 'POST',
    'callback' => 'update_ported_xml_users',
  ) );
} );

/**
* Implements add_action('init');
*/
function get_items_permissions_check($request) {
  return true;
}


function remove_post_variations ($post_id) {
   $variations = new WP_Query( array(
        'post_type' => 'product_variation',
        'posts_per_page' => -1,
        'post_parent' => $post_id
    ) );

    if ( $variations->have_posts() ) {

        while ( $variations->have_posts() ) {
            $variations->the_post();

            //$parent = wp_get_post_parent_id( get_the_id() );

            if ( false === get_post_status( $parent ) ) {
                wp_delete_post( get_the_id(), true );
            }

        }

    }
    wp_reset_postdata();

}
function get_xml_users() {
  global $wpdb;
  
  //$users = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users_xml WHERE ported_status = 0 Limit 0, 3", OBJECT );
  $users = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users_xml WHERE ported_status = 0", OBJECT );
  return $users;
}

function update_xml_users() {
  global $wpdb;
  $users = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}users_xml WHERE ported_status = 0", OBJECT );
  if($users) {
    foreach ($users as $key => $usr) {
      //$wpdb->update('wp_users_xml', ['ported_status' => 1], ['id' => $usr->id]);
      $wpdb->delete("{$wpdb->prefix}users_xml", ['id' => $usr->id] );
    }
  }
}
function update_ported_xml_users($data) {
  $ids = $data['ids'];
  print_r($ids);
  if(count($ids)) {
    foreach ($ids as $key => $id) {
      $wpdb->update( 
        '{$wpdb->prefix}users_xml', 
        array( 
          'ported_status' => 1 // integer (number) 
        ), 
        array( 'ID' => $id ), 
        array( 
          '%d'  // value2
        ), 
        array( '%d' ) 
      );
    }
  }
  return ['status' => true];
}
function if_product_exist($data) {
  //switch_to_blog( 1 );
  //update_option('product_creation_hash','f4ade62b5e30561fb75fsd4fe2f6sfs7d3b81fbff635zu35zocizcd0dd004bc74414b9d904a0a36600');
	$ret = [];
	$ret['success'] = false;
	$ret['product_exist'] = false;
	if(validate_access_key($data) == true ) {
		$args = array(
			'post_type' => 'product',
		  'meta_query' => array(
		       array(
		           'key' => 'rsn_number',
		           'value' => $data['rsn_number'],
		           'compare' => '=',
		       )
		   )
		);
		$query = new WP_Query($args);
		if($query->post_count > 0) {
			$ret['product_exist'] = true;
		}
		$ret['success'] = true;
		
	} else {
		$ret['msg'] = 'Invalid access key.';
	}
  restore_current_blog();
	return $ret;

}
function validate_access_key ($data) {
	$key = $data['key'];
	$if_key_exist = get_option('product_creation_hash');
	$ret = false;
	if($if_key_exist == $key ) {
		$ret = true;
	}
	return $ret;
		
}
function create_product ($data) {
  
  //switch_to_blog( 1 );
  /*
function my_awesome_func( WP_REST_Request $request ) {
  // You can access parameters via direct array access on the object:
  $param = $request['some_param'];
 
  // Or via the helper method:
  $param = $request->get_param( 'some_param' );
 
  // You can get the combined, merged set of parameters:
  $parameters = $request->get_params();
 
  // The individual sets of parameters are also available, if needed:
  $parameters = $request->get_url_params();
  $parameters = $request->get_query_params();
  $parameters = $request->get_body_params();
  $parameters = $request->get_json_params();
  $parameters = $request->get_default_params();
 
  // Uploads aren't merged in, but can be accessed separately:
  $parameters = $request->get_file_params();
}
  */
  
  $key = $data['key'];
	$rsn_number = $data['rsn_number'];
	$dat = $data;
  $title = $data['title'];
  $ret = [];
  $metas = [];
  $ret['success'] = false;
  $metas['key'] = $key;
	$metas['subtitle'] = $data['subtitle'];
	$metas['call_number'] = $data['call_number'];	
	$metas['year_of_release'] = $data['year_of_release'];	
	$metas['media_type'] = $data['media_type'];	
	$metas['duration'] = $data['duration'];	
  $metas['number_discs'] = $data['number_discs']; 
	$metas['files'] = $files = $data['files'];	
  if($key) {
    // f4ade62b5e30561fb75fsd4fe2f6sfs7d3b81fbff635zu35zocizcd0dd004bc74414b9d904a0a36600
    $if_key_exist = get_option('product_creation_hash');
    if(validate_access_key($data) == true ) {
      //return $metas;
      $post_id = wp_insert_post( array(
            'post_title' => $title,
            'post_author' => 1,
            'post_content' => 'content herer...',
            'post_status' => 'publish',
            'post_type' => "product",
            'post_name' => $rsn_number,
            ) 
        );
        $metas['product_id'] = $post_id;
        //return $metas;
      if($post_id) {
          wp_set_object_terms($post_id, 'variable', 'product_type');
          wp_set_post_terms( $post_id, '24', 'product_cat');
          
          $attributes = [];
          $files = [
            'score' => [
              'id' => md5(rand(100000,2000000).'_'. time()),
              'file' => '11859_musica_giocosa_111_admin_score-20190118-1.pdf',
              'name' => 'Score',
            ],
            'score_n_save' => [
              'id' => md5(rand(100000,2000000).'_'. time()),
              'file' => '11859_musica_giocosa_111_admin_score-20190118-1.pdf',
              'name' => 'Score & Save',
            ]
          ];
          if($files) {
            foreach ($files as $type => $file) {
              if($type != 'perusal_preview') {
                  $attributes[$type] = [
                        'name' => $type,
                        'value' => $file['name'],
                        'position' => 0,
                        'is_visible' => 1,
                        'is_variation' => 1,
                        'is_taxonomy' => 0,    
                     
                  ];        
              }
            }
          }
          $metas = array(
            '_visibility' => 'visible',
            '_stock_status' => 'instock',
            'total_sales' => '0',
            'language' => 'English',
            '_downloadable' => 'yes',
            '_virtual' => 'no',
            '_regular_price' => '30',
            '_sale_price' => '',
            '_purchase_note' => '',
            '_featured' => 'no',
            '_weight' => '',
            '_length' => '',
            '_width' => '',
            '_height' => '',
            //'rsn_number' => $rsn_number,
            '_sku' => $rsn_number,
            '_product_attributes' => $attributes,
            '_sale_price_dates_from' => '',
            '_sale_price_dates_to' => '',
            '_price' => '',
            '_sold_individually' => '',
            '_manage_stock' => 'no',
            '_backorders' => 'no',
            '_stock' => '',

            
            
          );
          update_field('subtitle', 'subtitle here.',$post_id);
          update_field('call_number', 'Mov 1001',$post_id);
          update_field('year_of_release', '01/01/1983',$post_id);
          update_field('label', '526music',$post_id);
          update_field('media_type', 'Print Book',$post_id);
          update_field('duration', '02:20:20',$post_id);
          update_field('number_discs', '3',$post_id);
          update_field('perusal_preview', '11859_musica_giocosa_111_admin_perusal_preview-20190118-1.pdf',$post_id);
          update_field('score', '11859_musica_giocosa_111_admin_score-20190118-1.pdf',$post_id);
          update_field('score_n_parts', '11859_musica_giocosa_111_admin_score_n_parts-20190118-1.pdf',$post_id);
          foreach ($metas as $key => $value) {
            update_post_meta($post_id, $key, $value);

          }

          
          if($files) {
            
            
            foreach ($files as $type => $file) {
              if($type != 'perusal_preview') {
                // The variation data
                $variation_data =  array(
                    'attributes' => [
                          $type => $file['name'],
                          
                        
                    ],
                    'sku'           => $file['id'],
                    'regular_price' => '22.00',
                    'sale_price'    => '',
                    'stock_qty'     => 1000,
                    'downloadable' => 'yes',
                    //'attributes' => $attributes,
                    'downloadable_files' => [$file['id'] => [
                        'id' => $file['id'],
                        'name' => $file['name'],
                        'file' => 'https://cmccanada.org/file-download?prod='.$post_id.'&type='.$type
                      ]]
                );

                // The function to be run
                create_product_variation( $post_id, $variation_data );
              }
            }
          }
          $ret['success'] = true;
          $ret['data'] = [
            'product_id' => $post_id,
            'url' => 'https://cmccanada.org/shop/'.$rsn_number
          ];
          return $ret;
          // The variation data
          /*$variation_data =  array(
              'attributes' => [
                  'name' => 'score',
                  'value' => 'Score',
                  'position' => 0,
                  'is_visible' => 1,
                  'is_variation' => 1,
                  'is_taxonomy' => 0,
              ],
              'sku'           => 'score_'.$post_id,
              'regular_price' => '22.00',
              'sale_price'    => '',
              'stock_qty'     => 10,
              'downloadable' => 'yes',
              'downloadable_files' => ['sflkjsf-sdfsfsf' => [
                  'id' => 'sflkjsf-sdfsfsf',
                  'name' => 'Score',
                  'file' => 'https://cmccanada.org/file-download?prod=65565&type=score'
                ]]
          );

          // The function to be run
          create_product_variation( $post_id, $variation_data );*/
  			
  			
  			
  		} else {
  			$ret['msg'] = 'Invalid request';
  			
  		}
  		$ret['msg'] = 'Invalid access key';
    }
	}
  //restore_current_blog();
	return $ret;
}

function authenticate_user( $data ) {
  //switch_to_blog( 1 );
	//	print_r($data);
	$hash = $data['hash'];
	$args  = array(
	    'meta_key' => 'auth_hash',
	    'meta_value' => $hash,
	    'meta_compare' => '=' // exact match only
	);
	$wp_user_query = new WP_User_Query( $args );
 
  	$user =  $wp_user_query->get_results();
  	if($user) {
  		$user_id = $user[0]->data->ID;
  		$meta = get_user_meta($user_id,'asn');
		$user[0]->asn = (is_array($meta) && isset($meta[0])) ? $meta[0] : null;
		$user = $user[0];
		unset($user->user_pass);
		unset($user->user_activation_key);
		unset($user->user_registered);
		unset($user->user_url);
		unset($user->spam);
		$user->hash = $hash;
		return $user->data;
	}
	return $user;
	
}
function remove_user_hash( $data ) {
	//	print_r($data);
	$hash = $data['hash'];
	$args  = array(
	    'meta_key' => 'auth_hash',
	    'meta_value' => $hash,
	    'meta_compare' => '=' // exact match only
	);
	$wp_user_query = new WP_User_Query( $args );
 
  	$user =  $wp_user_query->get_results();
  	if($user) {
  		$user_id = $user[0]->data->ID;
  		delete_user_meta($user_id,'auth_hash');
		return ['removed' => true];
	}
	return ['removed' =>false];
	
}



/**
 * Create a product variation for a defined variable product ID.
 *
 * @since 3.0.0
 * @param int   $product_id | Post ID of the product parent variable product.
 * @param array $variation_data | The data to insert in the product.
 */

function create_product_variation( $product_id, $variation_data ){
    // Get the Variable product object (parent)
    $product = wc_get_product($product_id);

    $variation_post = array(
        'post_title'  => $product->get_title(),
        'post_name'   => 'product-'.$product_id.'-variation',
        'post_status' => 'publish',
        'post_parent' => $product_id,
        'post_type'   => 'product_variation',
        'guid'        => $product->get_permalink()
    );

    // Creating the product variation
    $variation_id = wp_insert_post( $variation_post );

    // Get an instance of the WC_Product_Variation object
    $variation = new WC_Product_Variation( $variation_id );

    // Iterating through the variations attributes

    if(isset($variation_data['attributes']) && count($variation_data['attributes'])) {
      foreach ($variation_data['attributes'] as $attribute => $term_name ){
          $taxonomy = 'pa_'.$attribute; // The attribute taxonomy

          // If taxonomy doesn't exists we create it (Thanks to Carl F. Corneil)
          if( ! taxonomy_exists( $taxonomy ) ){
              register_taxonomy(
                  $taxonomy,
                 'product_variation',
                  array(
                      'hierarchical' => false,
                      'label' => ucfirst( $taxonomy ),
                      'query_var' => true,
                      'rewrite' => array( 'slug' => '$taxonomy'), // The base slug
                  )
              );
          }

          // Check if the Term name exist and if not we create it.
          if( ! term_exists( $term_name, $taxonomy ) )
              wp_insert_term( $term_name, $taxonomy ); // Create the term

          $term_slug = get_term_by('name', $term_name, $taxonomy )->slug; // Get the term slug

          // Get the post Terms names from the parent variable product.
          $post_term_names =  wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );

          // Check if the post term exist and if not we set it in the parent variable product.
          if( ! in_array( $term_name, $post_term_names ) )
              wp_set_post_terms( $product_id, $term_name, $taxonomy, true );

          // Set/save the attribute data in the product variation
          update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term_slug );
      }
    }

    ## Set/save all other data

    // SKU
    if( ! empty( $variation_data['sku'] ) )
        $variation->set_sku( $variation_data['sku'] );
    if( ! empty( $variation_data['downloadable'] ) )
        $variation->set_downloadable( $variation_data['downloadable'] );
    if( ! empty( $variation_data['downloadable_files'] ) )
        $variation->set_downloads( $variation_data['downloadable_files'] );

    // Prices
    if( empty( $variation_data['sale_price'] ) ){
        $variation->set_price( $variation_data['regular_price'] );
    } else {
        $variation->set_price( $variation_data['sale_price'] );
        $variation->set_sale_price( $variation_data['sale_price'] );
    }
    $variation->set_regular_price( $variation_data['regular_price'] );

    // Stock
    if( ! empty($variation_data['stock_qty']) ){
        $variation->set_stock_quantity( $variation_data['stock_qty'] );
        $variation->set_manage_stock(true);
        $variation->set_stock_status('');
    } else {
        $variation->set_manage_stock(false);
    }

    $variation->set_weight(''); // weight (reseting)

    $variation->save(); // Save the data
}

function custom_init_callback() {
  if(isset($_GET['debug']) && $_GET['debug'] == true) {
    $id = (isset($_GET['id']) && $_GET['id'] > 0) ? $_GET['id'] : 7463; 
    $per_page = (isset($_GET['per_page']) && $_GET['per_page'] > 0) ? $_GET['per_page'] : 100; 
    $args = array(
      'post_type' => 'product',
      'posts_per_page' => $per_page,
      //'p' => $id,
      //'offset' => 14000,
      'meta_query' => array(
           array(
               'key' => 'url_updted',
               'value' => 'yes',
               'compare' => 'NOT EXISTS',
           )
       ),
      'tax_query' => array(
        array(
          'taxonomy' => 'product_cat',
          'field'    => 'slug',
          'terms'    => array('cmc'),
        ),
      ),
    );

    $que = new WP_Query($args);
    
    $prods_without_variation = [];
    if($que->have_posts()) {
      while ( $que->have_posts() ) {
        $que->the_post();
        echo '<br>';
        //echo site_url('/product/'.);
        echo '<br>product id = '.get_the_ID();
        global $post;
        $pid  = $post->ID;
        echo '<a href="https://cmccanada.org/wp-admin/post.php?post='.$pid.'&action=edit&lang=en">Edit Link</a>';
        echo '<br>';
        echo $post_slug=$post->post_name;
        $prod = get_post(get_the_ID());
        $post_id= $prod->ID;
        $variations = new WP_Query( array(
            'post_type' => 'product_variation',
            'posts_per_page' => -1,
            'post_parent' => $post_id
        ) );
        //print_r($variations);
        if ( $variations->have_posts() ) {
          echo 'have variations';
            /*$files = [
                'score' => [
                  'id' => md5(rand(100000,2000000).'_'. time()),
                  'file' => '11859_musica_giocosa_111_admin_score-20190118-1.pdf',
                  'name' => 'Score',
                ],
                'score_n_save' => [
                  'id' => md5(rand(100000,2000000).'_'. time()),
                  'file' => '11859_musica_giocosa_111_admin_score-20190118-1.pdf',
                  'name' => 'Score & Save',
                ]
              ];*/
            while ( $variations->have_posts() ) {
                $variations->the_post();
                echo '<br>Variation id = '.get_the_ID();
                $variation = new WC_Product_Variation( get_the_id() );
                //print_r($variation);
                $name = @$variation->name;
                echo '<br>  Variation name = '.$name;
                
                  if(isset($_GET['update']) && $_GET['update'] == true) {
                    $type = '';

                    $rand_id = @$variation->sku;
                    $rsn = $prod->post_name;
                    if(strpos($name, 'on Demand') || strpos($name, 'Perusal Score')) {
                      //$type = 'score';
                      //update_field('score', 'file_products/'.$prod->post_name.'_score.zip',$post_id);
                    } elseif(strpos($name, 'Score and Parts')) {
                      $type = 'score_n_parts';
                      update_field('score_n_parts', 'file_products/'.$rsn.'_score_and_parts.zip',$post_id);
                    } elseif(strpos($name, 'Score')) {
                      $type = 'score';
                      update_field('score', 'file_products/'.$rsn.'_score.zip',$post_id);
                    } elseif(strpos($name, 'Solo Parts')) {
                      $type = 'solo_parts';
                      update_field('solo_parts', 'file_products/'.$rsn.'_solo_part.zip',$post_id);
                    } elseif(strpos($name, 'Reduction')) {
                      $type = 'reduction';
                      update_field('reduction', 'file_products/'.$rsn.'_red.zip',$post_id);
                    } 
                    if($type != '') {

                        $file = [$rand_id => [
                                'id' => $rand_id,
                                'name' => $name,
                                'file' => 'https://cmccanada.org/file-download?prod='.$post_id.'&type='.$type
                              ]
                            ];
                        //if( ! empty( $variation_data['downloadable'] ) )
                        $variation->set_downloadable( 1 );
                        $variation->set_downloads( $file );
                        $variation->save();
                        update_post_meta($post_id, 'url_updted','yes');
                    } else {
                        $variation->set_downloadable( 0 );
                        $variation->set_downloads( array() );
                        $variation->save();
                        update_post_meta($post_id, 'url_updted','yes');
                    }
                }
     
            }
        } else {
          $prods_without_variation[] = $post_id;
        }
      }
 
    } 
    print_r($prods_without_variation);
 
    wp_reset_postdata();
    exit;
  } elseif(isset($_GET['product_list']) && $_GET['product_list'] == true) {
    $page = (isset($_GET['page']) && $_GET['page'] > 0) ? $_GET['page'] : 1; 
    $per_page = (isset($_GET['per_page']) && $_GET['per_page'] > 0) ? $_GET['per_page'] : 100; 
    $offset = ($page-1)*$per_page;
    $args = array(
      'post_type' => 'product',
      'posts_per_page' => $per_page,
      //'p' => $id,
      'offset' => $per_page,
      'meta_query' => array(
           array(
               'key' => 'url_updted',
               'value' => 'yes',
               'compare' => '=',
           )
       ),
      'tax_query' => array(
        array(
          'taxonomy' => 'product_cat',
          'field'    => 'slug',
          'terms'    => array('cmc'),
        ),
      ),
    );

    $que = new WP_Query($args);
    if($que->have_posts()) {
      while ( $que->have_posts() ) {
        $que->the_post();
        echo '<br>product id = '.get_the_ID();
        global $post;
        $pid  = $post->ID;
        echo ' <a href="https://cmccanada.org/wp-admin/post.php?post='.$pid.'&action=edit&lang=en">'.get_the_title().'</a>';
        
      }
    }
  }
  
}
function init_callback_for_variations() {
  if(isset($_GET['debug_variation']) && $_GET['debug_variation'] == true) {
    $id = (isset($_GET['id']) && $_GET['id'] > 0) ? $_GET['id'] : 0; 
    $per_page = (isset($_GET['per_page']) && $_GET['per_page'] > 0) ? $_GET['per_page'] : 100; 
    $args = array(
      'post_type' => 'product',
      'posts_per_page' => $per_page,
      //'p' => $id,
      //'offset' => 14000,
      'meta_query' => array(
           array(
               'key' => 'variation_updated',
               'value' => 'yes',
               'compare' => 'NOT EXISTS',
           )
       ),
      'tax_query' => array(
        array(
          'taxonomy' => 'product_cat',
          'field'    => 'slug',
          'terms'    => array('cmc'),
        ),
      ),
    );
    if($id > 0) {
      $args['p'] = $id;
    }
    $que = new WP_Query($args);
    global $wpdb;
    $prods_without_variation = [];
    if($que->have_posts()) {
      while ( $que->have_posts() ) {
        $que->the_post();
        echo '<br>';
        //echo site_url('/product/'.);
        echo '<br>product id = '.get_the_ID();
        global $post;
        $pid  = $post->ID;
        echo ' <a href="https://cmccanada.org/wp-admin/post.php?post='.$pid.'&action=edit&lang=en">Edit Link</a>';
        echo '<br>';
        echo $post_slug=$post->post_name;
        $prod = get_post(get_the_ID());
        $post_id= $prod->ID;
        $variations = new WP_Query( array(
            'post_type' => 'product_variation',
            'posts_per_page' => -1,
            'post_parent' => $post_id
        ) );
        //print_r($variations);
        if ( $variations->have_posts() ) {
          echo 'have variations';
            /*$files = [
                'score' => [
                  'id' => md5(rand(100000,2000000).'_'. time()),
                  'file' => '11859_musica_giocosa_111_admin_score-20190118-1.pdf',
                  'name' => 'Score',
                ],
                'score_n_save' => [
                  'id' => md5(rand(100000,2000000).'_'. time()),
                  'file' => '11859_musica_giocosa_111_admin_score-20190118-1.pdf',
                  'name' => 'Score & Save',
                ]
              ];*/
            while ( $variations->have_posts() ) {
                $variations->the_post();
                echo '<br>Variation id = '.get_the_ID();
                $variation = new WC_Product_Variation( get_the_id() );
                //print_r($variation);
                $name = @$variation->name;
                echo '<br>  Variation name = '.$name;
                
                  if(isset($_GET['update']) && $_GET['update'] == true) {
                    $type = '';
                    //print_r($variation);
                    $rand_id = @$variation->sku;
                    $rsn = $prod->post_name;
                    if(strpos($name, 'Perusal Score')) {
                      //$type = 'score';
                      //update_field('score', 'file_products/'.$prod->post_name.'_score.zip',$post_id);
                      $where = array(
                        'post_type' => 'product_variation',
                        'ID' => get_the_ID()
                      );
                      /*echo 'print';
                      print_r($where);
                      echo 'print';*/
                      $wpdb->delete("{$wpdb->prefix}posts", $where);
                      echo '<br>';
                      echo $wpdb->last_query;
                      echo '<br>';

                    }
                    update_post_meta($post_id, 'variation_updated','yes');
                    
                  }
     
            }
        } else {
          $prods_without_variation[] = $post_id;
        }
      }
 
    } 
    print_r($prods_without_variation);
 
    wp_reset_postdata();
    exit;
  }
  
}
add_action('init', 'custom_init_callback');
add_action('init', 'init_callback_for_variations');


add_action('wp_logout', 'logoutUser', 100);

function logoutUser()
{
   delete_user_meta(get_current_user_id(), 'session_tokens');
}


