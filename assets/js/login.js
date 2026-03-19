document.addEventListener('DOMContentLoaded', () => {
    const form          = document.getElementById('loginForm');
    const email         = document.getElementById('email');
    const password      = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const emailError    = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    /* ── Helpers ── */
    function setError(input, errorElement, message) {
        input.closest('.input-wrapper').classList.add('input-error');
        errorElement.textContent = message;
    }

    function clearError(input, errorElement) {
        input.closest('.input-wrapper').classList.remove('input-error');
        errorElement.textContent = '';
    }

    function limpiarAlertaGeneral() {
        const alerta = document.getElementById('alertaGeneral');
        if (alerta) alerta.remove();
    }

    /* ── Validaciones ── */
    function validateEmail() {
        const value = email.value.trim();
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (value === '') {
            setError(email, emailError, 'El correo electrónico es obligatorio.');
            return false;
        }
        if (!regex.test(value)) {
            setError(email, emailError, 'Ingresa un correo electrónico válido.');
            return false;
        }

        clearError(email, emailError);
        return true;
    }

    function validatePassword() {
        const value = password.value.trim();

        if (value === '') {
            setError(password, passwordError, 'La contraseña es obligatoria.');
            return false;
        }
        if (value.length < 6) {
            setError(password, passwordError, 'La contraseña debe tener al menos 6 caracteres.');
            return false;
        }

        clearError(password, passwordError);
        return true;
    }

    /* ── Eventos en tiempo real ── */
    email.addEventListener('input', () => {
        validateEmail();
        limpiarAlertaGeneral();
    });

    password.addEventListener('input', () => {
        validatePassword();
        limpiarAlertaGeneral();
    });

    /* ── Mostrar / ocultar contraseña ── */
    togglePassword.addEventListener('click', () => {
        const esPassword = password.getAttribute('type') === 'password';
        password.setAttribute('type', esPassword ? 'text' : 'password');
        togglePassword.textContent = esPassword ? 'Ocultar' : 'Ver';
    });

    /* ── Submit ── */
    form.addEventListener('submit', (e) => {
        const emailValido    = validateEmail();
        const passwordValido = validatePassword();

        if (!emailValido || !passwordValido) {
            e.preventDefault();
        }
    });
});