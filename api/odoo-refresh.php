<?php
// api/odoo-refresh.php
if (! defined('ABSPATH')) exit;

header('Content-Type: application/json; charset=utf-8');

if (! function_exists('current_user_can') || ! current_user_can('manage_options')) {
    echo wp_json_encode(array('error' => 'Permiso denegado'));
    exit;
}

global $punksetter_odoo;
if (! isset($punksetter_odoo) || ! is_object($punksetter_odoo)) {
    echo wp_json_encode(array('error' => 'API Odoo no disponible'));
    exit;
}

// Ejemplo: refrescar clientes y pedidos y guardar en transient (para evitar llamadas repetidas)
try {
    $clientes = $punksetter_odoo->search_read('res.partner', array(array('customer_rank', '>', 0)), array('id', 'name', 'email', 'phone'), 500);
    $pedidos  = $punksetter_odoo->search_read('sale.order', array(), array('id', 'name', 'partner_id', 'amount_total', 'state'), 500);

    // Guardar en transient 5 minutos
    set_transient('punksetter_odoo_clientes', $clientes, 5 * MINUTE_IN_SECONDS);
    set_transient('punksetter_odoo_pedidos', $pedidos, 5 * MINUTE_IN_SECONDS);

    echo wp_json_encode(array('success' => true, 'message' => 'Datos refrescados'));
    exit;
} catch (Exception $e) {
    echo wp_json_encode(array('error' => $e->getMessage()));
    exit;
}
