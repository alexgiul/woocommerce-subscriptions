<?php
if (!defined('ABSPATH')) {
    exit;
}


class WSS_Subscription_Manager {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function create_subscription($user_id, $payment_method, $price, $interval) {
        if ($payment_method === 'stripe') {
            return $this->create_stripe_subscription($user_id, $price, $interval);
        } elseif ($payment_method === 'paypal') {
            return $this->create_paypal_subscription($user_id, $price, $interval);
        }
        return false;
    }

    public function cancel_subscription($user_id, $payment_method) {
        if ($payment_method === 'stripe') {
            return $this->cancel_stripe_subscription($user_id);
        } elseif ($payment_method === 'paypal') {
            return $this->cancel_paypal_subscription($user_id);
        }
        return false;
    }

    private function create_stripe_subscription($user_id, $price, $interval) {
        $customer_id = get_user_meta($user_id, 'wss_stripe_customer_id', true);
        if (!$customer_id) {
            return false;
        }

        $response = wp_remote_post('https://api.stripe.com/v1/subscriptions', [
            'headers' => [
                'Authorization' => 'Bearer ' . WSS_STRIPE_SECRET_KEY,
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ],
            'body' => [
                'customer' => $customer_id,
                'items[0][price_data][currency]' => 'usd',
                'items[0][price_data][unit_amount]' => $price * 100,
                'items[0][price_data][recurring][interval]' => $interval,
            ],
        ]);

        $body = json_decode(wp_remote_retrieve_body($response));
        if (isset($body->id)) {
            update_user_meta($user_id, 'wss_stripe_subscription_id', $body->id);
            return $body->id;
        }
        return false;
    }

    private function cancel_stripe_subscription($user_id) {
        $subscription_id = get_user_meta($user_id, 'wss_stripe_subscription_id', true);
        if (!$subscription_id) {
            return false;
        }

        $response = wp_remote_post("https://api.stripe.com/v1/subscriptions/$subscription_id", [
            'method' => 'DELETE',
            'headers' => ['Authorization' => 'Bearer ' . WSS_STRIPE_SECRET_KEY],
        ]);

        if (wp_remote_retrieve_response_code($response) === 200) {
            update_user_meta($user_id, 'wss_subscription_status', 'canceled');
            return true;
        }
        return false;
    }

    private function create_paypal_subscription($user_id, $plan_id) {
        $response = wp_remote_post(WSS_PAYPAL_API_URL . "/v1/billing/subscriptions", [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(WSS_PAYPAL_CLIENT_ID . ":" . WSS_PAYPAL_SECRET),
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'plan_id' => $plan_id,
                'start_time' => gmdate("Y-m-d\TH:i:s\Z", strtotime("+1 minute")),
                'subscriber' => ['email_address' => wp_get_current_user()->user_email],
                'application_context' => ['return_url' => home_url('/subscription-success')],
            ]),
        ]);

        $body = json_decode(wp_remote_retrieve_body($response));
        if (isset($body->id)) {
            update_user_meta($user_id, 'wss_paypal_subscription_id', $body->id);
            return $body->id;
        }
        return false;
    }

    private function cancel_paypal_subscription($user_id) {
        $subscription_id = get_user_meta($user_id, 'wss_paypal_subscription_id', true);
        if (!$subscription_id) {
            return false;
        }

        $response = wp_remote_post(WSS_PAYPAL_API_URL . "/v1/billing/subscriptions/$subscription_id/cancel", [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(WSS_PAYPAL_CLIENT_ID . ":" . WSS_PAYPAL_SECRET),
                'Content-Type'  => 'application/json',
            ],
        ]);

        if (wp_remote_retrieve_response_code($response) === 204) {
            update_user_meta($user_id, 'wss_subscription_status', 'canceled');
            return true;
        }
        return false;
    }
}
