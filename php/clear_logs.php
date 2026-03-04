<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $deleted = db()->exec('DELETE FROM chats');
    // Reset auto_increment for tidiness
    db()->exec('ALTER TABLE chats AUTO_INCREMENT = 1');
    echo json_encode(['success' => true, 'deleted' => $deleted]);
}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
