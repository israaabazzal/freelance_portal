<?php
/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — projects.php
   ═══════════════════════════════════════════════════════════ */
session_start();
if (!isset($_SESSION['user_id']))       { header('Location: login.php'); exit; }
if ($_SESSION['role'] !== 'freelancer') { header('Location: client-view.php'); exit; }

require 'api/db.php';

$gender = $_SESSION['gender'] ?? 'female';
$mascot = "images/mascot/sidebar-{$gender}.png";

// Fetch all projects with completion %
$projects = $pdo->query("
    SELECT
        p.project_id, p.title, p.status, p.deadline, p.budget,
        p.cover_color, p.emoji, p.description,
        u.name AS client_name, c.client_id, c.tag_color,
        COUNT(t.task_id)                                                AS total_tasks,
        COALESCE(SUM(t.is_done), 0)                                     AS done_tasks,
        IFNULL(ROUND(SUM(t.is_done)/NULLIF(COUNT(t.task_id),0)*100), 0) AS pct
    FROM projects p
    JOIN clients c ON c.client_id = p.client_id
    JOIN users   u ON u.user_id   = c.user_id
    LEFT JOIN tasks t ON t.project_id = p.project_id
    GROUP BY p.project_id
    ORDER BY p.updated_at DESC
")->fetchAll();

// Group by status for kanban columns
$columns = [
    'draft'      => ['label' => 'Draft',       'color' => '#BFE3F9'],
    'in_progress'=> ['label' => 'In Progress',  'color' => '#C9C2F0'],
    'review'     => ['label' => 'Review',       'color' => '#FAE4A2'],
    'completed'  => ['label' => 'Completed',    'color' => '#B6E8D3'],
    'cancelled'  => ['label' => 'Cancelled',    'color' => '#E5E2DF'],
];

$grouped = array_fill_keys(array_keys($columns), []);
foreach ($projects as $p) {
    $grouped[$p['status']][] = $p;
}

// Fetch clients for the "Add Project" dropdown
$clients = $pdo->query("
    SELECT c.client_id, u.name FROM clients c
    JOIN users u ON u.user_id = c.user_id
    WHERE c.is_active = 1
    ORDER BY u.name ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects — Freelance Portal</title>
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
        <a href="projects.php" class="nav-link active">
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
        <div class="page-header">
            <div>
                <h1>Projects</h1>
                <p class="page-subtitle"><?= count($projects) ?> project<?= count($projects) !== 1 ? 's' : '' ?> total</p>
            </div>
            <button class="btn btn-primary" onclick="openAddPanel()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Project
            </button>
        </div>

        <!-- Kanban Board -->
        <div class="kanban-board">
            <?php foreach ($columns as $status => $col): ?>
            <div class="kanban-col" data-status="<?= $status ?>">
                <div class="kanban-col-header">
                    <span class="kanban-col-title" style="color:<?= $col['color'] !== '#E5E2DF' ? 'var(--text-2)' : 'var(--text-3)' ?>">
                        <?= $col['label'] ?>
                    </span>
                    <span class="kanban-count"><?= count($grouped[$status]) ?></span>
                </div>

                <?php foreach ($grouped[$status] as $p): ?>
                <div class="project-card glass" data-project-id="<?= $p['project_id'] ?>" onclick="openDetailPanel(<?= $p['project_id'] ?>)">
                    <div class="cover-strip" style="background:<?= htmlspecialchars($p['cover_color']) ?>"></div>
                  <img src="images/stickers/<?= htmlspecialchars($p['emoji']) ?>.webp" 
     style="width:36px;height:36px;object-fit:contain;" alt="">
                    <div class="project-title"><?= htmlspecialchars($p['title']) ?></div>
                    <div class="project-client">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= htmlspecialchars($p['tag_color']) ?>;margin-right:5px;"></span>
                        <?= htmlspecialchars($p['client_name']) ?>
                    </div>
                    <?php if ($p['deadline']): ?>
                    <div style="font-size:11px;color:<?= strtotime($p['deadline']) < time() && $status !== 'completed' ? '#E24B4A' : 'var(--text-3)' ?>;margin-top:6px;">
                        <?= strtotime($p['deadline']) < time() && $status !== 'completed' ? '⚠ ' : '' ?>
                        <?= date('M j', strtotime($p['deadline'])) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($p['total_tasks'] > 0): ?>
                    <div style="margin-top:10px;">
                        <div style="background:rgba(180,160,140,0.15);border-radius:99px;height:4px;overflow:hidden;">
                            <div style="height:100%;border-radius:99px;background:<?= $col['color'] ?>;width:<?= $p['pct'] ?>%;"></div>
                        </div>
                        <div style="font-size:11px;color:var(--text-3);margin-top:3px;"><?= $p['pct'] ?>%</div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <?php if (empty($grouped[$status])): ?>
                <div style="text-align:center;padding:20px 10px;font-size:12px;color:var(--text-3);">Empty</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- ── Overlay ── -->
<div class="panel-overlay" id="overlay"></div>

<!-- ── Add Project Panel ── -->
<div class="panel" id="addPanel">
    <div class="panel-header">
        <span class="panel-title">New Project</span>
        <button class="panel-close" onclick="closePanel('addPanel')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="form-group">
        <label>Client *</label>
        <select id="add-client">
            <option value="">Select a client...</option>
            <?php foreach ($clients as $c): ?>
            <option value="<?= $c['client_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Title *</label>
        <input type="text" id="add-title" placeholder="Website Redesign">
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea id="add-description" placeholder="What's this project about?"></textarea>
    </div>
    <div style="display:flex;gap:12px;">
        <div class="form-group" style="flex:1;">
            <label>Deadline</label>
            <input type="date" id="add-deadline">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Budget ($)</label>
            <input type="number" id="add-budget" placeholder="0">
        </div>
    </div>
    
<div class="form-group">
    <label>Project Icon</label>
    <div style="display:flex;gap:10px;flex-wrap:wrap;" id="sticker-picker">
        <img src="images/stickers/folder.webp"  class="sticker-pick active" data-emoji="folder"  onclick="selectSticker(this)">
        <img src="images/stickers/rocket.webp"  class="sticker-pick" data-emoji="rocket"  onclick="selectSticker(this)">
        <img src="images/stickers/sparkle.webp" class="sticker-pick" data-emoji="sparkle" onclick="selectSticker(this)">
        <img src="images/stickers/star.webp"    class="sticker-pick" data-emoji="star"    onclick="selectSticker(this)">
        <img src="images/stickers/clock.webp"   class="sticker-pick" data-emoji="clock"   onclick="selectSticker(this)">
        <img src="images/stickers/chart.webp"   class="sticker-pick" data-emoji="chart"   onclick="selectSticker(this)">
        <img src="images/stickers/money.webp"   class="sticker-pick" data-emoji="money"   onclick="selectSticker(this)">
        <img src="images/stickers/mail.webp"    class="sticker-pick" data-emoji="mail"    onclick="selectSticker(this)">
        <img src="images/stickers/plant.webp"   class="sticker-pick" data-emoji="plant"   onclick="selectSticker(this)">
    </div>
    <input type="hidden" id="add-emoji" value="folder">
</div>
    <div class="form-group">
        <label>Cover Color</label>
        <div class="color-picker" id="add-cover-picker">
            <div class="color-swatch active" style="background:#FFCBB4;" data-color="#FFCBB4"></div>
            <div class="color-swatch" style="background:#C9C2F0;" data-color="#C9C2F0"></div>
            <div class="color-swatch" style="background:#B6E8D3;" data-color="#B6E8D3"></div>
            <div class="color-swatch" style="background:#BFE3F9;" data-color="#BFE3F9"></div>
            <div class="color-swatch" style="background:#FAE4A2;" data-color="#FAE4A2"></div>
            <div class="color-swatch" style="background:#F9D4DC;" data-color="#F9D4DC"></div>
        </div>
        <input type="hidden" id="add-cover-color" value="#FFCBB4">
    </div>
    <div id="add-error" style="color:#E24B4A;font-size:13px;margin-bottom:12px;"></div>
    <button class="btn btn-primary" style="width:100%;" onclick="submitAddProject()">Create Project</button>
</div>

<!-- ── Project Detail Panel ── -->
<div class="panel" id="detailPanel">
    <div class="panel-header">
        <span class="panel-title" id="detail-title">Project</span>
        <button class="panel-close" onclick="closePanel('detailPanel')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div id="detail-content">
        <div style="text-align:center;padding:40px;color:var(--text-3);">Loading...</div>
    </div>
</div>


<script src="js/main.js"></script>
<script src="js/kanban.js"></script>
<script src="js/projects.js"></script>


<!-- ── Edit Project Panel ── -->
<div class="panel" id="editProjectPanel">
    <div class="panel-header">
        <span class="panel-title">Edit Project</span>
        <button class="panel-close" onclick="closePanel('editProjectPanel')">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <input type="hidden" id="edit-project-id">
    <div class="form-group">
        <label>Title *</label>
        <input type="text" id="edit-project-title">
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea id="edit-project-description"></textarea>
    </div>
    <div style="display:flex;gap:12px;">
        <div class="form-group" style="flex:1;">
            <label>Deadline</label>
            <input type="date" id="edit-project-deadline">
        </div>
        <div class="form-group" style="flex:1;">
            <label>Budget ($)</label>
            <input type="number" id="edit-project-budget">
        </div>
    </div>
    <div class="form-group">
        <label>Project Icon</label>
        <div style="display:flex;gap:10px;flex-wrap:wrap;" id="edit-sticker-picker">
            <img src="images/stickers/folder.webp"  class="sticker-pick" data-emoji="folder"  onclick="selectEditSticker(this)">
            <img src="images/stickers/rocket.webp"  class="sticker-pick" data-emoji="rocket"  onclick="selectEditSticker(this)">
            <img src="images/stickers/sparkle.webp" class="sticker-pick" data-emoji="sparkle" onclick="selectEditSticker(this)">
            <img src="images/stickers/star.webp"    class="sticker-pick" data-emoji="star"    onclick="selectEditSticker(this)">
            <img src="images/stickers/clock.webp"   class="sticker-pick" data-emoji="clock"   onclick="selectEditSticker(this)">
            <img src="images/stickers/chart.webp"   class="sticker-pick" data-emoji="chart"   onclick="selectEditSticker(this)">
            <img src="images/stickers/money.webp"   class="sticker-pick" data-emoji="money"   onclick="selectEditSticker(this)">
            <img src="images/stickers/mail.webp"    class="sticker-pick" data-emoji="mail"    onclick="selectEditSticker(this)">
            <img src="images/stickers/plant.webp"   class="sticker-pick" data-emoji="plant"   onclick="selectEditSticker(this)">
        </div>
        <input type="hidden" id="edit-project-emoji" value="folder">
    </div>
    <div class="form-group">
        <label>Cover Color</label>
        <div class="color-picker" id="edit-cover-picker">
            <div class="color-swatch" style="background:#FFCBB4;" data-color="#FFCBB4"></div>
            <div class="color-swatch" style="background:#C9C2F0;" data-color="#C9C2F0"></div>
            <div class="color-swatch" style="background:#B6E8D3;" data-color="#B6E8D3"></div>
            <div class="color-swatch" style="background:#BFE3F9;" data-color="#BFE3F9"></div>
            <div class="color-swatch" style="background:#FAE4A2;" data-color="#FAE4A2"></div>
            <div class="color-swatch" style="background:#F9D4DC;" data-color="#F9D4DC"></div>
        </div>
        <input type="hidden" id="edit-project-color" value="#FFCBB4">
    </div>
    <div id="edit-project-error" style="color:#E24B4A;font-size:13px;margin-bottom:12px;"></div>
    <button class="btn btn-primary" style="width:100%;" onclick="submitEditProject()">Save Changes</button>
</div>

</body>
</html>