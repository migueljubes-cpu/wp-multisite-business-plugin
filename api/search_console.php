<?php
if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| AJAX: SEARCH CONSOLE COMPLETO
|--------------------------------------------------------------------------
*/
add_action('wp_ajax_punksetter_search_console_all', 'punksetter_search_console_all');
add_action('wp_ajax_nopriv_punksetter_search_console_all', 'punksetter_search_console_all');

function punksetter_search_console_all()
{

    // Ruta del JSON (igual que GA4)
    $json_path = dirname(__FILE__, 2) . '/ga4/punksetter-1de0e5b0d1c8.json';

    if (!file_exists($json_path)) {
        wp_send_json_error("JSON no encontrado: " . $json_path);
    }

    $creds = json_decode(file_get_contents($json_path), true);

    if (!$creds || !isset($creds['client_email'])) {
        wp_send_json_error("JSON inválido o sin client_email.");
    }

    // Generar token Search Console
    $token = punksetter_sc_get_token($creds);

    if (!$token) {
        wp_send_json_error("No se pudo generar token Search Console.");
    }

    // Propiedad Search Console EXACTA
    $siteUrl = "https://punksetter.com/";

    // Rango de fechas
    $range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : "30daysAgo";

    // Convertir rango GA4 → fechas reales
    $startDate = punksetter_sc_convert_range($range);
    $endDate   = date('Y-m-d');

    // Respuesta completa
    $data = array(
        'queries' => punksetter_sc_report($token, $siteUrl, $startDate, $endDate, "query"),
        'pages'   => punksetter_sc_report($token, $siteUrl, $startDate, $endDate, "page"),
        'countries' => punksetter_sc_report($token, $siteUrl, $startDate, $endDate, "country"),
        'devices' => punksetter_sc_report($token, $siteUrl, $startDate, $endDate, "device"),
    );

    wp_send_json_success($data);
}

/*
|--------------------------------------------------------------------------
| TOKEN JWT PARA SEARCH CONSOLE
|--------------------------------------------------------------------------
*/
function punksetter_sc_get_token($creds)
{

    $jwt_header = base64_encode(json_encode([
        "alg" => "RS256",
        "typ" => "JWT"
    ]));

    $jwt_claim = base64_encode(json_encode([
        "iss"   => $creds['client_email'],
        "scope" => "https://www.googleapis.com/auth/webmasters.readonly",
        "aud"   => "https://oauth2.googleapis.com/token",
        "exp"   => time() + 3600,
        "iat"   => time()
    ]));

    openssl_sign("$jwt_header.$jwt_claim", $signature, $creds['private_key'], "XXXXXXXXX");
    $jwt_signature = base64_encode($signature);

    $jwt = "$jwt_header.$jwt_claim.$jwt_signature";

    $response = wp_remote_post("https://oauth2.googleapis.com/token", [
        "body" => [
            "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
            "assertion"  => $jwt
        ]
    ]);

    if (is_wp_error($response)) {
        return false;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    return $body['access_token'] ?? false;
}

/*
|--------------------------------------------------------------------------
| CONVERTIR RANGO GA4 → FECHAS REALES
|--------------------------------------------------------------------------
*/
function punksetter_sc_convert_range($range)
{

    switch ($range) {
        case '7daysAgo':
            return date('Y-m-d', strtotime('-7 days'));
        case '30daysAgo':
            return date('Y-m-d', strtotime('-30 days'));
        case '90daysAgo':
            return date('Y-m-d', strtotime('-90 days'));
        case '180daysAgo':
            return date('Y-m-d', strtotime('-180 days'));
        case '365daysAgo':
            return date('Y-m-d', strtotime('-365 days'));
        default:
            return date('Y-m-d', strtotime('-30 days'));
    }
}

/*
|--------------------------------------------------------------------------
| REPORTES SEARCH CONSOLE
|--------------------------------------------------------------------------
*/
function punksetter_sc_report($token, $siteUrl, $startDate, $endDate, $dimension)
{

    $body = [
        "startDate" => $startDate,
        "endDate"   => $endDate,
        "dimensions" => [$dimension],
        "rowLimit" => 1000
    ];

    $response = wp_remote_post(
        "https://searchconsole.googleapis.com/webmasters/v3/sites/" . urlencode($siteUrl) . "/searchAnalytics/query",
        [
            "headers" => [
                "Authorization" => "Bearer $token",
                "Content-Type"  => "application/json"
            ],
            "body" => json_encode($body)
        ]
    );

    return json_decode(wp_remote_retrieve_body($response), true);
}
