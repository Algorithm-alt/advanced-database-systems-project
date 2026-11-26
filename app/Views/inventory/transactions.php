<?php
use App\Core\Request;
use App\Core\View;

$typeColors = [
    'SALE'          => 'badge-danger',
    'RETURN'        => 'badge-info',
    'ADJUSTMENT_IN' => 'badge-ok',
    'ADJUSTMENT_OUT'=> 'badge-low',
];
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Audit trail</p>
        <h1>Stock movements</h1>
        <p class="muted">Every sale, return and manual adjustment is recorded here.</p>
    </div>
    <a class="btn btn-ghost" href="<?= View::e(Request::url('inventory')) ?>">Back to inventory</a>
</section>

<form method="get" action="<?= View::e(Request::url('inventory/transactions')) ?>" class="toolbar">
    <input type="hidden" name="route" value="inventory/transactions">
    <div class="field">
        <label for="product_id">Product</label>
        <select id="product_id" name="product_id">
            <option value="0">All products</option>
            <?php foreach ($products as $row): ?>
                <option value="<?= View::e($row['ProductID']) ?>"
                    <?= (int)$row['ProductID'] === $productId ? 'selected' : '' ?>>
                    <?= View::e($row['ProductName']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label for="from_date">From</label>
        <input id="from_date" name="from_date" type="date" value="<?= View::e($from) ?>">
    </div>
    <div class="field">
        <label for="to_date">To</label>
        <input id="to_date" name="to_date" type="date" value="<?= View::e($to) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Apply</button>
    <?php if ($productId > 0 || $from !== '' || $to !== ''): ?>
        <a class="btn btn-ghost" href="<?= View::e(Request::url('inventory/transactions')) ?>">Clear</a>
    <?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>When</th>
            <th>Product</th>
            <th>Type</th>
            <th class="number">Change</th>
            <th class="number">Before</th>
            <th class="number">After</th>
            <th>Reference</th>
            <th>Performed by</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($rows === []): ?>
            <tr>
                <td colspan="8" class="table-empty">
                    No stock movements match this filter.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <?php
                $type = (string)($row['TransactionType'] ?? '');
                $badgeClass = $typeColors[$type] ?? 'badge-info';
                $change = (int)$row['QuantityChange'];
                ?>
                <tr>
                    <td><span class="muted text-xs nowrap"><?= View::e($row['TransactionDate']) ?></span></td>
                    <td><strong><?= View::e($row['ProductName']) ?></strong></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= View::e($type) ?></span></td>
                    <td class="number">
                        <strong class="<?= $change >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= $change >= 0 ? '+' : '' ?><?= View::e($row['QuantityChange']) ?>
                        </strong>
                    </td>
                    <td class="number"><?= View::e($row['QuantityBefore']) ?></td>
                    <td class="number"><strong><?= View::e($row['QuantityAfter']) ?></strong></td>
                    <td>
                        <span class="muted text-xs">
                            <?= View::e($row['ReferenceType'] ?? '') ?>
                            <?php if (!empty($row['ReferenceID'])): ?>
                                <code><?= View::e($row['ReferenceID']) ?></code>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td><?= View::e($row['PerformedBy'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if ($rows !== []): ?>
    <p class="muted table-count">
        <?= count($rows) ?> movement<?= count($rows) !== 1 ? 's' : '' ?> shown
    </p>
<?php endif; ?>
