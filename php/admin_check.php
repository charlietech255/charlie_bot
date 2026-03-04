<?php
require_once __DIR__ . '/config.php';

$token = $_GET['token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '');

if (!$token) {
    http_response_code(401);
    echo json_encode(['authenticated' => false, 'error' => 'No token provided']);
    exit;
}

try {
    $stmt = db()->prepare(
        'SELECT a.username, s.expires_at
           FROM admin_sessions s
           JOIN admins a ON a.id = s.admin_id
          WHERE s.token = ?
            AND s.expires_at > NOW()'
    );
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if ($row) {
        // Refresh the session expiry on each check
        db()->prepare(
            'UPDATE admin_sessions
                SET expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR)
              WHERE token = ?'
        )->execute([$token]);

        echo json_encode(['authenticated' => true, 'username' => $row['username']]);
    }
    else {
        http_response_code(401);
        echo json_encode(['authenticated' => false, 'error' => 'Session expired or invalid']);
    }
}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['authenticated' => false, 'error' => $e->getMessage()]);
}
