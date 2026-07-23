<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Controlador de Administración del Plugin LBtecno GTPW.
 *
 * @package LBtecno_GTPW
 * @author Alejandro Leal <https://lbtecno.net>
 */
class LBTecno_GTPW_Admin {

    /**
     * Instancia del componente de almacenamiento.
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

        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'handle_form_actions' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Registra la opción de menú en la barra lateral de administración.
     */
    public function register_admin_menu() {
        add_menu_page(
            'LBtecno GTPW',
            'GTPW',
            'manage_options',
            'lbtecno-gtpw',
            array( $this, 'render_admin_page' ),
            'dashicons-store',
            80
        );
    }

    /**
     * Carga las hojas de estilo, scripts de administración y la librería de Medios de WordPress.
     *
     * @param string $hook_suffix Identificador de la página actual en wp-admin.
     */
    public function enqueue_admin_assets( $hook_suffix ) {
        if ( 'toplevel_page_lbtecno-gtpw' !== $hook_suffix ) {
            return;
        }

        // Cargar Selector de Medios de WordPress (wp.media)
        wp_enqueue_media();

        wp_enqueue_style(
            'lbtecno-gtpw-admin-styles',
            LBTECNO_GTPW_URL . 'assets/css/admin.css',
            array(),
            LBTECNO_GTPW_VERSION
        );

        wp_enqueue_script(
            'lbtecno-gtpw-admin-script',
            LBTECNO_GTPW_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            LBTECNO_GTPW_VERSION,
            true
        );
    }

