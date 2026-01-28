<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../data/products.php';
require_once '../data/brands.php';

$cart_items = $_SESSION['cart'];
$subtotal = 0;
$total_items = array_sum($cart_items);

foreach ($cart_items as $id => $quantity) {
    if (isset($products[$id])) {
        $subtotal += $products[$id]['price'] * $quantity;
    }
}

$shipping_data = $_SESSION['shipping'] ?? ['type' => 'standard', 'price' => 0];
$shipping = $shipping_data['price'];
$tax_rate = 0.08;
$tax = $subtotal * $tax_rate;
$order_total = $subtotal + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your EasyCart shopping cart">
    <title>Shopping Cart - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.1'); ?>">

<body>
    <!-- HEADER ADDED: consistent site header (logo + standard navigation) -->
    <?php include '../includes/header.php'; ?>

    <main id="main-content">
        <section class="page-header">
            <h1>Shopping Cart</h1>
            <p><span id="header-total-items"><?php echo $total_items; ?></span>
                item<?php echo $total_items != 1 ? 's' : ''; ?> in your cart</p>
        </section>

        <div class="cart-container">
            <section class="cart-items">
                <h2 class="visually-hidden">Cart Items</h2>

                <?php if (empty($cart_items)): ?>
                    <p>Your cart is empty. <a href="products.php">Start shopping!</a></p>
                <?php else: ?>
                    <?php foreach ($cart_items as $id => $quantity):
                        if (!isset($products[$id]))
                            continue;
                        $item = $products[$id];
                        $item_total = $item['price'] * $quantity;
                        $brand_name = isset($brands[$item['brand_id']]) ? $brands[$item['brand_id']]['name'] : 'Generic';
                        ?>
                        <article class="cart-item">
                            <div class="item-image">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                            <div class="item-details">
                                <h3><a
                                        href="product-detail.php?id=<?php echo $id; ?>"><?php echo htmlspecialchars($item['name']); ?></a>
                                </h3>
                                <p class="item-brand">Brand: <?php echo htmlspecialchars($brand_name); ?></p>
                                <p class="item-stock">In Stock</p>

                            </div>
                            <div class="item-quantity">
                                <label for="quantity-<?php echo $id; ?>">Quantity:</label>
                                <div class="quantity-controls">
                                    <button type="button" class="qty-btn minus" aria-label="Decrease quantity">−</button>
                                    <input type="number" id="quantity-<?php echo $id; ?>" name="quantity"
                                        value="<?php echo $quantity; ?>" min="1" max="10" readonly>
                                    <button type="button" class="qty-btn plus" aria-label="Increase quantity">+</button>
                                </div>
                            </div>
                            <div class="item-price">
                                <p class="unit-price" data-price="<?php echo $item['price']; ?>">
                                    $<?php echo number_format($item['price'], 2); ?> each</p>
                                <p class="total-price" data-item-total>$<?php echo number_format($item_total, 2); ?></p>
                            </div>
                            <div class="item-actions">

                                <button type="button" class="action-btn remove-item">Remove</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <aside class="cart-summary order-summary">

                <!-- HEADER (same as checkout) -->
                <section class="summary-header">
                    <h2>Order Summary</h2>
                </section>

                <!-- TOTALS (same data, better alignment) -->
                <section class="summary-section">
                    <dl class="summary-details">
                        <dt>Items (<span id="summary-total-items"><?php echo $total_items; ?></span>):</dt>
                        <dd id="summary-subtotal">$<?php echo number_format($subtotal, 2); ?></dd>

                        <dt>Shipping:</dt>
                        <dd id="summary-shipping">
                            <?php echo $shipping == 0 ? 'FREE' : '$' . number_format($shipping, 2); ?>
                        </dd>

                        <dt>Tax (8%):</dt>
                        <dd id="summary-tax">$<?php echo number_format($tax, 2); ?></dd>

                        <dt class="total-label">Order Total:</dt>
                        <dd class="total-amount" id="summary-order-total">
                            $<?php echo number_format($order_total, 2); ?>
                        </dd>
                    </dl>
                </section>

                <!-- SHIPPING METHOD (UNCHANGED, JUST VISUALLY GROUPED) -->
                <section class="shipping-method-section">
                    <h3>Shipping Method</h3>

                    <form id="cart-shipping-form">
                        <fieldset>
                            <legend class="visually-hidden">Choose shipping method</legend>
                            <?php $current_type = $shipping_data['type']; ?>

                            <label class="shipping-option">
                                <input type="radio" name="shipping" value="standard" <?php echo $current_type === 'standard' ? 'checked' : ''; ?>>
                                <div class="option-details">
                                    <p class="option-name">Standard Shipping</p>
                                    <p class="option-time">5–7 business days</p>
                                </div>
                                <p class="option-price">FREE</p>
                            </label>

                            <label class="shipping-option">
                                <input type="radio" name="shipping" value="express" <?php echo $current_type === 'express' ? 'checked' : ''; ?>>
                                <div class="option-details">
                                    <p class="option-name">Express Shipping</p>
                                    <p class="option-time">2–3 business days</p>
                                </div>
                                <p class="option-price">$9.99</p>
                            </label>

                            <label class="shipping-option">
                                <input type="radio" name="shipping" value="next-day" <?php echo $current_type === 'next-day' ? 'checked' : ''; ?>>
                                <div class="option-details">
                                    <p class="option-name">Next Day Delivery</p>
                                    <p class="option-time">1 business day</p>
                                </div>
                                <p class="option-price">$19.99</p>
                            </label>
                        </fieldset>
                    </form>
                </section>

                <!-- PROMO (UNCHANGED) -->
                <section class="promo-code-section">
                    <h3>Have a promo code?</h3>
                    <section class="promo-code">
                        <form>
                            <label for="checkout-promo" class="visually-hidden">Promo code</label>
                            <input type="text" id="checkout-promo" name="promo-code" placeholder="Enter promo code">
                            <button type="submit">Apply</button>
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
                        <li>Visa</li>
                        <li>Mastercard</li>
                        <li>American Express</li>
                        <li>PayPal</li>
                        <li>Apple Pay</li>
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




        </div>

        <section class="cart-wishlist" id="wishlist-section">
            <div class="section-header">
                <h2>Your Wishlist</h2>
                <div class="carousel-controls">
                    <button type="button" class="carousel-btn prev" id="wishlist-prev"
                        aria-label="Previous items">←</button>
                    <button type="button" class="carousel-btn next" id="wishlist-next"
                        aria-label="Next items">→</button>
                </div>
            </div>
            <div class="wishlist-carousel-container">
                <div class="wishlist-items" id="wishlist-items-container">
                    <!-- Wishlist items will be loaded via JavaScript -->
                    <p class="wishlist-loading">Loading your wishlist...</p>
                </div>
            </div>
        </section>

        <!-- Product data bridge for JS -->
        <script>
            window.allProducts = <?php echo json_encode($products); ?>;
            window.shippingPrice = <?php echo isset($_SESSION['shipping_price']) ? $_SESSION['shipping_price'] : 0; ?>;
        </script>



        <section class="recommended-products">
            <h2>Frequently Bought Together</h2>
            <div class="product-grid">
                <article class="product-card">
                    <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400" alt="Headphone Stand">
                    <h3><a href="product-detail.php?id=14">Headphone Stand</a></h3>
                    <p class="product-price">$19.99</p>
                </article>
                <article class="product-card">
                    <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400"
                        alt="Audio Cable Premium">
                    <h3><a href="product-detail.php?id=15">Audio Cable Premium</a></h3>
                    <p class="product-price">$12.99</p>
                </article>
                <article class="product-card">
                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400"
                        alt="Watch Charging Dock">
                    <h3><a href="product-detail.php?id=17">Watch Charging Dock</a></h3>
                    <p class="product-price">$24.99</p>
                </article>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo asset('js/cart.js'); ?>"></script>
</body>

</html>