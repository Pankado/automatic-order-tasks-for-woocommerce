<?php

if ( !defined( 'ABSPATH' ) ) exit; // exit if accessed directly


if ( !class_exists( 'AOTFW_Abstract_Order_Task' ) ) {
  /**
   * Abstract base class for an Automatic Order Task
   *
   *
   * @since      1.0.0
   * @package    Automatic_Order_Tasks
   * @subpackage Automatic_Order_Tasks/settings
   * @author     Steven Mønsted Nielsen (contact@webirium.com)
   */
  abstract class AOTFW_Abstract_Order_Task {

    private $task_ID;
    private $tag_replaces_map = [];

    protected $args;
    protected $defaults = array( 
      'disabled'    => false,
      'conditions'   => null
    );

    public function __construct( $task_ID, $args ) {
      $this->task_ID = $task_ID;
      $this->args = wp_parse_args( $args, $this->defaults );
    }


    public abstract function do_task( $order );


    public function get_task_ID() {
      return $this->task_ID;
    }


    public function to_json() {
      $json = json_encode( array( 
        'task_ID' => $this->task_ID,
        'args' => $this->args
      ) );
      return $json;
    }

    protected function add_tag( $context, $name, $replace_callback ) {
      $this->tag_replaces_map[$context]['{{' . $name . '}}'] = $replace_callback();
    }
    
    protected function add_default_tags_for_field( $context, $order ) {
      $c = $context;
      $this->add_tag( $c, 'order id', function() use ($order) {
        return $order->get_id();
      });
      $this->add_tag( $c, 'billing name', function() use ($order) {
        return $order->get_formatted_billing_full_name();
      });
      $this->add_tag( $c, 'shipping name', function() use ($order) {
        return $order->get_formatted_shipping_full_name();
      });
    }

    protected function add_default_tags_for_textarea( $context, $order ) {
      $c = $context;
      $this->add_tag( $c, 'order id', function() use ($order) {
        return $order->get_id();
      });
      $this->add_tag( $c, 'order details', function() use ( $order ) {
        ob_start();
        WC_Emails::instance()->order_details( $order );
        return ob_get_clean();
      });
      $this->add_tag( $c, 'billing email', function() use ( $order ) {
        return $order->get_billing_email();
      });
      $this->add_tag( $c, 'billing name', function() use ( $order) {
        return $order->get_formatted_billing_full_name();
      });
      $this->add_tag( $c, 'billing address', function() use ( $order) {
        return $order->get_formatted_billing_address();
      });
      $this->add_tag( $c, 'shipping name', function() use ( $order) {
        return $order->get_formatted_shipping_full_name();
      });
      $this->add_tag( $c, 'shipping address', function() use ( $order) {
        return $order->get_formatted_shipping_address();
      });
    }

    protected function parse_tags( $context, $content ) { //TODO: Optimize this so only used tags are getting extracted
      $tags = array_keys( $this->tag_replaces_map[$context] );
      $replaces = array_values( $this->tag_replaces_map[$context] );
      return str_replace( $tags, $replaces, $content );
    }
  }
}

// SEND MAIL ORDER TASK //
if ( !class_exists( 'AOTFW_Sendmail_Order_Task' ) ) {
  class AOTFW_Sendmail_Order_Task extends AOTFW_Abstract_Order_Task {

    public function __construct( $args ) {
      $this->defaults = array_merge( $this->defaults, array(
        'subject' => 'No subject',
        'recipients' => '',
        'message' => ''
      ));

      parent::__construct( 'sendmail', $args );
    }
  
    public function do_task( $order ) {

      $this->set_tag_replacement_map( $order );
      
      $recipients = array_map( function($recipient) {
        return $recipient['value'];
      }, $this->args['recipients']);

      $recipients = $this->parse_tags( 'recipients', $recipients );
      $recipients = array_map( function($recipient) {
        return sanitize_email( $recipient );
      }, $recipients );
 
      $subject = sanitize_text_field( $this->args['subject'] );
      $subject = $this->parse_tags( 'subject', $subject );

      $content = wp_kses_post( $this->args['message'] );
      $content = $this->parse_tags( 'content', $content );

      $wc_mail = WC_Emails::instance();

      $content = $wc_mail->wrap_message( $subject, $content );

      foreach ( $recipients as $recipient ) {
        $wc_mail->send( $recipient, $subject, $content );
      }
    }

    private function set_tag_replacement_map( $order ) {
      $this->set_recipients_tag_map( $order );
      $this->set_subject_tag_map( $order );
      $this->set_content_tag_map( $order );
    }

    private function set_recipients_tag_map( $order ) {
      $c = 'recipients';

      $this->add_tag( $c, 'admin email', function() {
        return get_bloginfo('admin_email');
      });
      $this->add_tag( $c, 'billing email', function() use ($order) {
        return $order->get_billing_email();
      });
    }

    private function set_subject_tag_map( $order ) {
      $this->add_default_tags_for_field( 'subject', $order );
    }

    private function set_content_tag_map( $order ) {
      $this->add_default_tags_for_textarea( 'content', $order );
    }

  }
}

