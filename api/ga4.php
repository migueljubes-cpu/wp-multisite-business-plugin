<?php
if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| AJAX: MOTOR GA4 COMPLETO
|--------------------------------------------------------------------------
*/
add_action('wp_ajax_punksetter_ga4_all', 'punksetter_ga4_all');
add_action('wp_ajax_nopriv_punksetter_ga4_all', 'punksetter_ga4_all');

function punksetter_ga4_all()
{

    // Ruta correcta del JSON en multisite
    $json_path = dirname(__FILE__, 2) . '/ga4/punksetter-1de0e5b0d1c8.json';

    if (!file_exists($json_path)) {
        wp_send_json_error("JSON no encontrado: " . $json_path);
    }

    $creds = json_decode(file_get_contents($json_path), true);

    if (!$creds || !isset($creds['client_email'])) {
        wp_send_json_error("JSON inválido o sin client_email.");
    }

    // Generar token
    $token = punksetter_ga4_get_token($creds);

    if (!$token) {
        wp_send_json_error("No se pudo generar token GA4.");
    }

    // ID de propiedad GA4
    $property_id = "properties/YOUR_PROPERTY_ID";


    // Rango de fechas
    $range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : "30daysAgo";

    // Respuesta completa
    $data = array(
        'visitas'      => punksetter_ga4_report($token, $property_id, $range, "activeUsers", "date"),
        'paginas'      => punksetter_ga4_report($token, $property_id, $range, "screenPageViews", "pageTitle", 10),
        'paises'       => punksetter_ga4_report($token, $property_id, $range, "activeUsers", "country", 10),
        'tiempo'       => punksetter_ga4_report($token, $property_id, $range, "averageSessionDuration", "pageTitle", 10),
        'dispositivos' => punksetter_ga4_report($token, $property_id, $range, "activeUsers", "deviceCategory"),
        'eventos'      => punksetter_ga4_report($token, $property_id, $range, "eventCount", "eventName", 10),
        'conversiones' => punksetter_ga4_report($token, $property_id, $range, "conversions", "eventName", 10),
        'carritos'     => punksetter_ga4_event_report($token, $property_id, $range, "add_to_cart"),
        'compras'      => punksetter_ga4_event_report($token, $property_id, $range, "purchase"),
    );

    wp_send_json_success($data);
}

/*
|--------------------------------------------------------------------------
| TOKEN JWT PARA GOOGLE ANALYTICS 4
|--------------------------------------------------------------------------
*/
function punksetter_ga4_get_token($creds)
{

    $jwt_header = base64_encode(json_encode([
        "alg" => "RS256",
        "typ" => "JWT"
    ]));

    $jwt_claim = base64_encode(json_encode([
        "iss"   => $creds['client_email'],
        "scope" => "https://www.googleapis.com/auth/analytics.readonly",
        "aud"   => "https://oauth2.googleapis.com/token",
        "exp"   => time() + 3600,
        "iat"   => time()
    ]));

    openssl_sign("$jwt_header.$jwt_claim", $signature, $creds['private_key'], "XXXXXXXX");
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
| REPORTES GENERALES GA4
|--------------------------------------------------------------------------
*/
function punksetter_ga4_report($token, $property_id, $range, $metric, $dimension, $limit = null)
{

    $body = [
        "dateRanges" => [
            ["startDate" => $range, "endDate" => "today"]
        ],
        "metrics" => [
            ["name" => $metric]
        ],
        "dimensions" => [
            ["name" => $dimension]
        ]
    ];

    if ($limit) {
        $body["limit"] = $limit;
    }

    $response = wp_remote_post(
        "https://analyticsdata.googleapis.com/v1beta/$property_id:runReport",
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

/*
|--------------------------------------------------------------------------
| REPORTES DE EVENTOS (CARROS / COMPRAS)
|--------------------------------------------------------------------------
*/
function punksetter_ga4_event_report($token, $property_id, $range, $event_name)
{

    $body = [
        "dateRanges" => [
            ["startDate" => $range, "endDate" => "today"]
        ],
        "metrics" => [
            ["name" => "eventCount"]
        ],
        "dimensions" => [
            ["name" => "date"]
        ],
        "dimensionFilter" => [
            "filter" => [
                "fieldName" => "eventName",
                "stringFilter" => [
                    "matchType" => "EXACT",
                    "value"     => $event_name
                ]
            ]
        ]
    ];

    $response = wp_remote_post(
        "https://analyticsdata.googleapis.com/v1beta/$property_id:runReport",
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

if (!function_exists('punksetter_ga4_fetch_summary')) {



    function punksetter_ga4_fetch_summary()
    {

        $json_path = dirname(__FILE__, 2) . '/ga4/punksetter-1de0e5b0d1c8.json';

        if (!file_exists($json_path)) {
            return [
                'visitas' => 0,
                'conversiones' => 0
            ];
        }

        $creds = json_decode(file_get_contents($json_path), true);

        if (!$creds || !isset($creds['client_email'])) {
            return [
                'visitas' => 0,
                'conversiones' => 0
            ];
        }

        $token = punksetter_ga4_get_token($creds);

        if (!$token) {
            return [
                'visitas' => 0,
                'conversiones' => 0
            ];
        }

        $property_id = "properties/268648660";

        $visitas_report = punksetter_ga4_report(
            $token,
            $property_id,
            "30daysAgo",
            "activeUsers",
            "date"
        );

        $conversiones_report = punksetter_ga4_report(
            $token,
            $property_id,
            "30daysAgo",
            "conversions",
            "eventName",
            10
        );

        $visitas = 0;
        $conversiones = 0;

        if (!empty($visitas_report['rows'])) {
            foreach ($visitas_report['rows'] as $row) {
                $visitas += intval($row['metricValues'][0]['value']);
            }
        }

        if (!empty($conversiones_report['rows'])) {
            foreach ($conversiones_report['rows'] as $row) {
                $conversiones += intval($row['metricValues'][0]['value']);
            }
        }

        return [
            'visitas' => $visitas,
            'conversiones' => $conversiones
        ];
    }
}
