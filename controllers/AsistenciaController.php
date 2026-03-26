<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Sesión no iniciada']);
    exit;
}

require_once __DIR__ . '/../models/AsistenciaModel.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Para Endroid QR

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$model = new AsistenciaModel();

switch ($accion) {
    case 'generar_qr':
        if ($_SESSION['usuario_rol'] !== 'Administrador') {
            echo json_encode(['ok' => false, 'mensaje' => 'Acceso denegado']);
            break;
        }
        $userId = intval($_GET['id_usuario'] ?? 0);
        if (!$userId) {
            echo json_encode(['ok' => false, 'mensaje' => 'ID de usuario no válido']);
            break;
        }

        $conn = conectar();
        $stmt = $conn->prepare("SELECT nombre, apellido FROM usuarios WHERE id_usuario = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $conn->close();

        if (!$usuario) {
            echo json_encode(['ok' => false, 'mensaje' => 'Usuario no encontrado']);
            break;
        }

        $claveSecreta = 'mi_clave_secreta_2026'; // misma que en validación
        $payload = json_encode([
            "u" => $userId,
            "t" => md5($userId . $claveSecreta)
        ]);

        try {
            $builder = new Builder(
                writer: new PngWriter(),
                data: $payload,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 300,
                margin: 10
            );
            $result = $builder->build();
            $qrBase64 = base64_encode($result->getString());

            echo json_encode([
                'ok' => true,
                'qr' => 'data:image/png;base64,' . $qrBase64,
                'usuario' => $usuario['nombre'] . ' ' . $usuario['apellido']
            ]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error al generar QR: ' . $e->getMessage()]);
        }
        break;

    case 'registrar':
        // Recibir QR (JSON o URL, pero esperamos JSON con u y t)
        $qrData = $_POST['qrData'] ?? $_GET['qrData'] ?? '';
        if (!$qrData) {
            echo json_encode(['ok' => false, 'mensaje' => 'QR no proporcionado']);
            break;
        }

        $data = json_decode($qrData, true);
        if (!$data || !isset($data['u'])) {
            echo json_encode(['ok' => false, 'mensaje' => 'Formato QR inválido']);
            break;
        }

        $userId = intval($data['u']);
        $tokenRecibido = $data['t'] ?? '';
        $claveSecreta = 'mi_clave_secreta_2026';
        $tokenEsperado = md5($userId . $claveSecreta);

        if ($tokenRecibido !== $tokenEsperado) {
            echo json_encode(['ok' => false, 'mensaje' => 'QR inválido o falsificado']);
            break;
        }

        // Verificar existencia del usuario
        $conn = conectar();
        $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = ? AND estado = 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $usuarioExiste = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        $conn->close();

        if (!$usuarioExiste) {
            echo json_encode(['ok' => false, 'mensaje' => 'Usuario no encontrado o inactivo']);
            break;
        }

        $resultado = $model->registrar($userId, 'qr');
        $resultado['id_usuario'] = $userId;
        echo json_encode($resultado);
        break;

    case 'historial':
        if ($_SESSION['usuario_rol'] !== 'Administrador') {
            echo json_encode(['ok' => false, 'mensaje' => 'Acceso denegado']);
            break;
        }
        $desde = $_GET['desde'] ?? null;
        $hasta = $_GET['hasta'] ?? null;
        $usuarioId = isset($_GET['usuario']) ? intval($_GET['usuario']) : null;

        $historial = $model->obtenerHistorial($desde, $hasta, $usuarioId);
        echo json_encode(['ok' => true, 'datos' => $historial]);
        break;

    case 'reporte':
        if ($_SESSION['usuario_rol'] !== 'Administrador') {
            echo json_encode(['ok' => false, 'mensaje' => 'Acceso denegado']);
            break;
        }
        $reporte = $model->obtenerReporteRapido();
        echo json_encode(['ok' => true, 'datos' => $reporte]);
        break;

    case 'listar_usuarios':
        if ($_SESSION['usuario_rol'] !== 'Administrador') {
            echo json_encode(['ok' => false, 'mensaje' => 'Acceso denegado']);
            break;
        }
        $conn = conectar();
        $result = $conn->query("SELECT id_usuario, nombre, apellido, correo FROM usuarios WHERE estado = 1 ORDER BY nombre ASC");
        $usuarios = $result->fetch_all(MYSQLI_ASSOC);
        $conn->close();
        echo json_encode(['ok' => true, 'datos' => $usuarios]);
        break;

    default:
        echo json_encode(['ok' => false, 'mensaje' => 'Acción no reconocida']);


        ////////////////////////////////////////////////////////////////////////////////////////

        case 'generar_qr':
    if ($_SESSION['usuario_rol'] !== 'Administrador') {
        echo json_encode(['ok' => false, 'mensaje' => 'Acceso denegado']);
        break;
    }
    $userId = intval($_GET['id_usuario'] ?? 0);
    if (!$userId) {
        echo json_encode(['ok' => false, 'mensaje' => 'ID de usuario no válido']);
        break;
    }
    // ... obtener nombre, generar token, etc.
    try {
        $builder = new Builder(
            writer: new PngWriter(),
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10
        );
        $result = $builder->build();
        $qrBase64 = base64_encode($result->getString());
        echo json_encode([
            'ok' => true,
            'qr' => 'data:image/png;base64,' . $qrBase64,
            'url' => $url,
            'usuario' => $usuario['nombre'] . ' ' . $usuario['apellido']
        ]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'mensaje' => 'Error al generar QR: ' . $e->getMessage()]);
    }
    break;


}