// CREATE POST ORDER TASK //
if ( !class_exists('AOTFW_Createpost_Order_Task') ) {
  class AOTFW_Createpost_Order_Task extends AOTFW_Abstract_Order_Task {

    public function __construct( $args ) {
      $this->defaults = array_merge( $this->defaults, array(
        'subject' => '',
        'content' => '',
        'categories' => array(),
        'author' => 1,
      ));

      parent::__construct( 'createpost', $args );
    }

    public function do_task( $order ) {
      $this->set_tag_replacement_map( $order );

      $subject = $this->args['subject'];
      $subject = $this->parse_tags( 'subject', $subject );

      $content = $this->args['content'];
      $content = $this->parse_tags( 'content', $content );

      $categories = $this->args['categories'];
      $categories = preg_replace( '/[^0-9]/', '', $categories );

      $author = $this->args['author'];
      $author = $this->parse_tags( 'author', $author );
      $author = preg_replace( '/[^0-9]/', '', $author );

      $new_post = array(
        'post_title' => $subject,
        'post_content' => $content,
        'post_status' => 'publish',
        'post_author' => $author,
        'post_category' => $categories
      );

      wp_insert_post( $new_post ); // WP handles sanitization here. Thank you WP!
    }

    private function set_tag_replacement_map( $order ) {
      $this->add_default_tags_for_field( 'subject', $order );
      $this->add_default_tags_for_textarea( 'content', $order );
      
      $this->add_tag( 'author', 'customer', function() use ( $order ) {
        $customer_id = $order->get_customer_id();

        if (!$customer_id) {
          $customer_id = $this->defaults['author'];
        }
        return $customer_id;
      });
    }
  }
}

// LOG TO FILE ORDER TASK //
if ( !class_exists('AOTFW_Logtofile_Order_Task') ) {
  class AOTFW_Logtofile_Order_Task extends AOTFW_Abstract_Order_Task {

    public function __construct( $args ) {
      $this->defaults = array_merge( $this->defaults, array(
        'content' => ''
      ));

      parent::__construct( 'logtofile', $args );
    }

    public function do_task( $order ) {
      $this->set_tag_replacement_map( $order );

      $content = $this->args['content'];
      $content = $this->parse_tags( 'content', $content );

      $content = str_ireplace( array("<br />","<br>","<br/>"), "\n", $content ); // convert break tags to newlines

      $this->maybe_create_folder();

      $log_id = get_option( AOTFW_LOG_ID_OPTIONS_KEY );
      $log_upload_dir = wp_normalize_path( wp_get_upload_dir()['basedir'] ) . '/' . AOTFW_LOG_FOLDER_PREFIX . $log_id;

      $current_date = current_time( 'F d, Y H:i:s' );
      $order_id = $order->get_id();

      $start_str = "------- ${current_date} | order ${order_id} ----------------" . PHP_EOL;

      $file = fopen($log_upload_dir . '/logfile.txt', 'a' );
      fwrite( $file, $start_str );
      fwrite( $file, $content . PHP_EOL . PHP_EOL );

      fclose( $file );
    }

    private function maybe_create_folder() {
      $log_id = get_option( AOTFW_LOG_ID_OPTIONS_KEY );

      $upload_dir = wp_normalize_path( wp_get_upload_dir()['basedir'] );

      if ( empty( $log_id ) || !is_dir( $upload_dir . '/' . AOTFW_LOG_FOLDER_PREFIX . $log_id ) ) {
        $log_id = uniqid();
        update_option( AOTFW_LOG_ID_OPTIONS_KEY, $log_id );

        mkdir( $upload_dir . '/' . AOTFW_LOG_FOLDER_PREFIX . $log_id );
      }
    }

    private function set_tag_replacement_map( $order ) {
      $this->add_default_tags_for_textarea( 'content', $order );
    }
  }
}

