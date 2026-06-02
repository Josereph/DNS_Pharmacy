<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Sesión no iniciada.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {

    /* ── Anular venta con reversión de inventario ── */
    case 'anular':

        /* 1. Solo el Administrador puede anular */
        if (($_SESSION['usuario_rol'] ?? '') !== 'Administrador') {
            http_response_code(403);
            echo json_encode(['ok' => false, 'mensaje' => 'Sin permisos para anular ventas.']);
            break;
        }

        $id_venta  = intval($_POST['id_venta']  ?? 0);
        $motivo    = trim($_POST['motivo']       ?? '');
        $anulado_por = intval($_SESSION['usuario_id']);

        if ($id_venta <= 0) {
            echo json_encode(['ok' => false, 'mensaje' => 'ID de venta inválido.']);
            break;
        }

        /* 2. Conexión PDO para transacciones robustas */
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error de conexión: ' . $e->getMessage()]);
            break;
        }

        try {
            $pdo->beginTransaction();

            /* 3. Verificar que la venta existe y está en estado 'completada' */
            $stmtVenta = $pdo->prepare(
                "SELECT id_venta, estado, numero_ticket, total
                 FROM ventas
                 WHERE id_venta = ?
                 LIMIT 1"
            );
            $stmtVenta->execute([$id_venta]);
            $venta = $stmtVenta->fetch();

            if (!$venta) {
                $pdo->rollBack();
                echo json_encode(['ok' => false, 'mensaje' => 'Venta no encontrada.']);
                break;
            }

            if ($venta['estado'] !== 'completada') {
                $pdo->rollBack();
                $estadoActual = htmlspecialchars($venta['estado']);
                echo json_encode(['ok' => false, 'mensaje' => "La venta ya está en estado '{$estadoActual}' y no puede anularse."]);
                break;
            }

            /* 4. Obtener los ítems vendidos para revertir el inventario */
            $stmtDetalle = $pdo->prepare(
                "SELECT id_producto, cantidad
                 FROM detalle_venta
                 WHERE id_venta = ?"
            );
            $stmtDetalle->execute([$id_venta]);
            $items = $stmtDetalle->fetchAll();

            if (empty($items)) {
                $pdo->rollBack();
                echo json_encode(['ok' => false, 'mensaje' => 'No se encontraron productos en esta venta.']);
                break;
            }

            /* 5. Revertir stock producto por producto */
            $stmtStock = $pdo->prepare(
                "UPDATE productos
                 SET stock_actual = stock_actual + ?
                 WHERE id_producto = ?"
            );

            foreach ($items as $item) {
                $stmtStock->execute([
                    intval($item['cantidad']),
                    intval($item['id_producto'])
                ]);

                if ($stmtStock->rowCount() === 0) {
                    throw new Exception(
                        'No se pudo actualizar el stock del producto ID ' . $item['id_producto'] . '.'
                    );
                }
            }

            /* 6. Marcar la venta como anulada con auditoría completa */
            $stmtAnular = $pdo->prepare(
                "UPDATE ventas
                 SET estado           = 'anulada',
                     fecha_anulacion  = NOW(),
                     anulada_por      = ?,
                     motivo_anulacion = ?
                 WHERE id_venta = ?
                   AND estado   = 'completada'"
            );
            $stmtAnular->execute([
                $anulado_por,
                $motivo ?: null,
                $id_venta
            ]);

            if ($stmtAnular->rowCount() === 0) {
                throw new Exception('No se pudo actualizar el estado de la venta. Es posible que ya fue modificada por otro proceso.');
            }

            /* 7. Commit — todo salió bien */
            $pdo->commit();

            echo json_encode([
                'ok'      => true,
                'mensaje' => 'Venta ' . htmlspecialchars($venta['numero_ticket']) . ' anulada correctamente. El inventario ha sido restaurado.',
                'ticket'  => $venta['numero_ticket'],
            ]);

        } catch (Exception $e) {
            /* ROLLBACK total — ni la venta ni el stock se modifican */
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode([
                'ok'      => false,
                'mensaje' => 'Error al procesar la anulación: ' . $e->getMessage() . ' Se realizó un rollback completo.',
            ]);
        }

        break;

    /* ── Listar ventas con filtro de fechas ── */
    case 'listar':
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');

        $conn = conectar();

        $sql = "
            SELECT v.*,
                   CONCAT(u.nombre,' ',u.apellido) AS nombre_empleado
            FROM ventas v
            LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
            WHERE v.estado != 'pendiente'
        ";

        $params = [];
        $types  = '';

        if ($desde) { $sql .= " AND DATE(v.fecha_venta) >= ?"; $params[] = $desde; $types .= 's'; }
        if ($hasta) { $sql .= " AND DATE(v.fecha_venta) <= ?"; $params[] = $hasta; $types .= 's'; }

        $sql .= " ORDER BY v.fecha_venta DESC";

        $stmt = $conn->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $ventas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();

        echo json_encode(['ok' => true, 'datos' => $ventas]);
        break;

    /* ── Detalle de una venta ── */
    case 'detalle':
        $id   = intval($_GET['id'] ?? 0);
        $conn = conectar();

        $stmt = $conn->prepare("
            SELECT v.*,
                   CONCAT(u.nombre,' ',u.apellido) AS nombre_empleado
            FROM ventas v
            LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
            WHERE v.id_venta = ?
            LIMIT 1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $venta = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$venta) {
            echo json_encode(['ok' => false, 'mensaje' => 'Venta no encontrada.']);
            $conn->close(); break;
        }

        $stmt2 = $conn->prepare("
            SELECT dv.*, p.nombre AS nombre_producto
            FROM detalle_venta dv
            LEFT JOIN productos p ON dv.id_producto = p.id_producto
            WHERE dv.id_venta = ?
        ");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $detalle = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();
        $conn->close();

        echo json_encode(['ok' => true, 'datos' => ['venta' => $venta, 'detalle' => $detalle]]);
        break;

    default:
        echo json_encode(['ok' => false, 'mensaje' => 'Acción no reconocida.']);
        break;
}