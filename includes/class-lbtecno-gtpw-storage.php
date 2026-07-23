<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Clase para el manejo de almacenamiento y operaciones del sistema de archivos JSON de Tiendas.
 *
 * @package LBtecno_GTPW
 * @author Alejandro Leal <https://lbtecno.net>
 */
class LBTecno_GTPW_Storage {

    /**
     * Directorio absoluto de almacenamiento.
     *
     * @var string
     */
    private $json_dir = '';

    /**
     * Constructor.
     */
    public function __construct() {
        $upload_dir     = wp_upload_dir();
        $this->json_dir = trailingslashit( $upload_dir['basedir'] ) . 'lbtecno-gtpw/';
        $this->ensure_directory_exists();
    }

    /**
     * Garantiza la existencia del directorio e index.php de seguridad.
     */
    private function ensure_directory_exists() {
        if ( ! file_exists( $this->json_dir ) ) {
            wp_mkdir_p( $this->json_dir );

            $index_file = $this->json_dir . 'index.php';
            if ( ! file_exists( $index_file ) ) {
                file_put_contents( $index_file, "<?php\n// Silence is golden.\n" );
            }
        }
    }

    /**
     * Retorna la estructura de campos por defecto para cualquier tienda, incluyendo categorías y productos.
     *
     * @return array
     */
    public function get_default_store_data() {
        return array(
            'catalogName' => '',
            'subtitle'    => '',
            'whatsapp'    => '',
            'homeUrl'     => '',
            'logoImage'   => '',
            'bannerImage' => '',
            'categories'  => array(
                array(
                    'id'   => '1',
                    'name' => 'Todo',
                    'icon' => '',
                ),
            ),
            'products'    => array(),
        );
    }

    /**
     * Obtiene la ruta del directorio de almacenamiento.
     *
     * @return string
     */
    public function get_json_dir() {
        return $this->json_dir;
    }

    /**
     * Obtiene todos los archivos JSON almacenados.
     *
     * @return array
     */
    public function get_all_stores() {
        $files = glob( $this->json_dir . '*.json' );
        return ( false !== $files ) ? $files : array();
    }

    /**
     * Sanitiza el nombre ingresado por el usuario:
     * - Remueve caracteres especiales (solo letras, números y espacios).
     * - Sustituye espacios por guiones medios '-'.
     *
     * @param string $raw_name Nombre recibido del formulario.
     * @return string Slug limpio con extensión .json
     */
    public function sanitize_store_name( $raw_name ) {
        $clean = preg_replace( '/[^a-zA-Z0-9\s\-]/', '', $raw_name );
        $slug  = trim( preg_replace( '/\s+/', '-', $clean ) );
        $slug  = strtolower( preg_replace( '/-+/', '-', $slug ) );

        if ( empty( $slug ) ) {
            return '';
        }

        return $slug . '.json';
    }

    /**
     * Convierte el slug de la tienda a un nombre amigable para visualización.
     * (remueve extensión .json y sustituye guiones por espacios).
     *
     * @param string $filename Nombre del archivo (ej. tienda-san-jose.json).
     * @return string Nombre formateado (ej. Tienda San Jose).
     */
    public function get_display_name( $filename ) {
        $clean_slug = pathinfo( $filename, PATHINFO_FILENAME );
        return str_replace( array( '-', '_' ), ' ', $clean_slug );
    }

    /**
     * Valida si un archivo está dentro del directorio seguro de forma estricta (Anti Directory Traversal).
     *
     * @param string $filename Nombre del archivo.
     * @return string|false Ruta absoluta verificada o false si no es válida.
     */
    public function validate_filepath( $filename ) {
        $filepath  = $this->json_dir . basename( $filename );
        $real_dir  = realpath( $this->json_dir );
        $real_file = realpath( $filepath );

        if ( ! file_exists( $filepath ) || ! $real_file || 0 !== strpos( $real_file, $real_dir ) ) {
            return false;
        }

        return $real_file;
    }

