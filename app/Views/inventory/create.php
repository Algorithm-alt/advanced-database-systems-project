<?php
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
?>
<section class="narrow-page">
    <div class="panel">
        <p class="eyebrow">Catalogue management</p>
        <h1>Register product</h1>
        <p class="muted">Creates a new active SKU and initializes its inventory record at 0 units.</p>

        <form method="post" action="<?= View::e(Request::url('inventory/store')) ?>" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">

            <div class="form-grid">
                <div class="field">
                    <label for="sku">SKU <span class="req">*</span></label>
                    <input id="sku" name="sku" type="text" required maxlength="30" placeholder="e.g. SKU-011">
                </div>
                <div class="field">
                    <label for="barcode">Barcode (optional)</label>
                    <input id="barcode" name="barcode" type="text" maxlength="50" placeholder="e.g. 6001234567890">
                </div>
                <div class="field full">
                    <label for="product_name">Product name <span class="req">*</span></label>
                    <input id="product_name" name="product_name" type="text" required maxlength="150"
                           placeholder="e.g. Heavy-Duty Angle Grinder 900W">
                </div>
                <div class="field">
                    <label for="category_id">Category <span class="req">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select category…</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= View::e($category['CategoryID']) ?>">
                                <?= View::e($category['CategoryName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="supplier_id">Supplier <span class="req">*</span></label>
                    <select id="supplier_id" name="supplier_id" required>
                        <option value="">Select supplier…</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= View::e($supplier['SupplierID']) ?>">
                                <?= View::e($supplier['SupplierName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="unit_price">Unit selling price (GH₵) <span class="req">*</span></label>
                    <input id="unit_price" name="unit_price" type="number" min="0" step="0.01" required placeholder="0.00">
                </div>
                <div class="field">
                    <label for="cost_price">Cost price (GH₵) <span class="req">*</span></label>
                    <input id="cost_price" name="cost_price" type="number" min="0" step="0.01" required placeholder="0.00">
                </div>
                <div class="field">
                    <label for="reorder_level">Reorder level <span class="req">*</span></label>
                    <input id="reorder_level" name="reorder_level" type="number" min="0" step="1" value="5" required>
                </div>
            </div>

            <div class="actions form-footer">
                <button type="submit" class="btn btn-primary">Register product</button>
                <a class="btn btn-ghost" href="<?= View::e(Request::url('inventory')) ?>">Cancel</a>
            </div>
        </form>
    </div>
</section>
