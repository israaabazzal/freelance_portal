<?php
/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — clients.php
   ═══════════════════════════════════════════════════════════ */
session_start();
if (!isset($_SESSION['user_id']))       { header('Location: login.php'); exit; }
if ($_SESSION['role'] !== 'freelancer') { header('Location: client-view.php'); exit; }

require 'api/db.php';

$gender = $_SESSION['gender'] ?? 'female';
$mascot = "images/mascot/sidebar-{$gender}.png";

// Fetch all active clients with stats
$clients = $pdo->query("
    SELECT
        u.name, u.email,
        c.client_id, c.company, c.phone, c.tag_color, c.notes,
        COUNT(DISTINCT p.project_id)                                       AS projects,
        COALESCE(SUM(i.total), 0)                                          AS invoiced,
        COALESCE(SUM(CASE WHEN i.status='paid' THEN i.total END), 0)       AS paid
    FROM clients c
    JOIN users u ON u.user_id = c.user_id
    LEFT JOIN projects p ON p.client_id = c.client_id
    LEFT JOIN invoices i ON i.project_id = p.project_id
    WHERE c.is_active = 1
    GROUP BY c.client_id
    ORDER BY projects DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients — Freelance Portal</title>
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
        <a href="clients.php" class="nav-link active">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Clients
        </a>
        <a href="projects.php" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            Projects
        </a>
        <a href="invoices.php" class="nav-link">
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

        <!-- Header -->
        <div class="page-header">
            <div>
                <h1>Clients</h1>
                <p class="page-subtitle"><?= count($clients) ?> active client<?= count($clients) !== 1 ? 's' : '' ?></p>
            </div>
            <button class="btn btn-primary" onclick="openAddPanel()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Client
            </button>
        </div>

        <!-- Search -->
        <div class="filter-bar">
            <div class="search-input-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" class="search-input" id="searchInput" placeholder="Search clients...">
            </div>
        </div>

        <!-- Clients Grid -->
        <div class="clients-grid" id="clientsGrid">
            <?php if (empty($clients)): ?>
            <div class="empty-state" style="grid-column:1/-1;">
                <div class="empty-state-icon">👥</div>
                <h3>No clients yet</h3>
                <p>Add your first client to get started.</p>
            </div>
            <?php else: ?>
            <?php foreach ($clients as $c): ?>
            <div class="client-card glass" data-name="<?= htmlspecialchars(strtolower($c['name'])) ?>" data-company="<?= htmlspecialchars(strtolower($c['company'])) ?>">
                <img src="images/stickers/star.webp" alt="" class="sticker">
                <div class="client-name">
                    <span class="client-tag" style="background:<?= htmlspecialchars($c['tag_color']) ?>"></span>
                    <?= htmlspecialchars($c['name']) ?>
                </div>
                <?php if ($c['company']): ?>
                <div class="client-company"><?= htmlspecialchars($c['company']) ?></div>
                <?php endif; ?>

                <div class="client-stats">
                    <div class="client-stat-item">
                        <span class="client-stat-value"><?= $c['projects'] ?></span>
                        <span class="client-stat-label">Projects</span>
                    </div>
                    <div class="client-stat-item">
                        <span class="client-stat-value">$<?= number_format($c['invoiced'], 0) ?></span>
                        <span class="client-stat-label">Invoiced</span>
                    </div>
                    <div class="client-stat-item">
                        <span class="client-stat-value">$<?= number_format($c['paid'], 0) ?></span>
                        <span class="client-stat-label">Paid</span>
                    </div>
                </div>

                <div style="display:flex; gap:8px; margin-top:16px;">
                    <button class="btn btn-ghost" style="flex:1;" onclick="openDetailPanel(<?= $c['client_id'] ?>)">View →</button>
                    <button class="btn btn-ghost" onclick="openEditPanel(<?= $c['client_id'] ?>, <?= htmlspecialchars(json_encode($c)) ?>)">Edit</button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>
</div>

<!-- ── Overlay ── -->
<div class="panel-overlay" id="overlay"></div>

<!-- ── Add Client Panel ── -->
<div class="panel" id="addPanel">
    <div class="panel-header">
        <span class="panel-title">Add New Client</span>
        <button class="panel-close" onclick="closePanel('addPanel')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <div class="form-group">
        <label>Name *</label>
        <input type="text" id="add-name" placeholder="Rami Haddad">
    </div>
    <div class="form-group">
        <label>Email *</label>
        <input type="email" id="add-email" placeholder="rami@example.com">
    </div>
    <div class="form-group">
        <label>Company</label>
        <input type="text" id="add-company" placeholder="Haddad Co.">
    </div>
    <div class="form-group">
        <label>Phone</label>
        <input type="text" id="add-phone" placeholder="+961 70 000 000">
    </div>
    <div class="form-group">
        <label>Card Color</label>
        <div class="color-picker" id="add-color-picker">
            <div class="color-swatch active" style="background:#C9C2F0;" data-color="#C9C2F0"></div>
            <div class="color-swatch" style="background:#FFCBB4;" data-color="#FFCBB4"></div>
            <div class="color-swatch" style="background:#B6E8D3;" data-color="#B6E8D3"></div>
            <div class="color-swatch" style="background:#BFE3F9;" data-color="#BFE3F9"></div>
            <div class="color-swatch" style="background:#FAE4A2;" data-color="#FAE4A2"></div>
            <div class="color-swatch" style="background:#F9D4DC;" data-color="#F9D4DC"></div>
            <div class="color-swatch" style="background:#D7B5B3;" data-color="#D7B5B3"></div>
        </div>
        <input type="hidden" id="add-tag-color" value="#C9C2F0">
    </div>
    <div class="form-group">
        <label>Gender</label>
        <div class="gender-toggle">
            <button type="button" class="gender-btn active" data-value="female" onclick="setGender(this, 'add')">She/Her</button>
            <button type="button" class="gender-btn" data-value="male" onclick="setGender(this, 'add')">He/Him</button>
        </div>
        <input type="hidden" id="add-gender" value="female">
    </div>
    <div class="form-group">
        <label>Notes</label>
        <textarea id="add-notes" placeholder="Any notes about this client..."></textarea>
    </div>

    <div id="add-error" style="color:#E24B4A; font-size:13px; margin-bottom:12px;"></div>

    <button class="btn btn-primary" style="width:100%;" onclick="submitAddClient()">Create Client</button>

    <!-- Temp password shown after creation -->
    <div id="temp-password-box" class="hidden" style="margin-top:16px; padding:14px; background:rgba(182,232,211,0.4); border-radius:var(--r-sm); border:1.5px solid var(--mint-deep);">
        <p style="font-size:12px; color:var(--mint-deep); font-weight:600; margin-bottom:6px;">✓ Client created! Share these login credentials:</p>
        <p style="font-size:13px; color:var(--text-2);">Email: <strong id="tp-email"></strong></p>
        <p style="font-size:13px; color:var(--text-2); margin-top:4px;">Password: <strong id="tp-pass"></strong></p>
        <p style="font-size:11px; color:var(--text-3); margin-top:8px;">This password won't be shown again.</p>
        <button class="btn btn-primary" style="width:100%;margin-top:12px;" 
            onclick="location.reload()">Done — I've copied the credentials</button>
    </div>
</div>

<!-- ── Edit Client Panel ── -->
<div class="panel" id="editPanel">
    <div class="panel-header">
        <span class="panel-title">Edit Client</span>
        <button class="panel-close" onclick="closePanel('editPanel')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <input type="hidden" id="edit-client-id">
    <div class="form-group">
        <label>Name *</label>
        <input type="text" id="edit-name">
    </div>
    <div class="form-group">
        <label>Company</label>
        <input type="text" id="edit-company">
    </div>
    <div class="form-group">
        <label>Phone</label>
        <input type="text" id="edit-phone">
    </div>
    <div class="form-group">
        <label>Card Color</label>
        <div class="color-picker" id="edit-color-picker">
            <div class="color-swatch" style="background:#C9C2F0;" data-color="#C9C2F0"></div>
            <div class="color-swatch" style="background:#FFCBB4;" data-color="#FFCBB4"></div>
            <div class="color-swatch" style="background:#B6E8D3;" data-color="#B6E8D3"></div>
            <div class="color-swatch" style="background:#BFE3F9;" data-color="#BFE3F9"></div>
            <div class="color-swatch" style="background:#FAE4A2;" data-color="#FAE4A2"></div>
            <div class="color-swatch" style="background:#F9D4DC;" data-color="#F9D4DC"></div>
            <div class="color-swatch" style="background:#D7B5B3;" data-color="#D7B5B3"></div>
            
        </div>
        <input type="hidden" id="edit-tag-color" value="#C9C2F0">
    </div>
    <div class="form-group">
        <label>Notes</label>
        <textarea id="edit-notes"></textarea>
    </div>
    <div id="edit-error" style="color:#E24B4A; font-size:13px; margin-bottom:12px;"></div>
<div style="display:flex; gap:8px;">
    <button class="btn btn-primary" style="flex:1;" onclick="submitEditClient()">Save Changes</button>
    <button class="btn btn-danger" onclick="archiveClient()">Archive</button>
    <button class="btn btn-danger" onclick="deleteClient()">Delete</button>
</div>
</div>

<!-- ── Detail Panel ── -->
<div class="panel" id="detailPanel">
    <div class="panel-header">
    <div style="display:flex;align-items:center;gap:8px;">
        <div id="d-tag-dot" style="width:20px;height:20px;border-radius:50%;flex-shrink:0;"></div>
        <span class="panel-title" id="detail-title">Client</span>
    </div>
    <button class="panel-close" onclick="closePanel('detailPanel')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <!-- Loading -->
    <div id="detail-loading" style="text-align:center;padding:40px;color:var(--text-3);">Loading...</div>

    <!-- Content -->
    <div id="detail-content" class="hidden">

        <!-- Client info -->
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
            <div id="d-tag-dot" style="width:14px;height:14px;border-radius:50%;flex-shrink:0;"></div>
            <div>
                <div id="d-company" style="font-size:13px;color:var(--text-3);"></div>
                <div id="d-phone" style="font-size:13px;color:var(--text-3);"></div>
            </div>
        </div>

        <!-- Notes -->
        <div id="d-notes-wrap" class="hidden" style="font-size:13px;color:var(--text-2);margin-bottom:20px;padding:12px;background:rgba(180,160,140,0.08);border-radius:var(--r-sm);">
            <span id="d-notes"></span>
        </div>

        <!-- Projects -->
        <div class="panel-section">
            <div class="panel-section-title">Projects (<span id="d-projects-count">0</span>)</div>
            <div id="d-projects"></div>
        </div>

        <!-- Invoices -->
        <div class="panel-section">
            <div class="panel-section-title">Invoices</div>
            <div id="d-invoices"></div>
        </div>

        <!-- Actions -->
        <div class="panel-section">
            <button class="btn btn-ghost" style="width:100%;" id="d-edit-btn">Edit Client</button>
        </div>

    </div>
</div>

<script src="js/main.js"></script>
<script src="js/clients.js"></script>

</body>
</html>