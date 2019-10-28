<?php
/**
 * Template Name: Player Template 
 *
 * @package Avada
 * @subpackage Templates
 */
$url = $_GET['url'];
?>
<!DOCTYPE html>
<html>
<head>
	<title>Canada Music Center</title>
</head>
<body>
		<video id="vid1" class="azuremediaplayer amp-default-skin" autoplay controls width="640" height="400" poster="poster.jpg" data-setup='{"nativeControlsForTouch": false}'>
    
    <p class="amp-no-js">
        To view this video please enable JavaScript, and consider upgrading to a web browser that supports HTML5 video
    </p>
</video>
	<link href="//amp.azure.net/libs/amp/latest/skins/amp-default/azuremediaplayer.min.css" rel="stylesheet">
    <script src="//amp.azure.net/libs/amp/latest/azuremediaplayer.min.js"></script>
	<script type="text/javascript">
		var myOptions = {
				"nativeControlsForTouch": false,
				controls: true,
				autoplay: true,
				width: "640",
				height: "400",
				poster: "//download.blender.org/ED/cover.jpg"
			}
			
			var myPlayer2 = amp("vid1", myOptions);
			myPlayer2.src([{ src: "<?php echo $url; ?>", type: "application/vnd.ms-sstr+xml", protectionInfo: [{ type: "AES", authenticationToken: "Bearer=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1cm46bWljcm9zb2Z0OmF6dXJlOm1lZGlhc2VydmljZXM6Y29udGVudGtleWlkZW50aWZpZXIiOiJlNDI0NWM0Ni1iOTBiLTRmMzEtODk3ZC1mZWQ5YWQ3NGM5NTgiLCJpc3MiOiJodHRwczpcL1wvc3RzLmNvbnRvc28uY29tIiwiYXVkIjoidXJuOmNvbnRvc28iLCJleHAiOjE1NTc0OTE5NjQsIm5iZiI6MTU1NzQ0ODQ2NH0.V74WPKo3_sCh8_OahvIBny0qWcGVADuAdiCwDE1hdd4" }] 
			}]);
			/*var myPlayer = amp('vid1', { 
			        "nativeControlsForTouch": false,
			        autoplay: false,
			        controls: true,
			        width: "640",
			        height: "400",
			        poster: ""
			    });
			myPlayer.src([{
			    src: "http://amssamples.streaming.mediaservices.windows.net/91492735-c523-432b-ba01-faba6c2206a2/AzureMediaServicesPromo.ism/manifest",
			    type: "application/vnd.ms-sstr+xml"
			}]);*/


			
	</script>
</body>
</html>