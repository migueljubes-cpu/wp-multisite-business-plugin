<?php
if ( ! defined('ABSPATH') ) exit;

/*
|--------------------------------------------------------------------------
| SHORTCODE PRINCIPAL DEL PANEL DE CLIENTES
|--------------------------------------------------------------------------
*/
add_shortcode('panel_clientes', function() {

    switch_to_blog(1);

    if (!function_exists('wc_get_orders')) {
        restore_current_blog();
        return "<p>WooCommerce no está activo en el sitio principal.</p>";
    }

    global $wpdb;

    // ORDEN POR DEFECTO: ÚLTIMO CLIENTE PRIMERO (DESC)
   $clientes = $wpdb->get_results("
    SELECT DISTINCT u.ID AS user_id
    FROM {$wpdb->users} u
    WHERE u.ID IN (
        SELECT DISTINCT pm.meta_value
        FROM {$wpdb->prefix}postmeta pm
        WHERE pm.meta_key = '_customer_user'
        AND pm.meta_value > 0
    )
    OR u.ID IN (
        SELECT DISTINCT meta_value
        FROM {$wpdb->prefix}postmeta
        WHERE meta_key = '_billing_email'
    )
    ORDER BY user_id DESC
     ");


    // TABLA CON FLECHAS DE ORDENACIÓN
    $html = '<table class="panel-table">
                <thead>
                    <tr>
                        <th class="ordenable" data-col="nombre" data-dir="DESC">Nombre <span class="flechas">↑↓</span></th>
                        <th class="ordenable" data-col="email" data-dir="DESC">Email <span class="flechas">↑↓</span></th>
                        <th class="ordenable" data-col="telefono" data-dir="DESC">Teléfono <span class="flechas">↑↓</span></th>
                        <th class="ordenable" data-col="direccion" data-dir="DESC">Dirección <span class="flechas">↑↓</span></th>
                        <th class="ordenable" data-col="total" data-dir="DESC">Total gastado <span class="flechas">↑↓</span></th>
                        <th class="ordenable" data-col="num_pedidos" data-dir="DESC">Nº Pedidos <span class="flechas">↑↓</span></th>
                        <th class="ordenable" data-col="ultimo_pedido" data-dir="DESC">Último pedido <span class="flechas">↑↓</span></th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($clientes as $c) {

        $user_id = $c->user_id;
        $user = get_user_by('id', $user_id);
        if (!$user) continue;

        $nombre    = $user->display_name;
        $email     = $user->user_email;
        $telefono  = get_user_meta($user_id, 'billing_phone', true);
        $direccion = get_user_meta($user_id, 'billing_address_1', true);

        $orders = wc_get_orders([
            'customer_id' => $user_id,
            'limit'       => -1,
            'status'      => array_keys(wc_get_order_statuses())
        ]);

        $total_gastado = 0;
        $ultimo_pedido = '—';

        if (!empty($orders)) {

            foreach ($orders as $order) {
                $total_gastado += $order->get_total();
            }

            usort($orders, function($a, $b) {
                return $a->get_date_created()->getTimestamp() < $b->get_date_created()->getTimestamp();
            });

            $ultimo = $orders[0];
            $ultimo_pedido = $ultimo->get_date_created()->date('d/m/Y');
        }

        $pedidos_json = [];
        foreach ($orders as $o) {
            $pedidos_json[] = [
                'id' => $o->get_id(),
                'total' => wc_price($o->get_total()),
                'fecha' => $o->get_date_created()->date('d/m/Y'),
                'estado' => wc_get_order_status_name($o->get_status())
            ];
        }

        $pedidos_json = htmlspecialchars(json_encode($pedidos_json), ENT_QUOTES, 'UTF-8');

        $html .= "<tr>
                    <td>{$nombre}</td>
                    <td>{$email}</td>
                    <td>{$telefono}</td>
                    <td>{$direccion}</td>
                    <td>" . wc_price($total_gastado) . "</td>
                    <td>" . count($orders) . "</td>
                    <td>{$ultimo_pedido}</td>
                    <td>
                        <button class='panel-btn-small ver-direccion'
                                data-nombre='{$nombre}'
                                data-direccion='{$direccion}'
                                data-telefono='{$telefono}'
                                data-email='{$email}'>
                                Ver dirección
                        </button>

                        <button class='panel-btn-small ver-detalles'
                                data-nombre='{$nombre}'
                                data-email='{$email}'
                                data-telefono='{$telefono}'
                                data-total='" . wc_price($total_gastado) . "'
                                data-pedidos='{$pedidos_json}'>
                                Detalles
                        </button>
                    </td>
                  </tr>";
    }

    $html .= '</tbody></table>';

    restore_current_blog();

    return $html;
});


/*
|--------------------------------------------------------------------------
| AJAX: ORDENAR CLIENTES DINÁMICAMENTE (PRO)
|--------------------------------------------------------------------------
*/
add_action('wp_ajax_panel_ordenar_clientes', 'panel_ordenar_clientes');
add_action('wp_ajax_nopriv_panel_ordenar_clientes', 'panel_ordenar_clientes');

function panel_ordenar_clientes() {

    switch_to_blog(1);
    global $wpdb;

    $order_by = sanitize_text_field($_GET['order_by'] ?? 'id');
    $order_dir = sanitize_text_field($_GET['order_dir'] ?? 'DESC');

    // Mapeo seguro de columnas
    $allowed = [
        'id' => 'pm.meta_value',
        'nombre' => 'u.display_name',
        'email' => 'u.user_email',
        'telefono' => 'um.meta_value',
        'direccion' => 'um2.meta_value'
    ];

    $order_sql = $allowed[$order_by] ?? 'pm.meta_value';

    $clientes = $wpdb->get_results("
        SELECT DISTINCT pm.meta_value AS user_id
        FROM {$wpdb->prefix}postmeta pm
        WHERE pm.meta_key = '_customer_user'
        AND pm.meta_value > 0
        ORDER BY {$order_sql} {$order_dir}
    ");

    $rows = [];

    foreach ($clientes as $c) {

        $user_id = $c->user_id;
        $user = get_user_by('id', $user_id);
        if (!$user) continue;

        $nombre    = $user->display_name;
        $email     = $user->user_email;
        $telefono  = get_user_meta($user_id, 'billing_phone', true);
        $direccion = get_user_meta($user_id, 'billing_address_1', true);

        $orders = wc_get_orders([
            'customer_id' => $user_id,
            'limit'       => -1,
            'status'      => array_keys(wc_get_order_statuses())
        ]);

        $total_gastado = 0;
        $ultimo_pedido = '—';

        if (!empty($orders)) {

            foreach ($orders as $order) {
                $total_gastado += $order->get_total();
            }

            usort($orders, function($a, $b) {
                return $a->get_date_created()->getTimestamp() < $b->get_date_created()->getTimestamp();
            });

            $ultimo = $orders[0];
            $ultimo_pedido = $ultimo->get_date_created()->date('d/m/Y');
        }

        $pedidos_json = [];
        foreach ($orders as $o) {
            $pedidos_json[] = [
                'id' => $o->get_id(),
                'total' => wc_price($o->get_total()),
                'fecha' => $o->get_date_created()->date('d/m/Y'),
                'estado' => wc_get_order_status_name($o->get_status())
            ];
        }

        $rows[] = [
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'total' => wc_price($total_gastado),
            'num_pedidos' => count($orders),
            'ultimo_pedido' => $ultimo_pedido,
            'pedidos_json' => htmlspecialchars(json_encode($pedidos_json), ENT_QUOTES, 'UTF-8')
        ];
    }

    restore_current_blog();
    wp_send_json_success($rows);
}
/*
|--------------------------------------------------------------------------
| IA: OBTENER CLIENTES PARA CLAUDE
|--------------------------------------------------------------------------
*/
add_action('wp_ajax_ia_get_customers', 'ia_get_customers_handler');
add_action('wp_ajax_nopriv_ia_get_customers', 'ia_get_customers_handler');

function ia_get_customers_handler() {

    switch_to_blog(1);

    global $wpdb;

    $clientes = $wpdb->get_results("
    SELECT DISTINCT u.ID AS user_id
    FROM {$wpdb->users} u
    WHERE u.ID IN (
        SELECT DISTINCT pm.meta_value
        FROM {$wpdb->prefix}postmeta pm
        WHERE pm.meta_key = '_customer_user'
        AND pm.meta_value > 0
    )
    OR u.ID IN (
        SELECT DISTINCT meta_value
        FROM {$wpdb->prefix}postmeta
        WHERE meta_key = '_billing_email'
    )
    ORDER BY user_id DESC
     ");


    $data = [];

    foreach ($clientes as $c) {

        $user_id = $c->user_id;
        $user = get_user_by('id', $user_id);
        if (!$user) continue;

        $nombre    = $user->display_name;
        $email     = $user->user_email;
        $telefono  = get_user_meta($user_id, 'billing_phone', true);
        $direccion = get_user_meta($user_id, 'billing_address_1', true);

        $orders = wc_get_orders([
            'customer_id' => $user_id,
            'limit'       => -1,
            'status'      => array_keys(wc_get_order_statuses())
        ]);

        $total_gastado = 0;
        $ultimo_pedido = null;
        $pedidos_json  = [];

        if (!empty($orders)) {

            foreach ($orders as $order) {
                $total_gastado += $order->get_total();

                $pedidos_json[] = [
                    'id'     => $order->get_id(),
                    'total'  => $order->get_total(),
                    'fecha'  => $order->get_date_created()->date('Y-m-d'),
                    'estado' => wc_get_order_status_name($order->get_status())
                ];
            }

            usort($orders, function($a, $b) {
                return $a->get_date_created()->getTimestamp() < $b->get_date_created()->getTimestamp();
            });

            $ultimo_pedido = $orders[0]->get_date_created()->date('Y-m-d');
        }

        $data[] = [
            'id'            => $user_id,
            'nombre'        => $nombre,
            'email'         => $email,
            'telefono'      => $telefono,
            'direccion'     => $direccion,
            'total_gastado' => $total_gastado,
            'num_pedidos'   => count($orders),
            'ultimo_pedido' => $ultimo_pedido,
            'pedidos'       => $pedidos_json
        ];
    }

    restore_current_blog();

    wp_send_json($data);
}
/* --------------------------------------------------------------------------
 * FUNCIONES PÚBLICAS PARA PLAN B / SNAPSHOT
 * -------------------------------------------------------------------------- */

/**
 * Devuelve un resumen de clientes para el panel (Plan B).
 * Estructura:
 * [
 *   'count' => int,
 *   'top10' => [ {id,nombre,email,total_gastado,num_pedidos,ultimo_pedido} ... ],
 *   'examples' => [ ... ] // hasta 10 ejemplos
 * ]
 */
if (!function_exists('get_panel_clientes_summary')) {
    function get_panel_clientes_summary() {

        // Intentar leer cache en el sitio actual (panel, ID 5)
        $cache_key = 'punksetter_panel_clientes_summary';
        $cached = get_transient($cache_key);
        if (is_array($cached)) {
            return $cached;
        }

        // Si no hay cache, obtener datos desde sitio 1 (seguro y cacheado)
        $data = get_customers_lite();

        // Construir resumen
        $count = is_array($data) ? count($data) : 0;
        $top10 = array_slice($data, 0, 10);

        $summary = [
            'count' => $count,
            'top10' => $top10,
            'examples' => $top10
        ];

        // Guardar en transient en el sitio actual (panel) por 5 minutos
        set_transient($cache_key, $summary, 5 * MINUTE_IN_SECONDS);

        return $summary;
    }
}

/**
 * Obtiene lista ligera de clientes desde el sitio 1 (WooCommerce).
 * Devuelve array de clientes con campos básicos.
 * Cachea el resultado en el sitio actual (panel) para evitar llamadas repetidas.
 */
if (!function_exists('get_customers_lite')) {
    function get_customers_lite($force_refresh = false) {

        $cache_key = 'punksetter_panel_customers_lite';
        if (!$force_refresh) {
            $cached = get_transient($cache_key);
            if (is_array($cached)) {
                return $cached;
            }
        }

        // Ejecutar switch_to_blog(1) de forma segura y recuperar datos
        $result = [];
        $original_blog = get_current_blog_id();

        switch_to_blog(1);

        try {
            global $wpdb;

            // Query segura para obtener user IDs relacionados con pedidos
            $clientes = $wpdb->get_results("
                SELECT DISTINCT u.ID AS user_id
                FROM {$wpdb->users} u
                WHERE u.ID IN (
                    SELECT DISTINCT pm.meta_value
                    FROM {$wpdb->prefix}postmeta pm
                    WHERE pm.meta_key = '_customer_user'
                    AND pm.meta_value > 0
                )
                OR u.ID IN (
                    SELECT DISTINCT meta_value
                    FROM {$wpdb->prefix}postmeta
                    WHERE meta_key = '_billing_email'
                )
                ORDER BY user_id DESC
            ");

            if (!empty($clientes)) {
                foreach ($clientes as $c) {
                    $user_id = intval($c->user_id);
                    $user = get_user_by('id', $user_id);
                    if (!$user) continue;

                    $telefono  = get_user_meta($user_id, 'billing_phone', true);
                    $direccion = get_user_meta($user_id, 'billing_address_1', true);

                    // Pedidos ligeros
                    $orders = wc_get_orders([
                        'customer_id' => $user_id,
                        'limit'       => -1,
                        'status'      => array_keys(wc_get_order_statuses())
                    ]);

                    $total_gastado = 0;
                    $ultimo_pedido = null;
                    if (!empty($orders)) {
                        foreach ($orders as $order) {
                            $total_gastado += floatval($order->get_total());
                        }
                        usort($orders, function($a, $b) {
                            return $b->get_date_created()->getTimestamp() - $a->get_date_created()->getTimestamp();
                        });
                        $ultimo_pedido = $orders[0]->get_date_created()->date('Y-m-d');
                    }

                    $result[] = [
                        'id' => $user_id,
                        'nombre' => $user->display_name,
                        'email' => $user->user_email,
                        'telefono' => $telefono,
                        'direccion' => $direccion,
                        'total_gastado' => $total_gastado,
                        'num_pedidos' => is_array($orders) ? count($orders) : 0,
                        'ultimo_pedido' => $ultimo_pedido
                    ];
                }
            }

        } catch (Exception $e) {
            // En caso de error, devolver array vacío y log (si debug)
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('get_customers_lite error: ' . $e->getMessage());
            }
            $result = [];
        }

        // Restaurar contexto
        restore_current_blog();

        // Guardar en transient en el sitio actual (panel) por 5 minutos
        set_transient($cache_key, $result, 5 * MINUTE_IN_SECONDS);

        return $result;
    }
}
