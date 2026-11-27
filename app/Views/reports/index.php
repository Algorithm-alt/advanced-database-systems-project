<?php
use App\Core\Auth;
use App\Core\Request;
use App\Core\View;
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Insights</p>
        <h1>Sales report</h1>
        <p class="muted">Sales from <?= View::e($from) ?> through <?= View::e($to) ?> using the selected filters.</p>
    </div>
    <div class="actions">
        <button type="button" class="btn btn-ghost" data-print
                title="Open the print dialog; choose Save as PDF to download a PDF">Print / Save as PDF</button>
        <?php if (Auth::hasRole('Administrator')): ?>
            <a class="btn btn-ghost" href="<?= View::e(Request::url('reports/audit')) ?>">Audit trail</a>
        <?php endif; ?>
    </div>
</section>

<form method="get" action="<?= View::e(Request::url('reports')) ?>" class="toolbar">
    <input type="hidden" name="route" value="reports">
    <div class="field">
        <label for="from_date">From date</label>
        <input id="from_date" name="from_date" type="date" value="<?= View::e($from) ?>" required>
    </div>
    <div class="field">
        <label for="to_date">To date</label>
        <input id="to_date" name="to_date" type="date" value="<?= View::e($to) ?>" required>
    </div>
    <div class="field">
        <label for="customer_id">Customer</label>
        <select id="customer_id" name="customer_id">
            <option value="0">All customers</option>
            <?php foreach ($customers as $customer): ?>
                <option value="<?= View::e($customer['CustomerID']) ?>"
                    <?= (int)$customer['CustomerID'] === $customerId ? 'selected' : '' ?>>
                    <?= View::e($customer['FullName']) ?>
                </option>
            <?php endforeach; ?>
        </select>
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
    <button type="submit" class="btn btn-primary">Generate</button>
    <button type="submit" name="export" value="csv" class="btn btn-ghost">Export CSV</button>
</form>

<?php
$grandTotal = array_sum(array_column($rows, 'TotalAmount'));
$grandTax   = array_sum(array_column($rows, 'TaxAmount'));
$dailySales = [];
$customerSales = [];
foreach ($rows as $row) {
    $day = substr((string)$row['SaleDate'], 0, 10);
    if ($day !== '') {
        $dailySales[$day] = ($dailySales[$day] ?? 0) + (float)$row['TotalAmount'];
    }
    $customer = (string)($row['CustomerName'] ?? 'Walk-in');
    $customerSales[$customer] = ($customerSales[$customer] ?? 0) + (float)$row['TotalAmount'];
}
ksort($dailySales);
if (count($dailySales) > 14) {
    $dailySales = array_slice($dailySales, -14, null, true);
}
arsort($customerSales);
$customerSales = array_slice($customerSales, 0, 5, true);
?>

<?php if ($rows !== []): ?>
    <div class="kpi-row">
        <div class="kpi-card">
            <p class="eyebrow">Total sales</p>
            <strong class="kpi-value">GH₵<?= number_format($grandTotal, 2) ?></strong>
        </div>
        <div class="kpi-card">
            <p class="eyebrow">Total tax</p>
            <strong class="kpi-value">GH₵<?= number_format($grandTax, 2) ?></strong>
        </div>
        <div class="kpi-card">
            <p class="eyebrow">Transactions</p>
            <strong class="kpi-value"><?= count($rows) ?></strong>
        </div>
    </div>
<?php endif; ?>

