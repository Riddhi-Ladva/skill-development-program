<?php
require_once '../includes/checkout/logic.php';
require_once ROOT_PATH . '/includes/auth/guard.php';
auth_guard();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure checkout for your EasyCart order">
    <title>Checkout - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.1'); ?>">
    <!-- Global EasyCart configuration for JavaScript -->
    <script>
        window.EasyCart = {
            baseUrl: '<?php echo BASE_PATH; ?>',
            ajaxUrl: '<?php echo url('ajax'); ?>'
        };
    </script>
</head>

<body class="checkout-page">
    <header id="site-header">
        <div class="header-top">
            <div class="logo">
                <h1><a href="<?php echo url('index'); ?>">EasyCart</a></h1>
            </div>
            <div class="header-checkout-actions">
                <a href="<?php echo url('cart'); ?>" class="back-link">← Back to Cart</a>
            </div>
            <div class="secure-checkout-badge">
                <p>Secure Checkout</p>
            </div>
        </div>
        <nav class="checkout-progress" aria-label="Checkout progress">
            <ol>
                <li class="active" aria-current="step">Shipping</li>
                <li>Payment</li>
                <li>Review</li>
            </ol>
        </nav>
    </header>

    <main id="main-content">
        <div class="checkout-container">
            <section class="checkout-form">
                <h1>Checkout</h1>

                <section class="contact-information">
                    <h2>Contact Information</h2>
                    <form>
                        <div class="form-group">
                            <label for="email">Email Address <abbr title="required">*</abbr></label>
                            <input type="email" id="email" name="email" required autocomplete="email">
                        </div>
    
                    </form>
                </section>

                <section class="shipping-address">
                    <h2>Shipping Address</h2>
                    <form>
                        <fieldset>
                            <legend class="visually-hidden">Enter your shipping address</legend>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first-name">First Name <abbr title="required">*</abbr></label>
                                    <input type="text" id="first-name" name="first-name" required
                                        autocomplete="given-name">
                                </div>
                                <div class="form-group">
                                    <label for="last-name">Last Name <abbr title="required">*</abbr></label>
                                    <input type="text" id="last-name" name="last-name" required
                                        autocomplete="family-name">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="address-line1">Street Address <abbr title="required">*</abbr></label>
                                <input type="text" id="address-line1" name="address-line1" required
                                    autocomplete="address-line1">
                            </div>
                            <div class="form-group">
                                <label for="address-line2">Apartment, Suite, etc. (Optional)</label>
                                <input type="text" id="address-line2" name="address-line2" autocomplete="address-line2">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City <abbr title="required">*</abbr></label>
                                    <input type="text" id="city" name="city" required autocomplete="address-level2">
                                </div>
                                <div class="form-group">
                                    <label for="state">State/Province <abbr title="required">*</abbr></label>
                                    <select id="state" name="state" required autocomplete="address-level1">
                                        <option value="">Select state</option>
                                        <option value="CA">California</option>
                                        <option value="NY">New York</option>
                                        <option value="TX">Texas</option>
                                        <option value="FL">Florida</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="zip">ZIP/Postal Code <abbr title="required">*</abbr></label>
                                    <input type="text" id="zip" name="zip" required autocomplete="postal-code">
                                </div>
                                <div class="form-group">
                                    <label for="country">Country <abbr title="required">*</abbr></label>
                                    <select id="country" name="country" required autocomplete="country">
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="AU">Australia</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number <abbr title="required">*</abbr></label>
                                <input type="tel" id="phone" name="phone" required autocomplete="tel">
                                <p class="field-hint">For delivery updates</p>
                            </div>
                        </fieldset>
                    </form>
                </section>

                <section class="shipping-method">
                    <h2>Shipping Method</h2>
                    <?php
                    $shipping_method = $_SESSION['shipping_method'] ?? 'standard';
                    // Map codes to user-friendly labels (Logic should be in a helper, but fine here for view)
                    $shipping_labels = [
                        'standard' => 'Standard Shipping (5-7 business days)',
                        'express' => 'Express Shipping (2-3 business days)',
                        'white-glove' => 'White Glove Delivery (7-10 business days)',
                        'freight' => 'Freight Shipping (10-14 business days)'
                    ];
                    $label = isset($shipping_labels[$shipping_method]) ? $shipping_labels[$shipping_method] : 'Standard Shipping';
                    $price_display = '$' . number_format($shipping, 2);
                    ?>
                    <div class="selected-shipping-method">
                        <p class="method-label">
                            <?php echo htmlspecialchars($label); ?>
                        </p>
                        <p class="method-price">
                            <?php echo $price_display; ?>
                        </p>
                    </div>
                    <p class="change-shipping-hint">To change shipping method, <a href="<?php echo url('cart'); ?>">return to cart</a>.
                    </p>
                </section>

                <section class="payment-information">
                    <h2>Payment Information</h2>
                    <form>
                        <fieldset>
                            <legend class="visually-hidden">Select payment method</legend>
                            <div class="payment-method-selector">
                                <?php
                                $payment_methods = get_active_payment_methods();
                                foreach ($payment_methods as $index => $method):
                                    $checked = ($index === 0) ? 'checked' : '';
                                    ?>
                                    <label class="payment-tab">
                                        <input type="radio" name="payment-method"
                                            value="<?php echo htmlspecialchars($method['code']); ?>" <?php echo $checked; ?>>
                                        <?php echo htmlspecialchars($method['title']); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="field-hint" style="margin-top: 1rem;">
                                Selected payment method will be used for this order. No further details required.
                            </p>
                        </fieldset>
                    </form>
                </section>

                <section class="billing-address">
                    <h2>Billing Address</h2>
                    <form>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="same-as-shipping" checked>
                                Same as shipping address
                            </label>
                        </div>
                        <div class="billing-form" hidden>
                            <div class="form-group">
                                <label for="billing-address">Street Address <abbr title="required">*</abbr></label>
                                <input type="text" id="billing-address" name="billing-address"
                                    autocomplete="billing address-line1">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="billing-city">City <abbr title="required">*</abbr></label>
                                    <input type="text" id="billing-city" name="billing-city"
                                        autocomplete="billing address-level2">
                                </div>
                                <div class="form-group">
                                    <label for="billing-state">State <abbr title="required">*</abbr></label>
                                    <select id="billing-state" name="billing-state"
                                        autocomplete="billing address-level1">
                                        <option value="">Select state</option>
                                        <option value="CA">California</option>
                                        <option value="NY">New York</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="billing-zip">ZIP Code <abbr title="required">*</abbr></label>
                                <input type="text" id="billing-zip" name="billing-zip"
                                    autocomplete="billing postal-code">
                            </div>
                        </div>
                    </form>
                </section>

                <section class="order-actions">
                    <button type="submit" class="place-order-button">Place Order</button>
                    <a href="<?php echo url('cart'); ?>" class="back-to-cart">Return to Cart</a>
                    <p class="terms-notice">
                        By placing your order, you agree to our
                        <a href="#">Terms of Service</a> and
                        <a href="#">Privacy Policy</a>
                    </p>
                </section>
            </section>

            <aside class="order-summary">
                <section class="summary-header">
                    <h2>Order Summary</h2>
                    <button type="button" class="toggle-summary">Show Details</button>
                </section>

                <section class="summary-items">
                    <h3>Items (
                        <?php echo $total_items; ?>)
                    </h3>
                    <?php foreach ($cart_items as $id => $quantity):
                        if (!isset($products[$id]))
                            continue;
                        $item = $products[$id];
                        $item_total = $item['price'] * $quantity;
                        ?>
                        <article class="summary-item">
                            <div class="item-image">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="item-info">
                                <p class="item-name">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </p>
                                <p class="item-quantity">Qty:
                                    <?php echo $quantity; ?>
                                </p>
                            </div>
                            <p class="item-price">$
                                <?php echo number_format($item_total, 2); ?>
                            </p>
                        </article>
                    <?php endforeach; ?>
                </section>


                <section class="summary-totals">
                    <dl>
                        <dt>Subtotal:</dt>
                        <dd>$
                            <?php echo number_format($subtotal, 2); ?>
                        </dd>
                        <dt id="promo-row-label" style="<?php echo $promo_discount > 0 ? '' : 'display:none;'; ?>">Promo
                            Discount:</dt>
                        <dd id="promo-row-amount" class="discount-text"
                            style="<?php echo $promo_discount > 0 ? '' : 'display:none;'; ?>">-$
                            <?php echo number_format($promo_discount, 2); ?>
                        </dd>
                        <dt>Shipping:</dt>
                        <dd>$
                            <?php echo number_format($shipping, 2); ?>
                        </dd>
                        <dt>Tax (18%):</dt>
                        <dd>$
                            <?php echo number_format($tax, 2); ?>
                        </dd>
                        <dt class="total-label">Total:</dt>
                        <dd class="total-amount">$
                            <?php echo number_format($order_total, 2); ?>
                        </dd>
                    </dl>
                </section>

                <section class="trust-badges">
                    <h3>Secure Payment</h3>
                    <ul>
                        <li>256-bit SSL Encryption</li>
                        <li>PCI DSS Compliant</li>
                        <li>Secure Payment Processing</li>
                    </ul>
                </section>

                <section class="money-back-guarantee">
                    <h3>Money-Back Guarantee</h3>
                    <p>30-day returns on all products</p>
                </section>
            </aside>
        </div>
    </main>

    <footer id="site-footer">
        <div class="footer-minimal">
            <p>&copy; 2026 EasyCart. All rights reserved.</p>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Help</a></li>
            </ul>
        </div>
    </footer>
    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo asset('js/checkout/validation.js'); ?>"></script>
    <script>
        // Inject shipping price for any future cart summary updates on this page if needed
        window.shippingPrice = <?php echo isset($_SESSION['shipping_price']) ? $_SESSION['shipping_price'] : 0; ?>;

        // Handle shipping changes on checkout page specifically if they differ from product detail
        document.addEventListener('DOMContentLoaded', () => {
            const checkoutShippingOptions = document.querySelectorAll('.shipping-option input[type="radio"]');
            checkoutShippingOptions.forEach(radio => {
                radio.addEventListener('change', () => {
                    if (radio.checked) {
                        fetch('<?php echo url('ajax/shipping/update.php'); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ type: radio.value })
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Reload to update totals (simplest approach for checkout)
                                    window.location.reload();
                                }
                            })
                            .catch(error => console.error('Error updating shipping:', error));
                    }
                });
            });
        });
    </script>
</body>

</html>