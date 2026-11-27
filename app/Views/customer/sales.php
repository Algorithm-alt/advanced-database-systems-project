<?php
use App\Core\Request;
use App\Core\View;
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Customer portal</p>
        <h1>My purchases</h1>
        <p class="muted">
            <?php if ($profile !== null): ?>
                Purchase history for <strong><?= View::e($profile['FullName']) ?></strong>
                (<code><?= View::e($profile['CustomerCode']) ?></code>).
            <?php else: ?>
                Your purchase history, enforced by SQL Server session context.
            <?php endif; ?>
        </p>
    </div>
    <div class="actions">
        <?php $exportFilename = 'my-purchases.csv'; require APP_ROOT . '/app/Views/components/export_actions.php'; ?>
        <a class="btn btn-ghost" href="<?= View::e(Request::url('catalog')) ?>">Browse catalog</a>
    </div>
</section>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Sale #</th>
            <th>Date</th>
            <th class="number">Items</th>
            <th class="number">Total</th>
            <th class="number">Amount paid</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($rows === []): ?>
            <tr>
                <td colspan="6" class="table-empty">
                    No purchases found for your account yet.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><strong><?= View::e($row['SaleNumber']) ?></strong></td>
                    <td><span class="muted text-xs nowrap"><?= View::e($row['SaleDate']) ?></span></td>
                    <td class="number"><?= View::e($row['ItemCount']) ?></td>
                    <td class="number"><strong>GH₵<?= number_format((float)$row['TotalAmount'], 2) ?></strong></td>
                    <td class="number">GH₵<?= number_format((float)$row['AmountPaid'], 2) ?></td>
                    <td><span class="badge badge-ok"><?= View::e($row['Status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if ($rows !== []): ?>
    <p class="muted table-count">
        <?= count($rows) ?> order<?= count($rows) !== 1 ? 's' : '' ?> shown
    </p>
<?php endif; ?>
