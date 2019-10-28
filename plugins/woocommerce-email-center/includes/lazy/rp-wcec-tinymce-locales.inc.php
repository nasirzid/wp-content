<?php

/**
 * Locales for custom TinyMCE buttons
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$strings = 'tinyMCE.addI18n({' . _WP_Editors::$mce_locale . ': {
    rp_wcec_editor_buttons: {
        blocks: "' . esc_js(__('Blocks', 'rp_wcec')) . '",
        macros: "' . esc_js(__('Macros', 'rp_wcec')) . '",
    }
}});';
