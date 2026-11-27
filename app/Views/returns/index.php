<?php
use App\Core\Request;
use App\Core\View;
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">After-sales</p>
        <h1>Returns</h1>
        <p class="muted">Approved returns and refund history.</p>
    </div>
    <div class="actions">
        <?php $exportFilename = 'returns.csv'; require APP_ROOT . '/app/Views/components/export_actions.php'; ?>
        <a class="btn btn-primary" href="<?= View::e(Request::url('returns/create')) ?>">Process return</a>
    </div>
</section>

<form method="get" action="<?= View::e(Request::url('returns')) ?>" class="toolbar">
    <input type="hidden" name="route" value="returns">
    <div class="field">
        <label for="from_date">From date</label>
        <input id="from_date" name="from_date" type="date" value="<?= View::e($from) ?>">
    </div>
    <div class="field">
        <label for="to_date">To date</label>
        <input id="to_date" name="to_date" type="date" value="<?= View::e($to) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Apply</button>
    <?php if ($from !== '' || $to !== ''): ?>
        <a class="btn btn-ghost" href="<?= View::e(Request::url('returns')) ?>">Clear</a>
    <?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Return #</th>
            <th>Date</th>
            <th>Original sale</th>
            <th>Customer</th>
            <th>Reason</th>
            <th class="number">Refund amount</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($rows === []): ?>
            <tr>
                <td colspan="7" class="table-empty">
                    <?= ($from !== '' || $to !== '') ? 'No returns match this filter.' : 'No returns found.' ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><strong><?= View::e($row['ReturnNumber']) ?></strong></td>
                    <td><span class="muted text-xs nowrap"><?= View::e($row['ReturnDate']) ?></span></td>
                    <td><?= View::e($row['SaleNumber']) ?></td>
                    <td><?= View::e($row['CustomerName'] ?? 'Walk-in') ?></td>
                    <td class="truncate-cell" title="<?= View::e($row['Reason']) ?>">
                        <?= View::e($row['Reason']) ?>
                    </td>
                    <td class="number"><strong>GH₵<?= number_format((float)$row['RefundAmount'], 2) ?></strong></td>
                    <td><span class="badge badge-ok"><?= View::e($row['Status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if ($rows !== []): ?>
    <p class="muted table-count">
        <?= count($rows) ?> return<?= count($rows) !== 1 ? 's' : '' ?> shown
    </p>
<?php endif; ?>
