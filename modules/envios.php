<?php
if (! defined('ABSPATH')) exit;

/**
 * modules/envios.php
 * Módulo ENVÍOS — Pestaña principal con Zeleris y Correos
 * Versión Plan B:
 * - Shortcode [punksetter_envios]
 * - Encolado seguro de assets
 * - Endpoints AJAX protegidos (solo admin) para datos cacheados
 * - Funciones públicas ligeras para snapshot: get_panel_envios_summary(), get_shipments_zeleris()
 */

/* --------------------------------------------------------------------------
 * Shortcode principal: muestra la plantilla de envíos
 * -------------------------------------------------------------------------- */
add_shortcode('punksetter_envios', function () {

    // Permisos: solo administradores o roles con manage_options
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        return '<p class="ps-warning">No autorizado.</p>';
    }

    // Cargar template HTML (si existe)
    $tpl = PUNKSETTER_PANEL_DIR . 'templates/envios-template.php';
    ob_start();
    if (file_exists($tpl)) {
        include $tpl;
    } else {
        echo '<p class="ps-warning">Plantilla de envíos no encontrada.</p>';
    }
    return ob_get_clean();
});

/* --------------------------------------------------------------------------
 * Encolar JS y CSS del módulo ENVÍOS (solo en páginas que usan el shortcode)
 * -------------------------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {

    global $post;
    if (!isset($post) || !has_shortcode($post->post_content, 'punksetter_envios')) return;

    // JS general del módulo ENVÍOS
    $js_path = PUNKSETTER_PANEL_DIR . 'assets/js/envios.js';
    $js_url  = PUNKSETTER_PANEL_URL . 'assets/js/envios.js';
    if (file_exists($js_path)) {
        wp_enqueue_script(
            'punksetter-envios-js',
            $js_url,
            ['jquery'],
            filemtime($js_path),
            true
        );

        wp_localize_script('punksetter-envios-js', 'punkEnvios', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('panel_ajax_nonce')
        ]);
    }

    // CSS general del módulo ENVÍOS
    $css_path = PUNKSETTER_PANEL_DIR . 'assets/css/envios.css';
    $css_url  = PUNKSETTER_PANEL_URL . 'assets/css/envios.css';
    if (file_exists($css_path)) {
        wp_enqueue_style(
            'punksetter-envios-css',
            $css_url,
            [],
            filemtime($css_path)
        );
    }

    // CSS de tablas Zeleris (si existe)
    $css_tabla = PUNKSETTER_PANEL_DIR . 'assets/css/envios-tabla.css';
    $css_tabla_url = PUNKSETTER_PANEL_URL . 'assets/css/envios-tabla.css';
    if (file_exists($css_tabla)) {
        wp_enqueue_style(
            'punksetter-envios-tabla-css',
            $css_tabla_url,
            [],
            filemtime($css_tabla)
        );
    }
}, 20);

/* --------------------------------------------------------------------------
 * Endpoints AJAX (Plan B) — protegidos: solo admin puede llamarlos
 * -------------------------------------------------------------------------- */

/* IA / panel: resumen de envíos */
add_action('wp_ajax_ia_get_envios', 'ia_get_envios_handler');
// No nopriv: protegido

function ia_get_envios_handler()
{
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_send_json_error('Permiso denegado', 403);
    }
    $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
    if (!wp_verify_nonce($nonce, 'panel_ajax_nonce')) {
        wp_send_json_error('Nonce inválido', 403);
    }

    $summary = get_panel_envios_summary();
    wp_send_json_success($summary);
}

/* Zeleris: listado / incidencias */
add_action('wp_ajax_ia_get_shipments_zeleris', 'ia_get_shipments_zeleris_handler');
// No nopriv: protegido

function ia_get_shipments_zeleris_handler()
{
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_send_json_error('Permiso denegado', 403);
    }
    $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
    if (!wp_verify_nonce($nonce, 'panel_ajax_nonce')) {
        wp_send_json_error('Nonce inválido', 403);
    }

    $data = get_shipments_zeleris();
    wp_send_json_success($data);
}

/* Endpoint para acciones (ej. reintentar consulta) — requiere confirmación humana */
add_action('wp_ajax_punksetter_envios_action', 'punksetter_envios_action_handler');

function punksetter_envios_action_handler()
{
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_send_json_error('Permiso denegado', 403);
    }
    $nonce = isset($_POST['_wpnonce']) ? sanitize_text_field($_POST['_wpnonce']) : '';
    if (!wp_verify_nonce($nonce, 'panel_ajax_nonce')) {
        wp_send_json_error('Nonce inválido', 403);
    }

    $action = isset($_POST['action_name']) ? sanitize_text_field($_POST['action_name']) : '';
    $payload = isset($_POST['payload']) ? (array) $_POST['payload'] : [];

    switch ($action) {
        case 'refresh_zeleris':
            // Forzar refresco de cache
            $res = get_shipments_zeleris(true);
            wp_send_json_success(['refreshed' => true, 'count' => count($res)]);
            break;

        default:
            wp_send_json_error('Acción no implementada', 400);
    }
}

