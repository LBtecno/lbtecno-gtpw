<?php
/**
 * Plugin Name: LBtecno GTPW
 * Plugin URI: https://lbtecno.net
 * Description: Plugin para la gestión dinámica de creación, visualización y eliminación de tiendas (archivos JSON) con soporte de URLs virtuales.
 * Version: 1.0.0
 * Author: LBtecno
 * Author URI: https://lbtecno.net
 * License: GPL2
 * Text Domain: lbtecno-gtpw
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



require plugin_dir_path(__FILE__) . 'includes/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/LBtecno/lbtecno-gtpw',
    __FILE__,
    'lbtecno-gtpw'
);

// Rama principal
$updateChecker->setBranch('main');





// Definición de constantes del plugin
define( 'LBTECNO_GTPW_VERSION', '1.0.0' );
define( 'LBTECNO_GTPW_FILE', __FILE__ );
define( 'LBTECNO_GTPW_PATH', plugin_dir_path( __FILE__ ) );
define( 'LBTECNO_GTPW_URL', plugin_dir_url( __FILE__ ) );

// Carga de clases requeridas
require_once LBTECNO_GTPW_PATH . 'includes/class-lbtecno-gtpw-storage.php';
require_once LBTECNO_GTPW_PATH . 'includes/class-lbtecno-gtpw-virtual-url.php';
require_once LBTECNO_GTPW_PATH . 'includes/admin/class-lbtecno-gtpw-admin.php';
require_once LBTECNO_GTPW_PATH . 'includes/class-lbtecno-gtpw.php';

/**
 * Acciones al activar el plugin.
 */
function lbtecno_gtpw_activate() {
    add_rewrite_rule( '^tienda/([^/]+)/?$', 'index.php?lbtecno_tienda=$matches[1]', 'top' );
    flush_rewrite_rules();
}
register_activation_hook( LBTECNO_GTPW_FILE, 'lbtecno_gtpw_activate' );

/**
 * Acciones al desactivar el plugin.
 */
function lbtecno_gtpw_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( LBTECNO_GTPW_FILE, 'lbtecno_gtpw_deactivate' );

/**
 * Inicializa el plugin LBtecno GTPW.
 *
 * @return LBTecno_GTPW
 */
function lbtecno_gtpw_init() {
    return LBTecno_GTPW::get_instance();
}

add_action( 'plugins_loaded', 'lbtecno_gtpw_init' );

