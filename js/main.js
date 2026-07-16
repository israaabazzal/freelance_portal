/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — main.js
   Shared utilities: API helper, toast, panel, formatters
   Loaded on every freelancer page
   ═══════════════════════════════════════════════════════════ */


/* ── API Helper ──────────────────────────────────────────── */
async function api(endpoint, data = null) {
    try {
        const opts = data
            ? {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(data)
              }
            : { method: 'GET' };

        const res = await fetch(endpoint, opts);

        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        return await res.json();

    } catch (err) {
        console.error('API error:', err);
        toast('Something went wrong. Try again.', 'error');
        return null;
    }
}


/* ── Toast Notifications ─────────────────────────────────── */
// Usage: toast('Client saved!') / toast('Error!', 'error') / toast('Note', 'info')
function toast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className  = `toast toast-${type}`;
    t.textContent = msg;
    document.body.appendChild(t);

    // Trigger animation
    requestAnimationFrame(() => {
        requestAnimationFrame(() => t.classList.add('show'));
    });

    // Auto remove after 2.8s
    setTimeout(() => {
        t.classList.remove('show');
        setTimeout(() => t.remove(), 300);
    }, 2800);
}


/* ── Panel Open / Close ──────────────────────────────────── */
// Usage: openPanel('client-panel') / closePanel('client-panel')
function openPanel(id) {
    const panel   = document.getElementById(id);
    const overlay = document.getElementById('overlay');
    if (panel)   panel.classList.add('open');
    if (overlay) overlay.classList.add('open');
    document.body.style.overflow = 'hidden'; // prevent background scroll
}

function closePanel(id) {
    const panel   = document.getElementById(id);
    const overlay = document.getElementById('overlay');
    if (panel)   panel.classList.remove('open');
    if (overlay) overlay.classList.remove('open');
    document.body.style.overflow = '';
}

function closeAllPanels() {
    document.querySelectorAll('.panel.open').forEach(p => p.classList.remove('open'));
    const overlay = document.getElementById('overlay');
    if (overlay) overlay.classList.remove('open');
    document.body.style.overflow = '';
}

// Close on overlay click
document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('overlay');
    if (overlay) {
        overlay.addEventListener('click', closeAllPanels);
    }

    // Close on Escape key
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeAllPanels();
    });
});


/* ── Formatters ──────────────────────────────────────────── */
// Money: 1200 → "$1,200.00"
function money(n) {
    return '$' + parseFloat(n || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Short date: "2026-06-01" → "Jun 1, 2026"
function dateStr(d) {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', {
        day:   'numeric',
        month: 'short',
        year:  'numeric'
    });
}

// Relative time: "2 hours ago"
function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60)     return 'just now';
    if (diff < 3600)   return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400)  return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return dateStr;
}

// Capitalize first letter: "in_progress" → "In Progress"
function formatStatus(s) {
    return s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}


/* ── Badge HTML Helper ───────────────────────────────────── */
// Returns a badge span for a given status string
function statusBadge(status) {
    const map = {
        draft:       'badge-draft',
        in_progress: 'badge-progress',
        review:      'badge-review',
        completed:   'badge-done',
        cancelled:   'badge-cancelled',
        unpaid:      'badge-unpaid',
        paid:        'badge-paid',
        overdue:     'badge-overdue',
    };
    const cls   = map[status] || 'badge-draft';
    const label = formatStatus(status);
    return `<span class="badge ${cls}">${label}</span>`;
}

function priorityBadge(priority) {
    const map = {
        high:   'badge-high',
        medium: 'badge-medium',
        low:    'badge-low',
    };
    const cls = map[priority] || 'badge-low';
    return `<span class="badge ${cls}">${priority}</span>`;
}


/* ── Confirm Helper ──────────────────────────────────────── */
// Simple async confirm — returns true/false
// Usage: if (await confirm('Delete this client?')) { ... }
// FIXED — renamed to avoid conflict
function confirmDialog(msg) {
    return new Promise(resolve => {
        resolve(window.confirm(msg));
    });
}


/* ── Active Nav Link ─────────────────────────────────────── */
// Highlights the current page's nav link in the sidebar
document.addEventListener('DOMContentLoaded', () => {
    const current = window.location.pathname.split('/').pop();
    document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href === current) link.classList.add('active');
    });
});