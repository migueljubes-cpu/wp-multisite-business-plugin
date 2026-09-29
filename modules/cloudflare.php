<?php
if (!defined('ABSPATH')) exit;

function punksetter_cloudflare_render()
{
    ob_start();
    include PUNKSETTER_PANEL_DIR . 'templates/cloudflare-template.php';
    return ob_get_clean();
}

add_shortcode('panel_cloudflare', 'punksetter_cloudflare_render');
