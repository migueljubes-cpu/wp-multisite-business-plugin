<?php
if (!defined('ABSPATH')) exit;

/**
 * core/init.php
 * Carga de APIs, módulos y encolado de assets para punksetter-panel
 */

/* --------------------------------------------------------------------------
 * Constantes de plugin (rutas y URLs)
 * -------------------------------------------------------------------------- */
if (!defined('PUNKSETTER_PLUGIN_DIR')) {
    define('PUNKSETTER_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)));
}
if (!defined('PUNKSETTER_PLUGIN_URL')) {
    define('PUNKSETTER_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));
}

/* --------------------------------------------------------------------------
 * CONSTANTE NECESARIA PARA EL PANEL
 * -------------------------------------------------------------------------- */
if (!defined('PUNKSETTER_PANEL_DIR')) {
    define('PUNKSETTER_PANEL_DIR', PUNKSETTER_PLUGIN_DIR);
}

/* --------------------------------------------------------------------------
 * 🔥 CARGAR MÓDULOS DEL PANEL EN TODOS LOS SITIOS (Plan A)
 * -------------------------------------------------------------------------- */
add_action('plugins_loaded', 'punksetter_load_modules_multisite', 1);

function punksetter_load_modules_multisite()
{

    $modules = [
        'modules/clientes.php',
        'modules/pedidos.php',
        'modules/marketing.php',
        'modules/correo.php',
        'modules/odoo.php',
        'modules/cloudflare.php',
        'modules/envios.php',
        'modules/envios-zeleris.php',
        'modules/ia.php',
        'modules/floc-ajax.php',
    ];

    foreach ($modules as $rel) {
        $path = PUNKSETTER_PANEL_DIR . $rel;
        if (file_exists($path)) require_once $path;
    }
}


foreach ($modules as $rel) {
    $path = PUNKSETTER_PANEL_DIR . $rel;
    if (file_exists($path)) require_once $path;
}

/* --------------------------------------------------------------------------
 * CARGAR ARCHIVOS BÁSICOS
 * -------------------------------------------------------------------------- */
if (file_exists(PUNKSETTER_PLUGIN_DIR . 'core/ajax.php')) {
    require_once PUNKSETTER_PLUGIN_DIR . 'core/ajax.php';
}
if (file_exists(PUNKSETTER_PLUGIN_DIR . 'core/helpers.php')) {
    require_once PUNKSETTER_PLUGIN_DIR . 'core/helpers.php';
}

/* --------------------------------------------------------------------------
 * 🔥 CARGAR IA (DESPUÉS DE LOS MÓDULOS)
 * -------------------------------------------------------------------------- */
require_once PUNKSETTER_PANEL_DIR . 'modules/ia-ajax.php';
require_once PUNKSETTER_PANEL_DIR . 'modules/ia-data.php';

/* --------------------------------------------------------------------------
 * APIS
 * -------------------------------------------------------------------------- */
$apis = [
    'api/odoo.php',
    'api/ga4.php',
    'api/search_console.php',
    'api/gmail.php',
    'api/claude.php',
    'api/ia-data-endpoints.php',
    'api/envios-zeleris.php',
];

foreach ($apis as $rel) {
    $path = PUNKSETTER_PANEL_DIR . $rel;
    if (file_exists($path)) require_once $path;
}

/* --------------------------------------------------------------------------
 * SHORTCODE PRINCIPAL DEL PANEL
 * -------------------------------------------------------------------------- */
add_shortcode('punksetter_panel', function () {
    ob_start();
    $tpl = PUNKSETTER_PANEL_DIR . 'templates/panel-template.php';
    if (file_exists($tpl)) include $tpl;
    else echo '<p class="ps-warning">Plantilla del panel no encontrada.</p>';
    return ob_get_clean();
});

/* --------------------------------------------------------------------------
 * ENCOLAR ASSETS SOLO EN LA PÁGINA DEL PANEL
 * -------------------------------------------------------------------------- */
add_action('wp_enqueue_scripts', function () {

    global $post;
    if (!isset($post)) return;
    if (!has_shortcode($post->post_content, 'punksetter_panel')) return;

    /* CSS principal */
    $panel_css = PUNKSETTER_PANEL_DIR . 'assets/css/panel.css';
    if (file_exists($panel_css)) {
        wp_enqueue_style(
            'punksetter-panel-css',
            PUNKSETTER_PLUGIN_URL . 'assets/css/panel.css',
            [],
            filemtime($panel_css)
        );
    }

    /* JS principal */
    $panel_js = PUNKSETTER_PANEL_DIR . 'assets/js/panel.js';
    if (file_exists($panel_js)) {
        wp_enqueue_script(
            'punksetter-panel-js',
            PUNKSETTER_PLUGIN_URL . 'assets/js/panel.js',
            ['jquery'],
            filemtime($panel_js),
            true
        );
    }

    /* Módulos JS */
    $module_scripts = [
        'clientes'   => 'assets/js/clientes.js',
        'pedidos'    => 'assets/js/pedidos.js',
        'marketing'  => 'assets/js/marketing.js',
        'correo'     => 'assets/js/correo-v2.js',
        'odoo'       => 'assets/js/odoo.js',
        'ia'         => 'assets/js/ia.js',
        'cloudflare' => 'assets/js/cloudflare.js',
        'notas'      => 'assets/js/notas.js',
        'floc'       => 'assets/js/floc.js',
    ];

    foreach ($module_scripts as $handle => $rel) {
        $path = PUNKSETTER_PANEL_DIR . $rel;
        if (file_exists($path)) {
            wp_enqueue_script(
                "punksetter-{$handle}-js",
                PUNKSETTER_PLUGIN_URL . $rel,
                ['jquery'],
                filemtime($path),
                true
            );
        }
    }

    /* AJAX para NOTAS */
    wp_localize_script(
        'punksetter-notas-js',
        'punkNotas',
        [
            'ajaxurl' => admin_url('admin-ajax.php')
        ]
    );

    /* 🔥 AJAX para IA (Claude) */
    wp_localize_script(
        'punksetter-ia-js',
        'punkIA',
        [
            'ajaxurl' => admin_url('admin-ajax.php')
        ]
    );

    /* CSS de módulos */
    $module_styles = [
        'pedidos'    => 'assets/css/pedidos.css',
        'correo'     => 'assets/css/correo.css',
        'marketing'  => 'assets/css/marketing.css',
        'odoo'       => 'assets/css/odoo.css',
        'cloudflare' => 'assets/css/cloudflare.css',
        'notas'      => 'assets/css/notas.css',
        'floc'       => 'assets/css/floc.css',
    ];

    foreach ($module_styles as $handle => $rel) {
        $path = PUNKSETTER_PANEL_DIR . $rel;
        if (file_exists($path)) {
            wp_enqueue_style(
                "punksetter-{$handle}-css",
                PUNKSETTER_PLUGIN_URL . $rel,
                [],
                filemtime($path)
            );
        }
    }

    /* Chart.js */
    if (file_exists(PUNKSETTER_PANEL_DIR . 'assets/js/marketing.js')) {
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            [],
            null,
            true
        );
    }
}, 20);

/* --------------------------------------------------------------------------
 * ENDPOINTS GMAIL
 * -------------------------------------------------------------------------- */
add_action('admin_post_punksetter_gmail_auth', 'punksetter_gmail_auth_start');
add_action('admin_post_nopriv_punksetter_gmail_auth', 'punksetter_gmail_auth_start');

function punksetter_gmail_auth_start()
{
    include PUNKSETTER_PANEL_DIR . 'api/gmail-auth-start.php';
    exit;
}

add_action('admin_post_punksetter_gmail_callback', 'punksetter_gmail_auth_callback');
add_action('admin_post_nopriv_punksetter_gmail_callback', 'punksetter_gmail_auth_callback');

function punksetter_gmail_auth_callback()
{
    include PUNKSETTER_PANEL_DIR . 'api/gmail-auth-callback.php';
    exit;
}

add_action('admin_post_punksetter_gmail_api', 'punksetter_gmail_api');
add_action('admin_post_nopriv_punksetter_gmail_api', 'punksetter_gmail_api');

function punksetter_gmail_api()
{
    include PUNKSETTER_PANEL_DIR . 'api/gmail-api.php';
    exit;
}

/* --------------------------------------------------------------------------
 * ENDPOINTS ODOO
 * -------------------------------------------------------------------------- */
add_action('admin_post_punksetter_odoo_refresh', 'punksetter_odoo_refresh_handler');
add_action('admin_post_nopriv_punksetter_odoo_refresh', 'punksetter_odoo_refresh_handler');

function punksetter_odoo_refresh_handler()
{

    if (is_user_logged_in() && current_user_can('manage_options')) {
        include PUNKSETTER_PANEL_DIR . 'api/odoo-refresh.php';
        exit;
    }

    $clientes  = get_transient('punksetter_odoo_clientes');
    $facturas  = get_transient('punksetter_odoo_facturas');
    $productos = get_transient('punksetter_odoo_productos');

    if ($clientes === false && $facturas === false && $productos === false) {
        wp_send_json_error('Permiso denegado', 403);
    }

    wp_send_json_success([
        'counts' => [
            'clientes'  => is_array($clientes) ? count($clientes) : 0,
            'facturas'  => is_array($facturas) ? count($facturas) : 0,
            'productos' => is_array($productos) ? count($productos) : 0,
        ],
    ]);
    exit;
}

add_action('admin_post_punksetter_odoo_sync', 'punksetter_odoo_sync_handler');

function punksetter_odoo_sync_handler()
{

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_send_json_error('Método no permitido', 405);
    }

    $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
    if (!wp_verify_nonce($nonce, 'punksetter_odoo_sync_nonce')) {
        wp_send_json_error('Nonce inválido', 403);
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permiso denegado', 403);
    }

    include PUNKSETTER_PANEL_DIR . 'api/odoo-sync.php';
    exit;
}

