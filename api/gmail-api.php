<?php

if (!defined('ABSPATH')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
}

require_once __DIR__ . '/gmail-service.php';

$data = punksetter_gmail_get_messages(25);

header('Content-Type: application/json; charset=utf-8');

if (is_wp_error($data)) {

    echo json_encode([
        'status' => 'error',
        'messages' => [],
        'error' => $data->get_error_message()
    ]);

    exit;
}

echo json_encode($data);

exit;
