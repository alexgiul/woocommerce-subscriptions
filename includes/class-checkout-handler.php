<?php
class WSS_Checkout_Handler {
    public function __construct() {
        add_action('woocommerce_checkout_process', [$this, 'process_subscription_checkout']);
    }

    public function process_subscription_checkout() {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = wc_get_product($cart_item['product_id']);
            if ($product->get_type() === 'subscription') {
                $customer_id = get_user_meta(get_current_user_id(), 'stripe_customer_id', true);
                if (!$customer_id) {
                    $customer_id = $this->create_stripe_customer(WC()->customer->get_billing_email());
                    update_user_meta(get_current_user_id(), 'stripe_customer_id', $customer_id);
                }
                $subscription_id = $this->create_stripe_subscription($customer_id, $product);
                update_user_meta(get_current_user_id(), 'stripe_subscription_id', $subscription_id);
            }
        }
    }

    private function create_stripe_customer($email) {
        $response = wp_remote_post('https://api.stripe.com/v1/customers', [
            'headers' => ['Authorization' => 'Bearer ' . get_option('wss_stripe_secret_key')],
            'body' => ['email' => $email],
        ]);
        return json_decode(wp_remote_retrieve_body($response))->id ?? false;
    }

    private function create_stripe_subscription($customer_id, $product) {
        $response = wp_remote_post('https://api.stripe.com/v1/subscriptions', [
            'headers' => ['Authorization' => 'Bearer ' . get_option('wss_stripe_secret_key')],
            'body' => [
                'customer' => $customer_id,
                'items[0][price_data][currency]' => 'usd',
                'items[0][price_data][product]' => 'prod_abc',
                'items[0][price_data][unit_amount]' => get_post_meta($product->get_id(), '_subscription_price', true) * 100,
                'items[0][price_data][recurring][interval]' => 'month',
            ],
        ]);
        return json_decode(wp_remote_retrieve_body($response))->id ?? false;
    }
}