    /**
     * Procesa los envíos de formularios POST (Crear, Eliminar, Datos General, Categorías y Productos).
     */
    public function handle_form_actions() {
        if ( ! isset( $_POST['lbtecno_gtpw_action'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'No tienes permisos suficientes para realizar esta acción.', 'lbtecno-gtpw' ) );
        }

        $action = sanitize_text_field( wp_unslash( $_POST['lbtecno_gtpw_action'] ) );

        // Crear Tienda
        if ( 'create_json' === $action ) {
            check_admin_referer( 'lbtecno_gtpw_create_action', 'lbtecno_gtpw_nonce' );

            $raw_name = isset( $_POST['json_name'] ) ? trim( wp_unslash( $_POST['json_name'] ) ) : '';

            if ( empty( $raw_name ) ) {
                $this->redirect_with_notice( 'error', __( 'Debes especificar un nombre para la tienda.', 'lbtecno-gtpw' ) );
                return;
            }

            $sanitized_name = $this->storage->sanitize_store_name( $raw_name );

            if ( empty( $sanitized_name ) ) {
                $this->redirect_with_notice( 'error', __( 'El nombre de la tienda sólo debe contener letras, números y espacios.', 'lbtecno-gtpw' ) );
                return;
            }

            $result = $this->storage->create_store( $sanitized_name );

            if ( is_wp_error( $result ) ) {
                $this->redirect_with_notice( 'error', $result->get_error_message() );
            } else {
                $display_title = $this->storage->get_display_name( $sanitized_name );
                $this->redirect_with_notice(
                    'success',
                    sprintf( __( 'Tienda "%s" creada exitosamente.', 'lbtecno-gtpw' ), esc_html( $display_title ) )
                );
            }
            return;
        }

        // Guardar Datos de la Tienda (Pestaña "Tienda")
        if ( 'save_store_details' === $action ) {
            check_admin_referer( 'lbtecno_gtpw_save_store_action', 'lbtecno_gtpw_nonce' );

            $store_file = isset( $_POST['store_file'] ) ? sanitize_file_name( wp_unslash( $_POST['store_file'] ) ) : '';

            if ( empty( $store_file ) ) {
                $this->redirect_with_notice( 'error', __( 'Tienda no especificada.', 'lbtecno-gtpw' ) );
                return;
            }

            $catalog_name = isset( $_POST['catalogName'] ) ? sanitize_text_field( wp_unslash( $_POST['catalogName'] ) ) : '';
            $subtitle     = isset( $_POST['subtitle'] ) ? sanitize_text_field( wp_unslash( $_POST['subtitle'] ) ) : '';
            $whatsapp     = isset( $_POST['whatsapp'] ) ? sanitize_text_field( wp_unslash( $_POST['whatsapp'] ) ) : '';
            $home_url     = isset( $_POST['homeUrl'] ) ? esc_url_raw( wp_unslash( $_POST['homeUrl'] ) ) : '';
            $logo_image   = isset( $_POST['logoImage'] ) ? esc_url_raw( wp_unslash( $_POST['logoImage'] ) ) : '';
            $banner_image = isset( $_POST['bannerImage'] ) ? esc_url_raw( wp_unslash( $_POST['bannerImage'] ) ) : '';

            $current_data = $this->storage->get_store_data( $store_file );

            $store_data = array(
                'catalogName' => $catalog_name,
                'subtitle'    => $subtitle,
                'whatsapp'    => $whatsapp,
                'homeUrl'     => $home_url,
                'logoImage'   => $logo_image,
                'bannerImage' => $banner_image,
                'categories'  => isset( $current_data['categories'] ) ? $current_data['categories'] : array(),
                'products'    => isset( $current_data['products'] ) ? $current_data['products'] : array(),
            );

            $result = $this->storage->save_store_data( $store_file, $store_data );

            if ( is_wp_error( $result ) ) {
                $this->redirect_with_notice( 'error', $result->get_error_message(), $store_file, 'general' );
            } else {
                $display_title = $this->storage->get_display_name( $store_file );
                $this->redirect_with_notice(
                    'success',
                    sprintf( __( 'Datos de la tienda "%s" guardados correctamente.', 'lbtecno-gtpw' ), esc_html( $display_title ) ),
                    $store_file,
                    'general'
                );
            }
            return;
        }

        // Guardar Categorías (Pestaña "Categorías")
        if ( 'save_store_categories' === $action ) {
            check_admin_referer( 'lbtecno_gtpw_save_categories_action', 'lbtecno_gtpw_nonce' );

            $store_file = isset( $_POST['store_file'] ) ? sanitize_file_name( wp_unslash( $_POST['store_file'] ) ) : '';

            if ( empty( $store_file ) ) {
                $this->redirect_with_notice( 'error', __( 'Tienda no especificada.', 'lbtecno-gtpw' ) );
                return;
            }

            $current_data        = $this->storage->get_store_data( $store_file );
            $existing_categories = isset( $current_data['categories'] ) ? $current_data['categories'] : array();
            $existing_products   = isset( $current_data['products'] ) ? $current_data['products'] : array();

            $cat_ids   = isset( $_POST['cat_id'] ) && is_array( $_POST['cat_id'] ) ? $_POST['cat_id'] : array();
            $cat_names = isset( $_POST['cat_name'] ) && is_array( $_POST['cat_name'] ) ? $_POST['cat_name'] : array();
            $cat_icons = isset( $_POST['cat_icon'] ) && is_array( $_POST['cat_icon'] ) ? $_POST['cat_icon'] : array();

            // Mapa de IDs y nombres enviados en la petición POST
            $submitted_ids   = array();
            $submitted_names = array();
            for ( $i = 0; $i < count( $cat_names ); $i++ ) {
                $id   = isset( $cat_ids[ $i ] ) ? trim( sanitize_text_field( wp_unslash( $cat_ids[ $i ] ) ) ) : '';
                $name = isset( $cat_names[ $i ] ) ? trim( sanitize_text_field( wp_unslash( $cat_names[ $i ] ) ) ) : '';
                if ( ! empty( $id ) ) {
                    $submitted_ids[ $id ] = true;
                }
                if ( ! empty( $name ) ) {
                    $submitted_names[ $name ] = true;
                }
            }

            // Validar si alguna categoría existente fue eliminada y tiene productos asociados
            foreach ( $existing_categories as $exist_cat ) {
                $exist_id   = isset( $exist_cat['id'] ) ? (string) $exist_cat['id'] : '';
                $exist_name = isset( $exist_cat['name'] ) ? $exist_cat['name'] : '';

                if ( '1' === $exist_id || 'Todo' === $exist_name ) {
                    continue;
                }

                $is_deleted = false;
                if ( ! empty( $exist_id ) && ! isset( $submitted_ids[ $exist_id ] ) ) {
                    $is_deleted = true;
                } elseif ( empty( $exist_id ) && ! empty( $exist_name ) && ! isset( $submitted_names[ $exist_name ] ) ) {
                    $is_deleted = true;
                }

                if ( $is_deleted ) {
                    $linked_count = $this->storage->count_products_in_category( $exist_cat, $existing_products );
                    if ( $linked_count > 0 ) {
                        $this->redirect_with_notice(
                            'error',
                            sprintf(
                                __( 'No se puede eliminar la categoría "%1$s" porque tiene %2$d producto(s) asociado(s). Reasigna o elimina los productos primero.', 'lbtecno-gtpw' ),
                                esc_html( $exist_name ),
                                $linked_count
                            ),
                            $store_file,
                            'categories'
                        );
                        return;
                    }
                }
            }

            // Calcular el número máximo de ID numérico existente
            $max_id   = 1;
            $used_ids = array( '1' => true );

            foreach ( $cat_ids as $id_val ) {
                $clean_id = trim( sanitize_text_field( wp_unslash( $id_val ) ) );
                if ( ! empty( $clean_id ) ) {
                    $used_ids[ $clean_id ] = true;
                    if ( is_numeric( $clean_id ) && (int) $clean_id > $max_id ) {
                        $max_id = (int) $clean_id;
                    }
                }
            }

            $next_id    = max( 2, $max_id + 1 );
            $categories = array();
            $has_todo   = false;

            for ( $i = 0; $i < count( $cat_names ); $i++ ) {
                $id   = isset( $cat_ids[ $i ] ) ? trim( sanitize_text_field( wp_unslash( $cat_ids[ $i ] ) ) ) : '';
                $name = isset( $cat_names[ $i ] ) ? trim( sanitize_text_field( wp_unslash( $cat_names[ $i ] ) ) ) : '';
                $icon = isset( $cat_icons[ $i ] ) ? trim( sanitize_text_field( wp_unslash( $cat_icons[ $i ] ) ) ) : '';

                if ( '1' === $id || 'Todo' === $name ) {
                    $categories[] = array(
                        'id'   => '1',
                        'name' => 'Todo',
                        'icon' => $icon,
                    );
                    $has_todo = true;
                    continue;
                }

                if ( empty( $name ) && empty( $icon ) ) {
                    continue;
                }

                if ( empty( $id ) ) {
                    while ( isset( $used_ids[ (string) $next_id ] ) ) {
                        $next_id++;
                    }
                    $id              = (string) $next_id;
                    $used_ids[ $id ] = true;
                    $next_id++;
                }

                $categories[] = array(
                    'id'   => $id,
                    'name' => $name,
                    'icon' => $icon,
                );
            }

            if ( ! $has_todo ) {
                array_unshift(
                    $categories,
                    array(
                        'id'   => '1',
                        'name' => 'Todo',
                        'icon' => isset( $cat_icons[0] ) ? trim( sanitize_text_field( wp_unslash( $cat_icons[0] ) ) ) : '',
                    )
                );
            }

            $current_data['categories'] = $categories;

            $result = $this->storage->save_store_data( $store_file, $current_data );

            if ( is_wp_error( $result ) ) {
                $this->redirect_with_notice( 'error', $result->get_error_message(), $store_file, 'categories' );
            } else {
                $display_title = $this->storage->get_display_name( $store_file );
                $this->redirect_with_notice(
                    'success',
                    sprintf( __( 'Categorías de la tienda "%s" guardadas correctamente.', 'lbtecno-gtpw' ), esc_html( $display_title ) ),
                    $store_file,
                    'categories'
                );
            }
            return;
        }

        // Guardar Productos (Pestaña "Productos")
        if ( 'save_store_products' === $action ) {
            check_admin_referer( 'lbtecno_gtpw_save_products_action', 'lbtecno_gtpw_nonce' );

            $store_file = isset( $_POST['store_file'] ) ? sanitize_file_name( wp_unslash( $_POST['store_file'] ) ) : '';

            if ( empty( $store_file ) ) {
                $this->redirect_with_notice( 'error', __( 'Tienda no especificada.', 'lbtecno-gtpw' ) );
                return;
            }

            $prod_names  = isset( $_POST['prod_name'] ) && is_array( $_POST['prod_name'] ) ? $_POST['prod_name'] : array();
            $prod_descs  = isset( $_POST['prod_desc'] ) && is_array( $_POST['prod_desc'] ) ? $_POST['prod_desc'] : array();
            $prod_prices = isset( $_POST['prod_price'] ) && is_array( $_POST['prod_price'] ) ? $_POST['prod_price'] : array();
            $prod_cats   = isset( $_POST['prod_category'] ) && is_array( $_POST['prod_category'] ) ? $_POST['prod_category'] : array();
            $prod_imgs   = isset( $_POST['prod_image'] ) && is_array( $_POST['prod_image'] ) ? $_POST['prod_image'] : array();

            $products = array();

            for ( $i = 0; $i < count( $prod_names ); $i++ ) {
                $name      = isset( $prod_names[ $i ] ) ? trim( sanitize_text_field( wp_unslash( $prod_names[ $i ] ) ) ) : '';
                $desc      = isset( $prod_descs[ $i ] ) ? trim( sanitize_textarea_field( wp_unslash( $prod_descs[ $i ] ) ) ) : '';
                $price_raw = isset( $prod_prices[ $i ] ) ? trim( sanitize_text_field( wp_unslash( $prod_prices[ $i ] ) ) ) : '0';
                $cat       = isset( $prod_cats[ $i ] ) ? trim( sanitize_text_field( wp_unslash( $prod_cats[ $i ] ) ) ) : '';
                $image     = isset( $prod_imgs[ $i ] ) ? esc_url_raw( wp_unslash( $prod_imgs[ $i ] ) ) : '';

                // Formatear precio con exactamente 2 dígitos decimales como string ("10.00", "12.50")
                $price_float = is_numeric( $price_raw ) ? (float) $price_raw : 0.0;
                $price       = number_format( $price_float, 2, '.', '' );

                // Ignorar filas totalmente vacías
                if ( empty( $name ) && empty( $desc ) && empty( $image ) && '0.00' === $price ) {
                    continue;
                }

                $products[] = array(
                    'name'        => $name,
                    'description' => $desc,
                    'price'       => $price,
                    'category'    => $cat,
                    'image'       => $image,
                );
            }

            $current_data             = $this->storage->get_store_data( $store_file );
            $current_data['products'] = $products;

            $result = $this->storage->save_store_data( $store_file, $current_data );

            if ( is_wp_error( $result ) ) {
                $this->redirect_with_notice( 'error', $result->get_error_message(), $store_file, 'products' );
            } else {
                $display_title = $this->storage->get_display_name( $store_file );
                $this->redirect_with_notice(
                    'success',
                    sprintf( __( 'Productos de la tienda "%s" guardados correctamente.', 'lbtecno-gtpw' ), esc_html( $display_title ) ),
                    $store_file,
                    'products'
                );
            }
            return;
        }

        // Eliminar Tienda
        if ( 'delete_json' === $action ) {
            check_admin_referer( 'lbtecno_gtpw_delete_action', 'lbtecno_gtpw_nonce' );

            $file_to_delete = isset( $_POST['file_name'] ) ? sanitize_file_name( wp_unslash( $_POST['file_name'] ) ) : '';

            if ( empty( $file_to_delete ) ) {
                $this->redirect_with_notice( 'error', __( 'Nombre de tienda no especificado.', 'lbtecno-gtpw' ) );
                return;
            }

            $display_title = $this->storage->get_display_name( $file_to_delete );
            $result        = $this->storage->delete_store( $file_to_delete );

            if ( is_wp_error( $result ) ) {
                $this->redirect_with_notice( 'error', $result->get_error_message() );
            } else {
                $this->redirect_with_notice(
                    'success',
                    sprintf( __( 'Tienda "%s" eliminada correctamente.', 'lbtecno-gtpw' ), esc_html( $display_title ) )
                );
            }
            return;
        }
    }

