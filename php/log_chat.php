<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

$session_id = $body['session_id'] ?? session_id() ?: bin2hex(random_bytes(16));
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ip = explode(',', $ip)[0]; // First IP if behind proxy
$style = $body['style'] ?? 'charlie';
$user_message = $body['message'] ?? '';
$bot_response = $body['response'] ?? '';

if (!$user_message || !$bot_response) {
    http_response_code(400);
    echo json_encode(['error' => 'message and response are required']);
    exit;
}

try {
    $stmt = db()->prepare(
        'INSERT INTO chats (session_id, ip, style, user_message, bot_response)
         VALUES (:session_id, :ip, :style, :user_message, :bot_response)'
    );
    $stmt->execute([
        ':session_id' => $session_id,
        ':ip' => $ip,
        ':style' => $style,
        ':user_message' => $user_message,
        ':bot_response' => $bot_response,
    ]);

    echo json_encode(['success' => true, 'id' => db()->lastInsertId()]);
}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
