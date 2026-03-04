<?php
require_once __DIR__ . '/config.php';

// GET: return all/searched logs
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $q = '%' . ($_GET['q'] ?? '') . '%';
    $limit = (int)($_GET['limit'] ?? 300);
    $limit = max(1, min($limit, 2000));

    try {
        // FIX: PDO MySQL does NOT support reusing the same named param.
        // Use positional ? params instead.
        $stmt = db()->prepare(
            'SELECT id, session_id, ip, style, user_message, bot_response, created_at
               FROM chats
              WHERE user_message LIKE ?
                 OR bot_response  LIKE ?
                 OR ip             LIKE ?
                 OR style          LIKE ?
              ORDER BY created_at DESC
              LIMIT ?'
        );
        $stmt->execute([$q, $q, $q, $q, $limit]);
        $rows = $stmt->fetchAll();

        $blocked = db()->query('SELECT ip FROM blocked_ips')->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['logs' => $rows, 'blocked' => $blocked]);
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage(), 'logs' => [], 'blocked' => []]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
