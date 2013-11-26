<?php
/**
 * Plugin Name: WooCommerce CSV Orders
 * Plugin URI:  https://github.com/thiagolcks/woocommerce-csv-orders
 * Description: Export selected shop orders showing its products to CSV file.
 * Version:     1.0.0
 * Author:      Thiago Locks
 * Author URI:  https://github.com/thiagolcks
 * License:     GPL-2.0+
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/thiagolcks/woocommerce-csv-orders
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( plugin_dir_path( __FILE__ ) . 'class-woocommerce-csv-orders.php' );

WcCsvOrders::get_instance();