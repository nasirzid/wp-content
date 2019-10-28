<?php
/**
 * Product Details tab
 *
 * @author 		Ignitus Marketing Arts
 * @package 	WooCommerce/Templates
 * @version		3.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

$heading = esc_html( apply_filters( 'woocommerce_product_additional_information_heading', __( 'Additional information', 'woocommerce' ) ) );

?>
<?php if(get_field('subtitle')): ?>
	<h2 style="margin: 0px; font-size: 28px;"><?php the_title(); ?></h2>
	<h3><?php the_field('subtitle'); ?></h3>
<?php else : ?>
	<h2 style="font-size: 28px;"><?php the_title(); ?></h2>
<?php endif ?>
	<?php do_action( 'woocommerce_product_additional_information', $product ); ?>
	<div class="row">
		<?php 
		$rsn = $product->sku; 
		//$pdf_url = "https://stream.cmccanada.org/ViewerJS/#https://cmcprod.blob.core.windows.net/resources/pdf/perusals/".$rsn."_preview.pdf";
		$pdf_url = "https://cmccanada.org/pdfjs/web/viewer.html?file=https://cmcprod.blob.core.windows.net/resources/pdf/perusals/".$rsn."_preview.pdf";
		require_once 'Mobile_Detect.php';
		$detect = new Mobile_Detect;
		 
		// Any mobile device (phones or tablets).
		/*if ( $detect->isMobile() ) {
		 	$pdf_url = "https://cmcprod.blob.core.windows.net/resources/pdf/perusals/".$rsn."_preview.pdf";
		}*/
		/*echo file_get_contents("https://cmcprod.blob.core.windows.net/resources/pdf/perusals/".$rsn."_preview.pdf");
		if (false === file_get_contents($pdf_url,0,null,0)) {
			echo  'not found';
		} else {
			echo  'found';
		}*/
		$ch = curl_init("https://cmcprod.blob.core.windows.net/resources/pdf/perusals/".$rsn."_preview.pdf");

		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_exec($ch);
		$retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		// $retcode >= 400 -> not found, $retcode = 200, found.
		curl_close($ch);
		$cls = ($retcode == 200) ? 'col-md-5' : 'col-md-12';
		if($retcode == 200) {
				?>
			<div class="col-md-7">
				<iframe src = "<?php echo $pdf_url; ?>" height='500' style="width: 100%;" allowfullscreen webkitallowfullscreen></iframe>
				<p>Something you're looking for? Please <a href="/about/contact/">contact us</a>.</p>
			</div>
		<?php }  ?>
		<div class="<?php echo $cls; ?>">
			<strong>SKU:</strong> <?php echo $product->get_sku(); ?><br />
			<?php if(ICL_LANGUAGE_CODE=='en'): ?>
				<?php
				if(get_field('call_number'))
				{
					echo '<strong>Call Number:</strong> ' . get_field('call_number') . '<br />';
				}
				?>
			<?php endif; ?>
			<?php if(ICL_LANGUAGE_CODE=='fr'): ?>
				<?php
				if(get_field('call_number'))
				{
					echo '<strong>Cote:</strong> ' . get_field('call_number') . '<br />';
				}
				?>
			<?php endif; ?>

			<?php if(ICL_LANGUAGE_CODE=='en'): ?>
				<?php
				if(get_field('media_type'))
				{
					echo '<strong>Format:</strong> ' . get_field('media_type') . '<br />';
				}
				?>
			<?php endif; ?>
			<?php if(ICL_LANGUAGE_CODE=='fr'): ?>
				<?php
				if(get_field('media_type'))
				{
					echo '<strong>Type de média:</strong> ' . get_field('media_type') . '<br />';
				}
				?>
			<?php endif; ?>

			<?php if(ICL_LANGUAGE_CODE=='en'): ?>
				<?php
				if(get_field('number_discs'))
				{
					echo '<strong>Number of Discs:</strong> ' . get_field('number_discs') . '<br />';
				}
				?>
				<?php endif; ?>
			<?php if(ICL_LANGUAGE_CODE=='fr'): ?>
				<?php
				if(get_field('number_discs'))
				{
					echo '<strong>Nombre de disques:</strong> ' . get_field('number_discs') . '<br />';
				}
				?>
			<?php endif; ?>

			<?php if(ICL_LANGUAGE_CODE=='en'): ?>
				<?php
				if(get_field('year_of_release'))
				{
					echo '<strong>Release Date:</strong> ' . get_field('year_of_release') . '<br />';
				}
				?>
			<?php endif; ?>
			<?php if(ICL_LANGUAGE_CODE=='fr'): ?>
				<?php
				if(get_field('year_of_release'))
				{
					echo '<strong>Date de Parution:</strong> ' . get_field('year_of_release') . '<br />';
				}
				?>
			<?php endif; ?>

			<?php if(ICL_LANGUAGE_CODE=='en'): ?>
				<?php
				if(get_field('year_of_composition'))
				{
					echo '<strong>Composition Date:</strong> ' . get_field('year_of_composition') . '<br />';
				}
				?>
			<?php endif; ?>
			<?php if(ICL_LANGUAGE_CODE=='fr'): ?>
				<?php
				if(get_field('year_of_composition'))
				{
					echo '<strong>Date de composition:</strong> ' . get_field('year_of_composition') . '<br />';
				}
				?>
			<?php endif; ?>

			<?php if(ICL_LANGUAGE_CODE=='en'): ?>
				<?php
				if(get_field('duration'))
				{
					echo '<strong>Duration:</strong> ' . get_field('duration') . '<br />';
				}
				?>
			<?php endif; ?>
			<?php if(ICL_LANGUAGE_CODE=='fr'): ?>
				<?php
				if(get_field('duration'))
				{
					echo '<strong>Durée:</strong> ' . get_field('duration') . '<br />';
				}
				?>
			<?php endif; ?>

			<?php
			if(get_field('instrumentation'))
			{
				echo '<strong>Instrumentation:</strong> ' . get_field('instrumentation') . '<br />';
			}
			?>

			<?php if(ICL_LANGUAGE_CODE=='en'): ?>
				<?php
				if(get_field('label'))
				{
					echo '<strong>Label:</strong> ' . get_field('label') . '<br />';
				}
				?>
			<?php endif; ?>
			<?php if(ICL_LANGUAGE_CODE=='fr'): ?>
				<?php
				if(get_field('label'))
				{
					echo '<strong>Étiquette:</strong> ' . get_field('label') . '<br />';
				}
				?>
			<?php endif; ?>

			<?php if(ICL_LANGUAGE_CODE=='en'): ?>
				<?php
				if(get_field('publisher'))
				{
					echo '<strong>Publisher:</strong> ' . get_field('publisher') . '<br />';
				}
				?>
			<?php endif; ?>
			<?php if(ICL_LANGUAGE_CODE=='fr'): ?>
				<?php
				if(get_field('publisher'))
				{
					echo '<strong>Éditeur:</strong> ' . get_field('publisher') . '<br />';
				}
				?>
			<?php endif; ?>

			<?php if(ICL_LANGUAGE_CODE=='en'): ?>
				<?php
				if(get_field('library_record_url'))
				{
				    //get_field('library_record_url')
					echo '<div style="margin-top: 10px;" class="fusion-clearfix"></div><a class="fusion-read-more-button fusion-content-box-button fusion-button button-default button-large button-round button-flat" href="' . site_url('/music-library-redirect') . '" target="_blank">View Library Record</a>';
				}
				?>
			<?php endif; ?>
			<?php if(ICL_LANGUAGE_CODE=='fr'): ?>
				<?php
				if(get_field('library_record_url'))
				{
				    //get_field('library_record_url');
					echo '<div style="margin-top: 10px;" class="fusion-clearfix"></div><a class="fusion-read-more-button fusion-content-box-button fusion-button button-default button-large button-round button-flat" href="' . site_url('/music-library-redirect') . '" target="_blank">Lire le fichier</a>';
				}
			?>
			<?php endif; ?>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<?php
			//$rsn='14936';
			$portal_url = "https://portal.cmccanada.org/api/v1/get-media-service/".$rsn;
			$curl = curl_init();
		  	curl_setopt_array($curl, array(
		        CURLOPT_URL => $portal_url,
		        CURLOPT_RETURNTRANSFER => true,
		        CURLOPT_ENCODING => "",
		        CURLOPT_MAXREDIRS => 10,
		        CURLOPT_TIMEOUT => 30000,
		        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		        CURLOPT_CUSTOMREQUEST => "GET",
		        CURLOPT_POSTFIELDS => json_encode($data),
		        CURLOPT_HTTPHEADER => array(
		            // Set here requred headers
		            "accept: */*",
		            "accept-language: en-US,en;q=0.8",
		            "content-type: application/json",
		        ),
		    ));

		    $response = curl_exec($curl);      
		    $err = curl_error($curl);
		    curl_close($curl);
		    $url = '';
		    $response = json_decode($response);
		    
		    if($response->success == true) {
				$url = 'https://stream.cmccanada.org/mp4.php/?RSN='.$rsn;
			?>
				<iframe src = "<?php echo $url; ?>" height='500' style="width: 100%; max-width: 520px; display: block; margin: 0 auto;" ></iframe>
			<?php } ?>
		</div>
	</div>
