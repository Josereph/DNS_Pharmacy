<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña | DNS Pharmacy</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="../assets/css/recuperar.css">
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
                    Recupera el acceso a tu cuenta ingresando tu correo registrado.
                </p>
            </div>
        </div>

        <!-- Panel derecho -->
        <div class="login-right">

            <!-- PASO 1: Verificar correo -->
            <div id="paso1">
                <div class="form-header">
                    <span class="accent accent-purple"></span>
                    <h2>Recuperar contraseña</h2>
                    <p>Ingresa tu correo para continuar</p>
                </div>

                <div id="alertaPaso1" class="alert" style="display:none"></div>

                <form id="formPaso1" novalidate>
                    <div class="form-group">
                        <label for="correo_recuperar">Correo electrónico</label>
                        <div class="input-wrapper" id="wrapCorreo">
                            <span class="input-icon">@</span>
                            <input type="email" id="correo_recuperar"
                                   placeholder="ejemplo@correo.com">
                        </div>
                        <small class="error-message" id="errCorreo"></small>
                    </div>

                    <button type="submit" class="btn-login">
                        Verificar correo <span>→</span>
                    </button>
                </form>

                <div class="recuperar-back">
                    <a href="Login.php">← Volver al inicio de sesión</a>
                </div>
            </div>

            <!-- PASO 2: Nueva contraseña -->
            <div id="paso2" style="display:none">
                <div class="form-header">
                    <span class="accent accent-green"></span>
                    <h2>Nueva contraseña</h2>
                    <p>Correo verificado: <strong id="correoVerificado"></strong></p>
                </div>

                <div id="alertaPaso2" class="alert" style="display:none"></div>

                <form id="formPaso2" novalidate>
                    <input type="hidden" id="correo_confirmado">

                    <div class="form-group">
                        <label for="nueva_pass">Nueva contraseña</label>
                        <div class="input-wrapper" id="wrapNueva">
                            <span class="input-icon">•</span>
                            <input type="password" id="nueva_pass" placeholder="Mínimo 8 caracteres">
                            <button type="button" class="toggle-password" onclick="togglePass('nueva_pass', this)">Ver</button>
                        </div>
                        <small class="error-message" id="errNueva"></small>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_pass">Confirmar contraseña</label>
                        <div class="input-wrapper" id="wrapConfirmar">
                            <span class="input-icon">•</span>
                            <input type="password" id="confirmar_pass" placeholder="Repite la nueva contraseña">
                            <button type="button" class="toggle-password" onclick="togglePass('confirmar_pass', this)">Ver</button>
                        </div>
                        <small class="error-message" id="errConfirmar"></small>
                    </div>

                    <button type="submit" class="btn-login">
                        Guardar contraseña <span>→</span>
                    </button>
                </form>

                <div class="recuperar-back">
                    <a href="#" onclick="volverPaso1()">← Cambiar correo</a>
                </div>
            </div>

            <!-- PASO 3: Éxito -->
            <div id="paso3" style="display:none">
                <div class="recuperar-exito">
                    <div class="exito-icon">✓</div>
                    <h2>¡Contraseña actualizada!</h2>
                    <p>Tu contraseña ha sido cambiada correctamente.</p>
                    <a href="Login.php" class="btn-login" style="display:block;text-align:center;text-decoration:none;margin-top:24px">
                        Ir al inicio de sesión <span>→</span>
                    </a>
                </div>
            </div>

        </div>
    </section>
</main>

<script src="../assets/js/recuperar.js"></script>
</body>
</html>