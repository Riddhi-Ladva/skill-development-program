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

$shipping = (isset($_SESSION['shipping_price']) && $subtotal > 0) ? $_SESSION['shipping_price'] : 0;
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
                                <p class="item-shipping"><?php echo htmlspecialchars($item['shipping']); ?></p>
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
                                <button type="button" class="action-btn save-for-later">Save for Later</button>
                                <span class="action-divider" aria-hidden="true">|</span>
                                <button type="button" class="action-btn remove-item">Remove</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <aside class="cart-summary">
                <section class="summary-section">
                    <h2>Order Summary</h2>
                    <dl class="summary-details">
                        <dt>Items (<span id="summary-total-items"><?php echo $total_items; ?></span>):</dt>
                        <dd id="summary-subtotal">$<?php echo number_format($subtotal, 2); ?></dd>
                        <dt>Shipping:</dt>
                        <dd id="summary-shipping">
                            <?php echo $shipping == 0 ? 'FREE' : '$' . number_format($shipping, 2); ?>
                        </dd>
                        <dt>Tax (8%):</dt>
                        <dd id="summary-tax">$<?php echo number_format($tax, 2); ?></dd>
                        <dt>Order Total:</dt>
                        <dd class="total-amount" id="summary-order-total">$<?php echo number_format($order_total, 2); ?>
                        </dd>
                    </dl>
                </section>

                <section class="promo-code-section">
                    <h3>Have a promo code?</h3>
                    <form>
                        <label for="promo-code" class="visually-hidden">Enter promo code</label>
                        <input type="text" id="promo-code" name="promo-code" placeholder="Enter code">
                        <button type="submit">Apply</button>
                    </form>
                </section>

                <section class="checkout-section">
                    <a href="checkout.php" style="color:white;" class="checkout-button">Proceed to Checkout</a>
                    <a href="products.php" class="continue-shopping">Continue Shopping</a>
                </section>

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

                <section class="security-badges">
                    <p>Secure Checkout</p>
                    <p>256-bit SSL Encryption</p>
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
        </script>

        <section class="saved-for-later">
            <h2>Saved for Later (2 items)</h2>
            <div class="saved-items-grid">
                <article class="saved-item">
                    <div class="item-image">
                        <img src="https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=200"
                            alt="Portable Bluetooth Speaker">
                    </div>
                    <div class="item-info">
                        <h3><a href="product-detail.html?id=9">Portable Bluetooth Speaker</a></h3>
                        <p class="item-price">$69.99</p>
                    </div>
                    <div class="item-actions">
                        <button type="button" class="action-btn move-to-cart">Move to Cart</button>
                        <span class="action-divider" aria-hidden="true">|</span>
                        <button type="button" class="action-btn delete-saved">Delete</button>
                    </div>
                </article>
                <article class="saved-item">
                    <div class="item-image">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200"
                            alt="LED Desk Lamp">
                    </div>
                    <div class="item-info">
                        <h3><a href="product-detail.php?id=10">LED Desk Lamp</a></h3>
                        <p class="item-price">$34.99</p>
                    </div>
                    <div class="item-actions">
                        <button type="button" class="action-btn move-to-cart">Move to Cart</button>
                        <span class="action-divider" aria-hidden="true">|</span>
                        <button type="button" class="action-btn delete-saved">Delete</button>
                    </div>
                </article>
            </div>
        </section>

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