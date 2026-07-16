<?php
/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — api/projects.php
   Actions: get_all | get_one | create | update | delete
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

    /* ── GET ALL ─────────────────────────────────────────── */
    case 'get_all':
        $stmt = $pdo->query("
            SELECT
                p.project_id, p.title, p.description, p.status,
                p.deadline, p.budget, p.cover_color, p.emoji,
                p.created_at, p.updated_at,
                c.client_id, c.tag_color,
                u.name AS client_name,
                COUNT(t.task_id)                                                AS total_tasks,
                COALESCE(SUM(t.is_done), 0)                                     AS done_tasks,
                IFNULL(ROUND(SUM(t.is_done) / NULLIF(COUNT(t.task_id), 0) * 100), 0) AS pct
            FROM projects p
            JOIN clients c ON c.client_id = p.client_id
            JOIN users   u ON u.user_id   = c.user_id
            LEFT JOIN tasks t ON t.project_id = p.project_id
            GROUP BY p.project_id
            ORDER BY p.updated_at DESC
        ");
        echo json_encode($stmt->fetchAll());
        break;

    /* ── GET ONE ─────────────────────────────────────────── */
    case 'get_one':
        $id = intval($_GET['id'] ?? $data['project_id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing project_id']); exit; }

        // Project info
        $stmt = $pdo->prepare("
            SELECT
                p.*, u.name AS client_name, c.tag_color, c.client_id,
                COUNT(t.task_id)                                                AS total_tasks,
                COALESCE(SUM(t.is_done), 0)                                     AS done_tasks,
                IFNULL(ROUND(SUM(t.is_done) / NULLIF(COUNT(t.task_id), 0) * 100), 0) AS pct
            FROM projects p
            JOIN clients c ON c.client_id = p.client_id
            JOIN users   u ON u.user_id   = c.user_id
            LEFT JOIN tasks t ON t.project_id = p.project_id
            WHERE p.project_id = ?
            GROUP BY p.project_id
        ");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        if (!$project) { echo json_encode(['error' => 'Project not found']); exit; }

        // Tasks grouped by column
        $stmt = $pdo->prepare("
            SELECT task_id, title, description, priority,
                   column_name, due_date, is_done, position
            FROM tasks
            WHERE project_id = ?
            ORDER BY position ASC, created_at ASC
        ");
        $stmt->execute([$id]);
        $tasks = $stmt->fetchAll();

        // Group tasks into kanban columns
        $project['tasks'] = [
            'todo'        => array_values(array_filter($tasks, fn($t) => $t['column_name'] === 'todo')),
            'in_progress' => array_values(array_filter($tasks, fn($t) => $t['column_name'] === 'in_progress')),
            'done'        => array_values(array_filter($tasks, fn($t) => $t['column_name'] === 'done')),
        ];

        // Invoices for this project
        $stmt = $pdo->prepare("
            SELECT invoice_id, invoice_no, status, total, due_date, issued_at
            FROM invoices
            WHERE project_id = ?
            ORDER BY issued_at DESC
        ");
        $stmt->execute([$id]);
        $project['invoices'] = $stmt->fetchAll();

        echo json_encode($project);
        break;

    /* ── CREATE ──────────────────────────────────────────── */
    case 'create':
        $client_id   = intval($data['client_id']   ?? 0);
        $title       = trim($data['title']         ?? '');
        $description = trim($data['description']   ?? '');
        $status      = $data['status']             ?? 'draft';
        $deadline    = $data['deadline']            ?? null;
        $budget      = floatval($data['budget']    ?? 0);
        $cover_color = trim($data['cover_color']   ?? '#FFCBB4');
        $emoji       = trim($data['emoji']         ?? 'folder');

        if (!$client_id || !$title) {
            echo json_encode(['error' => 'Client and title are required']);
            exit;
        }

        $valid_statuses = ['draft', 'in_progress', 'review', 'completed', 'cancelled'];
        if (!in_array($status, $valid_statuses)) $status = 'draft';

        $deadline = $deadline ?: null;

        $pdo->prepare("
            INSERT INTO projects
                (client_id, title, description, status, deadline, budget, cover_color, emoji)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([$client_id, $title, $description, $status, $deadline, $budget, $cover_color, $emoji]);

        $project_id = $pdo->lastInsertId();

        logActivity($pdo, $_SESSION['user_id'], 'Created project', 'project', $project_id, "Created \"{$title}\"");

        echo json_encode(['success' => true, 'project_id' => $project_id]);
        break;

    /* ── UPDATE ──────────────────────────────────────────── */
    case 'update':
        $id          = intval($data['project_id']  ?? 0);
        $title       = trim($data['title']         ?? '');
        $description = trim($data['description']   ?? '');
        $status      = $data['status']             ?? 'draft';
        $deadline    = $data['deadline']            ?? null;
        $budget      = floatval($data['budget']    ?? 0);
        $cover_color = trim($data['cover_color']   ?? '#FFCBB4');
        $emoji       = trim($data['emoji']         ?? 'folder');

        if (!$id || !$title) {
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $valid_statuses = ['draft', 'in_progress', 'review', 'completed', 'cancelled'];
        if (!in_array($status, $valid_statuses)) $status = 'draft';

        $deadline = $deadline ?: null;

        $pdo->prepare("
            UPDATE projects
            SET title = ?, description = ?, status = ?, deadline = ?,
                budget = ?, cover_color = ?, emoji = ?
            WHERE project_id = ?
        ")->execute([$title, $description, $status, $deadline, $budget, $cover_color, $emoji, $id]);

        logActivity($pdo, $_SESSION['user_id'], 'Updated project', 'project', $id, "Updated \"{$title}\"");

        echo json_encode(['success' => true]);
        break;

    /* ── UPDATE STATUS ONLY (Kanban drag-drop) ───────────── */
    case 'update_status':
        $id     = intval($data['project_id'] ?? 0);
        $status = $data['status']            ?? '';

        $valid_statuses = ['draft', 'in_progress', 'review', 'completed', 'cancelled'];
        if (!$id || !in_array($status, $valid_statuses)) {
            echo json_encode(['error' => 'Invalid data']);
            exit;
        }

        $pdo->prepare("UPDATE projects SET status = ? WHERE project_id = ?")
            ->execute([$status, $id]);

        logActivity($pdo, $_SESSION['user_id'], 'Changed project status', 'project', $id, "Status → {$status}");

        echo json_encode(['success' => true]);
        break;

    /* ── DELETE ──────────────────────────────────────────── */
    case 'delete':
        $id = intval($data['project_id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing project_id']); exit; }

        // Get title for log before deleting
        $stmt = $pdo->prepare("SELECT title FROM projects WHERE project_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        $pdo->prepare("DELETE FROM projects WHERE project_id = ?")
            ->execute([$id]);

        logActivity($pdo, $_SESSION['user_id'], 'Deleted project', 'project', $id, "Deleted \"{$row['title']}\"");

        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}


/* ── Activity Logger ─────────────────────────────────────── */
function logActivity($pdo, $user_id, $action, $entity_type, $entity_id, $detail) {
    $pdo->prepare("
        INSERT INTO activity_log (user_id, action, entity_type, entity_id, detail)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([$user_id, $action, $entity_type, $entity_id, $detail]);
}
?>