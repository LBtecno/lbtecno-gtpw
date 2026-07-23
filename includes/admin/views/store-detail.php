<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plantilla de vista para la interfaz individual de una tienda con pestañas (Tienda, Categorías, Productos, Código JSON).
 *
 * @package LBtecno_GTPW
 * @author Alejandro Leal <https://lbtecno.net>
 */

$clean_slug    = pathinfo( $store_file, PATHINFO_FILENAME );
$display_name  = $this->storage->get_display_name( $store_file );
$store_data    = $this->storage->get_store_data( $store_file );
$content       = $this->storage->get_store_content( $store_file );
$virtual_url   = LBTecno_GTPW_Virtual_Url::get_virtual_url( $clean_slug );
$back_url      = admin_url( 'admin.php?page=lbtecno-gtpw' );

// Pestaña activa ('general' por defecto)
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

$tab_general_url    = admin_url( 'admin.php?page=lbtecno-gtpw&action=view_store&store=' . urlencode( $store_file ) . '&tab=general' );
$tab_categories_url = admin_url( 'admin.php?page=lbtecno-gtpw&action=view_store&store=' . urlencode( $store_file ) . '&tab=categories' );
$tab_products_url   = admin_url( 'admin.php?page=lbtecno-gtpw&action=view_store&store=' . urlencode( $store_file ) . '&tab=products' );
$tab_code_url       = admin_url( 'admin.php?page=lbtecno-gtpw&action=view_store&store=' . urlencode( $store_file ) . '&tab=code' );

// Formatear JSON para la pestaña de código
$decoded = json_decode( $content );
if ( null !== $decoded ) {
    $formatted_json = json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
} else {
    $formatted_json = ( false !== $content && ! empty( $content ) ) ? $content : "{}";
}

$categories = isset( $store_data['categories'] ) && is_array( $store_data['categories'] ) ? $store_data['categories'] : array();
$products   = isset( $store_data['products'] ) && is_array( $store_data['products'] ) ? $store_data['products'] : array();

