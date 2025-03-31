<?php
/**
 * Plugin Name: WooCommerce Stripe Subscriptions
 * Plugin URI:  https://wideopen.store
 * Description: A custom subscription system for WooCommerce using Stripe and PayPal.
 * Version:     1.0.0
 * Author:      VsgDev
 * Author URI:  https://wideopen.store
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wss-subscriptions
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('WSS_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Load required classes
require_once WSS_PLUGIN_DIR . 'includes/class-wss-logger.php';
require_once WSS_PLUGIN_DIR . 'includes/class-subscription-product.php';
require_once WSS_PLUGIN_DIR . 'includes/class-checkout-handler.php';
//require_once WSS_PLUGIN_DIR . 'includes/class-webhooks-handler.php';
//require_once WSS_PLUGIN_DIR . 'includes/class-subscription-manager.php';
require_once WSS_PLUGIN_DIR . 'includes/class-admin-settings.php';


// Initialize plugin
function wss_initialize_plugin() {
    new WSS_Subscription_Product();
    new WSS_Checkout_Handler();
    //new WSS_Webhooks_Handler();
    //new WSS_Subscription_Manager();
    new WSS_Admin_Settings();
}
add_action('plugins_loaded', 'wss_initialize_plugin');


// Activation Hook
function wss_activate() {
    // Any setup code if needed
}
register_activation_hook(__FILE__, 'wss_activate');

// Deactivation Hook
function wss_deactivate() {
    // Cleanup tasks if needed
}
register_deactivation_hook(__FILE__, 'wss_deactivate');
