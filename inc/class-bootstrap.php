<?php 
if ( !defined( 'ABSPATH' ) ) exit; // exit if accessed directly

if ( !class_exists( 'AOTFW_Bootstrap' ) ) {
  class AOTFW_Bootstrap {

    private static $instance = null;

    public static function get_instance() {
      if ( !self::$instance ) {
        self::$instance = new AOTFW_Bootstrap();
      }
      return self::$instance;
    }

    private function __construct() {
      $this->init_bootstrap();
    }

    private function init_bootstrap() {
      $this->load_requirements();
    }

    private function load_requirements() {
      $this->require_settings();
      $this->require_order_status_listener();
    }

    private function require_settings() {
      require_once( AOTFW_PLUGIN_PATH . 'inc/class-settings.php' );
      AOTFW_Settings::get_instance();

      require_once( AOTFW_PLUGIN_PATH . 'inc/settings/class-settings-api.php' );
      AOTFW_Settings_Api::get_instance();

      require_once( AOTFW_PLUGIN_PATH . 'inc/settings/class-settings-ajax.php' );
      AOTFW_Settings_Ajax::get_instance();
    }

    private function require_order_status_listener() {
      require_once( AOTFW_PLUGIN_PATH . 'inc/class-order-status-listener.php' );
      AOTFW_Order_Status_Listener::get_instance();
    }
  }
}

?>