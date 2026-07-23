/**
 * MÓDULO PRINCIPAL DE LA APLICACIÓN DE KIOSCO
 * Maneja la carga del catálogo desde datos inyectados (window.LBTECNO_STORE_DATA),
 * filtrado por categorías, buscador en tiempo real y renderizado de productos.
 *
 * @package LBtecno_GTPW
 * @author Alejandro Leal <https://lbtecno.net>
 */

// Datos por defecto (fallback)
const FALLBACK_DATA = {
    catalogName: "Kiosco Neutral",
    subtitle: "Catálogo de Productos",
    whatsapp: "",
    homeUrl: "",
    logoImage: "https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=600&q=80",
    bannerImage: "https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80",
    categories: [
        { id: 'all', name: 'Todo', icon: '✨' },
        { id: 'cat1', name: 'Categoría 1', icon: '📁' }
    ],
    products: []
};

// Estado global de datos
let DATA = FALLBACK_DATA;

// Carga de datos desde la ventana pública (window.LBTECNO_STORE_DATA) o fallback
async function loadMenuData() {
    if (window.LBTECNO_STORE_DATA && typeof window.LBTECNO_STORE_DATA === 'object') {
        DATA = window.LBTECNO_STORE_DATA;
        return;
    }

    if (window.LBTECNO_STORE_JSON_URL) {
        try {
            const response = await fetch(window.LBTECNO_STORE_JSON_URL);
            if (response.ok) {
                DATA = await response.json();
                return;
            }
        } catch (err) {
            console.warn('Error al cargar JSON desde LBTECNO_STORE_JSON_URL:', err);
        }
    }

    try {
        const response = await fetch('menu-data.json');
        if (response.ok) {
            DATA = await response.json();
            return;
        }
    } catch (error) {
        // Fallback a variable previa o datos por defecto
    }

    DATA = window.MENU_DATA || FALLBACK_DATA;
}

// Estados de la app
let currentCategory = 'all';
let searchQuery = '';
const cardQtyMap = {}; // Cantidad pendiente por producto en tarjeta

