<?php
use App\Core\Request;
use App\Core\View;
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Storefront</p>
        <h1>Product catalog</h1>
        <p class="muted">Browse active hardware products, prices, and real-time availability.</p>
    </div>
</section>

<form method="get" action="<?= View::e(Request::url('catalog')) ?>" class="toolbar">
    <input type="hidden" name="route" value="catalog">
    <div class="field toolbar-grow">
        <label for="search">Search product or SKU</label>
        <input id="search" name="search" type="text" value="<?= View::e($search) ?>"
               placeholder="e.g. drill, hammer, SKU-001…">
    </div>
    <div class="field">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
            <option value="0">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= View::e($category['CategoryID']) ?>"
                    <?= (int)$category['CategoryID'] === $categoryId ? 'selected' : '' ?>>
                    <?= View::e($category['CategoryName']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <?php if ($search !== '' || $categoryId > 0): ?>
        <a class="btn btn-ghost" href="<?= View::e(Request::url('catalog')) ?>">Clear</a>
    <?php endif; ?>
</form>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Category</th>
            <th class="number">Unit price</th>
            <th>Availability</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($rows === []): ?>
            <tr>
                <td colspan="5" class="table-empty">
                    No products match this filter.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <?php $qty = (int)($row['QuantityOnHand'] ?? 0); ?>
                <tr>
                    <td><strong><?= View::e($row['ProductName']) ?></strong></td>
                    <td><code><?= View::e($row['SKU']) ?></code></td>
                    <td><?= View::e($row['CategoryName']) ?></td>
                    <td class="number"><strong>GH₵<?= number_format((float)$row['UnitPrice'], 2) ?></strong></td>
                    <td>
                        <?php if ($qty > 0): ?>
                            <span class="badge badge-ok">In stock (<?= View::e($qty) ?>)</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Out of stock</span>
                        <?php endif; ?>
                    </td>
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
