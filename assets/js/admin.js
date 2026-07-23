/**
 * Funcionalidad JavaScript para el área de administración de LBtecno GTPW.
 * Incluye:
 * 1. Copia de URLs y texto al portapapeles.
 * 2. Integración del Selector de Medios de WordPress (wp.media) para imágenes de Logo, Banner y Productos.
 * 3. Gestión dinámica de Filas de Categorías con restricción si existen productos asociados.
 * 4. Gestión dinámica de Filas de Productos (excluyendo la categoría 'Todo').
 *
 * Author: Alejandro Leal <https://lbtecno.net>
 */

jQuery(document).ready(function ($) {

    // --- 1. COPIAR TEXTO / URL / CÓDIGO AL PORTAPAPELES ---
    $(document).on('click', '.lbtecno-gtpw-copy-btn', function (e) {
        e.preventDefault();
        var $button = $(this);
        var textToCopy = $button.attr('data-clipboard');
        var targetSelector = $button.attr('data-copy-target');

        if (!textToCopy && targetSelector) {
            var $target = $(targetSelector);
            if ($target.length) {
                textToCopy = $target.is('input, textarea') ? $target.val() : $target.text();
            }
        }

        if (!textToCopy) {
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(function () {
                showCopiedFeedback($button);
            }).catch(function () {
                fallbackCopyTextToClipboard(textToCopy, $button);
            });
        } else {
            fallbackCopyTextToClipboard(textToCopy, $button);
        }
    });

    function fallbackCopyTextToClipboard(text, $button) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.position = 'fixed';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            var successful = document.execCommand('copy');
            if (successful) {
                showCopiedFeedback($button);
            }
        } catch (err) {
            console.error('No se pudo copiar al portapapeles:', err);
        }

        document.body.removeChild(textArea);
    }

    function showCopiedFeedback($button) {
        var originalHTML = $button.html();
        $button.addClass('copied');
        $button.html('<span class="dashicons dashicons-yes"></span> ¡Copiado!');

        setTimeout(function () {
            $button.removeClass('copied');
            $button.html(originalHTML);
        }, 2000);
    }

    // --- 2. SELECTOR DE MEDIOS DE WORDPRESS (wp.media) ---
    $(document).on('click', '.lbtecno-gtpw-upload-media-btn', function (e) {
        e.preventDefault();

        var $button = $(this);
        var targetInputId = $button.data('target');
        var targetPreviewId = $button.data('preview');
        
        var $targetInput = targetInputId ? $('#' + targetInputId) : $button.closest('td, .lbtecno-gtpw-media-input-wrap').find('input.lbtecno-gtpw-media-input');
        var $targetPreview = targetPreviewId ? $('#' + targetPreviewId) : $button.closest('td').find('.lbtecno-gtpw-media-preview');

        var customUploader = wp.media({
            title: 'Seleccionar Imagen desde Medios',
            button: {
                text: 'Usar esta imagen'
            },
            multiple: false
        });

        customUploader.on('select', function () {
            var attachment = customUploader.state().get('selection').first().toJSON();
            if (attachment && attachment.url) {
                $targetInput.val(attachment.url);

                if ($targetPreview.length) {
                    $targetPreview.find('img').attr('src', attachment.url);
                    $targetPreview.show();
                }
            }
        });

        customUploader.open();
    });

    // Eliminar imagen seleccionada
    $(document).on('click', '.lbtecno-gtpw-remove-media-btn', function (e) {
        e.preventDefault();
        var $button = $(this);
        var targetInputId = $button.data('target');
        var targetPreviewId = $button.data('preview');

        var $targetInput = targetInputId ? $('#' + targetInputId) : $button.closest('td, .lbtecno-gtpw-media-input-wrap').find('input.lbtecno-gtpw-media-input');
        var $targetPreview = targetPreviewId ? $('#' + targetPreviewId) : $button.closest('.lbtecno-gtpw-media-preview');

        $targetInput.val('');
        $targetPreview.find('img').attr('src', '');
        $targetPreview.hide();
    });

    // --- 3. GESTIÓN DINÁMICA DE CATEGORÍAS ---
    $('#lbtecno-gtpw-add-category-btn').on('click', function (e) {
        e.preventDefault();

        var $tbody = $('#lbtecno-gtpw-categories-tbody');

        var newRowHTML = `
            <tr class="lbtecno-gtpw-category-row">
                <td>
                    <input type="hidden" name="cat_id[]" value="">
                    <input type="text" name="cat_name[]" class="widefat lbtecno-gtpw-cat-name" placeholder="Ej. Bebidas" required>
                </td>
                <td>
                    <input type="text" name="cat_icon[]" class="widefat" placeholder="Ej. dashicons-cart o URL de imagen">
                </td>
                <td style="text-align: right;">
                    <button type="button" class="button button-link-delete lbtecno-gtpw-remove-cat-btn" data-linked-count="0" style="color: #b32d2e;">
                        <span class="dashicons dashicons-trash"></span> Eliminar
                    </button>
                </td>
            </tr>
        `;

        $('#lbtecno-gtpw-no-categories-msg').hide();
        $tbody.append(newRowHTML);
        $tbody.find('tr.lbtecno-gtpw-category-row:last .lbtecno-gtpw-cat-name').focus();
    });

    $(document).on('click', '.lbtecno-gtpw-remove-cat-btn', function (e) {
        e.preventDefault();
        var $button = $(this);
        var linkedCount = parseInt($button.attr('data-linked-count') || '0', 10);
        var catName = $button.attr('data-cat-name') || 'esta categoría';

        if (linkedCount > 0) {
            alert('No se puede eliminar la categoría "' + catName + '" porque tiene ' + linkedCount + ' producto(s) asociado(s). Reasigna o elimina los productos primero.');
            return;
        }

        var $row = $button.closest('tr');
        $row.fadeOut(200, function () {
            $row.remove();
            var remaining = $('#lbtecno-gtpw-categories-tbody tr.lbtecno-gtpw-category-row').length;
            if (remaining === 0) {
                $('#lbtecno-gtpw-no-categories-msg').show();
            }
        });
    });

    // --- 4. GESTIÓN DINÁMICA DE PRODUCTOS ---
    $('#lbtecno-gtpw-add-product-btn').on('click', function (e) {
        e.preventDefault();

        var categoriesOptionsHTML = $('#lbtecno-gtpw-categories-options-template').html() || '';
        categoriesOptionsHTML = categoriesOptionsHTML.trim();

        if (!categoriesOptionsHTML) {
            alert('Para poder añadir un producto, primero debes crear al menos una categoría personalizada en la pestaña Categorías.');
            return;
        }

        var $tbody = $('#lbtecno-gtpw-products-tbody');

        var newProdHTML = `
            <tr class="lbtecno-gtpw-product-row">
                <td>
                    <input type="text" name="prod_name[]" class="widefat lbtecno-gtpw-prod-name" placeholder="Ej. Hamburguesa Doble" required>
                    <textarea name="prod_desc[]" class="widefat" rows="2" placeholder="Descripción corta del producto..." style="margin-top: 6px;"></textarea>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="prod_price[]" class="widefat" placeholder="0.00" value="0.00">
                </td>
                <td>
                    <select name="prod_category[]" class="widefat" required>
                        ${categoriesOptionsHTML}
                    </select>
                </td>
                <td>
                    <div class="lbtecno-gtpw-media-input-wrap">
                        <input type="text" name="prod_image[]" class="widefat lbtecno-gtpw-media-input" placeholder="URL de imagen...">
                        <button type="button" class="button lbtecno-gtpw-upload-media-btn" title="Seleccionar de Medios">
                            <span class="dashicons dashicons-admin-media"></span>
                        </button>
                    </div>
                    <div class="lbtecno-gtpw-media-preview" style="display:none; margin-top: 6px;">
                        <img src="" alt="Vista previa producto" style="max-height: 50px;">
                        <button type="button" class="button-link-delete lbtecno-gtpw-remove-media-btn" style="margin-top: 2px; font-size: 0.8em; color:#b32d2e;">Quitar</button>
                    </div>
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <button type="button" class="button button-link-delete lbtecno-gtpw-remove-prod-btn" style="color: #b32d2e;">
                        <span class="dashicons dashicons-trash"></span> Eliminar
                    </button>
                </td>
            </tr>
        `;

        $('#lbtecno-gtpw-no-products-msg').hide();
        $tbody.append(newProdHTML);
        $tbody.find('tr.lbtecno-gtpw-product-row:last .lbtecno-gtpw-prod-name').focus();
    });

    $(document).on('click', '.lbtecno-gtpw-remove-prod-btn', function (e) {
        e.preventDefault();
        var $row = $(this).closest('tr');
        $row.fadeOut(200, function () {
            $row.remove();
            var remaining = $('#lbtecno-gtpw-products-tbody tr.lbtecno-gtpw-product-row').length;
            if (remaining === 0) {
                $('#lbtecno-gtpw-no-products-msg').show();
            }
        });
    });

});
