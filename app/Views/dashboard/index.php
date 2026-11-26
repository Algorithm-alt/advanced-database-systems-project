<?php
use App\Core\Auth;
use App\Core\Request;
use App\Core\View;

$moduleCards = [
    ['icon' => 'inventory', 'title' => 'Inventory', 'desc' => 'Review stock levels, low-stock alerts and full movement history.', 'route' => 'inventory', 'label' => 'Open inventory', 'roles' => ['Administrator', 'Manager', 'Employee']],
    ['icon' => 'pos', 'title' => 'Point of sale', 'desc' => 'Create sales, record payments and print receipts from one screen.', 'route' => 'pos', 'label' => 'Open POS', 'roles' => ['Administrator', 'Manager', 'Employee']],
    ['icon' => 'catalog', 'title' => 'Product catalog', 'desc' => 'Browse available hardware products, prices and stock status.', 'route' => 'catalog', 'label' => 'Browse catalog', 'roles' => ['Customer']],
    ['icon' => 'purchases', 'title' => 'My purchases', 'desc' => 'View your personal order history and payment status.', 'route' => 'my-sales', 'label' => 'View purchases', 'roles' => ['Customer']],
    ['icon' => 'returns', 'title' => 'Returns', 'desc' => 'Process approved returns and review refund history.', 'route' => 'returns', 'label' => 'Open returns', 'roles' => ['Administrator', 'Manager']],
    ['icon' => 'reports', 'title' => 'Reports', 'desc' => 'Generate sales reports and review the security audit trail.', 'route' => 'reports', 'label' => 'Open reports', 'roles' => ['Administrator', 'Manager']],
    ['icon' => 'administration', 'title' => 'Administration', 'desc' => 'Manage users and application roles with critical-action approval.', 'route' => 'admin/users', 'label' => 'Open admin', 'roles' => ['Administrator']],
    ['icon' => 'suppliers', 'title' => 'Suppliers', 'desc' => 'Review supplier contacts and add suppliers to the catalogue.', 'route' => 'admin/records&section=suppliers', 'label' => 'Manage suppliers', 'roles' => ['Administrator', 'Manager']],
    ['icon' => 'customers', 'title' => 'Customers', 'desc' => 'Review customer records and add customers for point of sale.', 'route' => 'admin/records&section=customers', 'label' => 'Manage customers', 'roles' => ['Administrator', 'Manager', 'Employee']],
    ['icon' => 'employees', 'title' => 'Employees', 'desc' => 'Review staff records and add employees for sales and returns.', 'route' => 'admin/records&section=employees', 'label' => 'Manage employees', 'roles' => ['Administrator']],
];

$cardIconPaths = [
    'inventory' => '<path d="m3 7 9-4 9 4v10l-9 4-9-4V7Z"/><path d="m3 7 9 4 9-4M12 11v10"/>',
    'pos' => '<path d="M3 3h2l2.4 11.4a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L22 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/>',
    'catalog' => '<path d="M20 13 13 20 3 10V3h7l10 10Z"/><circle cx="7.5" cy="7.5" r=".5"/>',
    'purchases' => '<path d="M4 3h16v18l-3-2-3 2-4-2-3 2-3-2V3Z"/><path d="M8 8h8M8 12h8M8 16h4"/>',
    'returns' => '<path d="M9 14 4 9l5-5"/><path d="M4 9h10a6 6 0 0 1 0 12h-2"/>',
    'reports' => '<path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/>',
    'administration' => '<circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.7a8 8 0 0 1-1.7 1l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.7-1l-1.7.7-1.4-2.4 1.4-1.1a7 7 0 0 1 0-2l-1.4-1.1 1.4-2.4 1.7.7a8 8 0 0 1 1.7-1l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.7 1l1.7-.7 1.4 2.4-1.4 1.1a7 7 0 0 1 0 2Z"/>',
    'suppliers' => '<path d="M3 7 12 3l9 4-9 4-9-4Z"/><path d="M3 12l9 4 9-4M3 17l9 4 9-4"/>',
    'customers' => '<circle cx="9" cy="8" r="4"/><path d="M3 21v-2a6 6 0 0 1 12 0v2"/><path d="M16 4.5a4 4 0 0 1 0 7.5M21 21v-2a6 6 0 0 0-4-5.7"/>',
    'employees' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 5V3h8v2M8 11h8M8 15h5"/>',
];

