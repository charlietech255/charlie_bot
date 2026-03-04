<?php
require_once __DIR__ . '/config.php';

$body = json_decode(file_get_contents('php://input'), true);
$token = $body['token'] ?? '';

if ($token) {
    try {
        db()->prepare('DELETE FROM admin_sessions WHERE token = ?')->execute([$token]);
    }
    catch (PDOException $e) {
    // silently ignore
    }
}

session_destroy();
echo json_encode(['success' => true]);
