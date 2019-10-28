<?php

/**
 * View for Styling settings panel
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wrap">
    <h2><?php _e('Email Styling', 'rp_wcec'); ?></h2>

    <div class="notice notice-error" style="padding: 20px;">

        <h1>Important Notice</h1>
        <p>Styling options were removed from WooCommerce Email Center to focus on the core features.<br>
        Previously saved settings <strong>are still applied to emails sent to your customers</strong>, however, you must migrate to an alternative styling tool (see below) before <strong>July 1, 2017</strong>.</p>
        <p>If you have any questions, you can reach us via our <a href="http://support.rightpress.net/">support site</a>.</p>

        <?php if (RP_WCEC_Styling::are_templates_customized()): ?>

            <h3 style="padding-top: 10px;">Custom Email Templates</h3>
            <p>We are removing custom email templates - default WooCommerce email templates are going to be used for all emails.<br>
            If you modified these custom email templates, you must then apply these changes to default WooCommerce email templates to make sure emails look as expected.<br>
            Custom templates <strong>are still used</strong> for live emails until you take one of the steps described below.</p>

        <?php endif; ?>

        <?php if (RP_WCEC_Styling::are_wc_templates_overriden()): ?>

            <h3 style="padding-top: 10px;">Standard WooCommerce Email Styling</h3>
            <p>We are removing the option that let you choose whether or not to apply Styler styles to standard WooCommerce emails. Custom emails will now always use the same styling as standard emails.<br>
            Custom styles <strong>are still applied</strong> to standard WooCommerce emails until you take one of the steps described below.</p>

        <?php endif; ?>

        <?php if (RP_WCEC_Styling::is_styler_used() || RP_WCEC_Styling::are_templates_customized()): ?>

            <h3 style="padding-top: 10px;">Styler Replacement</h3>
            <p>We are removing the built-in Styler tool. Its functionality is now replaced by a new plugin named <a href="http://rightpress.net/decorator">Decorator</a> which you can <a href="https://downloads.wordpress.org/plugin/decorator-woocommerce-email-customizer.zip">download for free</a>.<br>
            You can easily migrate old Styler settings by clicking the corresponding button below (you can do this both before and after installing Decorator).</p>

            <p>WooCommerce Email Center will <strong>stop applying old styles</strong> and will <strong>hide this page completely</strong> after you either <strong>start using the Decorator plugin</strong> (i.e. save its options for the first time) or click the <strong>Discard All Styling Settings</strong> button.</p>

            <p style="padding-top: 15px;">
                <a href="<?php echo admin_url('/?rp_wcec_migrate_styles_to_decorator=1'); ?>" style="text-decoration: none;" onclick="return confirm('Are you sure you wish to migrate Styler settings to Decorator? This will overwrite any new settings saved in Decorator.')">
                    <button type="button" class="button button-secondary" value="Migrate Styler Settings To Decorator">
                        Migrate Styler Settings To Decorator
                    </button>
                </a>
                <a href="<?php echo admin_url('/?rp_wcec_discard_styles=1'); ?>" style="text-decoration: none;" onclick="return confirm('Are you sure you wish to discard all styling settings?')">
                    <button type="button" class="button button-secondary" value="Discard All Styling Settings">
                        Discard All Styling Settings
                    </button>
                </a>
            </p>

        <?php endif; ?>

    </div>

    <div id="rp_wcec_custom_settings_page">

        <!-- Styler button -->
        <div class="postbox">
            <div class="inside">

                <div id="rp_wcec_launch_styler_button">
                    <button type="button" class="button button-secondary" value="<?php _e('Launch Styler', 'rp_wcec'); ?>" disabled="disabled">
                        <i class="fa fa-paint-brush"></i>&nbsp;&nbsp;<span><?php _e('Launch Styler', 'rp_wcec'); ?></span>
                    </button>
                </div>

            </div>
        </div>

        <!-- Settings -->
        <?php if (RP_WCEC_Styling::are_wc_templates_overriden()): ?>

            <div id="rp_wcec_trigger_settings_wrapper" class="rp_wcec">
                <div class="postbox">
                    <div class="inside">

                        <div id="rp_wcec_trigger_settings">
                            <form method="post">

                                <h3 class="rp_wcec_settings_heading"><?php _e('Styling Settings', 'rp_wcec'); ?></h3>

                                <div class="rp_wcec_settings_rows">
                                    <div class="rp_wcec_settings_first_row">
                                        <div class="rp_wcec_field rp_wcec_field_full">
                                            <label for="rp_wcec_override_woocommerce_templates"><?php _e('Override default WooCommerce email templates', 'rp_wcec'); ?></label>
                                            <select name="rp_wcec[override_woocommerce_templates]" id="rp_wcec_override_woocommerce_templates" disabled="disabled">
                                                <option value="0" selected="selected"><?php _e('No', 'rp_wcec'); ?></option>
                                                <option value="1"><?php _e('Yes', 'rp_wcec'); ?></option>
                                            </select>
                                        </div>
                                        <div style="clear: both;"></div>
                                    </div>
                                </div>

                                <div class="rp_wcec_settings_submit">
                                    <button type="submit" class="button button-primary" title="<?php _e('Save changes', 'rp_wcec'); ?>" name="rp_wcec_settings_button" value="styler_settings" disabled="disabled"><?php _e('Save changes', 'rp_wcec'); ?></button>
                                </div>

                            </form>
                        </div>

                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>
