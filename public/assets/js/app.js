document.addEventListener('DOMContentLoaded', function () {
    const currency = 'GH₵';

    function fmt(n) {
        return currency + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // ── Dark mode toggle ───────────────────────────────────────────
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const themeIcon   = document.querySelector('[data-theme-icon]');
    const themeLabel  = document.querySelector('[data-theme-label]');
    const sunIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>';
    const moonIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/></svg>';

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        const tip = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        if (themeIcon)  themeIcon.innerHTML = theme === 'dark' ? sunIcon : moonIcon;
        if (themeLabel) themeLabel.textContent = theme === 'dark' ? 'Light' : 'Dark';
        if (themeToggle) {
            themeToggle.setAttribute('data-tooltip', tip);
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

    // ── Mobile left sidebar drawer ─────────────────────────────────
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

    // ── Autofocus ──────────────────────────────────────────────────
    const target = document.querySelector('[data-autofocus]');
    if (target) target.focus();

    // ── Auto-dismiss flash notices ─────────────────────────────────
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
        notice.addEventListener('mouseenter', function () {
            if (timerId) window.clearTimeout(timerId);
        });
        notice.addEventListener('mouseleave', startTimer);

        startTimer();
    });

    // ── History back (CSP-safe) ────────────────────────────────────
    document.querySelectorAll('[data-back]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = 'index.php?route=dashboard';
            }
        });
    });

    // ── Print button ───────────────────────────────────────────────
    const printButton = document.querySelector('[data-print]');
    if (printButton) {
        printButton.addEventListener('click', function () {
            window.print();
        });
    }

    // ── Return form auto-refund helper ─────────────────────────────
    const returnForm = document.querySelector('[data-return-form]');
    if (returnForm) {
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

    // ── POS sale form ──────────────────────────────────────────────
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
