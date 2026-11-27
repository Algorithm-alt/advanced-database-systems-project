<?php
use App\Core\Request;
use App\Core\View;

$actionColors = [
    'LOGIN_SUCCESS' => 'badge-ok',
    'LOGIN_FAILURE' => 'badge-danger',
    'LOGOUT'        => 'badge-info',
    'SALE'          => 'badge-ok',
    'RETURN'        => 'badge-warn',
    'ADJUSTMENT'    => 'badge-info',
    'ROLE_CHANGE'   => 'badge-warn',
];
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Security</p>
        <h1>Audit trail</h1>
        <p class="muted">Administrative view of authentication, approvals and business actions.</p>
    </div>
    <div class="actions">
        <?php $exportFilename = 'audit-trail.csv'; require APP_ROOT . '/app/Views/components/export_actions.php'; ?>
        <a class="btn btn-ghost" href="<?= View::e(Request::url('reports')) ?>">Sales report</a>
    </div>
</section>

<form method="get" action="<?= View::e(Request::url('reports/audit')) ?>" class="toolbar">
    <input type="hidden" name="route" value="reports/audit">
    <div class="field">
        <label for="from_date">From date</label>
        <input id="from_date" name="from_date" type="date" value="<?= View::e($from) ?>">
    </div>
    <div class="field">
        <label for="to_date">To date</label>
        <input id="to_date" name="to_date" type="date" value="<?= View::e($to) ?>">
    </div>
    <div class="field">
        <label for="user_id">User</label>
        <select id="user_id" name="user_id">
            <option value="0">All users</option>
            <?php foreach ($users as $user): ?>
                <option value="<?= View::e($user['UserID']) ?>"
                    <?= (int)$user['UserID'] === $userId ? 'selected' : '' ?>>
                    <?= View::e($user['FullName']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Apply</button>
    <?php if ($from !== '' || $to !== '' || $userId > 0): ?>
        <a class="btn btn-ghost" href="<?= View::e(Request::url('reports/audit')) ?>">Clear</a>
    <?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>When</th>
            <th>User</th>
            <th>Role</th>
            <th>Action</th>
            <th>Entity</th>
            <th>Details</th>
            <th>Verification</th>
            <th>Outcome</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($rows === []): ?>
            <tr>
                <td colspan="8" class="table-empty">
                    No audit records match this filter.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $action = (string)($row['Action'] ?? '');
                $badgeClass = 'badge-info';
                foreach ($actionColors as $key => $cls) {
                    if (str_contains($action, $key)) {
                        $badgeClass = $cls;
                        break;
                    }
                }
                ?>
                <tr>
                    <td><span class="muted text-xs nowrap"><?= View::e($row['OccurredAt']) ?></span></td>
                    <td><strong><?= View::e($row['Username'] ?? 'System') ?></strong></td>
                    <td><span class="role-pill text-xs"><?= View::e($row['UserRole'] ?? '') ?></span></td>
                    <td><span class="badge <?= $badgeClass ?> text-xs"><?= View::e($action) ?></span></td>
                    <td>
                        <span class="muted text-xs">
                            <?= View::e($row['EntityType'] ?? '') ?>
                            <?php if (!empty($row['EntityID'])): ?>
                                <code><?= View::e($row['EntityID']) ?></code>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td class="truncate-cell" title="<?= View::e($row['Details'] ?? '') ?>">
                        <?= View::e($row['Details'] ?? '') ?>
                    </td>
                    <td>
                        <span class="muted text-xs">
                            <?= View::e($row['ConfirmationStatus']) ?><?= !empty($row['AdditionalVerification']) ? ' / ' . View::e($row['AdditionalVerification']) : '' ?>
                        </span>
                    </td>
                    <td><?= View::e($row['Outcome']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if ($rows !== []): ?>
    <p class="muted table-count">
        <?= count($rows) ?> event<?= count($rows) !== 1 ? 's' : '' ?> shown
    </p>
<?php endif; ?>
