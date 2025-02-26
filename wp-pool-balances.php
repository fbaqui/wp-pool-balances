<?php
/**
 * Plugin Name: WordPress Pool Balances Plugin
 * Description: Plugin para exibir os saldos das pools de liquidez na Meteora.
 * Version: 1.0.0
 * Author: Felipe Baqui
 * Author URI: https://github.com/fbaqui
 * License: GPL v2 ou posterior
 * Text Domain:wp-pool-balances
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    die('Direct access not permitted');
}
// Define plugin constants
define('POOL_BALANCER_VERSION', '1.0.0');
define('POOL_BALANCER_PATH', plugin_dir_path(__FILE__));
define('POOL_BALANCER_URL', plugin_dir_url(__FILE__));
// Activation function
function pool_balancer_activate() {
    global $wpdb;
    $table_prefix = $wpdb->prefix;
    $table_name = $table_prefix . 'pool_balancer_pools';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        pool_id varchar(255) NOT NULL,
        balance decimal(20,8) DEFAULT NULL,
        update_time datetime DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    add_option('pool_balancer_settings', array());
}
// Deactivation function
function pool_balancer_deactivate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pool_balancer_pools';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    
    delete_option('pool_balancer_settings');
}
// Register activation/deactivation hooks
register_activation_hook(__FILE__, 'pool_balancer_activate');
register_deactivation_hook(__FILE__, 'pool_balancer_deactivate');
// Initialize the plugin
function pool_balancer_init() {
    // Include core classes
    require_once(POOL_BALANCER_PATH . 'includes/class-pool-settings.php');
    require_once(POOL_BALANCER_PATH . 'includes/class-pool-api.php');
    require_once(POOL_BALANCER_PATH . 'includes/class-pool-display.php');
    // Initialize classes
    $settings = new ClassPoolSettings();
    $api = new ClassPoolAPI();
    $display = new ClassPoolDisplay();
    // Register hooks
    add_action('admin_menu', array($settings, 'add_plugin_menu'));
    add_shortcode('pool_balances', array($display, 'display_pool_balances'));
}
// Initialize the plugin
add_action('plugins_loaded', 'pool_balancer_init');
// Load admin scripts
function pool_balancer_admin_scripts() {
    wp_enqueue_style(
        'pool-balancer-admin',
        POOL_BALANCER_URL . 'assets/css/admin.css',
        array(),
        POOL_BALANCER_VERSION
    );
    wp_enqueue_script(
        'pool-balancer-admin',
        POOL_BALANCER_URL . 'assets/js/admin.js',
        array('jquery'),
        POOL_BALANCER_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'pool_balancer_admin_scripts');
// Load frontend scripts
function pool_balancer_frontend_scripts() {
    wp_enqueue_style(
        'pool-balancer-frontend',
        POOL_BALANCER_URL . 'assets/css/frontend.css',
        array(),
        POOL_BALANCER_VERSION
    );
    wp_enqueue_script(
        'pool-balancer-frontend',
        POOL_BALANCER_URL . 'assets/js/frontend.js',
        array('jquery'),
        POOL_BALANCER_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'pool_balancer_frontend_scripts');
