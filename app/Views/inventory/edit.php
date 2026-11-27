<?php
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
?>
<section class="narrow-page">
    <div class="panel">
        <p class="eyebrow">Catalogue management</p>
        <h1>Edit product</h1>
        <p class="muted">Update the selected product details.</p>

        <form method="post" action="<?= View::e(Request::url('inventory/update')) ?>" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
            <input type="hidden" name="product_id" value="<?= (int)$product['ProductID'] ?>">

            <div class="form-grid">
                <div class="field">
                    <label for="sku">SKU <span class="req">*</span></label>
                    <input id="sku" name="sku" type="text" required maxlength="30"
                           value="<?= View::e($product['SKU']) ?>">
                </div>
                <div class="field">
                    <label for="barcode">Barcode (optional)</label>
                    <input id="barcode" name="barcode" type="text" maxlength="50"
                           value="<?= View::e($product['Barcode'] ?? '') ?>">
                </div>
                <div class="field full">
                    <label for="product_name">Product name <span class="req">*</span></label>
                    <input id="product_name" name="product_name" type="text" required maxlength="150"
                           value="<?= View::e($product['ProductName']) ?>">
                </div>
                <div class="field">
                    <label for="category_id">Category <span class="req">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int)$category['CategoryID'] ?>"
                                <?= (int)$category['CategoryID'] === (int)$product['CategoryID'] ? 'selected' : '' ?>>
                                <?= View::e($category['CategoryName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="supplier_id">Supplier <span class="req">*</span></label>
                    <select id="supplier_id" name="supplier_id" required>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= (int)$supplier['SupplierID'] ?>"
                                <?= (int)$supplier['SupplierID'] === (int)$product['SupplierID'] ? 'selected' : '' ?>>
                                <?= View::e($supplier['SupplierName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="unit_price">Unit selling price (GH₵) <span class="req">*</span></label>
                    <input id="unit_price" name="unit_price" type="number" min="0" step="0.01" required
                           value="<?= number_format((float)$product['UnitPrice'], 2, '.', '') ?>">
                </div>
                <div class="field">
                    <label for="cost_price">Cost price (GH₵) <span class="req">*</span></label>
                    <input id="cost_price" name="cost_price" type="number" min="0" step="0.01" required
                           value="<?= number_format((float)$product['CostPrice'], 2, '.', '') ?>">
                </div>
                <div class="field">
                    <label for="reorder_level">Reorder level <span class="req">*</span></label>
                    <input id="reorder_level" name="reorder_level" type="number" min="0" step="1" required
                           value="<?= (int)$product['ReorderLevel'] ?>">
                </div>
            </div>

            <div class="actions form-footer">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a class="btn btn-ghost" href="<?= View::e(Request::url('inventory')) ?>">Cancel</a>
            </div>
        </form>
    </div>
</section>