// Botón Home / Redirección o Resetear filtros
function goHome(event) {
    if (event) event.preventDefault();

    // Redirigir a la URL de home si está configurada en la tienda (misma ventana)
    if (DATA.homeUrl && DATA.homeUrl.trim() !== '' && DATA.homeUrl !== '#') {
        window.location.href = DATA.homeUrl;
        return;
    }

    currentCategory = 'all';
    searchQuery = '';

    const searchInput = document.getElementById('search-input');
    const clearSearchBtn = document.getElementById('clear-search');

    if (searchInput) searchInput.value = '';
    if (clearSearchBtn) clearSearchBtn.classList.add('hidden');

    renderCategories();
    renderProducts();

    const scrollContainer = document.querySelector('.overflow-y-auto');
    if (scrollContainer) {
        scrollContainer.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// Inicializar y renderizar lista de categorías
function renderCategories() {
    const categoryContainer = document.getElementById('category-container');
    if (!categoryContainer || !DATA || !Array.isArray(DATA.categories)) return;

    categoryContainer.innerHTML = DATA.categories.map(cat => {
        const isActive = currentCategory === cat.id;
        const icon = cat.icon || '📁';
        return `
            <button onclick="setCategory('${cat.id}')" class="px-4 py-2.5 rounded-full text-xs font-semibold tracking-wide flex items-center space-x-2 transition-all duration-300 cursor-pointer ${
                isActive 
                    ? 'bg-[#c5a880] text-zinc-950 shadow-md font-bold scale-[1.03]' 
                    : 'bg-[var(--color-bg-input)] text-[var(--color-text-secondary)] border border-[var(--color-border)] hover:bg-[var(--color-bg-card)] hover:text-[var(--color-text-primary)]'
            }">
                <span>${icon}</span>
                <span class="font-display font-medium">${cat.name}</span>
            </button>
        `;
    }).join('');
}

function setCategory(id) {
    currentCategory = id;
    renderCategories();
    renderProducts();
}

// Renderizar la rejilla de productos según categoría y búsqueda
function renderProducts() {
    const productsGrid = document.getElementById('products-grid');
    const emptyState   = document.getElementById('empty-state');
    if (!productsGrid || !emptyState || !DATA || !Array.isArray(DATA.products)) return;

    const filtered = DATA.products.filter(p => {
        const ignoreCategory = searchQuery.trim().length > 0;
        const matchesCat = ignoreCategory || currentCategory === 'all' || p.category === currentCategory;
        const matchesSearch = (p.name || '').toLowerCase().includes(searchQuery.toLowerCase()) || 
                               (p.description || '').toLowerCase().includes(searchQuery.toLowerCase());
        return matchesCat && matchesSearch;
    });

    if (typeof updateCartBadge === 'function') updateCartBadge();

    if (filtered.length === 0) {
        productsGrid.innerHTML = '';
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    productsGrid.innerHTML = filtered.map(p => {
        const inCart = typeof cart !== 'undefined' && cart.some(item => item.name === p.name);
        const cartItem = typeof cart !== 'undefined' ? cart.find(item => item.name === p.name) : null;

        if (!(p.name in cardQtyMap)) cardQtyMap[p.name] = 1;
        const cardQty = inCart ? cartItem.qty : cardQtyMap[p.name];
        const nameKey = (p.name || '').replace(/'/g, "\\'");
        const numericPrice = typeof p.price === 'number' ? p.price : parseFloat(p.price) || 0;

        return `
            <div onclick="openModal('${nameKey}')" class="bg-[var(--color-bg-card)] rounded-2xl overflow-hidden border border-[var(--color-border)] hover:border-[#c5a880]/40 flex p-3.5 gap-3.5 shadow-sm active:scale-[0.99] transition-all duration-200 cursor-pointer relative group transition-colors duration-300">
                
                <!-- Miniatura Imagen -->
                <div class="h-24 w-24 rounded-xl overflow-hidden shrink-0 bg-[var(--color-bg-container)] border border-[var(--color-border)] transition-colors duration-300">
                    <img src="${p.image || ''}" alt="${p.name || ''}" class="w-full h-full object-cover">
                </div>

                <!-- Información del Producto -->
                <div class="flex flex-col justify-between flex-1 min-w-0">
                    <div class="space-y-1">
                        <h3 class="font-serif font-bold text-sm tracking-wide text-[var(--color-text-primary)] truncate group-hover:text-[#c5a880] transition-colors duration-300">${p.name || ''}</h3>
                        <p class="text-[11px] leading-snug text-[var(--color-text-secondary)] line-clamp-2 transition-colors duration-300">${p.description || ''}</p>
                    </div>

                    <!-- Fila inferior: precio + stepper + botón carrito -->
                    <div onclick="event.stopPropagation()" class="flex items-center pt-1.5 gap-1.5">

                        <!-- Precio -->
                        <span class="text-sm font-bold font-serif text-[#c5a880] shrink-0 mr-auto">${'$' + numericPrice.toFixed(2)}</span>

                        <!-- Botón − -->
                        <button 
                            onclick="cardChangeQty(event,'${nameKey}',-1)" 
                            class="p-1.5 rounded-lg bg-[var(--color-bg-card)] border border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-rose-400 hover:border-rose-400/40 active:scale-95 transition-all flex items-center justify-center shadow-xs cursor-pointer select-none ${cardQty <= 1 ? 'opacity-40 pointer-events-none' : ''}" 
                            title="Reducir">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14"/></svg>
                        </button>

                        <!-- Cantidad -->
                        <span class="text-xs font-bold font-serif text-[var(--color-text-primary)] min-w-[18px] text-center select-none">${cardQty}</span>

                        <!-- Botón + -->
                        <button 
                            onclick="cardChangeQty(event,'${nameKey}',1)" 
                            class="p-1.5 rounded-lg bg-[var(--color-bg-card)] border border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-[#c5a880] hover:border-[#c5a880]/40 active:scale-95 transition-all flex items-center justify-center shadow-xs cursor-pointer select-none" 
                            title="Aumentar">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        </button>

                        <!-- Botón carrito -->
                        <button 
                            onclick="toggleCart(event,'${nameKey}',${numericPrice})" 
                            class="p-1.5 rounded-lg border active:scale-95 transition-all flex items-center justify-center shadow-xs cursor-pointer gap-1 px-2 ${
                                inCart 
                                    ? 'bg-[#c5a880] border-[#c5a880] text-zinc-950 hover:bg-[#b3956d]' 
                                    : 'bg-[var(--color-bg-card)] border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-[#c5a880] hover:border-[#c5a880]/40'
                            }"
                            title="${inCart ? 'Quitar del carrito' : 'Agregar al carrito'}">
                            ${inCart
                                ? `<svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>`
                                : `<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>`
                            }
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Cambia la cantidad desde las tarjetas del catálogo
function cardChangeQty(event, name, delta) {
    if (event) event.stopPropagation();
    const inCart = typeof cart !== 'undefined' && cart.some(item => item.name === name);
    if (inCart) {
        if (typeof changeQty === 'function') changeQty(name, delta);
    } else {
        if (!(name in cardQtyMap)) cardQtyMap[name] = 1;
        cardQtyMap[name] = Math.max(1, cardQtyMap[name] + delta);
        renderProducts();
    }
}

// Función de inicialización
async function initApp() {
    await loadMenuData();

    // Cargar textos e imágenes de la cabecera
    const nameTitle  = document.getElementById('catalog-name-title');
    const subtitleEl = document.getElementById('catalog-subtitle');
    const logoImg    = document.getElementById('catalog-logo-img');
    const heroImg    = document.getElementById('hero-banner-img');

    if (nameTitle)  nameTitle.textContent  = DATA.catalogName || "Kiosco";
    if (subtitleEl) subtitleEl.textContent = DATA.subtitle || "Catálogo Digital";
    if (logoImg && DATA.logoImage) logoImg.src = DATA.logoImage;
    if (heroImg && DATA.bannerImage) heroImg.src = DATA.bannerImage;

    // Configurar listeners del buscador
    const searchInput    = document.getElementById('search-input');
    const clearSearchBtn = document.getElementById('clear-search');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchQuery = e.target.value;
            if (clearSearchBtn) {
                if (searchQuery.length > 0) {
                    clearSearchBtn.classList.remove('hidden');
                } else {
                    clearSearchBtn.classList.add('hidden');
                }
            }
            renderProducts();
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            searchQuery = '';
            clearSearchBtn.classList.add('hidden');
            renderProducts();
        });
    }

    // Renderizado inicial
    renderCategories();
    renderProducts();
    if (typeof renderCartPanel === 'function') renderCartPanel();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
