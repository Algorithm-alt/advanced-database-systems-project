<?php
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">After-sales</p>
        <h1>Process a return</h1>
        <p class="muted">Refunds require a password, approval phrase and a second administrator to authorise.</p>
    </div>
    <a class="btn btn-ghost" href="<?= View::e(Request::url('returns')) ?>">Back to returns</a>
</section>

<form method="get" action="<?= View::e(Request::url('returns/create')) ?>" class="toolbar">
    <input type="hidden" name="route" value="returns/create">
    <div class="field toolbar-grow">
        <label for="sale_id">Select a completed sale</label>
        <select id="sale_id" name="sale_id">
            <option value="0">Choose a sale to return…</option>
            <?php foreach ($sales as $sale): ?>
                <option value="<?= View::e($sale['SaleID']) ?>" <?= (int)$sale['SaleID'] === $saleId ? 'selected' : '' ?>>
                    <?= View::e($sale['SaleNumber']) ?> — <?= View::e($sale['CustomerName'] ?? 'Walk-in') ?>
                    (<?= View::e($sale['SaleDate'] ?? '') ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Load sale</button>
</form>

<?php if ($header !== null): ?>
    <div class="panel">
        <div class="approval-detail wide">
            <span>Sale reference</span>
            <strong><?= View::e($header['SaleNumber']) ?></strong>
            <span>Customer</span>
            <strong><?= View::e($header['CustomerName'] ?? 'Walk-in customer') ?></strong>
            <span>Original sale total</span>
            <strong class="text-lg">GH₵<?= number_format((float)$header['TotalAmount'], 2) ?></strong>
        </div>

        <form method="post" action="<?= View::e(Request::url('returns/request')) ?>" class="form-stack" data-return-form>
            <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
            <input type="hidden" name="sale_id" value="<?= View::e($saleId) ?>">

            <h2 class="section-subtitle">
                Select items to return
            </h2>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Product</th>
                        <th class="number">Available to return</th>
                        <th class="number">Unit price</th>
                        <th class="number">Return quantity</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lines as $line): ?>
                        <?php $remaining = (int)$line['RemainingQuantity']; ?>
                        <tr class="<?= $remaining === 0 ? 'row-muted' : '' ?>">
                            <td><strong><?= View::e($line['ProductName']) ?></strong></td>
                            <td class="number">
                                <span class="badge <?= $remaining > 0 ? 'badge-ok' : 'tag' ?>">
                                    <?= View::e($remaining) ?>
                                </span>
                            </td>
                            <td class="number">GH₵<?= number_format((float)$line['UnitPrice'], 2) ?></td>
                            <td class="number">
                                <input type="number"
                                       name="return_qty[<?= (int)$line['SaleItemID'] ?>]"
                                       min="0" max="<?= View::e($remaining) ?>" step="1" value="0"
                                       data-return-qty
                                       data-unit-price="<?= View::e((float)$line['UnitPrice']) ?>"
                                       <?= $remaining === 0 ? 'disabled' : '' ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="form-grid mt-16">
                <div class="field">
                    <label for="employee_id">Employee processing return <span class="req">*</span></label>
                    <select id="employee_id" name="employee_id" required>
                        <option value="">Select employee…</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= View::e($employee['EmployeeID']) ?>"><?= View::e($employee['FullName']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="refund_amount">
                        Refund amount (GH₵) <span class="req">*</span>
                        <span class="muted text-xs" data-max-refund></span>
                    </label>
                    <input id="refund_amount" name="refund_amount" type="number" min="0" step="0.01" value="0" required>
                </div>
                <div class="field full">
                    <label for="reason">Reason for return <span class="req">*</span></label>
                    <input id="reason" name="reason" type="text" required maxlength="255"
                           placeholder="e.g. Damaged on delivery, wrong item shipped…">
                </div>
            </div>

            <div class="actions form-footer">
                <button type="submit" class="btn btn-primary">Create refund approval</button>
                <a class="btn btn-ghost" href="<?= View::e(Request::url('returns')) ?>">Cancel</a>
            </div>
        </form>
    </div>
<?php endif; ?>
