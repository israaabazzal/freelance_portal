<?php
/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — api/clients.php
   Actions: get_all | get_one | create | update | delete
   ═══════════════════════════════════════════════════════════ */

header('Content-Type: application/json');
session_start();
require 'db.php';

// Auth 
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
                u.name,
                u.email,
                c.client_id,
                c.company,
                c.phone,
                c.tag_color,
                c.notes,
                c.is_active,
                COUNT(DISTINCT p.project_id)                                AS projects,
                COALESCE(SUM(i.total), 0)                                   AS invoiced,
                COALESCE(SUM(CASE WHEN i.status = 'paid' THEN i.total END), 0) AS paid
            FROM clients c
            JOIN users u ON u.user_id = c.user_id
            LEFT JOIN projects p ON p.client_id = c.client_id
            LEFT JOIN invoices i ON i.project_id = p.project_id
            WHERE c.is_active = 1
            GROUP BY c.client_id
            ORDER BY projects DESC
        ");
        echo json_encode($stmt->fetchAll());
        break;

    /* ── GET ONE ─────────────────────────────────────────── */
    case 'get_one':
        $id = intval($_GET['id'] ?? $data['client_id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing client_id']); exit; }

        // Client info
        $stmt = $pdo->prepare("
            SELECT u.name, u.email, u.gender,
                   c.client_id, c.company, c.phone, c.tag_color, c.notes, c.is_active
            FROM clients c
            JOIN users u ON u.user_id = c.user_id
            WHERE c.client_id = ?
        ");
        $stmt->execute([$id]);
        $client = $stmt->fetch();
        if (!$client) { echo json_encode(['error' => 'Client not found']); exit; }

        // Their projects
        $stmt = $pdo->prepare("
            SELECT p.project_id, p.title, p.status, p.deadline, p.budget,
                   p.cover_color, p.emoji,
                   IFNULL(ROUND(SUM(t.is_done) / NULLIF(COUNT(t.task_id), 0) * 100), 0) AS pct
            FROM projects p
            LEFT JOIN tasks t ON t.project_id = p.project_id
            WHERE p.client_id = ?
            GROUP BY p.project_id
            ORDER BY p.updated_at DESC
        ");
        $stmt->execute([$id]);
        $client['projects'] = $stmt->fetchAll();

        // Their invoices
        $stmt = $pdo->prepare("
            SELECT i.invoice_id, i.invoice_no, i.status, i.total, i.due_date, i.issued_at
            FROM invoices i
            JOIN projects p ON p.project_id = i.project_id
            WHERE p.client_id = ?
            ORDER BY i.issued_at DESC
        ");
        $stmt->execute([$id]);
        $client['invoices'] = $stmt->fetchAll();

        echo json_encode($client);
        break;

    /* ── CREATE ──────────────────────────────────────────── */
    case 'create':
        $name      = trim($data['name']      ?? '');
        $email     = trim($data['email']     ?? '');
        $company   = trim($data['company']   ?? '');
        $phone     = trim($data['phone']     ?? '');
        $tag_color = trim($data['tag_color'] ?? '#C9C2F0');
        $notes     = trim($data['notes']     ?? '');
        $gender    = ($data['gender'] ?? 'female') === 'male' ? 'male' : 'female';

        if (!$name || !$email) {
            echo json_encode(['error' => 'Name and email are required']);
            exit;
        }

        // Check email not already taken
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            echo json_encode(['error' => 'Email already in use']);
            exit;
        }

        // Auto-generate a temp password
        $temp_password = bin2hex(random_bytes(5)); // e.g. "a3f9c12b4e"
        $hash          = password_hash($temp_password, PASSWORD_DEFAULT);

        // Insert into users then clients
        $pdo->prepare("
            INSERT INTO users (name, email, password, role, gender)
            VALUES (?, ?, ?, 'client', ?)
        ")->execute([$name, $email, $hash, $gender]);

        $user_id = $pdo->lastInsertId();

        $pdo->prepare("
            INSERT INTO clients (user_id, company, phone, tag_color, notes)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([$user_id, $company, $phone, $tag_color, $notes]);

        $client_id = $pdo->lastInsertId();

        // Log activity
        logActivity($pdo, $_SESSION['user_id'], 'Created client', 'client', $client_id, "Added {$name}");

        echo json_encode([
            'success'       => true,
            'client_id'     => $client_id,
            'temp_password' => $temp_password  // shown once in the panel
        ]);
        break;

    /* ── UPDATE ──────────────────────────────────────────── */
    case 'update':
        $id        = intval($data['client_id'] ?? 0);
        $name      = trim($data['name']        ?? '');
        $company   = trim($data['company']     ?? '');
        $phone     = trim($data['phone']       ?? '');
        $tag_color = trim($data['tag_color']   ?? '#C9C2F0');
        $notes     = trim($data['notes']       ?? '');

        if (!$id || !$name) {
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        // Update display name in users
        $pdo->prepare("
            UPDATE users u
            JOIN clients c ON c.user_id = u.user_id
            SET u.name = ?
            WHERE c.client_id = ?
        ")->execute([$name, $id]);

        // Update client profile
        $pdo->prepare("
            UPDATE clients
            SET company = ?, phone = ?, tag_color = ?, notes = ?
            WHERE client_id = ?
        ")->execute([$company, $phone, $tag_color, $notes, $id]);

        logActivity($pdo, $_SESSION['user_id'], 'Updated client', 'client', $id, "Updated {$name}");

        echo json_encode(['success' => true]);
        break;

    /* ── ARCHIVE (soft delete) ───────────────────────────── */
    case 'archive':
        $id = intval($data['client_id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing client_id']); exit; }

        $pdo->prepare("UPDATE clients SET is_active = 0 WHERE client_id = ?")
            ->execute([$id]);

        logActivity($pdo, $_SESSION['user_id'], 'Archived client', 'client', $id, '');

        echo json_encode(['success' => true]);
        break;

    /* ── DELETE (hard delete — cascades everything) ──────── */
    case 'delete':
        $id = intval($data['client_id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing client_id']); exit; }

        // Get user_id first (cascade will handle projects/tasks/invoices)
        $stmt = $pdo->prepare("SELECT user_id FROM clients WHERE client_id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { echo json_encode(['error' => 'Client not found']); exit; }

        $pdo->prepare("DELETE FROM users WHERE user_id = ?")
            ->execute([$row['user_id']]);

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