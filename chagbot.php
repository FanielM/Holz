<?php
// ── Sicherer Proxy für Anthropic API ──
// API-Key NUR hier auf dem Server – niemals im HTML/JS!

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://rhw-becker.de');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Nur POST erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// ── HIER Ihren API-Key eintragen ──
$API_KEY = 'sk-ant-HIER-IHREN-KEY-EINTRAGEN';

// Eingabe lesen und validieren
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Anfrage']);
    exit;
}

// Nur erlaubte Felder weitergeben (Sicherheit)
$payload = [
    'model'      => 'claude-sonnet-4-20250514',
    'max_tokens' => 600,
    'system'     => 'Du bist ein freundlicher Assistent der Rohrbacher Holzwerkstätte Becker GmbH in Heidelberg. Gegründet 1989 von Tischlermeister Michael Becker. Seit 2018 unterstützt von Sohn Maximilian Becker. Tel: 06221-315731. Mo–Do 7–16 Uhr, Fr 7–13:45 Uhr. Adresse: Brechtelstraße 15, 69126 Heidelberg. Mail: info@rhw-becker.de. Leistungen: Möbel nach Maß, Türen, Brand- & Rauchschutztüren, Theken, Parkett, Küchen, Restauration, Treppen, Sonderanfertigungen, CNC-Fertigung. Antworte auf Deutsch, professionell und freundlich.',
    'messages'   => array_slice($input['messages'], -20), // max. 20 Nachrichten
];

// API-Anfrage senden
$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $API_KEY,
        'anthropic-version: 2023-06-01',
    ],
    CURLOPT_TIMEOUT        => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Verbindungsfehler']);
    exit;
}

http_response_code($httpCode);
echo $response;
