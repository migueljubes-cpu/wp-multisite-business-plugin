<?php
if (!defined('ABSPATH')) exit;

/**
 * modules/marketing.php
 * Módulo MARKETING — Pestaña del panel
 * - Shortcode: [panel_marketing]
 * - Encolado seguro de assets
 * - Endpoint AJAX protegido para resumen ligero (Plan B)
 * - Funciones públicas ligeras para snapshot: get_panel_marketing_summary(), get_marketing_stats()
 */

/* --------------------------------------------------------------------------
 * Shortcode principal
 * -------------------------------------------------------------------------- */
add_shortcode('panel_marketing', function () {

    // Permisos: solo administradores o roles con manage_options
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        return '<p class="ps-warning">No autorizado.</p>';
    }

    // Encolar assets solo si la plantilla existe y el shortcode se muestra
    $css_path = PUNKSETTER_PANEL_DIR . 'assets/css/marketing.css';
    $css_url  = PUNKSETTER_PANEL_URL . 'assets/css/marketing.css';
    if (file_exists($css_path)) {
        wp_enqueue_style('punksetter-marketing-css', $css_url, [], filemtime($css_path));
    }

    $js_path = PUNKSETTER_PANEL_DIR . 'assets/js/marketing.js';
    $js_url  = PUNKSETTER_PANEL_URL . 'assets/js/marketing.js';
    if (file_exists($js_path)) {
        wp_enqueue_script('punksetter-marketing-js', $js_url, ['jquery', 'chartjs'], filemtime($js_path), true);
        wp_localize_script('punksetter-marketing-js', 'punkMarketing', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('panel_ajax_nonce')
        ]);
    }

    // Cargar template HTML (si existe)
    $tpl = PUNKSETTER_PANEL_DIR . 'templates/marketing-template.php';
    ob_start();
    if (file_exists($tpl)) {
        include $tpl;
    } else {
        echo '<p class="ps-warning">Plantilla de marketing no encontrada.</p>';
    }
    return ob_get_clean();
});

/* --------------------------------------------------------------------------
 * Endpoint AJAX protegido: ia_get_marketing
 * Devuelve resumen ligero de métricas para Plan B
 * -------------------------------------------------------------------------- */
add_action('wp_ajax_ia_get_marketing', 'ia_get_marketing_handler');
// No nopriv: protegido

function ia_get_marketing_handler()
{

    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_send_json_error('Permiso denegado', 403);
    }

    $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
    if (!wp_verify_nonce($nonce, 'panel_ajax_nonce')) {
        wp_send_json_error('Nonce inválido', 403);
    }

    $summary = get_panel_marketing_summary();
    wp_send_json_success($summary);
}

/* --------------------------------------------------------------------------
 * Funciones públicas ligeras para Plan B / snapshot
 * - get_panel_marketing_summary()
 * - get_marketing_stats()
 * -------------------------------------------------------------------------- */

/**
 * get_panel_marketing_summary
 * Devuelve resumen redactado y cacheado para el snapshot Plan B.
 * Estructura:
 * [
 *   'visitas' => int,
 *   'conversiones' => int,
 *   'conversion_rate' => float,
 *   'campanas' => [...],
 *   'examples' => [ {id, name, status, clicks, impressions, ctr} ... ]
 * ]
 */
if (!function_exists('get_panel_marketing_summary')) {
    function get_panel_marketing_summary()
    {

        $cache_key = 'punksetter_panel_marketing_summary';
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        $data = get_marketing_stats();

        $visitas = isset($data['visitas']) ? intval($data['visitas']) : 0;
        $conversiones = isset($data['conversiones']) ? intval($data['conversiones']) : 0;
        $conversion_rate = $visitas > 0 ? round(($conversiones / $visitas) * 100, 2) : 0.0;

        $campanas_raw = isset($data['campanas']) && is_array($data['campanas']) ? $data['campanas'] : [];
        $examples_raw = array_slice($campanas_raw, 0, 10);
        $examples = [];

        foreach ($examples_raw as $c) {
            $examples[] = [
                'id' => $c['id'] ?? null,
                'name' => mb_substr($c['name'] ?? '', 0, 120),
                'status' => $c['status'] ?? '',
                'clicks' => isset($c['clicks']) ? intval($c['clicks']) : 0,
                'impressions' => isset($c['impressions']) ? intval($c['impressions']) : 0,
                'ctr' => isset($c['impressions']) && $c['impressions'] > 0 ? round((($c['clicks'] ?? 0) / $c['impressions']) * 100, 2) : 0.0
            ];
        }

        $summary = [
            'visitas' => $visitas,
            'conversiones' => $conversiones,
            'conversion_rate' => $conversion_rate,
            'campanas_count' => count($campanas_raw),
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
 * get_marketing_stats
 * Obtiene métricas de marketing desde transients del panel o desde wrappers (GA4, Search Console)
 * Devuelve array con keys: visitas, conversiones, campanas (array)
 */
if (!function_exists('get_marketing_stats')) {
    function get_marketing_stats($force_refresh = false)
    {

        $cache_key = 'punksetter_panel_marketing_stats';
        if (!$force_refresh) {
            $cached = get_transient($cache_key);
            if (is_array($cached)) return $cached;
        }

        $result = [
            'visitas' => 0,
            'conversiones' => 0,
            'campanas' => []
        ];

        // Preferir datos cacheados por el panel
        $trans = get_transient('punksetter_panel_marketing_index');
        if (is_array($trans) && !$force_refresh) {
            $result = array_merge($result, $trans);
        } else {
            // Fallback: si existen wrappers para GA4 o Search Console, usarlos (no exponer credenciales)
            if (function_exists('punksetter_ga4_fetch_summary')) {
                try {
                    $ga4 = punksetter_ga4_fetch_summary(); // debe devolver ['visitas'=>int,'conversiones'=>int]
                    if (is_array($ga4)) {
                        $result['visitas'] = isset($ga4['visitas']) ? intval($ga4['visitas']) : $result['visitas'];
                        $result['conversiones'] = isset($ga4['conversiones']) ? intval($ga4['conversiones']) : $result['conversiones'];
                    }
                } catch (Exception $e) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('get_marketing_stats GA4 wrapper error: ' . $e->getMessage());
                    }
                }
            }

            if (function_exists('punksetter_marketing_campaigns_index')) {
                try {
                    $camp = punksetter_marketing_campaigns_index(); // debe devolver array de campañas
                    if (is_array($camp)) {
                        $result['campanas'] = $camp;
                    }
                } catch (Exception $e) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('get_marketing_stats campaigns wrapper error: ' . $e->getMessage());
                    }
                }
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
