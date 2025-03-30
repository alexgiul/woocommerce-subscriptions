<?php
class WSS_Webhooks_Handler {
    public function __construct() {
        add_action('rest_api_init', function () {
            register_rest_route('wss/v1', '/webhook', ['methods' => 'POST', 'callback' => [$this, 'handle_webhook']]);
        });
    }

    public function handle_webhook(WP_REST_Request $request) {
        $event = json_decode($request->get_body());

        if ($event->type === 'invoice.payment_succeeded') {
            update_user_meta(get_user_by_meta('stripe_subscription_id', $event->data->object->subscription), 'subscription_status', 'active');
        } elseif ($event->type === 'customer.subscription.deleted') {
            update_user_meta(get_user_by_meta('stripe_subscription_id', $event->data->object->id), 'subscription_status', 'canceled');
        }

        return new WP_REST_Response(['status' => 'success'], 200);
    }
}
