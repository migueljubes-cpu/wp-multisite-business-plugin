<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/gmail-service.php';

function punksetter_gmail_api_core()
{

    $data = punksetter_gmail_get_messages(5);

    return $data;
}
