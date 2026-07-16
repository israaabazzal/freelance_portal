<?php
/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — api/invoices.php
   Actions: get_all | get_one | create | update | 
            update_status | delete
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

// Auto-flag overdue on every request
$pdo->exec("
    UPDATE invoices SET status = 'overdue'
    WHERE status = 'unpaid' AND due_date < CURDATE()
");

$data   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $data['action'] ?? '';

switch ($action) {

    /* ── GET ALL ─────────────────────────────────────────── */
    case 'get_all':
        $status_filter = $_GET['status'] ?? 'all';

        $sql = "
            SELECT
                i.invoice_id, i.invoice_no, i.status, i.total,
                i.due_date, i.issued_at, i.paid_at, i.notes,
                p.project_id, p.title AS project_title,
                u.name AS client_name, c.client_id
            FROM invoices i
            JOIN projects p ON p.project_id = i.project_id
            JOIN clients  c ON c.client_id  = p.client_id
            JOIN users    u ON u.user_id     = c.user_id
        ";

        if ($status_filter !== 'all') {
            $sql .= " WHERE i.status = " . $pdo->quote($status_filter);
        }

        $sql .= " ORDER BY i.issued_at DESC";

        echo json_encode($pdo->query($sql)->fetchAll());
        break;

    /* ── GET ONE ─────────────────────────────────────────── */
    case 'get_one':
        $id = intval($_GET['id'] ?? $data['invoice_id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing invoice_id']); exit; }

        // Invoice info
        $stmt = $pdo->prepare("
            SELECT
                i.*, p.title AS project_title,
                u.name AS client_name, c.client_id, c.company
            FROM invoices i
            JOIN projects p ON p.project_id = i.project_id
            JOIN clients  c ON c.client_id  = p.client_id
            JOIN users    u ON u.user_id     = c.user_id
            WHERE i.invoice_id = ?
        ");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();
        if (!$invoice) { echo json_encode(['error' => 'Invoice not found']); exit; }

        // Line items
        $stmt = $pdo->prepare("
            SELECT item_id, description, quantity, unit_price,
                   ROUND(quantity * unit_price, 2) AS subtotal
            FROM invoice_items
            WHERE invoice_id = ?
            ORDER BY item_id ASC
        ");
        $stmt->execute([$id]);
        $invoice['items'] = $stmt->fetchAll();

        echo json_encode($invoice);
        break;

    /* ── CREATE ──────────────────────────────────────────── */
    case 'create':
        $project_id = intval($data['project_id'] ?? 0);
        $due_date   = $data['due_date']           ?? null;
        $notes      = trim($data['notes']         ?? '');
        $items      = $data['items']              ?? [];

        if (!$project_id) {
            echo json_encode(['error' => 'Project is required']);
            exit;
        }

        if (empty($items)) {
            echo json_encode(['error' => 'At least one line item is required']);
            exit;
        }

        // Auto-generate invoice number: INV-2026-001
        $year = date('Y');
        $n    = $pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn() + 1;
        $invoice_no = 'INV-' . $year . '-' . str_pad($n, 3, '0', STR_PAD_LEFT);

        $due_date = $due_date ?: null;

        $pdo->prepare("
            INSERT INTO invoices (project_id, invoice_no, status, due_date, notes)
            VALUES (?, ?, 'draft', ?, ?)
        ")->execute([$project_id, $invoice_no, $due_date, $notes]);

        $invoice_id = $pdo->lastInsertId();

        // Insert line items
        $item_stmt = $pdo->prepare("
            INSERT INTO invoice_items (invoice_id, description, quantity, unit_price)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $desc       = trim($item['description'] ?? '');
            $quantity   = floatval($item['quantity']   ?? 1);
            $unit_price = floatval($item['unit_price'] ?? 0);
            if (!$desc || $unit_price <= 0) continue;
            $item_stmt->execute([$invoice_id, $desc, $quantity, $unit_price]);
        }

        // Recalculate total
        recalcTotal($pdo, $invoice_id);

        logActivity($pdo, $_SESSION['user_id'], 'Created invoice', 'invoice', $invoice_id, "Created {$invoice_no}");

        echo json_encode(['success' => true, 'invoice_id' => $invoice_id, 'invoice_no' => $invoice_no]);
        break;

    /* ── UPDATE ──────────────────────────────────────────── */
    case 'update':
        $id       = intval($data['invoice_id'] ?? 0);
        $due_date = $data['due_date']           ?? null;
        $notes    = trim($data['notes']         ?? '');
        $items    = $data['items']              ?? [];

        if (!$id) { echo json_encode(['error' => 'Missing invoice_id']); exit; }

        // Only draft invoices can be fully edited
        $stmt = $pdo->prepare("SELECT status FROM invoices WHERE invoice_id = ?");
        $stmt->execute([$id]);
        $inv = $stmt->fetch();

        if (!$inv) { echo json_encode(['error' => 'Invoice not found']); exit; }
        if ($inv['status'] !== 'draft') {
            echo json_encode(['error' => 'Only draft invoices can be edited']);
            exit;
        }

        $due_date = $due_date ?: null;

        $pdo->prepare("
            UPDATE invoices SET due_date = ?, notes = ? WHERE invoice_id = ?
        ")->execute([$due_date, $notes, $id]);

        // Replace all line items
        $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")
            ->execute([$id]);

        $item_stmt = $pdo->prepare("
            INSERT INTO invoice_items (invoice_id, description, quantity, unit_price)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $desc       = trim($item['description'] ?? '');
            $quantity   = floatval($item['quantity']   ?? 1);
            $unit_price = floatval($item['unit_price'] ?? 0);
            if (!$desc || $unit_price <= 0) continue;
            $item_stmt->execute([$id, $desc, $quantity, $unit_price]);
        }

        recalcTotal($pdo, $id);

        logActivity($pdo, $_SESSION['user_id'], 'Updated invoice', 'invoice', $id, '');

        echo json_encode(['success' => true]);
        break;

    /* ── UPDATE STATUS ───────────────────────────────────── */
    case 'update_status':
        $id     = intval($data['invoice_id'] ?? 0);
        $status = $data['status']            ?? '';

        $valid = ['draft', 'unpaid', 'paid', 'overdue'];
        if (!$id || !in_array($status, $valid)) {
            echo json_encode(['error' => 'Invalid data']);
            exit;
        }

        $paid_at = $status === 'paid' ? date('Y-m-d H:i:s') : null;

        $pdo->prepare("
            UPDATE invoices SET status = ?, paid_at = ? WHERE invoice_id = ?
        ")->execute([$status, $paid_at, $id]);

        logActivity($pdo, $_SESSION['user_id'], 'Updated invoice status', 'invoice', $id, "Status → {$status}");

        echo json_encode(['success' => true]);
        break;

    /* ── DELETE (draft only) ─────────────────────────────── */
    case 'delete':
        $id = intval($data['invoice_id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing invoice_id']); exit; }

        $stmt = $pdo->prepare("SELECT status, invoice_no FROM invoices WHERE invoice_id = ?");
        $stmt->execute([$id]);
        $inv = $stmt->fetch();

        if (!$inv) { echo json_encode(['error' => 'Invoice not found']); exit; }

        if ($inv['status'] !== 'draft') {
            echo json_encode(['error' => 'Only draft invoices can be deleted']);
            exit;
        }

        $pdo->prepare("DELETE FROM invoices WHERE invoice_id = ?")
            ->execute([$id]);

        logActivity($pdo, $_SESSION['user_id'], 'Deleted invoice', 'invoice', $id, "Deleted {$inv['invoice_no']}");

        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}


/* ── Helpers ─────────────────────────────────────────────── */
function recalcTotal($pdo, $invoice_id) {
    $pdo->prepare("
        UPDATE invoices SET total = (
            SELECT COALESCE(SUM(quantity * unit_price), 0)
            FROM invoice_items WHERE invoice_id = ?
        ) WHERE invoice_id = ?
    ")->execute([$invoice_id, $invoice_id]);
}

function logActivity($pdo, $user_id, $action, $entity_type, $entity_id, $detail) {
    $pdo->prepare("
        INSERT INTO activity_log (user_id, action, entity_type, entity_id, detail)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([$user_id, $action, $entity_type, $entity_id, $detail]);
}
?>