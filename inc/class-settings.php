<?php

if (!defined('ABSPATH')) exit; // exit if accessed directly

if (!class_exists('AOTFW_Settings')) {
  /**
   * The plugin settings class
   *
   * Used to create menus, settings and options.
   *
   *
   * @since      1.0.0
   * @package    Automatic_Order_Tasks
   * @subpackage Automatic_Order_Tasks/settings
   * @author     Steven Mønsted Nielsen (contact@webirium.com)
   */
  class AOTFW_Settings {

    private static $instance;
    private $order_statuses;

    private function __construct() {
      $this->add_menu();
      $this->enqueue_settings_scripts();
    }

    public static function get_instance() {
      if (!self::$instance) {
        self::$instance = new AOTFW_Settings();
      }
      return self::$instance;
    }
    

    public function settings_html_callback() {
    ?>
      <div class="content-wrap">
        <div class="content-body">
          <div class="eam-panel" id="eam-status-manager">
            <div class="eam-row">
              <div class="eam-column-sm" id="eam-status-controls">
                <div class="eam-heavy-padded">
                  <h2><?php _e('Select Order Status','aotfw-domain') ?></h2>
                  <select name="eam-order-stage" id="eam-order-stage">
                    <?php
                    $this->order_statuses = wc_get_order_statuses();
                    foreach ($this->order_statuses as $order_status => $order_label) {
                    ?>
                      <option value="<?php echo esc_attr( $order_status ) ?>"><?php echo esc_html( $order_label ) ?></option>
                    <?php
                    }
                    ?>
                  </select>
                </div>
                <div class="eam-actions">
                  <?php $log_id = get_option( AOTFW_LOG_ID_OPTIONS_KEY ) ?>
                  <a id="view-log-link" target="_blank" download href="<?php echo !empty( $log_id ) ? wp_get_upload_dir()['baseurl'] . '/' . AOTFW_LOG_FOLDER_PREFIX . $log_id . '/' . 'logfile.txt' : ''; ?>"><?php _e('View Log', 'aotfw-domain') ?></a>
                </div>
              </div>
              <div class="eam-column-lg" id="eam-order-controls">
                <div class="eam-heavy-padded">
                  <div id="eam-order-options"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="aotfw-msg-box"></div>
    <?php
    }


    private function add_menu() {
      add_action('admin_menu', function () {
        add_submenu_page(
          'woocommerce',
          AOTFW_PLUGIN_NAME,
          AOTFW_PLUGIN_NAME,
          'manage_options',
          'automatic-order-tasks',
          array($this, 'settings_html_callback'),
          50
        );
      }, 90);
    }


    private function enqueue_settings_scripts() {
      add_action('load-' . 'woocommerce_page_automatic-order-tasks', array($this, 'action__enqueue_settings_scripts'));
    }


    public function action__enqueue_settings_scripts() {
      add_action(
        'admin_enqueue_scripts',
        function () {

          wp_enqueue_style(
            'automatic-order-tasks-settings',
            AOTFW_PLUGIN_URL . 'assets/css/automatic-order-tasks.css',
            array(),
            filemtime( AOTFW_PLUGIN_PATH . 'assets/css/automatic-order-tasks.css' )
          );

          wp_enqueue_script(
            'automatic-order-tasks-settings',
            AOTFW_PLUGIN_URL . 'assets/js/automatic-order-tasks.min.js',
            array('wp-i18n'),
            filemtime(AOTFW_PLUGIN_PATH . 'assets/js/automatic-order-tasks.min.js')
          );

          wp_set_script_translations('automatic-order-tasks-settings', 'AOTFW-domain');

          wp_add_inline_script(
            'automatic-order-tasks-settings',
            'const eam_nonce = "' . wp_create_nonce('eam-nonce') . '"',
            'before'
          );

          // vendors
          wp_enqueue_style(
            'font-awesome-6',
            AOTFW_PLUGIN_URL . 'assets/vendor/font-awesome/css/all.min.css',
            array(),
            filemtime(AOTFW_PLUGIN_PATH . 'assets/vendor/font-awesome/css/all.min.css')
          );

          wp_enqueue_script(
            'quilljs',
            AOTFW_PLUGIN_URL . 'assets/vendor/quill/quill.min.js',
            array('jquery'),
            filemtime(AOTFW_PLUGIN_PATH . 'assets/vendor/quill/quill.min.js')
          );

          wp_enqueue_style(
            'quilljs',
            AOTFW_PLUGIN_URL . 'assets/vendor/quill/quill.snow.css',
            array(),
            filemtime(AOTFW_PLUGIN_PATH . 'assets/vendor/quill/quill.snow.css')
          );

          wp_enqueue_script(
            'select2',
            AOTFW_PLUGIN_URL . 'assets/vendor/select2/js/select2.min.js',
            array('jquery'),
            filemtime(AOTFW_PLUGIN_PATH . 'assets/vendor/select2/js/select2.min.js')
          );

          wp_enqueue_style(
            'select2',
            AOTFW_PLUGIN_URL . 'assets/vendor/select2/css/select2.min.css',
            array(),
            filemtime(AOTFW_PLUGIN_PATH . 'assets/vendor/select2/css/select2.min.css')
          );
          
        }
      );
    }
  }
}