    /**
     * Redirecciona a la página del plugin con parámetros de notificación.
     *
     * @param string $type       Tipo de aviso ('success' o 'error').
     * @param string $message    Mensaje explicativo.
     * @param string $store_file Archivo de tienda opcional para volver a la pantalla de edición.
     * @param string $tab        Pestaña activa opcional.
     */
    private function redirect_with_notice( $type, $message, $store_file = '', $tab = '' ) {
        $page_url = admin_url( 'admin.php?page=lbtecno-gtpw' );

        $args = array(
            'lbtecno_notice_type' => $type,
            'lbtecno_notice_msg'  => rawurlencode( $message ),
        );

        if ( ! empty( $store_file ) ) {
            $args['action'] = 'view_store';
            $args['store']  = urlencode( $store_file );
        }

        if ( ! empty( $tab ) ) {
            $args['tab'] = $tab;
        }

        $redirect_url = add_query_arg( $args, $page_url );
        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Carga y renderiza la plantilla de la vista correspondiente.
     */
    public function render_admin_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $action     = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';
        $store_file = isset( $_GET['store'] ) ? sanitize_file_name( wp_unslash( $_GET['store'] ) ) : '';

        if ( 'view_store' === $action && ! empty( $store_file ) ) {
            $validated_path = $this->storage->validate_filepath( $store_file );

            if ( ! $validated_path ) {
                ?>
                <div class="wrap lbtecno-gtpw-wrap">
                    <div class="notice notice-error"><p><?php esc_html_e( 'La tienda solicitada no existe o no es válida.', 'lbtecno-gtpw' ); ?></p></div>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=lbtecno-gtpw' ) ); ?>" class="button">← <?php esc_html_e( 'Volver a la lista de tiendas', 'lbtecno-gtpw' ); ?></a>
                </div>
                <?php
                return;
            }

            include LBTECNO_GTPW_PATH . 'includes/admin/views/store-detail.php';
        } else {
            $stores = $this->storage->get_all_stores();
            include LBTECNO_GTPW_PATH . 'includes/admin/views/store-list.php';
        }
    }

    /**
     * Renderiza las notificaciones de WordPress Admin.
     */
    protected function render_notices() {
        if ( ! isset( $_GET['lbtecno_notice_type'] ) || ! isset( $_GET['lbtecno_notice_msg'] ) ) {
            return;
        }

        $type    = sanitize_key( $_GET['lbtecno_notice_type'] );
        $message = sanitize_text_field( rawurldecode( $_GET['lbtecno_notice_msg'] ) );
        $class   = ( 'success' === $type ) ? 'notice-success' : 'notice-error';

        printf(
            '<div class="notice %1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr( $class ),
            esc_html( $message )
        );
    }
}
