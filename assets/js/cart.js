/**
 * MÓDULO DEL CARRITO DE COMPRAS
 * Maneja el estado del carrito, almacenamiento en localStorage,
 * visualización del panel lateral y envío de pedidos a WhatsApp.
 *
 * @package LBtecno_GTPW
 * @author Alejandro Leal <https://lbtecno.net>
 */

let cart = JSON.parse(localStorage.getItem('core_catalog_cart')) || [];

// Guarda el carrito en localStorage
function saveCart() {
    localStorage.setItem('core_catalog_cart', JSON.stringify(cart));
}

// Actualiza el badge numérico del botón carrito
function updateCartBadge() {
    const cartCountBadge = document.getElementById('cart-count-badge');
    if (!cartCountBadge) return;

    const totalQty = cart.reduce((sum, item) => sum + item.qty, 0);
    if (totalQty > 0) {
        cartCountBadge.textContent = totalQty > 99 ? '99+' : totalQty;
        cartCountBadge.classList.remove('hidden');
        cartCountBadge.classList.remove('badge-pop');
        void cartCountBadge.offsetWidth; // reflow para animación
        cartCountBadge.classList.add('badge-pop');
    } else {
        cartCountBadge.classList.add('hidden');
    }
}

// Alterna la presencia de un producto en el carrito (desde tarjeta)
function toggleCart(event, name, price) {
    if (event) event.stopPropagation();
    const idx = cart.findIndex(item => item.name === name);
    if (idx >= 0) {
        // Si ya está en carrito -> quitar
        cart.splice(idx, 1);
        if (typeof cardQtyMap !== 'undefined') cardQtyMap[name] = 1;
    } else {
        // Si no está -> agregar con la cantidad seleccionada en la tarjeta
        const qty = (typeof cardQtyMap !== 'undefined' && cardQtyMap[name]) ? cardQtyMap[name] : 1;
        cart.push({ name, price, qty });
        if (typeof cardQtyMap !== 'undefined') cardQtyMap[name] = 1;
    }
    saveCart();
    if (typeof renderProducts === 'function') renderProducts();
    renderCartPanel();
}

// Cambia la cantidad de un item en el carrito
function changeQty(name, delta) {
    const idx = cart.findIndex(item => item.name === name);
    if (idx < 0) return;
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) {
        cart.splice(idx, 1);
    }
    saveCart();
    if (typeof renderProducts === 'function') renderProducts();
    renderCartPanel();
}

// Elimina un item del carrito
function removeFromCart(name) {
    cart = cart.filter(item => item.name !== name);
    saveCart();
    if (typeof renderProducts === 'function') renderProducts();
    renderCartPanel();
}

// Vacía el carrito
function clearCart() {
    cart = [];
    saveCart();
    if (typeof renderProducts === 'function') renderProducts();
    renderCartPanel();
}

