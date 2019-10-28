<?php

/**
 * General email template for custom emails
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<?php do_action('woocommerce_email_header', $email_heading); ?>

<?php do_action('rp_wcec_before_custom_email_content', $rp_wcec_email_id, $rp_wcec_trigger_id, $sent_to_admin, $plain_text, $style); ?>

<div id="rp_wcec_email_content">
    <?php echo wpautop(wptexturize($content)); ?>
</div>

<?php do_action('rp_wcec_after_custom_email_content', $rp_wcec_email_id, $rp_wcec_trigger_id, $sent_to_admin, $plain_text, $style); ?>

<?php do_action('woocommerce_email_footer'); ?>
