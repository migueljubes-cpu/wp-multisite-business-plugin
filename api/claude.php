<?php
if (! defined('ABSPATH')) exit;

/* Cargar API Key */
function punksetter_get_claude_api_key()
{

    $key = get_option('punksetter_claude_api_key');

    if ($key && strlen($key) > 10) {
        return $key;
    }

    return false;
}

/* Cargar Workspace ID */
function punksetter_get_claude_workspace_id()
{

    $wid = get_option('punksetter_claude_workspace_id');

    if ($wid && strlen($wid) > 10) {
        return $wid;
    }

    return false;
}

/* Enviar mensaje a Claude IA */
function punksetter_claude_query($mensaje)
{

    $claude_api_key = punksetter_get_claude_api_key();
    $workspace_id   = punksetter_get_claude_workspace_id();

    if (!$claude_api_key) {
        return new WP_Error('no_api_key', 'Claude API Key no configurada.');
    }

    if (!$workspace_id) {
        return new WP_Error('no_workspace', 'Workspace ID no configurado.');
    }

    $url = "https://api.anthropic.com/v1/messages";

    /* MODELO REAL DE TU CUENTA */
    $body = [
        "model" => "claude-sonnet-5",
        "max_tokens" => 800,
        "messages" => [
            [
                "role" => "user",
                "content" => [
                    [
                        "type" => "text",
                        "text" => $mensaje
                    ]
                ]
            ]
        ]
    ];

    $args = [
        "headers" => [
            "Content-Type"            => "application/json",
            "x-api-key"               => $claude_api_key,
            "anthropic-version"       => "2023-06-01",
            "anthropic-workspace-id"  => $workspace_id,
            "anthropic-beta"          => "messages-2023-12-15"
        ],
        "body" => json_encode($body),
        "timeout" => 30
    ];

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = wp_remote_retrieve_response_code($response);
    $raw  = wp_remote_retrieve_body($response);

    if ($code !== 200) {
        return new WP_Error('claude_error', "Error Claude ($code): $raw");
    }

    $json = json_decode($raw, true);

    error_log('CLAUDE RAW BODY:');
    error_log($raw);

    error_log('CLAUDE RAW RESPONSE:');
    error_log(print_r($json, true));


    if (isset($json['content']) && is_array($json['content'])) {

        foreach ($json['content'] as $block) {

            if (
                isset($block['type']) &&
                $block['type'] === 'text'
            ) {

                return [
                    'content' => $block['text']
                ];
            }
        }
    }

    return new WP_Error(
        'claude_format',
        'Formato de respuesta IA no válido.'
    );
}
