<?php

/**
 * Inicia OAuth con Google para conectar Gmail
 */

require_once dirname(__FILE__) . '/client_secret.json';

// Cargar el JSON con client_id y client_secret
$google = json_decode(file_get_contents(dirname(__FILE__) . '/client_secret.json'), true);

$client_id     = $google['web']['client_id'];
$redirect_uri  = $google['web']['redirect_uris'][0];

// Scopes necesarios para leer Gmail
$scope = urlencode('https://www.googleapis.com/auth/gmail.readonly');

// URL de autorización de Google
$auth_url = "https://accounts.google.com/o/oauth2/auth?" . http_build_query([
    'client_id'     => $client_id,
    'redirect_uri'  => $redirect_uri,
    'response_type' => 'code',
    'access_type'   => 'offline',
    'prompt'        => 'consent',
    'scope'         => 'https://www.googleapis.com/auth/gmail.readonly'
]);

// Redirigir al usuario a Google
wp_redirect($auth_url);
exit;
