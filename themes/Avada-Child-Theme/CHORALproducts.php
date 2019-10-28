<?php 
/* Template Name: Choral Products */
 
$args = array(
    'post_type' => 'product',
    'post_status' => 'draft',
     'fields' => 'ids',
    'meta_query' => array(
        array(
            'key'     => 'call_number',
            'value'   => 'mv',
            'compare' => 'LIKE',
        ),
    ),
    'orderby'   => 'ID',
    'order'     => 'ASC',
    'posts_per_page' => 10000,
    //'offset' => 4
);
 

    $the_query = new WP_Query( $args );
    $num = $the_query->post_count; 
    echo $num;
    if ( $the_query->have_posts() ) : 
        $count = 0;
        while ( $the_query->have_posts() ) : $the_query->the_post(); 

            the_title();
            echo '<br>';
            $name = get_the_title();
            $post_id = get_the_ID();
            $count++;
            echo $count;

            // $success = $wpdb->insert("woocommerce_products",
            //                     array(
            //                         "post_id" => $post_id,
            //                         "rsn_value" => $name,
            //                         "call_number"  => $name,
            //                         "post_title" => $name,		)
            //                     );	
            // $id =  $wpdb->insert_id;
            // if ($success) {
            //     echo $id;
            // }            
        endwhile; 
        wp_reset_postdata(); 

    else : ?>
    <p><?php _e( 'Sorry, no posts matched your criteria.' ); ?></p>
<?php endif;

echo $count;

?>


echo '======';
echo $count;