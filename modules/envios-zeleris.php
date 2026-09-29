<?php
if (! defined('ABSPATH')) exit;

/**
 * modules/envios-zeleris.php
 * ZELERIS — Conexión AJAX entre WordPress y la API (versión Plan B segura)
 *
 * Endpoints protegidos:
 * - punksetter_zeleris_busqueda   (POST) -> búsqueda rápida/avanzada
 * - punksetter_zeleris_detalles   (POST) -> detalles / historial de envío
 *
 * Requisitos:
 * - Usuario autenticado con capability 'manage_options'
 * - Nonce 'panel_ajax_nonce' enviado en cada petición
 *
 * Notas:
 * - No exponer endpoints a 'nopriv'
 * - Cache de búsquedas por 60s
 * - Rate limiting básico por usuario (transient)
 */

/* --------------------------------------------------------------------------
 * Helpers internos
 * -------------------------------------------------------------------------- */

/**
 * Comprueba permisos mínimos y nonce
 */
function punksetter_zeleris_require_admin_and_nonce()
{
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_send_json_error(['error' => 'Permiso denegado'], 403);
        exit;
    }
    $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
    if (!wp_verify_nonce($nonce, 'panel_ajax_nonce')) {
        wp_send_json_error(['error' => 'Nonce inválido'], 403);
        exit;
    }
}

/**
 * Rate limiting simple por usuario: max 10 llamadas por 60s (ajustable)
 */
function punksetter_zeleris_rate_limit_check($limit = 10, $period = 60)
{
    $user_id = get_current_user_id();
    if (!$user_id) return true; // fallback: permitir (pero endpoints requieren login)
    $key = "punksetter_zeleris_rl_{$user_id}";
    $data = get_transient($key);
    if (!is_array($data)) $data = ['count' => 0, 'ts' => time()];
    $now = time();
    if ($now - $data['ts'] > $period) {
        // reset
        $data = ['count' => 1, 'ts' => $now];
        set_transient($key, $data, $period);
        return true;
    }
    if ($data['count'] >= $limit) {
        return false;
    }
    $data['count']++;
    set_transient($key, $data, $period);
    return true;
}

/* --------------------------------------------------------------------------
 * BÚSQUEDA (rápida o avanzada)
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_punksetter_zeleris_busqueda', 'punksetter_zeleris_busqueda');
// No nopriv: protegido

function punksetter_zeleris_busqueda()
{

    punksetter_zeleris_require_admin_and_nonce();

    if (!punksetter_zeleris_rate_limit_check()) {
        wp_send_json_error(['error' => 'Demasiadas solicitudes. Intenta de nuevo más tarde.'], 429);
        exit;
    }

    // Sanitizar y obtener parámetros
    $tipo  = isset($_POST['tipo']) ? sanitize_text_field(wp_unslash($_POST['tipo'])) : '';
    $datos = isset($_POST['datos']) ? wp_unslash($_POST['datos']) : '';

    // Validaciones básicas
    if (empty($tipo) || empty($datos)) {
        wp_send_json_error(['error' => 'Parámetros inválidos'], 400);
        exit;
    }

    // Cache key por tipo+hash(datos)
    $cache_key = 'punksetter_zeleris_busqueda_' . md5($tipo . '|' . maybe_serialize($datos));
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        wp_send_json_success($cached);
        exit;
    }

    // Llamamos a la API real a través del puente
    $resultado = punksetter_zeleris_api('busqueda', [
        'tipo'  => $tipo,
        'datos' => $datos
    ]);

    if (!$resultado || (is_array($resultado) && isset($resultado['error']))) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ZELERIS BUSQUEDA ERROR: ' . print_r($resultado, true));
        }
        wp_send_json_error(['error' => true, 'html' => '<p class="ps-zeleris-info">Error al consultar Zeleris.</p>'], 500);
        exit;
    }

    // Guardar en cache por 60 segundos (ajustable)
    set_transient($cache_key, $resultado, 60);

    wp_send_json_success($resultado);
    exit;
}

/* --------------------------------------------------------------------------
 * DETALLES (historial del envío)
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_punksetter_zeleris_detalles', 'punksetter_zeleris_detalles');
// No nopriv: protegido

function punksetter_zeleris_detalles()
{

    punksetter_zeleris_require_admin_and_nonce();

    if (!punksetter_zeleris_rate_limit_check(20, 60)) {
        wp_send_json_error(['error' => 'Demasiadas solicitudes. Intenta de nuevo más tarde.'], 429);
        exit;
    }

    $id = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : '';

    if (empty($id)) {
        wp_send_json_error(['error' => 'ID inválido'], 400);
        exit;
    }

    // Cache por ID de envío (breve)
    $cache_key = 'punksetter_zeleris_detalles_' . md5($id);
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        wp_send_json_success($cached);
        exit;
    }

    // Llamamos a la API real
    $resultado = punksetter_zeleris_api('detalles', [
        'id' => $id
    ]);

    if (!$resultado || (is_array($resultado) && isset($resultado['error']))) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ZELERIS DETALLES ERROR: ' . print_r($resultado, true));
        }
        wp_send_json_error(['error' => true, 'html' => '<p class="ps-zeleris-info">Error al cargar detalles.</p>'], 500);
        exit;
    }

    // Cachear por 120s
    set_transient($cache_key, $resultado, 120);

    wp_send_json_success($resultado);
    exit;
}

/* --------------------------------------------------------------------------
 * FUNCIÓN PUENTE → API REAL
 * -------------------------------------------------------------------------- */
function punksetter_zeleris_api($accion, $payload)
{

    $api_file = PUNKSETTER_PANEL_DIR . 'api/envios-zeleris.php';

    if (!file_exists($api_file)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ZELERIS API file not found: ' . $api_file);
        }
        return ['error' => true, 'html' => '<p>Error: API no encontrada.</p>'];
    }

    // Incluir el archivo de la API (debe definir punksetter_zeleris_api_core)
    require_once $api_file;

    if (!function_exists('punksetter_zeleris_api_core')) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ZELERIS API core function missing in ' . $api_file);
        }
        return ['error' => true, 'html' => '<p>Error: API core no disponible.</p>'];
    }

    // Llamada segura al core
    try {
        $res = punksetter_zeleris_api_core($accion, $payload);
        return $res;
    } catch (Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('punksetter_zeleris_api_core exception: ' . $e->getMessage());
        }
        return ['error' => true, 'html' => '<p>Error interno al consultar Zeleris.</p>'];
    }
}
