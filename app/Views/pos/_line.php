<?php
use App\Core\View;
?>
<tr class="line-items">
    <td>
        <select name="product_id[]" required data-product-select>
            <option value="">Select product</option>
            <?php foreach ($products as $product): ?>
                <option value="<?= View::e($product['ProductID']) ?>"
                        data-price="<?= View::e($product['UnitPrice']) ?>"
                        data-stock="<?= View::e($product['QuantityOnHand']) ?>">
                    <?= View::e($product['ProductName']) ?> (<?= View::e($product['QuantityOnHand']) ?> in stock)
                </option>
            <?php endforeach; ?>
        </select>
    </td>
    <td><input type="number" name="quantity[]" min="1" step="1" value="1" required data-quantity></td>
    <td><input type="number" name="discount[]" min="0" step="0.01" value="0" data-discount></td>
    <td class="number line-total" data-line-total>GH₵0.00</td>
    <td><button type="button" class="btn btn-ghost btn-sm" data-remove-line>Remove</button></td>
</tr>
