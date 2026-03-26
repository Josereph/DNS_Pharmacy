<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Sesión no iniciada']);
    exit;
}

require_once __DIR__ . '/../models/AsistenciaModel.php';
require_once __DIR__ . '/../config/database.php';

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

        $secret = 'DNS_PHARMACY_SECRET_2025';
        $token = hash_hmac('sha256', $userId, $secret);
        $url = "https://" . $_SERVER['HTTP_HOST'] . "/DNS_Pharmacy/views/asistencia_scan.php?token=$token&uid=$userId";

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

    case 'registrar':
        $token = $_POST['token'] ?? $_GET['token'] ?? '';
        $userId = intval($_POST['uid'] ?? $_GET['uid'] ?? 0);
        $origen = $_POST['origen'] ?? 'qr';

        if ($token) {
            $secret = 'DNS_PHARMACY_SECRET_2025';
            $expectedToken = hash_hmac('sha256', $userId, $secret);
            if ($token !== $expectedToken) {
                echo json_encode(['ok' => false, 'mensaje' => 'Token inválido']);
                break;
            }
        }

        if (!$userId) {
            echo json_encode(['ok' => false, 'mensaje' => 'Usuario no identificado']);
            break;
        }

        $result = $model->registrar($userId, $origen);
        echo json_encode($result);
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
}