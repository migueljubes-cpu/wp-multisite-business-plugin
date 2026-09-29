<?php
if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| IA DATA ENDPOINTS (MULTISITE READY)
| Endpoints AJAX que devuelven datos reales del panel
| Usan directamente Punksetter_IA_Data, que ya hace switch_to_blog(1)
|--------------------------------------------------------------------------
*/

/* ============================
   PEDIDOS (con rango dinámico)
   ============================ */
add_action('wp_ajax_ia_get_orders', 'ia_get_orders');
add_action('wp_ajax_nopriv_ia_get_orders', 'ia_get_orders');

function ia_get_orders()
{

    $range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : 'year';

    switch ($range) {

        case 'month':
            $data = Punksetter_IA_Data::get_orders_month();
            break;

        case '7':
            $data = Punksetter_IA_Data::get_orders_range(7);
            break;

        case '30':
            $data = Punksetter_IA_Data::get_orders_range(30);
            break;

        case '90':
            $data = Punksetter_IA_Data::get_orders_range(90);
            break;

        default:
            $data = Punksetter_IA_Data::get_orders_year();
            break;
    }

    wp_send_json($data);
}


/* ============================
   CLIENTES REALES
   ============================ */
add_action('wp_ajax_ia_get_customers', 'ia_get_customers');
add_action('wp_ajax_nopriv_ia_get_customers', 'ia_get_customers');

function ia_get_customers()
{
    $data = Punksetter_IA_Data::get_customers();
    wp_send_json(is_array($data) ? $data : []);
}


/* ============================
   ENVÍOS ZELERIS
   ============================ */
add_action('wp_ajax_ia_get_shipments_zeleris', 'ia_get_shipments_zeleris');
add_action('wp_ajax_nopriv_ia_get_shipments_zeleris', 'ia_get_shipments_zeleris');

function ia_get_shipments_zeleris()
{
    $data = Punksetter_IA_Data::get_shipments_zeleris();
    wp_send_json(is_array($data) ? $data : []);
}


/* ============================
   MARKETING
   ============================ */
add_action('wp_ajax_ia_get_marketing', 'ia_get_marketing');
add_action('wp_ajax_nopriv_ia_get_marketing', 'ia_get_marketing');

function ia_get_marketing()
{
    $data = Punksetter_IA_Data::get_marketing_stats();
    wp_send_json(is_array($data) ? $data : []);
}


/* ============================
   ODOO
   ============================ */
add_action('wp_ajax_ia_get_odoo', 'ia_get_odoo');
add_action('wp_ajax_nopriv_ia_get_odoo', 'ia_get_odoo');

function ia_get_odoo()
{
    $data = Punksetter_IA_Data::get_odoo_data();
    wp_send_json(is_array($data) ? $data : []);
}


/* ============================
   CLOUDFLARE
   ============================ */
add_action('wp_ajax_ia_get_cloudflare', 'ia_get_cloudflare');
add_action('wp_ajax_nopriv_ia_get_cloudflare', 'ia_get_cloudflare');

function ia_get_cloudflare()
{
    $data = Punksetter_IA_Data::get_cloudflare_logs();
    wp_send_json(is_array($data) ? $data : []);
}


/* ============================
   CORREO / GMAIL — RESUMEN
   ============================ */
add_action('wp_ajax_ia_get_correo', 'ia_get_correo');
add_action('wp_ajax_nopriv_ia_get_correo', 'ia_get_correo');

function ia_get_correo()
{
    $data = Punksetter_IA_Data::get_correo_summary();
    wp_send_json(is_array($data) ? $data : []);
}


/* ============================
   ENVÍOS GLOBAL (Zeleris + otros)
   ============================ */
add_action('wp_ajax_ia_get_envios', 'ia_get_envios');
add_action('wp_ajax_nopriv_ia_get_envios', 'ia_get_envios');

function ia_get_envios()
{
    $data = Punksetter_IA_Data::get_envios_global();
    wp_send_json(is_array($data) ? $data : []);
}
