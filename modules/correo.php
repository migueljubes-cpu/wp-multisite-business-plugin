<?php
/**
 * Módulo CORREO — Vista del panel
 * Shortcode: [punksetter_correo]
 */

if (!defined('ABSPATH')) exit;

// Shortcode principal
add_shortcode('punksetter_correo', function() {

    // Encolar CSS
    wp_enqueue_style(
        'punksetter-correo-css',
        plugins_url('../assets/css/correo.css', __FILE__)
    );

    // Encolar JS REAL (lector Gmail API)
    wp_enqueue_script(
        'punksetter-correo-js',
        plugins_url('../assets/js/correo-v2.js', __FILE__),
        ['jquery'],
        null,
        true
    );

    // Pasar URL AJAX al JS — CORREGIDO
    wp_localize_script('punksetter-correo-js', 'punkCorreo', [
        'api_url' => admin_url('admin-post.php?action=punksetter_gmail_api'),
        'view_url' => admin_url('admin-post.php?action=punksetter_gmail_view'),
        'auth_url' => admin_url('admin-post.php?action=punksetter_gmail_auth')
    ]);

    // Cargar template HTML
    ob_start();
    include dirname(__FILE__, 2) . '/templates/correo-template.php';
    return ob_get_clean();
});