if (Auth::hasRole('Administrator')) {
    $hero = [
        'eyebrow' => 'Administrator workspace',
        'description' => 'Keep stock, sales and team operations aligned from one clear view.',
        'action' => 'Manage business records',
        'route' => 'admin/records&section=suppliers',
        'icon' => 'administration',
    ];
} elseif (Auth::hasRole('Manager')) {
    $hero = [
        'eyebrow' => 'Manager workspace',
        'description' => 'Stay on top of sales, returns and stock throughout the day.',
        'action' => 'Open point of sale',
        'route' => 'pos',
        'icon' => 'reports',
    ];
} elseif (Auth::hasRole('Employee')) {
    $hero = [
        'eyebrow' => 'Team workspace',
        'description' => 'Find products, serve customers and keep each transaction moving.',
        'action' => 'Open point of sale',
        'route' => 'pos',
        'icon' => 'pos',
    ];
} else {
    $hero = [
        'eyebrow' => 'Customer workspace',
        'description' => 'Explore the catalogue and keep track of your purchases.',
        'action' => 'Browse product catalog',
        'route' => 'catalog',
        'icon' => 'catalog',
    ];
}
?>
<section class="dashboard-hero">
    <div class="dashboard-hero-copy">
        <p class="eyebrow"><?= View::e($hero['eyebrow']) ?></p>
        <h1>Welcome back, <?= View::e($user['full_name']) ?>.</h1>
        <p><?= View::e($hero['description']) ?></p>
        <a class="btn btn-hero" href="<?= View::e(Request::url($hero['route'])) ?>">
            <?= View::e($hero['action']) ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="M5 12h14m-6-6 6 6-6 6"/>
            </svg>
        </a>
    </div>
    <div class="dashboard-hero-art" aria-hidden="true">
        <span class="dashboard-hero-orbit"></span>
        <span class="dashboard-hero-orbit dashboard-hero-orbit-small"></span>
        <div class="dashboard-hero-mark">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                <?= $cardIconPaths[$hero['icon']] ?>
            </svg>
        </div>
        <div class="dashboard-hero-status"><span></span><?= View::e($user['role']) ?> workspace</div>
    </div>
</section>

<section class="card-grid">
    <?php foreach ($moduleCards as $card): ?>
        <?php
        $show = !empty($card['always']);
        if (!$show && !empty($card['roles'])) {
            $show = Auth::hasRole(...$card['roles']);
        }
        if (!$show) continue;
        ?>
        <article class="card">
            <div class="card-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <?= $cardIconPaths[$card['icon']] ?>
                </svg>
            </div>
            <h2><?= View::e($card['title']) ?></h2>
            <p><?= View::e($card['desc']) ?></p>
            <a class="btn btn-ghost btn-sm" href="<?= View::e(Request::url($card['route'])) ?>">
                <?= View::e($card['label']) ?>
            </a>
        </article>
    <?php endforeach; ?>
</section>

<section class="panel">
    <h2>Your active roles</h2>
    <div class="chip-row mt-10">
        <?php foreach ($user['roles'] as $role): ?>
            <span class="role-pill"><?= View::e($role) ?></span>
        <?php endforeach; ?>
    </div>
    <p class="muted mt-10 text-sm">
        Permissions are enforced by both PHP and the SQL Server session context.
        Each request sets a named application context in the database for auditing.
    </p>
</section>
