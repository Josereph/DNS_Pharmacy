/**
 * DNS PHARMACY - GUEST VIEW
 * Solo informativo - Navegación, UI y ChatBot DNSBot
 */

document.addEventListener('DOMContentLoaded', function () {

    // ==================== ELEMENTOS DOM ====================
    const menuToggle = document.getElementById('menuToggle');
    const navLinks   = document.querySelectorAll('.nav-link');

    // ==================== NAVEGACIÓN ====================

    function smoothScroll(targetId) {
        const target = document.querySelector(targetId);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

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
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    }

    function initMobileMenu() {
        if (menuToggle) {
            menuToggle.addEventListener('click', function () {
                const nav = document.querySelector('.main-nav');
                nav.classList.toggle('active');
                this.classList.toggle('active');
                nav.style.display = nav.classList.contains('active') ? 'block' : '';
            });
        }
    }

    function initNavigation() {
        navLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                smoothScroll(this.getAttribute('href'));
                if (menuToggle && menuToggle.classList.contains('active')) {
                    menuToggle.click();
                }
            });
        });
    }

    function initScrollObserver() {
        window.addEventListener('scroll', function () {
            const header = document.querySelector('.guest-header');
            if (header) {
                header.style.boxShadow = window.scrollY > 50
                    ? '0 4px 20px rgba(132, 20, 128, 0.12)'
                    : '0 2px 20px rgba(132, 20, 128, 0.08)';
            }
            updateActiveLink();
        });
    }

    initNavigation();
    initMobileMenu();
    initScrollObserver();
    setTimeout(updateActiveLink, 100);

    console.log('DNS Pharmacy - Vista de Cliente cargada correctamente');

}); // fin DOMContentLoaded

/* =====================================================
   CHATBOT DNSBOT
   ===================================================== */

(function () {
    const btn      = document.getElementById('dns-chat-btn');
    const chatWin  = document.getElementById('dns-chat-window');
    const closeBtn = document.getElementById('dns-chat-close');
    const input    = document.getElementById('dns-chat-input');
    const sendBtn  = document.getElementById('dns-chat-send');
    const messages = document.getElementById('dns-chat-messages');
    const chips    = document.getElementById('dns-chat-chips');

    // Ruta al controlador PHP
    const API_URL = 'http://localhost/DNS_Pharmacy/controllers/chatbot_controller.php';

    // ── Abrir / cerrar ──────────────────────────────────────────────────────
    btn.addEventListener('click', () => {
        chatWin.classList.toggle('dns-open');
        if (chatWin.classList.contains('dns-open')) input.focus();
    });
    closeBtn.addEventListener('click', () => chatWin.classList.remove('dns-open'));

    // ── Agregar burbuja ─────────────────────────────────────────────────────
    function addMsg(html, role) {
        const div = document.createElement('div');
        div.className = 'dns-msg dns-' + role;
        div.innerHTML = html;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    // ── Indicador de escritura ──────────────────────────────────────────────
    function showTyping() {
        const t = document.createElement('div');
        t.className = 'dns-typing';
        t.id = 'dns-typing';
        t.innerHTML = '<span></span><span></span><span></span>';
        messages.appendChild(t);
        messages.scrollTop = messages.scrollHeight;
    }
    function hideTyping() {
        const t = document.getElementById('dns-typing');
        if (t) t.remove();
    }

    // ── Escape HTML (seguridad) ─────────────────────────────────────────────
    function escHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Enviar mensaje ──────────────────────────────────────────────────────
    async function sendMsg(texto) {
        const msg = (texto || input.value).trim();
        if (!msg) return;

        if (chips) chips.style.display = 'none';

        addMsg(escHtml(msg), 'user');
        input.value = '';
        sendBtn.disabled = true;
        showTyping();

        try {
            const res  = await fetch(API_URL, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ mensaje: msg })
            });
            const data = await res.json();
            hideTyping();

            if (data.respuesta) {
                const html = escHtml(data.respuesta).replace(/\n/g, '<br>');
                addMsg(html, 'bot');
            } else {
                addMsg('Lo siento, ocurrió un error. Intenta de nuevo. 😔', 'bot');
            }
        } catch (err) {
            hideTyping();
            addMsg('No pude conectarme. Verifica tu conexión e intenta de nuevo.', 'bot');
        }

        sendBtn.disabled = false;
        input.focus();
    }

    // ── Chips de sugerencias ────────────────────────────────────────────────
    window.dnsChip = function (el) { sendMsg(el.textContent.trim()); };

    // ── Eventos ─────────────────────────────────────────────────────────────
    sendBtn.addEventListener('click', () => sendMsg());
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMsg(); }
    });

})();