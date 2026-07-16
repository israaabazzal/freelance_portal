<?php
/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — invoices.php
   ═══════════════════════════════════════════════════════════ */
session_start();
if (!isset($_SESSION['user_id']))       { header('Location: login.php'); exit; }
if ($_SESSION['role'] !== 'freelancer') { header('Location: client-view.php'); exit; }

require 'api/db.php';

$gender = $_SESSION['gender'] ?? 'female';
$mascot = "images/mascot/sidebar-{$gender}.png";

// Auto-flag overdue
$pdo->exec("UPDATE invoices SET status='overdue' WHERE status='unpaid' AND due_date < CURDATE()");

// Fetch all invoices
$invoices = $pdo->query("
    SELECT
        i.invoice_id, i.invoice_no, i.status, i.total,
        i.due_date, i.issued_at, i.notes,
        p.title AS project_title, p.project_id,
        u.name  AS client_name,  c.client_id
    FROM invoices i
    JOIN projects p ON p.project_id = i.project_id
    JOIN clients  c ON c.client_id  = p.client_id
    JOIN users    u ON u.user_id    = c.user_id
    ORDER BY i.issued_at DESC
")->fetchAll();

// Fetch projects for "New Invoice" dropdown
$projects = $pdo->query("
    SELECT p.project_id, p.title, u.name AS client_name
    FROM projects p
    JOIN clients c ON c.client_id = p.client_id
    JOIN users   u ON u.user_id   = c.user_id
    WHERE p.status != 'cancelled'
    ORDER BY p.title ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices — Freelance Portal</title>
    <link href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
<div class="app-layout">

    <!-- ── Sidebar ── -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="images/logo.png" alt="logo">
            <span class="sidebar-logo-text">Freelance Portal</span>
        </div>
        <span class="nav-label">Menu</span>
        <a href="dashboard.php" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="clients.php" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Clients
        </a>
        <a href="projects.php" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            Projects
        </a>
        <a href="invoices.php" class="nav-link active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            Invoices
        </a>
        <div class="sidebar-mascot">
            <img src="<?= htmlspecialchars($mascot) ?>" alt="mascot" class="sidebar-mascot-img">
        </div>
        <a href="logout.php" class="sidebar-logout">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Logout
        </a>
    </aside>

    <!-- ── Main Content ── -->
    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>Invoices</h1>
                <p class="page-subtitle"><?= count($invoices) ?> invoice<?= count($invoices) !== 1 ? 's' : '' ?> total</p>
            </div>
            <button class="btn btn-primary" onclick="openNewInvoicePanel()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Invoice
            </button>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-bar">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="draft">Draft</button>
            <button class="filter-btn" data-filter="unpaid">Unpaid</button>
            <button class="filter-btn" data-filter="paid">Paid</button>
            <button class="filter-btn" data-filter="overdue">Overdue</button>
        </div>

        <!-- Invoices Grid -->
        <div class="invoices-grid" id="invoicesGrid">
            <?php if (empty($invoices)): ?>
            <div class="empty-state" style="grid-column:1/-1;">
                <div class="empty-state-icon">🧾</div>
                <h3>No invoices yet</h3>
                <p>Create your first invoice to get started.</p>
            </div>
            <?php else: ?>
            <?php foreach ($invoices as $inv): ?>
            <div class="invoice-card <?= $inv['status'] ?>" data-status="<?= $inv['status'] ?>">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                    <div>
                        <div class="invoice-amount">$<?= number_format($inv['total'], 2) ?></div>
                        <div class="invoice-no"><?= htmlspecialchars($inv['invoice_no']) ?></div>
                    </div>
                    <?= statusBadgePhp($inv['status']) ?>
                </div>
                <div style="font-size:13px;font-weight:500;color:var(--text);margin-bottom:3px;">
                    <?= htmlspecialchars($inv['project_title']) ?>
                </div>
                <div style="font-size:12px;color:var(--text-3);margin-bottom:12px;">
                    <?= htmlspecialchars($inv['client_name']) ?>
                    <?php if ($inv['due_date']): ?>
                    · Due <?= date('M j, Y', strtotime($inv['due_date'])) ?>
                    <?php endif; ?>
                </div>
                <div style="display:flex;gap:8px;">
                    <button class="btn btn-ghost" style="flex:1;" onclick="openViewPanel(<?= $inv['invoice_id'] ?>)">View</button>
                    <?php if ($inv['status'] === 'unpaid' || $inv['status'] === 'overdue'): ?>
                    <button class="btn btn-ghost" onclick="markPaid(<?= $inv['invoice_id'] ?>)" style="color:var(--mint-deep);">✓ Paid</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- ── Overlay ── -->
<div class="panel-overlay" id="overlay"></div>

<!-- ── New Invoice Panel ── -->
<div class="panel" id="newPanel">
    <div class="panel-header">
        <span class="panel-title">New Invoice</span>
        <button class="panel-close" onclick="closePanel('newPanel')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <div class="form-group">
        <label>Project *</label>
        <select id="new-project">
            <option value="">Select a project...</option>
            <?php foreach ($projects as $p): ?>
            <option value="<?= $p['project_id'] ?>"><?= htmlspecialchars($p['title']) ?> — <?= htmlspecialchars($p['client_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Due Date</label>
        <input type="date" id="new-due-date">
    </div>
    <div class="form-group">
        <label>Notes</label>
        <textarea id="new-notes" placeholder="Payment terms, bank details..."></textarea>
    </div>

    <!-- Line Items -->
    <div class="panel-section">
        <div class="panel-section-title">Line Items</div>
        <table class="line-items-table">
            <thead>
                <tr>
                    <th class="col-desc">Description</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-price">Price</th>
                    <th class="col-sub">Total</th>
                    <th class="col-del"></th>
                </tr>
            </thead>
            <tbody id="line-items">
                <!-- Row template — first row -->
                <tr class="line-item">
                    <td class="col-desc"><input type="text" class="desc" placeholder="Service description"></td>
                    <td class="col-qty"><input type="number" class="qty" value="1" min="0.01" step="0.01"></td>
                    <td class="col-price"><input type="number" class="price" placeholder="0.00" min="0" step="0.01"></td>
                    <td class="col-sub"><span class="subtotal">$0.00</span></td>
                    <td class="col-del"><button type="button" class="remove-line" onclick="removeLine(this)">×</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-ghost" style="margin-top:10px;font-size:13px;" id="add-line">+ Add Line</button>
    </div>

    <div class="invoice-total-row">
        <span class="invoice-total-label">Total</span>
        <span class="invoice-total-amount" id="grand-total">$0.00</span>
    </div>

    <div id="new-error" style="color:#E24B4A;font-size:13px;margin:12px 0;"></div>
    <button class="btn btn-primary" style="width:100%;margin-top:8px;" onclick="submitNewInvoice()">Create Invoice</button>
</div>

<!-- ── View Invoice Panel ── -->
<div class="panel" id="viewPanel">
    <div class="panel-header">
        <span class="panel-title" id="view-title">Invoice</span>
        <button class="panel-close" onclick="closePanel('viewPanel')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <!-- Loading state -->
    <div id="view-loading" class="hidden" style="text-align:center;padding:40px;color:var(--text-3);">Loading...</div>

    <!-- Invoice content — hidden until loaded -->
    <div id="view-content" class="hidden">

        <!-- Preview box -->
        <div class="invoice-preview">
            <div class="invoice-preview-header">
                <div>
                    <div style="font-size:13px;color:var(--text-3);">Bill To</div>
                    <div id="v-client" style="font-size:15px;font-weight:600;margin-top:2px;"></div>
                    <div id="v-company" style="font-size:12px;color:var(--text-3);"></div>
                </div>
                <div style="text-align:right;">
                    <div class="invoice-preview-no" id="v-invoice-no"></div>
                    <div id="v-issued" style="font-size:12px;color:var(--text-3);margin-top:4px;"></div>
                    <div id="v-due" style="font-size:12px;color:var(--text-3);"></div>
                </div>
            </div>

            <div id="v-project" style="font-size:13px;color:var(--text-2);margin-bottom:10px;"></div>

            <table class="invoice-preview">
                <thead>
                    <tr>
                        <th style="width:50%;">Description</th>
                        <th style="width:10%;text-align:center;">Qty</th>
                        <th style="width:20%;text-align:right;">Price</th>
                        <th style="width:20%;text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody id="v-items"></tbody>
            </table>

            <div class="invoice-total-row">
                <span class="invoice-total-label">Total</span>
                <span class="invoice-total-amount" id="v-total"></span>
            </div>
        </div>

        <!-- Notes -->
        <div id="v-notes-wrap" class="hidden" style="padding:12px;background:rgba(180,160,140,0.07);border-radius:var(--r-sm);font-size:13px;color:var(--text-2);margin-bottom:16px;">
            <span id="v-notes"></span>
        </div>

        <!-- Actions -->
        <div class="panel-section">
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                    <button id="v-btn-edit"   class="btn btn-ghost hidden" style="flex:1;"> Edit</button>
                    <button id="v-btn-paid"   class="btn btn-ghost hidden" style="color:var(--mint-deep);flex:1;">✓ Mark as Paid</button>
                    <div    id="v-paid-badge" class="badge badge-paid hidden" style="padding:10px 16px;">Paid ✓</div>
                    <button id="v-btn-delete" class="btn btn-danger hidden">Delete Draft</button>
                </div>
            <!-- Status change — always visible -->
            <div>
                <label style="font-size:12px;color:var(--text-3);display:block;margin-bottom:6px;">Change Status</label>
                <div style="display:flex;gap:8px;">
                    <select id="v-status-select" style="flex:1;padding:8px;border:1.5px solid rgba(180,160,140,0.25);border-radius:var(--r-sm);font-family:inherit;font-size:13px;">
                        <option value="draft">Draft</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                    <button class="btn btn-ghost" id="v-btn-status">Update</button>
                </div>
            </div>
        </div>

    </div><!-- /#view-content -->
</div>

<script src="js/main.js"></script>
<script src="js/invoices.js"></script>
</body>
</html>

<?php
function statusBadgePhp($status) {
    $map = [
        'draft'   => ['badge-draft',    'Draft'],
        'unpaid'  => ['badge-unpaid',   'Unpaid'],
        'paid'    => ['badge-paid',     'Paid ✓'],
        'overdue' => ['badge-overdue',  'Overdue'],
    ];
    [$cls, $label] = $map[$status] ?? ['badge-draft', $status];
    return "<span class=\"badge {$cls}\">{$label}</span>";
}
?>