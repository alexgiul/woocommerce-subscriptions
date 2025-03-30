<?php
class WSS_Admin_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'create_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function create_admin_menu() {
        add_menu_page('Stripe Subscriptions', 'Stripe Subscriptions', 'manage_options', 'wss-settings', [$this, 'settings_page']);
    }

    public function register_settings() {
        register_setting('wss_settings', 'wss_stripe_secret_key');
    }

    public function settings_page() {
        echo '<form method="post" action="options.php">';
        settings_fields('wss_settings');
        do_settings_sections('wss_settings');
        echo '<label>Stripe Secret Key: <input type="text" name="wss_stripe_secret_key" value="' . esc_attr(get_option('wss_stripe_secret_key')) . '" /></label>';
        submit_button();
        echo '</form>';
    }
}