    /**
     * Crea un nuevo archivo de tienda JSON inicializado con la estructura completa de campos, categorías y productos.
     *
     * @param string $sanitized_name Nombre sanitizado del archivo (.json).
     * @return true|WP_Error
     */
    public function create_store( $sanitized_name ) {
        $filepath = $this->json_dir . $sanitized_name;

        if ( file_exists( $filepath ) ) {
            $display_title = $this->get_display_name( $sanitized_name );
            return new WP_Error(
                'store_exists',
                sprintf( __( 'La tienda "%s" ya existe. No se permiten nombres duplicados.', 'lbtecno-gtpw' ), esc_html( $display_title ) )
            );
        }

        // Estructura de datos por defecto con categoría 'Todo' y array de productos
        $default_data    = $this->get_default_store_data();
        $initial_content = json_encode( $default_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        $created         = file_put_contents( $filepath, $initial_content );

        if ( false === $created ) {
            return new WP_Error( 'store_create_failed', __( 'Error al intentar crear el archivo de la tienda en el servidor.', 'lbtecno-gtpw' ) );
        }

        /**
         * Hook propio al crear una tienda.
         */
        do_action( 'lbtecno_gtpw_after_create_file', $sanitized_name, $filepath );

        return true;
    }

    /**
     * Elimina un archivo de tienda de forma segura.
     *
     * @param string $filename Nombre del archivo a eliminar.
     * @return true|WP_Error
     */
    public function delete_store( $filename ) {
        $validated_path = $this->validate_filepath( $filename );

        if ( ! $validated_path ) {
            return new WP_Error( 'invalid_store', __( 'La tienda especificada no existe o la ruta no es válida.', 'lbtecno-gtpw' ) );
        }

        if ( unlink( $validated_path ) ) {
            /**
             * Hook propio al eliminar una tienda.
             */
            do_action( 'lbtecno_gtpw_after_delete_file', $filename );
            return true;
        }

        return new WP_Error( 'delete_failed', __( 'No se pudo eliminar el archivo de la tienda.', 'lbtecno-gtpw' ) );
    }

    /**
     * Obtiene el contenido del archivo JSON de una tienda en formato de texto.
     *
     * @param string $filename Nombre del archivo.
     * @return string|false
     */
    public function get_store_content( $filename ) {
        $validated_path = $this->validate_filepath( $filename );
        if ( ! $validated_path ) {
            return false;
        }

        return file_get_contents( $validated_path );
    }

    /**
     * Obtiene los datos decodificados de la tienda garantizando categorías y el array de productos.
     *
     * @param string $filename Nombre del archivo.
     * @return array
     */
    public function get_store_data( $filename ) {
        $content  = $this->get_store_content( $filename );
        $defaults = $this->get_default_store_data();

        if ( false === $content || empty( trim( $content ) ) ) {
            return $defaults;
        }

        $decoded = json_decode( $content, true );
        if ( ! is_array( $decoded ) ) {
            return $defaults;
        }

        // Asegurar array de categorías
        if ( ! isset( $decoded['categories'] ) || ! is_array( $decoded['categories'] ) ) {
            $decoded['categories'] = array();
        }

        // Garantizar categoría ID 1 ("Todo")
        $has_todo = false;
        foreach ( $decoded['categories'] as $cat ) {
            if ( isset( $cat['id'] ) && '1' === (string) $cat['id'] ) {
                $has_todo = true;
                break;
            }
        }

        if ( ! $has_todo ) {
            array_unshift(
                $decoded['categories'],
                array(
                    'id'   => '1',
                    'name' => 'Todo',
                    'icon' => '',
                )
            );
        }

        // Asegurar array de productos
        if ( ! isset( $decoded['products'] ) || ! is_array( $decoded['products'] ) ) {
            $decoded['products'] = array();
        }

        return wp_parse_args( $decoded, $defaults );
    }

    /**
     * Cuenta la cantidad de productos asociados a una categoría específica.
     *
     * @param array $category Datos de la categoría (id, name).
     * @param array $products Lista de productos almacenados en la tienda.
     * @return int Número de productos asignados a la categoría.
     */
    public function count_products_in_category( array $category, array $products ) {
        $count    = 0;
        $cat_id   = isset( $category['id'] ) ? (string) $category['id'] : '';
        $cat_name = isset( $category['name'] ) ? (string) $category['name'] : '';

        foreach ( $products as $prod ) {
            $p_cat = isset( $prod['category'] ) ? (string) $prod['category'] : '';
            if ( ! empty( $p_cat ) ) {
                if ( ( ! empty( $cat_id ) && $p_cat === $cat_id ) || ( ! empty( $cat_name ) && $p_cat === $cat_name ) ) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Guarda los datos estructurados de una tienda en el archivo JSON.
     *
     * @param string $filename Nombre del archivo.
     * @param array  $data     Datos estructurados de la tienda.
     * @return true|WP_Error
     */
    public function save_store_data( $filename, array $data ) {
        $validated_path = $this->validate_filepath( $filename );
        if ( ! $validated_path ) {
            return new WP_Error( 'invalid_store', __( 'No se pudo validar la ruta de la tienda a guardar.', 'lbtecno-gtpw' ) );
        }

        $defaults    = $this->get_default_store_data();
        $merged_data = wp_parse_args( $data, $defaults );

        if ( ! isset( $merged_data['categories'] ) || ! is_array( $merged_data['categories'] ) ) {
            $merged_data['categories'] = array();
        }

        if ( ! isset( $merged_data['products'] ) || ! is_array( $merged_data['products'] ) ) {
            $merged_data['products'] = array();
        }

        // Garantizar que la categoría ID 1 ("Todo") se mantenga con el nombre "Todo"
        $has_todo = false;
        foreach ( $merged_data['categories'] as $index => &$cat ) {
            if ( isset( $cat['id'] ) && '1' === (string) $cat['id'] ) {
                $cat['name'] = 'Todo';
                $has_todo    = true;
                break;
            }
        }
        unset( $cat );

        if ( ! $has_todo ) {
            array_unshift(
                $merged_data['categories'],
                array(
                    'id'   => '1',
                    'name' => 'Todo',
                    'icon' => '',
                )
            );
        }

        $json_str = json_encode( $merged_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        $saved = file_put_contents( $validated_path, $json_str );
        if ( false === $saved ) {
            return new WP_Error( 'save_failed', __( 'Error al guardar los datos de la tienda en el servidor.', 'lbtecno-gtpw' ) );
        }

        /**
         * Hook propio al actualizar una tienda.
         */
        do_action( 'lbtecno_gtpw_after_save_store', $filename, $merged_data );

        return true;
    }
}
