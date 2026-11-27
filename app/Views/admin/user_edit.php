<?php
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
?>
<section class="narrow-page">
    <div class="panel">
        <p class="eyebrow">Users &amp; roles</p>
        <h1>Edit user</h1>
        <p class="muted text-sm">Role changes must still be made through the protected role-request workflow.</p>

        <form method="post" action="<?= View::e(Request::url('admin/users/update')) ?>" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
            <input type="hidden" name="user_id" value="<?= (int)$user['UserID'] ?>">

            <div class="form-grid">
                <div class="field">
                    <label for="full_name">Full name <span class="req">*</span></label>
                    <input id="full_name" name="full_name" type="text" maxlength="100" required
                           value="<?= View::e($user['FullName']) ?>">
                </div>
                <div class="field">
                    <label for="username">Username <span class="req">*</span></label>
                    <input id="username" name="username" type="text" maxlength="50" required
                           pattern="[A-Za-z0-9._-]{3,50}" value="<?= View::e($user['Username']) ?>">
                </div>
                <div class="field">
                    <label for="email">Email <span class="req">*</span></label>
                    <input id="email" name="email" type="email" maxlength="256" required
                           value="<?= View::e($user['Email']) ?>">
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input id="phone" name="phone" type="tel" maxlength="30"
                           value="<?= View::e($user['Phone'] ?? '') ?>">
                </div>
            </div>

            <div class="actions form-footer">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a class="btn btn-ghost" href="<?= View::e(Request::url('admin/users')) ?>">Cancel</a>
            </div>
        </form>
    </div>
</section>
