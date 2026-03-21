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

    case 'procesar_venta':
        $id_usuario    = intval($_SESSION['usuario_id']);
        $subtotal      = floatval($_POST['subtotal']       ?? 0);
        $impuesto      = floatval($_POST['impuesto']       ?? 0);
        $total         = floatval($_POST['total']          ?? 0);
        $monto_recibido = floatval($_POST['monto_recibido'] ?? 0);
        $cambio        = floatval($_POST['cambio']         ?? 0);
        $metodo_pago   = $_POST['metodo_pago'] ?? 'efectivo';
        $items         = json_decode($_POST['items'] ?? '[]', true);

        if (empty($items)) {
            echo json_encode(['ok' => false, 'mensaje' => 'No hay productos en la venta.']);
            break;
        }

        $conn = conectar();
        $conn->begin_transaction();

        try {
            // Buscar o crear turno activo
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
                // Crear turno nuevo
                $fecha  = date('Y-m-d');
                $hora   = date('H:i:s');
                $stmt2  = $conn->prepare("INSERT INTO turnos (id_usuario, fecha, hora_entrada, estado) VALUES (?,?,?,'abierto')");
                $stmt2->bind_param('iss', $id_usuario, $fecha, $hora);
                $stmt2->execute();
                $id_turno = $conn->insert_id;
                $stmt2->close();
            } else {
                $id_turno = $turno['id_turno'];
            }

            // Generar número de ticket único
            $numero_ticket = 'TKT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Insertar venta
            $fecha_venta = date('Y-m-d H:i:s');
            $metodos_validos = ['efectivo','tarjeta','transferencia'];
            if (!in_array($metodo_pago, $metodos_validos)) $metodo_pago = 'efectivo';

            $stmt3 = $conn->prepare("
                INSERT INTO ventas
                    (id_usuario, id_turno, numero_ticket, fecha_venta,
                     subtotal, impuesto, total,
                     monto_recibido, cambio, metodo_pago, estado)
                VALUES (?,?,?,?,?,?,?,?,?,'$metodo_pago','completada')
            ");
            $stmt3->bind_param(
                'iissddddd',
                $id_usuario, $id_turno, $numero_ticket, $fecha_venta,
                $subtotal, $impuesto, $total,
                $monto_recibido, $cambio
            );
            $stmt3->execute();
            $id_venta = $conn->insert_id;
            $stmt3->close();

            // Insertar detalle y actualizar stock
            foreach ($items as $item) {
                $id_producto  = intval($item['id_producto']);
                $cantidad     = intval($item['cantidad']);
                $precio_unit  = floatval($item['precio']);
                $subtotal_det = $precio_unit * $cantidad;

                // Verificar stock
                $stmtStock = $conn->prepare("SELECT stock_actual FROM productos WHERE id_producto = ? FOR UPDATE");
                $stmtStock->bind_param('i', $id_producto);
                $stmtStock->execute();
                $prodData = $stmtStock->get_result()->fetch_assoc();
                $stmtStock->close();

                if (!$prodData || $prodData['stock_actual'] < $cantidad) {
                    throw new Exception('Stock insuficiente para el producto ID ' . $id_producto);
                }

                // Insertar detalle_venta
                $stmtDet = $conn->prepare("
                    INSERT INTO detalle_venta
                        (id_venta, id_producto, cantidad, precio_unitario, subtotal)
                    VALUES (?,?,?,?,?)
                ");
                $stmtDet->bind_param('iiidd', $id_venta, $id_producto, $cantidad, $precio_unit, $subtotal_det);
                $stmtDet->execute();
                $stmtDet->close();

                // Actualizar stock
                $stmtUpd = $conn->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id_producto = ?");
                $stmtUpd->bind_param('ii', $cantidad, $id_producto);
                $stmtUpd->execute();
                $stmtUpd->close();
            }

            // Actualizar totales del turno
            $stmtTurno = $conn->prepare("
                UPDATE turnos
                SET total_ventas  = total_ventas + ?,
                    total_tickets = total_tickets + 1
                WHERE id_turno = ?
            ");
            $stmtTurno->bind_param('di', $total, $id_turno);
            $stmtTurno->execute();
            $stmtTurno->close();

            $conn->commit();

            // Nombre del cajero para el ticket
            $cajero = ($_SESSION['usuario_nombre'] ?? 'Cajero');

            echo json_encode([
                'ok'            => true,
                'mensaje'       => 'Venta registrada correctamente.',
                'numero_ticket' => $numero_ticket,
                'id_venta'      => $id_venta,
                'cajero'        => $cajero
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