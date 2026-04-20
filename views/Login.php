<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
session_start();

// Si ya hay sesión activa redirigir al dashboard
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

    // ── Validaciones de formato ──────────────────────────
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

    // ── Verificar en BD si el formato es correcto ────────
    if (empty($errors['email']) && empty($errors['password'])) {
        $conn = conectar();

        $stmt = $conn->prepare("
            SELECT u.id_usuario, u.nombre, u.apellido,
                   u.password_hash, u.estado,
                   r.nombre AS rol
            FROM usuarios u
            INNER JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.correo = ?
            LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario   = $resultado->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (!$usuario) {
            $error_general = 'Correo o contraseña incorrectos.';

        } elseif (!$usuario['estado']) {
            $error_general = 'Tu cuenta está desactivada. Contacta al administrador.';

        } else {
            // ── Soporte hash bcrypt Y texto plano ────────
            $passwordValida  = false;
            $eraTextoPlano   = false;

            if (password_verify($password, $usuario['password_hash'])) {
                $passwordValida = true;
            } elseif ($password === $usuario['password_hash']) {
                $passwordValida = true;
                $eraTextoPlano  = true;
            }

            if (!$passwordValida) {
                $error_general = 'Correo o contraseña incorrectos.';

            } else {
                // ── Login exitoso ─────────────────────────
                $_SESSION['usuario_id']     = $usuario['id_usuario'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
                $_SESSION['usuario_rol']    = $usuario['rol'];

                $conn2 = conectar();

                // Si era texto plano, hashear automáticamente
                if ($eraTextoPlano) {
                    $nuevoHash = password_hash($password, PASSWORD_BCRYPT);
                    $stmtHash  = $conn2->prepare(
                        "UPDATE usuarios SET password_hash = ? WHERE id_usuario = ?"
                    );
                    $stmtHash->bind_param('si', $nuevoHash, $usuario['id_usuario']);
                    $stmtHash->execute();
                    $stmtHash->close();
                }

                // Actualizar último acceso
                $stmtAcceso = $conn2->prepare(
                    "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?"
                );
                $stmtAcceso->bind_param('i', $usuario['id_usuario']);
                $stmtAcceso->execute();
                $stmtAcceso->close();
                $conn2->close();

                // Redirigir según rol
                if ($usuario['rol'] === 'Administrador') {
                    header('Location: /DNS_Pharmacy/index.php');
                } else {
                    header('Location: /DNS_Pharmacy/index.php');
                }
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
    <title>Login | DNS Pharmacy</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<main class="login-page">
    <section class="login-card">

        <!-- Panel izquierdo -->
        <div class="login-left">
            <div class="overlay"></div>
            <div class="brand-content">
                <div class="brand-logo-box">
                    <img src="../assets/img/logo-dns.png" alt="DNS Pharmacy Logo" class="brand-logo">
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

            <?php if ($error_general): ?>
                <div class="alert alert-danger" id="alertaGeneral">
                    <?= htmlspecialchars($error_general) ?>
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
                        <button type="button" class="toggle-password" id="togglePassword">Ver</button>
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

            </form>
        </div>

    </section>
</main>

<script src="../assets/js/login.js"></script>
</body>
</html>