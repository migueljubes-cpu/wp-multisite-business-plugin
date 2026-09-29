<?php
if (!defined('ABSPATH')) exit;

/**
 * ============================================================
 *  FLOC IA — ENDPOINT PRINCIPAL
 * ============================================================
 * Este archivo conecta:
 * - Claude IA
 * - Permisos IA
 * - Filesystem IA
 * - Multisite IA
 * - APIs externas
 * - Funciones internas del panel
 * - FLOC (interfaz JS)
 * ============================================================
 */

require_once dirname(__FILE__, 2) . '/core/permissions.php';
require_once dirname(__FILE__, 2) . '/core/filesystem.php';
require_once dirname(__FILE__, 2) . '/core/multisite.php';
require_once dirname(__FILE__) . '/claude.php';

/**
 * Endpoint AJAX
 */
add_action("wp_ajax_floc_ask", "punksetter_floc_ask");
add_action("wp_ajax_nopriv_floc_ask", "punksetter_floc_ask");

function punksetter_floc_ask()
{

    // Recibir JSON desde floc.js
    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!$data || !isset($data['action'])) {
        wp_send_json_error("Solicitud IA inválida.");
    }

    $action = sanitize_text_field($data['action']);

    switch ($action) {

        /**
         * ============================================================
         * 1. Ejecutar una función interna del panel
         * ============================================================
         */
        case 'execute_function':

            $function = sanitize_text_field($data['function']);
            $args     = isset($data['args']) ? $data['args'] : [];

            if (!Punksetter_IA_Permissions::can_execute_function($function)) {
                wp_send_json_error("Claude no tiene permiso para ejecutar {$function}.");
            }

            if (!function_exists($function)) {
                wp_send_json_error("La función {$function} no existe.");
            }

            $result = call_user_func_array($function, $args);

            wp_send_json_success([
                'type'   => 'function',
                'result' => $result
            ]);
            break;


        /**
         * ============================================================
         * 2. Leer archivo
         * ============================================================
         */
        case 'read_file':

            $path = sanitize_text_field($data['path']);
            $result = Punksetter_IA_Filesystem::read_file($path);

            if (!$result['success']) wp_send_json_error($result['error']);
            wp_send_json_success($result);
            break;


        /**
         * ============================================================
         * 3. Escribir archivo
         * ============================================================
         */
        case 'write_file':

            $path    = sanitize_text_field($data['path']);
            $content = $data['content'];

            $result = Punksetter_IA_Filesystem::write_file($path, $content);

            if (!$result['success']) wp_send_json_error($result['error']);
            wp_send_json_success($result);
            break;


        /**
         * ============================================================
         * 4. Listar archivos
         * ============================================================
         */
        case 'list_path':

            $path = sanitize_text_field($data['path']);
            $result = Punksetter_IA_Filesystem::list_path($path);

            if (!$result['success']) wp_send_json_error($result['error']);
            wp_send_json_success($result);
            break;


        /**
         * ============================================================
         * 5. Cambiar de sitio (multisite)
         * ============================================================
         */
        case 'switch_blog':

            $blog_id = intval($data['blog_id']);
            $result  = Punksetter_IA_Multisite::switch($blog_id);

            if (!$result['success']) wp_send_json_error($result['error']);
            wp_send_json_success($result);
            break;


        /**
         * ============================================================
         * 6. Ejecutar función dentro de un sitio específico
         * ============================================================
         */
        case 'execute_in_blog':

            $blog_id = intval($data['blog_id']);
            $function = sanitize_text_field($data['function']);
            $args     = isset($data['args']) ? $data['args'] : [];

            $result = Punksetter_IA_Multisite::execute_in($blog_id, $function, $args);

            if (!$result['success']) wp_send_json_error($result['error']);
            wp_send_json_success($result);
            break;


        /**
         * ============================================================
         * 7. Llamar a Claude IA (respuesta IA)
         * ============================================================
         */
        case 'ask_claude':

            $prompt = sanitize_text_field($data['prompt']);

            $response = punksetter_claude_query($prompt);

            if (is_wp_error($response)) {
                wp_send_json_error("Error IA: " . $response->get_error_message());
            }

            $text = isset($response['content']) ? $response['content'] : 'Sin respuesta IA.';

            // Filtro de seguridad (sin saludos prohibidos)
            $prohibidos = [
                'hola punksetter',
                'hola patrón',
                'hola amo',
                'hola jefe',
                'hola dueño',
                'hola miguel',
                'hola usuario',
                'hola humano'
            ];

            foreach ($prohibidos as $bad) {
                if (stripos($text, $bad) !== false) {
                    $text = str_ireplace($bad, '', $text);
                }
            }

            wp_send_json_success([
                'type' => 'claude',
                'reply' => trim($text)
            ]);
            break;


        /**
         * ============================================================
         * 8. Acción desconocida
         * ============================================================
         */
        default:
            wp_send_json_error("Acción IA desconocida: {$action}");
    }

    exit;
}
