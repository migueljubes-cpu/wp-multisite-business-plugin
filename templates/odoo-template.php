<?php if (!defined('ABSPATH')) exit; ?>

<div class="ps-odoo" data-ps-odoo>

    <div class="ps-odoo-header">
        <h2>Panel Odoo</h2>
        <div class="ps-odoo-header-actions">
            <a href="https://punksetter.odoo.com" target="_blank" class="ps-btn ps-btn--primary">
                Ir a Odoo
            </a>
            <button class="ps-btn ps-btn--secondary ps-odoo-refresh">Refrescar</button>
            <button class="ps-btn ps-btn--secondary ps-odoo-sync">Sincronizar</button>
            <span class="ps-odoo-sync-result"></span>
        </div>
    </div>

    <!-- TABS PRINCIPALES -->
    <div class="ps-odoo-main-tabs">
        <button class="ps-odoo-main-tab ps-odoo-main-tab--active" data-main="ventas">Ventas</button>
        <button class="ps-odoo-main-tab" data-main="compras">Compras</button>
        <button class="ps-odoo-main-tab" data-main="facturas">Facturas</button>
    </div>

    <!-- SUBTABS VENTAS -->
    <div class="ps-odoo-subtabs ps-odoo-subtabs--ventas ps-odoo-subtabs--active">
        <button class="ps-odoo-tab ps-odoo-tab--active" data-ps-tab="ventas_presupuestos">Presupuestos</button>
        <button class="ps-odoo-tab" data-ps-tab="ventas_facturas">Facturas</button>
        <button class="ps-odoo-tab" data-ps-tab="ventas_clientes">Clientes</button>
        <button class="ps-odoo-tab" data-ps-tab="ventas_productos">Productos</button>
    </div>

    <!-- SUBTABS COMPRAS -->
    <div class="ps-odoo-subtabs ps-odoo-subtabs--compras">
        <button class="ps-odoo-tab" data-ps-tab="compras_pedidos">Pedidos</button>
        <button class="ps-odoo-tab" data-ps-tab="facturas_proveedores">Facturas proveedores</button>
        <button class="ps-odoo-tab" data-ps-tab="compras_proveedores">Proveedores</button>
        <button class="ps-odoo-tab" data-ps-tab="compras_productos">Productos</button>
    </div>

    <!-- SUBTABS FACTURAS (vista global) -->
    <div class="ps-odoo-subtabs ps-odoo-subtabs--facturas">
        <button class="ps-odoo-tab ps-odoo-tab--active" data-ps-tab="facturas_ventas">Ventas</button>
        <button class="ps-odoo-tab" data-ps-tab="facturas_proveedores_global">Proveedores</button>
        <button class="ps-odoo-tab" data-ps-tab="facturas_gastos">Gastos</button>
    </div>

    <!-- VENTAS / PRESUPUESTOS -->
    <div id="ps-odoo-panel-ventas_presupuestos" class="ps-odoo-panel ps-odoo-panel--active">
        <h3>Presupuestos (Ventas)</h3>
        <table class="ps-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas_presupuestos as $p): ?>
                <tr>
                    <td><?= esc_html($p['id']); ?></td>
                    <td><?= esc_html($p['name']); ?></td>
                    <td><?= esc_html($p['partner_id'][1] ?? ''); ?></td>
                    <td><?= esc_html($p['amount_total']); ?></td>
                    <td><?= esc_html($p['state']); ?></td>
                    <td>
                        <button
                            class="ps-btn ps-btn--secondary ps-odoo-view-doc"
                            data-tipo="presupuesto"
                            data-id="<?= esc_attr($p['id']); ?>"
                            data-numero="<?= esc_attr($p['name']); ?>"
                            data-cliente="<?= esc_attr($p['partner_id'][1] ?? ''); ?>"
                            data-total="<?= esc_attr($p['amount_total']); ?>"
                            data-fecha="<?= esc_attr($p['date_order'] ?? ''); ?>"
                            data-vencimiento="<?= esc_attr($p['validity_date'] ?? ''); ?>"
                        >
                            Ver presupuesto
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- VENTAS / FACTURAS -->
    <div id="ps-odoo-panel-ventas_facturas" class="ps-odoo-panel">
        <h3>Facturas (Ventas)</h3>
        <table class="ps-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas_facturas as $f): ?>
                <tr>
                    <td><?= esc_html($f['id']); ?></td>
                    <td><?= esc_html($f['name']); ?></td>
                    <td><?= esc_html($f['partner_id'][1] ?? ''); ?></td>
                    <td><?= esc_html($f['invoice_date'] ?? ''); ?></td>
                    <td><?= esc_html($f['amount_total']); ?></td>
                    <td><?= esc_html($f['state']); ?></td>
                    <td>
                        <button
                            class="ps-btn ps-btn--secondary ps-odoo-view-doc"
                            data-tipo="factura_venta"
                            data-id="<?= esc_attr($f['id']); ?>"
                            data-numero="<?= esc_attr($f['name']); ?>"
                            data-cliente="<?= esc_attr($f['partner_id'][1] ?? ''); ?>"
                            data-total="<?= esc_attr($f['amount_total']); ?>"
                            data-fecha="<?= esc_attr($f['invoice_date'] ?? ''); ?>"
                            data-untaxed="<?= esc_attr($f['amount_untaxed'] ?? ''); ?>"
                            data-tax="<?= esc_attr($f['amount_tax'] ?? ''); ?>"
                            data-origen="<?= esc_attr($f['invoice_origin'] ?? ''); ?>"
                            data-notas="<?= esc_attr($f['narration'] ?? ''); ?>"
                        >
                            Ver factura
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- VENTAS / CLIENTES -->
    <div id="ps-odoo-panel-ventas_clientes" class="ps-odoo-panel">
        <h3>Clientes (Ventas)</h3>
        <table class="ps-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Ciudad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas_clientes as $c): ?>
                <tr>
                    <td><?= esc_html($c['id']); ?></td>
                    <td><?= esc_html($c['name']); ?></td>
                    <td><?= esc_html($c['email']); ?></td>
                    <td><?= esc_html($c['phone']); ?></td>
                    <td><?= esc_html($c['city'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- VENTAS / PRODUCTOS -->
    <div id="ps-odoo-panel-ventas_productos" class="ps-odoo-panel">
        <h3>Productos (Ventas)</h3>
        <table class="ps-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Código</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                <tr>
                    <td><?= esc_html($p['id']); ?></td>
                    <td><?= esc_html($p['name']); ?></td>
                    <td><?= esc_html($p['default_code']); ?></td>
                    <td><?= esc_html($p['list_price']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- COMPRAS / PEDIDOS -->
    <div id="ps-odoo-panel-compras_pedidos" class="ps-odoo-panel">
        <h3>Pedidos de compra</h3>
        <table class="ps-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Referencia</th>
                    <th>Proveedor</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compra_pedidos as $p): ?>
                <tr>
                    <td><?= esc_html($p['id']); ?></td>
                    <td><?= esc_html($p['name']); ?></td>
                    <td><?= esc_html($p['partner_id'][1] ?? ''); ?></td>
                    <td><?= esc_html($p['amount_total']); ?></td>
                    <td><?= esc_html($p['state']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- COMPRAS / FACTURAS PROVEEDORES -->
    <div id="ps-odoo-panel-facturas_proveedores" class="ps-odoo-panel">
        <h3>Facturas de proveedores</h3>
        <table class="ps-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($facturas_proveedores as $f): ?>
                <tr>
                    <td><?= esc_html($f['id']); ?></td>
                    <td><?= esc_html($f['name']); ?></td>
                    <td><?= esc_html($f['partner_id'][1] ?? ''); ?></td>
                    <td><?= esc_html($f['invoice_date'] ?? ''); ?></td>
                    <td><?= esc_html($f['amount_total']); ?></td>
                    <td><?= esc_html($f['state']); ?></td>
                    <td>
                        <button
                            class="ps-btn ps-btn--secondary ps-odoo-view-doc"
                            data-tipo="factura_proveedor"
                            data-id="<?= esc_attr($f['id']); ?>"
                            data-numero="<?= esc_attr($f['name']); ?>"
                            data-cliente="<?= esc_attr($f['partner_id'][1] ?? ''); ?>"
                            data-total="<?= esc_attr($f['amount_total']); ?>"
                            data-fecha="<?= esc_attr($f['invoice_date'] ?? ''); ?>"
                            data-untaxed="<?= esc_attr($f['amount_untaxed'] ?? ''); ?>"
                            data-tax="<?= esc_attr($f['amount_tax'] ?? ''); ?>"
                            data-origen="<?= esc_attr($f['invoice_origin'] ?? ''); ?>"
                            data-notas="<?= esc_attr($f['narration'] ?? ''); ?>"
                        >
                            Ver factura
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- COMPRAS / PROVEEDORES -->
    <div id="ps-odoo-panel-compras_proveedores" class="ps-odoo-panel">
        <h3>Proveedores</h3>
        <table class="ps-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Ciudad</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compra_proveedores as $c): ?>
                <tr>
                    <td><?= esc_html($c['id']); ?></td>
                    <td><?= esc_html($c['name']); ?></td>
                    <td><?= esc_html($c['email']); ?></td>
                    <td><?= esc_html($c['phone']); ?></td>
                    <td><?= esc_html($c['city'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- COMPRAS / PRODUCTOS -->
    <div id="ps-odoo-panel-compras_productos" class="ps-odoo-panel">
        <h3>Productos (Compras)</h3>
        <table class="ps-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Código</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                <tr>
                    <td><?= esc_html($p['id']); ?></td>
                    <td><?= esc_html($p['name']); ?></td>
                    <td><?= esc_html($p['default_code']); ?></td>
                    <td><?= esc_html($p['list_price']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- FACTURAS / VENTAS -->
    <div id="ps-odoo-panel-facturas_ventas" class="ps-odoo-panel">
        <h3>Facturas de ventas (vista global)</h3>
        <?php // reutilizamos $ventas_facturas ?>
        <table class="ps-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas_facturas as $f): ?>
                <tr>
                    <td><?= esc_html($f['id']); ?></td>
                    <td><?= esc_html($f['name']); ?></td>
                    <td><?= esc_html($f['partner_id'][1] ?? ''); ?></td>
                    <td><?= esc_html($f['invoice_date'] ?? ''); ?></td>
                    <td><?= esc_html($f['amount_total']); ?></td>
                    <td><?= esc_html($f['state']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- FACTURAS / PROVEEDORES (vista global) -->
    <div id="ps-odoo-panel-facturas_proveedores_global" class="ps-odoo-panel">
        <h3>Facturas de proveedores (vista global)</h3>
        <table class="ps-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($facturas_proveedores as $f): ?>
                <tr>
                    <td><?= esc_html($f['id']); ?></td>
                    <td><?= esc_html($f['name']); ?></td>
                    <td><?= esc_html($f['partner_id'][1] ?? ''); ?></td>
                    <td><?= esc_html($f['invoice_date'] ?? ''); ?></td>
                    <td><?= esc_html($f['amount_total']); ?></td>
                    <td><?= esc_html($f['state']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- FACTURAS / GASTOS -->
    <div id="ps-odoo-panel-facturas_gastos" class="ps-odoo-panel">
        <h3>Facturas de gastos</h3>
        <table class="ps-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($facturas_gastos as $f): ?>
                <tr>
                    <td><?= esc_html($f['id']); ?></td>
                    <td><?= esc_html($f['name']); ?></td>
                    <td><?= esc_html($f['partner_id'][1] ?? ''); ?></td>
                    <td><?= esc_html($f['invoice_date'] ?? ''); ?></td>
                    <td><?= esc_html($f['amount_total']); ?></td>
                    <td><?= esc_html($f['state']); ?></td>
                    <td>
                        <button
                            class="ps-btn ps-btn--secondary ps-odoo-view-doc"
                            data-tipo="factura_gasto"
                            data-id="<?= esc_attr($f['id']); ?>"
                            data-numero="<?= esc_attr($f['name']); ?>"
                            data-cliente="<?= esc_attr($f['partner_id'][1] ?? ''); ?>"
                            data-total="<?= esc_attr($f['amount_total']); ?>"
                            data-fecha="<?= esc_attr($f['invoice_date'] ?? ''); ?>"
                            data-untaxed="<?= esc_attr($f['amount_untaxed'] ?? ''); ?>"
                            data-tax="<?= esc_attr($f['amount_tax'] ?? ''); ?>"
                            data-origen="<?= esc_attr($f['invoice_origin'] ?? ''); ?>"
                            data-notas="<?= esc_attr($f['narration'] ?? ''); ?>"
                        >
                            Ver factura
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL DOCUMENTO -->
    <div id="ps-odoo-doc-modal" class="ps-odoo-modal">
        <div class="ps-odoo-modal-content">
            <button class="ps-odoo-modal-close">×</button>
            <h3>Detalle documento</h3>
            <div class="ps-odoo-modal-body"></div>
        </div>
    </div>

</div>
