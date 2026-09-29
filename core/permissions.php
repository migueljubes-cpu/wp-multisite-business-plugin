<?php
if (!defined('ABSPATH')) exit;

/**
 * Sistema de permisos IA para el panel Punksetter
 */

class Punksetter_IA_Permissions
{

    public static function allow($action)
    {

        // Lista de acciones permitidas para la IA
        $allowed = [
            'crear_cliente',
            'modificar_cliente',
            'borrar_cliente',

            'crear_pedido',
            'modificar_pedido',
            'borrar_pedido',

            'actualizar_marketing',
            'enviar_correo',

            'odoo_sync',
            'odoo_refresh',

            'crear_nota',
            'borrar_nota',

            'envios_crear',
            'envios_modificar',
            'envios_borrar',
        ];

        return in_array($action, $allowed);
    }
}
