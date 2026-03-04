<?php
require_once __DIR__ . '/config.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// 1. Validate Session Token
$token = $_GET['token'] ?? ($_SERVER['HTTP_X_ADMIN_TOKEN'] ?? '');
if (!$token) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $pdo = db();

    // Check if session is valid and get admin_id
    $stmt = $pdo->prepare('SELECT admin_id FROM admin_sessions WHERE token = ? AND expires_at > NOW()');
    $stmt->execute([$token]);
    $session = $stmt->fetch();

    if (!$session) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Session expired or invalid']);
        exit;
    }

    $adminId = $session['admin_id'];

    // 2. Get Input
    $body = json_decode(file_get_contents('php://input'), true);
    $currentPass = $body['current_password'] ?? '';
    $newPass = $body['new_password'] ?? '';

    if (!$currentPass || !$newPass) {
        echo json_encode(['success' => false, 'error' => 'Missing password information']);
        exit;
    }

    if (strlen($newPass) < 6) {
        echo json_encode(['success' => false, 'error' => 'New password must be at least 6 characters']);
        exit;
    }

    // 3. Verify Current Password
    $stmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?');
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($currentPass, $admin['password_hash'])) {
        echo json_encode(['success' => false, 'error' => 'Current password incorrect']);
        exit;
    }

    // 4. Update Password
    $newHash = password_hash($newPass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
    $stmt->execute([$newHash, $adminId]);

    // Optional: Revoke other sessions or just return success
    // For simplicity, we just return success.
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);

}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
