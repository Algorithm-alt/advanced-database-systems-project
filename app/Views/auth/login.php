<?php
use App\Core\Config;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
?>
<section class="auth-grid">
    <div class="auth-card">
        <p class="eyebrow">Secure access</p>
        <h1>Sign in to your workspace</h1>
        <p class="muted">Use your staff credentials to continue.</p>

        <?php if ($loggedOut !== null): ?>
            <div class="notice notice-success" role="status">
                You have been signed out successfully.
            </div>
        <?php endif; ?>

        <form method="post" action="<?= View::e(Request::url('login')) ?>" class="form-stack" novalidate>
            <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">

            <div class="field">
                <label for="login">Username or email</label>
                <input id="login" name="login" type="text" autocomplete="username" required
                       value="<?= View::e($oldLogin ?? '') ?>" placeholder="e.g. admin_060"
                       data-autofocus>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                       placeholder="Enter your password">
            </div>

            <button type="submit" class="btn btn-primary mt-4">Sign in</button>
        </form>

        <?php if (Config::get('app.env') === 'local'): ?>
            <div class="demo-note">
                <strong>Local demo access</strong>
                <p>Accounts: admin_060, manager_060, cashier_060, attendant_060, customer_060.</p>
                <p>Password: <code>Demo@060</code></p>
            </div>
        <?php endif; ?>
    </div>

    <aside class="auth-aside">
        <p class="eyebrow eyebrow-light">Why choose us</p>
        <h2>Built for the counter and the back office</h2>
        <ul class="feature-list">
            <li>Role-based access with database-enforced context</li>
            <li>Approval workflow for sensitive operations</li>
            <li>Tamper-proof audit trail for every critical action</li>
            <li>Inventory, sales, returns and reporting — all in one place</li>
        </ul>
    </aside>
</section>
