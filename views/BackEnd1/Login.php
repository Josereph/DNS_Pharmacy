<?php
$email = '';
$password = '';
$errors = [
    'email' => '',
    'password' => ''
];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validaciones básicas
    if ($email === '') {
        $errors['email'] = 'El correo electrónico es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Ingresa un correo electrónico válido.';
    }

    if ($password === '') {
        $errors['password'] = 'La contraseña es obligatoria.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'La contraseña debe tener al menos 6 caracteres.';
    }

    // Si no hay errores, solo simulamos validación correcta
    if (empty($errors['email']) && empty($errors['password'])) {
        $success = 'Validación correcta. Pendiente conectar usuarios y roles con base de datos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | DNS Pharmacy</title>
    <link rel="stylesheet" href="assets/css/BackEnd1/login.css">
</head>
<body>

    <main class="login-page">
        <section class="login-card">

            <!-- Panel izquierdo -->
            <div class="login-left">
                <div class="overlay"></div>

                <div class="brand-content">
                    <div class="brand-logo-box">
                        <img src="assets/img/logo-dns.png" alt="DNS Pharmacy Logo" class="brand-logo">
                    </div>

                    <h1>DNS Pharmacy</h1>
                    <p class="brand-subtitle">Sistema POS para gestión de farmacia</p>

                    <div class="brand-divider"></div>

                    <p class="brand-text">
                        Accede al sistema de manera segura para administrar ventas,
                        inventario y operaciones internas.
                    </p>
                </div>
            </div>

            <!-- Panel derecho -->
            <div class="login-right">
                <div class="form-header">
                    <span class="accent accent-purple"></span>
                    <h2>Bienvenido</h2>
                    <p>Ingresa tus credenciales para iniciar sesión</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <form id="loginForm" class="login-form" method="POST" action="" novalidate>

                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <div class="input-wrapper <?= $errors['email'] ? 'input-error' : '' ?>">
                            <span class="input-icon">@</span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="ejemplo@correo.com"
                                value="<?= htmlspecialchars($email) ?>"
                            >
                        </div>
                        <small class="error-message" id="emailError">
                            <?= htmlspecialchars($errors['email']) ?>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <div class="input-wrapper <?= $errors['password'] ? 'input-error' : '' ?>">
                            <span class="input-icon">•</span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Ingresa tu contraseña"
                            >
                            <button type="button" class="toggle-password" id="togglePassword">
                                Ver
                            </button>
                        </div>
                        <small class="error-message" id="passwordError">
                            <?= htmlspecialchars($errors['password']) ?>
                        </small>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Recordarme</span>
                        </label>

                        <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        Iniciar sesión <span>→</span>
                    </button>

                    <p class="login-note">
                        Esta versión solo valida formato y campos básicos.
                        La autenticación real y roles serán integrados después.
                    </p>
                </form>
            </div>
        </section>
    </main>

    <script src="assets/js/BackEnd1/login.js"></script>
</body>
</html>