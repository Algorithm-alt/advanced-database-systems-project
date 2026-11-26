<?php
use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Stock control</p>
        <h1>Inventory</h1>
        <p class="muted">Current catalogue and stock position across all active products.</p>
    </div>
    <div class="actions">
        <a class="btn btn-ghost" href="<?= View::e(Request::url('inventory/transactions')) ?>">Stock movements</a>
        <?php if (Auth::hasRole('Administrator', 'Manager')): ?>
            <a class="btn btn-ghost" href="<?= View::e(Request::url('inventory/create')) ?>">+ Register product</a>
            <a class="btn btn-primary" href="<?= View::e(Request::url('inventory/adjust')) ?>">Adjust stock</a>
        <?php endif; ?>
    </div>
</section>

<form method="get" action="<?= View::e(Request::url('inventory')) ?>" class="toolbar">
    <input type="hidden" name="route" value="inventory">
    <div class="field">
        <label for="search">Search product or SKU</label>
        <input id="search" name="search" type="text" value="<?= View::e($search) ?>"
               placeholder="e.g. drill, SKU-001…">
    </div>
    <div class="field">
        <label for="low_stock">Stock filter</label>
        <select id="low_stock" name="low_stock">
            <option value="0" <?= $lowStock ? '' : 'selected' ?>>All products</option>
            <option value="1" <?= $lowStock ? 'selected' : '' ?>>Low stock only</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Apply filter</button>
    <?php if ($search !== '' || $lowStock): ?>
        <a class="btn btn-ghost" href="<?= View::e(Request::url('inventory')) ?>">Clear</a>
    <?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Category</th>
            <th class="number">On hand</th>
            <th class="number">Reorder at</th>
            <th>Status</th>
            <th class="number">Unit price</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($rows === []): ?>
            <tr>
                <td colspan="7" class="table-empty">
                    <?= $search !== '' || $lowStock ? 'No products match this filter.' : 'No products found.' ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <?php $isLow = (int)$row['IsLowStock'] === 1; ?>
                <tr>
                    <td>
                        <strong><?= View::e($row['ProductName']) ?></strong>
                        <?php if (!empty($row['SupplierName'])): ?>
                            <br><span class="muted text-xs"><?= View::e($row['SupplierName']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><code><?= View::e($row['SKU']) ?></code></td>
                    <td><?= View::e($row['CategoryName']) ?></td>
                    <td class="number">
                        <strong class="<?= $isLow ? 'text-danger' : '' ?>"><?= View::e($row['QuantityOnHand']) ?></strong>
                    </td>
                    <td class="number"><?= View::e($row['ReorderLevel']) ?></td>
                    <td>
                        <span class="badge <?= $isLow ? 'badge-low' : 'badge-ok' ?>">
                            <?= $isLow ? 'Low stock' : 'Healthy' ?>
                        </span>
                    </td>
                    <td class="number"><strong>GH₵<?= number_format((float)$row['UnitPrice'], 2) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if ($rows !== []): ?>
    <p class="muted table-count">
        <?= count($rows) ?> product<?= count($rows) !== 1 ? 's' : '' ?> shown
    </p>
<?php endif; ?>
