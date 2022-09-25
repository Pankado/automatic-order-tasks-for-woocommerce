<?php
/**
* @package   automatic-order-tasks
* @link      https://webirium.com
* @author    Steven Mønsted Nielsen <contact@webirium.com>
* @copyright 2022-2022 Steven Mønsted Nielsen

* Plugin Name: Automatic Order Tasks For WooCommerce
* Description: Streamline order management. Set up tasks that run automatically when an order reaches your chosen order status.
* Version: 1.0.0
* Author: Steven Mønsted Nielsen
* Author URI: https://webirium.com
* Plugin URI: https://webirium.com
* Requires at least: 5.6
* Requires PHP: 7.4
* Text Domain:  aotfw-domain
* Domain Path: /languages/
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
*/

if ( !defined( 'ABSPATH' ) ) exit; // exit if accessed directly


/** Globals **/
define( 'AOTFW_PLUGIN_NAME', 'Automatic Order Tasks' );

define( 'AOTFW_VERSION', '1' );

define( 'AOTFW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AOTFW_PLUGIN_PATH', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );

define( 'AOTFW_LOG_ID_OPTIONS_KEY', '_aotfw-logid');
define( 'AOTFW_LOG_FOLDER_PREFIX', 'aotfw-log-');


if ( !class_exists( 'Automatic_Order_Tasks' ) ) {
  class Automatic_Order_Tasks {
    
    public function __construct() {
      add_action( 'init', array( $this, 'init' ) );
    }

    public function init() {
        if ( !class_exists('woocommerce') ) return; // skip if woocommerce is not active.

        require_once( AOTFW_PLUGIN_PATH . 'inc/class-bootstrap.php' );
        AOTFW_Bootstrap::get_instance();
    }
  }


}

new Automatic_Order_Tasks();

?>