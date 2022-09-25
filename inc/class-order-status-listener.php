<?php 
if ( !defined( 'ABSPATH' ) ) exit; // exit if accessed directly

if ( !class_exists( 'AOTFW_Order_Status_Listener' ) ) {
  class AOTFW_Order_Status_Listener {

    private static $instance = null;
    
    
    private function __construct() {
      add_action( 'woocommerce_order_status_changed', array( $this, 'action__do_tasks' ), 10, 3 );
    }
    

    public static function get_instance() {
      if ( !self::$instance ) {
        self::$instance = new AOTFW_Order_Status_Listener();
      }
      return self::$instance;
    }

    
    public function action__do_tasks( $order_id, $old_status, $new_status ) {
      $this->require_tasks(); // requiring tasks late, as the file is only necessary when executing tasks.
      
      $task_factory = AOTFW_Order_Task_Factory::get_instance();
      $settings_api = AOTFW_Settings_Api::get_instance();
      $order = wc_get_order( $order_id );

      $new_status = 'wc-' . $new_status; // add the wc prefix

      $config = json_decode( $settings_api->get_config( $new_status ), true );

      if ( !empty( $config ) ) {
        foreach ( $config as $task_config ) {
          if ( !empty( $task_config ) && isset( $task_config['id'] ) ) {
            $task = $task_factory->get( $task_config['id'], $task_config['fields'] );
            $task->do_task( $order );
          }
        }

      }

    }

    private function require_tasks() {
      require_once( AOTFW_PLUGIN_PATH . 'inc/tasks/class-order-task.php' );
      require_once( AOTFW_PLUGIN_PATH . 'inc/tasks/class-order-task-factory.php' );

    }
  }
}

?>