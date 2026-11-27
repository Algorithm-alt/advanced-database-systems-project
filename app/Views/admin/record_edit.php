<?php
use App\Core\Request;
use App\Core\Session;
use App\Core\View;

$isSupplier = $section === 'suppliers';
$isEmployee = $section === 'employees';
$recordLabel = $isSupplier ? 'supplier' : ($isEmployee ? 'employee' : 'customer');
$recordId = (int)$record[$isSupplier ? 'SupplierID' : ($isEmployee ? 'EmployeeID' : 'CustomerID')];
?>
<section class="narrow-page">
    <div class="panel">
        <p class="eyebrow">Business records</p>
        <h1>Edit <?= $recordLabel ?></h1>

        <form method="post" action="<?= View::e(Request::url('admin/records/update')) ?>" class="form-stack">
            <input type="hidden" name="csrf_token" value="<?= View::e(Session::csrfToken()) ?>">
            <input type="hidden" name="section" value="<?= View::e($section) ?>">
            <input type="hidden" name="record_id" value="<?= $recordId ?>">

            <div class="form-grid">
                <?php if ($isSupplier): ?>
                    <div class="field">
                        <label for="company_name">Company name <span class="req">*</span></label>
                        <input id="company_name" name="company_name" type="text" maxlength="100" required
                               value="<?= View::e($record['SupplierName']) ?>">
                    </div>
                    <div class="field">
                        <label for="contact_name">Contact name</label>
                        <input id="contact_name" name="contact_name" type="text" maxlength="100"
                               value="<?= View::e($record['ContactName'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" maxlength="256"
                               value="<?= View::e($record['Email'] ?? '') ?>">
                    </div>
                <?php elseif ($isEmployee): ?>
                    <div class="field">
                        <label for="full_name">Full name <span class="req">*</span></label>
                        <input id="full_name" name="full_name" type="text" maxlength="100" required
                               value="<?= View::e($record['FullName']) ?>">
                    </div>
                    <div class="field">
                        <label for="position">Position <span class="req">*</span></label>
                        <input id="position" name="position" type="text" maxlength="50" required
                               value="<?= View::e($record['Position']) ?>">
                    </div>
                    <div class="field">
                        <label for="department">Department</label>
                        <input id="department" name="department" type="text" maxlength="50"
                               value="<?= View::e($record['Department'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="hire_date">Hire date <span class="req">*</span></label>
                        <input id="hire_date" name="hire_date" type="date" required
                               value="<?= View::e($record['HireDate']) ?>">
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" maxlength="256"
                               value="<?= View::e($record['Email'] ?? '') ?>">
                    </div>
                <?php else: ?>
                    <div class="field">
                        <label for="full_name">Full name <span class="req">*</span></label>
                        <input id="full_name" name="full_name" type="text" maxlength="100" required
                               value="<?= View::e($record['FullName']) ?>">
                    </div>
                    <div class="field">
                        <label for="email">Email <span class="req">*</span></label>
                        <input id="email" name="email" type="email" maxlength="256" required
                               value="<?= View::e($record['Email']) ?>">
                    </div>
                <?php endif; ?>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input id="phone" name="phone" type="tel" maxlength="30"
                           value="<?= View::e($record['Phone'] ?? '') ?>">
                </div>
                <?php if (!$isEmployee): ?>
                    <div class="field full">
                        <label for="address">Address</label>
                        <input id="address" name="address" type="text" maxlength="255"
                               value="<?= View::e($record['Address'] ?? '') ?>">
                    </div>
                <?php endif; ?>
            </div>

            <div class="actions form-footer">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a class="btn btn-ghost" href="<?= View::e(Request::url('admin/records&section=' . $section)) ?>">Cancel</a>
            </div>
        </form>
    </div>
</section>
