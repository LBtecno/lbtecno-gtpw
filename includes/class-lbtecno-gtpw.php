<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase orquestadora y cargador principal del plugin LBtecno GTPW.
 *
 * @package LBtecno_GTPW
 * @author Alejandro Leal <https://lbtecno.net>
 */
class LBTecno_GTPW {

    /**
     * Instancia Singleton.
     *
     * @var LBTecno_GTPW|null
     */
    private static $instance = null;

    /**
     * Instancia de almacenamiento.
     *
     * @var LBTecno_GTPW_Storage
     */
    public $storage;

    /**
     * Instancia del controlador de administración.
     *
     * @var LBTecno_GTPW_Admin
     */
    public $admin;

    /**
     * Instancia del gestor de URLs virtuales.
     *
     * @var LBTecno_GTPW_Virtual_Url
     */
    public $virtual_url;

    /**
     * Obtiene la instancia Singleton.
     *
     * @return LBTecno_GTPW
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor privado. Inicializa componentes del plugin.
     */
    private function __construct() {
        $this->storage     = new LBTecno_GTPW_Storage();
        $this->virtual_url = new LBTecno_GTPW_Virtual_Url( $this->storage );

        if ( is_admin() ) {
            $this->admin = new LBTecno_GTPW_Admin( $this->storage );
        }
    }
}
