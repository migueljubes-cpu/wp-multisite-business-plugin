<?php
if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| IA DATA MODULE — DATOS REALES PARA CLAUDE
| Versión 2026 — Compatible con Multisite Punksetter
|--------------------------------------------------------------------------
*/

class Punksetter_IA_Data
{

    /* ============================================================
       SNAPSHOT GENERAL DEL PANEL (TOTAL)
       ============================================================ */
    public static function get_panel_snapshot()
    {

        return [
            'clientes'   => self::get_customers(),
            'pedidos'    => self::get_orders_lite(),
            'inventario' => self::get_multisite_inventory(),
            'marketing'  => self::get_marketing_stats(),
            'correo'     => self::get_correo_summary(),
            'cloudflare' => self::get_cloudflare_logs(),
            'odoo'       => self::get_odoo_lite(),
            'envios'     => self::get_envios_global()
        ];
    }

    /* ============================================================
       CLIENTES — IGUAL QUE EL PANEL
       ============================================================ */
    public static function get_customers()
    {

        // Siempre leer del sitio principal (ID 1)
        switch_to_blog(1);

        // Si ya tienes un módulo clientes.php con una función propia, la usamos
        if (function_exists('punksetter_get_clientes_panel')) {
            $data = punksetter_get_clientes_panel();
            restore_current_blog();
            return is_array($data) ? $data : [];
        }

        // Si no existe, usamos la versión LITE basada en usuarios
        global $wpdb;

        $user_ids = $wpdb->get_col("
            SELECT DISTINCT pm.meta_value
            FROM {$wpdb->prefix}postmeta pm
            WHERE pm.meta_key = '_customer_user'
            AND pm.meta_value > 0
            ORDER BY pm.meta_value DESC
        ");

        $clientes = [];

        foreach ($user_ids as $user_id) {

            $user = get_user_by('id', $user_id);
            if (!$user) continue;

            $nombre    = $user->display_name;
            $email     = $user->user_email;
            $telefono  = get_user_meta($user_id, 'billing_phone', true);
            $direccion = get_user_meta($user_id, 'billing_address_1', true);

            $orders = function_exists('wc_get_orders') ? wc_get_orders([
                'customer_id' => $user_id,
                'limit'       => -1,
                'status'      => array_keys(wc_get_order_statuses())
            ]) : [];

            $total_gastado = 0;
            $ultimo_ts     = null;

            foreach ($orders as $order) {
                $total_gastado += $order->get_total();
                $ts = $order->get_date_created()->getTimestamp();
                if (!$ultimo_ts || $ts > $ultimo_ts) {
                    $ultimo_ts = $ts;
                }
            }

            $clientes[] = [
                'id'            => (int) $user_id,
                'name'          => $nombre,
                'email'         => $email,
                'telefono'      => $telefono,
                'direccion'     => $direccion,
                'total_gastado' => $total_gastado,
                'num_pedidos'   => count($orders),
                'ultimo_pedido' => $ultimo_ts ? date('Y-m-d H:i:s', $ultimo_ts) : null
            ];
        }

        restore_current_blog();
        return $clientes;
    }

    /* ============================================================
       PEDIDOS — LITE (últimos 90 días)
       ============================================================ */
    public static function get_orders_lite()
    {

        switch_to_blog(1);

        if (!function_exists('wc_get_orders')) {
            restore_current_blog();
            return [];
        }

        $date = date('Y-m-d', strtotime('-90 days'));

        $orders = wc_get_orders([
            'limit'        => -1,
            'status'       => array_keys(wc_get_order_statuses()),
            'date_created' => '>' . $date
        ]);

        $data = [];

        foreach ($orders as $order) {

            if ($order instanceof WC_Order_Refund) continue;

            $data[] = [
                'id'       => $order->get_id(),
                'date'     => $order->get_date_created()->date('Y-m-d H:i:s'),
                'total'    => $order->get_total(),
                'status'   => $order->get_status(),
                'cliente'  => $order->get_formatted_billing_full_name(),
                'email'    => $order->get_billing_email(),
                'pago'     => $order->get_payment_method_title(),
                'envio'    => $order->get_shipping_method()
            ];
        }

        restore_current_blog();
        return $data;
    }

    /* ============================================================
       INVENTARIO MULTISITE
       ============================================================ */
    public static function get_multisite_inventory()
    {

        switch_to_blog(1);

        if (!function_exists('punksetter_multisite_inventory')) {
            restore_current_blog();
            return [];
        }

        $data = punksetter_multisite_inventory();

        restore_current_blog();
        return $data;
    }

    /* ============================================================
       MARKETING
       ============================================================ */

    public static function get_marketing_stats()
    {

        switch_to_blog(1);

        if (!function_exists('punksetter_ga4_fetch_summary')) {
            restore_current_blog();
            return [];
        }

        $data = punksetter_ga4_fetch_summary();

        restore_current_blog();

        return is_array($data) ? $data : [];
    }


    /* ============================================================
       CORREO / GMAIL
       ============================================================ */


    public static function get_correo_summary()
    {

        require_once PUNKSETTER_PANEL_DIR . 'api/gmail-core.php';

        return punksetter_gmail_api_core();
    }

    /* ============================================================
       CLOUDFLARE
       ============================================================ */
    public static function get_cloudflare_logs()
    {

        switch_to_blog(1);

        if (!function_exists('punksetter_cloudflare_get_logs')) {
            restore_current_blog();
            return [];
        }

        $data = punksetter_cloudflare_get_logs();

        restore_current_blog();
        return $data;
    }

    /* ============================================================
       ODOO — LITE
       ============================================================ */


    public static function get_odoo_lite()
    {

        switch_to_blog(1);

        if (!isset($GLOBALS['punksetter_odoo'])) {
            restore_current_blog();
            return [];
        }

        $odoo = $GLOBALS['punksetter_odoo'];

        $ventas = $odoo->search_read(
            'sale.order',
            [],
            ['id', 'name', 'amount_total'],
            5
        );

        restore_current_blog();

        return [
            'ventas' => $ventas
        ];
    }

    /* ============================================================
       ENVÍOS — GLOBAL
       ============================================================ */
    public static function get_envios_global()
    {

        $zeleris = self::get_shipments_zeleris();

        return [
            'zeleris' => $zeleris
        ];
    }

    public static function get_shipments_zeleris()
    {

        switch_to_blog(1);

        $api_file = PUNKSETTER_PANEL_DIR . 'api/envios-zeleris.php';

        if (!file_exists($api_file)) {
            restore_current_blog();
            return [];
        }

        require_once $api_file;

        if (!function_exists('punksetter_zeleris_api_core')) {
            restore_current_blog();
            return [];
        }

        $data = punksetter_zeleris_api_core('resumen', []);

        restore_current_blog();
        return $data;
    }
}
