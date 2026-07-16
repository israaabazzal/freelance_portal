
let currentProjectId = null;
 
/* ── Init kanban on load ─────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    initProjectKanban();
 
    // Stop click on draggable cards from opening panel during drag
    document.querySelectorAll('.project-card').forEach(card => {
        card.addEventListener('dragstart', () => card._dragging = true);
        card.addEventListener('dragend',   () => setTimeout(() => card._dragging = false, 0));
        card.addEventListener('click', function(e) {
            if (this._dragging) e.stopImmediatePropagation();
        }, true);
    });
});
 
/* ── Emoji Picker ────────────────────────────────────────── */
function selectSticker(img) {
    document.querySelectorAll('.sticker-pick').forEach(s => s.classList.remove('active'));
    img.classList.add('active');
    document.getElementById('add-emoji').value = 'folder';
}

// Select first sticker by default
document.addEventListener('DOMContentLoaded', () => {
    const first = document.querySelector('.sticker-pick');
    if (first) first.classList.add('active');
});
 
/* ── Color Picker ────────────────────────────────────────── */
document.querySelectorAll('#add-cover-picker .color-swatch').forEach(s => {
    s.addEventListener('click', () => {
        document.querySelectorAll('#add-cover-picker .color-swatch').forEach(x => x.classList.remove('active'));
        s.classList.add('active');
        document.getElementById('add-cover-color').value = s.dataset.color;
    });
});
 
/* ── Open Add Panel ──────────────────────────────────────── */
function openAddPanel() {
    document.getElementById('add-client').value      = '';
    document.getElementById('add-title').value       = '';
    document.getElementById('add-description').value = '';
    document.getElementById('add-deadline').value    = '';
    document.getElementById('add-budget').value      = '';
    document.getElementById('add-emoji').value       = '📁';
    document.getElementById('add-cover-color').value = '#FFCBB4';
    document.getElementById('add-error').textContent = '';
    openPanel('addPanel');
}
 
/* ── Submit Add Project ──────────────────────────────────── */
async function submitAddProject() {
    const client_id   = document.getElementById('add-client').value;
    const title       = document.getElementById('add-title').value.trim();
    const description = document.getElementById('add-description').value.trim();
    const deadline    = document.getElementById('add-deadline').value;
    const budget      = document.getElementById('add-budget').value;
    const emoji       = document.getElementById('add-emoji').value;
    const cover_color = document.getElementById('add-cover-color').value;
    const errEl       = document.getElementById('add-error');
 
    errEl.textContent = '';
    if (!client_id) { errEl.textContent = 'Please select a client.'; return; }
    if (!title)     { errEl.textContent = 'Title is required.'; return; }
 
    const res = await api('api/projects.php?action=create', {
        client_id, title, description, deadline, budget, emoji, cover_color
    });
    if (!res) return;
    if (res.error) { errEl.textContent = res.error; return; }
 
    toast('Project created!');
    setTimeout(() => location.reload(), 800);
}
 
/* ── Open Detail Panel ───────────────────────────────────── */
async function openDetailPanel(id) {
    currentProjectId = id;
    document.getElementById('detail-content').innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-3);">Loading...</div>';
    openPanel('detailPanel');
 
    const data = await api(`api/projects.php?action=get_one&id=${id}`);
    if (!data || data.error) {
        document.getElementById('detail-content').innerHTML = '<p style="color:#E24B4A;">Failed to load project.</p>';
        return;
    }

    // Store current project data globally for edit panel
        window._currentProject = data;
 
document.getElementById('detail-title').textContent = data.title; // ← remove emoji

// Task columns
const cols = ['todo','in_progress','done'];
const colLabels = { todo: 'To Do', in_progress: 'In Progress', done: 'Done' };

