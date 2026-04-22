<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña | DNS Pharmacy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/recuperar.css">
</head>
<body>

<div class="login-page">

    <!-- Panel izquierdo - Mismo que login -->
    <div class="login-left">
        <div class="login-left-content">
            <div class="login-logo-circle">
                <img src="../assets/img/logo-dns.png" alt="DNS Pharmacy">
            </div>
            <h1 class="login-brand">DNS Pharmacy</h1>
            <p class="login-brand-sub">Drug Network Supply</p>
            <div class="login-divider"></div>
            <p class="login-brand-desc">
                Recupera el acceso a tu cuenta de manera segura.
            </p>
            <div class="login-features">
                <div class="login-feature"><i class="bi bi-shield-lock-fill"></i> Recuperación segura</div>
                <div class="login-feature"><i class="bi bi-envelope-paper-fill"></i> Verificación por correo</div>
                <div class="login-feature"><i class="bi bi-key-fill"></i> Restablece tu contraseña</div>
            </div>
        </div>
    </div>

    <!-- Panel derecho - Pasos de recuperación -->
    <div class="login-right">
        <div class="login-form-wrap">

            <!-- PASO 1: Verificar correo -->
            <div id="paso1">
                <div class="login-form-header">
                    <img src="../assets/img/DNS_LOGO.png" alt="DNS" class="login-form-logo">
                    <h2>Recuperar contraseña</h2>
                    <p>Ingresa tu correo para continuar</p>
                </div>

                <div id="alertaPaso1" class="alert-error" style="display:none">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span id="mensajePaso1"></span>
                </div>

                <form id="formPaso1" novalidate>
                    <div class="field-group">
                        <label>Correo electrónico</label>
                        <div class="field-wrap" id="wrapCorreo">
                            <i class="bi bi-envelope field-icon"></i>
                            <input type="email" id="correo_recuperar"
                                   placeholder="ejemplo@correo.com"
                                   autocomplete="email">
                        </div>
                        <span class="field-msg" id="errCorreo"></span>
                    </div>

                    <button type="submit" class="btn-login">
                        <span>Verificar correo</span>
                        <i class="bi bi-arrow-right-circle-fill"></i>
                    </button>
                </form>

                <p class="login-footer-note">
                    <a href="Login.php" style="color: var(--purple); text-decoration: none;">
                        ← Volver al inicio de sesión
                    </a>
                </p>
            </div>

            <!-- PASO 2: Nueva contraseña -->
            <div id="paso2" style="display:none">
                <div class="login-form-header">
                    <img src="../assets/img/DNS_LOGO.png" alt="DNS" class="login-form-logo">
                    <h2>Nueva contraseña</h2>
                    <p>Correo verificado: <strong id="correoVerificado" style="color: var(--green);"></strong></p>
                </div>

                <div id="alertaPaso2" class="alert-error" style="display:none">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span id="mensajePaso2"></span>
                </div>

                <form id="formPaso2" novalidate>
                    <input type="hidden" id="correo_confirmado">

                    <div class="field-group">
                        <label>Nueva contraseña</label>
                        <div class="field-wrap" id="wrapNueva">
                            <i class="bi bi-lock field-icon"></i>
                            <input type="password" id="nueva_pass" 
                                   placeholder="Mínimo 8 caracteres">
                            <button type="button" class="field-toggle" onclick="togglePassword('nueva_pass', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <span class="field-msg" id="errNueva"></span>
                    </div>

                    <div class="field-group">
                        <label>Confirmar contraseña</label>
                        <div class="field-wrap" id="wrapConfirmar">
                            <i class="bi bi-lock field-icon"></i>
                            <input type="password" id="confirmar_pass" 
                                   placeholder="Repite la nueva contraseña">
                            <button type="button" class="field-toggle" onclick="togglePassword('confirmar_pass', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <span class="field-msg" id="errConfirmar"></span>
                    </div>

                    <button type="submit" class="btn-login">
                        <span>Guardar contraseña</span>
                        <i class="bi bi-check-circle-fill"></i>
                    </button>
                </form>

                <p class="login-footer-note">
                    <a href="#" onclick="volverPaso1(); return false;" style="color: var(--purple); text-decoration: none;">
                        ← Cambiar correo
                    </a>
                </p>
            </div>

            <!-- PASO 3: Éxito -->
            <div id="paso3" style="display:none">
                <div class="recuperar-exito">
                    <div class="exito-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <h2>¡Contraseña actualizada!</h2>
                    <p>Tu contraseña ha sido cambiada correctamente.</p>
                    <a href="Login.php" class="btn-login" style="display:flex; justify-content:center; text-decoration:none; margin-top:24px">
                        <span>Ir al inicio de sesión</span>
                        <i class="bi bi-arrow-right-circle-fill"></i>
                    </a>
                </div>
            </div>

            <p class="login-footer-note">DNS Pharmacy &copy; <?= date('Y') ?> · Sistema POS v1.0</p>

        </div>
    </div>

</div>

<script src="../assets/js/recuperar.js"></script>
</body>
</html>