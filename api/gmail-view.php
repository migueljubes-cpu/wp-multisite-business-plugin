<?php
header('Content-Type: application/json');

// Cargar WordPress correctamente desde la raíz
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

// Cargar token
$token_file = dirname(__FILE__) . '/gmail-token.json';
$tokens = json_decode(file_get_contents($token_file), true);

$access_token = $tokens['access_token'];

// ID del correo
$id = sanitize_text_field($_GET['id']);

// URL Gmail API
$url = "https://gmail.googleapis.com/gmail/v1/users/me/messages/$id?format=full";

// Llamada a Gmail API usando WordPress
$response = wp_remote_get($url, [
    'headers' => [
        'Authorization' => "Bearer $access_token"
    ]
]);

// Errores
if (is_wp_error($response)) {
    echo json_encode(['error' => $response->get_error_message()]);
    exit;
}

// Respuesta Gmail
$body = json_decode($response['body'], true);

echo json_encode($body);
exit;
