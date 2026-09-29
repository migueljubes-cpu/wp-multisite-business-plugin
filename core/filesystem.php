<?php
if (!defined('ABSPATH')) exit;

/**
 * ============================================================
 *  FILESYSTEM — UTILIDADES IA (LECTURA / ESCRITURA)
 * ============================================================
 * Este archivo da a Claude/FLOC funciones seguras para:
 * - Leer archivos del plugin y WP
 * - Escribir archivos solo donde está permitido
 * - Listar archivos y directorios
 * Todo controlado por Punksetter_IA_Permissions.
 * ============================================================
 */

require_once dirname(__FILE__) . '/permissions.php';

class Punksetter_IA_Filesystem
{

    /**
     * Leer contenido de un archivo
     */
    public static function read_file($path)
    {

        $real = realpath($path);
        if (!$real || !Punksetter_IA_Permissions::can_read($real)) {
            return [
                'success' => false,
                'error'   => 'Permiso denegado para leer este archivo.'
            ];
        }

        if (!is_file($real) || !is_readable($real)) {
            return [
                'success' => false,
                'error'   => 'Archivo no encontrado o no legible.'
            ];
        }

        $content = file_get_contents($real);

        return [
            'success' => true,
            'path'    => $real,
            'content' => $content
        ];
    }

    /**
     * Escribir contenido en un archivo (crear o sobrescribir)
     */
    public static function write_file($path, $content)
    {

        $real = self::normalize_path($path);

        if (!$real || !Punksetter_IA_Permissions::can_write($real)) {
            return [
                'success' => false,
                'error'   => 'Permiso denegado para escribir en este archivo.'
            ];
        }

        $dir = dirname($real);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return [
                    'success' => false,
                    'error'   => 'No se pudo crear el directorio destino.'
                ];
            }
        }

        $bytes = file_put_contents($real, $content);

        if ($bytes === false) {
            return [
                'success' => false,
                'error'   => 'Error al escribir el archivo.'
            ];
        }

        return [
            'success' => true,
            'path'    => $real,
            'bytes'   => $bytes
        ];
    }

    /**
     * Listar archivos y directorios dentro de una ruta
     */
    public static function list_path($path)
    {

        $real = realpath($path);
        if (!$real || !Punksetter_IA_Permissions::can_read($real)) {
            return [
                'success' => false,
                'error'   => 'Permiso denegado para listar esta ruta.'
            ];
        }

        if (!is_dir($real)) {
            return [
                'success' => false,
                'error'   => 'La ruta no es un directorio válido.'
            ];
        }

        $items = scandir($real);
        if ($items === false) {
            return [
                'success' => false,
                'error'   => 'No se pudo leer el directorio.'
            ];
        }

        $files = [];
        $dirs  = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $full = $real . DIRECTORY_SEPARATOR . $item;

            if (is_dir($full)) {
                $dirs[] = [
                    'name' => $item,
                    'path' => $full
                ];
            } else {
                $files[] = [
                    'name' => $item,
                    'path' => $full,
                    'size' => filesize($full)
                ];
            }
        }

        return [
            'success' => true,
            'path'    => $real,
            'dirs'    => $dirs,
            'files'   => $files
        ];
    }

    /**
     * Borrar un archivo
     */
    public static function delete_file($path)
    {

        $real = self::normalize_path($path);

        if (!$real || !Punksetter_IA_Permissions::can_write($real)) {
            return [
                'success' => false,
                'error'   => 'Permiso denegado para borrar este archivo.'
            ];
        }

        if (!is_file($real)) {
            return [
                'success' => false,
                'error'   => 'El archivo no existe.'
            ];
        }

        if (!unlink($real)) {
            return [
                'success' => false,
                'error'   => 'No se pudo borrar el archivo.'
            ];
        }

        return [
            'success' => true,
            'path'    => $real
        ];
    }

    /**
     * Normalizar ruta relativa dentro de wp-content/plugins/punksetter-panel
     */
    private static function normalize_path($path)
    {

        // Si es absoluta, la usamos tal cual
        if (strpos($path, DIRECTORY_SEPARATOR) === 0 || preg_match('#^[A-Z]:#i', $path)) {
            return realpath($path) ?: $path;
        }

        // Ruta relativa al plugin punksetter-panel
        $base = WP_CONTENT_DIR . '/plugins/punksetter-panel/';
        $full = $base . ltrim($path, '/');

        return $full;
    }
}
