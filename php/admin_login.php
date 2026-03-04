<?php
require_once __DIR__ . '/config.php';

// ── Auto-create default admin if table is empty ───────────────
function ensureDefaultAdmin()
{
    $count = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    if ($count === 0) {
        $hash = password_hash(ADMIN_PASS, PASSWORD_DEFAULT);
        $stmt = db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
        $stmt->execute([ADMIN_USER, $hash]);
    }
}

// ── POST: Login ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    $username = trim($body['username'] ?? '');
    $password = $body['password'] ?? '';

    try {
        ensureDefaultAdmin();

        $stmt = db()->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Update last login
            db()->prepare('UPDATE admins SET last_login = NOW() WHERE id = ?')
                ->execute([$admin['id']]);

            // Set session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_user'] = $username;
            $_SESSION['logged_in'] = true;

            // Clean up expired sessions from DB
            db()->exec('DELETE FROM admin_sessions WHERE expires_at < NOW()');

            // Store session token in DB for cross-request validation
            $token = bin2hex(random_bytes(32));
            $stmt2 = db()->prepare(
                'INSERT INTO admin_sessions (token, admin_id, expires_at)
                 VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
            );
            $stmt2->execute([$token, $admin['id']]);

            echo json_encode(['success' => true, 'token' => $token, 'username' => $username]);
        }
        else {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
        }
    }
    catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
