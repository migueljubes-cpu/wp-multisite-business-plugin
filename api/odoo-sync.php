<?php
if (! defined('ABSPATH')) exit;

/**
 * api/odoo-sync.php
 * Sincronización completa desde Odoo hacia WordPress (transients)
 */

global $punksetter_odoo;

if (! isset($punksetter_odoo)) {
    wp_send_json_error('Odoo API no cargada', 500);
}

/* -----------------------------------------------------------
 * 1. Sincronizar CLIENTES (Odoo 19 usa customer_rank)
 * ----------------------------------------------------------- */

$clientes = $punksetter_odoo->search_read(
    'res.partner',
    [['customer_rank', '>', 0]],   // CAMBIO IMPORTANTE
    ['id', 'name', 'email', 'phone', 'customer_rank'],
    500
);

if (is_wp_error($clientes)) {
    wp_send_json_error('Error clientes: ' . $clientes->get_error_message(), 500);
}

set_transient('punksetter_odoo_clientes', $clientes, 60 * 60 * 24); // 24h


/* -----------------------------------------------------------
 * 2. Sincronizar FACTURAS
 * ----------------------------------------------------------- */

$facturas = $punksetter_odoo->search_read(
    'account.move',
    [['move_type', '=', 'out_invoice']],
    ['id', 'name', 'amount_total', 'state', 'invoice_date'],
    500
);

if (is_wp_error($facturas)) {
    wp_send_json_error('Error facturas: ' . $facturas->get_error_message(), 500);
}

set_transient('punksetter_odoo_facturas', $facturas, 60 * 60 * 24);


/* -----------------------------------------------------------
 * 3. Sincronizar PRODUCTOS
 * ----------------------------------------------------------- */

$productos = $punksetter_odoo->search_read(
    'product.product',
    [],
    ['id', 'name', 'list_price', 'default_code'],
    500
);

if (is_wp_error($productos)) {
    wp_send_json_error('Error productos: ' . $productos->get_error_message(), 500);
}

set_transient('punksetter_odoo_productos', $productos, 60 * 60 * 24);


/* -----------------------------------------------------------
 * RESPUESTA FINAL
 * ----------------------------------------------------------- */

wp_send_json_success([
    'clientes'  => count($clientes),
    'facturas'  => count($facturas),
    'productos' => count($productos),
    'msg'       => 'Sincronización completada correctamente'
]);

exit;
