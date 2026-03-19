document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    function setError(input, errorElement, message) {
        const wrapper = input.closest('.input-wrapper');
        wrapper.classList.add('input-error');
        errorElement.textContent = message;
    }

    function clearError(input, errorElement) {
        const wrapper = input.closest('.input-wrapper');
        wrapper.classList.remove('input-error');
        errorElement.textContent = '';
    }

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

    email.addEventListener('input', validateEmail);
    password.addEventListener('input', validatePassword);

    togglePassword.addEventListener('click', () => {
        const isPassword = password.getAttribute('type') === 'password';
        password.setAttribute('type', isPassword ? 'text' : 'password');
        togglePassword.textContent = isPassword ? 'Ocultar' : 'Ver';
    });

    form.addEventListener('submit', (e) => {
        const emailValid = validateEmail();
        const passwordValid = validatePassword();

        if (!emailValid || !passwordValid) {
            e.preventDefault();
        }
    });
});