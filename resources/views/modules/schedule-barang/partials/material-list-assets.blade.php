@push('styles')
<style>
    .material-table { min-width: 1540px; }
    .material-table .material-plant { min-width: 82px; color: #315a87; font-weight: 700; }
    .material-table .material-unit { min-width: 160px; font-weight: 600; }
    .material-table .material-number { min-width: 105px; font-variant-numeric: tabular-nums; font-weight: 600; }
    .material-table .material-text { min-width: 245px; max-width: 330px; font-weight: 600; line-height: 1.55; }
    .material-table .material-detail { min-width: 130px; color: #60758b; }
    .material-status { display: inline-flex; min-width: 38px; justify-content: center; padding: 5px 7px; border: 1px solid; border-radius: 5px; font-size: 9px; font-weight: 800; }
    .material-status.is-ok { border-color: #a7dfca; background: #eaf9f2; color: #19875e; }
    .material-status.is-no { border-color: #efbac3; background: #fff0f2; color: #cc3c52; }
    .material-cancel { min-width: 58px; }
    .material-cancel.is-cancelled { border-color: #f0b8c0; background: #fff0f2; color: #c93f52; }
    .material-drawing { border-color: #efc6cd; background: #fff4f5; color: #c43b50; }
    .material-drawing.is-empty { border-color: #dbe3eb; background: #f8fafc; color: #9aa8b7; }
    .material-work-list .bwl-check { min-height: 38px; display: flex; flex-direction: row; align-items: center; gap: 9px; padding-top: 21px; }
    .material-work-list .bwl-check input { width: 16px; height: 16px; accent-color: #214f82; }
    .material-work-list .bwl-cancelled td { background: #fffafb; }
    .material-work-list .bwl-cancelled .bwl-total { color: #8b99aa; text-decoration: line-through; }
    @media (max-width: 575.98px) { .material-work-list .bwl-check { padding-top: 0; } }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-material-work-list]');
    if (!root) return;

    const rows = {{ Illuminate\Support\Js::from($materialRows) }}.map(row => ({ ...row, notes: [...row.notes] }));
    const mode = root.dataset.mode;
    const year = root.querySelector('[data-year]');
    const plant = root.querySelector('[data-plant]');
    const search = root.querySelector('[data-search]');
    const pageSize = root.querySelector('[data-page-size]');
    const tbody = root.querySelector('[data-rows]');
    const pagination = root.querySelector('[data-pagination]');
    const feedback = root.querySelector('[data-feedback]');
    const editElement = root.querySelector('#material-edit-modal');
    const notesElement = root.querySelector('#material-notes-modal');
    const form = root.querySelector('#material-edit-form');
    const noteForm = root.querySelector('#material-note-form');
    const noteInput = root.querySelector('#material-note-input');
    const money = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const quantityFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
    const formFields = ['year', 'plant', 'unit', 'mrp', 'material_no', 'short_text', 'quantity', 'uom', 'price', 'bom_status', 'rif'];
    const detailFields = ['plant_section', 'program', 'equipment', 'functional_location', 'lifetime', 'estimation', 'order', 'reservation', 'pic_evaluator', 'evaluation_note'];
    let page = 1;
    let expanded = false;
    let editingId = null;
    let notesId = null;
    let sequence = 0;

    const createElement = (tag, text, className) => {
        const node = document.createElement(tag);
        if (text !== undefined) node.textContent = text;
        if (className) node.className = className;
        return node;
    };

    const announce = message => {
        feedback.textContent = message;
        feedback.hidden = false;
    };

    const belongsToTab = row => mode === 'cancel' ? row.cancelled : mode === 'lanjut' ? !row.cancelled : true;
    const getFilteredRows = () => {
        const term = search.value.trim().toLocaleLowerCase('id-ID');
        return rows.filter(row => belongsToTab(row)
            && (!year.value || String(row.year) === year.value)
            && (!plant.value || row.plant === plant.value)
            && (!term || [...formFields, ...detailFields, 'bom_status'].map(key => String(row[key] ?? '')).join(' ').toLocaleLowerCase('id-ID').includes(term)));
    };
    const total = row => row.cancelled ? 0 : row.quantity * row.price;

    const actionButton = (action, row, icon, label, className = '') => {
        const button = createElement('button', undefined, 'bwl-icon ' + className);
        button.type = 'button';
        button.dataset.action = action;
        button.dataset.rowId = row.id;
        button.title = label;
        button.setAttribute('aria-label', label + ': ' + row.short_text);
        const glyph = createElement('i', undefined, 'bi ' + icon);
        glyph.setAttribute('aria-hidden', 'true');
        button.append(glyph);
        return button;
    };

    const renderRow = (row, number) => {
        const tr = createElement('tr');
        if (row.cancelled) tr.classList.add('bwl-cancelled');
        const cell = (value, className = '', detail = false) => {
            const td = createElement('td', value, className);
            if (detail) td.hidden = !expanded;
            tr.append(td);
            return td;
        };
        cell(number, 'bwl-center');
        cell(row.year, 'bwl-center', true);
        cell(row.plant, 'material-plant');
        cell(row.unit, 'material-unit');
        cell(row.mrp, 'bwl-center');
        cell(row.material_no, 'material-number');
        cell(row.short_text, 'material-text');
        cell(quantityFormat.format(row.quantity), 'bwl-center');
        cell(row.uom, 'bwl-center');
        cell(money.format(row.price), 'bwl-number');
        cell(money.format(total(row)), 'bwl-number bwl-total');
        cell(undefined, 'bwl-center').append(createElement('span', row.bom_status, 'material-status ' + (row.bom_status === 'OK' ? 'is-ok' : 'is-no')));
        detailFields.forEach(key => cell(row[key] || '—', 'material-detail', true));

        const drawing = actionButton('drawing', row, row.has_drawing ? 'bi-file-earmark-pdf-fill' : 'bi-dash', row.has_drawing ? 'Lihat drawing' : 'Drawing belum tersedia', 'material-drawing ' + (row.has_drawing ? '' : 'is-empty'));
        cell(undefined, 'bwl-center').append(drawing);
        cell(row.rif || '—', 'bwl-center');
        cell(undefined, 'bwl-center').append(actionButton('edit', row, 'bi-pencil-square', 'Edit material', 'bwl-icon-edit'));

        const cancelCell = cell(undefined, 'bwl-center');
        if (row.cancelled) {
            const restore = createElement('button', 'Pulihkan', 'bwl-button material-cancel is-cancelled');
            restore.type = 'button';
            restore.dataset.action = 'cancel';
            restore.dataset.rowId = row.id;
            restore.title = 'Pulihkan ajuan';
            cancelCell.append(restore);
        } else {
            cancelCell.append(actionButton('cancel', row, 'bi-x-lg', 'Batalkan ajuan', 'material-cancel'));
        }

        const noteButton = actionButton('notes', row, 'bi-chat-left-text', row.notes.length + ' catatan');
        if (row.notes.length) {
            const count = createElement('span', row.notes.length > 99 ? '99+' : row.notes.length, 'bwl-note-count');
            count.setAttribute('aria-hidden', 'true');
            noteButton.append(count);
        }
        cell(undefined, 'bwl-center').append(noteButton);
        return tr;
    };

    const pageButton = (label, target, disabled, current = false) => {
        const button = createElement('button', label, 'bwl-page');
        button.type = 'button';
        button.disabled = disabled;
        button.setAttribute('aria-label', label === '‹' ? 'Halaman sebelumnya' : label === '›' ? 'Halaman berikutnya' : 'Halaman ' + label);
        if (current) button.setAttribute('aria-current', 'page');
        button.addEventListener('click', () => { page = target; render(); root.querySelector('.bwl-table-scroll').scrollTop = 0; });
        return button;
    };

    const render = () => {
        const filtered = getFilteredRows();
        const size = Number(pageSize.value);
        const pages = Math.max(1, Math.ceil(filtered.length / size));
        page = Math.min(Math.max(page, 1), pages);
        const start = (page - 1) * size;
        const fragment = document.createDocumentFragment();
        filtered.slice(start, start + size).forEach((row, offset) => fragment.append(renderRow(row, start + offset + 1)));
        tbody.replaceChildren(fragment);
        root.querySelector('[data-empty]').hidden = filtered.length > 0;
        root.querySelector('[data-summary]').textContent = filtered.length
            ? 'Menampilkan ' + (start + 1) + '–' + Math.min(start + size, filtered.length) + ' dari ' + filtered.length + ' material'
            : 'Menampilkan 0 material';
        pagination.replaceChildren(pageButton('‹', page - 1, page === 1));
        for (let current = 1; current <= pages; current += 1) pagination.append(pageButton(String(current), current, false, current === page));
        pagination.append(pageButton('›', page + 1, page === pages));
        root.querySelector('[data-export]').disabled = filtered.length === 0;
    };

    [year, plant, pageSize].forEach(control => control.addEventListener('change', () => { page = 1; render(); }));
    search.addEventListener('input', () => { page = 1; render(); });
    root.querySelector('[data-reset]').addEventListener('click', () => { year.value = ''; plant.value = ''; search.value = ''; page = 1; render(); search.focus(); });

    const setExpanded = value => {
        expanded = value;
        root.querySelectorAll('[data-material-detail]').forEach(cell => { cell.hidden = !value; });
        const toggle = root.querySelector('[data-toggle-detail]');
        if (toggle) { toggle.setAttribute('aria-pressed', String(value)); toggle.querySelector('span').textContent = value ? 'Kolom Ringkas' : 'Detail BOM'; }
        root.querySelector('[data-close-detail]').hidden = !value;
        render();
    };
    root.querySelector('[data-toggle-detail]')?.addEventListener('click', () => setExpanded(!expanded));
    root.querySelector('[data-close-detail]').addEventListener('click', () => { setExpanded(false); root.querySelector('[data-toggle-detail]')?.focus(); });

    const showModal = modalElement => {
        if (!window.bootstrap?.Modal) { announce('Modal belum dapat dimuat. Muat ulang halaman dan coba lagi.'); return; }
        window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
    };
    const updateTotal = () => {
        const editingRow = rows.find(row => row.id === editingId);
        const value = (mode === 'cancel' || editingRow?.cancelled)
            ? 0
            : Number(form.elements.price.value) * Number(form.elements.quantity.value);
        form.querySelector('[data-total]').textContent = 'Rp ' + money.format(Number.isFinite(value) ? value : 0);
    };
    const openEditor = row => {
        editingId = row?.id ?? null;
        form.reset();
        const values = row || { year: Number(year.value || 2026), plant: plant.value || 'Tonasa 5', unit: '', mrp: 'M01', material_no: '', short_text: '', quantity: 1, uom: 'PCE', price: 0, bom_status: 'NO', rif: '' };
        formFields.forEach(key => { form.elements[key].value = values[key]; });
        form.elements.has_drawing.checked = Boolean(values.has_drawing);
        root.querySelector('#material-edit-title').textContent = row ? 'Edit Ajuan Barang' : 'Tambah Ajuan Barang';
        updateTotal();
        showModal(editElement);
    };
    form.addEventListener('input', updateTotal);
    root.querySelector('[data-add]').addEventListener('click', () => openEditor(null));
    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const values = Object.fromEntries(formFields.map(key => [key, form.elements[key].value.trim()]));
        ['year', 'quantity', 'price'].forEach(key => { values[key] = Number(values[key]); });
        values.has_drawing = form.elements.has_drawing.checked;
        if (!Number.isFinite(values.quantity * values.price)) { announce('Quantity atau harga satuan tidak valid.'); return; }
        const existing = rows.find(row => row.id === editingId);
        if (existing) Object.assign(existing, values);
        else rows.unshift({ ...values, id: 'material-local-' + (++sequence), cancelled: mode === 'cancel', notes: [], ...Object.fromEntries(detailFields.map(key => [key, '—'])) });
        page = 1;
        render();
        window.bootstrap.Modal.getInstance(editElement)?.hide();
        announce('Ajuan material diperbarui pada pratinjau dan belum tersimpan ke server.');
    });

    const renderNotes = () => {
        const row = rows.find(item => item.id === notesId);
        if (!row) return;
        root.querySelector('[data-notes-description]').textContent = row.material_no + ' · ' + row.short_text;
        const list = root.querySelector('[data-notes-list]');
        list.replaceChildren();
        if (!row.notes.length) { list.append(createElement('p', 'Belum ada catatan untuk material ini.', 'bwl-notes-empty')); return; }
        row.notes.forEach(note => {
            const card = createElement('article', undefined, 'bwl-note');
            card.append(createElement('p', note.text));
            const footer = createElement('div', undefined, 'bwl-note-footer');
            footer.append(createElement('span', note.time));
            const remove = createElement('button');
            remove.type = 'button'; remove.title = 'Hapus catatan'; remove.setAttribute('aria-label', 'Hapus catatan');
            const icon = createElement('i', undefined, 'bi bi-trash3'); icon.setAttribute('aria-hidden', 'true'); remove.append(icon);
            remove.addEventListener('click', () => { if (!window.confirm('Hapus catatan ini dari pratinjau?')) return; row.notes = row.notes.filter(item => item.id !== note.id); renderNotes(); render(); });
            footer.append(remove); card.append(footer); list.append(card);
        });
    };

    tbody.addEventListener('click', event => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        const row = rows.find(item => item.id === button.dataset.rowId);
        if (!row) return;
        if (button.dataset.action === 'edit') openEditor(row);
        if (button.dataset.action === 'drawing') announce(row.has_drawing ? 'Drawing ' + row.material_no + ' siap dihubungkan ke dokumen backend.' : 'Drawing untuk ' + row.material_no + ' belum tersedia.');
        if (button.dataset.action === 'notes') { notesId = row.id; noteInput.value = ''; renderNotes(); showModal(notesElement); }
        if (button.dataset.action === 'cancel') {
            const action = row.cancelled ? 'memulihkan' : 'membatalkan';
            if (!window.confirm('Yakin ingin ' + action + ' ajuan ' + row.material_no + ' pada pratinjau?')) return;
            row.cancelled = !row.cancelled; page = 1; render(); announce('Status ajuan diperbarui pada pratinjau dan belum tersimpan ke server.');
        }
    });

    noteForm.addEventListener('submit', event => {
        event.preventDefault();
        const row = rows.find(item => item.id === notesId);
        const text = noteInput.value.trim();
        if (!row || !text) { noteInput.focus(); return; }
        row.notes.push({ id: 'note-' + (++sequence), text, time: new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date()) });
        noteInput.value = ''; renderNotes(); render(); noteInput.focus();
    });

    const csvCell = value => {
        let text = String(value ?? '');
        if (/^[\s]*[=+@-]/.test(text) || /^[\t\r\n]/.test(text)) text = "'" + text;
        return '"' + text.replaceAll('"', '""') + '"';
    };
    root.querySelector('[data-export]').addEventListener('click', () => {
        const filtered = getFilteredRows();
        const headers = ['No', 'Tahun', 'Plant', 'Unit Kerja', 'MRP', 'No Material', 'Short Text Material', 'Quantity', 'UOM', 'Harga Satuan', 'Harga Total', 'Status BOM', 'Drawing', 'RIF', 'Status Ajuan', 'Catatan'];
        const lines = [headers, ...filtered.map((row, index) => [index + 1, row.year, row.plant, row.unit, row.mrp, row.material_no, row.short_text, row.quantity, row.uom, money.format(row.price), money.format(total(row)), row.bom_status, row.has_drawing ? 'Ada' : 'Tidak ada', row.rif, row.cancelled ? 'Cancel' : 'Lanjut', row.notes.map(note => note.text).join(' | ')])];
        const blob = new Blob(['\uFEFF' + lines.map(line => line.map(csvCell).join(';')).join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = createElement('a'); link.href = url; link.download = 'rencana-kerja-barang-' + mode + '.csv'; document.body.append(link); link.click(); link.remove();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
        announce(filtered.length + ' material diekspor ke CSV.');
    });

    render();
});
</script>
@endpush
