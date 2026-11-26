<?php
use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\BusinessData;

$authUser = Auth::user();
$currentRoute = Request::route();
$sidebarCounts = $authUser !== null ? BusinessData::sidebarCounts() : [];
$navBadge = static function (string $key) use ($sidebarCounts): ?string {
    $count = (int)($sidebarCounts[$key] ?? 0);
    if ($count <= 0) {
        return null;
    }
    return $count > 99 ? '99+' : (string)$count;
};
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Algorithm Hardware Plug – Retail Management System">
    <title><?= View::e($title ?? 'Algorithm Hardware Plug') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= View::e(Request::asset('images/site-logo.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="<?= View::e(Request::asset('css/app.css')) ?>">
</head>
<body class="<?= $authUser !== null ? 'has-sidebar' : ($currentRoute === 'login' ? 'auth-page' : '') ?>">
<header class="site-header">
    <div class="header-inner <?= $authUser !== null ? 'header-fluid' : 'shell' ?>">
        <div class="header-left">
            <?php if ($authUser !== null): ?>
                <button type="button" class="icon-btn sidebar-toggle" data-sidebar-toggle
                        data-tooltip="Open navigation menu"
                        aria-label="Toggle navigation menu" aria-expanded="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            <?php endif; ?>
            <a class="brand" href="<?= View::e(Request::url($authUser !== null ? 'dashboard' : 'login')) ?>"
               title="Go to <?= $authUser !== null ? 'dashboard' : 'sign in' ?>"
               aria-label="Algorithm Hardware Plug">
                <img class="brand-mark" src="<?= View::e(Request::asset('images/site-logo.svg')) ?>" alt="" width="42" height="42">
                <span class="brand-text">
                    <strong>Algorithm Hardware Plug</strong>
                    <small>Retail Management System</small>
                </span>
            </a>
        </div>

        <div class="header-actions">
            <?php if (Auth::hasRole('Administrator', 'Manager', 'Employee')): ?>
                <div class="notification-center"
                     data-notification-center
                     data-notifications-url="<?= View::e(Request::url('notifications')) ?>"
                     data-notification-user="<?= (int)$authUser['id'] ?>">
                    <button type="button" class="notification-toggle" data-notification-toggle
                            aria-label="Open notifications" aria-expanded="false" title="Notifications">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/>
                            <path d="M10 21h4"/>
                        </svg>
                        <span class="notification-count" data-notification-count hidden>0</span>
                    </button>
                    <section class="notification-panel" data-notification-panel role="region"
                             aria-label="Notifications" hidden>
                        <div class="notification-panel-header">
                            <div>
                                <strong>Notifications</strong>
                                <small data-notification-status aria-live="polite">Checking for updates…</small>
                            </div>
                            <button type="button" class="notification-mark-read" data-notification-read>
                                Mark all read
                            </button>
                        </div>
                        <ul class="notification-list" data-notification-list></ul>
                        <p class="notification-empty" data-notification-empty hidden>No active notifications.</p>
                    </section>
                </div>
            <?php endif; ?>
            <button type="button" class="btn btn-ghost btn-sm theme-toggle" data-theme-toggle
                    data-tooltip="Switch to dark mode" title="Switch to dark mode" aria-label="Switch to dark mode">
                <span class="theme-icon" data-theme-icon aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                        <path d="M20.9 13A9 9 0 0 1 11 3.1 9 9 0 1 0 20.9 13Z"/>
                    </svg>
                </span>
                <span class="theme-label" data-theme-label>Dark</span>
            </button>
            <?php if ($authUser !== null): ?>
                <span class="role-pill" title="Active role: <?= View::e($authUser['role']) ?>"><?= View::e($authUser['role']) ?></span>
                <a class="btn btn-ghost btn-sm btn-signout" href="<?= View::e(Request::url('logout')) ?>"
                   data-tooltip="End your current session" title="End your current session">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Sign out</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if ($currentRoute === 'login'): ?>
    <div class="auth-ambient" aria-hidden="true">
        <span class="auth-orbit auth-orbit-one"></span>
        <span class="auth-orbit auth-orbit-two"></span>
        <span class="auth-glow auth-glow-one"></span>
        <span class="auth-glow auth-glow-two"></span>
    </div>
<?php endif; ?>

<?php if ($authUser !== null): ?>
    <div class="sidebar-backdrop" data-sidebar-backdrop></div>
    <div class="app-shell">
        <aside class="sidebar" id="app-sidebar" aria-label="Main navigation">
            <div class="sidebar-section">
                <div class="sidebar-topline">
                    <p class="sidebar-heading">Workspace</p>
                    <button type="button" class="sidebar-collapse" data-sidebar-collapse
                            aria-label="Collapse navigation sidebar" aria-expanded="true"
                            title="Collapse navigation sidebar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path data-sidebar-collapse-glyph d="m14 18-6-6 6-6m6 12-6-6 6-6"/>
                        </svg>
                    </button>
                </div>
                <nav class="sidebar-nav">
                    <a href="<?= View::e(Request::url('dashboard')) ?>" aria-label="Dashboard" title="Dashboard"
                       <?= $currentRoute === 'dashboard' ? 'aria-current="page"' : '' ?>>
                        <span class="nav-ico" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                <rect x="3" y="3" width="8" height="8" rx="1"/>
                                <rect x="13" y="3" width="8" height="8" rx="1"/>
                                <rect x="3" y="13" width="8" height="8" rx="1"/>
                                <rect x="13" y="13" width="8" height="8" rx="1"/>
                            </svg>
                        </span>
                        <span>Dashboard</span>
                    </a>

                    <?php if (Auth::hasRole('Administrator', 'Manager', 'Employee')): ?>
                        <a href="<?= View::e(Request::url('inventory')) ?>" aria-label="Inventory" title="Inventory"
                           <?= str_starts_with($currentRoute, 'inventory') ? 'aria-current="page"' : '' ?>>
                            <span class="nav-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                    <path d="m3 7 9-4 9 4v10l-9 4-9-4V7Z"/>
                                    <path d="m3 7 9 4 9-4M12 11v10"/>
                                </svg>
                            </span>
                            <span>Inventory</span>
                            <?php if (($badge = $navBadge('LowStockCount')) !== null): ?>
                                <span class="nav-count nav-count-alert" aria-label="<?= View::e($badge) ?> low-stock items"><?= View::e($badge) ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= View::e(Request::url('pos')) ?>" aria-label="Point of Sale" title="Point of Sale"
                           <?= str_starts_with($currentRoute, 'pos') ? 'aria-current="page"' : '' ?>>
                            <span class="nav-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                    <path d="M3 3h2l2.4 11.4a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L22 7H6"/>
                                    <circle cx="10" cy="20" r="1"/>
                                    <circle cx="18" cy="20" r="1"/>
                                </svg>
                            </span>
                            <span>Point of Sale</span>
                            <?php if (($badge = $navBadge('SalesTodayCount')) !== null): ?>
                                <span class="nav-count" aria-label="<?= View::e($badge) ?> sales today"><?= View::e($badge) ?></span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <a href="<?= View::e(Request::url('catalog')) ?>" aria-label="Product Catalog" title="Product Catalog"
                           <?= $currentRoute === 'catalog' ? 'aria-current="page"' : '' ?>>
                            <span class="nav-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                    <path d="M20 13 13 20 3 10V3h7l10 10Z"/>
                                    <circle cx="7.5" cy="7.5" r=".5"/>
                                </svg>
                            </span>
                            <span>Product Catalog</span>
                        </a>
                        <a href="<?= View::e(Request::url('my-sales')) ?>" aria-label="My Purchases" title="My Purchases"
                           <?= $currentRoute === 'my-sales' ? 'aria-current="page"' : '' ?>>
                            <span class="nav-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                    <path d="M4 3h16v18l-3-2-3 2-4-2-3 2-3-2V3Z"/>
                                    <path d="M8 8h8M8 12h8M8 16h4"/>
                                </svg>
                            </span>
                            <span>My Purchases</span>
                            <?php if (($badge = $navBadge('PurchaseCount')) !== null): ?>
                                <span class="nav-count" aria-label="<?= View::e($badge) ?> purchases"><?= View::e($badge) ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>

                    <?php if (Auth::hasRole('Administrator', 'Manager')): ?>
                        <a href="<?= View::e(Request::url('returns')) ?>" aria-label="Returns" title="Returns"
                           <?= str_starts_with($currentRoute, 'returns') ? 'aria-current="page"' : '' ?>>
                            <span class="nav-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                    <path d="M9 14 4 9l5-5"/>
                                    <path d="M4 9h10a6 6 0 0 1 0 12h-2"/>
                                </svg>
                            </span>
                            <span>Returns</span>
                            <?php if (($badge = $navBadge('PendingReturnCount')) !== null): ?>
                                <span class="nav-count nav-count-alert" aria-label="<?= View::e($badge) ?> pending returns"><?= View::e($badge) ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= View::e(Request::url('reports')) ?>" aria-label="Sales Reports" title="Sales Reports"
                           <?= $currentRoute === 'reports' ? 'aria-current="page"' : '' ?>>
                            <span class="nav-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                    <path d="M3 3v18h18"/>
                                    <path d="M18 17V9M13 17V5M8 17v-3"/>
                                </svg>
                            </span>
                            <span>Sales Reports</span>
                        </a>
                    <?php endif; ?>

                    <?php if (Auth::hasRole('Administrator', 'Manager', 'Employee')): ?>
                        <?php $recordsSection = Auth::hasRole('Administrator', 'Manager') ? 'suppliers' : 'customers'; ?>
                        <a href="<?= View::e(Request::url('admin/records&section=' . $recordsSection)) ?>" aria-label="Business Records" title="Business Records"
                           <?= str_starts_with($currentRoute, 'admin/records') ? 'aria-current="page"' : '' ?>>
                            <span class="nav-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                    <path d="M3 7 12 3l9 4-9 4-9-4Z"/>
                                    <path d="M3 12l9 4 9-4M3 17l9 4 9-4"/>
                                </svg>
                            </span>
                            <span>Business Records</span>
                        </a>
                    <?php endif; ?>

                    <?php if (Auth::hasRole('Administrator')): ?>
                        <a href="<?= View::e(Request::url('admin/users')) ?>" aria-label="Users and Roles" title="Users and Roles"
                           <?= $currentRoute === 'admin/users' ? 'aria-current="page"' : '' ?>>
                            <span class="nav-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                    <circle cx="9" cy="8" r="4"/>
                                    <path d="M3 21v-2a6 6 0 0 1 12 0v2"/>
                                    <path d="M16 4.5a4 4 0 0 1 0 7.5M21 21v-2a6 6 0 0 0-4-5.7"/>
                                </svg>
                            </span>
                            <span>Users &amp; Roles</span>
                        </a>
                        <a href="<?= View::e(Request::url('reports/audit')) ?>" aria-label="Audit Trail" title="Audit Trail"
                           <?= $currentRoute === 'reports/audit' ? 'aria-current="page"' : '' ?>>
                            <span class="nav-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                    <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                            </span>
                            <span>Audit Trail</span>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <strong><?= View::e($authUser['full_name']) ?></strong>
                    <small><?= View::e($authUser['username']) ?> &middot; <?= View::e($authUser['role']) ?></small>
                </div>
                <a class="btn btn-ghost btn-sm btn-signout sidebar-signout" href="<?= View::e(Request::url('logout')) ?>"
                   aria-label="Sign out" title="Sign out">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Sign out</span>
                </a>
            </div>
        </aside>

        <div class="app-main">
            <main class="page page-with-sidebar">
                <?php $flashes = Session::consumeFlash(); ?>
                <?php if ($flashes !== []): ?>
                    <div class="notice-stack" role="alert" aria-live="polite">
                        <?php foreach ($flashes as $type => $messages): ?>
                            <?php foreach ((array)$messages as $message): ?>
                                <div class="notice notice-<?= View::e($type) ?>"><?= View::e($message) ?></div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </main>

            <footer class="site-footer footer-fluid">
                <span>&copy; <?= date('Y') ?> Algorithm Hardware Plug. All rights reserved.</span>
                <span>Secured retail operations platform</span>
            </footer>
        </div>
    </div>
<?php else: ?>
    <main class="shell page">
        <?php $flashes = Session::consumeFlash(); ?>
        <?php if ($flashes !== []): ?>
            <div class="notice-stack" role="alert" aria-live="polite">
                <?php foreach ($flashes as $type => $messages): ?>
                    <?php foreach ((array)$messages as $message): ?>
                        <div class="notice notice-<?= View::e($type) ?>"><?= View::e($message) ?></div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="site-footer shell">
        <span>&copy; <?= date('Y') ?> Algorithm Hardware Plug. All rights reserved.</span>
        <span>Secured retail operations platform</span>
    </footer>
<?php endif; ?>

<?php if (isset($approval) && is_array($approval) && $approval !== []): ?>
    <?php require APP_ROOT . '/app/Views/components/approval_modal.php'; ?>
<?php endif; ?>

<script src="<?= View::e(Request::asset('js/app.js')) ?>" defer></script>
</body>
</html>
