<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /views/Login.php');
    exit;
}

$nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$rol    = $_SESSION['usuario_rol']    ?? '';
$ini    = strtoupper(substr($nombre, 0, 1));

// Destino según rol
$destino = $rol === 'Administrador'
    ? $base_url . '/index.php'
    : $base_url . '/views/Pos.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido | DNS Pharmacy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            background: linear-gradient(135deg, #841480 0%, #5a0e58 50%, #70ab32 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            overflow: hidden;
        }

        /* ── Partículas de fondo ── */
        .particles {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            animation: floatUp linear infinite;
        }

        @keyframes floatUp {
            0%   { transform: translateY(100vh) scale(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(-100px) scale(1); opacity: 0; }
        }

        /* ── Contenedor principal ── */
        .welcome-wrap {
            position: relative;
            z-index: 10;
            text-align: center;
            color: #ffffff;
            padding: 40px;
        }

        /* ── Logo ── */
        .welcome-logo {
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            margin: 0 auto 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.3);
            opacity: 0;
            transform: scale(0.5);
            animation: popIn 0.6s cubic-bezier(0.34,1.56,0.64,1) 0.2s forwards;
        }

        .welcome-logo img { width: 80px; object-fit: contain; }

        @keyframes popIn {
            to { opacity:1; transform:scale(1); }
        }

        /* ── Texto DNS Pharmacy ── */
        .welcome-brand {
            font-size: 14px;
            letter-spacing: 3px;
            text-transform: uppercase;
            opacity: 0;
            color: rgba(255,255,255,0.7);
            animation: fadeInUp 0.5s ease 0.7s forwards;
            margin-bottom: 12px;
        }

        /* ── "Bienvenido" ── */
        .welcome-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 700;
            opacity: 0;
            animation: fadeInUp 0.6s ease 0.9s forwards;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        /* ── Nombre del usuario ── */
        .welcome-name {
            font-size: clamp(1.4rem, 3vw, 2rem);
            font-weight: 300;
            opacity: 0;
            animation: fadeInUp 0.6s ease 1.1s forwards;
            color: #c8f08a;
            margin-bottom: 32px;
        }

        /* ── Avatar con iniciales ── */
        .welcome-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            border: 3px solid rgba(255,255,255,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            margin: 0 auto 32px;
            opacity: 0;
            animation: fadeInUp 0.5s ease 1.3s forwards;
            backdrop-filter: blur(8px);
        }

        /* ── Rol badge ── */
        .welcome-role {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 99px;
            padding: 6px 18px;
            font-size: 13px;
            backdrop-filter: blur(8px);
            opacity: 0;
            animation: fadeInUp 0.5s ease 1.5s forwards;
            margin-bottom: 40px;
        }

        /* ── Barra de progreso ── */
        .progress-wrap {
            width: 280px;
            margin: 0 auto;
            opacity: 0;
            animation: fadeInUp 0.5s ease 1.7s forwards;
        }

        .progress-label {
            font-size: 12px;
            color: rgba(255,255,255,0.6);
            margin-bottom: 10px;
            letter-spacing: .5px;
        }

        .progress-bar-bg {
            height: 4px;
            background: rgba(255,255,255,0.2);
            border-radius: 99px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #70ab32, #c8f08a);
            border-radius: 99px;
            width: 0%;
            animation: fillBar 2.2s ease 2s forwards;
        }

        @keyframes fillBar {
            0%   { width: 0%; }
            100% { width: 100%; }
        }

        /* ── Animaciones generales ── */
        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* ── Círculos decorativos ── */
        .deco-circle {
            position: fixed;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.08);
            animation: pulse 4s ease-in-out infinite;
        }

        .deco-circle:nth-child(1) { width:400px; height:400px; top:-100px; right:-100px; animation-delay:0s; }
        .deco-circle:nth-child(2) { width:300px; height:300px; bottom:-80px; left:-80px; animation-delay:1s; }
        .deco-circle:nth-child(3) { width:200px; height:200px; top:40%; left:5%; animation-delay:2s; }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity:0.5; }
            50%       { transform: scale(1.05); opacity:1; }
        }
    </style>
</head>
<body>

<!-- Círculos decorativos -->
<div class="deco-circle"></div>
<div class="deco-circle"></div>
<div class="deco-circle"></div>

<!-- Partículas flotantes -->
<div class="particles" id="particles"></div>

<!-- Contenido central -->
<div class="welcome-wrap">

    <div class="welcome-logo">
        <img src="../assets/img/logo-dns.png" alt="DNS Pharmacy">
    </div>

    <div class="welcome-brand">DNS Pharmacy · Sistema POS</div>

    <div class="welcome-title">¡Bienvenido!</div>

    <div class="welcome-avatar"><?= htmlspecialchars($ini) ?></div>

    <div class="welcome-name"><?= htmlspecialchars($nombre) ?></div>

    <div class="welcome-role">
        <i class="bi bi-<?= $rol === 'Administrador' ? 'shield-check' : 'person-badge' ?>"></i>
        <?= htmlspecialchars($rol) ?>
    </div>

    <div class="progress-wrap">
        <div class="progress-label">Cargando el sistema...</div>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill"></div>
        </div>
    </div>

</div>

<script>
// Generar partículas flotantes
(function() {
    var container = document.getElementById('particles');
    for (var i = 0; i < 18; i++) {
        var p = document.createElement('div');
        p.className = 'particle';
        var size = Math.random() * 40 + 10;
        p.style.cssText = [
            'width:'    + size + 'px',
            'height:'   + size + 'px',
            'left:'     + Math.random() * 100 + '%',
            'bottom:'   + (-size) + 'px',
            'animation-duration:' + (Math.random() * 8 + 6) + 's',
            'animation-delay:'    + (Math.random() * 5) + 's'
        ].join(';');
        container.appendChild(p);
    }
})();

// Redirigir después de la animación
setTimeout(function() {
    window.location.href = '<?= $destino ?>';
}, 4200);
</script>

</body>
</html>