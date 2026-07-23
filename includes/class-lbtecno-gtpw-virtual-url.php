<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Componente encargado de la gestión de URLs Virtuales para cada Tienda (/tienda/slug/).
 *
 * @package LBtecno_GTPW
 * @author Alejandro Leal <https://lbtecno.net>
 */
class LBTecno_GTPW_Virtual_Url {

    /**
     * Instancia de la clase de almacenamiento.
     *
     * @var LBTecno_GTPW_Storage
     */
    private $storage;

    /**
     * Constructor.
     *
     * @param LBTecno_GTPW_Storage $storage Instancia de almacenamiento.
     */
    public function __construct( LBTecno_GTPW_Storage $storage ) {
        $this->storage = $storage;

        add_action( 'init', array( $this, 'add_rewrite_rules' ) );
        add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'render_virtual_url' ) );
    }

    /**
     * Registra las reglas de reescritura para URLs virtuales.
     */
    public function add_rewrite_rules() {
        add_rewrite_rule( '^tienda/([^/]+)/?$', 'index.php?lbtecno_tienda=$matches[1]', 'top' );
    }

    /**
     * Registra la variable de consulta personalizada para la tienda.
     *
     * @param array $vars Variables de consulta registradas.
     * @return array
     */
    public function register_query_vars( $vars ) {
        $vars[] = 'lbtecno_tienda';
        return $vars;
    }

    /**
     * Obtiene la URL Virtual para una tienda dada.
     *
     * @param string $store_slug Nombre o slug de la tienda.
     * @return string URL virtual completa.
     */
    public static function get_virtual_url( $store_slug ) {
        $clean_slug = pathinfo( $store_slug, PATHINFO_FILENAME );
        $clean_slug = sanitize_file_name( $clean_slug );

        if ( get_option( 'permalink_structure' ) ) {
            return home_url( '/tienda/' . $clean_slug . '/' );
        }

        return add_query_arg( 'lbtecno_tienda', $clean_slug, home_url( '/' ) );
    }

    /**
     * Encola los estilos y scripts necesarios para la interfaz del Kiosco público.
     *
     * @param array $store_data Estructura de datos del JSON de la tienda.
     */
    public function enqueue_kiosk_assets( $store_data ) {
        // Tailwind CSS v4 CDN
        wp_enqueue_script( 'tailwind-cdn', 'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4', array(), '4.0.0', false );

        // Google Fonts
        wp_enqueue_style(
            'lbtecno-gtpw-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@400;500;600;700&display=swap',
            array(),
            null
        );

        // Estilos del Kiosco
        wp_enqueue_style(
            'lbtecno-gtpw-kiosk-css',
            LBTECNO_GTPW_URL . 'assets/css/kiosk.css',
            array(),
            LBTECNO_GTPW_VERSION
        );

        // Módulos JavaScript
        wp_enqueue_script(
            'lbtecno-gtpw-theme',
            LBTECNO_GTPW_URL . 'assets/js/theme.js',
            array(),
            LBTECNO_GTPW_VERSION,
            true
        );

        wp_enqueue_script(
            'lbtecno-gtpw-cart',
            LBTECNO_GTPW_URL . 'assets/js/cart.js',
            array(),
            LBTECNO_GTPW_VERSION,
            true
        );

        wp_enqueue_script(
            'lbtecno-gtpw-modal',
            LBTECNO_GTPW_URL . 'assets/js/modal.js',
            array(),
            LBTECNO_GTPW_VERSION,
            true
        );

        wp_enqueue_script(
            'lbtecno-gtpw-app',
            LBTECNO_GTPW_URL . 'assets/js/app.js',
            array( 'lbtecno-gtpw-theme', 'lbtecno-gtpw-cart', 'lbtecno-gtpw-modal' ),
            LBTECNO_GTPW_VERSION,
            true
        );

        // Inyectar datos del JSON de la tienda antes de app.js
        wp_add_inline_script(
            'lbtecno-gtpw-app',
            'window.LBTECNO_STORE_DATA = ' . wp_json_encode( $store_data ) . ';',
            'before'
        );
    }

    /**
     * Intercepta la carga de plantilla y renderiza la vista pública de la tienda si coincide la URL virtual.
     */
    public function render_virtual_url() {
        $store_slug = get_query_var( 'lbtecno_tienda' );

        if ( empty( $store_slug ) ) {
            return;
        }

        $clean_slug = pathinfo( $store_slug, PATHINFO_FILENAME );
        $filename   = sanitize_file_name( $clean_slug ) . '.json';

        $content = $this->storage->get_store_content( $filename );

        if ( false === $content ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            include get_query_template( '404' );
            exit;
        }

        $store_data = json_decode( $content, true );

        if ( ! is_array( $store_data ) ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            include get_query_template( '404' );
            exit;
        }

        // Encolar assets del Kiosco
        $this->enqueue_kiosk_assets( $store_data );

        // Establecer título dinámico
        $store_title = ! empty( $store_data['catalogName'] ) ? $store_data['catalogName'] : $this->storage->get_display_name( $filename );
        add_filter( 'pre_get_document_title', function() use ( $store_title ) {
            return esc_html( $store_title ) . ' - ' . get_bloginfo( 'name' );
        }, 99 );

        status_header( 200 );

        $view_file = LBTECNO_GTPW_PATH . 'includes/views/public-kiosk.php';
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo( 'charset' ); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <?php wp_head(); ?>
            <style>
                body.lbtecno-gtpw-kiosk-page {
                    margin: 0;
                    padding: 0;
                    background-color: #09090b;
                    min-height: 100vh;
                }
            </style>
        </head>
        <body <?php body_class( 'lbtecno-gtpw-kiosk-page' ); ?>>
            <?php
            if ( file_exists( $view_file ) ) {
                include $view_file;
            }
            ?>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
        exit;
    }
}