// Renderiza el panel lateral del carrito
function renderCartPanel() {
    updateCartBadge();

    const cartItemsList      = document.getElementById('cart-items-list');
    const cartEmpty          = document.getElementById('cart-empty');
    const cartFooter         = document.getElementById('cart-footer');
    const cartSubtotal       = document.getElementById('cart-subtotal');
    const cartItemCountLabel = document.getElementById('cart-item-count-label');

    if (!cartItemsList) return;

    const totalQty = cart.reduce((sum, i) => sum + i.qty, 0);
    const subtotal = cart.reduce((sum, i) => sum + i.price * i.qty, 0);

    if (cartItemCountLabel) {
        cartItemCountLabel.textContent = `${totalQty} ${totalQty === 1 ? 'producto' : 'productos'}`;
    }
    if (cartSubtotal) {
        cartSubtotal.textContent = `$${subtotal.toFixed(2)}`;
    }

    if (cart.length === 0) {
        cartItemsList.innerHTML = '';
        if (cartEmpty) {
            cartEmpty.classList.remove('hidden');
            cartEmpty.classList.add('flex');
        }
        if (cartFooter) cartFooter.classList.add('hidden');
        return;
    }

    if (cartEmpty) {
        cartEmpty.classList.add('hidden');
        cartEmpty.classList.remove('flex');
    }
    if (cartFooter) cartFooter.classList.remove('hidden');

    const productsData = (typeof DATA !== 'undefined' && Array.isArray(DATA.products)) ? DATA.products : [];

    cartItemsList.innerHTML = cart.map(item => {
        const product = productsData.find(p => p.name === item.name);
        const imageSrc = product ? product.image : (item.image || '');
        const itemSubtotal = (item.price * item.qty).toFixed(2);
        const nameKey = item.name.replace(/'/g, "\\'");

        return `
            <div class="cart-item-enter bg-[var(--color-bg-card)] rounded-2xl border border-[var(--color-border)] p-3.5 flex gap-3 items-center">
                <!-- Mini imagen -->
                <div class="w-14 h-14 rounded-xl overflow-hidden shrink-0 bg-[var(--color-bg-container)] border border-[var(--color-border)]">
                    <img src="${imageSrc}" alt="${item.name}" class="w-full h-full object-cover">
                </div>
                <!-- Info -->
                <div class="flex-1 min-w-0 space-y-1.5">
                    <p class="font-serif font-bold text-xs text-[var(--color-text-primary)] truncate">${item.name}</p>
                    <p class="text-[10px] font-bold text-[#c5a880]">$${item.price.toFixed(2)} c/u</p>
                    <!-- Controles cantidad -->
                    <div class="flex items-center gap-2">
                        <button onclick="changeQty('${nameKey}', -1)" class="w-6 h-6 rounded-lg bg-[var(--color-bg-container)] border border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-rose-500 hover:border-rose-500/40 flex items-center justify-center text-xs font-bold active:scale-90 transition-all cursor-pointer">−</button>
                        <span class="text-xs font-bold text-[var(--color-text-primary)] min-w-[16px] text-center">${item.qty}</span>
                        <button onclick="changeQty('${nameKey}', 1)" class="w-6 h-6 rounded-lg bg-[var(--color-bg-container)] border border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-[#c5a880] hover:border-[#c5a880]/40 flex items-center justify-center text-xs font-bold active:scale-90 transition-all cursor-pointer">+</button>
                    </div>
                </div>
                <!-- Total del item + eliminar -->
                <div class="flex flex-col items-end gap-2 shrink-0">
                    <p class="font-serif font-bold text-sm text-[#c5a880]">$${itemSubtotal}</p>
                    <button onclick="removeFromCart('${nameKey}')" class="w-6 h-6 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center active:scale-90 transition-all cursor-pointer shadow-xs" title="Eliminar del carrito">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Abre el panel lateral del carrito
function openCartPanel() {
    renderCartPanel();
    const backdrop = document.getElementById('cart-backdrop');
    const panel    = document.getElementById('cart-panel');
    if (!backdrop || !panel) return;

    backdrop.classList.remove('opacity-0', 'pointer-events-none');
    backdrop.classList.add('opacity-100');
    panel.classList.remove('translate-x-full', 'pointer-events-none');
    panel.classList.add('translate-x-0');
}

// Cierra el panel lateral del carrito
function closeCartPanel() {
    const backdrop = document.getElementById('cart-backdrop');
    const panel    = document.getElementById('cart-panel');
    if (!backdrop || !panel) return;

    panel.classList.remove('translate-x-0');
    panel.classList.add('translate-x-full', 'pointer-events-none');
    backdrop.classList.add('opacity-0', 'pointer-events-none');
    backdrop.classList.remove('opacity-100');
}

// Envía el pedido por WhatsApp usando el número configurado en la tienda
function sendWhatsAppOrder() {
    if (cart.length === 0) return;

    const data = (typeof DATA !== 'undefined') ? DATA : {};
    const phone = (data.whatsapp || '').replace(/\D/g, ''); // solo dígitos
    if (!phone) {
        alert('⚠️ No hay número de WhatsApp configurado para esta tienda.');
        return;
    }

    const catalogName = data.catalogName || 'Kiosco';
    const total = cart.reduce((sum, i) => sum + i.price * i.qty, 0);

    const lines = cart.map((item, idx) => {
        const subtotal = (item.price * item.qty).toFixed(2);
        return `${idx + 1}. ${item.name} × ${item.qty} = $${subtotal}`;
    });

    const message = [
        `🛒 *Pedido desde ${catalogName}*`,
        '─────────────────',
        ...lines,
        '─────────────────',
        `*Total: $${total.toFixed(2)}*`,
        '',
        '_Enviado desde el catálogo digital._'
    ].join('\n');

    const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
    window.open(url, '_blank');
}
