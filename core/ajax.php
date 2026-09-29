<?php
if (! defined('ABSPATH')) exit;

/* ============================================================
   AJAX: PEDIDOS — panel_ver_pedido
   ============================================================ */

add_action('wp_ajax_panel_ver_pedido', 'panel_ver_pedido');
add_action('wp_ajax_nopriv_panel_ver_pedido', 'panel_ver_pedido');

function panel_ver_pedido()
{

    if (!isset($_GET['order_id'])) {
        wp_send_json_error("Falta order_id");
    }

    $order_id = intval($_GET['order_id']);

    switch_to_blog(1);

    if (!function_exists('wc_get_order')) {
        restore_current_blog();
        wp_send_json_error("WooCommerce no está activo");
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        restore_current_blog();
        wp_send_json_error("Pedido no encontrado");
    }

    $data = [
        'id'         => $order->get_id(),
        'cliente'    => $order->get_formatted_billing_full_name(),
        'estado'     => wc_get_order_status_name($order->get_status()),
        'total'      => wc_price($order->get_total()),
        'fecha'      => $order->get_date_created()->date('d/m/Y H:i'),
        'pago'       => $order->get_payment_method_title(),
        'envio'      => $order->get_shipping_method(),
        'items'      => []
    ];

    foreach ($order->get_items() as $item) {
        $data['items'][] = [
            'producto' => $item->get_name(),
            'cantidad' => $item->get_quantity(),
            'total'    => wc_price($item->get_total())
        ];
    }

    restore_current_blog();

    wp_send_json_success($data);
}


/* ============================================================
   AJAX: NOTAS — GET / GUARDAR / BORRAR / TEMPLATE
   ============================================================ */

add_action("wp_ajax_punksetter_get_notas", function () {
    wp_send_json_success(get_option("punksetter_notas", []));
});

add_action("wp_ajax_nopriv_punksetter_get_notas", function () {
    wp_send_json_success(get_option("punksetter_notas", []));
});

add_action("wp_ajax_punksetter_guardar_nota", function () {
    $texto = sanitize_text_field($_POST["texto"]);
    $notas = get_option("punksetter_notas", []);

    $notas[] = [
        "id"    => uniqid(),
        "texto" => $texto,
        "fecha" => date("d/m/Y H:i")
    ];

    update_option("punksetter_notas", $notas);
    wp_send_json_success();
});

add_action("wp_ajax_nopriv_punksetter_guardar_nota", function () {
    $texto = sanitize_text_field($_POST["texto"]);
    $notas = get_option("punksetter_notas", []);

    $notas[] = [
        "id"    => uniqid(),
        "texto" => $texto,
        "fecha" => date("d/m/Y H:i")
    ];

    update_option("punksetter_notas", $notas);
    wp_send_json_success();
});

add_action("wp_ajax_punksetter_borrar_nota", function () {
    $id = sanitize_text_field($_POST["id"]);
    $notas = get_option("punksetter_notas", []);

    $notas = array_filter($notas, fn($nota) => $nota["id"] !== $id);

    update_option("punksetter_notas", $notas);
    wp_send_json_success();
});

add_action("wp_ajax_nopriv_punksetter_borrar_nota", function () {
    $id = sanitize_text_field($_POST["id"]);
    $notas = get_option("punksetter_notas", []);

    $notas = array_filter($notas, fn($nota) => $nota["id"] !== $id);

    update_option("punksetter_notas", $notas);
    wp_send_json_success();
});

add_action("wp_ajax_punksetter_notas_template", function () {
    include PUNKSETTER_PANEL_DIR . 'templates/notas-template.php';
    exit;
});

add_action("wp_ajax_nopriv_punksetter_notas_template", function () {
    include PUNKSETTER_PANEL_DIR . 'templates/notas-template.php';
    exit;
});





/* ============================================================
   AJAX: FLOC IA — SEPARADO Y LIMPIO
   ============================================================ */

add_action("wp_ajax_floc_ask", "punksetter_floc_ask");
add_action("wp_ajax_nopriv_floc_ask", "punksetter_floc_ask");

function punksetter_floc_ask()
{
    require_once PUNKSETTER_PANEL_DIR . 'api/floc-endpoints.php';
    exit;
}
/* ============================================================
   AJAX: CORREO — VER EMAIL
   ============================================================ */

add_action('admin_post_punksetter_gmail_view', 'punksetter_gmail_view');
add_action('admin_post_nopriv_punksetter_gmail_view', 'punksetter_gmail_view');

function punksetter_gmail_view()
{

    if (!isset($_GET['id'])) {
        wp_send_json([
            'error' => true,
            'html'  => 'ID de correo no recibido.'
        ]);
    }

    $id = sanitize_text_field($_GET['id']);

    require_once PUNKSETTER_PANEL_DIR . 'api/gmail-view.php';

    $contenido = punksetter_gmail_view_api($id);

    if (!$contenido) {
        wp_send_json([
            'error' => true,
            'html'  => 'Error al cargar el correo.'
        ]);
    }

    wp_send_json([
        'error' => false,
        'html'  => $contenido
    ]);
}
/* ============================================================
   AJAX: CAMBIAR ESTADO DEL PEDIDO
   ============================================================ */

add_action('wp_ajax_panel_cambiar_estado', 'panel_cambiar_estado');
add_action('wp_ajax_nopriv_panel_cambiar_estado', 'panel_cambiar_estado');

function panel_cambiar_estado()
{

    if (!isset($_POST['order_id']) || !isset($_POST['estado'])) {
        wp_send_json_error("Faltan datos");
    }

    $order_id = intval($_POST['order_id']);
    $estado   = sanitize_text_field($_POST['estado']);

    switch_to_blog(1);

    $order = wc_get_order($order_id);

    if (!$order) {
        restore_current_blog();
        wp_send_json_error("Pedido no encontrado");
    }

    $order->update_status($estado);

    restore_current_blog();

    wp_send_json_success("Estado actualizado");
}
