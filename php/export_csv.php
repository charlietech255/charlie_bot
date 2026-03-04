<?php
require_once __DIR__ . '/config.php';

try {
    $rows = db()->query(
        'SELECT id, session_id, ip, style, user_message, bot_response, created_at
           FROM chats ORDER BY created_at DESC'
    )->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="charlie_chat_logs_' . date('Y-m-d') . '.csv"');

    $out = fopen('php://output', 'w');
    // BOM for Excel UTF-8 compatibility
    fwrite($out, "\xEF\xBB\xBF");

    fputcsv($out, ['ID', 'Session ID', 'IP Address', 'Style', 'User Message', 'Bot Response', 'Timestamp']);
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['id'],
            $r['session_id'],
            $r['ip'],
            $r['style'],
            $r['user_message'],
            $r['bot_response'],
            $r['created_at'],
        ]);
    }
    fclose($out);
}
catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
