<?php
/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — client-view.php
   Read-only portal for clients
   ═══════════════════════════════════════════════════════════ */
session_start();
if (!isset($_SESSION['user_id']))   { header('Location: login.php'); exit; }
if ($_SESSION['role'] !== 'client') { header('Location: dashboard.php'); exit; }

require 'api/db.php';

$user_id = $_SESSION['user_id'];
$name    = $_SESSION['name'] ?? 'there';

// Get client record
$stmt = $pdo->prepare("
    SELECT c.*, u.gender 
    FROM clients c 
    JOIN users u ON u.user_id = c.user_id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$client = $stmt->fetch();

if (!$client) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$peek_mascot = "images/mascot/peek-" . ($client['gender'] ?? 'female') . ".png";
$client_id   = $client['client_id'];

// Get projects
$projects = $pdo->prepare("
    SELECT
        p.project_id, p.title, p.status, p.deadline,
        p.budget, p.cover_color, p.emoji, p.description,
        COUNT(t.task_id)                                                AS total_tasks,
        COALESCE(SUM(t.is_done), 0)                                     AS done_tasks,
        IFNULL(ROUND(SUM(t.is_done)/NULLIF(COUNT(t.task_id),0)*100), 0) AS pct
    FROM projects p
    LEFT JOIN tasks t ON t.project_id = p.project_id
    WHERE p.client_id = ?
    GROUP BY p.project_id
    ORDER BY p.updated_at DESC
");
$projects->execute([$client_id]);
$projects = $projects->fetchAll();

// Auto-flag overdue invoices
$pdo->exec("UPDATE invoices SET status='overdue' WHERE status='unpaid' AND due_date < CURDATE()");

// Get invoices
$invoices = $pdo->prepare("
    SELECT i.invoice_id, i.invoice_no, i.status, i.total, i.due_date, i.issued_at,
           p.title AS project_title
    FROM invoices i
    JOIN projects p ON p.project_id = i.project_id
    WHERE p.client_id = ?
    ORDER BY i.issued_at DESC
");
$invoices->execute([$client_id]);
$invoices = $invoices->fetchAll();

// Stats
$total_paid      = array_sum(array_filter(array_map(
    fn($i) => $i['status'] === 'paid' ? $i['total'] : 0, $invoices
)));
$active_projects = count(array_filter($projects, fn($p) => $p['status'] === 'in_progress'));

// Helpers
function statusLabel($s) {
    return ['draft'=>'Draft','in_progress'=>'In Progress','review'=>'In Review',
            'completed'=>'Completed','cancelled'=>'Cancelled'][$s] ?? $s;
}
function statusClass($s) {
    return ['draft'=>'badge-draft','in_progress'=>'badge-progress','review'=>'badge-review',
            'completed'=>'badge-done','cancelled'=>'badge-cancelled'][$s] ?? 'badge-draft';
}
function invoiceClass($s) {
    return ['draft'=>'badge-draft','unpaid'=>'badge-unpaid',
            'paid'=>'badge-paid','overdue'=>'badge-overdue'][$s] ?? 'badge-draft';
}
function invoiceLabel($s) {
    return ['draft'=>'Draft','unpaid'=>'Unpaid','paid'=>'Paid ✓','overdue'=>'Overdue'][$s] ?? $s;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Workspace — Freelance Portal</title>
    <link href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/client.css">
</head>
<body>

    <!-- ── Topbar ── -->
    <header class="topbar">
        <div class="topbar-logo">
            <img src="images/logo.png" alt="logo">
            <span class="topbar-logo-text">Freelance Portal</span>
        </div>
        <nav class="topbar-nav">
            <a href="#projects" class="active">Projects</a>
            <a href="#invoices">Invoices</a>
        </nav>
        <a href="logout.php" class="topbar-logout">Logout</a>
    </header>

    <!-- ── Page Content ── -->
    <div class="page">

        <!-- Greeting -->
        <div class="greeting">
            <h1>Hello, <span><?= htmlspecialchars($name) ?></span> 👋</h1>
            <p>Here's your workspace overview.</p>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-card-value"><?= count($projects) ?></div>
                <div class="stat-card-label">Total Projects</div>
            </div>
            <div class="stat-card stat-card-peek">
                <img src="<?= htmlspecialchars($peek_mascot) ?>" class="peek-img">
                <div class="stat-card-value">$<?= number_format($total_paid, 0) ?></div>
                <div class="stat-card-label">Total Paid</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-value"><?= $active_projects ?></div>
                <div class="stat-card-label">Active Projects</div>
            </div>
        </div>

        <!-- Projects -->
        <div class="section" id="projects">
            <div class="section-title">Your Projects</div>

            <?php if (empty($projects)): ?>
            <div class="empty">
                <img src="images/stickers/folder.webp" class="empty-sticker">
                <p>No projects yet.</p>
            </div>
            <?php else: ?>
            <?php foreach ($projects as $p): ?>
            <div class="project-card">
                <div class="cover-bar" style="background:<?= htmlspecialchars($p['cover_color']) ?>"></div>
                <div class="project-header">
                    <img src="images/stickers/<?= htmlspecialchars($p['emoji']) ?>.webp"
                         class="project-emoji"
                         onerror="this.style.display='none'">
                    <span class="project-name"><?= htmlspecialchars($p['title']) ?></span>
                    <span class="badge <?= statusClass($p['status']) ?>"><?= statusLabel($p['status']) ?></span>
                </div>

                <?php if ($p['description']): ?>
                <p class="project-description"><?= htmlspecialchars($p['description']) ?></p>
                <?php endif; ?>

                <div class="project-meta">
                    <?php if ($p['deadline']): ?>
                    <span>
                        <img src="images/stickers/calendar.webp" class="meta-icon">
                        Due <?= date('M j, Y', strtotime($p['deadline'])) ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($p['budget']): ?>
                    <span>
                        <img src="images/stickers/money.webp" class="meta-icon">
                        Budget $<?= number_format($p['budget'], 0) ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($p['total_tasks'] > 0): ?>
                    <span>
                        <img src="images/stickers/done.webp" class="meta-icon">
                        <?= $p['done_tasks'] ?>/<?= $p['total_tasks'] ?> tasks done
                    </span>
                    <?php endif; ?>
                </div>

                <?php if ($p['total_tasks'] > 0): ?>
                <div class="progress-wrap">
                    <div class="progress-track">
                        <div class="progress-fill" style="width:<?= $p['pct'] ?>%"></div>
                    </div>
                    <div class="progress-label">
                        <span>Progress</span>
                        <span><?= $p['pct'] ?>% complete</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Invoices -->
        <div class="section" id="invoices">
            <div class="section-title">Your Invoices</div>

            <?php if (empty($invoices)): ?>
            <div class="empty">
                <img src="images/stickers/document.webp" class="empty-sticker">
                <p>No invoices yet.</p>
            </div>
            <?php else: ?>
            <?php foreach ($invoices as $inv): ?>
            <div class="invoice-row">
                <div>
                    <div class="invoice-no">
                        <img src="images/stickers/document.webp" class="invoice-icon">
                        <?= htmlspecialchars($inv['invoice_no']) ?>
                    </div>
                    <div class="invoice-meta">
                        <?= htmlspecialchars($inv['project_title']) ?>
                        <?php if ($inv['due_date']): ?>
                        · Due <?= date('M j, Y', strtotime($inv['due_date'])) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="invoice-actions">
                    <span class="badge <?= invoiceClass($inv['status']) ?>"><?= invoiceLabel($inv['status']) ?></span>
                    <div class="invoice-amount">$<?= number_format($inv['total'], 2) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div><!-- /.page -->

</body>
</html>