const taskKanban = cols.map(col => `
    <div class="task-col" data-column="${col}">
        <div class="task-col-header">
            <span class="task-col-title">${colLabels[col]}</span>
            <span class="task-col-count">${(data.tasks[col] || []).length}</span>
        </div>
        <div class="task-list">
            ${(data.tasks[col] || []).map(t => `
                <div class="task-card" data-task-id="${t.task_id}">
                    <div class="task-done-dot" style="background:${col === 'done' ? 'var(--mint-deep)' : 'rgba(180,160,140,0.3)'}"></div>
                    <div class="task-card-title ${col === 'done' ? 'done' : ''}">${t.title}</div>
                    ${priorityBadge(t.priority)}
                    <button onclick="deleteTask(${t.task_id}, ${data.project_id})"
                            style="background:none;border:none;cursor:pointer;color:var(--text-3);padding:0 0 0 4px;font-size:14px;flex-shrink:0;"
                            title="Delete task">🗑️</button>
                </div>
            `).join('')}
        </div>
    </div>
`).join('');




 
    // Invoices
    const invoiceList = (data.invoices || []).map(i => `
        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(180,160,140,0.1);">
            <div>
                <div style="font-size:13px;font-weight:600;">${i.invoice_no}</div>
                <div style="font-size:11px;color:var(--text-3);">${i.due_date ? 'Due ' + i.due_date : ''}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:14px;font-weight:600;">$${parseFloat(i.total).toFixed(2)}</div>
                ${statusBadge(i.status)}
            </div>
        </div>
    `).join('') || '<p style="font-size:13px;color:var(--text-3);">No invoices yet.</p>';
 
    document.getElementById('detail-content').innerHTML = `
        <!-- Cover strip -->
        <div style="height:6px;border-radius:6px;background:${data.cover_color};margin:-0px 0 20px;"></div>
 
        <!-- Meta -->
        <div class="detail-meta">
            <div class="detail-meta-item">
                <span class="detail-meta-label">Client</span>
                <span class="detail-meta-value">${data.client_name}</span>
            </div>
            <div class="detail-meta-item">
                <span class="detail-meta-label">Status</span>
                <span class="detail-meta-value">${statusBadge(data.status)}</span>
            </div>
            ${data.deadline ? `
            <div class="detail-meta-item">
                <span class="detail-meta-label">Deadline</span>
                <span class="detail-meta-value">${data.deadline}</span>
            </div>` : ''}
            ${data.budget ? `
            <div class="detail-meta-item">
                <span class="detail-meta-label">Budget</span>
                <span class="detail-meta-value">$${parseFloat(data.budget).toLocaleString()}</span>
            </div>` : ''}
        </div>
 
        <!-- Progress -->
        ${data.total_tasks > 0 ? `
        <div style="margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-2);margin-bottom:5px;">
                <span>${data.done_tasks} / ${data.total_tasks} tasks done</span>
                <span>${data.pct}%</span>
            </div>
            <div style="background:rgba(180,160,140,0.15);border-radius:99px;height:6px;overflow:hidden;">
                <div style="height:100%;width:${data.pct}%;border-radius:99px;background:var(--lavender-deep);transition:width 0.6s;"></div>
            </div>
        </div>` : ''}
 
        <!-- Tasks -->
        <div class="panel-section">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <div class="panel-section-title" style="margin-bottom:0;">Tasks</div>
                <button class="btn btn-ghost" style="padding:5px 10px;font-size:12px;" onclick="openAddTaskForm(${data.project_id})">+ Add Task</button>
            </div>
            <div id="add-task-form-${data.project_id}" class="hidden" style="margin-bottom:10px;padding:12px;background:rgba(255,255,255,0.6);border-radius:var(--r-sm);border:1.5px solid var(--border);">
                <input type="text" id="new-task-title" placeholder="Task title..." style="width:100%;padding:8px;border:1.5px solid rgba(180,160,140,0.25);border-radius:var(--r-xs);font-family:inherit;font-size:13px;margin-bottom:8px;">
                <div style="display:flex;gap:8px;">
                    <select id="new-task-priority" style="flex:1;padding:7px;border:1.5px solid rgba(180,160,140,0.25);border-radius:var(--r-xs);font-family:inherit;font-size:13px;">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>

<input type="date" id="new-task-due" style="flex:1;padding:7px;border:1.5px solid rgba(180,160,140,0.25);border-radius:var(--r-xs);font-family:inherit;font-size:13px;">
                    <button class="btn btn-primary" style="padding:7px 14px;font-size:13px;" onclick="submitAddTask(${data.project_id})">Add</button>
                    <button class="btn btn-ghost" style="padding:7px 10px;font-size:13px;" onclick="document.getElementById('add-task-form-${data.project_id}').classList.add('hidden')">✕</button>
                </div>
            </div>
            <div class="task-kanban">${taskKanban}</div>
        </div>
 
        <!-- Invoices -->
        <div class="panel-section">
            <div class="panel-section-title">Invoices</div>
            ${invoiceList}
        </div>
 
        <!-- Actions -->
        <div class="panel-section" style="display:flex;gap:8px;">
            <button class="btn btn-ghost" onclick="openEditProjectPanel()">Edit</button>
            <button class="btn btn-ghost" style="flex:1;" onclick="openEditStatus(${data.project_id}, '${data.status}')">Change Status</button>
            <button class="btn btn-danger" onclick="deleteProject(${data.project_id})">Delete</button>
        </div>
    `;
 
    // Init task drag-drop
    initTaskKanban();
}
 
/* ── Add Task ────────────────────────────────────────────── */
function openAddTaskForm(project_id) {
    document.getElementById(`add-task-form-${project_id}`).classList.toggle('hidden');
    document.getElementById('new-task-title').focus();
}
 
async function submitAddTask(project_id) {
    const title    = document.getElementById('new-task-title').value.trim();
    const priority = document.getElementById('new-task-priority').value;
    const due_date = document.getElementById('new-task-due').value;
    if (!title) return;

    const res = await api('api/tasks.php?action=create', { project_id, title, priority, due_date });
    if (!res || res.error) { toast('Failed to add task', 'error'); return; }

    toast('Task added!');
    openDetailPanel(project_id);
}
 
