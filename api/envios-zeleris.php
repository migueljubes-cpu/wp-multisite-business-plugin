<?php
if (!defined('ABSPATH')) exit;

/* ============================================================
   API REAL — ZELERIS
   Aquí se hace el login y scraping
   ============================================================ */

/*
    ⚠️ IMPORTANTE ⚠️
    PEGA AQUÍ TU USUARIO Y CONTRASEÑA DE ZELERIS
*/

define('ZELERIS_USER', '000803467');   // ← TU USUARIO
define('ZELERIS_PASS', 'Punky26');     // ← TU CONTRASEÑA


/* ============================================================
   FUNCIÓN PRINCIPAL
   ============================================================ */
function punksetter_zeleris_api_core($accion, $payload) {

    // Iniciar sesión en Zeleris
    $cookie = punksetter_zeleris_login();

    if (!$cookie) {
        return ['error' => true, 'html' => '<p>Error al iniciar sesión en Zeleris.</p>'];
    }

    switch ($accion) {

        case 'busqueda':
            return punksetter_zeleris_busqueda_api($cookie, $payload['tipo'], $payload['datos']);
            break;

        case 'detalles':
            return punksetter_zeleris_detalles_api($cookie, $payload['id']);
            break;

        default:
            return ['error' => true, 'html' => '<p>Acción no reconocida.</p>'];
    }
}


/* ============================================================
   LOGIN AUTOMÁTICO
   ============================================================ */
function punksetter_zeleris_login() {

    $login_url = "https://clientes.zeleris.com/login.aspx";

    $post_fields = http_build_query([
        'usuario' => ZELERIS_USER,
        'clave'   => ZELERIS_PASS
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $login_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '');

    $response = curl_exec($ch);
    $cookie   = curl_getinfo($ch, CURLINFO_COOKIELIST);
    curl_close($ch);

    if (!$cookie) return false;

    return $cookie;
}


/* ============================================================
   BÚSQUEDA (Rápida / Avanzada)
   ============================================================ */
function punksetter_zeleris_busqueda_api($cookie, $tipo, $datos) {

    $url = "https://clientes.zeleris.com/pvda_transporte.aspx";

    $post_fields = http_build_query([
        'tipo'  => $tipo,
        'datos' => json_encode($datos)
    ]);

    $html = punksetter_zeleris_request($url, $cookie, $post_fields);

    if (!$html) {
        return ['error' => true, 'html' => '<p>Error al consultar Zeleris.</p>'];
    }

    // Parsear tabla real
    $tabla = punksetter_zeleris_parse_tabla($html);

    return [
        'error' => false,
        'html'  => $tabla
    ];
}


/* ============================================================
   DETALLES (Historial del envío)
   ============================================================ */
function punksetter_zeleris_detalles_api($cookie, $id) {

    $url = "https://clientes.zeleris.com/pvda_detalle.aspx?id=" . urlencode($id);

    $html = punksetter_zeleris_request($url, $cookie);

    if (!$html) {
        return ['error' => true, 'html' => '<p>Error al cargar detalles.</p>'];
    }

    // Parsear historial real
    $historial = punksetter_zeleris_parse_historial($html);

    return [
        'error' => false,
        'html'  => $historial
    ];
}


/* ============================================================
   PETICIÓN CURL
   ============================================================ */
function punksetter_zeleris_request($url, $cookie, $post_fields = null) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);

    if ($post_fields) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $post_fields);
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookie));

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}


/* ============================================================
   PARSEAR TABLA DE RESULTADOS (ENVÍOS)
   ============================================================ */
function punksetter_zeleris_parse_tabla($html) {

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    // Buscar tabla principal
    $tabla = $xpath->query("//table[contains(@id,'GridView') or contains(@class,'grid')]")->item(0);

    if (!$tabla) {
        return '<p class="ps-zeleris-info">No se encontraron envíos.</p>';
    }

    $html_final = '<table class="ps-zeleris-table"><thead><tr>';

    // Encabezados
    foreach ($tabla->getElementsByTagName('th') as $th) {
        $html_final .= '<th>' . trim($th->textContent) . '</th>';
    }

    $html_final .= '</tr></thead><tbody>';

    // Filas
    foreach ($tabla->getElementsByTagName('tr') as $tr) {

        if ($tr->getElementsByTagName('th')->length > 0) continue;

        $html_final .= '<tr>';

        foreach ($tr->getElementsByTagName('td') as $td) {

            $texto = trim($td->textContent);

            // Detectar ID del envío
            if (preg_match('/\d{8,}/', $texto)) {
                $html_final .= '<td><button class="ps-zeleris-detalle" data-id="' . $texto . '">Ver detalles</button></td>';
            } else {
                $html_final .= '<td>' . $texto . '</td>';
            }
        }

        $html_final .= '</tr>';
    }

    $html_final .= '</tbody></table>';

    return $html_final;
}


/* ============================================================
   PARSEAR HISTORIAL DEL ENVÍO
   ============================================================ */
function punksetter_zeleris_parse_historial($html) {

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    // Buscar tabla de historial
    $tabla = $xpath->query("//table[contains(@id,'GridView') or contains(@class,'grid')]")->item(0);

    if (!$tabla) {
        return '<p class="ps-zeleris-info">No hay historial disponible.</p>';
    }

    $html_final = '<h3>Historial del envío</h3>';
    $html_final .= '<table class="ps-zeleris-table"><thead><tr>';

    // Encabezados
    foreach ($tabla->getElementsByTagName('th') as $th) {
        $html_final .= '<th>' . trim($th->textContent) . '</th>';
    }

    $html_final .= '</tr></thead><tbody>';

    // Filas
    foreach ($tabla->getElementsByTagName('tr') as $tr) {

        if ($tr->getElementsByTagName('th')->length > 0) continue;

        $html_final .= '<tr>';

        foreach ($tr->getElementsByTagName('td') as $td) {
            $html_final .= '<td>' . trim($td->textContent) . '</td>';
        }

        $html_final .= '</tr>';
    }

    $html_final .= '</tbody></table>';

    return $html_final;
}
