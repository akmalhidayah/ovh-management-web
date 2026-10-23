@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-barang-work-list]');
    if (!root) return;

    const initialRows = {{ Illuminate\Support\Js::from($workListRows) }};
    const rows = initialRows.map(row => ({ ...row, notes: [...row.notes] }));
    const mode = root.dataset.mode;
    const year = root.querySelector('[data-year]');
    const plant = root.querySelector('[data-plant]');
    const search = root.querySelector('[data-search]');
    const pageSize = root.querySelector('[data-page-size]');
    const tbody = root.querySelector('[data-rows]');
    const pagination = root.querySelector('[data-pagination]');
    const feedback = root.querySelector('[data-feedback]');
    const editElement = root.querySelector('#barang-work-edit');
    const notesElement = root.querySelector('#barang-work-notes');
    const form = root.querySelector('#barang-work-form');
    const noteForm = root.querySelector('#barang-note-form');
    const noteInput = root.querySelector('#barang-note-input');
    const money = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const number = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });
    const statusColors = { Lanjut: 'lanjut', PGO: 'pgo', TS: 'ts', BMS: 'bms', Cancel: 'cancel' };
    const processColors = { Kontrak: 'kontrak', Fabrikasi: 'fabrikasi', Tender: 'tender', Cancel: 'cancel', 'PO/Done': 'done', 'BMS Review': 'review' };
    const fields = ['year', 'plant', 'unit', 'notification', 'area', 'location', 'equipment', 'description', 'duration', 'quantity', 'uom', 'price', 'status', 'process'];
    let page = 1;
    let expanded = false;
    let editingId = null;
    let notesId = null;
    let sequence = 0;

    function element(tag, text, className) {
        const node = document.createElement(tag);
        if (text !== undefined) node.textContent = text;
        if (className) node.className = className;
        return node;
    }

    function announce(message) {
        feedback.textContent = message;
        feedback.hidden = false;
    }

    function inCurrentTab(row) {
        if (mode === 'lanjut') return row.status === 'Lanjut';
        if (mode === 'internal') return ['PGO', 'TS', 'BMS'].includes(row.status);
        if (mode === 'batal') return row.status === 'Cancel';
        return true;
    }

    function filteredRows() {
        const term = search.value.trim().toLocaleLowerCase('id-ID');
        return rows.filter(row => inCurrentTab(row)
            && (!year.value || String(row.year) === year.value)
            && (!plant.value || row.plant === plant.value)
            && (!term || fields.map(key => String(row[key] ?? '')).join(' ').toLocaleLowerCase('id-ID').includes(term)));
    }

    function total(row) {
        return row.price * row.quantity;
    }

    function badge(text, palette) {
        return element('span', text, 'bwl-pill is-' + (palette[text] || 'review'));
    }

    function actionButton(action, row, icon, label, extraClass = '') {
        const button = element('button', undefined, 'bwl-icon ' + extraClass);
        button.type = 'button';
        button.dataset.action = action;
        button.dataset.rowId = row.id;
        button.title = label;
        button.setAttribute('aria-label', label + ': ' + row.description);
        const glyph = element('i', undefined, 'bi ' + icon);
        glyph.setAttribute('aria-hidden', 'true');
        button.append(glyph);
        return button;
    }

    function renderRow(row, index) {
        const tr = element('tr');
        if (row.status === 'Cancel') tr.classList.add('bwl-cancelled');
        function cell(value, className = '', detail = false) {
            const td = element('td', value, className);
            if (detail) td.hidden = !expanded;
            tr.append(td);
            return td;
        }
        cell(index + 1);
        cell(row.year, 'bwl-center', true);
        cell(row.unit, 'bwl-unit');
        cell(row.plant, 'bwl-plant');
        cell(row.area || '—', 'bwl-meta', true);
        cell(row.location || '—', 'bwl-meta', true);
        cell(row.equipment || '—', 'bwl-meta', true);
        cell(row.notification || '—', 'bwl-notification');
        cell(row.description, 'bwl-description');
        cell(number.format(row.duration), 'bwl-center');
        cell('HARI', 'bwl-center');
        cell(number.format(row.quantity), 'bwl-center');
        cell(row.uom, 'bwl-center');
        cell(money.format(row.price), 'bwl-number');
        cell(money.format(total(row)), 'bwl-number bwl-total');
        cell(undefined, 'bwl-center').append(badge(row.status, statusColors));
        if (mode === 'semua') cell(undefined, 'bwl-center').append(badge(row.process, processColors));

        const noteButton = actionButton('notes', row, 'bi-chat-left-text', row.notes.length + ' catatan');
        if (row.notes.length) {
            const count = element('span', row.notes.length > 99 ? '99+' : row.notes.length, 'bwl-note-count');
            count.setAttribute('aria-hidden', 'true');
            noteButton.append(count);
        }
        cell(undefined, 'bwl-center').append(noteButton);
        cell(undefined, 'bwl-center').append(actionButton('edit', row, 'bi-pencil-square', 'Edit ajuan', 'bwl-icon-edit'));
        return tr;
    }

    function pageButton(label, target, disabled, current = false) {
        const button = element('button', label, 'bwl-page');
        button.type = 'button';
        button.disabled = disabled;
        button.setAttribute('aria-label', label === '‹' ? 'Halaman sebelumnya' : label === '›' ? 'Halaman berikutnya' : 'Halaman ' + label);
        if (current) button.setAttribute('aria-current', 'page');
        button.addEventListener('click', () => {
            page = target;
            render();
            pagination.querySelector('[aria-current="page"]')?.focus({ preventScroll: true });
            root.querySelector('.bwl-table-scroll').scrollTop = 0;
        });
        return button;
    }

    function render() {
        const filtered = filteredRows();
        const size = Number(pageSize.value);
        const pages = Math.max(1, Math.ceil(filtered.length / size));
        page = Math.min(Math.max(1, page), pages);
        const start = (page - 1) * size;
        const fragment = document.createDocumentFragment();
        filtered.slice(start, start + size).forEach((row, offset) => fragment.append(renderRow(row, start + offset)));
        tbody.replaceChildren(fragment);
        root.querySelector('[data-empty]').hidden = filtered.length > 0;
        root.querySelector('[data-summary]').textContent = filtered.length
            ? 'Menampilkan ' + (start + 1) + '–' + Math.min(start + size, filtered.length) + ' dari ' + filtered.length + ' ajuan'
            : 'Menampilkan 0 ajuan';
        pagination.replaceChildren(pageButton('‹', page - 1, page === 1));
        for (let current = 1; current <= pages; current += 1) {
            pagination.append(pageButton(String(current), current, false, current === page));
        }
        pagination.append(pageButton('›', page + 1, page === pages));
        root.querySelector('[data-export]').disabled = filtered.length === 0;
    }

    [year, plant, pageSize].forEach(control => control.addEventListener('change', () => { page = 1; render(); }));
    search.addEventListener('input', () => { page = 1; render(); });
    root.querySelector('[data-reset]').addEventListener('click', () => {
        year.value = '';
        plant.value = '';
        search.value = '';
        page = 1;
        render();
        search.focus();
    });

    function setExpanded(value) {
        expanded = value;
        root.querySelectorAll('[data-detail-col]').forEach(cell => { cell.hidden = !value; });
        const toggle = root.querySelector('[data-toggle-detail]');
        if (toggle) {
            toggle.setAttribute('aria-pressed', String(value));
            toggle.querySelector('span').textContent = value ? 'Kolom Ringkas' : 'Detail Kolom';
        }
        root.querySelector('[data-close-detail]').hidden = !value;
        render();
    }
    root.querySelector('[data-toggle-detail]')?.addEventListener('click', () => setExpanded(!expanded));
    root.querySelector('[data-close-detail]').addEventListener('click', () => {
        setExpanded(false);
        root.querySelector('[data-toggle-detail]')?.focus();
    });

    function showModal(modalElement) {
        if (!window.bootstrap?.Modal) {
            announce('Modal belum dapat dimuat. Muat ulang halaman dan coba lagi.');
            return;
        }
        const trigger = document.activeElement;
        modalElement.addEventListener('hidden.bs.modal', () => {
            const target = trigger?.isConnected ? trigger : root.querySelector('[data-add]');
            target?.focus({ preventScroll: true });
        }, { once: true });
        window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }

    function updateTotal() {
        const cancelled = form.elements.status.value === 'Cancel';
        form.elements.price.readOnly = cancelled;
        if (cancelled) {
            form.elements.price.value = 0;
            form.elements.process.value = 'Cancel';
        }
        const value = Number(form.elements.price.value) * Number(form.elements.quantity.value);
        form.querySelector('[data-total]').textContent = 'Rp ' + money.format(Number.isFinite(value) ? value : 0);
    }

    function openEditor(row) {
        editingId = row?.id ?? null;
        form.reset();
        const values = row || {
            year: Number(year.value || 2027), plant: plant.value || 'Tonasa 5', unit: '', notification: '',
            area: '', location: '', equipment: '', description: 'OH27 - ', duration: 1, quantity: 1,
            uom: 'LOT', price: 0, status: mode === 'batal' ? 'Cancel' : mode === 'internal' ? 'PGO' : 'Lanjut',
            process: mode === 'batal' ? 'Cancel' : 'Tender',
        };
        fields.forEach(key => { form.elements[key].value = values[key]; });
        root.querySelector('#barang-work-edit-title').textContent = row ? 'Edit Ajuan' : 'Tambah Ajuan';
        updateTotal();
        showModal(editElement);
    }
    form.addEventListener('input', updateTotal);
    form.addEventListener('change', updateTotal);
    editElement.addEventListener('shown.bs.modal', () => form.elements.unit.focus());
    root.querySelector('[data-add]').addEventListener('click', () => openEditor(null));

    form.addEventListener('submit', event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const values = Object.fromEntries(fields.map(key => [key, form.elements[key].value.trim()]));
        if (!values.unit || !values.description) {
            announce('Unit kerja dan deskripsi pekerjaan wajib diisi.');
            return;
        }
        ['year', 'duration', 'quantity', 'price'].forEach(key => { values[key] = Number(values[key]); });
        if (!Number.isSafeInteger(Math.round(values.quantity * values.price * 100))) {
            announce('Total harga terlalu besar. Periksa quantity dan harga satuan.');
            return;
        }
        if (values.status === 'Cancel') { values.price = 0; values.process = 'Cancel'; }
        const existing = rows.find(row => row.id === editingId);
        if (existing) Object.assign(existing, values);
        else rows.unshift({ ...values, id: 'barang-local-' + (++sequence), notes: [] });
        page = 1;
        render();
        window.bootstrap.Modal.getInstance(editElement)?.hide();
        announce(inCurrentTab(values)
            ? 'Ajuan diperbarui pada pratinjau. Perubahan belum tersimpan ke server.'
            : 'Status diperbarui. Ajuan tidak lagi masuk kategori tab ini; perubahan hanya berlaku pada pratinjau halaman ini.');
    });

    function renderNotes() {
        const row = rows.find(item => item.id === notesId);
        if (!row) return;
        root.querySelector('[data-notes-description]').textContent = row.description;
        const list = root.querySelector('[data-notes-list]');
        list.replaceChildren();
        if (!row.notes.length) {
            list.append(element('p', 'Belum ada catatan untuk ajuan ini.', 'bwl-notes-empty'));
            return;
        }
        row.notes.forEach(note => {
            const card = element('article', undefined, 'bwl-note');
            card.append(element('p', note.text));
            const footer = element('div', undefined, 'bwl-note-footer');
            footer.append(element('span', note.time));
            const remove = element('button');
            remove.type = 'button';
            remove.title = 'Hapus catatan';
            remove.setAttribute('aria-label', 'Hapus catatan');
            const icon = element('i', undefined, 'bi bi-trash3');
            icon.setAttribute('aria-hidden', 'true');
            remove.append(icon);
            remove.addEventListener('click', () => {
                if (!window.confirm('Hapus catatan ini dari pratinjau?')) return;
                row.notes = row.notes.filter(item => item.id !== note.id);
                renderNotes();
                render();
                noteInput.focus();
            });
            footer.append(remove);
            card.append(footer);
            list.append(card);
        });
    }

    tbody.addEventListener('click', event => {
        const button = event.target.closest('button[data-action]');
        if (!button) return;
        const row = rows.find(item => item.id === button.dataset.rowId);
        if (!row) return;
        if (button.dataset.action === 'edit') openEditor(row);
        else {
            notesId = row.id;
            noteInput.value = '';
            renderNotes();
            showModal(notesElement);
        }
    });

    noteForm.addEventListener('submit', event => {
        event.preventDefault();
        const row = rows.find(item => item.id === notesId);
        const text = noteInput.value.trim();
        if (!row || !text) { noteInput.focus(); return; }
        row.notes.push({
            id: 'note-' + (++sequence),
            text,
            time: new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date()),
        });
        noteInput.value = '';
        renderNotes();
        render();
        noteInput.focus();
    });

    function csvCell(value) {
        let text = String(value ?? '');
        if (/^[\s]*[=+@-]/.test(text) || /^[\t\r\n]/.test(text)) text = "'" + text;
        return '"' + text.replaceAll('"', '""') + '"';
    }

    root.querySelector('[data-export]').addEventListener('click', () => {
        const filtered = filteredRows();
        const headers = ['No', 'Tahun', 'Unit Kerja', 'Plant', 'Area', 'Functional Location', 'ID Equipment', 'Notifikasi', 'Deskripsi Pekerjaan', 'Durasi (hari)', 'Quantity', 'UOM', 'Harga Satuan', 'Total Harga', 'Status Ajuan', 'Proses Pengadaan', 'Catatan'];
        const lines = [headers, ...filtered.map((row, index) => [
            index + 1, row.year, row.unit, row.plant, row.area, row.location, row.equipment, row.notification,
            row.description, row.duration, row.quantity, row.uom, money.format(row.price), money.format(total(row)),
            row.status, row.process, row.notes.map(note => note.text).join(' | '),
        ])];
        const blob = new Blob(['\uFEFF' + lines.map(line => line.map(csvCell).join(';')).join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = element('a');
        link.href = url;
        link.download = 'rencana-kerja-barang-' + mode + '.csv';
        document.body.append(link);
        link.click();
        link.remove();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
        announce(filtered.length + ' ajuan diekspor ke CSV. File dapat dibuka di Excel.');
    });

    render();
});
</script>
@endpush
