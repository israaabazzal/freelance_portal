/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — kanban.js
   Drag-and-drop for project cards between status columns
   and tasks within the project detail panel
   ═══════════════════════════════════════════════════════════ */

/* ── Project Kanban (main board) ─────────────────────────── */
let draggedCard   = null;
let draggedFromCol = null;

function initProjectKanban() {
    // Make cards draggable
    document.querySelectorAll('.project-card').forEach(card => {
        card.setAttribute('draggable', true);

        card.addEventListener('dragstart', e => {
            draggedCard    = card;
            draggedFromCol = card.closest('.kanban-col');
            setTimeout(() => card.classList.add('dragging'), 0);
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            document.querySelectorAll('.kanban-col').forEach(col => col.classList.remove('drag-over'));
            draggedCard    = null;
            draggedFromCol = null;
        });
    });

    // Make columns drop targets
    document.querySelectorAll('.kanban-col').forEach(col => {
        col.addEventListener('dragover', e => {
            e.preventDefault();
            col.classList.add('drag-over');
        });

        col.addEventListener('dragleave', e => {
            if (!col.contains(e.relatedTarget)) {
                col.classList.remove('drag-over');
            }
        });

        col.addEventListener('drop', async e => {
            e.preventDefault();
            col.classList.remove('drag-over');

            if (!draggedCard || col === draggedFromCol) return;

            // Move card to new column
            col.appendChild(draggedCard);

            const project_id = draggedCard.dataset.projectId;
            const status      = col.dataset.status;

            // Update status badge on card
            const badge = draggedCard.querySelector('.badge');
            if (badge) badge.outerHTML = statusBadge(status);

            // Update count badges
            updateColCounts();

            // Persist to DB
            const res = await api('api/projects.php?action=update_status', { project_id, status });
            if (!res || res.error) {
                toast('Failed to update status', 'error');
                // Move back on failure
                draggedFromCol.appendChild(draggedCard);
                updateColCounts();
            } else {
                toast('Status updated');
            }
        });
    });
}

function updateColCounts() {
    document.querySelectorAll('.kanban-col').forEach(col => {
        const count = col.querySelectorAll('.project-card').length;
        const badge = col.querySelector('.kanban-count');
        if (badge) badge.textContent = count;
    });
}


/* ── Task Mini-Kanban (inside project detail panel) ──────── */
let draggedTask     = null;
let draggedTaskFrom = null;

function initTaskKanban() {
    document.querySelectorAll('.task-card').forEach(card => {
        card.setAttribute('draggable', true);

        card.addEventListener('dragstart', e => {
            draggedTask     = card;
            draggedTaskFrom = card.closest('.task-col');
            setTimeout(() => card.classList.add('dragging'), 0);
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            document.querySelectorAll('.task-col').forEach(col => col.classList.remove('drag-over'));
            draggedTask     = null;
            draggedTaskFrom = null;
        });
    });

    document.querySelectorAll('.task-col').forEach(col => {
        col.addEventListener('dragover', e => {
            e.preventDefault();
            col.classList.add('drag-over');
        });

        col.addEventListener('dragleave', e => {
            if (!col.contains(e.relatedTarget)) col.classList.remove('drag-over');
        });

        col.addEventListener('drop', async e => {
            e.preventDefault();
            col.classList.remove('drag-over');

            if (!draggedTask) return;

            const taskList   = col.querySelector('.task-list');
            const task_id    = draggedTask.dataset.taskId;
            const column_name = col.dataset.column;

            // Get new position
            taskList.appendChild(draggedTask);
            const position = [...taskList.querySelectorAll('.task-card')].indexOf(draggedTask);

            // Update done indicator
            const doneIndicator = draggedTask.querySelector('.task-done-dot');
            if (doneIndicator) {
                doneIndicator.style.background = column_name === 'done' ? 'var(--mint-deep)' : 'rgba(180,160,140,0.3)';
            }

            // Update task col counts
            updateTaskColCounts();

            // Persist
            const res = await api('api/tasks.php?action=update_column', { task_id, column_name, position });
            if (!res || res.error) {
                toast('Failed to move task', 'error');
                draggedTaskFrom.querySelector('.task-list').appendChild(draggedTask);
                updateTaskColCounts();
            }
        });
    });
}

function updateTaskColCounts() {
    document.querySelectorAll('.task-col').forEach(col => {
        const count = col.querySelectorAll('.task-card').length;
        const badge = col.querySelector('.task-col-count');
        if (badge) badge.textContent = count;
    });
}


/* ── CSS for drag states ─────────────────────────────────── */
const kanbanStyle = document.createElement('style');
kanbanStyle.textContent = `
    .project-card.dragging {
        opacity:    0.4;
        transform:  scale(0.97);
        cursor:     grabbing;
    }
    .kanban-col.drag-over {
        background: rgba(201,194,240,0.25);
        border-color: var(--lavender-deep);
    }
    .task-card.dragging {
        opacity:  0.4;
        transform: scale(0.97);
    }
    .task-col.drag-over {
        background: rgba(201,194,240,0.15);
        border-color: var(--lavender-deep);
    }
`;
document.head.appendChild(kanbanStyle);