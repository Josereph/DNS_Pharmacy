<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'mensaje' => 'Sesión no iniciada.']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../vendor/autoload.php';

// ── use statements SIEMPRE al inicio, fuera de funciones/switch ──
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

switch ($accion) {

    /* ══════════════════════════════════════════
       LISTAR VENTAS
    ══════════════════════════════════════════ */
    case 'listar':
        $desde      = trim($_GET['desde']       ?? '');
        $hasta      = trim($_GET['hasta']        ?? '');
        $id_usuario = intval($_GET['id_usuario'] ?? 0);

        $conn   = conectar();
        $sql    = "SELECT v.*, CONCAT(u.nombre,' ',u.apellido) AS nombre_empleado
                   FROM ventas v
                   LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                   WHERE v.estado != 'pendiente'";
        $params = [];
        $types  = '';

        if ($desde)      { $sql .= " AND DATE(v.fecha_venta) >= ?"; $params[] = $desde;      $types .= 's'; }
        if ($hasta)      { $sql .= " AND DATE(v.fecha_venta) <= ?"; $params[] = $hasta;      $types .= 's'; }
        if ($id_usuario) { $sql .= " AND v.id_usuario = ?";          $params[] = $id_usuario; $types .= 'i'; }

        $sql .= " ORDER BY v.fecha_venta DESC";

        $stmt = $conn->prepare($sql);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $ventas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();

        echo json_encode(['ok' => true, 'datos' => $ventas]);
        break;

    /* ══════════════════════════════════════════
       DETALLE VENTA
    ══════════════════════════════════════════ */
    case 'detalle':
        $id   = intval($_GET['id'] ?? 0);
        $conn = conectar();

        $stmt = $conn->prepare("
            SELECT v.*, CONCAT(u.nombre,' ',u.apellido) AS nombre_empleado
            FROM ventas v
            LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
            WHERE v.id_venta = ? LIMIT 1
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $venta = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$venta) {
            echo json_encode(['ok' => false, 'mensaje' => 'Venta no encontrada.']);
            $conn->close();
            break;
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

    /* ══════════════════════════════════════════
       ENVIAR HISTORIAL POR CORREO (PDF adjunto)
    ══════════════════════════════════════════ */
    case 'enviar_correo':
        if ($_SESSION['usuario_rol'] !== 'Administrador') {
            echo json_encode(['ok' => false, 'mensaje' => 'Solo el administrador puede enviar correos.']);
            break;
        }

        $tipo       = $_POST['tipo']        ?? 'dia';
        $destino    = trim($_POST['destino'] ?? '');
        $asunto     = trim($_POST['asunto']  ?? 'Historial de Ventas - DNS Pharmacy');
        $desde      = trim($_POST['desde']   ?? '');
        $hasta      = trim($_POST['hasta']   ?? '');
        $id_usuario = intval($_POST['id_usuario'] ?? 0);

        if (!$destino || !filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'mensaje' => 'Correo destino inválido.']);
            break;
        }

        // ── Obtener ventas según tipo ──
        $conn   = conectar();
        $sql    = "SELECT v.*, CONCAT(u.nombre,' ',u.apellido) AS nombre_empleado
                   FROM ventas v
                   LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
                   WHERE v.estado != 'pendiente'";
        $params = [];
        $types  = '';

        if ($tipo === 'dia') {
            $dia = $desde ?: date('Y-m-d');
            $sql .= " AND DATE(v.fecha_venta) = ?";
            $params[] = $dia;
            $types   .= 's';
        } elseif ($tipo === 'rango') {
            if ($desde) { $sql .= " AND DATE(v.fecha_venta) >= ?"; $params[] = $desde; $types .= 's'; }
            if ($hasta) { $sql .= " AND DATE(v.fecha_venta) <= ?"; $params[] = $hasta; $types .= 's'; }
        } elseif ($tipo === 'usuario') {
            if ($id_usuario) { $sql .= " AND v.id_usuario = ?"; $params[] = $id_usuario; $types .= 'i'; }
            if ($desde)      { $sql .= " AND DATE(v.fecha_venta) >= ?"; $params[] = $desde; $types .= 's'; }
            if ($hasta)      { $sql .= " AND DATE(v.fecha_venta) <= ?"; $params[] = $hasta; $types .= 's'; }
        }

        $sql .= " ORDER BY v.fecha_venta DESC";
        $stmt = $conn->prepare($sql);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $ventas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Nombre del empleado si es por usuario
        $nombreEmpleado = '';
        if ($tipo === 'usuario' && $id_usuario) {
            $stmtE = $conn->prepare("SELECT CONCAT(nombre,' ',apellido) AS n FROM usuarios WHERE id_usuario = ? LIMIT 1");
            $stmtE->bind_param('i', $id_usuario);
            $stmtE->execute();
            $resE           = $stmtE->get_result()->fetch_assoc();
            $nombreEmpleado = $resE['n'] ?? '';
            $stmtE->close();
        }
        $conn->close();

        if (empty($ventas)) {
            echo json_encode(['ok' => false, 'mensaje' => 'No hay ventas en el período seleccionado.']);
            break;
        }

        // ── Título descriptivo ──
        switch ($tipo) {
            case 'dia':
                $tituloReporte = 'Ventas del día ' . ($desde ?: date('Y-m-d'));
                break;
            case 'rango':
                $tituloReporte = 'Ventas del ' . ($desde ?: '—') . ' al ' . ($hasta ?: date('Y-m-d'));
                break;
            case 'usuario':
                $tituloReporte = 'Ventas de ' . ($nombreEmpleado ?: 'Empleado #' . $id_usuario);
                if ($desde || $hasta) {
                    $tituloReporte .= ' (' . ($desde ?: 'inicio') . ' – ' . ($hasta ?: date('Y-m-d')) . ')';
                }
                break;
            default:
                $tituloReporte = 'Historial de Ventas';
        }

        // ── Calcular totales ──
        $totalVendido = array_sum(array_column($ventas, 'total'));
        $totalIva     = array_sum(array_column($ventas, 'impuesto'));
        $promedio     = count($ventas) > 0 ? $totalVendido / count($ventas) : 0;

        // ── Generar HTML del PDF ──
        $filas = '';
        foreach ($ventas as $i => $v) {
            $estado = $v['estado'] === 'completada'
                ? '<span style="color:#2e7d32;font-weight:600;">Completada</span>'
                : '<span style="color:#c62828;font-weight:600;">Anulada</span>';
            $bg     = ($i % 2 === 0) ? '#fdf8fd' : '#ffffff';
            $filas .= '<tr style="background:' . $bg . '">
                <td style="padding:5px 6px;">' . ($i + 1) . '</td>
                <td style="padding:5px 6px;font-family:monospace;color:#841480;">' . htmlspecialchars($v['numero_ticket']) . '</td>
                <td style="padding:5px 6px;">' . htmlspecialchars($v['nombre_empleado'] ?? '—') . '</td>
                <td style="padding:5px 6px;">' . date('d/m/Y H:i', strtotime($v['fecha_venta'])) . '</td>
                <td style="padding:5px 6px;text-align:right;">$' . number_format($v['subtotal'], 2) . '</td>
                <td style="padding:5px 6px;text-align:right;">' . ($v['impuesto'] > 0 ? '$' . number_format($v['impuesto'], 2) : '—') . '</td>
                <td style="padding:5px 6px;text-align:right;font-weight:700;color:#841480;">$' . number_format($v['total'], 2) . '</td>
                <td style="padding:5px 6px;text-align:center;">' . ucfirst($v['metodo_pago']) . '</td>
                <td style="padding:5px 6px;text-align:center;">' . $estado . '</td>
            </tr>';
        }

        $htmlPDF = '<!DOCTYPE html><html><head><meta charset="utf-8">
        <style>
            body  { font-family:Arial,sans-serif; font-size:11px; color:#333; margin:0; padding:0; }
            .hdr  { background:#841480; padding:18px 22px; color:#fff; }
            .hdr h1 { margin:0; font-size:17px; }
            .hdr p  { margin:3px 0 0; font-size:10px; opacity:.85; }
            .sub  { font-size:12px; font-weight:bold; color:#841480; padding:12px 22px 4px; }
            .stats { display:flex; gap:10px; padding:0 22px 14px; }
            .sb   { flex:1; background:#f5e8f5; border-radius:6px; padding:8px 12px; border-left:3px solid #841480; }
            .sn   { font-size:14px; font-weight:700; color:#841480; }
            .sl   { font-size:9px; color:#888; text-transform:uppercase; }
            table { width:100%; border-collapse:collapse; }
            th    { background:#841480; color:#fff; padding:7px 6px; text-align:left; font-size:10px; }
            td    { border-bottom:1px solid #f0e0f0; font-size:10px; }
            .ft   { text-align:center; padding:12px; font-size:10px; color:#aaa; border-top:1px solid #e8d5e8; margin-top:8px; }
        </style></head><body>
        <div class="hdr"><h1>DNS Pharmacy</h1><p>Drug Network Supply — Reporte de Ventas</p></div>
        <div class="sub">' . htmlspecialchars($tituloReporte) . '</div>
        <div class="stats">
            <div class="sb"><div class="sn">' . count($ventas) . '</div><div class="sl">Total tickets</div></div>
            <div class="sb"><div class="sn">$' . number_format($totalVendido, 2) . '</div><div class="sl">Total vendido</div></div>
            <div class="sb"><div class="sn">$' . number_format($totalIva, 2) . '</div><div class="sl">Total IVA</div></div>
            <div class="sb"><div class="sn">$' . number_format($promedio, 2) . '</div><div class="sl">Ticket promedio</div></div>
        </div>
        <table>
            <thead><tr>
                <th>#</th><th>Ticket</th><th>Empleado</th><th>Fecha</th>
                <th>Subtotal</th><th>IVA</th><th>Total</th><th>Método</th><th>Estado</th>
            </tr></thead>
            <tbody>' . $filas . '</tbody>
        </table>
        <div class="ft">Generado el ' . date('d/m/Y H:i') . ' — DNS Pharmacy · Sistema POS</div>
        </body></html>';

        // ── Generar PDF con mPDF ──
        try {
            $mpdf = new \Mpdf\Mpdf([
                'margin_top'    => 0,
                'margin_bottom' => 10,
                'margin_left'   => 10,
                'margin_right'  => 10,
                'format'        => 'A4-L',
            ]);
            $mpdf->WriteHTML($htmlPDF);
            $pdfContent = $mpdf->Output('', 'S');
        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error al generar PDF: ' . $e->getMessage()]);
            break;
        }

        // ── Enviar con PHPMailer ──
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USER;
            $mail->Password   = MAIL_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(MAIL_USER, MAIL_FROM_NAME);
            $mail->addAddress($destino);
            $mail->Subject = $asunto;
            $mail->isHTML(true);

            $mail->Body = '
            <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
                <div style="background:linear-gradient(135deg,#841480,#6a1066);padding:22px;border-radius:10px 10px 0 0;text-align:center;">
                    <h2 style="color:#fff;margin:0;font-size:20px;">DNS Pharmacy</h2>
                    <p style="color:rgba(255,255,255,.8);margin:4px 0 0;font-size:12px;">Drug Network Supply — Sistema POS</p>
                </div>
                <div style="background:#f8f7fc;padding:22px;border:1px solid #e8d5e8;">
                    <p style="font-size:14px;color:#333;">Hola,</p>
                    <p style="font-size:14px;color:#333;">Se adjunta el reporte: <strong>' . htmlspecialchars($tituloReporte) . '</strong></p>
                    <table style="width:100%;border-collapse:collapse;margin:14px 0;border-radius:8px;overflow:hidden;">
                        <tr style="background:#f5e8f5;">
                            <td style="padding:10px;font-size:13px;color:#841480;font-weight:600;">Total tickets</td>
                            <td style="padding:10px;font-size:13px;font-weight:700;text-align:right;">' . count($ventas) . '</td>
                        </tr>
                        <tr>
                            <td style="padding:10px;font-size:13px;color:#555;">Total vendido</td>
                            <td style="padding:10px;font-size:14px;font-weight:700;color:#70ab32;text-align:right;">$' . number_format($totalVendido, 2) . '</td>
                        </tr>
                        <tr style="background:#f5e8f5;">
                            <td style="padding:10px;font-size:13px;color:#555;">Total IVA</td>
                            <td style="padding:10px;font-size:13px;text-align:right;">$' . number_format($totalIva, 2) . '</td>
                        </tr>
                        <tr>
                            <td style="padding:10px;font-size:13px;color:#555;">Ticket promedio</td>
                            <td style="padding:10px;font-size:13px;text-align:right;">$' . number_format($promedio, 2) . '</td>
                        </tr>
                    </table>
                    <p style="font-size:12px;color:#aaa;">El detalle completo está en el PDF adjunto.</p>
                </div>
                <div style="background:#fff;padding:12px;text-align:center;border:1px solid #e8d5e8;border-top:none;border-radius:0 0 10px 10px;">
                    <p style="font-size:11px;color:#aaa;margin:0;">DNS Pharmacy · Generado el ' . date('d/m/Y H:i') . '</p>
                </div>
            </div>';

            $nombreArchivo = 'historial_ventas_' . date('Ymd_His') . '.pdf';
            $mail->addStringAttachment($pdfContent, $nombreArchivo, 'base64', 'application/pdf');
            $mail->send();

            echo json_encode([
                'ok'      => true,
                'mensaje' => 'Correo enviado a ' . $destino . ' con ' . count($ventas) . ' ventas.'
            ]);

        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'mensaje' => 'Error al enviar correo: ' . $mail->ErrorInfo]);
        }
        break;

    default:
        echo json_encode(['ok' => false, 'mensaje' => 'Acción no reconocida.']);
        break;
}