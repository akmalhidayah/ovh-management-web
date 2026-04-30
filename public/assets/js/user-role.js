document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-template-selector]').forEach((selector) => {
        const catalogNode = selector.querySelector('[data-template-catalog]');
        const categoryInput = selector.querySelector('[data-template-category]');
        const tabs = selector.querySelectorAll('[data-template-tab]');
        const searchInput = selector.querySelector('[data-template-search]');
        const optionContainer = selector.querySelector('[data-template-options]');
        const select = selector.querySelector('[data-template-select]');
        const toggleButton = selector.querySelector('[data-template-toggle]');
        const dropdown = selector.querySelector('[data-template-dropdown]');
        const currentLabel = selector.querySelector('[data-template-current]');
        const showFormButton = selector.querySelector('[data-show-form]');
        const formWorkspace = document.querySelector('[data-form-workspace]');
        const headingTargets = document.querySelectorAll('[data-selected-template-label]');
        const categoryTargets = document.querySelectorAll('[data-selected-category-label]');
        const inputTargets = document.querySelectorAll('[data-selected-template-input]');

        if (!catalogNode || !categoryInput || !select) {
            return;
        }

        const catalog = JSON.parse(catalogNode.textContent || '{}');
        let activeCategory = categoryInput.value;
        let activeSearch = '';
        let selectedTemplate = select.value || '';

        const closeDropdown = () => {
            if (!dropdown || !toggleButton) {
                return;
            }

            dropdown.hidden = true;
            toggleButton.setAttribute('aria-expanded', 'false');
        };

        const openDropdown = () => {
            if (!dropdown || !toggleButton) {
                return;
            }

            dropdown.hidden = false;
            toggleButton.setAttribute('aria-expanded', 'true');

            if (searchInput) {
                searchInput.value = '';
                activeSearch = '';
                renderOptions();
                searchInput.focus();
            }
        };

        const updateTemplateLabels = () => {
            if (currentLabel) {
                currentLabel.textContent = selectedTemplate;
            }

            headingTargets.forEach((target) => {
                target.textContent = selectedTemplate;
            });

            categoryTargets.forEach((target) => {
                target.textContent = activeCategory;
            });

            inputTargets.forEach((target) => {
                target.value = selectedTemplate;
            });
        };

        const chooseTemplate = (template) => {
            selectedTemplate = template || '';
            select.value = selectedTemplate;

            if (searchInput) {
                searchInput.value = '';
            }

            activeSearch = '';
            renderOptions();
            updateTemplateLabels();
            closeDropdown();
        };

        const renderOptions = () => {
            const options = (catalog[activeCategory] || []).filter((option) => option.toLowerCase().includes(activeSearch.toLowerCase()));
            const fallbackOptions = catalog[activeCategory] || [];
            const data = activeSearch ? options : fallbackOptions;

            select.innerHTML = '';
            fallbackOptions.forEach((option) => {
                const item = document.createElement('option');
                item.value = option;
                item.textContent = option;
                select.appendChild(item);
            });

            if (!fallbackOptions.includes(selectedTemplate)) {
                selectedTemplate = data[0] || '';
            }

            select.value = selectedTemplate;

            if (optionContainer) {
                optionContainer.innerHTML = '';

                if (data.length === 0) {
                    const emptyState = document.createElement('div');
                    emptyState.className = 'user-template-empty';
                    emptyState.textContent = 'Template tidak ditemukan.';
                    optionContainer.appendChild(emptyState);
                }

                data.forEach((option) => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.value = option;
                    item.dataset.templateOption = '';
                    item.className = `user-template-option${option === selectedTemplate ? ' active' : ''}`;
                    item.textContent = option;
                    item.addEventListener('click', () => chooseTemplate(option));
                    optionContainer.appendChild(item);
                });
            }

            updateTemplateLabels();
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activeCategory = tab.dataset.category || activeCategory;
                categoryInput.value = activeCategory;
                activeSearch = '';

                tabs.forEach((button) => button.classList.toggle('active', button === tab));
                selectedTemplate = (catalog[activeCategory] || [])[0] || '';
                if (searchInput) {
                    searchInput.value = selectedTemplate;
                }
                renderOptions();
            });
        });

        if (toggleButton && dropdown) {
            toggleButton.addEventListener('click', () => {
                if (dropdown.hidden) {
                    openDropdown();
                } else {
                    closeDropdown();
                }
            });

            document.addEventListener('click', (event) => {
                if (!selector.contains(event.target)) {
                    closeDropdown();
                }
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', (event) => {
                activeSearch = event.target.value || '';
                renderOptions();
            });

            searchInput.addEventListener('focus', () => {
                renderOptions();
            });
        }

        if (showFormButton && formWorkspace) {
            showFormButton.addEventListener('click', () => {
                formWorkspace.classList.remove('d-none');
                formWorkspace.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }

        select.addEventListener('change', (event) => chooseTemplate(event.target.value));
        renderOptions();
    });

    document.querySelectorAll('[data-qc-record-form]').forEach((form) => {
        const tableBody = form.querySelector('[data-qc-table-body]');
        const mobileList = form.querySelector('[data-qc-mobile-list]');
        const addButton = form.querySelector('[data-qc-add-row]');
        let rowIndex = form.querySelectorAll('[data-qc-item-row]').length;

        const escapeAttribute = (value) => String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        const getLastCategory = () => {
            const lastDesktopRow = tableBody ? tableBody.querySelector('[data-qc-item-row]:last-child') : null;
            const lastDesktopCategory = lastDesktopRow ? lastDesktopRow.querySelector('td:first-child input') : null;

            if (lastDesktopCategory && lastDesktopCategory.value) {
                return lastDesktopCategory.value;
            }

            const lastMobileRow = mobileList ? mobileList.querySelector('[data-qc-mobile-row]:last-child') : null;
            const lastMobileCategory = lastMobileRow ? lastMobileRow.querySelector('input') : null;

            return lastMobileCategory ? lastMobileCategory.value : '';
        };

        const removeRow = (rowId) => {
            form.querySelectorAll(`[data-row-id="${rowId}"]`).forEach((row) => row.remove());
        };

        const createDesktopRow = (rowId, index, category = '') => {
            const row = document.createElement('tr');
            row.dataset.qcItemRow = '';
            row.dataset.rowId = rowId;
            row.innerHTML = `
                <td><input type="text" class="form-control" value="${escapeAttribute(category)}" placeholder="Kategori"></td>
                <td><input type="text" class="form-control" placeholder="Item pengecekan"></td>
                <td><input type="text" class="form-control" placeholder="Standar"></td>
                <td>
                    <div class="qc-status-toggle">
                        <label><input type="checkbox" name="qc_status_${index}_ok" value="OK"><span>OK</span></label>
                        <label><input type="checkbox" name="qc_status_${index}_not_ok" value="Not OK"><span>Not OK</span></label>
                    </div>
                </td>
                <td><textarea class="form-control" rows="1" placeholder="Catatan..."></textarea></td>
                <td class="text-center">
                    <button type="button" class="btn btn-light inspector-icon-action" title="Hapus item" data-qc-remove-row>
                        <i class="bi bi-trash3"></i>
                    </button>
                </td>
            `;

            return row;
        };

        const createMobileRow = (rowId, index, category = '') => {
            const row = document.createElement('article');
            row.className = 'qc-mobile-item';
            row.dataset.qcMobileRow = '';
            row.dataset.rowId = rowId;
            row.innerHTML = `
                <div class="d-flex justify-content-between gap-3">
                    <label class="flex-grow-1">
                        <span>Kategori</span>
                        <input type="text" class="form-control" value="${escapeAttribute(category)}" placeholder="Kategori">
                    </label>
                    <button type="button" class="btn btn-light inspector-icon-action mt-4" title="Hapus item" data-qc-remove-row>
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
                <label>
                    <span>Item Pengecekan</span>
                    <input type="text" class="form-control" placeholder="Item pengecekan">
                </label>
                <label>
                    <span>Standar</span>
                    <input type="text" class="form-control" placeholder="Standar">
                </label>
                <div>
                    <span class="qc-mobile-label">Status</span>
                    <div class="qc-status-toggle">
                        <label><input type="checkbox" name="qc_mobile_status_${index}_ok" value="OK"><span>OK</span></label>
                        <label><input type="checkbox" name="qc_mobile_status_${index}_not_ok" value="Not OK"><span>Not OK</span></label>
                    </div>
                </div>
                <label>
                    <span>Catatan</span>
                    <textarea class="form-control" rows="2" placeholder="Catatan..."></textarea>
                </label>
            `;

            return row;
        };

        if (addButton) {
            addButton.addEventListener('click', () => {
                rowIndex += 1;
                const rowId = `qc-row-${Date.now()}-${rowIndex}`;
                const category = getLastCategory();

                if (tableBody) {
                    tableBody.appendChild(createDesktopRow(rowId, rowIndex, category));
                }

                if (mobileList) {
                    mobileList.appendChild(createMobileRow(rowId, rowIndex, category));
                }
            });
        }

        form.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-qc-remove-row]');

            if (!removeButton) {
                return;
            }

            const row = removeButton.closest('[data-row-id]');
            if (row) {
                removeRow(row.dataset.rowId);
            }
        });
    });

    document.querySelectorAll('[data-signature-pad]').forEach((pad) => {
        const canvas = pad.querySelector('[data-signature-canvas]');
        const clearButton = pad.querySelector('[data-signature-clear]');

        if (!canvas) {
            return;
        }

        const context = canvas.getContext('2d');
        let isDrawing = false;

        context.lineWidth = 2;
        context.lineCap = 'round';
        context.lineJoin = 'round';
        context.strokeStyle = '#102033';

        const getPoint = (event) => {
            const rect = canvas.getBoundingClientRect();
            const point = event.touches ? event.touches[0] : event;

            return {
                x: (point.clientX - rect.left) * (canvas.width / rect.width),
                y: (point.clientY - rect.top) * (canvas.height / rect.height),
            };
        };

        const startDrawing = (event) => {
            event.preventDefault();
            isDrawing = true;
            const point = getPoint(event);
            context.beginPath();
            context.moveTo(point.x, point.y);
        };

        const draw = (event) => {
            if (!isDrawing) {
                return;
            }

            event.preventDefault();
            const point = getPoint(event);
            context.lineTo(point.x, point.y);
            context.stroke();
        };

        const stopDrawing = () => {
            isDrawing = false;
            context.closePath();
        };

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);
        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDrawing);

        if (clearButton) {
            clearButton.addEventListener('click', () => {
                context.clearRect(0, 0, canvas.width, canvas.height);
            });
        }
    });

    document.querySelectorAll('[data-commissioning-check-table]').forEach((table) => {
        const body = table.querySelector('[data-commissioning-check-body]');
        const addButton = document.querySelector('[data-commissioning-add-check]');

        if (!body || !addButton) {
            return;
        }

        const renumberRows = () => {
            body.querySelectorAll('tr').forEach((row, index) => {
                const numberCell = row.querySelector('td:first-child');

                if (numberCell) {
                    numberCell.textContent = String(index + 1);
                }
            });
        };

        addButton.addEventListener('click', () => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="text-center"></td>
                <td><input type="text" class="form-control" placeholder="Item to check"></td>
                <td class="text-center"><input type="checkbox"></td>
                <td class="text-center"><input type="checkbox"></td>
                <td class="text-center"><input type="checkbox"></td>
                <td class="text-center"><input type="checkbox"></td>
                <td><input type="text" class="form-control" placeholder="Remarks..."></td>
            `;
            body.appendChild(row);
            renumberRows();
        });
    });

    document.querySelectorAll('[data-commissioning-test-table]').forEach((table) => {
        const body = table.querySelector('[data-commissioning-test-body]');
        const addButton = document.querySelector('[data-commissioning-add-test]');

        if (!body || !addButton) {
            return;
        }

        const updateRemarksSpan = () => {
            const remarksCell = body.querySelector('.commissioning-merged-remarks');

            if (remarksCell) {
                remarksCell.rowSpan = body.querySelectorAll('tr').length;
            }
        };

        addButton.addEventListener('click', () => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" class="form-control"></td>
                <td><input type="text" class="form-control"></td>
                <td><input type="text" class="form-control"></td>
                <td><input type="text" class="form-control"></td>
                <td><input type="text" class="form-control"></td>
            `;
            body.appendChild(row);
            updateRemarksSpan();
        });
    });
});
