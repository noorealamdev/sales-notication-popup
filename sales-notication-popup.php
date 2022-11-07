<?php
/**
 * Plugin Name: Sales Notification Popup Woocommerce
 * Plugin URI:  https://chess-teacher.com
 * Description: Showing purchase notification popup from woocommerce orders in real time.
 * Version:     1.0.0
 * Author:      Noor E Alam
 * Author URI:  https://codenpy.com
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: sales-popup
 * Domain Path: /languages
 * Requires at least: 4.9
 * Tested up to: 5.8
 * Requires PHP: 5.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Invalid request.' );
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
use GeoIp2\Database\Reader;

function get_location_by_ip($ipAddress, $locationType='country') {
    //$ipAddress = '86.168.117.73';
    $databaseFile = plugin_dir_path( __FILE__ ) . 'maxmind-geolite2/city.mmdb';
    $reader = new Reader($databaseFile);
    $record = $reader->city($ipAddress);

    if ($locationType == 'country') {
        return $record->country->name;
    }
    elseif ($locationType == 'city') {
        return $record->city->name;
    }
    else {
        return $record->country->name;
    }
}


function salespopup_loading_assets(){
    wp_enqueue_style('salespopup-style', plugins_url( 'assets/css/style.css', __FILE__ ));
}
add_action('wp_enqueue_scripts', 'salespopup_loading_assets');


include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
    require_once plugin_dir_path( __FILE__ ). 'inc/sales-popup.php';
}