/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — clients.js
   ═══════════════════════════════════════════════════════════ */

/* ── Search ──────────────────────────────────────────────── */
document.getElementById('searchInput').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.client-card').forEach(card => {
        const name    = card.dataset.name    || '';
        const company = card.dataset.company || '';
        card.style.display = (name.includes(q) || company.includes(q)) ? '' : 'none';
    });
});

/* ── Color Picker ────────────────────────────────────────── */
function initColorPicker(pickerId, inputId) {
    document.querySelectorAll(`#${pickerId} .color-swatch`).forEach(swatch => {
        swatch.addEventListener('click', () => {
            document.querySelectorAll(`#${pickerId} .color-swatch`).forEach(s => s.classList.remove('active'));
            swatch.classList.add('active');
            document.getElementById(inputId).value = swatch.dataset.color;
        });
    });
}
initColorPicker('add-color-picker', 'add-tag-color');
initColorPicker('edit-color-picker', 'edit-tag-color');

/* ── Gender Toggle ───────────────────────────────────────── */
function setGender(btn, prefix) {
    btn.closest('.gender-toggle').querySelectorAll('.gender-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(`${prefix}-gender`).value = btn.dataset.value;
}

/* ── Open Add Panel ──────────────────────────────────────── */
function openAddPanel() {
    ['add-name','add-email','add-company','add-phone','add-notes'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('add-tag-color').value = '#C9C2F0';
    document.getElementById('add-error').textContent = '';
    document.getElementById('temp-password-box').classList.add('hidden');
    document.querySelectorAll('#add-color-picker .color-swatch').forEach((s,i) => s.classList.toggle('active', i===0));
    openPanel('addPanel');
}

/* ── Submit Add Client ───────────────────────────────────── */
async function submitAddClient() {
    const name      = document.getElementById('add-name').value.trim();
    const email     = document.getElementById('add-email').value.trim();
    const company   = document.getElementById('add-company').value.trim();
    const phone     = document.getElementById('add-phone').value.trim();
    const tag_color = document.getElementById('add-tag-color').value;
    const notes     = document.getElementById('add-notes').value.trim();
    const gender    = document.getElementById('add-gender').value;
    const errEl     = document.getElementById('add-error');

    errEl.textContent = '';
    if (!name || !email) { errEl.textContent = 'Name and email are required.'; return; }

    const res = await api('api/clients.php?action=create', { name, email, company, phone, tag_color, notes, gender });
    if (!res) return;
    if (res.error) { errEl.textContent = res.error; return; }

    document.getElementById('tp-email').textContent = email;
    document.getElementById('tp-pass').textContent  = res.temp_password;
    document.getElementById('temp-password-box').classList.remove('hidden');

  toast('Client created! Copy the credentials below.');
   
}

/* ── Open Edit Panel ─────────────────────────────────────── */
function openEditPanel(id, data) {
    document.getElementById('edit-client-id').value  = id;
    document.getElementById('edit-name').value        = data.name    || '';
    document.getElementById('edit-company').value     = data.company || '';
    document.getElementById('edit-phone').value       = data.phone   || '';
    document.getElementById('edit-notes').value       = data.notes   || '';
    document.getElementById('edit-tag-color').value   = data.tag_color || '#C9C2F0';
    document.getElementById('edit-error').textContent = '';

    document.querySelectorAll('#edit-color-picker .color-swatch').forEach(s => {
        s.classList.toggle('active', s.dataset.color === data.tag_color);
    });

    openPanel('editPanel');
}

/* ── Submit Edit Client ──────────────────────────────────── */
async function submitEditClient() {
    const client_id = document.getElementById('edit-client-id').value;
    const name      = document.getElementById('edit-name').value.trim();
    const company   = document.getElementById('edit-company').value.trim();
    const phone     = document.getElementById('edit-phone').value.trim();
    const tag_color = document.getElementById('edit-tag-color').value;
    const notes     = document.getElementById('edit-notes').value.trim();
    const errEl     = document.getElementById('edit-error');

    errEl.textContent = '';
    if (!name) { errEl.textContent = 'Name is required.'; return; }

    const res = await api('api/clients.php?action=update', { client_id, name, company, phone, tag_color, notes });
    if (!res) return;
    if (res.error) { errEl.textContent = res.error; return; }

    toast('Client updated!');
    setTimeout(() => location.reload(), 800);
}

/* ── Archive Client ──────────────────────────────────────── */
async function archiveClient() {
    const client_id = document.getElementById('edit-client-id').value;
    if (!await confirmDialog('Archive this client? They will be hidden but data is kept.')) return;

    const res = await api('api/clients.php?action=archive', { client_id });
    if (!res) return;
    if (res.error) { toast(res.error, 'error'); return; }

    toast('Client archived.');
    setTimeout(() => location.reload(), 800);
}

/* ── Delete Client ───────────────────────────────────────── */
async function deleteClient() {
    const client_id = document.getElementById('edit-client-id').value;
    if (!await confirmDialog('Permanently delete this client? All their projects, tasks and invoices will be deleted too.')) return;

    const res = await api('api/clients.php?action=delete', { client_id });
    if (!res) return;
    if (res.error) { toast(res.error, 'error'); return; }

    toast('Client deleted.');
    setTimeout(() => location.reload(), 800);
}

/* ── Open Detail Panel ───────────────────────────────────── */
async function openDetailPanel(id) {
    document.getElementById('detail-loading').classList.remove('hidden');
    document.getElementById('detail-content').classList.add('hidden');
    openPanel('detailPanel');

    const data = await api(`api/clients.php?action=get_one&id=${id}`);
    if (!data || data.error) {
        document.getElementById('detail-loading').textContent = 'Failed to load client.';
        return;
    }

    // Populate basic info
    document.getElementById('detail-title').textContent     = data.name;
    
    document.getElementById('d-tag-dot').style.background   = data.tag_color;
    document.getElementById('d-company').textContent        = data.company || '';
    document.getElementById('d-phone').textContent          = data.phone   || '';
    document.getElementById('d-projects-count').textContent = data.projects.length;

    // Notes
    const notesWrap = document.getElementById('d-notes-wrap');
    if (data.notes) {
        document.getElementById('d-notes').textContent = data.notes;
        notesWrap.classList.remove('hidden');
    } else {
        notesWrap.classList.add('hidden');
    }

    // Projects
    document.getElementById('d-projects').innerHTML = data.projects.length
        ? data.projects.map(p => `
            <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid rgba(180,160,140,0.1);">
                <img src="images/stickers/${p.emoji}.webp" style="width:28px;height:28px;object-fit:contain;" onerror="this.style.display='none'">
                <div style="flex:1;">
                    <div style="font-size:14px;font-weight:600;color:var(--text);">${p.title}</div>
                    <div style="font-size:12px;color:var(--text-3);">${p.pct}% complete</div>
                </div>
                ${statusBadge(p.status)}
            </div>
        `).join('')
        : '<p style="font-size:13px;color:var(--text-3);">No projects yet.</p>';

    // Invoices
    document.getElementById('d-invoices').innerHTML = data.invoices.length
        ? data.invoices.map(i => `
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(180,160,140,0.1);">
                <div>
                    <div style="font-size:13px;font-weight:600;">${i.invoice_no}</div>
                    <div style="font-size:11px;color:var(--text-3);">${i.due_date ? 'Due ' + i.due_date : 'No due date'}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:14px;font-weight:600;">$${parseFloat(i.total).toFixed(2)}</div>
                    ${statusBadge(i.status)}
                </div>
            </div>
        `).join('')
        : '<p style="font-size:13px;color:var(--text-3);">No invoices yet.</p>';

    // Edit button
    document.getElementById('d-edit-btn').onclick = () => {
        closePanel('detailPanel');
        openEditPanel(data.client_id, data);
    };

    // Show content
    document.getElementById('detail-loading').classList.add('hidden');
    document.getElementById('detail-content').classList.remove('hidden');
}