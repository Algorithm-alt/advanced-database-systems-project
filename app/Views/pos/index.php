<?php
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Point of sale</p>
        <h1>Create a sale</h1>
        <p class="muted">Prices are taken from the catalogue. Discounts are applied per line item.</p>
    </div>
</section>

<form method="post" action="<?= View::e(Request::url('pos/store')) ?>" class="panel" data-sale-form data-tax-rate="0.08">
    <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">

    <div class="form-grid mb-24">
        <div class="field">
            <label for="customer_id">Customer (optional)</label>
            <select id="customer_id" name="customer_id">
                <option value="0">Walk-in customer</option>
                <?php foreach ($customers as $customer): ?>
                    <option value="<?= View::e($customer['CustomerID']) ?>"><?= View::e($customer['FullName']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="employee_id">Served by <span class="req">*</span></label>
            <select id="employee_id" name="employee_id" required>
                <option value="">Select employee…</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= View::e($employee['EmployeeID']) ?>"><?= View::e($employee['FullName']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="payment_method">Payment method</label>
            <select id="payment_method" name="payment_method">
                <option value="CASH">Cash</option>
                <option value="CARD">Card</option>
                <option value="BANK_TRANSFER">Bank transfer</option>
                <option value="OTHER">Other</option>
            </select>
        </div>
        <div class="field">
            <label for="amount_paid">Amount paid (GH₵)</label>
            <div class="input-with-btn">
                <input id="amount_paid" name="amount_paid" type="number" min="0" step="0.01" value="0" required data-amount-paid>
                <button type="button" class="btn btn-ghost btn-sm" data-pay-exact title="Fill exact total">Exact</button>
            </div>
        </div>
    </div>

    <h2 class="section-subtitle">
        Sale items
    </h2>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th class="col-product">Product</th>
                <th class="col-qty">Quantity</th>
                <th class="col-discount">Discount (GH₵)</th>
                <th class="number col-total">Line total</th>
                <th class="col-action"></th>
            </tr>
            </thead>
            <tbody id="sale-lines">
            <?php $index = 0; require APP_ROOT . '/app/Views/pos/_line.php'; ?>
            </tbody>
        </table>
    </div>

    <div class="actions mt-14">
        <button type="button" class="btn btn-ghost btn-sm" data-add-line>+ Add item</button>
    </div>

    <div class="totals">
        <div class="total-row"><span>Subtotal</span><strong id="subtotal">GH₵0.00</strong></div>
        <div class="total-row"><span>Tax (8%)</span><strong id="tax">GH₵0.00</strong></div>
        <div class="total-row grand"><span>Total</span><strong id="total">GH₵0.00</strong></div>
        <div class="total-row" id="change-row"><span id="change-label">Change due</span><strong id="change-due">GH₵0.00</strong></div>
    </div>

    <div class="actions form-footer">
        <button type="submit" class="btn btn-primary">Complete sale</button>
        <a class="btn btn-ghost" href="<?= View::e(Request::url('dashboard')) ?>">Cancel</a>
    </div>
</form>

<template id="sale-line-template">
    <?php $index = '__INDEX__'; require APP_ROOT . '/app/Views/pos/_line.php'; ?>
</template>
