<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = db();

    // Total chat count
    $total = (int)$pdo->query('SELECT COUNT(*) FROM chats')->fetchColumn();

    // Today's count
    $today = (int)$pdo->query("SELECT COUNT(*) FROM chats WHERE DATE(created_at) = CURDATE()")->fetchColumn();

    // Unique users (distinct IPs)
    $unique_users = (int)$pdo->query('SELECT COUNT(DISTINCT ip) FROM chats')->fetchColumn();

    // Blocked IPs count
    $blocked_count = (int)$pdo->query('SELECT COUNT(*) FROM blocked_ips')->fetchColumn();

    // Style distribution
    $styleRows = $pdo->query(
        'SELECT style, COUNT(*) AS cnt FROM chats GROUP BY style ORDER BY cnt DESC LIMIT 10'
    )->fetchAll();

    // Hourly distribution (last 24 hours, index 0 = midnight)
    $hourlyRows = $pdo->query(
        'SELECT HOUR(created_at) AS h, COUNT(*) AS cnt
           FROM chats
          WHERE created_at >= NOW() - INTERVAL 24 HOUR
          GROUP BY h'
    )->fetchAll();
    $hourly = array_fill(0, 24, 0);
    foreach ($hourlyRows as $r) {
        $hourly[(int)$r['h']] = (int)$r['cnt'];
    }

    // Daily distribution (last 7 days)
    $dailyRows = $pdo->query(
        'SELECT DATE(created_at) AS day, COUNT(*) AS cnt
           FROM chats
          WHERE created_at >= CURDATE() - INTERVAL 6 DAY
          GROUP BY day
          ORDER BY day ASC'
    )->fetchAll();

    // Recent 5 conversations
    $recentRows = $pdo->query(
        'SELECT ip, style, user_message, created_at
           FROM chats
          ORDER BY created_at DESC
          LIMIT 5'
    )->fetchAll();

    echo json_encode([
        'total' => $total,
        'today' => $today,
        'unique_users' => $unique_users,
        'blocked_count' => $blocked_count,
        'styles' => array_map(fn($r) => ['style' => $r['style'], 'cnt' => (int)$r['cnt']], $styleRows),
        'hourly' => $hourly,
        'daily' => array_map(fn($r) => ['day' => $r['day'], 'cnt' => (int)$r['cnt']], $dailyRows),
        'recent' => $recentRows,
    ]);

}
catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
