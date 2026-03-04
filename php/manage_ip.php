<?php
require_once __DIR__ . '/config.php';

// GET → return blocked IP list
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $rows = db()->query(
            'SELECT id, ip, reason, blocked_at FROM blocked_ips ORDER BY blocked_at DESC'
        )->fetchAll();
        echo json_encode(['blocked' => $rows]);
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// POST → block or unblock an IP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    $action = $body['action'] ?? '';
    $ip = trim($body['ip'] ?? '');
    $reason = trim($body['reason'] ?? 'Manual block');

    if (!$ip) {
        http_response_code(400);
        echo json_encode(['error' => 'IP is required']);
        exit;
    }

    try {
        if ($action === 'block') {
            $stmt = db()->prepare(
                'INSERT INTO blocked_ips (ip, reason) VALUES (:ip, :reason)
                 ON DUPLICATE KEY UPDATE reason = VALUES(reason), blocked_at = NOW()'
            );
            $stmt->execute([':ip' => $ip, ':reason' => $reason]);
            echo json_encode(['success' => true, 'action' => 'blocked', 'ip' => $ip]);
        }
        elseif ($action === 'unblock') {
            $stmt = db()->prepare('DELETE FROM blocked_ips WHERE ip = :ip');
            $stmt->execute([':ip' => $ip]);
            echo json_encode(['success' => true, 'action' => 'unblocked', 'ip' => $ip]);
        }
        else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action. Use "block" or "unblock"']);
        }
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
