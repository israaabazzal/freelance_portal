/* ═══════════════════════════════════════════════════════════
   FREELANCE PORTAL — invoices.js
   Filter tabs, line item calculator, create, view, mark paid
   ═══════════════════════════════════════════════════════════ */

/* ── Filter Tabs ─────────────────────────────────────────── */
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const filter = btn.dataset.filter;
        document.querySelectorAll('.invoice-card').forEach(card => {
            card.style.display = (filter === 'all' || card.dataset.status === filter) ? '' : 'none';
        });
    });
});


/* ── Live Line Item Calculator ───────────────────────────── */
function recalcTotal() {
    let total = 0;
    document.querySelectorAll('.line-item').forEach(row => {
        const qty   = parseFloat(row.querySelector('.qty').value)   || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        const sub   = qty * price;
        row.querySelector('.subtotal').textContent = '$' + sub.toFixed(2);
        total += sub;
    });
    document.getElementById('grand-total').textContent = '$' + total.toFixed(2);
}

// Listen on existing row
document.querySelectorAll('.qty, .price').forEach(input => {
    input.addEventListener('input', recalcTotal);
});

// Add line item
document.getElementById('add-line').addEventListener('click', () => {
    const tbody = document.getElementById('line-items');
    const newRow = document.createElement('tr');
    newRow.className = 'line-item';
    newRow.innerHTML = `
        <td class="col-desc"><input type="text" class="desc" placeholder="Service description"></td>
        <td class="col-qty"><input type="number" class="qty" value="1" min="0.01" step="0.01"></td>
        <td class="col-price"><input type="number" class="price" placeholder="0.00" min="0" step="0.01"></td>
        <td class="col-sub"><span class="subtotal">$0.00</span></td>
        <td class="col-del"><button type="button" class="remove-line" onclick="removeLine(this)">×</button></td>
    `;
    tbody.appendChild(newRow);
    newRow.querySelectorAll('.qty, .price').forEach(i => i.addEventListener('input', recalcTotal));
    newRow.querySelector('.desc').focus();
});

function removeLine(btn) {
    const rows = document.querySelectorAll('.line-item');
    if (rows.length <= 1) return; // keep at least one row
    btn.closest('.line-item').remove();
    recalcTotal();
}


/* ── Open New Invoice Panel ──────────────────────────────── */
function openNewInvoicePanel() {

    document.querySelector('#newPanel .panel-title').textContent = 'New Invoice';
    const saveBtn = document.querySelector('#newPanel .btn-primary');
    saveBtn.textContent = 'Create Invoice';
    saveBtn.onclick = submitNewInvoice;

    document.getElementById('new-project').value  = '';
    document.getElementById('new-due-date').value = '';
    document.getElementById('new-notes').value    = '';
    document.getElementById('new-error').textContent = '';
    document.getElementById('grand-total').textContent = '$0.00';

    // Reset to one empty line
    document.getElementById('line-items').innerHTML = `
        <tr class="line-item">
            <td class="col-desc"><input type="text" class="desc" placeholder="Service description"></td>
            <td class="col-qty"><input type="number" class="qty" value="1" min="0.01" step="0.01"></td>
            <td class="col-price"><input type="number" class="price" placeholder="0.00" min="0" step="0.01"></td>
            <td class="col-sub"><span class="subtotal">$0.00</span></td>
            <td class="col-del"><button type="button" class="remove-line" onclick="removeLine(this)">×</button></td>
        </tr>
    `;
    document.querySelectorAll('.qty, .price').forEach(i => i.addEventListener('input', recalcTotal));

    openPanel('newPanel');
}


