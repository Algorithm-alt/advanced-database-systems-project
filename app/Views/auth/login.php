<?php
use App\Core\Config;
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
?>
<section class="auth-grid">
    <div class="auth-card">
        <a class="auth-back-link" href="<?= View::e(Request::url('home')) ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="M19 12H5m7 7-7-7 7-7"/>
            </svg>
            <span>Back to home</span>
        </a>
        <div class="auth-kicker">
            <span class="auth-kicker-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <rect x="4" y="10" width="16" height="11" rx="2"/>
                    <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/>
                </svg>
            </span>
            <span>Secure team access</span>
        </div>
        <h1>Welcome back<span class="auth-title-dot">.</span></h1>
        <p class="auth-lead">Sign in to continue to your retail operations workspace.</p>

        <?php if ($loggedOut): ?>
            <div class="notice notice-success" role="status">
                You have been signed out successfully.
            </div>
        <?php endif; ?>

        <form method="post" action="<?= View::e(Request::url('login')) ?>" class="form-stack" data-auth-form>
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

            <button type="submit" class="btn btn-primary auth-submit mt-4">
                <span data-submit-label>Sign in securely</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M5 12h14m-6-6 6 6-6 6"/>
                </svg>
            </button>
        </form>

        <div class="auth-signup-gateway">
            <span>New customer?</span>
            <button type="button" class="btn btn-ghost btn-sm"
                    data-signup-toggle aria-controls="customer-signup" aria-expanded="false">
                Create account
            </button>
        </div>

        <form id="customer-signup" method="post" action="<?= View::e(Request::url('signup')) ?>"
              class="form-stack auth-signup-form" data-auth-form data-signup-form hidden>
            <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
            <h2>Create customer account</h2>
            <div class="form-grid">
                <div class="field full">
                    <label for="signup-full-name">Full name <span class="req">*</span></label>
                    <input id="signup-full-name" name="full_name" type="text" maxlength="100" required
                           autocomplete="name" placeholder="Your full name">
                </div>
                <div class="field">
                    <label for="signup-username">Username <span class="req">*</span></label>
                    <input id="signup-username" name="username" type="text" maxlength="50" required
                           autocomplete="username" placeholder="Choose a username">
                </div>
                <div class="field">
                    <label for="signup-email">Email <span class="req">*</span></label>
                    <input id="signup-email" name="email" type="email" maxlength="256" required
                           autocomplete="email" placeholder="you@example.com">
                </div>
                <div class="field full">
                    <label for="signup-phone">Phone</label>
                    <input id="signup-phone" name="phone" type="tel" maxlength="30"
                           autocomplete="tel" placeholder="Phone number">
                </div>
                <div class="field">
                    <label for="signup-password">Password <span class="req">*</span></label>
                    <input id="signup-password" name="password" type="password" minlength="8" required
                           autocomplete="new-password" placeholder="At least 8 characters">
                </div>
                <div class="field">
                    <label for="signup-confirm-password">Confirm password <span class="req">*</span></label>
                    <input id="signup-confirm-password" name="confirm_password" type="password" minlength="8" required
                           autocomplete="new-password" placeholder="Repeat your password">
                </div>
            </div>
            <button type="submit" class="btn btn-primary auth-submit">
                <span data-submit-label>Create customer account</span>
            </button>
        </form>

        <div class="auth-assurance">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
            <span>Your account is protected by role-based access and audited activity.</span>
        </div>

        <?php if (Config::get('app.env') === 'local'): ?>
            <div class="demo-note">
                <strong>Local demo access</strong>
                <p>Accounts: admin_060, manager_060, cashier_060, attendant_060, customer_060.</p>
                <p>Password: <code>Demo@060</code></p>
            </div>
        <?php endif; ?>
    </div>

    <aside class="auth-aside">
        <div class="auth-aside-brand">
            <img src="<?= View::e(Request::asset('images/site-logo.svg')) ?>" alt="" width="48" height="48">
            <span>ALGORITHM HARDWARE PLUG<small>RETAIL OPERATIONS</small></span>
        </div>
        <figure class="auth-aside-photo">
            <img src="<?= View::e(Request::asset('images/hardware-store.jpg')) ?>"
                 alt="Real hardware store with tools and shelves ready for retail operations"
                 width="1200" height="800" loading="eager" decoding="async">
            <figcaption>Hardware store operations · Photo: vijesh vijayan / Pexels</figcaption>
        </figure>
        <p class="eyebrow eyebrow-light">One connected workspace</p>
        <h2>Keep every part of your business moving.</h2>
        <p class="auth-aside-copy">A clear, secure home for the people, products and daily activity behind your store.</p>
        <ul class="feature-list">
            <li>Role-based access with database-enforced context</li>
            <li>Approval workflow for sensitive operations</li>
            <li>Tamper-proof audit trail for every critical action</li>
            <li>Inventory, sales, returns and reporting: all in one place</li>
        </ul>
        <div class="auth-aside-footer">
            <span class="auth-aside-footer-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
            </span>
            <span><strong>Secure by design</strong><small>Built for accountable retail operations.</small></span>
        </div>
    </aside>
</section>
