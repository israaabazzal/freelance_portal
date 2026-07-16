<?php
/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — api/tasks.php
   Actions: get_by_project | create | update | update_column
            update_position | toggle_done | delete
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

    /* ── GET BY PROJECT ──────────────────────────────────── */
    case 'get_by_project':
        $project_id = intval($_GET['project_id'] ?? $data['project_id'] ?? 0);
        if (!$project_id) { echo json_encode(['error' => 'Missing project_id']); exit; }

        $stmt = $pdo->prepare("
            SELECT task_id, title, description, priority,
                   column_name, due_date, is_done, position, created_at
            FROM tasks
            WHERE project_id = ?
            ORDER BY position ASC, created_at ASC
        ");
        $stmt->execute([$project_id]);
        $tasks = $stmt->fetchAll();

        // Return grouped by column
        echo json_encode([
            'todo'        => array_values(array_filter($tasks, fn($t) => $t['column_name'] === 'todo')),
            'in_progress' => array_values(array_filter($tasks, fn($t) => $t['column_name'] === 'in_progress')),
            'done'        => array_values(array_filter($tasks, fn($t) => $t['column_name'] === 'done')),
        ]);
        break;

    /* ── CREATE ──────────────────────────────────────────── */
    case 'create':
        $project_id  = intval($data['project_id']  ?? 0);
        $title       = trim($data['title']         ?? '');
        $description = trim($data['description']   ?? '');
        $priority    = $data['priority']            ?? 'medium';
        $column_name = $data['column_name']         ?? 'todo';
        $due_date    = $data['due_date']            ?? null;

        if (!$project_id || !$title) {
            echo json_encode(['error' => 'Project and title are required']);
            exit;
        }

        $valid_priorities = ['low', 'medium', 'high'];
        $valid_columns    = ['todo', 'in_progress', 'done'];
        if (!in_array($priority, $valid_priorities))    $priority    = 'medium';
        if (!in_array($column_name, $valid_columns))    $column_name = 'todo';

        $due_date = $due_date ?: null;

        // Get max position in that column so new task goes to the bottom
        $stmt = $pdo->prepare("
            SELECT COALESCE(MAX(position), -1) + 1
            FROM tasks
            WHERE project_id = ? AND column_name = ?
        ");
        $stmt->execute([$project_id, $column_name]);
        $position = (int) $stmt->fetchColumn();

        $pdo->prepare("
            INSERT INTO tasks
                (project_id, title, description, priority, column_name, due_date, position)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([$project_id, $title, $description, $priority, $column_name, $due_date, $position]);

        $task_id = $pdo->lastInsertId();

        logActivity($pdo, $_SESSION['user_id'], 'Created task', 'task', $task_id, "Added \"{$title}\"");

        echo json_encode(['success' => true, 'task_id' => $task_id, 'position' => $position]);
        break;

    /* ── UPDATE ──────────────────────────────────────────── */
    case 'update':
        $id          = intval($data['task_id']     ?? 0);
        $title       = trim($data['title']         ?? '');
        $description = trim($data['description']   ?? '');
        $priority    = $data['priority']            ?? 'medium';
        $due_date    = $data['due_date']            ?? null;

        if (!$id || !$title) {
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        $valid_priorities = ['low', 'medium', 'high'];
        if (!in_array($priority, $valid_priorities)) $priority = 'medium';

        $due_date = $due_date ?: null;

        $pdo->prepare("
            UPDATE tasks
            SET title = ?, description = ?, priority = ?, due_date = ?
            WHERE task_id = ?
        ")->execute([$title, $description, $priority, $due_date, $id]);

        logActivity($pdo, $_SESSION['user_id'], 'Updated task', 'task', $id, "Updated \"{$title}\"");

        echo json_encode(['success' => true]);
        break;

    /* ── UPDATE COLUMN (drag between kanban columns) ─────── */
    case 'update_column':
        $id          = intval($data['task_id']     ?? 0);
        $column_name = $data['column_name']         ?? '';
        $position    = intval($data['position']    ?? 0);

        $valid_columns = ['todo', 'in_progress', 'done'];
        if (!$id || !in_array($column_name, $valid_columns)) {
            echo json_encode(['error' => 'Invalid data']);
            exit;
        }

        $pdo->prepare("
            UPDATE tasks SET column_name = ?, position = ?, is_done = ?
            WHERE task_id = ?
        ")->execute([
            $column_name,
            $position,
            $column_name === 'done' ? 1 : 0,  // auto-mark done when moved to done column
            $id
        ]);

        logActivity($pdo, $_SESSION['user_id'], 'Moved task', 'task', $id, "→ {$column_name}");

        echo json_encode(['success' => true]);
        break;

    /* ── UPDATE POSITIONS (reorder within a column) ──────── */
    // Expects: { tasks: [ {task_id: 1, position: 0}, {task_id: 2, position: 1}, ... ] }
    case 'update_positions':
        $tasks = $data['tasks'] ?? [];
        if (empty($tasks)) { echo json_encode(['error' => 'No tasks provided']); exit; }

        $stmt = $pdo->prepare("UPDATE tasks SET position = ? WHERE task_id = ?");
        foreach ($tasks as $t) {
            $stmt->execute([intval($t['position']), intval($t['task_id'])]);
        }

        echo json_encode(['success' => true]);
        break;

    /* ── TOGGLE DONE ─────────────────────────────────────── */
    case 'toggle_done':
        $id = intval($data['task_id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing task_id']); exit; }

        // Flip is_done and sync column_name
        $stmt = $pdo->prepare("SELECT is_done, title FROM tasks WHERE task_id = ?");
        $stmt->execute([$id]);
        $task = $stmt->fetch();

        if (!$task) { echo json_encode(['error' => 'Task not found']); exit; }

        $new_done   = $task['is_done'] ? 0 : 1;
        $new_column = $new_done ? 'done' : 'todo';

        $pdo->prepare("
            UPDATE tasks SET is_done = ?, column_name = ? WHERE task_id = ?
        ")->execute([$new_done, $new_column, $id]);

        $action_label = $new_done ? 'Completed task' : 'Reopened task';
        logActivity($pdo, $_SESSION['user_id'], $action_label, 'task', $id, "\"{$task['title']}\"");

        echo json_encode(['success' => true, 'is_done' => $new_done, 'column_name' => $new_column]);
        break;

    /* ── DELETE ──────────────────────────────────────────── */
    case 'delete':
        $id = intval($data['task_id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing task_id']); exit; }

        $stmt = $pdo->prepare("SELECT title FROM tasks WHERE task_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        $pdo->prepare("DELETE FROM tasks WHERE task_id = ?")
            ->execute([$id]);

        logActivity($pdo, $_SESSION['user_id'], 'Deleted task', 'task', $id, "Deleted \"{$row['title']}\"");

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