<?php
/*
Plugin Name: Punksetter Panel
Description: Panel de control multisite para Punksetter.
Version: 4.0
Author: Punksetter
*/

if ( ! defined('ABSPATH') ) exit;

/* --------------------------------------------------------------------------
 * Rutas principales del plugin
 * -------------------------------------------------------------------------- */
/* Definimos constantes solo si no existen (evita redefiniciones en multisite) */
if (!defined('PUNKSETTER_PANEL_DIR')) {
    define('PUNKSETTER_PANEL_DIR', plugin_dir_path(__FILE__));
}
if (!defined('PUNKSETTER_PANEL_URL')) {
    define('PUNKSETTER_PANEL_URL', plugin_dir_url(__FILE__));
}

/* --------------------------------------------------------------------------
 * Cargar núcleo del plugin (el cerebro)
 * -------------------------------------------------------------------------- */
/* core/init.php gestiona la carga segura de módulos y assets según Plan B */
require_once PUNKSETTER_PANEL_DIR . 'core/init.php';

/* --------------------------------------------------------------------------
 * NOTA IMPORTANTE (Plan B)
 * --------------------------------------------------------------------------
 * No forzamos la carga de todos los módulos aquí con glob().
 * El loader en core/init.php (plugins_loaded) se encargará de incluir
 * los módulos cuando corresponda. Esto evita cargas inesperadas en
 * contextos distintos y mantiene la arquitectura actual intacta.
 *
 * Si en el futuro activas Plan A (acceso total), puedes reactivar
 * la carga global moviendo la inclusión de módulos al loader.
 * -------------------------------------------------------------------------- */
