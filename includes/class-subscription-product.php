<?php
class WSS_Subscription_Product {
    public function __construct() {
        add_filter('product_type_selector', [$this, 'add_subscription_product_type']);
        add_action('woocommerce_product_options_general_product_data', [$this, 'add_subscription_fields']);
        add_action('woocommerce_process_product_meta', [$this, 'save_subscription_fields']);
    }

    // Add subscription product type
    public function add_subscription_product_type($types) {
        $types['subscription'] = __('Subscription', 'woocommerce');
        return $types;
    }

    // Add subscription fields to the product edit page
    public function add_subscription_fields() {
        echo '<div class="options_group">';

        // Subscription Price
        woocommerce_wp_text_input([
            'id' => '_subscription_price',
            'label' => __('Subscription Price ($)', 'woocommerce'),
            'type' => 'number',
            'custom_attributes' => ['step' => '0.01', 'min' => '0']
        ]);

        // Billing Interval Value
        woocommerce_wp_text_input([
            'id' => '_subscription_interval',
            'label' => __('Billing Interval', 'woocommerce'),
            'type' => 'number',
            'custom_attributes' => ['min' => '1']
        ]);

        // Billing Interval Unit (Dropdown)
        woocommerce_wp_select([
            'id' => '_subscription_period',
            'label' => __('Billing Period', 'woocommerce'),
            'options' => [
                'day'   => __('Day', 'woocommerce'),
                'week'  => __('Week', 'woocommerce'),  // ✅ Added "Week"
                'month' => __('Month', 'woocommerce'),
                'year'  => __('Year', 'woocommerce'),
            ],
        ]);

        echo '</div>';
    }

    // Save the subscription fields
    public function save_subscription_fields($post_id) {
        if (isset($_POST['_subscription_price'])) {
            update_post_meta($post_id, '_subscription_price', esc_attr($_POST['_subscription_price']));
        }

        if (isset($_POST['_subscription_interval'])) {
            update_post_meta($post_id, '_subscription_interval', esc_attr($_POST['_subscription_interval']));
        }

        if (isset($_POST['_subscription_period'])) {
            update_post_meta($post_id, '_subscription_period', esc_attr($_POST['_subscription_period']));
        }
    }
}
