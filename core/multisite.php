<?php
if (!defined('ABSPATH')) exit;

/**
 * ============================================================
 *  MULTISITE — CONTROL TOTAL PARA CLAUDE IA (VERSIÓN MEJORADA)
 * ============================================================
 * Este archivo permite a Claude/FLOC:
 * - Cambiar entre sitios del multisite
 * - Ejecutar funciones internas en cualquier sitio
 * - Llamar endpoints IA del sitio principal (ID 1)
 * - Leer WooCommerce del sitio 1
 * - Leer datos del panel del sitio 5
 * - Leer sitios FR/IT/PRO
 * - Mantener seguridad y restaurar contexto
 * ============================================================
 */

require_once dirname(__FILE__) . '/permissions.php';

class Punksetter_IA_Multisite
{

    /* ============================================================
       CAMBIAR DE SITIO
       ============================================================ */
    public static function switch($blog_id)
    {

        if (!Punksetter_IA_Permissions::can_switch_blog($blog_id)) {
            return [
                'success' => false,
                'error'   => "Claude no tiene permiso para cambiar al sitio ID {$blog_id}."
            ];
        }

        switch_to_blog($blog_id);

        return [
            'success' => true,
            'blog_id' => $blog_id
        ];
    }

    /* ============================================================
       RESTAURAR SITIO ORIGINAL
       ============================================================ */
    public static function restore()
    {
        restore_current_blog();
        return [
            'success' => true,
            'message' => 'Contexto multisite restaurado.'
        ];
    }

    /* ============================================================
       EJECUTAR FUNCIÓN EN SITIO ESPECÍFICO
       ============================================================ */
    public static function execute_in($blog_id, $function_name, $args = [])
    {

        if (!Punksetter_IA_Permissions::can_switch_blog($blog_id)) {
            return [
                'success' => false,
                'error'   => "Claude no tiene permiso para cambiar al sitio ID {$blog_id}."
            ];
        }

        if (!Punksetter_IA_Permissions::can_execute_function($function_name)) {
            return [
                'success' => false,
                'error'   => "Claude no tiene permiso para ejecutar la función {$function_name}."
            ];
        }

        switch_to_blog($blog_id);

        if (!function_exists($function_name)) {
            restore_current_blog();
            return [
                'success' => false,
                'error'   => "La función {$function_name} no existe en el sitio ID {$blog_id}."
            ];
        }

        $result = call_user_func_array($function_name, $args);

        restore_current_blog();

        return [
            'success' => true,
            'result'  => $result
        ];
    }

    /* ============================================================
       LEER DATOS DE WOOCOMMERCE (SITIO 1)
       ============================================================ */
    public static function wc($callback)
    {

        if (!Punksetter_IA_Permissions::can_switch_blog(1)) {
            return [
                'success' => false,
                'error'   => 'Claude no tiene permiso para acceder al sitio 1 (WooCommerce).'
            ];
        }

        switch_to_blog(1);

        $result = is_callable($callback) ? $callback() : null;

        restore_current_blog();

        return [
            'success' => true,
            'result'  => $result
        ];
    }

    /* ============================================================
       LEER DATOS DEL PANEL (SITIO 5)
       ============================================================ */
    public static function panel($callback)
    {

        if (!Punksetter_IA_Permissions::can_switch_blog(5)) {
            return [
                'success' => false,
                'error'   => 'Claude no tiene permiso para acceder al sitio 5 (panel).'
            ];
        }

        switch_to_blog(5);

        $result = is_callable($callback) ? $callback() : null;

        restore_current_blog();

        return [
            'success' => true,
            'result'  => $result
        ];
    }

    /* ============================================================
       LEER SITIOS FR / IT / PRO (2, 3, 4)
       ============================================================ */
    public static function locale($blog_id, $callback)
    {

        if (!Punksetter_IA_Permissions::can_switch_blog($blog_id)) {
            return [
                'success' => false,
                'error'   => "Claude no tiene permiso para acceder al sitio ID {$blog_id}."
            ];
        }

        switch_to_blog($blog_id);

        $result = is_callable($callback) ? $callback() : null;

        restore_current_blog();

        return [
            'success' => true,
            'result'  => $result
        ];
    }

    /* ============================================================
       LLAMAR ENDPOINT IA DEL SITIO PRINCIPAL (ID 1)
       ============================================================ */
    public static function call_endpoint($action, $params = [])
    {

        $url = get_site_url(1, '/wp-admin/admin-ajax.php?action=' . $action);

        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error'   => $response->get_error_message()
            ];
        }

        $body = trim(wp_remote_retrieve_body($response));
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);

        $json = json_decode($body, true);

        return [
            'success' => true,
            'result'  => $json
        ];
    }

    /* ============================================================
       ATAJO PARA LLAMAR IA (clientes, pedidos, marketing, etc.)
       ============================================================ */
    public static function ia($action, $params = [])
    {
        return self::call_endpoint($action, $params);
    }
}
