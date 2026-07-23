/**
 * MÓDULO DEL MODAL DETALLE DE PRODUCTO
 * Controla la apertura/cierre del modal inferior de producto
 * y la selección de cantidad antes de añadir al carrito.
 *
 * @package LBtecno_GTPW
 * @author Alejandro Leal <https://lbtecno.net>
 */

let modalCurrentProduct = null;
let modalQty = 1;

function openModal(name) {
    const data = (typeof DATA !== 'undefined' && DATA.products) ? DATA.products : [];
    const product = data.find(p => p.name === name);
    if (!product) return;

    modalCurrentProduct = product;
    modalQty = 1; // Resetear cantidad al abrir

    const modalImage = document.getElementById('modal-image');
    const modalTitle = document.getElementById('modal-title');
    const modalDesc  = document.getElementById('modal-description');
    const modalPrice = document.getElementById('modal-price');

    if (modalImage) modalImage.src = product.image;
    if (modalTitle) modalTitle.textContent = product.name;
    if (modalDesc)  modalDesc.textContent  = product.description;
    if (modalPrice) modalPrice.textContent = `$${product.price.toFixed(2)}`;

    syncModalQtyDisplay();

    const modal = document.getElementById('detail-modal');
    if (!modal) return;
    const drawer = modal.querySelector('.relative');

    modal.classList.remove('pointer-events-none', 'opacity-0');
    modal.classList.add('opacity-100');
    if (drawer) {
        drawer.classList.remove('translate-y-full');
        drawer.classList.add('translate-y-0');
    }
}

function closeModal() {
    const modal = document.getElementById('detail-modal');
    if (!modal) return;
    const drawer = modal.querySelector('.relative');

    if (drawer) {
        drawer.classList.remove('translate-y-0');
        drawer.classList.add('translate-y-full');
    }
    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modalCurrentProduct = null;
}

// Incrementa la cantidad en el modal
function modalIncQty() {
    modalQty++;
    syncModalQtyDisplay();
}

// Decrementa la cantidad en el modal (mínimo 1)
function modalDecQty() {
    if (modalQty > 1) modalQty--;
    syncModalQtyDisplay();
}

// Sincroniza el display de cantidad y el estado del botón menos en el modal
function syncModalQtyDisplay() {
    const display = document.getElementById('modal-qty-display');
    const decBtn  = document.getElementById('modal-dec-btn');
    if (display) display.textContent = modalQty;
    if (decBtn) {
        if (modalQty <= 1) {
            decBtn.classList.add('opacity-40', 'cursor-not-allowed');
        } else {
            decBtn.classList.remove('opacity-40', 'cursor-not-allowed');
        }
    }
}

// Agrega al carrito desde el modal usando la cantidad seleccionada
function addToCartFromModal() {
    if (!modalCurrentProduct) return;
    const { name, price } = modalCurrentProduct;
    const idx = cart.findIndex(item => item.name === name);
    if (idx >= 0) {
        cart[idx].qty += modalQty;
    } else {
        cart.push({ name, price, qty: modalQty });
    }
    saveCart();
    if (typeof renderProducts === 'function') renderProducts();
    if (typeof renderCartPanel === 'function') renderCartPanel();

    // Feedback visual en el botón y cierre suave del modal
    const btn = document.getElementById('modal-add-cart-btn');
    if (btn) {
        const prevHTML = btn.innerHTML;
        btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg><span class="text-[10px] font-bold uppercase tracking-wider font-display">×${modalQty} Agregado</span>`;
        btn.classList.add('in-cart-btn');
        setTimeout(() => {
            btn.innerHTML = prevHTML;
            btn.classList.remove('in-cart-btn');
            closeModal();
        }, 400);
    } else {
        closeModal();
    }

    // Resetear qty a 1 tras agregar
    modalQty = 1;
    syncModalQtyDisplay();
}

/**
 * MÓDULO DEL MODAL DE INFORMACIÓN
 */
function openInfoModal() {
    const modal = document.getElementById('info-modal');
    if (!modal) return;
    const content = modal.querySelector('.relative');
    modal.classList.remove('pointer-events-none', 'opacity-0');
    modal.classList.add('opacity-100');
    if (content) {
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }
}

function closeInfoModal() {
    const modal = document.getElementById('info-modal');
    if (!modal) return;
    const content = modal.querySelector('.relative');
    if (content) {
        content.classList.remove('scale-100');
        content.classList.add('scale-95');
    }
    modal.classList.remove('opacity-100');
    modal.classList.add('opacity-0', 'pointer-events-none');
}

function handleInfoCTA() {
    if (typeof DATA !== 'undefined' && DATA.whatsapp) {
        const phone = DATA.whatsapp;
        const msg = encodeURIComponent("¡Hola! Me gustaría obtener más información.");
        window.open(`https://wa.me/${phone}?text=${msg}`, '_blank');
    } else {
        closeInfoModal();
    }
}
