document.addEventListener('DOMContentLoaded', function () {
    const currency = 'GH₵';

    function fmt(n) {
        return currency + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

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

    document.querySelectorAll('[data-export-csv]').forEach(function (exportButton) {
        exportButton.addEventListener('click', function () {
            const page = exportButton.closest('main');
            const table = page ? page.querySelector('.table-wrap table') : null;
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
            const rows = Array.from(table.querySelectorAll('tbody tr'))
                .filter(function (row) { return !row.classList.contains('table-empty'); })
                .map(function (row) {
                    const cells = Array.from(row.cells);
                    return columns.map(function (column) {
                        const cell = cells[column.index];
                        return cell ? cell.innerText.replace(/\s+/g, ' ').trim() : '';
                    });
                });
            const csvCell = function (value) {
                const safeValue = /^[\t\r ]*[=+\-@]/.test(value) ? "'" + value : value;
                return '"' + safeValue.replace(/"/g, '""') + '"';
            };
            const content = [columns.map(function (column) {
                return column.cell.textContent.replace(/\s+/g, ' ').trim();
            })]
                .concat(rows)
                .map(function (row) { return row.map(csvCell).join(','); })
                .join('\r\n');
            let filename = exportButton.dataset.exportFilename || 'table-export.csv';
            filename = filename.replace(/[^a-z0-9._-]+/gi, '-').replace(/^-+|-+$/g, '');
            if (!filename.toLowerCase().endsWith('.csv')) filename += '.csv';

            const blob = new Blob(['\uFEFF' + content], { type: 'text/csv;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
        });
    });

    const revealTargets = document.querySelectorAll('[data-reveal]');
    const prefersReducedMotion = window.matchMedia
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
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