/* --------------------------------------------------------------------------
 * DEBUG ODOO
 * -------------------------------------------------------------------------- */
add_action('admin_menu', function () {
    add_submenu_page(
        'tools.php',
        'Odoo Debug',
        'Odoo Debug',
        'manage_options',
        'punksetter-odoo-debug',
        'punksetter_odoo_debug_page'
    );
});

function punksetter_odoo_debug_page()
{

    if (!current_user_can('manage_options')) wp_die('No autorizado');

    echo '<div class="wrap"><h1>Odoo Debug</h1><pre>';

    global $punksetter_odoo;

    if (!isset($punksetter_odoo)) {
        echo "API Odoo no cargada\n</pre></div>";
        return;
    }

    $auth = $punksetter_odoo->authenticate();
    echo is_wp_error($auth)
        ? "AUTH ERROR: " . esc_html($auth->get_error_message()) . "\n"
        : "AUTH OK: uid=" . esc_html($auth) . "\n";

    $res = $punksetter_odoo->search_read(
        'account.move',
        [['move_type', '=', 'out_invoice']],
        ['id', 'name', 'amount_total', 'state'],
        5
    );

    echo is_wp_error($res)
        ? "SEARCH_READ ERROR: " . esc_html($res->get_error_message()) . "\n"
        : "SEARCH_READ count: " . count($res) . "\n" . esc_html(print_r(array_slice($res, 0, 5), true));

    $keys = [
        'punksetter_odoo_clientes',
        'punksetter_odoo_facturas',
        'punksetter_odoo_productos'
    ];

    foreach ($keys as $k) {
        $val = get_transient($k);
        echo "\nTransient {$k}: ";
        if ($val === false) echo "NO EXISTE\n";
        elseif (is_array($val)) echo "array count=" . count($val) . "\n";
        else echo esc_html(var_export($val, true)) . "\n";
    }

    echo "\nCurrent blog id: " . get_current_blog_id() . "\n";

    echo '</pre></div>';
}

/* --------------------------------------------------------------------------
 * ENDPOINT REST PARA PLANTILLA DE NOTAS
 * -------------------------------------------------------------------------- */
add_action('rest_api_init', function () {
    register_rest_route('punksetter/v1', '/notas/template', [
        'methods'  => 'GET',
        'callback' => function () {

            ob_start();
            include PUNKSETTER_PANEL_DIR . 'templates/notas-template.php';
            $html = ob_get_clean();

            return new WP_REST_Response(
                $html,
                200,
                ['Content-Type' => 'text/html']
            );
        },
        'permission_callback' => '__return_true'
    ]);
});

/* --------------------------------------------------------------------------
 * FIN core/init.php
 * -------------------------------------------------------------------------- */
