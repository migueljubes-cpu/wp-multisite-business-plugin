<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   LOG SEGURO
   ============================================================ */
add_action('init', function () {
    error_log("IA.PHP CARGADO DESDE INIT: " . __FILE__);
});

/* ============================================================
   SHORTCODE DEL PANEL IA
   ============================================================ */

add_shortcode('punksetter_ia', function () {

    ob_start();
?>
    <div class="ps-ia-panel">

        <div class="ps-ia-header">
            <h2>Inteligencia del Panel (Claude)</h2>
            <p class="ps-ia-subtitle">
                Analiza clientes, pedidos, marketing, correo, Odoo, Cloudflare y envíos desde un solo lugar.
            </p>
        </div>

        <div class="ps-ia-layout">

            <!-- BLOQUE IZQUIERDO -->
            <div class="ps-ia-left">
                <label for="ps-ia-input" class="ps-ia-label">
                    Escribe tu consulta para la IA:
                </label>

                <textarea id="ps-ia-input"
                    class="ps-ia-textarea"
                    placeholder="Ejemplos:
- Analiza las ventas de este mes.
- Dime qué clientes compran más.
- Revisa si hay errores en el módulo de envíos.
- Haz un resumen de los pedidos completados."></textarea>

                <button id="ps-ia-send" class="ps-ia-button">
                    Enviar a Claude
                </button>

                <div id="ps-ia-status" class="ps-ia-status" style="display:none;">
                    Procesando consulta IA...
                </div>
            </div>

            <!-- BLOQUE DERECHO -->
            <div class="ps-ia-right">
                <h3>Respuesta de la IA</h3>
                <div id="ps-ia-output" class="ps-ia-output">
                    <p class="ps-ia-placeholder">
                        La respuesta de Claude aparecerá aquí, con análisis del panel, resúmenes y sugerencias.
                    </p>
                </div>
            </div>

        </div>

    </div>
<?php
    return ob_get_clean();
});


/* ============================================================
   IA-DATA — Conexión automática con los datos del panel
   ============================================================ */

function punksetter_ia_get_data_for_prompt($prompt)
{

    $data = [];
    $prompt_lower = strtolower(
        trim(
            preg_replace('/[\x00-\x1F\x7F]/u', '', $prompt)
        )
    );


    /* ============================================================
       SIEMPRE CONSULTAR EL SITIO PRINCIPAL (ID 1)
       ============================================================ */

    $ajax_base = 'https://punksetter.com/wp-admin/admin-ajax.php';


    /* ============================================================
       CLIENTES — CORREGIDO
       ============================================================ */
    if (preg_match('/cliente|clientes/i', $prompt_lower)) {

        $response = wp_remote_get($ajax_base . '?action=ia_get_customers');
        $body = trim(wp_remote_retrieve_body($response));
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);

        $json = json_decode($body, true);
        $data['clientes'] = is_array($json) ? $json : [];
    }

    /* ============================================================
       PEDIDOS / VENTAS
       ============================================================ */
    if (preg_match('/venta|ventas|pedido|pedidos|orden|ordenes/i', $prompt_lower)) {

        if (strpos($prompt_lower, 'año') !== false) {
            $range = 'year';
        } elseif (strpos($prompt_lower, 'mes') !== false) {
            $range = 'month';
        } elseif (strpos($prompt_lower, '90') !== false) {
            $range = '90';
        } elseif (strpos($prompt_lower, '30') !== false) {
            $range = '30';
        } elseif (strpos($prompt_lower, '7') !== false) {
            $range = '7';
        } else {
            $range = 'year';
        }

        $response = wp_remote_get($ajax_base . '?action=ia_get_orders&range=' . $range);
        $body = trim(wp_remote_retrieve_body($response));
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);

        $json = json_decode($body, true);
        $data['pedidos'] = is_array($json) ? $json : [];
    }

    /* ============================================================
       ENVÍOS ZELERIS
       ============================================================ */
    if (preg_match('/envio|envíos|zeleris/i', $prompt_lower)) {

        $response = wp_remote_get($ajax_base . '?action=ia_get_shipments_zeleris');
        $body = trim(wp_remote_retrieve_body($response));
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);

        $json = json_decode($body, true);
        $data['envios'] = is_array($json) ? $json : [];
    }

    /* ============================================================
       MARKETING
       ============================================================ */
    if (preg_match('/marketing|campaña|campañas/i', $prompt_lower)) {

        $response = wp_remote_get($ajax_base . '?action=ia_get_marketing');
        $body = trim(wp_remote_retrieve_body($response));
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);

        $json = json_decode($body, true);
        $data['marketing'] = is_array($json) ? $json : [];
    }

    /* ============================================================
       ODOO
       ============================================================ */
    if (preg_match('/odoo|factura|facturación/i', $prompt_lower)) {

        $response = wp_remote_get($ajax_base . '?action=ia_get_odoo');
        $body = trim(wp_remote_retrieve_body($response));
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);

        $json = json_decode($body, true);
        $data['odoo'] = is_array($json) ? $json : [];
    }

    /* ============================================================
       CLOUDFLARE
       ============================================================ */
    if (preg_match('/cloudflare|firewall|ataque|bloqueo/i', $prompt_lower)) {

        $response = wp_remote_get($ajax_base . '?action=ia_get_cloudflare');
        $body = trim(wp_remote_retrieve_body($response));
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);

        $json = json_decode($body, true);
        $data['cloudflare'] = is_array($json) ? $json : [];
    }

    return $data;
}


