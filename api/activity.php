<?php
/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — api/activity.php
   Actions: get_recent | log
   ═══════════════════════════════════════════════════════════ */

header('Content-Type: application/json');
session_start();
require 'db.php';

// Auth guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'freelancer') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $data['action'] ?? '';

switch ($action) {

    /* ── GET RECENT ──────────────────────────────────────── */
    case 'get_recent':
        $limit = intval($_GET['limit'] ?? 10);
        if ($limit > 50) $limit = 50; // cap at 50

        $stmt = $pdo->prepare("
            SELECT
                a.log_id, a.action, a.entity_type,
                a.entity_id, a.detail, a.logged_at,
                u.name, u.role
            FROM activity_log a
            JOIN users u ON u.user_id = a.user_id
            ORDER BY a.logged_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        echo json_encode($stmt->fetchAll());
        break;

    /* ── LOG ─────────────────────────────────────────────── */
    case 'log':
        $action_text  = trim($data['action_text']  ?? '');
        $entity_type  = trim($data['entity_type']  ?? '');
        $entity_id    = intval($data['entity_id']  ?? 0);
        $detail       = trim($data['detail']       ?? '');

        if (!$action_text) {
            echo json_encode(['error' => 'action_text is required']);
            exit;
        }

        $pdo->prepare("
            INSERT INTO activity_log (user_id, action, entity_type, entity_id, detail)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([
            $_SESSION['user_id'],
            $action_text,
            $entity_type  ?: null,
            $entity_id    ?: null,
            $detail       ?: null,
        ]);

        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}
?>