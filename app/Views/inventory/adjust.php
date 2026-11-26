<?php
use App\Core\Request;
use App\Core\View;
?>
<section class="narrow-page">
    <div class="panel">
        <p class="eyebrow">Stock control</p>
        <h1>Adjust stock</h1>
        <p class="muted">Increases apply immediately. Reductions require password-confirmed approval.</p>

        <?php if ($product !== null): ?>
            <div class="approval-detail wide mt-16">
                <span>Selected product</span>
                <strong><?= View::e($product['ProductName']) ?> <code><?= View::e($product['SKU']) ?></code></strong>
                <span>Current stock on hand</span>
                <strong class="text-xl <?= (int)$product['QuantityOnHand'] <= (int)($product['ReorderLevel'] ?? 999) ? 'text-danger' : 'text-success' ?>">
                    <?= View::e($product['QuantityOnHand']) ?> units
                </strong>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= View::e(Request::url('inventory/adjust/request')) ?>" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">

            <div class="field">
                <label for="product_id">Product</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Select a product…</option>
                    <?php foreach ($products as $row): ?>
                        <option value="<?= View::e($row['ProductID']) ?>"
                            <?= (int)$row['ProductID'] === $productId ? 'selected' : '' ?>>
                            <?= View::e($row['ProductName']) ?> (<?= View::e($row['SKU']) ?>)
                            — <?= View::e($row['QuantityOnHand']) ?> in stock
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="quantity_change">Quantity change</label>
                <input id="quantity_change" name="quantity_change" type="number" required step="1"
                       placeholder="Positive = add, negative = remove (requires approval)">
            </div>

            <div class="field">
                <label for="reason">Reason for adjustment</label>
                <input id="reason" name="reason" type="text" required maxlength="255"
                       placeholder="e.g. Supplier delivery, damaged goods, stocktake correction">
            </div>

            <div class="actions form-footer">
                <button type="submit" class="btn btn-primary">Submit adjustment</button>
                <a class="btn btn-ghost" href="<?= View::e(Request::url('inventory')) ?>">Back to inventory</a>
            </div>
        </form>
    </div>
</section>
