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

    /* ══════════════════════════════════════════
       PROCESAR VENTA
    ══════════════════════════════════════════ */
    case 'procesar_venta':
        $id_usuario     = intval($_SESSION['usuario_id']);
        $subtotal       = floatval($_POST['subtotal']        ?? 0);
        $impuesto       = floatval($_POST['impuesto']        ?? 0);
        $total          = floatval($_POST['total']           ?? 0);
        $monto_recibido = floatval($_POST['monto_recibido']  ?? 0);
        $cambio         = floatval($_POST['cambio']          ?? 0);
        $metodo_pago    = $_POST['metodo_pago'] ?? 'efectivo';
        $items          = json_decode($_POST['items'] ?? '[]', true);

        if (empty($items)) {
            echo json_encode(['ok' => false, 'mensaje' => 'No hay productos en la venta.']);
            break;
        }

        $conn = conectar();
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("
                SELECT id_turno FROM turnos
                WHERE id_usuario = ? AND estado = 'abierto'
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->bind_param('i', $id_usuario);
            $stmt->execute();
            $turno = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$turno) {
                $fecha = date('Y-m-d'); $hora = date('H:i:s');
                $stmt2 = $conn->prepare("INSERT INTO turnos (id_usuario, fecha, hora_entrada, estado) VALUES (?,?,?,'abierto')");
                $stmt2->bind_param('iss', $id_usuario, $fecha, $hora);
                $stmt2->execute();
                $id_turno = $conn->insert_id;
                $stmt2->close();
            } else {
                $id_turno = $turno['id_turno'];
            }

            $numero_ticket   = 'TKT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $fecha_venta     = date('Y-m-d H:i:s');
            $metodos_validos = ['efectivo', 'tarjeta', 'transferencia'];
            if (!in_array($metodo_pago, $metodos_validos)) $metodo_pago = 'efectivo';

            $stmt3 = $conn->prepare("
                INSERT INTO ventas
                    (id_usuario, id_turno, numero_ticket, fecha_venta,
                     subtotal, impuesto, total, monto_recibido, cambio, metodo_pago, estado)
                VALUES (?,?,?,?,?,?,?,?,?,?,'completada')
            ");
            $stmt3->bind_param('iissddddds',
                $id_usuario, $id_turno, $numero_ticket, $fecha_venta,
                $subtotal, $impuesto, $total, $monto_recibido, $cambio, $metodo_pago
            );
            $stmt3->execute();
            $id_venta = $conn->insert_id;
            $stmt3->close();

            foreach ($items as $item) {
                $id_producto  = intval($item['id_producto']);
                $cantidad     = intval($item['cantidad']);
                $precio_unit  = floatval($item['precio']);
                $subtotal_det = $precio_unit * $cantidad;

                $stmtStock = $conn->prepare("SELECT stock_actual FROM productos WHERE id_producto = ? FOR UPDATE");
                $stmtStock->bind_param('i', $id_producto);
                $stmtStock->execute();
                $prodData = $stmtStock->get_result()->fetch_assoc();
                $stmtStock->close();

                if (!$prodData || $prodData['stock_actual'] < $cantidad)
                    throw new Exception('Stock insuficiente para el producto ID ' . $id_producto);

                $stmtDet = $conn->prepare("INSERT INTO detalle_venta (id_venta,id_producto,cantidad,precio_unitario,subtotal) VALUES (?,?,?,?,?)");
                $stmtDet->bind_param('iiidd', $id_venta, $id_producto, $cantidad, $precio_unit, $subtotal_det);
                $stmtDet->execute(); $stmtDet->close();

                $stmtUpd = $conn->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id_producto = ?");
                $stmtUpd->bind_param('ii', $cantidad, $id_producto);
                $stmtUpd->execute(); $stmtUpd->close();
            }

            $stmtTurno = $conn->prepare("UPDATE turnos SET total_ventas = total_ventas + ?, total_tickets = total_tickets + 1 WHERE id_turno = ?");
            $stmtTurno->bind_param('di', $total, $id_turno);
            $stmtTurno->execute(); $stmtTurno->close();

            $conn->commit();
            echo json_encode([
                'ok'            => true,
                'mensaje'       => 'Venta registrada.',
                'numero_ticket' => $numero_ticket,
                'id_venta'      => $id_venta,
                'cajero'        => $_SESSION['usuario_nombre'] ?? 'Cajero'
            ]);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
        }
        $conn->close();
        break;

    /* ══════════════════════════════════════════
       ÚLTIMAS VENTAS DEL TURNO (para el modal)
    ══════════════════════════════════════════ */
    case 'listar_recientes':
        $id_usuario = intval($_SESSION['usuario_id']);
        $conn       = conectar();

        // Ventas completadas del usuario hoy (últimas 5)
        $stmt = $conn->prepare("
            SELECT v.id_venta, v.numero_ticket, v.fecha_venta,
                   v.total, v.metodo_pago, v.estado,
                   CONCAT(u.nombre,' ',u.apellido) AS cajero
            FROM ventas v
            LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
            WHERE v.id_usuario = ?
              AND DATE(v.fecha_venta) = CURDATE()
            ORDER BY v.fecha_venta DESC
            LIMIT 5
        ");
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $ventas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();

        echo json_encode(['ok' => true, 'datos' => $ventas]);
        break;

    /* ══════════════════════════════════════════
       REVERTIR VENTA
    ══════════════════════════════════════════ */
    case 'revertir_venta':
        $id_venta   = intval($_POST['id_venta'] ?? 0);
        $id_usuario = intval($_SESSION['usuario_id']);

        if (!$id_venta) {
            echo json_encode(['ok' => false, 'mensaje' => 'ID de venta inválido.']);
            break;
        }

        $conn = conectar();
        $conn->begin_transaction();

        try {
            // Verificar que la venta pertenece a este usuario y no está anulada
            $stmt = $conn->prepare("SELECT * FROM ventas WHERE id_venta = ? AND id_usuario = ? FOR UPDATE");
            $stmt->bind_param('ii', $id_venta, $id_usuario);
            $stmt->execute();
            $venta = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$venta)
                throw new Exception('Venta no encontrada o no tienes permiso.');

            if ($venta['estado'] === 'anulada')
                throw new Exception('Esta venta ya fue anulada anteriormente.');

            // Obtener detalle para devolver stock
            $stmt2 = $conn->prepare("SELECT id_producto, cantidad FROM detalle_venta WHERE id_venta = ?");
            $stmt2->bind_param('i', $id_venta);
            $stmt2->execute();
            $detalle = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt2->close();

            // Devolver stock
            foreach ($detalle as $item) {
                $stmtStock = $conn->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id_producto = ?");
                $stmtStock->bind_param('ii', $item['cantidad'], $item['id_producto']);
                $stmtStock->execute(); $stmtStock->close();
            }

            // Marcar como anulada
            $stmt3 = $conn->prepare("UPDATE ventas SET estado = 'anulada' WHERE id_venta = ?");
            $stmt3->bind_param('i', $id_venta);
            $stmt3->execute(); $stmt3->close();

            // Descontar del turno
            if ($venta['id_turno']) {
                $stmt4 = $conn->prepare("
                    UPDATE turnos
                    SET total_ventas  = GREATEST(0, total_ventas - ?),
                        total_tickets = GREATEST(0, total_tickets - 1)
                    WHERE id_turno = ?
                ");
                $stmt4->bind_param('di', $venta['total'], $venta['id_turno']);
                $stmt4->execute(); $stmt4->close();
            }

            $conn->commit();
            echo json_encode([
                'ok'      => true,
                'mensaje' => 'Venta ' . $venta['numero_ticket'] . ' revertida. Stock restaurado.'
            ]);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
        }
        $conn->close();
        break;

    default:
        echo json_encode(['ok' => false, 'mensaje' => 'Acción no reconocida.']);
        break;
}