/* ── Change Status ───────────────────────────────────────── */
function openEditStatus(project_id, current) {
    const statuses = ['draft','in_progress','review','completed','cancelled'];
    const labels   = { draft:'Draft', in_progress:'In Progress', review:'Review', completed:'Completed', cancelled:'Cancelled' };
    const options  = statuses.map(s =>
        `<option value="${s}" ${s === current ? 'selected' : ''}>${labels[s]}</option>`
    ).join('');
 
    const html = `
        <div style="padding:16px;">
            <p style="font-size:13px;color:var(--text-2);margin-bottom:10px;">Select new status:</p>
            <select id="status-select" style="width:100%;padding:9px;border:1.5px solid rgba(180,160,140,0.25);border-radius:var(--r-sm);font-family:inherit;font-size:14px;">${options}</select>
            <div style="display:flex;gap:8px;margin-top:12px;">
                <button class="btn btn-primary" style="flex:1;" onclick="submitStatusChange(${project_id})">Update</button>
                <button class="btn btn-ghost" onclick="openDetailPanel(${project_id})">Cancel</button>
            </div>
        </div>
    `;
    document.getElementById('detail-content').innerHTML = html;
}
 
async function submitStatusChange(project_id) {
    const status = document.getElementById('status-select').value;
    const res = await api('api/projects.php?action=update_status', { project_id, status });
    if (!res || res.error) { toast('Failed to update', 'error'); return; }
    toast('Status updated!');
    setTimeout(() => location.reload(), 600);
}
 
/* ── Delete Project ──────────────────────────────────────── */
async function deleteProject(project_id) {
    if (!await confirmDialog('Delete this project? All tasks and invoices will be deleted too.')) return;
    const res = await api('api/projects.php?action=delete', { project_id });
    if (!res || res.error) { toast('Failed to delete', 'error'); return; }
    toast('Project deleted.');
    closePanel('detailPanel');
    setTimeout(() => location.reload(), 800);
}


  // delete task 
async function deleteTask(task_id, project_id) {
    if (!await confirmDialog('Delete this task?')) return;
    const res = await api('api/tasks.php?action=delete', { task_id });
    if (!res || res.error) { toast('Failed to delete task', 'error'); return; }
    toast('Task deleted.');
    openDetailPanel(project_id);
}

/* ── Open Edit Project Panel ─────────────────────────────── */
function openEditProjectPanel() {
    const data = window._currentProject;
    if (!data) return;

    document.getElementById('edit-project-id').value          = data.project_id;
    document.getElementById('edit-project-title').value       = data.title       || '';
    document.getElementById('edit-project-description').value = data.description || '';
    document.getElementById('edit-project-deadline').value    = data.deadline    || '';
    document.getElementById('edit-project-budget').value      = data.budget      || '';
    document.getElementById('edit-project-emoji').value       = data.emoji       || 'folder';
    document.getElementById('edit-project-color').value       = data.cover_color || '#FFCBB4';
    document.getElementById('edit-project-error').textContent = '';

    // Set active sticker
    document.querySelectorAll('#edit-sticker-picker .sticker-pick').forEach(s => {
        s.classList.toggle('active', s.dataset.emoji === data.emoji);
    });

    // Set active color swatch + click
        document.querySelectorAll('#edit-cover-picker .color-swatch').forEach(s => {
            s.classList.toggle('active', s.dataset.color === data.cover_color);
            s.onclick = () => {
                document.querySelectorAll('#edit-cover-picker .color-swatch').forEach(x => x.classList.remove('active'));
                s.classList.add('active');
                document.getElementById('edit-project-color').value = s.dataset.color;
            };
        });

    openPanel('editProjectPanel');
}

function selectEditSticker(img) {
    document.querySelectorAll('#edit-sticker-picker .sticker-pick').forEach(s => s.classList.remove('active'));
    img.classList.add('active');
    document.getElementById('edit-project-emoji').value = img.dataset.emoji;
}

/* ── Submit Edit Project ─────────────────────────────────── */
async function submitEditProject() {
    const project_id  = document.getElementById('edit-project-id').value;
    const title       = document.getElementById('edit-project-title').value.trim();
    const description = document.getElementById('edit-project-description').value.trim();
    const deadline    = document.getElementById('edit-project-deadline').value;
    const budget      = document.getElementById('edit-project-budget').value;
    const emoji       = document.getElementById('edit-project-emoji').value;
    const cover_color = document.getElementById('edit-project-color').value;
    const errEl       = document.getElementById('edit-project-error');

    errEl.textContent = '';
    if (!title) { errEl.textContent = 'Title is required.'; return; }

    const res = await api('api/projects.php?action=update', {
        project_id, title, description, deadline, budget, emoji, cover_color
    });
    if (!res) return;
    if (res.error) { errEl.textContent = res.error; return; }

    toast('Project updated!');
    closePanel('editProjectPanel');
    setTimeout(() => location.reload(), 800);
}