<?php if ($rows !== []): ?>
    <div class="report-charts">
        <section class="panel report-chart">
            <div class="report-chart-heading">
                <div>
                    <h2>Daily sales</h2>
                    <p class="muted text-sm mb-0">Revenue by sale date (GH₵)</p>
                </div>
            </div>
            <svg class="report-chart-svg" viewBox="0 0 640 220" role="img"
                 aria-label="Daily sales revenue bar chart in Ghana cedis">
                <?php
                $dailyMax = max($dailySales ?: [0]);
                $dailyCount = count($dailySales);
                $dailyStep = 580 / max(1, $dailyCount);
                foreach ($dailySales as $day => $amount):
                    $barHeight = $dailyMax > 0 ? max(2, (float)$amount / $dailyMax * 145) : 0;
                    $x = 35 + (array_search($day, array_keys($dailySales), true) * $dailyStep);
                    $label = date('m/d', strtotime($day));
                ?>
                    <g>
                        <title><?= View::e($day) ?>: GH₵<?= number_format((float)$amount, 2) ?></title>
                        <rect class="report-chart-bar" x="<?= number_format($x + $dailyStep * .2, 2, '.', '') ?>"
                              y="<?= number_format(172 - $barHeight, 2, '.', '') ?>"
                              width="<?= number_format(max(3, $dailyStep * .6), 2, '.', '') ?>"
                              height="<?= number_format($barHeight, 2, '.', '') ?>" rx="3"/>
                        <text class="report-chart-label" x="<?= number_format($x + $dailyStep / 2, 2, '.', '') ?>"
                              y="194" text-anchor="middle"><?= View::e($label) ?></text>
                    </g>
                <?php endforeach; ?>
                <line class="report-chart-axis" x1="30" y1="173" x2="615" y2="173"/>
            </svg>
        </section>

        <section class="panel report-chart">
            <div class="report-chart-heading">
                <div>
                    <h2>Top customers</h2>
                    <p class="muted text-sm mb-0">Sales total by customer (GH₵)</p>
                </div>
            </div>
            <svg class="report-chart-svg" viewBox="0 0 640 220" role="img"
                 aria-label="Top five customers by sales total in Ghana cedis">
                <?php
                $customerMax = max($customerSales ?: [0]);
                $customerCount = count($customerSales);
                $customerStep = 580 / max(1, $customerCount);
                $customerIndex = 0;
                foreach ($customerSales as $customer => $amount):
                    $barHeight = $customerMax > 0 ? max(2, (float)$amount / $customerMax * 145) : 0;
                    $x = 35 + ($customerIndex * $customerStep);
                    $customerIndex++;
                    $shortName = strlen($customer) > 12 ? substr($customer, 0, 11) . '…' : $customer;
                ?>
                    <g>
                        <title><?= View::e($customer) ?>: GH₵<?= number_format((float)$amount, 2) ?></title>
                        <rect class="report-chart-bar" x="<?= number_format($x + $customerStep * .2, 2, '.', '') ?>"
                              y="<?= number_format(172 - $barHeight, 2, '.', '') ?>"
                              width="<?= number_format(max(3, $customerStep * .6), 2, '.', '') ?>"
                              height="<?= number_format($barHeight, 2, '.', '') ?>" rx="3"/>
                        <text class="report-chart-label" x="<?= number_format($x + $customerStep / 2, 2, '.', '') ?>"
                              y="194" text-anchor="middle"><?= View::e($shortName) ?></text>
                    </g>
                <?php endforeach; ?>
                <line class="report-chart-axis" x1="30" y1="173" x2="615" y2="173"/>
            </svg>
        </section>
    </div>
<?php endif; ?>

<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Sale</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Employee</th>
            <th class="number">Subtotal</th>
            <th class="number">Tax</th>
            <th class="number">Total</th>
            <th>Status</th>
            <th data-export-ignore>Invoice</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($rows === []): ?>
            <tr>
                <td colspan="9" class="table-empty">
                    No sales in this date range.
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><strong><?= View::e($row['SaleNumber']) ?></strong></td>
                    <td><span class="muted text-xs nowrap"><?= View::e($row['SaleDate']) ?></span></td>
                    <td><?= View::e($row['CustomerName'] ?? 'Walk-in') ?></td>
                    <td><?= View::e($row['EmployeeName']) ?></td>
                    <td class="number">GH₵<?= number_format((float)$row['SubTotal'], 2) ?></td>
                    <td class="number">GH₵<?= number_format((float)$row['TaxAmount'], 2) ?></td>
                    <td class="number"><strong>GH₵<?= number_format((float)$row['TotalAmount'], 2) ?></strong></td>
                    <td><span class="badge badge-ok"><?= View::e($row['Status']) ?></span></td>
                    <td>
                        <?php if ((string)$row['Status'] === 'COMPLETED'): ?>
                            <a class="btn btn-ghost btn-sm report-invoice-link"
                               href="<?= View::e(Request::url('pos/invoice&sale_id=' . (int)$row['SaleID'])) ?>">Invoice</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
