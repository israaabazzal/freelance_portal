<?php
/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — dashboard.php
   ═══════════════════════════════════════════════════════════ */
session_start();
if (!isset($_SESSION['user_id']))       { header('Location: login.php'); exit; }
if ($_SESSION['role'] !== 'freelancer') { header('Location: client-view.php'); exit; }

require 'api/db.php';

$gender  = $_SESSION['gender'] ?? 'female';
$mascot  = "images/mascot/sidebar-{$gender}.png";
$name    = $_SESSION['name'] ?? 'there';

// ── Greeting ──────────────────────────────────────────────
$hour = (int) date('H');
if ($hour < 12)      $greeting = 'Good morning';
elseif ($hour < 18)  $greeting = 'Good afternoon';
else                 $greeting = 'Good evening';

$today = date('F j, Y');

// ── Stats ─────────────────────────────────────────────────
$active_clients  = $pdo->query("SELECT COUNT(*) FROM clients WHERE is_active = 1")->fetchColumn();
$active_projects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'in_progress'")->fetchColumn();
$overdue_tasks   = $pdo->query("SELECT COUNT(*) FROM tasks WHERE is_done = 0 AND due_date < CURDATE()")->fetchColumn();

// Revenue this month
$revenue = $pdo->query("
    SELECT COALESCE(SUM(total), 0)
    FROM invoices
    WHERE status = 'paid'
    AND MONTH(paid_at) = MONTH(NOW())
    AND YEAR(paid_at)  = YEAR(NOW())
")->fetchColumn();

// Unpaid invoices count
$unpaid_count = $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'unpaid'")->fetchColumn();

// Overdue invoices count
$overdue_invoices = $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'overdue'")->fetchColumn();

// Projects on track (not overdue deadline)
$on_track = $pdo->query("
    SELECT COUNT(*) FROM projects
    WHERE status = 'in_progress'
    AND (deadline IS NULL OR deadline >= CURDATE())
")->fetchColumn();

$on_track_pct = $active_projects > 0 ? round(($on_track / $active_projects) * 100) : 0;

// ── Top Clients ────────────────────────────────────────────
$top_clients = $pdo->query("
    SELECT u.name, c.tag_color,
           COUNT(DISTINCT p.project_id) AS project_count
    FROM clients c
    JOIN users u ON u.user_id = c.user_id
    LEFT JOIN projects p ON p.client_id = c.client_id
    WHERE c.is_active = 1
    GROUP BY c.client_id
    ORDER BY project_count DESC
    LIMIT 4
")->fetchAll();

// ── Due This Week ──────────────────────────────────────────
$due_tasks = $pdo->query("
    SELECT COUNT(*) FROM tasks
    WHERE is_done = 0
    AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
")->fetchColumn();

$due_invoices = $pdo->query("
    SELECT COUNT(*) FROM invoices
    WHERE status = 'unpaid'
    AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
")->fetchColumn();

// ── Recent Activity ────────────────────────────────────────
$activity = $pdo->query("
    SELECT a.action, a.detail, a.logged_at, u.name
    FROM activity_log a
    JOIN users u ON u.user_id = a.user_id
    ORDER BY a.logged_at DESC
    LIMIT 8
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Freelance Portal</title>
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
            <div class="sidebar-logo-text">Freelance Portal</div>
        </div>

        <span class="nav-label">Menu</span>
        <a href="dashboard.php" class="nav-link active">
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
        <a href="invoices.php" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            Invoices
        </a>

        <!-- Mascot -->
        <div class="sidebar-mascot">
            <img src="<?= htmlspecialchars($mascot) ?>" alt="mascot" 
     class="sidebar-mascot-img <?= $gender === 'male' ? 'mascot-male' : '' ?>">
        </div>

        <a href="logout.php" class="sidebar-logout">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Logout
        </a>
    </aside>

    <!-- ── Main Content ── -->
    <main class="main-content">

        <!-- Greeting -->
        <div class="greeting-row">
            <div>
                <h1><?= $greeting ?> , <span class="greeting-name"><?= htmlspecialchars($name) ?></span> ✨</h1>
                <p class="greeting-date"><?= $today ?></p>
            </div>
        </div>

<!-- Bento Grid -->
<div class="bento">

    <!-- Active Projects — WIDE PEACH (row 1-2, col 1-7) -->
    <div class="bento-card glass peach bento-wide" style="overflow:visible;">
        <img src="images/stickers/rocket.webp" alt="" class="bento-sticker">
        <h3>Active Projects</h3>
        <div class="stat-number" data-count="<?= $active_projects ?>"><?= $active_projects ?></div>
        <p class="stat-sub"><?= $active_projects ?> in progress right now</p>
        <div class="progress-wrap">
            <div class="progress-label">
                <span>On track</span>
                <span><?= $on_track_pct ?>%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill" data-pct="<?= $on_track_pct ?>"></div>
            </div>
        </div>
    </div>

        <!-- Revenue — TALL MINT -->
        <div class="bento-card glass mint bento-tall" style="overflow:visible;">
            <img src="images/stickers/money.webp" alt="" class="bento-sticker">
            <h3>Revenue</h3>

<div class="stat-number">
    $<span data-count="<?= intval($revenue) ?>"><?= intval($revenue) ?></span>
    <span class="stat-sub">paid this month</span>
</div>

<?php
// Fetch chart data FIRST
$chart_data = $pdo->query("
    SELECT DATE_FORMAT(paid_at, '%b') AS month,
           DATE_FORMAT(paid_at, '%Y-%m') AS month_key,
           COALESCE(SUM(total),0) AS total
    FROM invoices WHERE status='paid'
    AND paid_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(paid_at,'%Y-%m')
    ORDER BY MIN(paid_at) ASC
")->fetchAll();

// Build 6-month array
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $months[] = [
        'month'     => date('M', strtotime("-$i months")),
        'month_key' => date('Y-m', strtotime("-$i months")),
        'total'     => 0
    ];
}

// Match by month_key — unique per month
foreach ($chart_data as $row) {
    foreach ($months as &$m) {
        if ($m['month_key'] === $row['month_key']) {
            $m['total'] = floatval($row['total']);
        }
    }
}
unset($m); // clear reference

$max_val = max(array_column($months, 'total') ?: [1]);
if ($max_val == 0) $max_val = 1;
?>

            <div style="display:flex;align-items:flex-end;gap:6px;height:80px;margin-top:20px;">
                <?php foreach ($months as $m):
                    $pct        = round(($m['total'] / $max_val) * 100);
                    $is_current = ($m['month'] === date('M'));
                ?>
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;height:100%;">
                    <div style="flex:1;width:100%;display:flex;align-items:flex-end;">
                        <div style="
                            width:100%;
                            height:<?= max(4, $pct) ?>%;
                            background:<?= $is_current ? 'var(--mint-deep)' : 'rgba(46,175,125,0.25)' ?>;
                            border-radius:4px 4px 0 0;
                            min-height:4px;
                        "></div>
                    </div>
                    <span style="font-size:9px;color:<?= $is_current ? 'var(--mint-deep)' : 'var(--text-3)' ?>;font-weight:<?= $is_current ? '600' : '400' ?>;">
                        <?= $m['month'] ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- One clean stat below -->
            <?php
            $total_all = $pdo->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status='paid'")->fetchColumn();
            ?>
            <div style="margin-top:16px;padding-top:14px;border-top:1.5px solid rgba(180,160,140,0.15);">
                <div class="due-item">
                    <span style="font-size:12px;color:var(--text-2);">All time earned</span>
                    <span style="font-size:14px;font-weight:600;color:var(--mint-deep);">$<?= number_format($total_all, 0) ?></span>
                </div>
            </div>

        </div>

    <!-- Due This Week — MEDIUM LAVENDER (row 3-4, col 1-4) -->
    <div class="bento-card glass lavender bento-medium" style="overflow:visible;">
        <img src="images/stickers/clock.webp" alt="" class="bento-sticker">
        <h3>Due This Week</h3>
        <div class="due-items">
            <div class="due-item">
                <span>Tasks</span>
                <span class="due-count" data-count="<?= $due_tasks ?>"><?= $due_tasks ?></span>
            </div>
            <div class="due-item">
                <span>Invoices</span>
                <span class="due-count" data-count="<?= $due_invoices ?>"><?= $due_invoices ?></span>
            </div>
            <?php if ($overdue_invoices > 0): ?>
            <div class="due-item" style="color:#E24B4A;">
                <span>⚠ Overdue</span>
                <span class="due-count" style="color:#E24B4A;"><?= $overdue_invoices ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats — HALF WHITE (row 3-4, col 5-7) -->
    <div class="bento-card glass white bento-half" style="overflow:visible;">
        <img src="images/stickers/sparkle.webp" alt="" class="bento-sticker">
        <h3>Quick Stats</h3>
        <div class="due-items">
            <div class="due-item">
                <span>Unpaid</span>
                <span class="due-count"><?= $unpaid_count ?></span>
            </div>
                <div class="due-item">
                    <span>Overdue invoices</span>
                    <span class="due-count" style="color:#E24B4A;"><?= $overdue_invoices ?></span>
                </div>
        </div>
    </div>

    <!-- Active Clients — BOTTOM SKY (row 5-6, col 1-6) -->
    <div class="bento-card glass sky bento-bottom" style="overflow:visible;">
        <img src="images/stickers/star.webp" alt="" class="bento-sticker">
        <h3>Active Clients</h3>
        <div class="stat-number" style="font-size:32px;" data-count="<?= $active_clients ?>"><?= $active_clients ?></div>
        <div class="client-list">
            <?php foreach ($top_clients as $c): ?>
            <div class="client-row">
                <span class="client-dot" style="background:<?= htmlspecialchars($c['tag_color']) ?>"></span>
                <span class="client-row-name"><?= htmlspecialchars($c['name']) ?></span>
                <span class="client-row-proj"><?= $c['project_count'] ?> proj</span>
            </div>
            <?php endforeach; ?>
            <?php if (empty($top_clients)): ?>
                <p style="font-size:12px;color:var(--text-3);">No clients yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Activity — BOTTOM BUTTER (row 5-6, col 7-12) -->
    <!-- Recent Activity -->
<div class="bento-card glass butter bento-butter" style="overflow:visible;">
        <img src="images/stickers/chart.webp" alt="" class="bento-sticker">
        <h3>Recent Activity</h3>
        <div class="activity-list">
            <?php foreach ($activity as $log): ?>
            <div class="activity-item">
                <div class="activity-dot"></div>
                <div style="flex:1;">
                    <div class="activity-text">
                        <?= htmlspecialchars($log['action']) ?>
                        <?php if ($log['detail']): ?>
                            — <span style="color:var(--text-3)"><?= htmlspecialchars($log['detail']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="activity-time"><?= timeAgo($log['logged_at']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($activity)): ?>
                <p style="font-size:12px;color:var(--text-3);">No activity yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div><!-- /.bento -->
    </main>

</div><!-- /.app-layout -->

<script src="js/main.js"></script>
<script src="js/dashboard.js"></script>
</body>
</html>

<?php
function timeAgo($dateStr) {
    $diff = time() - strtotime($dateStr);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}
?>