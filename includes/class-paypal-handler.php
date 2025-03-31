<?php
class WSS_PayPal_Handler {
    public function create_paypal_subscription($plan_id) {
        $response = wp_remote_post(PAYPAL_API_URL . "/v1/billing/subscriptions", [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode(PAYPAL_CLIENT_ID . ":" . PAYPAL_SECRET),
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
        return $body->id ?? false;
    }
}