/* ── Submit New Invoice ──────────────────────────────────── */
async function submitNewInvoice() {
    const project_id = document.getElementById('new-project').value;
    const due_date   = document.getElementById('new-due-date').value;
    const notes      = document.getElementById('new-notes').value.trim();
    const errEl      = document.getElementById('new-error');

    errEl.textContent = '';
    if (!project_id) { errEl.textContent = 'Please select a project.'; return; }

    // Collect line items
    const items = [];
    document.querySelectorAll('.line-item').forEach(row => {
        const description = row.querySelector('.desc').value.trim();
        const quantity    = parseFloat(row.querySelector('.qty').value)   || 0;
        const unit_price  = parseFloat(row.querySelector('.price').value) || 0;
        if (description && unit_price > 0) {
            items.push({ description, quantity, unit_price });
        }
    });

    if (items.length === 0) {
        errEl.textContent = 'Add at least one line item with a description and price.';
        return;
    }

    const res = await api('api/invoices.php?action=create', { project_id, due_date, notes, items });
    if (!res) return;
    if (res.error) { errEl.textContent = res.error; return; }

    toast('Invoice created!');
    setTimeout(() => location.reload(), 800);
}


/* ── Open View Panel ─────────────────────────────────────── */
let currentInvoiceId = null;

async function openViewPanel(id) {
    currentInvoiceId = id;

    // Show loading, hide content
    document.getElementById('view-loading').classList.remove('hidden');
    document.getElementById('view-content').classList.add('hidden');
    openPanel('viewPanel');

    const data = await api(`api/invoices.php?action=get_one&id=${id}`);
    if (!data || data.error) {
        document.getElementById('view-loading').textContent = 'Failed to load invoice.';
        return;
    }

    // Populate text fields
    document.getElementById('view-title').textContent    = data.invoice_no;
    document.getElementById('v-invoice-no').textContent  = data.invoice_no;
    document.getElementById('v-client').textContent      = data.client_name;
    document.getElementById('v-company').textContent     = data.company || '';
    document.getElementById('v-issued').textContent      = data.issued_at ? 'Issued: ' + data.issued_at.substring(0, 10) : '';
    document.getElementById('v-due').textContent         = data.due_date  ? 'Due: ' + data.due_date : '';
    document.getElementById('v-project').innerHTML       = 'Project: <strong>' + data.project_title + '</strong>';
    document.getElementById('v-total').textContent       = '$' + parseFloat(data.total).toFixed(2);

    // Notes
    const notesWrap = document.getElementById('v-notes-wrap');
    if (data.notes) {
        document.getElementById('v-notes').textContent = data.notes;
        notesWrap.classList.remove('hidden');
    } else {
        notesWrap.classList.add('hidden');
    }

    // Line items
    const tbody = document.getElementById('v-items');
    tbody.innerHTML = (data.items || []).map(item => `
        <tr>
            <td>${item.description}</td>
            <td style="text-align:center;">${item.quantity}</td>
            <td style="text-align:right;">$${parseFloat(item.unit_price).toFixed(2)}</td>
            <td>$${parseFloat(item.subtotal).toFixed(2)}</td>
        </tr>
    `).join('');

    // Action buttons
    const canPay    = data.status === 'unpaid' || data.status === 'overdue';
    const canDelete = data.status === 'draft';

    const canEdit = data.status === 'draft';
    document.getElementById('v-btn-edit').classList.toggle('hidden', !canEdit);
    document.getElementById('v-btn-edit').onclick = () => openEditInvoice(data);


    document.getElementById('v-btn-paid').classList.toggle('hidden', !canPay);
    document.getElementById('v-paid-badge').classList.toggle('hidden', data.status !== 'paid');
    document.getElementById('v-btn-delete').classList.toggle('hidden', !canDelete);

    // Status select
    document.getElementById('v-status-select').value = data.status;

    // Wire up buttons with current invoice id
    document.getElementById('v-btn-paid').onclick   = () => markPaid(id);
    document.getElementById('v-btn-delete').onclick = () => deleteInvoice(id);
    document.getElementById('v-btn-status').onclick = () => changeStatus(id);

    // Show content
    document.getElementById('view-loading').classList.add('hidden');
    document.getElementById('view-content').classList.remove('hidden');
}


