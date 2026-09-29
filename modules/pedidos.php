<?php
if (! defined('ABSPATH')) exit;

/**
 * Shortcode: [panel_pedidos]
 * Lista de pedidos con filtros, paginación, botón "Ver" y botón "Estado"
 */
add_shortcode('panel_pedidos', function () {

    switch_to_blog(1);

    if (!function_exists('wc_get_orders')) {
        restore_current_blog();
        return "<p>WooCommerce no está activo en la tienda principal.</p>";
    }

    $estado     = isset($_GET['panel_estado']) ? sanitize_text_field($_GET['panel_estado']) : '';
    $pagina     = isset($_GET['panel_pagina']) ? max(1, intval($_GET['panel_pagina'])) : 1;
    $por_pagina = 25;

    $args = [
        'limit'   => -1,
        'orderby' => 'date',
        'order'   => 'DESC',
        'status'  => array_keys(wc_get_order_statuses()),
    ];

    $orders = wc_get_orders($args);

    if ($estado) {
        $orders = array_filter($orders, function ($order) use ($estado) {
            return $order->get_status() === $estado;
        });
    }

    $total_pedidos = count($orders);
    $total_paginas = max(1, ceil($total_pedidos / $por_pagina));

    $offset = ($pagina - 1) * $por_pagina;
    $orders = array_slice($orders, $offset, $por_pagina);

    $html = '<div class="panel-filters">
                <form method="get">
                    <label>Estado:</label>
                    <select name="panel_estado" onchange="this.form.submit()">
                        <option value="">Todos</option>';

    foreach (wc_get_order_statuses() as $key => $label) {
        $key      = str_replace('wc-', '', $key);
        $selected = ($estado === $key) ? 'selected' : '';
        $html    .= "<option value='{$key}' {$selected}>{$label}</option>";
    }

    $html .= '    </select>
                    <input type="hidden" name="panel_pagina" value="1">
                </form>
            </div>';

    $html .= '<table class="panel-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Pago</th>
                        <th>Envío</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($orders as $order) {

        if ($order instanceof WC_Order_Refund) continue;

        $order_id   = $order->get_id();
        $cliente    = $order->get_formatted_billing_full_name();
        $estado_txt = wc_get_order_status_name($order->get_status());
        $pago       = $order->get_payment_method_title();
        $envio      = $order->get_shipping_method();
        $total      = wc_price($order->get_total());
        $fecha      = $order->get_date_created()->date('d/m/Y');

        $html .= "<tr>
                    <td>#{$order_id}</td>
                    <td>{$cliente}</td>
                    <td>{$estado_txt}</td>
                    <td>{$pago}</td>
                    <td>{$envio}</td>
                    <td>{$total}</td>
                    <td>{$fecha}</td>
                    <td>
                        <button class='panel-btn-small view-order'
                                data-order='{$order_id}'>
                                Ver
                        </button>

                        <button class='panel-btn-small change-status'
                                data-order='{$order_id}'>
                                Estado
                        </button>
                    </td>
                  </tr>";
    }

    $html .= '</tbody></table>';

    restore_current_blog();

    return $html;
});
