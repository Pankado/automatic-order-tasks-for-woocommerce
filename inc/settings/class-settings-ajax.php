<?php 

if ( !defined( 'ABSPATH' ) ) exit; // exit if accessed directly

if ( !class_exists('AOTFW_Settings_Ajax') ) {
  /**
   * Class holding the ajax accessed functions for order tasks
   *
   *
   * @since      1.0.0
   * @package    Automatic_Order_Tasks
   * @subpackage Automatic_Order_Tasks/settings
   * @author     Steven Mønsted Nielsen (contact@webirium.com)
   */
  class AOTFW_Settings_Ajax {

    private static $instance;

    private function __construct(){
      $this->expose_ajax_functions();
    }


    public static function get_instance() {
      if ( !self::$instance ) {
        $instance = new AOTFW_Settings_Ajax();
      }
      return $instance;
    }


    public function ajax_get_order_tasks_config() {
      $this->check_request_allowed();
      
      $id = sanitize_text_field( $_GET['id'] ) ?? null;

      if ( empty($id) ) {
        http_response_code( 400 );
        die( 'Missing ID parameter in request' );
      }

      $config = AOTFW_Settings_Api::get_instance()->get_config($id);

      echo $config;
      die();
    }


    public function ajax_post_order_tasks_config() {
      $this->check_request_allowed();

      $data = json_decode( wp_unslash( $_POST['data'] ), true );
  
      if ( $data === null ) {
        wp_send_json_error( __('Invalid JSON data in request', 'aotfw-domain'), 400 );
      }

      // check if input order status exists exists
      $order_status_input = sanitize_text_field( $data['orderStatus'] ) ?? null;

      $order_statuses = wc_get_order_statuses();

      if ( !array_key_exists( $order_status_input, $order_statuses ) ) {
        wp_send_json_error( __('The requested order status does not exist', 'aotfw-domain'), 404 );
      }

      // update option
      $new_config = $data['config'] ?? null;
      AOTFW_Settings_Api::get_instance()->update_config( $order_status_input, $new_config );

      wp_send_json_success(array( 'message' => __("Success! Settings for", 'aotfw-domain') . ' ' . $order_status_input . ' ' .  __("have been updated.", 'aotfw-domain')));
    }

    public function ajax_get_post_categories() {
      echo json_encode( AOTFW_Settings_Api::get_instance()->get_post_categories() );
      die();
    }

    public function ajax_get_users() {
      echo json_encode( AOTFW_Settings_Api::get_instance()->get_users() );
      die();
    }

    public function ajax_get_shipping_methods() {
      echo json_encode( AOTFW_Settings_Api::get_instance()->get_shipping_methods() );
      die();
    }

    private function check_request_allowed() {
      if ( !(check_ajax_referer('eam-nonce', false, false) && current_user_can( 'manage_options' ) ) ) {
        wp_send_json_error( __('Access denied. You might need to refresh your browser page'), 403 );
      }

      $rm = $_SERVER['REQUEST_METHOD'];

      if ($rm !== 'GET' && !isset($_POST['data'] ) ) {
        wp_send_json_error( __('Data missing in request.', 'aotfw-domain'), 400 );
      }
    }


    private function expose_ajax_functions() {
      add_action( 'wp_ajax_eam_get_order_tasks_config', array( $this, 'ajax_get_order_tasks_config' ) );
      add_action( 'wp_ajax_eam_post_order_tasks_config', array( $this, 'ajax_post_order_tasks_config' ) );
      add_action( 'wp_ajax_eam_get_post_categories', array( $this, 'ajax_get_post_categories' ) );
      add_action( 'wp_ajax_eam_get_users', array( $this, 'ajax_get_users' ) );
      add_action( 'wp_ajax_eam_get_shipping_methods', array( $this, 'ajax_get_shipping_methods' ) );
    }
  }
}


?>