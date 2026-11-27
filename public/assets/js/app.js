document.addEventListener('DOMContentLoaded', function () {
    const currency = 'GH₵';
    const prefersReducedMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finePointer = window.matchMedia
        && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    function fmt(n) {
        return currency + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    const themeToggle = document.querySelector('[data-theme-toggle]');
    const themeIcon   = document.querySelector('[data-theme-icon]');
    const sunIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>';
    const moonIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/></svg>';

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        const tip = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        if (themeIcon)  themeIcon.innerHTML = theme === 'dark' ? sunIcon : moonIcon;
        if (themeToggle) {
            themeToggle.setAttribute('title', tip);
            themeToggle.setAttribute('aria-label', tip);
        }
    }

    const savedTheme = localStorage.getItem('ahp_theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(savedTheme || (prefersDark ? 'dark' : 'light'));

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('ahp_theme', next);
            applyTheme(next);
        });
    }

    function setupAuthPointerMotion() {
        if (prefersReducedMotion || !finePointer) return;

        const ambient = document.querySelector('.auth-ambient');
        const card = document.querySelector('.auth-card');
        if (!ambient || !card) return;

        let ambientFrame = 0;
        let ambientX = 0;
        let ambientY = 0;

        window.addEventListener('pointermove', function (event) {
            const rect = ambient.getBoundingClientRect();
            ambientX = event.clientX - rect.left;
            ambientY = event.clientY - rect.top;
            if (ambientFrame) return;
            ambientFrame = window.requestAnimationFrame(function () {
                ambient.style.setProperty('--auth-pointer-x', ambientX + 'px');
                ambient.style.setProperty('--auth-pointer-y', ambientY + 'px');
                ambient.classList.add('has-auth-pointer');
                ambientFrame = 0;
            });
        }, { passive: true });

        document.addEventListener('pointerleave', function () {
            ambient.classList.remove('has-auth-pointer');
        });

        let cardFrame = 0;
        let cardX = 0;
        let cardY = 0;

        card.addEventListener('pointermove', function (event) {
            const rect = card.getBoundingClientRect();
            cardX = event.clientX - rect.left;
            cardY = event.clientY - rect.top;
            if (cardFrame) return;
            cardFrame = window.requestAnimationFrame(function () {
                card.style.setProperty('--auth-spot-x', cardX + 'px');
                card.style.setProperty('--auth-spot-y', cardY + 'px');
                cardFrame = 0;
            });
        }, { passive: true });

        card.addEventListener('pointerleave', function () {
            card.style.removeProperty('--auth-spot-x');
            card.style.removeProperty('--auth-spot-y');
        });
    }

    function setupAuthSubmitLoading() {
        document.querySelectorAll('.auth-card form[data-auth-form]').forEach(function (authForm) {
            authForm.addEventListener('submit', function () {
                const submit = authForm.querySelector('button[type="submit"]');
                if (!submit || submit.disabled) return;

                const label = submit.querySelector('[data-submit-label]');
                if (label) {
                    if (!submit.dataset.originalLabel) submit.dataset.originalLabel = label.textContent;
                    label.textContent = submit.dataset.loadingLabel || 'Please wait…';
                }

                submit.classList.add('is-loading');
                submit.disabled = true;
                authForm.setAttribute('aria-busy', 'true');
            });
        });
    }

    setupAuthPointerMotion();
    setupAuthSubmitLoading();

    function setupPasswordToggle(passwordInput) {
        if (!passwordInput || passwordInput.dataset.passwordToggle === 'true') return;
        if (passwordInput.closest('.password-field')) return;

        passwordInput.dataset.passwordToggle = 'true';
        const wrapper = document.createElement('div');
        wrapper.className = 'password-field';
        passwordInput.parentNode.insertBefore(wrapper, passwordInput);
        wrapper.appendChild(passwordInput);

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'password-toggle';
        toggle.setAttribute('aria-label', 'Show password');
        toggle.setAttribute('title', 'Show password');
        toggle.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';

        toggle.addEventListener('click', function () {
            const showPassword = passwordInput.type === 'password';
            passwordInput.type = showPassword ? 'text' : 'password';
            toggle.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
            toggle.setAttribute('title', showPassword ? 'Hide password' : 'Show password');
            toggle.classList.toggle('is-visible', showPassword);
        });

        wrapper.appendChild(toggle);
    }

    document.querySelectorAll('input[type="password"]').forEach(setupPasswordToggle);

    const signupToggle = document.querySelector('[data-signup-toggle]');
    const signupForm = document.querySelector('[data-signup-form]');
    if (signupToggle && signupForm) {
        let signupOpen = !signupForm.hidden;
        let signupAnimation = null;

        function setSignupOpen(open, focusFirst) {
            if (signupAnimation) {
                signupAnimation.cancel();
                signupAnimation = null;
            }

            signupOpen = open;
            signupToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            signupToggle.textContent = open ? 'Hide form' : 'Create account';

            const firstInput = signupForm.querySelector('input:not([type="hidden"])');
            if (open && focusFirst && firstInput) firstInput.focus();

            const canAnimate = !prefersReducedMotion && typeof signupForm.animate === 'function';
            if (!canAnimate) {
                signupForm.classList.remove('is-toggling');
                signupForm.hidden = !open;
                return;
            }

            signupForm.classList.add('is-toggling');
            if (open) {
                signupForm.hidden = false;
                signupAnimation = signupForm.animate([
                    { opacity: 0, transform: 'translate3d(0, -10px, 0) scale(.985)' },
                    { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' }
                ], { duration: 320, easing: 'cubic-bezier(.16, 1, .3, 1)' });
            } else {
                signupAnimation = signupForm.animate([
                    { opacity: 1, transform: 'translate3d(0, 0, 0) scale(1)' },
                    { opacity: 0, transform: 'translate3d(0, -8px, 0) scale(.985)' }
                ], { duration: 220, easing: 'cubic-bezier(.4, 0, 1, 1)' });
            }

            signupAnimation.onfinish = function () {
                signupForm.classList.remove('is-toggling');
                if (!open) signupForm.hidden = true;
                signupAnimation = null;
            };
        }

        signupToggle.addEventListener('click', function () {
            setSignupOpen(!signupOpen, true);
        });

        if (new URLSearchParams(window.location.search).get('signup') === '1') {
            setSignupOpen(true, false);
        }
    }

    const passwordToggleObserver = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (!(node instanceof Element)) return;
                if (node.matches && node.matches('input[type="password"]')) setupPasswordToggle(node);
                node.querySelectorAll && node.querySelectorAll('input[type="password"]').forEach(setupPasswordToggle);
            });
        });
    });
    passwordToggleObserver.observe(document.body, { childList: true, subtree: true });

    let searchableSelectCounter = 0;

    function setupSearchableSelect(select) {
        if (!select || select.dataset.searchableSelect === 'true') return;
        select.dataset.searchableSelect = 'true';

        const wrapper = document.createElement('div');
        wrapper.className = 'searchable-select';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        select.classList.add('searchable-select-native');

        const toggleId = 'searchable-select-toggle-' + (++searchableSelectCounter);
        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.id = toggleId;
        toggle.className = 'searchable-select-toggle';
        toggle.setAttribute('role', 'combobox');
        toggle.setAttribute('aria-haspopup', 'listbox');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', select.getAttribute('aria-label') || 'Search and select');

        const panel = document.createElement('div');
        panel.className = 'searchable-select-panel';
        panel.hidden = true;

        const search = document.createElement('input');
        search.type = 'search';
        search.className = 'searchable-select-search';
        search.setAttribute('autocomplete', 'off');
        search.setAttribute('aria-label', 'Search choices');
        search.placeholder = 'Search options';

        const list = document.createElement('div');
        list.className = 'searchable-select-list';
        list.setAttribute('role', 'listbox');

        function syncToggle() {
            const selected = select.options[select.selectedIndex];
            toggle.textContent = selected
                ? selected.textContent.replace(/\s+/g, ' ').trim()
                : 'Select an option';
        }

        function closePanel(returnFocus) {
            panel.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            if (returnFocus) toggle.focus();
        }

        function openPanel() {
            document.querySelectorAll('.searchable-select-panel:not([hidden])').forEach(function (openPanel) {
                if (openPanel !== panel) openPanel.hidden = true;
                const openToggle = openPanel.parentElement.querySelector('.searchable-select-toggle');
                if (openToggle) openToggle.setAttribute('aria-expanded', 'false');
            });
            panel.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
            search.value = '';
            renderOptions('');
            window.requestAnimationFrame(function () { search.focus(); });
        }

        function chooseOption(option) {
            select.value = option.value;
            syncToggle();
            closePanel(true);
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function renderOptions(query) {
            list.replaceChildren();
            const normalizedQuery = query.trim().toLocaleLowerCase();
            let matchCount = 0;

            Array.from(select.options).forEach(function (option) {
                const label = option.textContent.replace(/\s+/g, ' ').trim();
                if (normalizedQuery && !label.toLocaleLowerCase().includes(normalizedQuery)) return;
                matchCount++;

                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'searchable-select-option';
                item.setAttribute('role', 'option');
                item.textContent = label;
                item.dataset.value = option.value;
                item.setAttribute('aria-selected', option.selected ? 'true' : 'false');
                item.addEventListener('click', function () { chooseOption(option); });
                list.appendChild(item);
            });

            if (matchCount === 0) {
                const empty = document.createElement('div');
                empty.className = 'searchable-select-empty';
                empty.textContent = 'No matching options';
                list.appendChild(empty);
            }
        }

        toggle.addEventListener('click', function () {
            if (panel.hidden) openPanel();
            else closePanel(false);
        });
        toggle.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openPanel();
            }
        });
        search.addEventListener('input', function () { renderOptions(search.value); });
        search.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                closePanel(true);
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                const first = list.querySelector('.searchable-select-option');
                if (first) first.focus();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                const items = list.querySelectorAll('.searchable-select-option');
                if (items.length) items[items.length - 1].focus();
            } else if (event.key === 'Enter') {
                event.preventDefault();
                const first = list.querySelector('.searchable-select-option');
                if (first) first.click();
            }
        });

        panel.addEventListener('keydown', function (event) {
            if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') return;
            event.preventDefault();
            const items = Array.from(list.querySelectorAll('.searchable-select-option'));
            if (items.length === 0) return;
            const currentIndex = items.indexOf(document.activeElement);
            const nextIndex = event.key === 'ArrowDown'
                ? (currentIndex + 1) % items.length
                : (currentIndex <= 0 ? items.length - 1 : currentIndex - 1);
            items[nextIndex].focus();
        });

        select.addEventListener('change', syncToggle);
        wrapper.appendChild(toggle);
        panel.appendChild(search);
        panel.appendChild(list);
        wrapper.appendChild(panel);

        if (select.id) {
            const label = document.querySelector('label[for="' + CSS.escape(select.id) + '"]');
            if (label) label.setAttribute('for', toggleId);
        }

        syncToggle();
        renderOptions('');

        const optionsObserver = new MutationObserver(function () {
            syncToggle();
            if (!panel.hidden) renderOptions(search.value);
        });
        optionsObserver.observe(select, { childList: true });
    }

    document.querySelectorAll('select').forEach(setupSearchableSelect);

    const searchableSelectObserver = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (!(node instanceof Element)) return;
                if (node.matches && node.matches('select')) setupSearchableSelect(node);
                node.querySelectorAll && node.querySelectorAll('select').forEach(setupSearchableSelect);
            });
        });
    });
    searchableSelectObserver.observe(document.body, { childList: true, subtree: true });

    document.addEventListener('click', function (event) {
        if (event.target.closest('.searchable-select')) return;
        document.querySelectorAll('.searchable-select-panel:not([hidden])').forEach(function (panel) {
            panel.hidden = true;
            const toggle = panel.parentElement.querySelector('.searchable-select-toggle');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        const openPanel = document.querySelector('.searchable-select-panel:not([hidden])');
        if (!openPanel) return;
        openPanel.hidden = true;
        const toggle = openPanel.parentElement.querySelector('.searchable-select-toggle');
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
            toggle.focus();
        }
    });

    function setupHeaderPopover(menuSelector, toggleSelector, panelSelector, labelAttribute) {
        const menu = document.querySelector(menuSelector);
        if (!menu) return;

        const toggle = menu.querySelector(toggleSelector);
        const panel = menu.querySelector(panelSelector);
        if (!toggle || !panel) return;

        function setOpen(open, restoreFocus) {
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            const label = toggle.getAttribute(labelAttribute) || 'menu';
            toggle.setAttribute('aria-label', (open ? 'Close ' : 'Open ') + label);
            if (restoreFocus) toggle.focus();
        }

        toggle.addEventListener('click', function () {
            setOpen(panel.hidden, false);
        });

        menu.addEventListener('focusout', function (event) {
            if (!menu.contains(event.relatedTarget)) setOpen(false, false);
        });

        document.addEventListener('click', function (event) {
            if (!menu.contains(event.target)) setOpen(false, false);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !panel.hidden) setOpen(false, true);
        });
    }

    setupHeaderPopover('[data-account-menu]', '[data-account-toggle]', '[data-account-panel]', 'data-account-label');
    setupHeaderPopover('[data-quick-actions-menu]', '[data-quick-actions-toggle]', '[data-quick-actions-panel]', 'data-quick-actions-label');

    const sidebarToggle   = document.querySelector('[data-sidebar-toggle]');
    const sidebarBackdrop = document.querySelector('[data-sidebar-backdrop]');

    function setSidebarOpen(open) {
        document.body.classList.toggle('sidebar-open', open);
        if (sidebarToggle) {
            sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            sidebarToggle.setAttribute('data-tooltip', open ? 'Close navigation menu' : 'Open navigation menu');
        }
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            setSidebarOpen(!document.body.classList.contains('sidebar-open'));
        });
    }
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function () {
            setSidebarOpen(false);
        });
    }

    const sidebarCollapse = document.querySelector('[data-sidebar-collapse]');
    const sidebarCollapseGlyph = sidebarCollapse
        ? sidebarCollapse.querySelector('[data-sidebar-collapse-glyph]')
        : null;
    const collapseGlyph = 'm14 18-6-6 6-6m6 12-6-6 6-6';
    const expandGlyph = 'm10 18 6-6-6-6M4 18l6-6-6-6';

    function setSidebarCollapsed(collapsed) {
        document.body.classList.toggle('sidebar-collapsed', collapsed);
        if (sidebarCollapse) {
            const label = collapsed ? 'Expand navigation sidebar' : 'Collapse navigation sidebar';
            sidebarCollapse.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            sidebarCollapse.setAttribute('aria-label', label);
            sidebarCollapse.setAttribute('title', label);
        }
        if (sidebarCollapseGlyph) {
            sidebarCollapseGlyph.setAttribute('d', collapsed ? expandGlyph : collapseGlyph);
        }
    }

    if (sidebarCollapse) {
        setSidebarCollapsed(localStorage.getItem('ahp_sidebar_collapsed') === 'true');
        sidebarCollapse.addEventListener('click', function () {
            const collapsed = !document.body.classList.contains('sidebar-collapsed');
            setSidebarCollapsed(collapsed);
            localStorage.setItem('ahp_sidebar_collapsed', collapsed ? 'true' : 'false');
        });
    }

    const target = document.querySelector('[data-autofocus]');
    if (target) target.focus();

    document.querySelectorAll('.notice-stack .notice').forEach(function (notice) {
        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'notice-close';
        closeBtn.setAttribute('aria-label', 'Dismiss message');
        closeBtn.textContent = '×';
        notice.appendChild(closeBtn);

        let timerId = null;
        function dismiss() {
            if (notice.classList.contains('is-leaving')) return;
            notice.classList.add('is-leaving');
            window.setTimeout(function () {
                const stack = notice.parentElement;
                notice.remove();
                if (stack && stack.children.length === 0) {
                    stack.remove();
                }
            }, 360);
        }

        function startTimer() {
            timerId = window.setTimeout(dismiss, 4500);
        }

        closeBtn.addEventListener('click', function () {
            if (timerId) window.clearTimeout(timerId);
            dismiss();
        });
        if (!notice.classList.contains('notice-success')) {
            notice.addEventListener('mouseenter', function () {
                if (timerId) window.clearTimeout(timerId);
            });
            notice.addEventListener('mouseleave', startTimer);
        }

        startTimer();
    });

    document.querySelectorAll('[data-back]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = 'index.php?route=dashboard';
            }
        });
    });

    document.querySelectorAll('[data-print]').forEach(function (printButton) {
        printButton.addEventListener('click', function () {
            window.print();
        });
    });

    document.querySelectorAll('form[data-confirm-deactivate]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.dataset.confirmDeactivate || 'Deactivate this record?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    document.querySelectorAll('form[data-confirm-delete]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.dataset.confirmDelete || 'Permanently delete this record?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    document.querySelectorAll('[data-record-form-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            const form = document.getElementById(toggle.dataset.recordFormToggle);
            if (!form) return;
            const open = form.hidden;
            form.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.textContent = open ? 'Cancel' : 'Add supplier';
            if (open) {
                const firstInput = form.querySelector('input:not([type="hidden"])');
                if (firstInput) firstInput.focus();
            }
        });
    });

    function openCsvPreview(table, requestedFilename) {
        if (!table) {
            console.error('CSV export requires a table on the current page.');
            return;
        }

        const headerCells = Array.from(table.querySelectorAll('thead tr:last-child th'));
        if (headerCells.length === 0) {
            console.error('CSV export requires table headers.');
            return;
        }

        const columns = headerCells
            .map(function (cell, index) { return { cell: cell, index: index }; })
            .filter(function (column) { return !column.cell.hasAttribute('data-export-ignore'); });
        const headers = columns.map(function (column) {
            return column.cell.textContent.replace(/\s+/g, ' ').trim();
        });
        const rows = Array.from(table.querySelectorAll('tbody tr'))
            .filter(function (row) { return !row.classList.contains('table-empty'); })
            .map(function (row) {
                const cells = Array.from(row.cells);
                return columns.map(function (column) {
                    const cell = cells[column.index];
                    return cell ? cell.innerText.replace(/\s+/g, ' ').trim() : '';
                });
            });

        let filename = requestedFilename || 'table-export.csv';
        filename = filename.replace(/[^a-z0-9._-]+/gi, '-').replace(/^-+|-+$/g, '');
        if (!filename.toLowerCase().endsWith('.csv')) filename += '.csv';

        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop csv-preview-backdrop';
        backdrop.setAttribute('role', 'dialog');
        backdrop.setAttribute('aria-modal', 'true');
        backdrop.setAttribute('aria-labelledby', 'csv-preview-title');

        const card = document.createElement('div');
        card.className = 'modal-card csv-preview-card';

        const eyebrow = document.createElement('p');
        eyebrow.className = 'eyebrow';
        eyebrow.textContent = 'CSV export preview';

        const title = document.createElement('h2');
        title.id = 'csv-preview-title';
        title.textContent = 'Confirm download';

        const summary = document.createElement('p');
        summary.className = 'muted text-md csv-preview-summary';
        summary.textContent = filename + ' · ' + rows.length + ' data row' + (rows.length === 1 ? '' : 's');

        const preview = document.createElement('div');
        preview.className = 'csv-preview-table';
        const previewTable = document.createElement('table');
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        headers.forEach(function (header) {
            const th = document.createElement('th');
            th.textContent = header;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        previewTable.appendChild(thead);

        const tbody = document.createElement('tbody');
        const previewRows = rows.slice(0, 5);
        if (previewRows.length === 0) {
            const emptyRow = document.createElement('tr');
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = Math.max(headers.length, 1);
            emptyCell.textContent = 'No data rows';
            emptyRow.appendChild(emptyCell);
            tbody.appendChild(emptyRow);
        } else {
            previewRows.forEach(function (row) {
                const tr = document.createElement('tr');
                row.forEach(function (value) {
                    const td = document.createElement('td');
                    td.textContent = value;
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        }
        previewTable.appendChild(tbody);
        preview.appendChild(previewTable);

        const actions = document.createElement('div');
        actions.className = 'modal-actions';
        const cancel = document.createElement('button');
        cancel.type = 'button';
        cancel.className = 'btn btn-ghost';
        cancel.textContent = 'Cancel';
        const download = document.createElement('button');
        download.type = 'button';
        download.className = 'btn btn-primary';
        download.textContent = 'Download CSV';

        function closePreview() {
            backdrop.remove();
            document.removeEventListener('keydown', handleKeydown);
        }

        function handleKeydown(event) {
            if (event.key === 'Escape') closePreview();
        }

        cancel.addEventListener('click', closePreview);
        backdrop.addEventListener('click', function (event) {
            if (event.target === backdrop) closePreview();
        });
        download.addEventListener('click', function () {
            const csvCell = function (value) {
                const safeValue = /^[\t\r ]*[=+\-@]/.test(value) ? "'" + value : value;
                return '"' + safeValue.replace(/"/g, '""') + '"';
            };
            const content = [headers].concat(rows)
                .map(function (row) { return row.map(csvCell).join(','); })
                .join('\r\n');
            const blob = new Blob(['\uFEFF' + content], { type: 'text/csv;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
            closePreview();
        });
        document.addEventListener('keydown', handleKeydown);

        actions.appendChild(cancel);
        actions.appendChild(download);
        card.appendChild(eyebrow);
        card.appendChild(title);
        card.appendChild(summary);
        card.appendChild(preview);
        card.appendChild(actions);
        backdrop.appendChild(card);
        document.body.appendChild(backdrop);
        download.focus();
    }

    document.querySelectorAll('[data-export-csv]').forEach(function (exportButton) {
        exportButton.addEventListener('click', function () {
            const page = exportButton.closest('main');
            const table = page ? page.querySelector('.table-wrap table') : null;
            openCsvPreview(table, exportButton.dataset.exportFilename);
        });
    });

    document.querySelectorAll('[data-report-csv-export]').forEach(function (exportButton) {
        exportButton.addEventListener('click', function (event) {
            event.preventDefault();
            const table = document.querySelector('main .table-wrap table');
            openCsvPreview(table, 'sales-report.csv');
        });
    });

    const revealTargets = document.querySelectorAll('[data-reveal]');
    if (revealTargets.length > 0 && 'IntersectionObserver' in window && !prefersReducedMotion) {
        const revealObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -24px 0px' });

        revealTargets.forEach(function (target) {
            target.classList.add('reveal-ready');
            revealObserver.observe(target);
        });
    }

    const scrollProgressBar = document.querySelector('[data-scroll-progress]');
    if (scrollProgressBar) {
        const siteHeader = document.querySelector('.site-header');
        let scrollUpdatePending = false;
        const updateScrollProgress = function () {
            const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollTop = window.scrollY || document.documentElement.scrollTop;
            const progress = scrollableHeight > 0 ? Math.min(1, scrollTop / scrollableHeight) : 0;
            scrollProgressBar.style.transform = 'scaleX(' + progress + ')';
            if (siteHeader) siteHeader.classList.toggle('is-scrolled', scrollTop > 8);
            scrollUpdatePending = false;
        };
        const scheduleScrollUpdate = function () {
            if (scrollUpdatePending) return;
            scrollUpdatePending = true;
            window.requestAnimationFrame(updateScrollProgress);
        };

        window.addEventListener('scroll', scheduleScrollUpdate, { passive: true });
        window.addEventListener('resize', scheduleScrollUpdate);
        updateScrollProgress();
    }

    const notificationCenter = document.querySelector('[data-notification-center]');
    if (notificationCenter) {
        const notificationToggle = notificationCenter.querySelector('[data-notification-toggle]');
        const notificationPanel = notificationCenter.querySelector('[data-notification-panel]');
        const notificationCount = notificationCenter.querySelector('[data-notification-count]');
        const notificationList = notificationCenter.querySelector('[data-notification-list]');
        const notificationEmpty = notificationCenter.querySelector('[data-notification-empty]');
        const notificationStatus = notificationCenter.querySelector('[data-notification-status]');
        const markNotificationsRead = notificationCenter.querySelector('[data-notification-read]');
        const notificationUrl = notificationCenter.dataset.notificationsUrl;
        const storageKey = 'ahp_read_notifications_' + notificationCenter.dataset.notificationUser;
        let currentNotifications = [];
        let knownNotificationIds = null;
        let readNotificationIds = new Set();

        try {
            const storedIds = JSON.parse(localStorage.getItem(storageKey) || '[]');
            if (Array.isArray(storedIds)) {
                readNotificationIds = new Set(storedIds.filter(function (id) {
                    return typeof id === 'string';
                }));
            }
        } catch (error) {
            console.error('Could not read notification state.', error);
        }

        function saveReadNotificationIds() {
            try {
                localStorage.setItem(storageKey, JSON.stringify(Array.from(readNotificationIds)));
            } catch (error) {
                console.error('Could not save notification state.', error);
                if (notificationStatus) notificationStatus.textContent = 'Could not save read status.';
            }
        }

        function updateNotificationList(checkedAt) {
            if (!notificationList) return;
            notificationList.replaceChildren();

            const activeIds = new Set(currentNotifications.map(function (item) { return item.id; }));
            readNotificationIds = new Set(Array.from(readNotificationIds).filter(function (id) {
                return activeIds.has(id);
            }));
            saveReadNotificationIds();

            let unreadCount = 0;
            currentNotifications.forEach(function (item) {
                const isUnread = !readNotificationIds.has(item.id);
                if (isUnread) unreadCount += 1;

                const listItem = document.createElement('li');
                const link = document.createElement('a');
                const dot = document.createElement('span');
                const copy = document.createElement('span');
                const title = document.createElement('strong');
                const message = document.createElement('span');
                link.className = 'notification-item' + (isUnread ? ' is-unread' : '');
                link.href = item.url;
                link.setAttribute('aria-label', item.title + '. ' + item.message);
                dot.className = 'notification-dot';
                title.textContent = item.title;
                message.textContent = item.message;
                copy.append(title, message);
                link.append(dot, copy);
                link.addEventListener('click', function () {
                    readNotificationIds.add(item.id);
                    saveReadNotificationIds();
                    updateNotificationList();
                });
                listItem.appendChild(link);
                notificationList.appendChild(listItem);
            });

            if (notificationCount) {
                notificationCount.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
                notificationCount.hidden = unreadCount === 0;
            }
            if (notificationToggle) {
                notificationToggle.setAttribute(
                    'aria-label',
                    unreadCount > 0 ? 'Open notifications, ' + unreadCount + ' unread' : 'Open notifications'
                );
            }
            if (notificationEmpty) notificationEmpty.hidden = currentNotifications.length > 0;
            if (notificationStatus && checkedAt) {
                notificationStatus.textContent = 'Updated ' + new Date(checkedAt).toLocaleTimeString();
            }
        }

        function showNotificationToast(item) {
            const toast = document.createElement('div');
            const title = document.createElement('strong');
            const message = document.createElement('span');
            toast.className = 'notification-toast';
            toast.setAttribute('role', 'status');
            title.textContent = item.title;
            message.textContent = item.message;
            toast.append(title, message);
            document.body.appendChild(toast);
            window.setTimeout(function () { toast.remove(); }, 6500);
        }

        function markAllNotificationsRead() {
            currentNotifications.forEach(function (item) {
                readNotificationIds.add(item.id);
            });
            saveReadNotificationIds();
            updateNotificationList();
        }

        async function refreshNotifications() {
            try {
                const response = await fetch(notificationUrl, {
                    method: 'GET',
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });
                if (!response.ok) throw new Error('Notification request failed with status ' + response.status);

                const payload = await response.json();
                if (!Array.isArray(payload.notifications)) throw new Error('Notification response is invalid.');

                const nextNotifications = payload.notifications.filter(function (item) {
                    return item && typeof item.id === 'string'
                        && typeof item.title === 'string'
                        && typeof item.message === 'string'
                        && typeof item.url === 'string';
                });
                const nextIds = new Set(nextNotifications.map(function (item) { return item.id; }));
                const newNotifications = knownNotificationIds === null
                    ? []
                    : nextNotifications.filter(function (item) {
                        return !knownNotificationIds.has(item.id) && !readNotificationIds.has(item.id);
                    });

                currentNotifications = nextNotifications;
                knownNotificationIds = nextIds;
                updateNotificationList(payload.checked_at);

                newNotifications.slice(0, 3).forEach(showNotificationToast);
                if (newNotifications.length > 3) {
                    showNotificationToast({
                        title: 'More notifications',
                        message: (newNotifications.length - 3) + ' additional updates are available.',
                    });
                }
            } catch (error) {
                console.error('Could not refresh notifications.', error);
                if (notificationStatus) notificationStatus.textContent = 'Notifications are temporarily unavailable.';
            }
        }

        if (notificationToggle && notificationPanel) {
            notificationToggle.addEventListener('click', function () {
                const open = notificationPanel.hidden;
                notificationPanel.hidden = !open;
                notificationToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) markAllNotificationsRead();
            });

            document.addEventListener('click', function (event) {
                if (!notificationCenter.contains(event.target)) {
                    notificationPanel.hidden = true;
                    notificationToggle.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !notificationPanel.hidden) {
                    notificationPanel.hidden = true;
                    notificationToggle.setAttribute('aria-expanded', 'false');
                    notificationToggle.focus();
                }
            });
        }

        if (markNotificationsRead) {
            markNotificationsRead.addEventListener('click', markAllNotificationsRead);
        }

        refreshNotifications();
        window.setInterval(refreshNotifications, 15000);
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) refreshNotifications();
        });
    }

    const returnForm = document.querySelector('[data-return-form]');
    if (returnForm) {
        returnForm.addEventListener('submit', function (event) {
            if (!window.confirm(returnForm.dataset.confirmRefund || 'Process this refund?')) {
                event.preventDefault();
            }
        });
        const refundInput = document.getElementById('refund_amount');
        const maxRefundEl = returnForm.querySelector('[data-max-refund]');
        let refundTouched = false;

        if (refundInput) {
            refundInput.addEventListener('input', function () {
                refundTouched = true;
            });
        }

        function updateReturnEstimate() {
            let expected = 0;
            returnForm.querySelectorAll('[data-return-qty]').forEach(function (input) {
                const qty = Math.max(0, parseInt(input.value || '0', 10) || 0);
                const price = parseFloat(input.dataset.unitPrice || '0') || 0;
                expected += qty * price;
            });
            expected = Math.round(expected * 100) / 100;
            if (maxRefundEl) {
                maxRefundEl.textContent = expected > 0 ? '(Max: ' + fmt(expected) + ')' : '';
            }
            if (refundInput) {
                refundInput.max = expected.toFixed(2);
                if (!refundTouched) {
                    refundInput.value = expected.toFixed(2);
                }
            }
        }

        returnForm.addEventListener('input', function (event) {
            if (event.target.matches('[data-return-qty]')) {
                updateReturnEstimate();
            }
        });
        updateReturnEstimate();
    }

    const form = document.querySelector('[data-sale-form]');
    if (!form) return;

    const tbody       = document.getElementById('sale-lines');
    const template    = document.getElementById('sale-line-template');
    const taxRate     = parseFloat(form.dataset.taxRate || '0');
    const amountInput = form.querySelector('[data-amount-paid]');
    let lastTotal     = 0;

    function syncRowStock(row) {
        const select = row.querySelector('[data-product-select]');
        const qtyInput = row.querySelector('[data-quantity]');
        if (!select || !qtyInput) return;
        const option = select.selectedIndex > 0 ? select.options[select.selectedIndex] : null;
        const stock = option ? parseInt(option.dataset.stock || '0', 10) : 0;
        if (option && stock > 0) {
            qtyInput.max = String(stock);
        } else {
            qtyInput.removeAttribute('max');
        }
    }

    function lineTotal(row) {
        const select = row.querySelector('[data-product-select]');
        const price  = select && select.selectedIndex > 0
            ? parseFloat(select.options[select.selectedIndex].dataset.price || '0')
            : 0;
        const qty      = Math.max(0, parseFloat(row.querySelector('[data-quantity]').value || '0'));
        const discount = Math.max(0, parseFloat(row.querySelector('[data-discount]').value || '0'));
        const total    = price * qty - discount;
        return total > 0 ? total : 0;
    }

    function recalculate() {
        let subtotal = 0;
        tbody.querySelectorAll('tr').forEach(function (row) {
            syncRowStock(row);
            const total = lineTotal(row);
            subtotal += total;
            const cell = row.querySelector('[data-line-total]');
            if (cell) cell.textContent = fmt(total);
        });
        const tax = subtotal * taxRate;
        lastTotal = Math.round((subtotal + tax) * 100) / 100;

        const subtotalEl  = document.getElementById('subtotal');
        const taxEl       = document.getElementById('tax');
        const totalEl     = document.getElementById('total');
        const changeRow   = document.getElementById('change-row');
        const changeLabel = document.getElementById('change-label');
        const changeDueEl = document.getElementById('change-due');

        if (subtotalEl) subtotalEl.textContent = fmt(subtotal);
        if (taxEl)      taxEl.textContent      = fmt(tax);
        if (totalEl)    totalEl.textContent    = fmt(lastTotal);

        if (amountInput && changeDueEl && changeLabel && changeRow) {
            const paid = parseFloat(amountInput.value || '0') || 0;
            const diff = Math.round((paid - lastTotal) * 100) / 100;
            changeRow.classList.remove('is-positive', 'is-negative');
            if (diff >= 0) {
                changeLabel.textContent = 'Change due';
                changeDueEl.textContent = fmt(diff);
                if (lastTotal > 0) changeRow.classList.add('is-positive');
            } else {
                changeLabel.textContent = 'Balance due';
                changeDueEl.textContent = fmt(Math.abs(diff));
                if (lastTotal > 0) changeRow.classList.add('is-negative');
            }
        }
    }

    form.addEventListener('input', recalculate);
    form.addEventListener('change', recalculate);

    form.addEventListener('click', function (event) {
        const payExact = event.target.closest('[data-pay-exact]');
        if (payExact && amountInput) {
            amountInput.value = lastTotal.toFixed(2);
            recalculate();
            return;
        }

        const addButton = event.target.closest('[data-add-line]');
        if (addButton) {
            const clone = template.content.firstElementChild.cloneNode(true);
            tbody.appendChild(clone);
            recalculate();
            return;
        }

        const removeButton = event.target.closest('[data-remove-line]');
        if (removeButton) {
            const rows = tbody.querySelectorAll('tr');
            if (rows.length > 1) {
                removeButton.closest('tr').remove();
                recalculate();
            }
        }
    });

    recalculate();
});
