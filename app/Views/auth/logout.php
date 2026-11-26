<?php
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
?>
<section class="narrow-page">
    <div class="panel center empty-state-panel">
        <div class="signout-icon" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
        </div>
        <h1>Sign out</h1>
        <p class="muted error-desc">
            You are signed in as <strong><?= View::e($user['full_name'] ?? 'your account') ?></strong>.
            Are you sure you want to end your current session?
        </p>

        <?php if ($logoutToken === ''): ?>
            <form method="post" action="<?= View::e(Request::url('logout/request')) ?>" class="actions actions-center">
                <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
                <button type="submit" class="btn btn-danger">Sign out</button>
                <a class="btn btn-ghost" href="<?= View::e(Request::url('dashboard')) ?>">Stay signed in</a>
            </form>
        <?php else: ?>
            <form method="post" action="<?= View::e(Request::url('logout/confirm')) ?>" class="actions actions-center">
                <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
                <input type="hidden" name="approval_token" value="<?= View::e($logoutToken) ?>">
                <button type="submit" class="btn btn-danger">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Sign out</span>
                </button>
                <button type="submit" class="btn btn-ghost" formaction="<?= View::e(Request::url('logout/cancel')) ?>">
                    Cancel
                </button>
            </form>
        <?php endif; ?>
    </div>
</section>
