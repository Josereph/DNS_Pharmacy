<?php
// controllers/chatbot_controller.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

define('GEMINI_API_KEY', 'AIzaSyBkR6UEuAgHca_Zo2iNa92kdzQDx-uAEDU');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['respuesta' => 'Método no permitido']);
    exit;
}

$input   = json_decode(file_get_contents('php://input'), true);
$mensaje = trim($input['mensaje'] ?? '');

if (empty($mensaje)) {
    echo json_encode(['respuesta' => 'Por favor, escribe tu consulta. 💊']);
    exit;
}

$contexto  = analizarPreguntaYObtenerContexto($mensaje);
$respuesta = generarRespuestaConGemini($mensaje, $contexto);

echo json_encode(['respuesta' => $respuesta]);
exit;

// ─────────────────────────────────────────────────────────────────────────────

function analizarPreguntaYObtenerContexto($pregunta) {
    $preguntaLower = strtolower($pregunta);
    $contexto = [
        'productos'      => [],
        'info_farmacia'  => getInfoFarmacia(),
        'categorias'     => [],
        'requiere_receta'=> null,
        'tipo_consulta'  => 'general'
    ];

    // Detectar tipo de consulta
    if (strpos($preguntaLower, 'precio') !== false ||
        strpos($preguntaLower, 'cuesta') !== false ||
        strpos($preguntaLower, 'valor')  !== false) {
        $contexto['tipo_consulta'] = 'precio';
    }
    if (strpos($preguntaLower, 'stock')      !== false ||
        strpos($preguntaLower, 'disponible') !== false ||
        strpos($preguntaLower, 'hay')        !== false ||
        strpos($preguntaLower, 'tienen')     !== false) {
        $contexto['tipo_consulta'] = 'stock';
    }
    if (strpos($preguntaLower, 'receta') !== false) {
        $contexto['tipo_consulta'] = 'receta';
    }
    if (strpos($preguntaLower, 'horario') !== false ||
        strpos($preguntaLower, 'abren')   !== false ||
        strpos($preguntaLower, 'cierran') !== false ||
        strpos($preguntaLower, 'abierto') !== false) {
        $contexto['tipo_consulta'] = 'horario';
    }
    if (strpos($preguntaLower, 'domicilio') !== false ||
        strpos($preguntaLower, 'delivery')  !== false ||
        strpos($preguntaLower, 'envio')     !== false ||
        strpos($preguntaLower, 'envío')     !== false) {
        $contexto['tipo_consulta'] = 'domicilio';
    }
    if (strpos($preguntaLower, 'categoria') !== false ||
        strpos($preguntaLower, 'tipos de')  !== false ||
        strpos($preguntaLower, 'que tienen')!== false) {
        $contexto['categorias'] = getCategorias();
    }

    // Extraer posibles nombres de medicamentos
    $stopWords = ['qué', 'que', 'como', 'cuál', 'cual', 'cuanto', 'donde', 'cuando',
                  'hay', 'tienen', 'un', 'una', 'me', 'te', 'se', 'lo', 'la', 'los',
                  'las', 'del', 'el', 'precio', 'cuesta', 'vale', 'tiene', 'para',
                  'por', 'con', 'sin', 'hola', 'buenas', 'buenos', 'dias', 'tardes',
                  'noches', 'gracias', 'favor', 'quiero', 'necesito', 'busco'];

    $palabras         = explode(' ', $preguntaLower);
    $posiblesProductos = [];

    foreach ($palabras as $palabra) {
        $palabra = trim($palabra, '¿?¡!,.;');
        if (strlen($palabra) > 3 && !in_array($palabra, $stopWords)) {
            $posiblesProductos[] = $palabra;
        }
    }

    // Buscar productos por cada palabra clave
    $productosEncontrados = [];
    foreach ($posiblesProductos as $termino) {
        $resultados = buscarProductos($termino);
        foreach ($resultados as $prod) {
            $productosEncontrados[$prod['id_producto']] = $prod;
        }
    }

    // Si no encontró productos específicos, cargar todos los disponibles
    if (empty($productosEncontrados)) {
        $todos = buscarProductos('');
        foreach ($todos as $prod) {
            $productosEncontrados[$prod['id_producto']] = $prod;
        }
    }

    $contexto['productos'] = array_values($productosEncontrados);

    if ($contexto['tipo_consulta'] === 'receta' && !empty($contexto['productos'])) {
        $contexto['requiere_receta'] = requiereReceta($contexto['productos'][0]['nombre']);
    }

    return $contexto;
}

// ─────────────────────────────────────────────────────────────────────────────

function generarRespuestaConGemini($pregunta, $contexto) {
    $prompt = construirPrompt($pregunta, $contexto);

    $data = [
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'temperature'     => 0.7,
            'maxOutputTokens' => 800,
            'topP'            => 0.95
        ]
    ];

    $ch = curl_init(GEMINI_API_URL . '?key=' . GEMINI_API_KEY);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST,           true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT,        30);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        return $result['candidates'][0]['content']['parts'][0]['text']
               ?? "No pude procesar tu consulta. ¿Podrías reformularla?";
    } else {
        return "Lo siento, hubo un problema técnico. Escríbenos al WhatsApp "
               . $contexto['info_farmacia']['whatsapp'] . " 💊";
    }
}

