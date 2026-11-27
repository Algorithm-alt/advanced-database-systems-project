<?php
use App\Core\Request;
use App\Core\View;
?>
<section class="landing-hero" aria-labelledby="landing-title">
    <div class="landing-hero-copy">
        <p class="landing-eyebrow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="M4 19V5m0 14h16"/>
                <path d="m7 15 4-4 3 2 5-6"/>
                <path d="M16 7h3v3"/>
            </svg>
            Retail operations, connected
        </p>
        <h1 id="landing-title">Run your store with <span>confidence.</span></h1>
        <p class="landing-intro">
            Keep inventory, sales, returns and reporting moving together in one secure workspace built for your team.
        </p>
        <div class="landing-actions">
            <a class="btn btn-primary landing-primary" href="<?= View::e(Request::url('login')) ?>">
                Sign in to your workspace
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M5 12h14m-6-6 6 6-6 6"/>
                </svg>
            </a>
            <a class="landing-secondary" href="#capabilities">Explore capabilities</a>
        </div>
        <div class="landing-assurance">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                <path d="m9 12 2 2 4-4"/>
            </svg>
            <span>Role-based access</span>
            <span class="landing-assurance-divider" aria-hidden="true"></span>
            <span>Ghana cedi-ready</span>
        </div>
    </div>

    <div class="landing-showcase" role="img" aria-label="Illustration of a connected operations dashboard for stock, sales and reporting">
        <div class="landing-showcase-orbit landing-showcase-orbit-one"></div>
        <div class="landing-showcase-orbit landing-showcase-orbit-two"></div>
        <div class="showcase-window">
            <div class="showcase-window-top">
                <span class="showcase-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                        <path d="m3 7 9-4 9 4v10l-9 4-9-4V7Z"/>
                        <path d="m3 7 9 4 9-4M12 11v10"/>
                    </svg>
                </span>
                <span class="showcase-window-title"><strong>Operations overview</strong><small>One clear view of your store</small></span>
                <span class="showcase-live"><i></i> Connected</span>
            </div>
            <div class="showcase-summary">
                <div>
                    <span class="showcase-label">Today's activity</span>
                    <strong>Everything in sync</strong>
                </div>
                <span class="showcase-summary-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                        <path d="M4 19V5m0 14h16"/>
                        <path d="m7 15 4-4 3 2 5-6"/>
                    </svg>
                </span>
            </div>
            <div class="showcase-workflows">
                <div class="showcase-workflow">
                    <span class="showcase-workflow-icon showcase-inventory" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="m3 7 9-4 9 4v10l-9 4-9-4V7Z"/>
                            <path d="m3 7 9 4 9-4M12 11v10"/>
                        </svg>
                    </span>
                    <span><strong>Inventory</strong><small>Stock and reorder levels</small></span>
                    <svg class="showcase-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="m5 12 4 4L19 6"/>
                    </svg>
                </div>
                <div class="showcase-workflow">
                    <span class="showcase-workflow-icon showcase-sales" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="M3 3h2l2.4 11.4a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L22 7H6"/>
                            <circle cx="10" cy="20" r="1"/>
                            <circle cx="18" cy="20" r="1"/>
                        </svg>
                    </span>
                    <span><strong>Sales &amp; receipts</strong><small>Checkout and payment records</small></span>
                    <svg class="showcase-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="m5 12 4 4L19 6"/>
                    </svg>
                </div>
                <div class="showcase-workflow">
                    <span class="showcase-workflow-icon showcase-reports" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="M3 3v18h18"/>
                            <path d="M18 17V9M13 17V5M8 17v-3"/>
                        </svg>
                    </span>
                    <span><strong>Reports</strong><small>Performance and audit history</small></span>
                    <svg class="showcase-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="m5 12 4 4L19 6"/>
                    </svg>
                </div>
            </div>
            <div class="showcase-footer">
                <span><i></i> Secure team workspace</span>
                <span>GH₵ supported</span>
            </div>
        </div>
        <div class="showcase-float-card">
            <span class="showcase-float-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
            </span>
            <span><strong>Accountable by design</strong><small>Access and activity stay traceable</small></span>
        </div>
    </div>
</section>

<section class="landing-capabilities" id="capabilities" aria-labelledby="capabilities-title">
    <div class="landing-section-heading" data-reveal>
        <p class="landing-section-eyebrow">Built around your day</p>
        <h2 id="capabilities-title">The essentials, working together.</h2>
        <p>Spend less time switching between tasks and more time keeping your business moving.</p>
    </div>
    <div class="landing-feature-grid">
        <article class="landing-feature" data-reveal>
            <span class="landing-feature-icon feature-stock" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <path d="m3 7 9-4 9 4v10l-9 4-9-4V7Z"/>
                    <path d="m3 7 9 4 9-4M12 11v10"/>
                </svg>
            </span>
            <h3>Know your stock</h3>
            <p>Track quantities, review stock movements and spot items that need reordering.</p>
        </article>
        <article class="landing-feature" data-reveal>
            <span class="landing-feature-icon feature-checkout" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <path d="M3 3h2l2.4 11.4a2 2 0 0 0 2 1.6h8.8a2 2 0 0 0 2-1.6L22 7H6"/>
                    <circle cx="10" cy="20" r="1"/>
                    <circle cx="18" cy="20" r="1"/>
                </svg>
            </span>
            <h3>Keep sales moving</h3>
            <p>Record transactions, payments and receipts from a streamlined point-of-sale workflow.</p>
        </article>
        <article class="landing-feature" data-reveal>
            <span class="landing-feature-icon feature-team" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <circle cx="9" cy="8" r="4"/>
                    <path d="M3 21v-2a6 6 0 0 1 12 0v2"/>
                    <path d="M16 4.5a4 4 0 0 1 0 7.5M21 21v-2a6 6 0 0 0-4-5.7"/>
                </svg>
            </span>
            <h3>Support your team</h3>
            <p>Give staff the right tools with role-based access and clear approval workflows.</p>
        </article>
        <article class="landing-feature" data-reveal>
            <span class="landing-feature-icon feature-insights" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <path d="M3 3v18h18"/>
                    <path d="M18 17V9M13 17V5M8 17v-3"/>
                </svg>
            </span>
            <h3>See the full picture</h3>
            <p>Use clear reports and an audit trail to understand activity across your operations.</p>
        </article>
    </div>
</section>

<section class="landing-cta" aria-labelledby="landing-cta-title" data-reveal>
    <div>
        <p class="landing-section-eyebrow">Ready when you are</p>
        <h2 id="landing-cta-title">Bring your daily operations into focus.</h2>
    </div>
    <a class="btn btn-primary landing-primary" href="<?= View::e(Request::url('login')) ?>">
        Open your workspace
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
            <path d="M5 12h14m-6-6 6 6-6 6"/>
        </svg>
    </a>
</section>