// Filtrar categorías personalizadas excluyendo la categoría fija "Todo" (ID 1)
$custom_categories = array_values( array_filter( $categories, function( $cat ) {
    $cat_id   = isset( $cat['id'] ) ? (string) $cat['id'] : '';
    $cat_name = isset( $cat['name'] ) ? $cat['name'] : '';
    return ( '1' !== $cat_id && 'Todo' !== $cat_name );
}) );
?>
<div class="wrap lbtecno-gtpw-wrap">
    <a href="<?php echo esc_url( $back_url ); ?>" class="lbtecno-gtpw-back-link">
        <span class="dashicons dashicons-arrow-left-alt"></span> Volver a la lista de tiendas
    </a>

    <h1 class="wp-heading-inline">Tienda: <?php echo esc_html( $display_name ); ?></h1>
    <hr class="wp-header-end">

    <?php $this->render_notices(); ?>

    <!-- Template oculto de opciones de categorías personalizadas (excluyendo 'Todo') para JS dinámico -->
    <template id="lbtecno-gtpw-categories-options-template">
        <?php foreach ( $custom_categories as $cat ) : ?>
            <option value="<?php echo esc_attr( isset( $cat['id'] ) ? $cat['id'] : $cat['name'] ); ?>">
                <?php echo esc_html( isset( $cat['name'] ) ? $cat['name'] : $cat['id'] ); ?>
            </option>
        <?php endforeach; ?>
    </template>

    <!-- Navegación por Pestañas (Tabs) -->
    <nav class="nav-tab-wrapper wp-clearfix" style="margin-top: 15px; margin-bottom: 20px;">
        <a href="<?php echo esc_url( $tab_general_url ); ?>" class="nav-tab <?php echo ( 'general' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-store"></span> Tienda
        </a>
        <a href="<?php echo esc_url( $tab_categories_url ); ?>" class="nav-tab <?php echo ( 'categories' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-category"></span> Categorías (<?php echo count( $categories ); ?>)
        </a>
        <a href="<?php echo esc_url( $tab_products_url ); ?>" class="nav-tab <?php echo ( 'products' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-cart"></span> Productos (<?php echo count( $products ); ?>)
        </a>
        <a href="<?php echo esc_url( $tab_code_url ); ?>" class="nav-tab <?php echo ( 'code' === $active_tab ) ? 'nav-tab-active' : ''; ?>">
            <span class="dashicons dashicons-code-standards"></span> Visualizar código en el JSON
        </a>
    </nav>

    <?php if ( 'general' === $active_tab ) : ?>
        <!-- Pestaña 1: Tienda (Configuración de Campos JSON) -->
        <div class="lbtecno-gtpw-card">
            <h2>Configuración de la Tienda</h2>
            <p>Edita los parámetros principales de la tienda. Al guardar, se actualizará el archivo JSON correspondiente.</p>
            
            <p style="margin-bottom: 20px;">
                <strong>URL Virtual:</strong> 
                <a href="<?php echo esc_url( $virtual_url ); ?>" target="_blank" rel="noopener noreferrer" class="lbtecno-gtpw-code-tag"><?php echo esc_html( $virtual_url ); ?></a>
                <button 
                    type="button" 
                    class="button button-small lbtecno-gtpw-copy-btn" 
                    data-clipboard="<?php echo esc_attr( $virtual_url ); ?>"
                    title="Copiar URL al portapapeles"
                >
                    <span class="dashicons dashicons-admin-page"></span> Copiar URL
                </button>
            </p>

            <form method="post" action="" class="lbtecno-gtpw-store-form">
                <?php wp_nonce_field( 'lbtecno_gtpw_save_store_action', 'lbtecno_gtpw_nonce' ); ?>
                <input type="hidden" name="lbtecno_gtpw_action" value="save_store_details">
                <input type="hidden" name="store_file" value="<?php echo esc_attr( $store_file ); ?>">

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="catalogName">Nombre del Catálogo</label></th>
                            <td>
                                <input 
                                    type="text" 
                                    id="catalogName" 
                                    name="catalogName" 
                                    class="regular-text" 
                                    value="<?php echo esc_attr( $store_data['catalogName'] ); ?>" 
                                    placeholder="Ejemplo: Catálogo Digital Principal"
                                >
                                <p class="description">Nombre general identificador del catálogo de la tienda.</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="subtitle">Subtítulo</label></th>
                            <td>
                                <input 
                                    type="text" 
                                    id="subtitle" 
                                    name="subtitle" 
                                    class="regular-text" 
                                    value="<?php echo esc_attr( $store_data['subtitle'] ); ?>" 
                                    placeholder="Ejemplo: Variedad en tecnología y soluciones"
                                >
                                <p class="description">Eslogan o descripción secundaria de la tienda.</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="whatsapp">WhatsApp</label></th>
                            <td>
                                <input 
                                    type="text" 
                                    id="whatsapp" 
                                    name="whatsapp" 
                                    class="regular-text" 
                                    value="<?php echo esc_attr( $store_data['whatsapp'] ); ?>" 
                                    placeholder="Ejemplo: +50688888888"
                                >
                                <p class="description">Número de contacto telefónico o WhatsApp de la tienda.</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="homeUrl">URL de Inicio (Home URL)</label></th>
                            <td>
                                <input 
                                    type="url" 
                                    id="homeUrl" 
                                    name="homeUrl" 
                                    class="regular-text" 
                                    value="<?php echo esc_attr( $store_data['homeUrl'] ); ?>" 
                                    placeholder="https://lbtecno.net"
                                >
                                <p class="description">Enlace de retorno a la página principal o sitio web.</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="logoImage">Imagen de Logo</label></th>
                            <td>
                                <div class="lbtecno-gtpw-media-input-wrap">
                                    <input 
                                        type="text" 
                                        id="logoImage" 
                                        name="logoImage" 
                                        class="regular-text" 
                                        value="<?php echo esc_attr( $store_data['logoImage'] ); ?>" 
                                        placeholder="https://..."
                                    >
                                    <button 
                                        type="button" 
                                        class="button lbtecno-gtpw-upload-media-btn" 
                                        data-target="logoImage" 
                                        data-preview="logoImage_preview"
                                    >
                                        <span class="dashicons dashicons-admin-media"></span> Seleccionar de Medios
                                    </button>
                                </div>
                                <div id="logoImage_preview" class="lbtecno-gtpw-media-preview" style="<?php echo empty( $store_data['logoImage'] ) ? 'display:none;' : ''; ?>">
                                    <img src="<?php echo esc_url( $store_data['logoImage'] ); ?>" alt="Vista Previa Logo">
                                    <button type="button" class="button-link-delete lbtecno-gtpw-remove-media-btn" data-target="logoImage" data-preview="logoImage_preview" style="margin-top: 4px; font-size: 0.85em; color:#b32d2e;">Eliminar imagen</button>
                                </div>
                                <p class="description">URL de la imagen del logotipo desde la biblioteca de Medios de WordPress.</p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="bannerImage">Imagen de Banner</label></th>
                            <td>
                                <div class="lbtecno-gtpw-media-input-wrap">
                                    <input 
                                        type="text" 
                                        id="bannerImage" 
                                        name="bannerImage" 
                                        class="regular-text" 
                                        value="<?php echo esc_attr( $store_data['bannerImage'] ); ?>" 
                                        placeholder="https://..."
                                    >
                                    <button 
                                        type="button" 
                                        class="button lbtecno-gtpw-upload-media-btn" 
                                        data-target="bannerImage" 
                                        data-preview="bannerImage_preview"
                                    >
                                        <span class="dashicons dashicons-admin-media"></span> Seleccionar de Medios
                                    </button>
                                </div>
                                <div id="bannerImage_preview" class="lbtecno-gtpw-media-preview" style="<?php echo empty( $store_data['bannerImage'] ) ? 'display:none;' : ''; ?>">
                                    <img src="<?php echo esc_url( $store_data['bannerImage'] ); ?>" alt="Vista Previa Banner">
                                    <button type="button" class="button-link-delete lbtecno-gtpw-remove-media-btn" data-target="bannerImage" data-preview="bannerImage_preview" style="margin-top: 4px; font-size: 0.85em; color:#b32d2e;">Eliminar imagen</button>
                                </div>
                                <p class="description">URL de la imagen del banner promocional desde Medios de WordPress.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p class="submit" style="margin-top: 20px;">
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-saved"></span> Guardar Cambios de la Tienda
                    </button>
                </p>
            </form>
        </div>

    <?php elseif ( 'categories' === $active_tab ) : ?>
        <!-- Pestaña 2: Categorías -->
        <div class="lbtecno-gtpw-card">
            <h2>Gestión de Categorías de la Tienda</h2>
            <p>Añade, edita o elimina las categorías pertenecientes a <strong><?php echo esc_html( $display_name ); ?></strong>. La categoría <strong>Todo</strong> es fija y permanente. No se pueden eliminar categorías con productos asignados.</p>

            <form method="post" action="">
                <?php wp_nonce_field( 'lbtecno_gtpw_save_categories_action', 'lbtecno_gtpw_nonce' ); ?>
                <input type="hidden" name="lbtecno_gtpw_action" value="save_store_categories">
                <input type="hidden" name="store_file" value="<?php echo esc_attr( $store_file ); ?>">

                <table class="wp-list-table widefat fixed striped lbtecno-gtpw-categories-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Nombre de la Categoría</th>
                            <th style="width: 35%;">Icono</th>
                            <th style="width: 15%; text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="lbtecno-gtpw-categories-tbody">
                        <?php foreach ( $categories as $index => $cat ) : 
                            $cat_name     = isset( $cat['name'] ) ? $cat['name'] : '';
                            $cat_id       = isset( $cat['id'] ) ? (string) $cat['id'] : '';
                            $cat_icon     = isset( $cat['icon'] ) ? $cat['icon'] : '';
                            $is_todo      = ( '1' === $cat_id || 'Todo' === $cat_name );
                            $linked_count = $this->storage->count_products_in_category( $cat, $products );
                        ?>
                            <tr class="lbtecno-gtpw-category-row">
                                <td>
                                    <input type="hidden" name="cat_id[]" value="<?php echo esc_attr( $cat_id ); ?>">
                                    <?php if ( $is_todo ) : ?>
                                        <input 
                                            type="text" 
                                            name="cat_name[]" 
                                            class="widefat" 
                                            value="Todo" 
                                            readonly 
                                            style="background-color: #f0f0f1; color: #50575e; cursor: not-allowed;" 
                                            title="La categoría 'Todo' es fija y su nombre no puede modificarse."
                                        >
                                    <?php else : ?>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <input type="text" name="cat_name[]" class="widefat" value="<?php echo esc_attr( $cat_name ); ?>" placeholder="Ej. Bebidas" required>
                                            <?php if ( $linked_count > 0 ) : ?>
                                                <span class="lbtecno-gtpw-badge" style="background:#fff8e5; color:#b25900; border:1px solid #f0b849; white-space:nowrap; font-weight:normal;" title="Productos vinculados a esta categoría">
                                                    <?php echo esc_html( $linked_count ); ?> prod.
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="text" name="cat_icon[]" class="widefat" value="<?php echo esc_attr( $cat_icon ); ?>" placeholder="Ej. dashicons-cart o URL de imagen">
                                </td>
                                <td style="text-align: right;">
                                    <?php if ( $is_todo ) : ?>
                                        <span class="description" style="color: #646970; font-size: 0.88em; font-style: italic; display: inline-flex; align-items: center; gap: 3px;">
                                            <span class="dashicons dashicons-lock" style="font-size: 15px; width: 15px; height: 15px;"></span> Fija
                                        </span>
                                    <?php else : ?>
                                        <button 
                                            type="button" 
                                            class="button button-link-delete lbtecno-gtpw-remove-cat-btn" 
                                            data-linked-count="<?php echo esc_attr( $linked_count ); ?>"
                                            data-cat-name="<?php echo esc_attr( $cat_name ); ?>"
                                            style="color: #b32d2e;"
                                        >
                                            <span class="dashicons dashicons-trash"></span> Eliminar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                    <button type="button" id="lbtecno-gtpw-add-category-btn" class="button">
                        <span class="dashicons dashicons-plus-alt"></span> Añadir Categoría
                    </button>

                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-saved"></span> Guardar Categorías
                    </button>
                </div>
            </form>
        </div>

    <?php elseif ( 'products' === $active_tab ) : ?>
        <!-- Pestaña 3: Productos -->
        <div class="lbtecno-gtpw-card">
            <h2>Gestión de Productos de la Tienda</h2>
            <p>Añade, edita o elimina los productos pertenecientes a <strong><?php echo esc_html( $display_name ); ?></strong>. Se asignan a categorías personalizadas existentes.</p>

            <?php if ( empty( $custom_categories ) ) : ?>
                <div class="notice notice-warning inline" style="margin-top: 15px; margin-bottom: 20px; padding: 12px 15px; border-left-color: #dba617;">
                    <p style="margin: 0; font-size: 0.95em;">
                        <span class="dashicons dashicons-warning" style="color: #dba617;"></span> 
                        <strong>Atención:</strong> No existen categorías personalizadas creadas en esta tienda. Para poder crear productos, primero debes añadir al menos una categoría en la pestaña <a href="<?php echo esc_url( $tab_categories_url ); ?>">Categorías</a>.
                    </p>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <?php wp_nonce_field( 'lbtecno_gtpw_save_products_action', 'lbtecno_gtpw_nonce' ); ?>
                <input type="hidden" name="lbtecno_gtpw_action" value="save_store_products">
                <input type="hidden" name="store_file" value="<?php echo esc_attr( $store_file ); ?>">

                <table class="wp-list-table widefat fixed striped lbtecno-gtpw-products-table">
                    <thead>
                        <tr>
                            <th style="width: 28%;">Nombre y Descripción</th>
                            <th style="width: 14%;">Precio</th>
                            <th style="width: 18%;">Categoría</th>
                            <th style="width: 30%;">Imagen (Medios)</th>
                            <th style="width: 10%; text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="lbtecno-gtpw-products-tbody">
                        <?php if ( empty( $products ) ) : ?>
                            <tr id="lbtecno-gtpw-no-products-msg">
                                <td colspan="5"><em>No hay productos registrados en esta tienda por el momento. <?php echo ! empty( $custom_categories ) ? 'Haz clic en "Añadir Producto" para agregar el primero.' : ''; ?></em></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ( $products as $index => $prod ) : 
                                $p_name      = isset( $prod['name'] ) ? $prod['name'] : '';
                                $p_desc      = isset( $prod['description'] ) ? $prod['description'] : '';
                                $p_raw_price = isset( $prod['price'] ) && is_numeric( $prod['price'] ) ? (float) $prod['price'] : 0.0;
                                $p_price_fmt = number_format( $p_raw_price, 2, '.', '' );
                                $p_cat       = isset( $prod['category'] ) ? $prod['category'] : '';
                                $p_img       = isset( $prod['image'] ) ? $prod['image'] : '';
                            ?>
                                <tr class="lbtecno-gtpw-product-row">
                                    <td>
                                        <input type="text" name="prod_name[]" class="widefat" value="<?php echo esc_attr( $p_name ); ?>" placeholder="Ej. Hamburguesa Doble" required>
                                        <textarea name="prod_desc[]" class="widefat" rows="2" placeholder="Descripción corta del producto..." style="margin-top: 6px;"><?php echo esc_textarea( $p_desc ); ?></textarea>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="prod_price[]" class="widefat" value="<?php echo esc_attr( $p_price_fmt ); ?>" placeholder="0.00">
                                    </td>
                                    <td>
                                        <select name="prod_category[]" class="widefat" required>
                                            <?php if ( empty( $custom_categories ) ) : ?>
                                                <option value="">(Crea una categoría primero)</option>
                                            <?php else : ?>
                                                <?php foreach ( $custom_categories as $cat_opt ) : 
                                                    $cat_val = isset( $cat_opt['id'] ) ? $cat_opt['id'] : ( isset( $cat_opt['name'] ) ? $cat_opt['name'] : '' );
                                                    $cat_lbl = isset( $cat_opt['name'] ) ? $cat_opt['name'] : $cat_val;
                                                ?>
                                                    <option value="<?php echo esc_attr( $cat_val ); ?>" <?php selected( $p_cat, $cat_val ); ?>>
                                                        <?php echo esc_html( $cat_lbl ); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="lbtecno-gtpw-media-input-wrap">
                                            <input type="text" name="prod_image[]" class="widefat lbtecno-gtpw-media-input" value="<?php echo esc_attr( $p_img ); ?>" placeholder="URL de imagen...">
                                            <button type="button" class="button lbtecno-gtpw-upload-media-btn" title="Seleccionar de Medios">
                                                <span class="dashicons dashicons-admin-media"></span>
                                            </button>
                                        </div>
                                        <div class="lbtecno-gtpw-media-preview" style="<?php echo empty( $p_img ) ? 'display:none;' : ''; ?> margin-top: 6px;">
                                            <img src="<?php echo esc_url( $p_img ); ?>" alt="Vista previa producto" style="max-height: 50px;">
                                            <button type="button" class="button-link-delete lbtecno-gtpw-remove-media-btn" style="margin-top: 2px; font-size: 0.8em; color:#b32d2e;">Quitar</button>
                                        </div>
                                    </td>
                                    <td style="text-align: right; vertical-align: top;">
                                        <button type="button" class="button button-link-delete lbtecno-gtpw-remove-prod-btn" style="color: #b32d2e;">
                                            <span class="dashicons dashicons-trash"></span> Eliminar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                    <?php if ( empty( $custom_categories ) ) : ?>
                        <button type="button" class="button" disabled title="Debes crear al menos una categoría personalizada en la pestaña Categorías primero.">
                            <span class="dashicons dashicons-plus-alt"></span> Añadir Producto
                        </button>
                    <?php else : ?>
                        <button type="button" id="lbtecno-gtpw-add-product-btn" class="button">
                            <span class="dashicons dashicons-plus-alt"></span> Añadir Producto
                        </button>
                    <?php endif; ?>

                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-saved"></span> Guardar Productos
                    </button>
                </div>
            </form>
        </div>

    <?php else : ?>
        <!-- Pestaña 4: Visualizar Código en el JSON -->
        <div class="lbtecno-gtpw-card">
            <h2>Contenido del Archivo JSON (<code><?php echo esc_html( $store_file ); ?></code>)</h2>
            <p>A continuación se muestra el código JSON almacenado para la tienda <strong><?php echo esc_html( $display_name ); ?></strong>:</p>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                <p style="margin: 0;">
                    <strong>URL Virtual:</strong> 
                    <a href="<?php echo esc_url( $virtual_url ); ?>" target="_blank" rel="noopener noreferrer" class="lbtecno-gtpw-code-tag"><?php echo esc_html( $virtual_url ); ?></a>
                </p>
                <button 
                    type="button" 
                    class="button button-secondary lbtecno-gtpw-copy-btn" 
                    data-copy-target="#lbtecno-gtpw-json-editor"
                    title="Copiar todo el código JSON al portapapeles"
                >
                    <span class="dashicons dashicons-admin-page"></span> Copiar Código JSON
                </button>
            </div>

            <textarea id="lbtecno-gtpw-json-editor" class="lbtecno-gtpw-code-editor" readonly spellcheck="false"><?php echo esc_textarea( $formatted_json ); ?></textarea>
        </div>
    <?php endif; ?>

    <!-- Footer Informativo -->
    <div class="lbtecno-gtpw-footer">
        <strong>LBtecno GTPW v<?php echo esc_html( LBTECNO_GTPW_VERSION ); ?></strong> | Desarrollado por <a href="https://lbtecno.net" target="_blank" rel="noopener noreferrer">Alejandro Leal</a>.
    </div>
</div>
