<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plantilla de vista para el listado principal de tiendas y formulario de creación.
 *
 * @package LBtecno_GTPW
 * @author Alejandro Leal <https://lbtecno.net>
 */
?>
<div class="wrap lbtecno-gtpw-wrap">
    <h1 class="wp-heading-inline">Gestor de Tiendas - LBtecno GTPW</h1>
    <hr class="wp-header-end">

    <?php $this->render_notices(); ?>

    <!-- Formulario de Creación de Tiendas -->
    <div class="lbtecno-gtpw-card">
        <h2>Crear Nueva Tienda</h2>
        <p>Ingresa el nombre de la tienda deseada (sólo letras, números y espacios). Los espacios se convertirán automáticamente en guiones.</p>

        <form method="post" action="" class="lbtecno-gtpw-form">
            <?php wp_nonce_field( 'lbtecno_gtpw_create_action', 'lbtecno_gtpw_nonce' ); ?>
            <input type="hidden" name="lbtecno_gtpw_action" value="create_json">

            <label for="lbtecno_json_name" class="screen-reader-text">Nombre de la Tienda</label>
            <input 
                type="text" 
                id="lbtecno_json_name" 
                name="json_name" 
                placeholder="Ejemplo: Tienda San Jose" 
                pattern="[a-zA-Z0-9\s]+" 
                title="Solo se permiten letras, números y espacios."
                required
                autocomplete="off"
            >

            <button type="submit" class="button button-primary">
                <span class="dashicons dashicons-plus-alt"></span> Crear Tienda
            </button>
        </form>

        <p class="description" style="margin-top: 15px; padding-top: 12px; border-top: 1px solid #f0f0f1; font-size: 0.9em; display: flex; align-items: center; gap: 5px;">
            <span class="dashicons dashicons-info" style="color: #2271b1;"></span>
            <span><strong>Nota:</strong> Si las URLs virtuales devuelven un error 404 o no se visualizan correctamente, ve a la sección de <a href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>" target="_blank" rel="noopener noreferrer">Enlaces permanentes</a> y haz clic en <strong>Guardar cambios</strong> para refrescar las reglas de reescritura.</span>
        </p>
    </div>

    <!-- Listado de Tiendas Existentes -->
    <div class="lbtecno-gtpw-card">
        <h2>Tiendas Registradas (<?php echo count( $stores ); ?>)</h2>
        
        <?php if ( empty( $stores ) ) : ?>
            <p><em>No hay tiendas creadas por el momento.</em></p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped table-view-list lbtecno-gtpw-table">
                <thead>
                    <tr>
                        <th>Nombre de la Tienda</th>
                        <th>URL Virtual</th>
                        <th style="width: 270px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $stores as $file_path ) : 
                        $filename     = basename( $file_path );
                        $clean_slug   = pathinfo( $filename, PATHINFO_FILENAME );
                        $display_name = $this->storage->get_display_name( $filename );
                        $virtual_url  = LBTecno_GTPW_Virtual_Url::get_virtual_url( $clean_slug );
                        $store_url    = admin_url( 'admin.php?page=lbtecno-gtpw&action=view_store&store=' . urlencode( $filename ) );
                    ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( $display_name ); ?></strong>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( $virtual_url ); ?>" target="_blank" rel="noopener noreferrer" style="text-decoration: none;">
                                    <?php echo esc_html( $virtual_url ); ?>
                                    <span class="dashicons dashicons-external"></span>
                                </a>
                            </td>
                            <td class="lbtecno-gtpw-actions" style="text-align: right;">
                                <button 
                                    type="button" 
                                    class="button button-small lbtecno-gtpw-copy-btn" 
                                    data-clipboard="<?php echo esc_attr( $virtual_url ); ?>"
                                    title="Copiar URL Virtual: <?php echo esc_attr( $virtual_url ); ?>"
                                >
                                    <span class="dashicons dashicons-admin-page"></span> Copiar URL
                                </button>
                                <a href="<?php echo esc_url( $store_url ); ?>" class="button button-small">
                                    <span class="dashicons dashicons-edit"></span> Editar
                                </a>
                                <form method="post" action="" onsubmit="return confirm('¿Estás seguro de que deseas eliminar permanentemente la tienda <?php echo esc_js( $display_name ); ?>?');">
                                    <?php wp_nonce_field( 'lbtecno_gtpw_delete_action', 'lbtecno_gtpw_nonce' ); ?>
                                    <input type="hidden" name="lbtecno_gtpw_action" value="delete_json">
                                    <input type="hidden" name="file_name" value="<?php echo esc_attr( $filename ); ?>">
                                    <button type="submit" class="button button-small button-link-delete" style="color: #b32d2e;">
                                        <span class="dashicons dashicons-trash"></span> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Footer Informativo -->
    <div class="lbtecno-gtpw-footer">
        <strong>LBtecno GTPW v<?php echo esc_html( LBTECNO_GTPW_VERSION ); ?></strong> | Desarrollado por <a href="https://lbtecno.net" target="_blank" rel="noopener noreferrer">Alejandro Leal</a>.
    </div>
</div>
