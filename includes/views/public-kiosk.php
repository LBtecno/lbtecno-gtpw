<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Plantilla pública de la interfaz del Kiosco Digital.
 *
 * @package LBtecno_GTPW
 * @author Alejandro Leal <https://lbtecno.net>
 */
?>
<div class="lbtecno-gtpw-kiosk-wrapper">
    <!-- CONTENEDOR PRINCIPAL: Modo Kiosco en Desktop / Fullscreen en Mobile -->
    <div id="kiosk-container" class="relative bg-[var(--color-bg-container)] w-full h-full sm:h-[840px] sm:w-[412px] sm:rounded-[36px] sm:shadow-[0_25px_60px_-15px_rgba(0,0,0,0.9)] sm:border-[8px] sm:border-zinc-800 flex flex-col overflow-hidden transition-all duration-300 mx-auto">
        
        <!-- Notch simulador (Solo visible en pantallas grandes) -->
        <div class="hidden sm:flex absolute top-0 left-1/2 -translate-x-1/2 w-32 h-5 bg-zinc-800 rounded-b-xl z-50 items-center justify-center space-x-1.5">
            <div class="w-12 h-1 bg-zinc-900 rounded-full"></div>
            <div class="w-2 h-2 bg-zinc-900 rounded-full"></div>
        </div>

        <!-- Barra de Estado Simulada (Mobile-first feel) -->
        <div class="px-6 pt-3 sm:pt-6 pb-2 flex items-center justify-between text-xs font-semibold bg-[var(--color-bg-container)] border-b border-[var(--color-border)] z-40 shrink-0 select-none text-[var(--color-text-secondary)] transition-colors duration-300">
            <!-- Botón Home -->
            <button onclick="goHome(event)" class="p-1.5 rounded-lg bg-[var(--color-bg-card)] border border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-[#c5a880] hover:border-[#c5a880]/40 active:scale-95 transition-all flex items-center justify-center shadow-xs cursor-pointer" title="Inicio / Home">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12l9-9 9 9M4 10v10a1 1 0 001 1h4a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h4a1 1 0 001-1V10"></path>
                </svg>
            </button>

            <!-- Toggle Switch de Tema (Claro / Oscuro) -->
            <button id="theme-toggle-btn" onclick="toggleTheme()" class="relative flex items-center w-11 h-6 bg-[var(--color-bg-card)] border border-[var(--color-border)] rounded-full p-0.5 cursor-pointer hover:border-[#c5a880]/40 transition-all duration-300 shadow-xs" title="Cambiar Tema (Claro / Oscuro)">
                <div id="theme-toggle-dot" class="w-4 h-4 rounded-full bg-[#c5a880] flex items-center justify-center text-[9px] transition-transform duration-300 transform translate-x-0 shadow-sm">
                    <span id="theme-icon">🌙</span>
                </div>
            </button>

            <!-- Botón Información -->
            <button onclick="openInfoModal()" class="p-1.5 rounded-lg bg-[var(--color-bg-card)] border border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-[#c5a880] hover:border-[#c5a880]/40 active:scale-95 transition-all flex items-center justify-center shadow-xs cursor-pointer" title="Información">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="1"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
            </button>
        </div>

        <!-- VISTA DE CONTENIDO SCROLLABLE -->
        <div class="flex-1 flex flex-col min-h-0 bg-[var(--color-bg-container)] overflow-y-auto no-scrollbar transition-colors duration-300">
            
            <!-- Hero / Banner del Catálogo -->
            <div class="relative h-48 w-full shrink-0 overflow-hidden bg-zinc-900">
                <img id="hero-banner-img" src="" alt="Catálogo" class="w-full h-full object-cover brightness-[0.35]">
                <div class="absolute inset-0 bg-gradient-to-t from-[var(--color-bg-container)] via-[var(--color-bg-container)]/40 to-transparent transition-colors duration-300"></div>
                <div class="absolute inset-x-0 bottom-0 p-5 flex items-center space-x-3 text-[var(--color-text-primary)]">
                    <div class="w-12 h-12 rounded-2xl overflow-hidden bg-[var(--color-bg-card)] border border-[var(--color-border)] shadow-xl shrink-0">
                        <img id="catalog-logo-img" src="" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <h2 id="catalog-name-title" class="text-2xl font-bold font-serif tracking-wide text-[#c5a880]">Kiosco</h2>
                        <p id="catalog-subtitle" class="text-xs text-[var(--color-text-secondary)] font-medium mt-0.5 transition-colors duration-300">Catálogo Digital</p>
                    </div>
                </div>
            </div>

            <!-- Barra de Controles: Buscador y Botón Carrito -->
            <div class="p-4 bg-[var(--color-bg-container)]/90 backdrop-blur-md border-b border-[var(--color-border)] flex items-center gap-2 sticky top-0 z-30 shadow-md transition-colors duration-300">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 text-sm">🔍</span>
                    <input id="search-input" type="text" placeholder="Buscar..." class="w-full pl-9 pr-8 py-2.5 rounded-xl text-xs font-medium bg-[var(--color-bg-input)] border border-[var(--color-border)] focus:border-[#c5a880] focus:outline-none focus:ring-1 focus:ring-[#c5a880]/30 text-[var(--color-text-primary)] placeholder-zinc-500 transition-colors duration-300">
                    <button id="clear-search" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-[10px] bg-[var(--color-bg-card)] px-1.5 py-0.5 rounded text-[var(--color-text-secondary)] font-bold hover:text-[var(--color-text-primary)] transition-colors duration-300">X</button>
                </div>
                
                <!-- Botón Carrito de Compras -->
                <button id="cart-toggle-btn" onclick="openCartPanel()" class="relative p-2.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] text-[var(--color-text-secondary)] hover:text-[#c5a880] hover:border-[#c5a880]/40 transition-all duration-300 cursor-pointer" title="Mi Carrito">
                    <!-- Icono carrito SVG -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <!-- Badge contador -->
                    <span id="cart-count-badge" class="hidden absolute -top-1.5 -right-1.5 bg-[#c5a880] text-zinc-950 text-[9px] font-extrabold w-4 h-4 rounded-full flex items-center justify-center border border-[var(--color-bg-container)]">0</span>
                </button>
            </div>

            <!-- Slider Horizontal de Categorías -->
            <div class="py-3.5 px-4 bg-[var(--color-bg-container)] border-b border-[var(--color-border)] overflow-x-auto no-scrollbar shrink-0 sticky top-[69px] z-30 transition-colors duration-300">
                <div id="category-container" class="flex space-x-2 min-w-max">
                    <!-- Inyectado dinámicamente por JS -->
                </div>
            </div>

            <!-- Grid de Productos -->
            <div class="p-4 flex-1">
                <div id="products-grid" class="grid grid-cols-1 gap-3.5 pb-10">
                    <!-- Inyectado dinámicamente por JS -->
                </div>
                
                <!-- Estado Vacío -->
                <div id="empty-state" class="hidden text-center py-16 px-4 space-y-3">
                    <span class="text-4xl block">✨</span>
                    <h4 class="font-bold text-xs text-[var(--color-text-secondary)] uppercase tracking-widest">Sin resultados</h4>
                    <p class="text-xs text-[var(--color-text-secondary)] max-w-[200px] mx-auto leading-normal">No encontramos opciones que coincidan con tu búsqueda.</p>
                </div>
            </div>

        </div>
        
        <!-- Indicador Home de iOS simulado -->
        <div class="hidden sm:block absolute bottom-1 left-1/2 -translate-x-1/2 w-32 h-1 bg-zinc-700 rounded-full z-50 pointer-events-none"></div>
    </div>

    <!-- ===== PANEL LATERAL: CARRITO DE COMPRAS ===== -->
    <div id="cart-backdrop" class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300" onclick="closeCartPanel()"></div>

    <div id="cart-panel" class="fixed inset-y-0 right-0 z-[70] w-full sm:max-w-[380px] bg-[var(--color-bg-modal)] border-l border-[var(--color-border)] shadow-2xl flex flex-col transform translate-x-full pointer-events-none" style="opacity:1">
        
        <!-- Cabecera del panel -->
        <div class="px-5 pt-5 pb-4 border-b border-[var(--color-border)] flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#c5a880]/15 border border-[#c5a880]/30 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-[#c5a880]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-serif font-bold text-base text-[var(--color-text-primary)]">Mi Carrito</h2>
                    <p id="cart-item-count-label" class="text-[10px] text-[var(--color-text-secondary)] font-medium">0 productos</p>
                </div>
            </div>
            <button onclick="closeCartPanel()" class="w-8 h-8 rounded-full bg-[var(--color-bg-card)] border border-[var(--color-border)] flex items-center justify-center text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] active:scale-90 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>

        <!-- Lista de items del carrito -->
        <div id="cart-items-list" class="flex-1 overflow-y-auto no-scrollbar px-4 py-3 space-y-3">
            <!-- Inyectado dinámicamente -->
        </div>

        <!-- Estado vacío del carrito -->
        <div id="cart-empty" class="hidden flex-1 flex flex-col items-center justify-center text-center px-6 py-12 space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-[var(--color-bg-card)] border border-[var(--color-border)] flex items-center justify-center">
                <svg class="w-7 h-7 text-[var(--color-text-secondary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
            <div>
                <p class="font-bold text-sm text-[var(--color-text-primary)]">Carrito vacío</p>
                <p class="text-xs text-[var(--color-text-secondary)] mt-1 leading-relaxed">Agrega productos desde el catálogo para verlos aquí.</p>
            </div>
        </div>

        <!-- Footer: Resumen y Acción -->
        <div id="cart-footer" class="hidden px-5 py-4 border-t border-[var(--color-border)] bg-[var(--color-bg-modal)]/95 backdrop-blur-md space-y-3 shrink-0">
            <!-- Subtotal -->
            <div class="flex items-center justify-between text-xs">
                <span class="text-[var(--color-text-secondary)] font-medium">Subtotal</span>
                <span id="cart-subtotal" class="font-bold font-serif text-[#c5a880] text-base">$0.00</span>
            </div>
            <!-- Separador decorativo -->
            <div class="border-t border-dashed border-[var(--color-border)]"></div>
            <!-- Botón Volver al catálogo -->
            <button onclick="closeCartPanel()" class="w-full py-2 rounded-xl text-[10px] font-bold uppercase tracking-widest text-[var(--color-text-secondary)] border border-[var(--color-border)] hover:border-[#c5a880]/40 hover:text-[#c5a880] active:scale-95 transition-all duration-200">
                Volver al catálogo
            </button>
            <!-- Botón Ordenar (WhatsApp) -->
            <button onclick="sendWhatsAppOrder()" class="w-full py-3 rounded-xl font-bold font-display text-xs uppercase tracking-widest text-zinc-950 bg-[#c5a880] hover:bg-[#b3956d] active:scale-[0.98] transition-all shadow-lg flex items-center justify-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Enviar pedido
            </button>
        </div>
    </div>

    <!-- MODAL DETALLE DE PRODUCTO (Bottom Sheet animado) -->
    <div id="detail-modal" class="fixed inset-0 z-50 flex flex-col justify-end opacity-0 pointer-events-none transition-opacity duration-300">
        <!-- Backdrop oscuro con blur -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-xs" onclick="closeModal()"></div>
        
        <!-- Drawer -->
        <div class="relative max-h-[85%] w-full sm:max-w-[412px] mx-auto bg-[var(--color-bg-modal)] rounded-t-[28px] border-t border-[var(--color-border)] shadow-2xl flex flex-col transform translate-y-full transition-transform duration-300 overflow-hidden">
            <!-- Botón Cerrar Superior -->
            <button onclick="closeModal()" class="absolute top-4 right-4 z-50 w-8 h-8 rounded-full bg-black/60 text-zinc-300 hover:text-white flex items-center justify-center font-bold text-sm border border-zinc-700 transition-colors">✕</button>
            
            <div class="overflow-y-auto no-scrollbar pb-36">
                <!-- Imagen Hero del Modal -->
                <div class="h-52 w-full relative bg-zinc-900">
                    <img id="modal-image" src="" alt="" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-[var(--color-bg-modal)] via-[var(--color-bg-modal)]/20 to-transparent"></div>
                    <div class="absolute bottom-4 left-5 right-5 text-white">
                        <h3 id="modal-title" class="text-2xl font-serif leading-tight text-[#c5a880]"></h3>
                    </div>
                </div>

                <!-- Contenido detallado -->
                <div class="p-5 space-y-5">
                    <!-- Descripción -->
                    <div class="space-y-1">
                        <h4 class="text-[10px] font-bold uppercase tracking-widest text-[#c5a880]">Descripción</h4>
                        <p id="modal-description" class="text-xs text-[var(--color-text-secondary)] leading-relaxed"></p>
                    </div>

                    <!-- Mensaje informativo -->
                    <div class="p-3 bg-[var(--color-bg-card)]/30 rounded-xl border border-[var(--color-border)] flex items-start space-x-2 text-[11px] text-[var(--color-text-secondary)]">
                        <span class="text-xs text-[#c5a880]">ℹ️</span>
                        <p class="leading-normal">
                            Este catálogo es informativo. Si desea adquirir o consultar sobre un producto, contacte directamente con soporte.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Panel de acción inferior sticky -->
            <div class="absolute bottom-0 inset-x-0 p-4 border-t border-[var(--color-border)] bg-[var(--color-bg-modal)]/95 backdrop-blur-md flex flex-col gap-3">

                <!-- Fila: precio + controles qty + botón agregar -->
                <div class="flex items-center gap-2">

                    <!-- Precio -->
                    <div class="flex flex-col leading-tight mr-1">
                        <span class="text-[9px] font-bold uppercase tracking-widest text-[var(--color-text-secondary)]">Valor</span>
                        <span id="modal-price" class="text-lg font-bold font-serif text-[#c5a880]"></span>
                    </div>

                    <div class="flex-1"></div>

                    <!-- Controles de cantidad -->
                    <button onclick="modalDecQty()" id="modal-dec-btn"
                        class="p-1.5 rounded-lg bg-[var(--color-bg-card)] border border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-rose-400 hover:border-rose-400/40 active:scale-95 transition-all flex items-center justify-center shadow-xs cursor-pointer select-none"
                        title="Reducir cantidad">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14"/></svg>
                    </button>

                    <span id="modal-qty-display" class="text-sm font-bold font-serif text-[var(--color-text-primary)] min-w-[24px] text-center select-none">1</span>

                    <button onclick="modalIncQty()"
                        class="p-1.5 rounded-lg bg-[var(--color-bg-card)] border border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-[#c5a880] hover:border-[#c5a880]/40 active:scale-95 transition-all flex items-center justify-center shadow-xs cursor-pointer select-none"
                        title="Aumentar cantidad">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    </button>

                    <!-- Botón Agregar al Carrito -->
                    <button id="modal-add-cart-btn" onclick="addToCartFromModal()"
                        class="p-1.5 rounded-lg bg-[#c5a880] border border-[#c5a880] text-zinc-950 hover:bg-[#b3956d] hover:border-[#b3956d] active:scale-95 transition-all flex items-center justify-center shadow-xs cursor-pointer gap-1.5 px-3"
                        title="Agregar al carrito">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        <span class="text-[10px] font-bold uppercase tracking-wider font-display">Agregar</span>
                    </button>
                </div>

                <!-- Botón volver -->
                <button onclick="closeModal()" class="w-full py-2 rounded-xl font-bold font-display text-[10px] uppercase tracking-widest text-[var(--color-text-secondary)] border border-[var(--color-border)] hover:border-[#c5a880]/40 hover:text-[#c5a880] transition-all">
                    Volver al Catálogo
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL DE INFORMACIÓN ESTÁTICO -->
    <div id="info-modal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
        <!-- Backdrop oscuro con blur -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-xs" onclick="closeInfoModal()"></div>
        
        <!-- Contenedor del Modal Pequeño -->
        <div class="relative w-full max-w-[320px] bg-[var(--color-bg-modal)] rounded-3xl border border-[var(--color-border)] shadow-2xl p-6 flex flex-col items-center text-center transform scale-95 transition-transform duration-300 z-10 space-y-4">
            <!-- Botón Cerrar Superior -->
            <button onclick="closeInfoModal()" class="absolute top-3 right-3 w-7 h-7 rounded-full bg-[var(--color-bg-card)] text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] border border-[var(--color-border)] flex items-center justify-center font-bold text-xs transition-colors cursor-pointer" title="Cerrar">✕</button>
            
            <!-- Imagen Tipo Logo -->
            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-[var(--color-bg-card)] border-2 border-[#c5a880]/40 shadow-xl flex items-center justify-center shrink-0 mt-2">
                <img src="https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=300&q=80" alt="Logo Negocio" class="w-full h-full object-cover">
            </div>

            <!-- Subtítulo / Información Básica -->
            <div class="space-y-1">
                <h3 class="text-base font-serif font-bold text-[#c5a880]">Kiosco Core</h3>
                <p class="text-xs font-medium text-[var(--color-text-secondary)] leading-snug">
                    Catálogo Digital & Experiencia Interactiva
                </p>
            </div>

            <!-- Botón de Llamada a la Acción (CTA) -->
            <button onclick="handleInfoCTA()" class="w-full py-2.5 px-4 rounded-xl font-bold font-display text-xs uppercase tracking-widest text-zinc-950 bg-[#c5a880] hover:bg-[#b3956d] active:scale-[0.98] transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                <span>Más información</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
</div>
