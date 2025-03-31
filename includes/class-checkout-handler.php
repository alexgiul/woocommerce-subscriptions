<?php
class WSS_Checkout_Handler {
    public function __construct() {
        add_action('woocommerce_checkout_process', [$this, 'process_subscription_checkout']);
        //add_action('woocommerce_review_order_before_payment', 'add_payment_options');
    }

    function add_payment_options() {
        echo '<p><label><input type="radio" name="subscription_payment_method" value="stripe" checked> Pay with Stripe</label></p>';
        echo '<p><label><input type="radio" name="subscription_payment_method" value="paypal"> Pay with PayPal</label></p>';
    }

    public function process_subscription_checkout() {

        /*
        if (!isset($_POST['subscription_payment_method'])) {
            wc_add_notice(__('Please select a payment method for your subscription.', 'woocommerce'), 'error');
            return;
        }
        $payment_method = sanitize_text_field($_POST['subscription_payment_method']);
        */

        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = wc_get_product($cart_item['product_id']);
            if ($product->get_type() === 'subscription') {

                WSS_Logger::log('Product: ' . print_r($product, true));

                $email = WC()->customer->get_billing_email();
                // Get product details
                $product_data = [
                    'id' => $product->get_id(),
                    'name' => $product->get_name(),
                    'price' => $product->get_price(),
                    'billing_interval' => get_post_meta($product->get_id(), '_subscription_interval', true),
                    'billing_period' => get_post_meta($product->get_id(), '_subscription_period', true),
                ];

            $this->call_lambda($email, $product_data);

            }
        }
    }


    private function call_lambda($email, $product_data) {
        $response = wp_remote_post('https://wleheuq7vmhjefvimliqln7i6e0hzoga.lambda-url.eu-central-1.on.aws/v1/create-subscription', [
            'headers' => [
                'api_key' => 'day-trading-signals-20250305-key',
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'email' => $email,
                'product' => $product_data,
            ]),
        ]);    
    
        WSS_Logger::log('API Response: ' . print_r($response, true));
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