// CUSTOM ORDER FIELD ORDER TASK //
if ( !class_exists('AOTFW_CustomOrderfield_Order_Task') ) {
  class AOTFW_CustomOrderfield_Order_Task extends AOTFW_Abstract_Order_Task {
    public function __construct( $args ) {
      $this->defaults = array_merge( $this->defaults, array(
        'name' => '',
        'value' => ''
      ));

      parent::__construct( 'customorderfield', $args );
    }

    public function do_task( $order ) {
      $this->set_tag_replacement_map( $order );

      $name = $this->args['name'];
      $name = $this->parse_tags( 'name', $name );
      $name = sanitize_text_field( $name );

      $value = $this->args['value'];
      $value = $this->parse_tags( 'value', $value );
      $value = wp_kses_post( $value );

      update_post_meta( $order->get_id(), $name, $value );
     
    }

    private function set_tag_replacement_map( $order ) {
      $this->add_default_tags_for_field( 'name', $order );
      $this->add_default_tags_for_textarea( 'value', $order );
    }
  }
}

// CHANGE SHIPPING ORDER TASK //
if ( !class_exists('AOTFW_Changeshipping_Order_Task') ) {
  class AOTFW_Changeshipping_Order_Task extends AOTFW_Abstract_Order_Task {
    public function __construct( $args ) {
      $this->defaults = array_merge( $this->defaults, array(
        'new_shipping_name' => '',
        'new_shipping_method' => '',
      ));

      parent::__construct( 'changeshipping', $args );
    }

    public function do_task( $order ) {
      $this->set_tag_replacement_map( $order );

      $new_shipping_name = $this->args['new_shipping_name'];
      $new_shipping_name = $this->parse_tags( 'new_shipping_name', $new_shipping_name );
      $new_shipping_name = sanitize_text_field( $new_shipping_name );

      $new_shipping_method = $this->args['new_shipping_method'];
      $new_shipping_method = sanitize_key( $new_shipping_method );

      // TODO: Add functionality to calculate new cost. 
      // Array for tax calculations
      // $calculate_tax_for = array(
      //   'country'  => $order->get_shipping_country(),
      //   'state'    => $order->get_shipping_state(),
      //   'postcode' => $order->get_shipping_postcode(),
      //   'city'     => $order->get_shipping_city(),
      // );

      // $changed = false;

      foreach ( $order->get_items('shipping') as $item_id => $item ) {
        if ( !empty( $new_shipping_name ) ) {
          $item->set_method_title( $new_shipping_name );
        }

        $shipping_methods = WC()->shipping->get_shipping_methods();

        foreach ( $shipping_methods as $id => $shipping_method ) {
          if ( $shipping_method->id === $new_shipping_method ) {
            $item->set_method_id( $shipping_method->get_rate_id() );
            $item->save();

            //$changed = true;
          }
        }
      }
      // if ( $changed ) {
      //   $order->calculate_totals();
      // }
    }

    private function set_tag_replacement_map( $order ) {
      $this->add_default_tags_for_field( 'new_shipping_name', $order );
    }
  }
}

// SEND WEBHOOK ORDER TASK //
if ( !class_exists('AOTFW_Sendwebhook_Order_Task') ) {
  class AOTFW_Sendwebhook_Order_Task extends AOTFW_Abstract_Order_Task {
    public function __construct( $args ) {
      $this->defaults = array_merge( $this->defaults, array(
        'delivery_url' => '',
        'secret' => '',
      ));

      parent::__construct( 'sendwebhook', $args );
    }

    public function do_task( $order ) {
      $delivery_url = $this->args['delivery_url'];
      $delivery_url = esc_url_raw( $delivery_url );

      $secret = $this->args['secret'];
      $secret = sanitize_text_field( $secret );

      $webhook = new WC_Webhook();
      $webhook->set_delivery_url( $delivery_url );
      $webhook->set_secret( $secret );
      $webhook->set_topic( 'action.wc_order-' . $order->get_status() );

      $webhook->deliver( $order->get_data() );
    }
  }
}


// TRASH ORDER ORDER TASK //
if ( !class_exists('AOTFW_Trashorder_Order_Task') ) {
  class AOTFW_Trashorder_Order_Task extends AOTFW_Abstract_Order_Task {
    public function __construct( $args ) {
      $this->defaults = array_merge( $this->defaults, array(
        'reason' => '',
      ));

      parent::__construct( 'trashorder', $args );
    }

    public function do_task( $order ) {
      $reason = $this->args['reason'];
      $reason = sanitize_text_field( $reason );

      if ( !empty( $reason ) ) {
        update_post_meta( $order->get_id(), 'trash_reason', $reason );
      }

      add_action( 'shutdown', function() use ( $order ) { // delayed execution
        wp_trash_post( $order->get_id() );
      } );
    }
  }
}
?>

?>