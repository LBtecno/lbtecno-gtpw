/**
 * MÓDULO DE TEMA (CLARO / OSCURO)
 * Maneja la alternancia del tema visual y su persistencia en localStorage.
 *
 * @package LBtecno_GTPW
 * @author Alejandro Leal <https://lbtecno.net>
 */

let currentTheme = localStorage.getItem('core_catalog_theme') || 'dark';

function applyTheme(theme) {
    document.body.classList.remove('theme-dark', 'theme-light');
    document.body.classList.add(theme === 'light' ? 'theme-light' : 'theme-dark');
    
    const toggleDot = document.getElementById('theme-toggle-dot');
    const themeIcon = document.getElementById('theme-icon');
    
    if (theme === 'light') {
        if (toggleDot) toggleDot.classList.add('translate-x-5');
        if (themeIcon) themeIcon.textContent = '☀️';
    } else {
        if (toggleDot) toggleDot.classList.remove('translate-x-5');
        if (themeIcon) themeIcon.textContent = '🌙';
    }
}

function toggleTheme() {
    currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
    localStorage.setItem('core_catalog_theme', currentTheme);
    applyTheme(currentTheme);
}

// Aplicar tema inicial al cargar el script o el DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        applyTheme(currentTheme);
    });
} else {
    applyTheme(currentTheme);
}
