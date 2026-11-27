<?php
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Administration</p>
        <h1>Users &amp; roles</h1>
        <p class="muted">Role changes are protected by a two-factor critical-action approval flow.</p>
    </div>
    <div class="actions">
        <?php $exportFilename = 'users-and-roles.csv'; require APP_ROOT . '/app/Views/components/export_actions.php'; ?>
        <span class="badge badge-info"><?= count($users) ?> user<?= count($users) !== 1 ? 's' : '' ?></span>
    </div>
</section>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Current roles</th>
            <th>Status</th>
            <th data-export-ignore>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><strong><?= View::e($user['FullName']) ?></strong></td>
                <td><code><?= View::e($user['Username']) ?></code></td>
                <td>
                    <span class="muted text-sm"><?= View::e($user['Email']) ?></span>
                </td>
                <td>
                    <div class="chip-row">
                        <?php if (trim((string)$user['Roles']) === ''): ?>
                            <span class="tag">No role</span>
                        <?php else: ?>
                            <?php foreach (explode(', ', (string)$user['Roles']) as $role): ?>
                                <span class="role-pill"><?= View::e(trim($role)) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <?php if ((bool)$user['IsLocked']): ?>
                        <span class="badge badge-danger">Locked</span>
                    <?php elseif (!(bool)$user['IsActive']): ?>
                        <span class="badge badge-warn">Inactive</span>
                    <?php else: ?>
                        <span class="badge badge-ok">Active</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="actions">
                        <form method="post" action="<?= View::e(Request::url('admin/role/request')) ?>"
                              class="inline-form">
                            <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
                            <input type="hidden" name="user_id" value="<?= View::e($user['UserID']) ?>">
                            <select name="role_id"
                                    aria-label="New role for <?= View::e($user['FullName']) ?>"
                                    required>
                                <option value="">Select role…</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= View::e($role['RoleID']) ?>"><?= View::e($role['RoleName']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-ghost btn-sm">Request</button>
                        </form>
                        <a class="btn btn-ghost btn-sm"
                           href="<?= View::e(Request::url('admin/users/edit&user_id=' . (int)$user['UserID'])) ?>">Edit</a>
                        <?php if ((bool)$user['IsActive']): ?>
                            <form method="post" action="<?= View::e(Request::url('admin/users/deactivate')) ?>"
                                  data-confirm-deactivate="Deactivate this user?">
                                <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
                                <input type="hidden" name="user_id" value="<?= (int)$user['UserID'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Deactivate</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="<?= View::e(Request::url('admin/users/activate')) ?>">
                                <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
                                <input type="hidden" name="user_id" value="<?= (int)$user['UserID'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm">Activate</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= View::e(Request::url('admin/users/permanently-delete')) ?>"
                              data-confirm-delete="Permanently delete this user? This cannot be undone.">
                            <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
                            <input type="hidden" name="user_id" value="<?= (int)$user['UserID'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete permanently</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
