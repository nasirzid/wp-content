/**
 * WooCommerce Email Center Plugin custom TinyMCE buttons
 */
(function () {

    /**
     * Add custom buttons
     */
    if (typeof tinymce !== 'undefined') {

        tinymce.PluginManager.add('rp_wcec_editor_buttons', function (editor) {

            // Get active editor
            var ed = tinymce.activeEditor;

            // Check if macros are used on this page
            if (typeof rp_wcec_custom_button_config !== 'undefined' && typeof rp_wcec_custom_button_config.macros !== 'undefined') {

                // Set up macro list
                var macro_list = [];

                // Iterate over macros
                for (var macro_key in rp_wcec_custom_button_config.macros) {
                    (function (current_title, current_value) {
                        macro_list.push({
                            text: current_title,
                            onclick: function () {
                                editor.insertContent(' ' + current_value + ' ');
                            }
                        });
                    })(rp_wcec_custom_button_config.macros[macro_key], rp_wcec_custom_button_config.macros[macro_key]);
                }

                // Add macros button
                editor.addButton('rp_wcec_macros', {
                    text: ed.getLang('rp_wcec_editor_buttons.macros'),
                    icon: 'rp-wcec-macros',
                    type: 'menubutton',
                    menu: macro_list
                });
            }

            // Check if blocks are used on this page
            if (typeof rp_wcec_custom_button_config !== 'undefined' && typeof rp_wcec_custom_button_config.blocks !== 'undefined') {

                // Set up block list
                var block_list = [];

                // Iterate over blocks
                for (var block_key in rp_wcec_custom_button_config.blocks) {
                    (function (current_title, current_value) {
                        block_list.push({
                            text: current_title,
                            onclick: function () {
                                editor.insertContent(' ' + current_value + ' ');
                            }
                        });
                    })(rp_wcec_custom_button_config.blocks[block_key], '[wcec_block id=' + block_key + ']');
                }

                // Add blocks button
                editor.addButton('rp_wcec_blocks', {
                    text: ed.getLang('rp_wcec_editor_buttons.blocks'),
                    icon: 'rp-wcec-blocks',
                    type: 'menubutton',
                    menu: block_list
                });
            }
        });
    }

})();

/**
 * Change custom button positions
 */
function rp_wcec_change_custom_editor_button_positions()
{
    jQuery('#wp-content-wrap .mce-btn-group').css('width', '100%');
    jQuery('i.mce-i-rp-wcec-blocks').closest('div.mce-btn').css('float', 'right');
    jQuery('i.mce-i-rp-wcec-macros').closest('div.mce-btn').css('float', 'right');
}
