<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {

    /* ── Paso 1: verificar que el correo existe ── */
    case 'verificar':
        $correo = trim($_POST['correo'] ?? '');

        if (!$correo || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => true, 'mensaje' => 'Correo no válido.']);
            break;
        }

        $conn = conectar();
        $stmt = $conn->prepare("SELECT id_usuario, estado FROM usuarios WHERE correo = ? LIMIT 1");
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (!$row) {
            echo json_encode(['error' => true, 'mensaje' => 'No existe una cuenta con ese correo.']);
            break;
        }

        if (!$row['estado']) {
            echo json_encode(['error' => true, 'mensaje' => 'Esta cuenta está desactivada. Contacta al administrador.']);
            break;
        }

        echo json_encode(['error' => false, 'mensaje' => 'Correo verificado.']);
        break;

    /* ── Paso 2: actualizar la contraseña ── */
    case 'actualizar':
        $correo   = trim($_POST['correo']   ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!$correo || !$password) {
            echo json_encode(['error' => true, 'mensaje' => 'Faltan datos.']);
            break;
        }

        if (strlen($password) < 8) {
            echo json_encode(['error' => true, 'mensaje' => 'La contraseña debe tener al menos 8 caracteres.']);
            break;
        }

        $conn = conectar();

        $stmtCheck = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = ? AND estado = 1 LIMIT 1");
        $stmtCheck->bind_param('s', $correo);
        $stmtCheck->execute();
        $existe = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();

        if (!$existe) {
            $conn->close();
            echo json_encode(['error' => true, 'mensaje' => 'Correo no válido.']);
            break;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE usuarios SET password_hash = ? WHERE correo = ?");
        $stmt->bind_param('ss', $hash, $correo);
        $stmt->execute();
        $ok = $stmt->affected_rows > 0;
        $stmt->close();
        $conn->close();

        echo json_encode([
            'error'   => !$ok,
            'mensaje' => $ok ? 'Contraseña actualizada.' : 'Error al actualizar.'
        ]);
        break;

    default:
        echo json_encode(['error' => true, 'mensaje' => 'Acción no válida.']);
        break;
}