/* --------------------------------------------------------------------------
 * Funciones públicas ligeras para Plan B / snapshot
 * - get_panel_envios_summary()
 * - get_shipments_zeleris()
 * -------------------------------------------------------------------------- */

/**
 * get_panel_envios_summary
 * Devuelve resumen redactado y cacheado para el snapshot Plan B.
 * Estructura:
 * [
 *   'count' => int,
 *   'incidencias' => [...],
 *   'totales' => [...],
 *   'examples' => [ {id, estado, fecha, destino, tracking_redacted} ... ]
 * ]
 */
if (!function_exists('get_panel_envios_summary')) {
    function get_panel_envios_summary()
    {

        $cache_key = 'punksetter_panel_envios_summary';
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        $shipments = get_shipments_zeleris();

        $count = is_array($shipments) ? count($shipments) : 0;
        $incidencias = [];
        $examples_raw = array_slice(is_array($shipments) ? $shipments : [], 0, 10);
        $examples = [];

        foreach ($examples_raw as $s) {
            $tracking = isset($s['tracking']) ? redact_tracking($s['tracking']) : '';
            $examples[] = [
                'id' => $s['id'] ?? null,
                'estado' => $s['status'] ?? '',
                'fecha' => $s['date'] ?? '',
                'destino' => $s['destination'] ?? '',
                'tracking_redacted' => $tracking
            ];
            if (!empty($s['status']) && in_array(strtolower($s['status']), ['incidencia', 'error', 'pendiente'])) {
                $incidencias[] = $s;
            }
        }

        $summary = [
            'count' => $count,
            'incidencias' => array_slice($incidencias, 0, 20),
            'totales' => [
                'with_issues' => count($incidencias)
            ],
            'examples' => $examples
        ];

        // Guardar en transient en el sitio panel (ID 5)
        $current = get_current_blog_id();
        if ($current !== 5) {
            switch_to_blog(5);
            set_transient($cache_key, $summary, 5 * MINUTE_IN_SECONDS);
            restore_current_blog();
        } else {
            set_transient($cache_key, $summary, 5 * MINUTE_IN_SECONDS);
        }

        return $summary;
    }
}

/**
 * get_shipments_zeleris
 * Obtiene envíos Zeleris desde transient del panel o desde wrapper/api (solo si existe).
 * $force_refresh = true fuerza recarga desde la API/scraping.
 */
if (!function_exists('get_shipments_zeleris')) {
    function get_shipments_zeleris($force_refresh = false)
    {

        $cache_key = 'punksetter_panel_envios_zeleris';
        if (!$force_refresh) {
            $cached = get_transient($cache_key);
            if (is_array($cached)) return $cached;
        }

        $result = [];

        // Preferir datos cacheados por el panel (transient)
        $trans = get_transient('punksetter_panel_envios_index');
        if (is_array($trans) && !$force_refresh) {
            $result = $trans;
        } else {
            // Fallback: si existe wrapper de Zeleris en api/envios-zeleris.php, usarlo (no exponer tokens)
            if (function_exists('punksetter_zeleris_fetch')) {
                try {
                    $res = punksetter_zeleris_fetch(); // debe devolver array de envíos
                    if (is_array($res)) $result = $res;
                } catch (Exception $e) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('get_shipments_zeleris wrapper error: ' . $e->getMessage());
                    }
                    $result = [];
                }
            } else {
                // No hay datos: devolver array vacío
                $result = [];
            }
        }

        // Guardar en transient en sitio panel (ID 5)
        $current = get_current_blog_id();
        if ($current !== 5) {
            switch_to_blog(5);
            set_transient($cache_key, $result, 5 * MINUTE_IN_SECONDS);
            restore_current_blog();
        } else {
            set_transient($cache_key, $result, 5 * MINUTE_IN_SECONDS);
        }

        return $result;
    }
}

/* --------------------------------------------------------------------------
 * Helpers
 * -------------------------------------------------------------------------- */

/**
 * redact_tracking
 * Muestra versión truncada del tracking: últimos 4 caracteres visibles
 */
if (!function_exists('redact_tracking')) {
    function redact_tracking($tracking)
    {
        if (empty($tracking)) return '';
        $t = preg_replace('/\s+/', '', (string)$tracking);
        $len = mb_strlen($t);
        if ($len <= 6) return str_repeat('*', max(0, $len - 2)) . mb_substr($t, -2);
        return '***' . mb_substr($t, -4);
    }
}
