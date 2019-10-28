<?php
    
if(isset($_GET['create_product'])) {

    echo '<pre>';
    $product_id = @$_GET['prod'];
    $metas = get_post_meta($product_id,'_product_attributes');
    print_r($metas);
    
     $post_id = wp_insert_post( array(
            'post_title' => 'another test product',
            'post_content' => 'post content',
            'post_status' => 'publish',
            'post_type' => "product",
            ) 
        );

    if($post_id) {
        wp_set_object_terms($post_id, 'variable', 'product_type');
        wp_set_post_terms( $post_id, '24', 'product_cat');
        $attributes = [
            'score' => [
                'name' => 'score',
                'value' => 'Score',
                'position' => 0,
                'is_visible' => 1,
                'is_variation' => 1,
                'is_taxonomy' => 0,
            ],
            'score_n_parts' => [
                'name' => 'score_n_parts',
                'value' => 'Score & Parts',
                'position' => 1,
                'is_visible' => 1,
                'is_variation' => 1,
                'is_taxonomy' => 0,
            ]
        ];
        $metas = array(
          '_visibility' => 'visible',
          '_stock_status' => 'instock',
          'total_sales' => '0',
          '_downloadable' => 'yes',
          '_virtual' => 'no',
          '_regular_price' => '20',
          '_sale_price' => '',
          '_purchase_note' => '',
          '_featured' => 'no',
          '_weight' => '',
          '_length' => '',
          '_width' => '',
          '_height' => '',
          '_sku' => '123456',
          '_product_attributes' => $attributes,
          '_sale_price_dates_from' => '',
          '_sale_price_dates_to' => '',
          '_price' => '',
          '_sold_individually' => '',
          '_manage_stock' => 'no',
          '_backorders' => 'no',
          '_stock' => '',
          'slug' => 'slfkjslfjsdfkj',
          '_downloadable_files' => ['sflkjsf-sfdlkjsdf-sdf' => [
            'id' => 'sflkjsf-sfdlkjsdf-sdf',
            'name' => 'Perusal',
            'file' => 'http://dev-cmccanada.com/file-download?prod=65565&type=perusal_preview'
          ]]
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
        // The variation data
        $variation_data =  array(
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
                'file' => 'http://dev-cmccanada.com/file-download?prod=65565&type=score'
              ]]
        );

        // The function to be run
        create_product_variation( $post_id, $variation_data );
        echo $post_id;
    }
} else {
    $product_id = @$_GET['prod'];
    $type = @$_GET['type'];
    $fileDownload = new FileDownload($type);
    $order = $fileDownload->check_if_have_order($product_id);
    /*$metas = get_post_meta($product_id);
    echo '<pr>';
    print_r($metas);
    exit;*/
    if(count($order)) {

        $product = $order['product'];
        $blob  = get_field($type, $product_id);
        if($blob) {
            $download_link = $fileDownload->generateBlobDownloadLinkWithSAS($blob);
            if($download_link !='') {
                wp_redirect($download_link);
            }
        } else {
            echo 'Invalid request.';
        }
    }
    

}
