<?php
if (!defined('ABSPATH')) exit;

// Aseguramos que la clase IA_DATA está cargada
require_once PUNKSETTER_PANEL_DIR . 'modules/ia-data.php';

/*
|--------------------------------------------------------------------------
| IA AJAX ENDPOINTS — Datos para FLOC / Claude
|--------------------------------------------------------------------------
*/

// CLIENTES
add_action('wp_ajax_ia_get_customers', 'punksetter_ia_get_customers');
add_action('wp_ajax_nopriv_ia_get_customers', 'punksetter_ia_get_customers');

function punksetter_ia_get_customers()
{
    ia_get_customers_handler();
    exit;
}


// PEDIDOS
add_action('wp_ajax_ia_get_orders', 'punksetter_ia_get_orders');
add_action('wp_ajax_nopriv_ia_get_orders', 'punksetter_ia_get_orders');

function punksetter_ia_get_orders()
{

    // Como solo tienes get_orders_lite(), ignoramos range y devolvemos últimos 90 días
    $data = Punksetter_IA_Data::get_orders_lite();
    wp_send_json($data);
}

// ENVÍOS ZELERIS
add_action('wp_ajax_ia_get_shipments_zeleris', 'punksetter_ia_get_shipments_zeleris');
add_action('wp_ajax_nopriv_ia_get_shipments_zeleris', 'punksetter_ia_get_shipments_zeleris');

function punksetter_ia_get_shipments_zeleris()
{
    $data = Punksetter_IA_Data::get_shipments_zeleris();
    wp_send_json($data);
}

// MARKETING
add_action('wp_ajax_ia_get_marketing', 'punksetter_ia_get_marketing');
add_action('wp_ajax_nopriv_ia_get_marketing', 'punksetter_ia_get_marketing');

function punksetter_ia_get_marketing()
{
    $data = Punksetter_IA_Data::get_marketing_stats();
    wp_send_json($data);
}

// ODOO
add_action('wp_ajax_ia_get_odoo', 'punksetter_ia_get_odoo');
add_action('wp_ajax_nopriv_ia_get_odoo', 'punksetter_ia_get_odoo');

function punksetter_ia_get_odoo()
{
    // USAMOS get_odoo_lite(), que es lo que tienes definido
    $data = Punksetter_IA_Data::get_odoo_lite();
    wp_send_json($data);
}

// CLOUDFLARE
add_action('wp_ajax_ia_get_cloudflare', 'punksetter_ia_get_cloudflare');
add_action('wp_ajax_nopriv_ia_get_cloudflare', 'punksetter_ia_get_cloudflare');

function punksetter_ia_get_cloudflare()
{
    $data = Punksetter_IA_Data::get_cloudflare_logs();
    wp_send_json($data);
}

// ACCIONES IA DEL PANEL
add_action('wp_ajax_punksetter_ia_action', 'punksetter_ia_action_handler');

function punksetter_ia_action_handler()
{

    if (!current_user_can('manage_options')) {
        wp_send_json_error('No autorizado', 403);
    }

    $action  = isset($_POST['action_name']) ? sanitize_text_field($_POST['action_name']) : '';
    $payload = isset($_POST['payload']) ? $_POST['payload'] : [];

    // Verificar permisos IA (asegúrate de tener esta clase cargada)
    if (class_exists('Punksetter_IA_Permissions')) {
        if (!Punksetter_IA_Permissions::allow($action)) {
            wp_send_json_error('Acción IA no permitida', 403);
        }
    }

    switch ($action) {

        case 'crear_cliente':
            $nombre = isset($payload['nombre']) ? sanitize_text_field($payload['nombre']) : 'Cliente IA';

            $id = wp_insert_post([
                'post_type'   => 'customer',
                'post_title'  => $nombre,
                'post_status' => 'publish'
            ]);

            wp_send_json_success(['cliente_id' => $id]);
            break;

        case 'odoo_sync':
            include PUNKSETTER_PANEL_DIR . 'api/odoo-sync.php';
            wp_send_json_success(['sync' => 'ok']);
            break;

        default:
            wp_send_json_error('Acción IA no implementada');
    }
}
