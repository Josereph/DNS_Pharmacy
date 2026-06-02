<?php
// test_gemini.php - Probar modelos disponibles
$API_KEY = "AIzaSyBkR6UEuAgHca_Zo2iNa92kdzQDx-uAEDU";

// Primero, ver qué modelos tienes disponibles
$listUrl = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $API_KEY;

echo "=== MODELOS DISPONIBLES ===\n";
$ch = curl_init($listUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$models = json_decode($response, true);
if (isset($models['models'])) {
    foreach ($models['models'] as $model) {
        echo "• " . $model['name'] . " - " . ($model['supportedGenerationMethods'] ? implode(', ', $model['supportedGenerationMethods']) : '') . "\n";
    }
} else {
    echo "No se pudieron listar los modelos. Error: " . ($models['error']['message'] ?? 'Desconocido') . "\n";
}

echo "\n=== PROBANDO gemini-pro ===\n";
$testUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $API_KEY;

$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => 'Responde solo: "¡Funciona! DNSBot está listo para ayudar"']
            ]
        ]
    ]
];

$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
?>