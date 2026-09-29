<?php
if (!defined('ABSPATH')) exit;

add_shortcode('panel_odoo', function () {
    ob_start();

    if (!isset($GLOBALS['punksetter_odoo'])) {
        echo '<p>Error: API Odoo no cargada.</p>';
        return ob_get_clean();
    }

    $odoo = $GLOBALS['punksetter_odoo'];

    /*
    ───────────────────────────────────────────────
    VENTAS → PRESUPUESTOS (sale.order)
    ───────────────────────────────────────────────
    */
    $ventas_presupuestos = $odoo->search_read(
        'sale.order',
        [],
        [
            'id',
            'name',
            'partner_id',
            'amount_total',
            'state',
            'validity_date',
            'date_order',
            'order_line'
        ],
        200
    );

    /*
    ───────────────────────────────────────────────
    VENTAS → FACTURAS (account.move → out_invoice)
    ───────────────────────────────────────────────
    */
    $ventas_facturas = $odoo->search_read(
        'account.move',
        [['move_type', '=', 'out_invoice']],
        [
            'id',
            'name',
            'partner_id',
            'invoice_date',
            'invoice_origin',
            'amount_untaxed',
            'amount_tax',
            'amount_total',
            'state',
            'invoice_line_ids',
            'payment_reference',
            'narration'
        ],
        200
    );

    /*
    ───────────────────────────────────────────────
    COMPRAS → PEDIDOS (purchase.order)
    ───────────────────────────────────────────────
    */
    $compra_pedidos = $odoo->search_read(
        'purchase.order',
        [],
        [
            'id',
            'name',
            'partner_id',
            'amount_total',
            'state',
            'date_order',
            'order_line'
        ],
        200
    );

    /*
    ───────────────────────────────────────────────
    COMPRAS → FACTURAS DE PROVEEDORES (account.move → in_invoice)
    ───────────────────────────────────────────────
    */
    $facturas_proveedores = $odoo->search_read(
        'account.move',
        [['move_type', '=', 'in_invoice']],
        [
            'id',
            'name',
            'partner_id',
            'invoice_date',
            'invoice_origin',
            'amount_untaxed',
            'amount_tax',
            'amount_total',
            'state',
            'invoice_line_ids',
            'payment_reference',
            'narration'
        ],
        200
    );

    /*
    ───────────────────────────────────────────────
    GASTOS → FACTURAS (account.move → in_receipt)
    ───────────────────────────────────────────────
    */
    $facturas_gastos = $odoo->search_read(
        'account.move',
        [['move_type', '=', 'in_receipt']],
        [
            'id',
            'name',
            'partner_id',
            'invoice_date',
            'invoice_origin',
            'amount_untaxed',
            'amount_tax',
            'amount_total',
            'state',
            'invoice_line_ids',
            'payment_reference',
            'narration'
        ],
        200
    );

    /*
    ───────────────────────────────────────────────
    CLIENTES / PROVEEDORES / PRODUCTOS
    ───────────────────────────────────────────────
    */
    $ventas_clientes = $odoo->search_read(
        'res.partner',
        [['customer_rank', '>', 0]],
        ['id', 'name', 'email', 'phone', 'street', 'city', 'zip', 'country_id', 'vat'],
        200
    );

    $compra_proveedores = $odoo->search_read(
        'res.partner',
        [['supplier_rank', '>', 0]],
        ['id', 'name', 'email', 'phone', 'street', 'city', 'zip', 'country_id', 'vat'],
        200
    );

    $productos = $odoo->search_read(
        'product.template',
        [['active', '=', true]],
        ['id', 'name', 'default_code', 'list_price'],
        200
    );

    /*
    ───────────────────────────────────────────────
    LÍNEAS DE FACTURA (account.move.line)
    ───────────────────────────────────────────────
    */
    function cargar_lineas_factura($odoo, $facturas)
    {
        $ids = [];

        foreach ($facturas as $f) {
            if (!empty($f['invoice_line_ids'])) {
                foreach ($f['invoice_line_ids'] as $lid) {
                    $ids[] = $lid;
                }
            }
        }

        if (empty($ids)) return [];

        return $odoo->read(
            'account.move.line',
            $ids,
            ['id', 'name', 'quantity', 'price_unit', 'price_subtotal', 'tax_ids']
        );
    }

    $lineas_facturas_ventas = cargar_lineas_factura($odoo, $ventas_facturas);
    $lineas_facturas_proveedores = cargar_lineas_factura($odoo, $facturas_proveedores);
    $lineas_facturas_gastos = cargar_lineas_factura($odoo, $facturas_gastos);

    /*
    ───────────────────────────────────────────────
    CARGAR TEMPLATE
    ───────────────────────────────────────────────
    */
    include PUNKSETTER_PLUGIN_DIR . 'templates/odoo-template.php';

    return ob_get_clean();
});
