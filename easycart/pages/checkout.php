require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../data/products.php';

$cart_items = $_SESSION['cart'];

// Redirect if cart is empty
if (empty($cart_items)) {
header('Location: cart.php');
exit;
}

$subtotal = 0;
$total_items = array_sum($cart_items);

foreach ($cart_items as $id => $quantity) {
if (isset($products[$id])) {
$subtotal += $products[$id]['price'] * $quantity;
}
}

$shipping = ($subtotal > 50) ? 0 : 9.99;
$tax_rate = 0.08;
$tax = $subtotal * $tax_rate;
$order_total = $subtotal + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Secure checkout for your EasyCart order">
    <title>Checkout - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.1'); ?>">

<body class="checkout-page">
    <header id="site-header">
        <div class="header-top">
            <div class="logo">
                <h1><a href="../index.php">EasyCart</a></h1>
            </div>
            <div class="header-checkout-actions">
                <a href="cart.php" class="back-link">← Back to Cart</a>
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
                            <p class="field-hint">We'll send order confirmation to this email</p>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="newsletter" value="yes">
                                Keep me updated on special offers and news
                            </label>
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
                                <label for="company">Company Name (Optional)</label>
                                <input type="text" id="company" name="company" autocomplete="organization">
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
                    <form>
                        <fieldset>
                            <legend class="visually-hidden">Choose shipping method</legend>
                            <label class="shipping-option">
                                <input type="radio" name="shipping" value="standard" checked>
                                <div class="option-details">
                                    <p class="option-name">Standard Shipping</p>
                                    <p class="option-time">5-7 business days</p>
                                </div>
                                <p class="option-price">FREE</p>
                            </label>
                            <label class="shipping-option">
                                <input type="radio" name="shipping" value="express">
                                <div class="option-details">
                                    <p class="option-name">Express Shipping</p>
                                    <p class="option-time">2-3 business days</p>
                                </div>
                                <p class="option-price">$9.99</p>
                            </label>
                            <label class="shipping-option">
                                <input type="radio" name="shipping" value="next-day">
                                <div class="option-details">
                                    <p class="option-name">Next Day Delivery</p>
                                    <p class="option-time">1 business day</p>
                                </div>
                                <p class="option-price">$19.99</p>
                            </label>
                        </fieldset>
                    </form>
                </section>

                <section class="payment-information">
                    <h2>Payment Information</h2>
                    <form>
                        <fieldset>
                            <legend class="visually-hidden">Select payment method</legend>
                            <div class="payment-method-selector">
                                <label class="payment-tab">
                                    <input type="radio" name="payment-method" value="card" checked>
                                    Credit/Debit Card
                                </label>
                                <label class="payment-tab">
                                    <input type="radio" name="payment-method" value="paypal">
                                    PayPal
                                </label>
                                <label class="payment-tab">
                                    <input type="radio" name="payment-method" value="apple-pay">
                                    Apple Pay
                                </label>
                            </div>

                            <div class="payment-form">
                                <div class="form-group">
                                    <label for="card-number">Card Number <abbr title="required">*</abbr></label>
                                    <input type="text" id="card-number" name="card-number" required
                                        autocomplete="cc-number" inputmode="numeric" maxlength="19">
                                </div>
                                <div class="form-group">
                                    <label for="cardholder-name">Cardholder Name <abbr title="required">*</abbr></label>
                                    <input type="text" id="cardholder-name" name="cardholder-name" required
                                        autocomplete="cc-name">
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="expiry-date">Expiry Date <abbr title="required">*</abbr></label>
                                        <input type="text" id="expiry-date" name="expiry-date" placeholder="MM/YY"
                                            required autocomplete="cc-exp" maxlength="5">
                                    </div>
                                    <div class="form-group">
                                        <label for="cvv">CVV <abbr title="required">*</abbr></label>
                                        <input type="text" id="cvv" name="cvv" required autocomplete="cc-csc"
                                            inputmode="numeric" maxlength="4">
                                        <p class="field-hint">3-4 digits on back of card</p>
                                    </div>
                                </div>
                            </div>
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
                    <a href="cart.php" class="back-to-cart">Return to Cart</a>
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
                    <h3>Items (<?php echo $total_items; ?>)</h3>
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
                                <p class="item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                                <p class="item-quantity">Qty: <?php echo $quantity; ?></p>
                            </div>
                            <p class="item-price">$<?php echo number_format($item_total, 2); ?></p>
                        </article>
                    <?php endforeach; ?>
                </section>

                <section class="promo-code">
                    <form>
                        <label for="checkout-promo" class="visually-hidden">Promo code</label>
                        <input type="text" id="checkout-promo" name="promo-code" placeholder="Enter promo code">
                        <button type="submit">Apply</button>
                    </form>
                </section>

                <section class="summary-totals">
                    <dl>
                        <dt>Subtotal:</dt>
                        <dd>$<?php echo number_format($subtotal, 2); ?></dd>
                        <dt>Shipping:</dt>
                        <dd><?php echo $shipping == 0 ? 'FREE' : '$' . number_format($shipping, 2); ?></dd>
                        <dt>Tax (8%):</dt>
                        <dd>$<?php echo number_format($tax, 2); ?></dd>
                        <dt class="total-label">Total:</dt>
                        <dd class="total-amount">$<?php echo number_format($order_total, 2); ?></dd>
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
</body>

</html>