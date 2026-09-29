<?php

/**
 * Recibe el "code" de Google y genera los tokens OAuth
 */

if (!isset($_GET['code'])) {
    wp_die('Error: Google no devolvió ningún código.');
}

$code = sanitize_text_field($_GET['code']);

// Cargar client_secret.json
$google = json_decode(file_get_contents(dirname(__FILE__) . '/client_secret.json'), true);

$client_id     = $google['web']['client_id'];
$client_secret = $google['web']['client_secret'];
$redirect_uri  = $google['web']['redirect_uris'][0];

// Intercambiar el code por tokens
$response = wp_remote_post('https://oauth2.googleapis.com/token', [
    'body' => [
        'code'          => $code,
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri'  => $redirect_uri,
        'grant_type'    => 'authorization_code'
    ]
]);

if (is_wp_error($response)) {
    wp_die('Error al conectar con Google.');
}

$body = json_decode($response['body'], true);

// Validar respuesta
if (!isset($body['access_token'])) {
    wp_die('Error: Google no devolvió tokens.');
}

// Guardar tokens en gmail-token.json
file_put_contents(
    dirname(__FILE__) . '/gmail-token.json',
    json_encode($body, JSON_PRETTY_PRINT)
);

// Redirigir al panel
wp_redirect('https://panel.punksetter.com/?gmail=connected');
exit;
