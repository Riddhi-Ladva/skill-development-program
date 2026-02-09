<?php if (empty($cart_items)): ?>
    <p>Your cart is empty. <a href="<?php echo url('products'); ?>">Start shopping!</a></p>
<?php else: ?>
    <?php foreach ($cart_details as $id => $item):
        // $item now contains all the calculated fields from services.php
        $brand_name = isset($brands[$products[$id]['brand_id']]) ? $brands[$products[$id]['brand_id']]['name'] : 'Generic';
        ?>
        <article class="cart-item" data-product-id="<?php echo $id; ?>">
            <div class="item-image">
                <img src="<?php echo htmlspecialchars($products[$id]['image']); ?>"
                    alt="<?php echo htmlspecialchars($products[$id]['name']); ?>">
            </div>
            <div class="item-details">
                <h3><a href="<?php echo url('product-detail?id=' . $id); ?>">
                        <?php echo htmlspecialchars($products[$id]['name']); ?>
                    </a>
                </h3>
                <p class="item-brand">Brand:
                    <?php echo htmlspecialchars($brand_name); ?>
                </p>

                <p class="item-stock">In Stock</p>
                <p class="shipping-eligibility">
                    <span class="shipping-label <?php echo $item['shipping_eligibility']['class']; ?>">
                        <?php echo $item['shipping_eligibility']['icon']; ?>
                        <?php echo $item['shipping_eligibility']['label']; ?>
                    </span>
                </p>

            </div>
            <div class="item-quantity">
                <label for="quantity-<?php echo $id; ?>">Quantity:</label>
                <div class="quantity-controls">
                    <button type="button" class="qty-btn minus" aria-label="Decrease quantity">−</button>
                    <input type="number" id="quantity-<?php echo $id; ?>" name="quantity"
                        value="<?php echo $item['quantity']; ?>" min="1" max="10" readonly>
                    <button type="button" class="qty-btn plus" aria-label="Increase quantity">+</button>
                </div>
            </div>
            <div class="item-price">
                <p class="unit-price" data-price="<?php echo $item['price']; ?>">
                    $
                    <?php echo number_format($item['price'], 2); ?> each
                </p>

                <p class="total-price" data-item-total>
                    $
                    <?php echo number_format($item['final_total'], 2); ?>
                </p>

                <p class="item-discount" style="<?php echo $item['discount_amount'] > 0 ? '' : 'display:none;'; ?>">
                    Discount (
                    <?php echo $item['discount_percent']; ?>%):
                    -$
                    <?php echo number_format($item['discount_amount'], 2); ?>
                </p>
            </div>
            <div class="item-actions">

                <button type="button" class="action-btn remove-item">Remove</button>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>