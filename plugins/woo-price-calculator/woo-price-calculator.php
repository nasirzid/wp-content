<?php
/*
Plugin Name: AWS Price Calculator
Plugin URI:  https://altoswebsolutions.com/cms-plugins/woopricecalculator
Description: Price Calculator for WooCommerce
Version:     2.3.3
Author:      Altos Web Solutions Italia
Author URI:  https://www.altoswebsolutions.com
License:     
License URI: 
Domain Path: /lang
Text Domain: PoEdit
*/

/*
 * ATTENZIONE, Se si aggiorna Version, aggiornare anche la variabile $plugin_db_version
 * qui sotto per il database
 */

/*WPC-PRO*/
require 'admin/resources/lib/plugin-update-checker-3.1.0/plugin-update-checker.php';
$WPCUpdateChecker = new PluginUpdateChecker_3_1(
            'https://altoswebsolutions.com/aws_files/woopricecalculator/pro/woopricecalculator.json',
	__FILE__,
        'woo-price-calculator'
);
/*/WPC-PRO*/

require 'awspricecalculator.php';

/*
 * Controllo che WooCommerce sia attivato
 */
if (in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    $GLOBALS['woopricecalculator'] = new AWSPriceCalculator("2.3.3");
}
