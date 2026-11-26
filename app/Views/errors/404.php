<?php
use App\Core\Request;
use App\Core\View;
?>
<section class="narrow-page">
    <div class="panel center empty-state-panel">
        <div class="error-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                <circle cx="11" cy="11" r="7"/>
                <path d="m20 20-4-4"/>
            </svg>
        </div>
        <p class="eyebrow">Error 404</p>
        <h1>Page not found</h1>
        <p class="muted error-desc">
            The page you requested doesn't exist or may have been moved.
        </p>
        <div class="actions actions-center">
            <a class="btn btn-primary" href="<?= View::e(Request::url('dashboard')) ?>">Go to dashboard</a>
            <button type="button" class="btn btn-ghost" data-back>Go back</button>
        </div>
    </div>
</section>
