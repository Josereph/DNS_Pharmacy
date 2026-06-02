/* =====================
   LOGIN.JS - DNS Pharmacy
   ===================== */

document.addEventListener('DOMContentLoaded', function() {

    var form          = document.getElementById('loginForm');
    var email         = document.getElementById('email');
    var password      = document.getElementById('password');
    var toggleBtn     = document.getElementById('togglePassword');
    var toggleIcon    = document.getElementById('toggleIcon');
    var emailError    = document.getElementById('emailError');
    var passwordError = document.getElementById('passwordError');

    /* ── Helpers ── */
    function setError(wrap, msgEl, msg) {
        wrap.classList.add('field-error');
        msgEl.textContent = msg;
    }

    function clearError(wrap, msgEl) {
        wrap.classList.remove('field-error');
        msgEl.textContent = '';
    }

    function getWrap(input) { return input.closest('.field-wrap'); }

    function limpiarAlerta() {
        var al = document.getElementById('alertaGeneral');
        if (al) al.remove();
    }

    /* ── Validaciones ── */
    function validateEmail() {
        var value = email.value.trim();
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!value) { setError(getWrap(email), emailError, 'El correo electrónico es obligatorio.'); return false; }
        if (!regex.test(value)) { setError(getWrap(email), emailError, 'Ingresa un correo electrónico válido.'); return false; }
        clearError(getWrap(email), emailError);
        return true;
    }

    function validatePassword() {
        var value = password.value.trim();
        if (!value) { setError(getWrap(password), passwordError, 'La contraseña es obligatoria.'); return false; }
        if (value.length < 6) { setError(getWrap(password), passwordError, 'La contraseña debe tener al menos 6 caracteres.'); return false; }
        clearError(getWrap(password), passwordError);
        return true;
    }

    /* ── Eventos tiempo real ── */
    email.addEventListener('input', function() { validateEmail(); limpiarAlerta(); });
    password.addEventListener('input', function() { validatePassword(); limpiarAlerta(); });

    /* ── Toggle contraseña ── */
    toggleBtn.addEventListener('click', function() {
        var esPass = password.getAttribute('type') === 'password';
        password.setAttribute('type', esPass ? 'text' : 'password');
        toggleIcon.className = esPass ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    /* ── Submit ── */
    form.addEventListener('submit', function(e) {
        var emailOk    = validateEmail();
        var passwordOk = validatePassword();
        if (!emailOk || !passwordOk) { e.preventDefault(); return; }

        // Feedback visual en el botón
        var btn  = document.getElementById('btnLogin');
        var text = document.getElementById('btnText');
        if (btn && text) {
            text.textContent = 'Verificando...';
            btn.disabled     = true;
            btn.style.opacity = '0.8';
        }
    });

});