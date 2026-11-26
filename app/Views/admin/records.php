<?php
use App\Core\Request;
use App\Core\Session;
use App\Core\View;
?>
<section class="page-heading">
    <div>
        <p class="eyebrow">Administration</p>
        <h1>Business records</h1>
        <p class="muted">Manage suppliers, customers and employees.</p>
    </div>
</section>

<nav class="actions mb-24" aria-label="Business record types">
    <?php foreach ($sections as $key => $details): ?>
        <a class="btn <?= $section === $key ? 'btn-primary' : 'btn-ghost' ?> btn-sm"
           href="<?= View::e(Request::url('admin/records&section=' . $key)) ?>"
           <?= $section === $key ? 'aria-current="page"' : '' ?>>
            <?= View::e($details['label']) ?>
        </a>
    <?php endforeach; ?>
</nav>

<?php if ($section === 'suppliers'): ?>
    <section class="panel mb-24">
        <div class="section-heading">
            <div>
                <h2>Suppliers</h2>
                <p class="muted">Active supplier contacts available to the business.</p>
            </div>
            <span class="badge badge-info"><?= count($suppliers) ?> supplier<?= count($suppliers) !== 1 ? 's' : '' ?></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Company</th>
                    <th>Contact</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($suppliers === []): ?>
                    <tr><td colspan="5" class="table-empty">No suppliers found.</td></tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><strong><?= View::e($supplier['SupplierName']) ?></strong><br><code><?= View::e($supplier['SupplierCode']) ?></code></td>
                            <td><?= View::e($supplier['ContactName'] ?? '—') ?></td>
                            <td><?= View::e($supplier['Phone'] ?? '—') ?></td>
                            <td><?= View::e($supplier['Email'] ?? '—') ?></td>
                            <td><?= View::e($supplier['Address'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <form method="post" action="<?= View::e(Request::url('admin/records/create')) ?>" class="panel form-stack">
        <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
        <input type="hidden" name="type" value="suppliers">
        <h2>Add supplier</h2>
        <div class="form-grid">
            <div class="field">
                <label for="company_name">Company name <span class="req">*</span></label>
                <input id="company_name" name="company_name" type="text" maxlength="100" required>
            </div>
            <div class="field">
                <label for="supplier_contact">Contact name</label>
                <input id="supplier_contact" name="contact_name" type="text" maxlength="100">
            </div>
            <div class="field">
                <label for="supplier_phone">Phone</label>
                <input id="supplier_phone" name="phone" type="tel" maxlength="30">
            </div>
            <div class="field">
                <label for="supplier_email">Email</label>
                <input id="supplier_email" name="email" type="email" maxlength="256">
            </div>
            <div class="field full">
                <label for="supplier_address">Address</label>
                <input id="supplier_address" name="address" type="text" maxlength="255">
            </div>
        </div>
        <div class="actions form-footer">
            <button type="submit" class="btn btn-primary">Add supplier</button>
        </div>
    </form>
<?php elseif ($section === 'customers'): ?>
    <section class="panel mb-24">
        <div class="section-heading">
            <div>
                <h2>Customers</h2>
                <p class="muted">Active customers available to point of sale.</p>
            </div>
            <span class="badge badge-info"><?= count($customers) ?> customer<?= count($customers) !== 1 ? 's' : '' ?></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Loyalty points</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($customers === []): ?>
                    <tr><td colspan="5" class="table-empty">No customers found.</td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><strong><?= View::e($customer['FullName']) ?></strong><br><code><?= View::e($customer['CustomerCode']) ?></code></td>
                            <td><?= View::e($customer['Email']) ?></td>
                            <td><?= View::e($customer['Phone'] ?? '—') ?></td>
                            <td><?= View::e($customer['Address'] ?? '—') ?></td>
                            <td class="number"><?= View::e($customer['LoyaltyPoints']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <form method="post" action="<?= View::e(Request::url('admin/records/create')) ?>" class="panel form-stack">
        <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
        <input type="hidden" name="type" value="customers">
        <h2>Add customer</h2>
        <p class="muted text-sm">This creates a customer record for point of sale. Customer login access is managed under Users &amp; Roles.</p>
        <div class="form-grid">
            <div class="field">
                <label for="customer_name">Full name <span class="req">*</span></label>
                <input id="customer_name" name="full_name" type="text" maxlength="100" required>
            </div>
            <div class="field">
                <label for="customer_email">Email <span class="req">*</span></label>
                <input id="customer_email" name="email" type="email" maxlength="256" required>
            </div>
            <div class="field">
                <label for="customer_phone">Phone</label>
                <input id="customer_phone" name="phone" type="tel" maxlength="30">
            </div>
            <div class="field">
                <label for="customer_address">Address</label>
                <input id="customer_address" name="address" type="text" maxlength="255">
            </div>
        </div>
        <div class="actions form-footer">
            <button type="submit" class="btn btn-primary">Add customer</button>
        </div>
    </form>
<?php else: ?>
    <section class="panel mb-24">
        <div class="section-heading">
            <div>
                <h2>Employees</h2>
                <p class="muted">Active staff records available to point of sale and returns.</p>
            </div>
            <span class="badge badge-info"><?= count($employees) ?> employee<?= count($employees) !== 1 ? 's' : '' ?></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Employee</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Hire date</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($employees === []): ?>
                    <tr><td colspan="6" class="table-empty">No employees found.</td></tr>
                <?php else: ?>
                    <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><strong><?= View::e($employee['FullName']) ?></strong><br><code><?= View::e($employee['EmployeeCode']) ?></code></td>
                            <td><?= View::e($employee['Position']) ?></td>
                            <td><?= View::e($employee['Department'] ?? '—') ?></td>
                            <td><?= View::e($employee['Phone'] ?? '—') ?></td>
                            <td><?= View::e($employee['Email'] ?? '—') ?></td>
                            <td><?= View::e($employee['HireDate']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <form method="post" action="<?= View::e(Request::url('admin/records/create')) ?>" class="panel form-stack">
        <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
        <input type="hidden" name="type" value="employees">
        <h2>Add employee</h2>
        <p class="muted text-sm">This creates an employee record for sales and returns. Login access is managed under Users &amp; Roles.</p>
        <div class="form-grid">
            <div class="field">
                <label for="employee_name">Full name <span class="req">*</span></label>
                <input id="employee_name" name="full_name" type="text" maxlength="100" required>
            </div>
            <div class="field">
                <label for="employee_position">Position <span class="req">*</span></label>
                <input id="employee_position" name="position" type="text" maxlength="50" required>
            </div>
            <div class="field">
                <label for="employee_department">Department</label>
                <input id="employee_department" name="department" type="text" maxlength="50">
            </div>
            <div class="field">
                <label for="employee_hire_date">Hire date <span class="req">*</span></label>
                <input id="employee_hire_date" name="hire_date" type="date" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="field">
                <label for="employee_phone">Phone</label>
                <input id="employee_phone" name="phone" type="tel" maxlength="30">
            </div>
            <div class="field">
                <label for="employee_email">Email</label>
                <input id="employee_email" name="email" type="email" maxlength="256">
            </div>
        </div>
        <div class="actions form-footer">
            <button type="submit" class="btn btn-primary">Add employee</button>
        </div>
    </form>
<?php endif; ?>