// ─────────────────────────────────────────────────────────────────────────────

function construirPrompt($pregunta, $contexto) {
    $info   = $contexto['info_farmacia'];
    $prompt = "Eres DNSBot, el asistente virtual de DNS Pharmacy en El Salvador.
Eres amable, empático, profesional y muy completo en tus respuestas.

═══════════════════════════════════════
INFORMACIÓN OFICIAL DE DNS PHARMACY:
═══════════════════════════════════════
- Nombre: {$info['nombre']}
- Horario: {$info['horario']}
- WhatsApp: {$info['whatsapp']}
- Teléfono: {$info['telefono']}
- Email: {$info['email']}
- Dirección: {$info['direccion']}

SERVICIOS QUE OFRECEMOS:
- Venta de medicamentos genéricos y de marca
- Asesoría farmacéutica gratuita
- Toma de presión arterial sin costo
- Entregas a domicilio (consultar disponibilidad y costo)
- Métodos de pago: Efectivo, Tarjeta y Transferencia
- Programa de afiliación con beneficios exclusivos

";

    // Agregar productos encontrados
    if (!empty($contexto['productos'])) {
        $prompt .= "═══════════════════════════════════════\n";
        $prompt .= "📦 PRODUCTOS EN NUESTRO INVENTARIO:\n";
        $prompt .= "═══════════════════════════════════════\n";
        foreach ($contexto['productos'] as $producto) {
            $prompt .= "▸ {$producto['nombre']}";
            if (!empty($producto['presentacion'])) {
                $prompt .= " ({$producto['presentacion']})";
            }
            $prompt .= "\n";
            $prompt .= "  💰 Precio: $" . number_format($producto['precio_venta'], 2) . "\n";
            $prompt .= "  📦 Stock: {$producto['stock_actual']} unidades disponibles\n";
            if (!empty($producto['marca'])) {
                $prompt .= "  🏷️ Marca: {$producto['marca']}\n";
            }
            if (!empty($producto['laboratorio'])) {
                $prompt .= "  🔬 Laboratorio: {$producto['laboratorio']}\n";
            }
            if (!empty($producto['descripcion'])) {
                $prompt .= "  📝 Descripción: {$producto['descripcion']}\n";
            }
            if ($producto['requiere_receta']) {
                $prompt .= "  ⚠️ REQUIERE RECETA MÉDICA\n";
            }
            $prompt .= "\n";
        }
    }

    // Agregar categorías si se pidieron
    if (!empty($contexto['categorias'])) {
        $prompt .= "═══════════════════════════════════════\n";
        $prompt .= "📂 CATEGORÍAS DISPONIBLES:\n";
        $prompt .= "═══════════════════════════════════════\n";
        foreach ($contexto['categorias'] as $cat) {
            $prompt .= "- {$cat['nombre']}";
            if (!empty($cat['descripcion'])) {
                $prompt .= ": {$cat['descripcion']}";
            }
            $prompt .= "\n";
        }
        $prompt .= "\n";
    }

    $prompt .= "═══════════════════════════════════════
INSTRUCCIONES PARA RESPONDER:
═══════════════════════════════════════
1. SALUDES Y CONVERSACIÓN GENERAL: Responde de forma amigable y natural. 
   Preséntate como DNSBot cuando sea apropiado.

2. CONSULTAS DE PRODUCTOS: Usa la información del inventario de arriba.
   Si el producto está disponible, muestra precio y stock.
   Si no está en el inventario, sugiere contactar por WhatsApp para consultar.

3. CONSULTAS DE SALUD Y SÍNTOMAS: Puedes dar información general sobre 
   medicamentos comunes (analgésicos, antigripales, antibióticos, etc.) 
   y sugerir los productos de nuestro inventario que podrían ayudar.
   SIEMPRE recomienda consultar al médico para diagnósticos.

4. PREGUNTAS SOBRE RECETAS: Indica claramente si el producto requiere receta.
   Los antibióticos generalmente requieren receta médica.

5. HORARIOS Y UBICACIÓN: Usa la información oficial de arriba.

6. PREGUNTAS FUERA DE TEMA FARMACÉUTICO: Responde brevemente y redirige
   amablemente hacia temas de salud o los servicios de la farmacia.

7. SI NO SABES ALGO: Sugiere contactar por WhatsApp {$info['whatsapp']} 
   o visitar la farmacia directamente.

8. FORMATO: Usa emojis con moderación para hacer la respuesta más visual.
   Sé conciso pero completo. Máximo 3-4 párrafos cortos.

9. IDIOMA: Responde SIEMPRE en español.

10. TONO: Profesional pero cercano, como un farmacéutico amigable.

═══════════════════════════════════════
CONSULTA DEL CLIENTE: {$pregunta}
═══════════════════════════════════════

RESPUESTA DE DNSBOT:";

    return $prompt;
}