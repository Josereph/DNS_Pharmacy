<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: /DNS_Pharmacy/index.php');
    exit;
}

require_once '../config/database.php';

$email         = '';
$errors        = ['email' => '', 'password' => ''];
$error_general = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

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

    if (empty($errors['email']) && empty($errors['password'])) {
        $conn = conectar();
        $stmt = $conn->prepare("
            SELECT u.id_usuario, u.nombre, u.apellido,
                   u.password_hash, u.estado, r.nombre AS rol
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.correo = ? LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (!$usuario) {
            $error_general = 'Correo o contraseña incorrectos.';
        } elseif (!$usuario['estado']) {
            $error_general = 'Tu cuenta está desactivada. Contacta al administrador.';
        } else {
            $passwordValida = false;
            $eraTextoPlano  = false;

            if (password_verify($password, $usuario['password_hash'])) {
                $passwordValida = true;
            } elseif ($password === $usuario['password_hash']) {
                $passwordValida = true;
                $eraTextoPlano  = true;
            }

            if (!$passwordValida) {
                $error_general = 'Correo o contraseña incorrectos.';
            } else {
                $_SESSION['usuario_id']     = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
                $_SESSION['usuario_rol']    = $usuario['rol'];

                $conn2 = conectar();
                if ($eraTextoPlano) {
                    $nuevoHash = password_hash($password, PASSWORD_BCRYPT);
                    $sh = $conn2->prepare("UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?");
                    $sh->bind_param('si', $nuevoHash, $usuario['id_usuario']);
                    $sh->execute(); $sh->close();
                }
                $sa = $conn2->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?");
                $sa->bind_param('i', $usuario['id_usuario']);
                $sa->execute(); $sa->close();
                $conn2->close();

                // Redirigir a pantalla de bienvenida
                header('Location: /DNS_Pharmacy/views/bienvenida.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | DNS Pharmacy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<div class="login-page">

    <!-- Panel izquierdo -->
    <div class="login-left">
        <div class="login-left-content">
            <div class="login-logo-circle">
                <img src="../assets/img/logo-dns.png" alt="DNS Pharmacy">
            </div>
            <h1 class="login-brand">DNS Pharmacy</h1>
            <p class="login-brand-sub">Drug Network Supply</p>
            <div class="login-divider"></div>
            <p class="login-brand-desc">
                Accede al sistema de manera segura para administrar ventas,
                inventario y operaciones internas.
            </p>
            <div class="login-features">
                <div class="login-feature"><i class="bi bi-check-circle-fill"></i> Gestión de ventas POS</div>
                <div class="login-feature"><i class="bi bi-check-circle-fill"></i> Control de inventario</div>
                <div class="login-feature"><i class="bi bi-check-circle-fill"></i> Reportes en tiempo real</div>
            </div>
        </div>
    </div>

    <!-- Panel derecho -->
    <div class="login-right">
        <div class="login-form-wrap">

            <div class="login-form-header">
                <img src="../assets/img/DNS_LOGO.png" alt="DNS" class="login-form-logo">
                <h2>Bienvenido de vuelta</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

            <?php if ($error_general): ?>
                <div class="alert-error" id="alertaGeneral">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <?= htmlspecialchars($error_general) ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="" novalidate>

                <div class="field-group">
                    <label>Correo electrónico</label>
                    <div class="field-wrap <?= $errors['email'] ? 'field-error' : '' ?>">
                        <i class="bi bi-envelope field-icon"></i>
                        <input type="email" id="email" name="email"
                               placeholder="ejemplo@correo.com"
                               value="<?= htmlspecialchars($email) ?>"
                               autocomplete="email">
                    </div>
                    <span class="field-msg" id="emailError"><?= htmlspecialchars($errors['email']) ?></span>
                </div>

                <div class="field-group">
                    <label>Contraseña</label>
                    <div class="field-wrap <?= $errors['password'] ? 'field-error' : '' ?>">
                        <i class="bi bi-lock field-icon"></i>
                        <input type="password" id="password" name="password"
                               placeholder="Ingresa tu contraseña"
                               autocomplete="current-password">
                        <button type="button" class="field-toggle" id="togglePassword">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    <span class="field-msg" id="passwordError"><?= htmlspecialchars($errors['password']) ?></span>
                </div>

                <div class="login-options">
                    <label class="remember-check">
                        <input type="checkbox" name="remember">
                        <span>Recordarme</span>
                    </label>
                    <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    <span id="btnText">Iniciar sesión</span>
                    <i class="bi bi-arrow-right-circle-fill"></i>
                </button>

            </form>

            <p class="login-footer-note">DNS Pharmacy &copy; <?= date('Y') ?> · Sistema POS v1.0</p>

        </div>
    </div>

</div>

<script src="../assets/js/login.js"></script>
</body>
</html>