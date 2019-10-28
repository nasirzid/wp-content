<?php

/**
 * View for preloader
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div id="rp_wcec_preloader">
    <div id="rp_wcec_preloader_content">

        <p class="rp_wcec_preloader_icon">
            <i class="fa fa-cog fa-spin fa-3x fa-fw" aria-hidden="true"></i>
        </p>

        <p class="rp_wcec_preloader_header">
            <?php _e('<strong>User Interface Loading</strong>', 'rp_wcec'); ?>
        </p>

        <p class="rp_wcec_preloader_text">
            <?php printf(__('This plugin uses a JavaScript-driven user interface. If this notice does not disappear in a few seconds, you should check Console for any JavaScript errors or get in touch with <a href="%s">RightPress Support</a>.', 'rp_wcec'), 'http://support.rightpress.net'); ?><br>
        </p>

    </div>
</div>
