<?php
    require_once "vendor/autoload.php";
    use MicrosoftAzure\Storage\Blob\BlobRestProxy;
    use MicrosoftAzure\Storage\Blob\BlobSharedAccessSignatureHelper;
    use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
    use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
    use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
    use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;
    use MicrosoftAzure\Storage\Blob\Models\DeleteBlobOptions;
    use MicrosoftAzure\Storage\Blob\Models\CreateBlobOptions;
    use MicrosoftAzure\Storage\Blob\Models\GetBlobOptions;
    use MicrosoftAzure\Storage\Blob\Models\ContainerACL;
    use MicrosoftAzure\Storage\Blob\Models\SetBlobPropertiesOptions;
    use MicrosoftAzure\Storage\Blob\Models\ListPageBlobRangesOptions;
    use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
    use MicrosoftAzure\Storage\Common\Exceptions\InvalidArgumentTypeException;
    use MicrosoftAzure\Storage\Common\Internal\Resources;
    use MicrosoftAzure\Storage\Common\Internal\StorageServiceSettings;
    use MicrosoftAzure\Storage\Common\Models\Range;
    use MicrosoftAzure\Storage\Common\Models\Logging;
    use MicrosoftAzure\Storage\Common\Models\Metrics;
    use MicrosoftAzure\Storage\Common\Models\RetentionPolicy;
    use MicrosoftAzure\Storage\Common\Models\ServiceProperties;
class FileDownload {
    private $myContainer;
    private $composerContainer;
    private $connectionString;
    private $blobClient;
    private $account_name;
    private $account_key;
    function __construct() {
        //$this->myContainer = 'files';
        // live product
        $this->myContainer = 'files';

        //$this->account_name = 'cmcdevtest';
        // live name
        $this->account_name = 'cmcprod';
        //$this->account_key = 'cWAN9nmh0GmzO43T6o5/xPb+uOhcU7jq2k3/svnj//+YjXdu3Yy3Z0q+d3RCDloqVhqSZFkOFYEZ1WB2TbB4Dw==';
        // live key
        $this->account_key = 'wKrPGy7Ng9xHA0bd24SO9Oa/rOnMIrvV8vu/uSOCmfI/jaXU9Qabil/9RYKu1S/Ip/1+dChlNE0LnEY2/5FS7A==';
        //$this->connectionString = 'DefaultEndpointsProtocol=https;AccountName='.$this->account_name.';AccountKey='.$this->account_key;
        // live string
        //DefaultEndpointsProtocol=https;AccountName=cmcprod;AccountKey=evdBFfIG6tBnGtZEej1B8yYaIYojgsVQyuA1Oc4NkcpnAwoJpaYcPjKTWDh/B5Plo659ArqZRfJM/7CJm9smKQ==;EndpointSuffix=core.windows.net
        $this->connectionString = 'DefaultEndpointsProtocol=https;AccountName='.$this->account_name.';AccountKey='.$this->account_key;
        $this->blobClient = BlobRestProxy::createBlobService($this->connectionString);
        
    }
    function generateBlobDownloadLinkWithSAS($blob){
        $ip = $_SERVER['REMOTE_ADDR'];

        $settings = StorageServiceSettings::createFromConnectionString($this->connectionString);
        $accountName = $settings->getName();
        $accountKey = $settings->getKey();

        $helper = new BlobSharedAccessSignatureHelper(
            $accountName,
            $accountKey
        );
        $date = new DateTime(date('Y-m-d H:i:s'));
        $date_time = $date->format('Y-m-d').'T'.$date->format('H:i:s').'Z'; 
        $date->modify('+3 day');
        $exp_date_time = $date->format('Y-m-d').'T'.$date->format('H:i:s').'Z'; 
        
        //$date_time = Carbon::parse(Carbon::now())->subDays(1);
        //$exp_date_time = Carbon::parse(Carbon::now())->addDays(2);
        
        
        // Refer to following link for full candidate values to construct a service level SAS
        // https://docs.microsoft.com/en-us/rest/api/storageservices/constructing-a-service-sas
        $sas = $helper->generateBlobServiceSharedAccessSignatureToken(
            Resources::RESOURCE_TYPE_BLOB,
            "$this->myContainer/$blob",
            'r',                            // Read
            $exp_date_time,
            $date_time,
            $ip,//'0.0.0.0-255.255.255.255'
            'https,http'
        );

        $connectionStringWithSAS = Resources::BLOB_ENDPOINT_NAME .
            '='.
            'https://' .
            $accountName .
            '.' .
            Resources::BLOB_BASE_DNS_NAME .
            ';' .
            Resources::SAS_TOKEN_NAME .
            '=' .
            $sas;

        $blobClientWithSAS = BlobRestProxy::createBlobService(
            $connectionStringWithSAS
        );

        // We can download the blob with PHP Client Library
        // downloadBlobSample($blobClientWithSAS);

        // Or generate a temporary readonly download URL link
        $blobUrlWithSAS = sprintf(
            '%s%s?%s',
            (string)$blobClientWithSAS->getPsrPrimaryUri(),
            "$this->myContainer/$blob",
            $sas
        );

        //file_put_contents("outputBySAS.txt", fopen($blobUrlWithSAS, 'r'));

        return $blobUrlWithSAS;
    }
    function check_if_have_order($product_id) {
        $customer_orders = get_posts( array(
            'numberposts' => - 1,
            'meta_key'    => '_customer_user',
            'meta_value'  => get_current_user_id(),
            'post_type'   => array( 'shop_order' ),
            'post_status' => array( 'wc-completed' ),

            /*'date_query' => array(
                'after' => date('Y-m-d', strtotime('-10 days')),
                'before' => date('Y-m-d', strtotime('today')) 
            )*/

        ) );

        $total = 0;
        foreach ( $customer_orders as $customer_order ) {
            
            $order = wc_get_order( $customer_order );
            $items = $order->get_items();
            if($items) {
                foreach ($items as $item) {
                    $pid = $item->get_product_id();
                    if($pid == $product_id) {
                        return ['order' => $item, 'product' => $customer_order];
                    }

                    # code...
                }
            }
            $total += $order->get_total();
        }

        return [];
    }
}
if(isset($_GET['create_product'])) {

echo  get_current_blog_id();
exit;


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
