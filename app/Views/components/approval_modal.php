<?php
use App\Core\Session;
use App\Core\View;
?>
<div class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="approval-title">
    <div class="modal-card">
        <p class="eyebrow">Sensitive action</p>
        <h2 id="approval-title"><?= View::e($approval['title'] ?? 'Confirm this action') ?></h2>
        <p class="muted text-md mb-0"><?= View::e($approval['message'] ?? '') ?></p>

        <?php if (!empty($approval['detail'])): ?>
            <div class="approval-detail mt-14">
                <span>Approval reference</span>
                <code><?= View::e($approval['detail']) ?></code>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= View::e($approval['action'] ?? '') ?>" class="form-stack mt-16">
            <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
            <input type="hidden" name="approval_token" value="<?= View::e($approval['token'] ?? '') ?>">

            <?php if (!empty($approval['show_password'])): ?>
                <div class="field">
                    <label for="approval-password">Your password</label>
                    <input id="approval-password" name="approval_password" type="password"
                           autocomplete="current-password" required placeholder="Enter your current password"
                           data-autofocus>
                </div>
            <?php endif; ?>

            <?php if (!empty($approval['show_phrase'])): ?>
                <div class="field">
                    <label for="approval-phrase">Approval phrase</label>
                    <input id="approval-phrase" name="approval_phrase" type="text"
                           autocomplete="off" required
                           placeholder="<?= View::e($approval['phrase_hint'] ?? 'Enter the approval phrase') ?>">
                </div>
            <?php endif; ?>

            <?php if (!empty($approval['approvers'])): ?>
                <div class="field">
                    <label for="approval-verifier">Second approver (administrator)</label>
                    <select id="approval-verifier" name="approval_verifier" required>
                        <option value="">Select a second administrator…</option>
                        <?php foreach ($approval['approvers'] as $approver): ?>
                            <option value="<?= View::e($approver['UserID']) ?>">
                                <?= View::e($approver['FullName']) ?> (<?= View::e($approver['Username']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">
                    <?= View::e($approval['confirm_label'] ?? 'Confirm') ?>
                </button>
                <?php if (!empty($approval['cancel_action'])): ?>
                    <button type="submit" class="btn btn-ghost"
                            formaction="<?= View::e($approval['cancel_action']) ?>"
                            formnovalidate>Cancel</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
