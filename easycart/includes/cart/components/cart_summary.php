<?php if (!empty($cart_items)): ?>
    <aside class="cart-summary order-summary">

        <!-- HEADER (same as checkout) -->
        <section class="summary-header">
            <h2>Order Summary</h2>
        </section>

        <!-- TOTALS (same data, better alignment) -->
        <section class="summary-section">
            <dl class="summary-details">
                <dt>Items (<span id="summary-total-items">
                        <?php echo $total_items; ?>
                    </span>):</dt>
                <dd id="summary-subtotal">$
                    <?php echo number_format($subtotal, 2); ?>
                </dd>

                <dt>Shipping:</dt>
                <dd id="summary-shipping">
                    $
                    <?php echo number_format($shipping, 2); ?>
                </dd>

                <!-- PROMO DISCOUNT ROW -->
                <dt id="promo-row-label" style="<?php echo $promo_discount > 0 ? '' : 'display:none;'; ?>">Promo Discount:
                </dt>
                <dd id="promo-row-amount" class="discount-text"
                    style="<?php echo $promo_discount > 0 ? '' : 'display:none;'; ?>">-$
                    <?php echo number_format($promo_discount, 2); ?>
                </dd>

                <dt>Tax (18%):</dt>
                <dd id="summary-tax">$
                    <?php echo number_format($tax, 2); ?>
                </dd>

                <dt class="total-label">Order Total:</dt>
                <dd class="total-amount" id="summary-order-total">
                    $
                    <?php echo number_format($order_total, 2); ?>
                </dd>
            </dl>
        </section>

        <!-- SHIPPING METHOD (UNCHANGED, JUST VISUALLY GROUPED) -->
        <section class="shipping-method-section">
            <h3>Shipping Method</h3>

            <form id="cart-shipping-form">
                <fieldset>
                    <legend class="visually-hidden">Choose shipping method</legend>
                    <?php
                    $current_method = $_SESSION['shipping_method'] ?? 'standard';
                    foreach ($shipping_options as $option):
                        $is_disabled = false;
                        // Simple logic: Freight requires freight method. Non-freight requires non-freight (unless user wants to upgrade).
                        if ($requires_freight && $option['code'] !== 'freight' && $option['code'] !== 'white-glove') {
                            $is_disabled = true;
                        }
                        // If NO freight required, disable freight options? Or allow them?
                        // Prompt said "Disable: Freight Shipping... if not required".
                        if (!$requires_freight && ($option['code'] === 'freight' || $option['code'] === 'white-glove')) {
                            $is_disabled = true;
                        }
                        ?>
                        <label class="shipping-option <?php echo $is_disabled ? 'is-disabled' : ''; ?>">
                            <input type="radio" name="shipping" value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo $current_method === $option['code'] ? 'checked' : ''; ?>
                            <?php echo $is_disabled ? 'disabled' : ''; ?>>
                            <div class="option-details">
                                <p class="option-name">
                                    <?php echo htmlspecialchars($option['title']); ?>
                                </p>
                                <p class="option-time">
                                    <?php echo htmlspecialchars($option['time']); ?>
                                </p>
                            </div>
                            <p class="option-price">$
                                <?php echo number_format($option['cost'], 2); ?>
                            </p>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
            </form>
        </section>

        <!-- PROMO (UNCHANGED) -->
        <section class="promo-code-section">
            <h3>Have a promo code?</h3>
            <section class="promo-code">
                <form id="promo-form" onsubmit="return false;">
                    <label for="checkout-promo" class="visually-hidden">Promo code</label>
                    <?php
                    $active_code = isset($_SESSION['promo_code']) ? $_SESSION['promo_code'] : '';
                    ?>
                    <div class="promo-input-group">
                        <input type="text" id="checkout-promo" name="promo-code" placeholder="Enter promo code"
                            value="<?php echo htmlspecialchars($active_code); ?>" <?php echo $active_code ? 'disabled' : ''; ?>>

                        <button type="button" id="apply-promo-btn"
                            style="<?php echo $active_code ? 'display:none;' : ''; ?>">Apply</button>
                        <button type="button" id="remove-promo-btn" class="remove-btn"
                            style="<?php echo $active_code ? '' : 'display:none;'; ?>">Remove</button>
                    </div>
                    <p id="promo-message" class="message"></p>
                </form>
            </section>
        </section>

        <!-- CHECKOUT CTA -->
        <section class="checkout-section">
            <a href="checkout.php" class="checkout-button">Proceed to Checkout</a>
        </section>

        <!-- PAYMENT -->
        <section class="payment-methods">
            <h3>We Accept</h3>
            <ul class="payment-icons">
                <?php foreach ($payment_methods as $pm): ?>
                    <li>
                        <?php echo htmlspecialchars($pm['title']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- SECURITY -->
        <section class="trust-badges">
            <h3>Secure Payment</h3>
            <ul>
                <li>256-bit SSL Encryption</li>
                <li>PCI DSS Compliant</li>
                <li>Secure Payment Processing</li>
            </ul>
        </section>

    </aside>
<?php endif; ?>