/* ── Mark as Paid ────────────────────────────────────────── */
async function markPaid(invoice_id) {
    const res = await api('api/invoices.php?action=update_status', { invoice_id, status: 'paid' });
    if (!res || res.error) { toast('Failed to update', 'error'); return; }
    toast('Invoice marked as paid ✓');
    setTimeout(() => location.reload(), 800);
}


/* ── Change Status ───────────────────────────────────────── */
async function changeStatus(invoice_id) {
    const status = document.getElementById('v-status-select').value;
    const res = await api('api/invoices.php?action=update_status', { invoice_id, status });
    if (!res || res.error) { toast('Failed to update', 'error'); return; }
    toast('Status updated!');
    setTimeout(() => location.reload(), 800);
}


/* ── Delete Invoice ──────────────────────────────────────── */
async function deleteInvoice(invoice_id) {
    if (!await confirmDialog('Delete this draft invoice?')) return;
    const res = await api('api/invoices.php?action=delete', { invoice_id });
    if (!res || res.error) { toast(res?.error || 'Failed to delete', 'error'); return; }
    toast('Invoice deleted.');
    closePanel('viewPanel');
    setTimeout(() => location.reload(), 800);
}


/* ── Edit Invoice (draft only) ───────────────────────────── */
function openEditInvoice(data) {
    closePanel('viewPanel');

    // Populate the new invoice panel with existing data
    document.getElementById('new-project').value  = data.project_id;
    document.getElementById('new-due-date').value = data.due_date || '';
    document.getElementById('new-notes').value    = data.notes   || '';
    document.getElementById('new-error').textContent = '';

    // Populate line items
    const tbody = document.getElementById('line-items');
    tbody.innerHTML = data.items.map(item => `
        <tr class="line-item">
            <td class="col-desc"><input type="text" class="desc" value="${item.description}"></td>
            <td class="col-qty"><input type="number" class="qty" value="${item.quantity}" min="0.01" step="0.01"></td>
            <td class="col-price"><input type="number" class="price" value="${item.unit_price}" min="0" step="0.01"></td>
            <td class="col-sub"><span class="subtotal">$${parseFloat(item.subtotal).toFixed(2)}</span></td>
            <td class="col-del"><button type="button" class="remove-line" onclick="removeLine(this)">×</button></td>
        </tr>
    `).join('');

    // Wire up recalc on existing rows
    document.querySelectorAll('.qty, .price').forEach(i => i.addEventListener('input', recalcTotal));
    recalcTotal();

    // Change panel title and button to "Save Changes"
    document.querySelector('#newPanel .panel-title').textContent = 'Edit Invoice';
    const saveBtn = document.querySelector('#newPanel .btn-primary');
    saveBtn.textContent = 'Save Changes';
    saveBtn.onclick = () => submitEditInvoice(data.invoice_id);

    openPanel('newPanel');
}

/* ── Submit Edit Invoice ─────────────────────────────────── */
async function submitEditInvoice(invoice_id) {
    const due_date = document.getElementById('new-due-date').value;
    const notes    = document.getElementById('new-notes').value.trim();
    const errEl    = document.getElementById('new-error');

    errEl.textContent = '';

    const items = [];
    document.querySelectorAll('.line-item').forEach(row => {
        const description = row.querySelector('.desc').value.trim();
        const quantity    = parseFloat(row.querySelector('.qty').value)   || 0;
        const unit_price  = parseFloat(row.querySelector('.price').value) || 0;
        if (description && unit_price > 0) {
            items.push({ description, quantity, unit_price });
        }
    });

    if (items.length === 0) {
        errEl.textContent = 'Add at least one line item.';
        return;
    }

    const res = await api('api/invoices.php?action=update', { invoice_id, due_date, notes, items });
    if (!res) return;
    if (res.error) { errEl.textContent = res.error; return; }

    // Reset panel title and button back to original
    document.querySelector('#newPanel .panel-title').textContent = 'New Invoice';
    const saveBtn = document.querySelector('#newPanel .btn-primary');
    saveBtn.textContent = 'Create Invoice';
    saveBtn.onclick = submitNewInvoice;

    toast('Invoice updated!');
    setTimeout(() => location.reload(), 800);
}