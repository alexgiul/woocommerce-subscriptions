<?php
if (!defined('ABSPATH')) {
    exit;
}

class WSS_Logger {
    public static function log($message) {
        $log_file = WP_CONTENT_DIR . '/wss-subscriptions.log';
        $timestamp = date('[Y-m-d H:i:s]');
        file_put_contents($log_file, "$timestamp $message" . PHP_EOL, FILE_APPEND);
    }
}