/* ============================================================
   ENDPOINT AJAX — IA INTERNA (CLAUDE)
   ============================================================ */

add_action('wp_ajax_punksetter_ia_execute', 'punksetter_ia_execute');
add_action('wp_ajax_nopriv_punksetter_ia_execute', 'punksetter_ia_execute');

function punksetter_ia_execute()
{


    $prompt = isset($_POST['prompt']) ? trim($_POST['prompt']) : '';

    if (strlen($prompt) < 1) {
        wp_send_json_error('Prompt vacío.');
    }

    $panel_data = [
        'clientes' => Punksetter_IA_Data::get_customers(),
        'pedidos' => Punksetter_IA_Data::get_orders_lite(),
        'marketing' => Punksetter_IA_Data::get_marketing_stats(),
        'correo' => Punksetter_IA_Data::get_correo_summary(),
        'cloudflare' => Punksetter_IA_Data::get_cloudflare_logs(),
        'odoo' => Punksetter_IA_Data::get_odoo_lite(),
        'envios' => Punksetter_IA_Data::get_envios_global()
    ];

    error_log('=== CORREO ===');
    error_log(print_r($panel_data['correo'], true));

    $panel_json = json_encode(
        $panel_data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    $mensaje = "
Eres la IA interna del panel Punksetter.

Datos del panel:
$panel_json

Mensaje del usuario:
$prompt
";

    error_log('=== PANEL DATA ===');
    error_log(print_r($panel_data, true));

    error_log('=== PANEL JSON ===');
    error_log($panel_json);

    error_log('=== ENVIANDO A CLAUDE ===');

    try {

        $respuesta = punksetter_claude_query($mensaje);

        error_log('=== RESPUESTA CLAUDE ===');
        error_log(print_r($respuesta, true));
    } catch (Throwable $e) {

        error_log('=== ERROR FATAL CLAUDE ===');
        error_log($e->getMessage());

        wp_send_json_error(
            'FATAL: ' . $e->getMessage()
        );
    }

    if (is_wp_error($respuesta)) {

        error_log('=== WP ERROR CLAUDE ===');
        error_log($respuesta->get_error_message());

        wp_send_json_error(
            $respuesta->get_error_message()
        );
    }

    wp_send_json_success([
        'reply' => $respuesta['content']
    ]);
}


/* ============================================================
   CARGA DEL JS DEL PANEL IA (FRONTEND)
   ============================================================ */

add_action('wp_enqueue_scripts', function () {

    $js_path = PUNKSETTER_PANEL_DIR . 'assets/js/ia.js';
    $js_url  = PUNKSETTER_PANEL_URL . 'assets/js/ia.js';

    if (file_exists($js_path)) {

        wp_enqueue_script(
            'punksetter-ia-js',
            $js_url,
            ['jquery'],
            filemtime($js_path),
            true
        );

        wp_localize_script(
            'punksetter-ia-js',
            'punkIA',
            [
                // 🔥 Claude ahora SIEMPRE llama al sitio principal (ID 1)
                'ajaxurl' => 'https://punksetter.com/wp-admin/admin-ajax.php'
            ]
        );
    }
});
