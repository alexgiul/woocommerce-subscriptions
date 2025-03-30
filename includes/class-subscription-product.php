<?php
class WSS_Subscription_Product {
    public function __construct() {
        add_filter('product_type_selector', [$this, 'add_subscription_product_type']);
        add_action('woocommerce_product_options_general_product_data', [$this, 'add_subscription_fields']);
        add_action('woocommerce_process_product_meta', [$this, 'save_subscription_fields']);
    }

    public function add_subscription_product_type($types) {
        $types['subscription'] = __('Subscription', 'woocommerce');
        return $types;
    }

    public function add_subscription_fields() {
        echo '<div class="options_group">';
        woocommerce_wp_text_input(['id' => '_subscription_price', 'label' => 'Subscription Price ($)', 'type' => 'number']);
        woocommerce_wp_text_input(['id' => '_subscription_interval', 'label' => 'Billing Interval (Months)', 'type' => 'number']);
        echo '</div>';
    }

    public function save_subscription_fields($post_id) {

        $price = $_POST['_subscription_price'];
        $interval = $_POST['_subscription_interval'];
    
        if (!empty($price)) {
            update_post_meta($post_id, '_subscription_price', esc_attr($price));
        }
        if (!empty($interval)) {
            update_post_meta($post_id, '_subscription_interval', esc_attr($interval));
        }
    }
}
