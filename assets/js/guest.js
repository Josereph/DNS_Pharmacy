/**
 * DNS PHARMACY - GUEST VIEW
 * Solo informativo - Navegación y UI
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ==================== ELEMENTOS DOM ====================
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // ==================== FUNCIONES ====================
    
    /**
     * Smooth scroll para links de navegación
     */
    function smoothScroll(targetId) {
        const target = document.querySelector(targetId);
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
    
    /**
     * Actualiza el link activo según el scroll
     */
    function updateActiveLink() {
        const sections = ['inicio', 'servicios', 'nosotros'];
        let current = '';
        
        for (const section of sections) {
            const element = document.getElementById(section);
            if (element) {
                const rect = element.getBoundingClientRect();
                if (rect.top <= 100 && rect.bottom >= 100) {
                    current = section;
                    break;
                }
            }
        }
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            if (href === `#${current}`) {
                link.classList.add('active');
            }
        });
    }
    
    /**
     * Menú toggle para móvil
     */
    function initMobileMenu() {
        if (menuToggle) {
            menuToggle.addEventListener('click', function() {
                const nav = document.querySelector('.main-nav');
                nav.classList.toggle('active');
                this.classList.toggle('active');
                
                // Forzar display para móvil
                if (nav.classList.contains('active')) {
                    nav.style.display = 'block';
                } else {
                    nav.style.display = '';
                }
            });
        }
    }
    
    /**
     * Inicializa los eventos de navegación
     */
    function initNavigation() {
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                smoothScroll(targetId);
                
                // Cerrar menú móvil si está abierto
                if (menuToggle && menuToggle.classList.contains('active')) {
                    menuToggle.click();
                }
            });
        });
    }
    
    /**
     * Observador de scroll para header
     */
    function initScrollObserver() {
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.guest-header');
            if (header) {
                if (window.scrollY > 50) {
                    header.style.boxShadow = '0 4px 20px rgba(132, 20, 128, 0.12)';
                } else {
                    header.style.boxShadow = '0 2px 20px rgba(132, 20, 128, 0.08)';
                }
            }
            updateActiveLink();
        });
    }
    
    // ==================== INICIALIZACIÓN ====================
    initNavigation();
    initMobileMenu();
    initScrollObserver();
    
    // Actualizar link activo al inicio
    setTimeout(updateActiveLink, 100);
    
    console.log('DNS Pharmacy - Vista de Cliente cargada correctamente');
});