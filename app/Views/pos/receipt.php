<?php
use App\Core\Request;
use App\Core\View;

$documentType = $documentType ?? 'Receipt';
?>
<section class="narrow-page">
    <div class="panel" id="receipt">
        <div class="receipt-header">
            <div>
                <p class="eyebrow">Algorithm Hardware Plug · <?= View::e($documentType) ?></p>
                <h1 class="receipt-title"><?= View::e($sale['SaleNumber']) ?></h1>
                <p class="muted text-sm mb-0"><?= View::e($sale['SaleDate']) ?></p>
            </div>
            <span class="badge badge-ok mt-4"><?= View::e($sale['Status']) ?></span>
        </div>

        <div class="approval-detail wide">
            <span>Customer</span>
            <strong><?= View::e($sale['CustomerName'] ?? 'Walk-in customer') ?></strong>
            <span>Employee</span>
            <strong><?= View::e($sale['EmployeeName']) ?></strong>
            <span>Payment method</span>
            <strong><?= View::e($sale['PaymentMethod'] ?? '—') ?></strong>
        </div>

        <div class="table-wrap mt-20">
            <table>
                <thead>
                <tr>
                    <th>Product</th>
                    <th class="number">Qty</th>
                    <th class="number">Unit price</th>
                    <th class="number">Discount</th>
                    <th class="number">Line total</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($lines as $line): ?>
                    <tr>
                        <td><?= View::e($line['ProductName']) ?></td>
                        <td class="number"><?= View::e($line['Quantity']) ?></td>
                        <td class="number">GH₵<?= number_format((float)$line['UnitPrice'], 2) ?></td>
                        <td class="number">
                            <?php if ((float)$line['DiscountAmount'] > 0): ?>
                                <span class="text-danger">-GH₵<?= number_format((float)$line['DiscountAmount'], 2) ?></span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="number"><strong>GH₵<?= number_format((float)$line['LineTotal'], 2) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="totals">
            <div class="total-row"><span>Subtotal</span><strong>GH₵<?= number_format((float)$sale['SubTotal'], 2) ?></strong></div>
            <?php if ($documentType === 'Invoice' && (float)$sale['DiscountAmount'] > 0): ?>
                <div class="total-row"><span>Discount applied (included in subtotal)</span><strong>-GH₵<?= number_format((float)$sale['DiscountAmount'], 2) ?></strong></div>
            <?php endif; ?>
            <div class="total-row"><span>Tax</span><strong>GH₵<?= number_format((float)$sale['TaxAmount'], 2) ?></strong></div>
            <div class="total-row grand"><span>Total</span><strong>GH₵<?= number_format((float)$sale['TotalAmount'], 2) ?></strong></div>
            <div class="total-row"><span>Amount paid</span><strong>GH₵<?= number_format((float)$sale['AmountPaid'], 2) ?></strong></div>
            <div class="total-row is-positive">
                <span>Change due</span>
                <strong>GH₵<?= number_format((float)$sale['ChangeAmount'], 2) ?></strong>
            </div>
        </div>

        <div class="actions form-footer">
            <button type="button" class="btn btn-primary" data-print>Print <?= View::e(strtolower($documentType)) ?></button>
            <?php if ($documentType === 'Invoice'): ?>
                <a class="btn btn-ghost" href="<?= View::e(Request::url('pos/receipt&sale_id=' . (int)$sale['SaleID'])) ?>">View receipt</a>
            <?php elseif ((string)$sale['Status'] === 'COMPLETED'): ?>
                <a class="btn btn-ghost" href="<?= View::e(Request::url('pos/invoice&sale_id=' . (int)$sale['SaleID'])) ?>">Generate invoice</a>
            <?php endif; ?>
            <a class="btn btn-ghost" href="<?= View::e(Request::url('pos')) ?>">New sale</a>
            <a class="btn btn-ghost" href="<?= View::e(Request::url('inventory')) ?>">View inventory</a>
        </div>
    </div>
</section>
