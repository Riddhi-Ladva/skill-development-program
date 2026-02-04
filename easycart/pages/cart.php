<?php 
require_once '../includes/cart/logic.php'; 
require_once ROOT_PATH . '/includes/auth/guard.php';
auth_guard();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your EasyCart shopping cart">
    <title>Shopping Cart - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.1'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/shipping-labels.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/shipping.css'); ?>">
</head>

<body>
    <!-- HEADER ADDED: consistent site header (logo + standard navigation) -->
    <?php include '../includes/header.php'; ?>

    <main id="main-content">
        <section class="page-header">
            <h1>Shopping Cart</h1>
            <p><span id="cart-page-count"><?php echo $total_items; ?></span>
                <span id="cart-page-text">item<?php echo $total_items != 1 ? 's' : ''; ?></span> in your cart
            </p>
        </section>

        <div class="cart-container">
            <section class="cart-items">
                <h2 class="visually-hidden">Cart Items</h2>

                <?php if (empty($cart_items)): ?>
                    <p>Your cart is empty. <a href="products.php">Start shopping!</a></p>
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
                                <h3><a
                                        href="product-detail.php?id=<?php echo $id; ?>"><?php echo htmlspecialchars($products[$id]['name']); ?></a>
                                </h3>
                                <p class="item-brand">Brand: <?php echo htmlspecialchars($brand_name); ?></p>

                                <p class="item-stock">In Stock</p>
                                <p class="shipping-eligibility">
                                    <span class="shipping-label <?php echo $item['shipping_eligibility']['class']; ?>">
                                        <?php echo $item['shipping_eligibility']['icon']; ?> <?php echo $item['shipping_eligibility']['label']; ?>
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
                                    $<?php echo number_format($item['price'], 2); ?> each</p>

                                <p class="total-price" data-item-total>
                                    $<?php echo number_format($item['final_total'], 2); ?>
                                </p>

                                <p class="item-discount"
                                    style="<?php echo $item['discount_amount'] > 0 ? '' : 'display:none;'; ?>">
                                    Discount (<?php echo $item['discount_percent']; ?>%):
                                    -$<?php echo number_format($item['discount_amount'], 2); ?>
                                </p>
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
                            $<?php echo number_format($shipping, 2); ?>
                        </dd>
                        
                        <!-- PROMO DISCOUNT ROW -->
                        <dt id="promo-row-label" style="<?php echo $promo_discount > 0 ? '' : 'display:none;'; ?>">Promo Discount:</dt>
                        <dd id="promo-row-amount" class="discount-text" style="<?php echo $promo_discount > 0 ? '' : 'display:none;'; ?>">-$<?php echo number_format($promo_discount, 2); ?></dd>

                        <dt>Tax (18%):</dt>
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
                            <?php
                            $current_method = $_SESSION['shipping_method'];

                            // Calculate dynamic prices for display
                            $standard_price = 40;
                            $express_price = min(80, $subtotal * 0.10);
                            $white_glove_price = min(150, $subtotal * 0.05);
                            $freight_price = max(200, $subtotal * 0.03);
                            ?>

                            <label class="shipping-option <?php echo $requires_freight ? 'is-disabled' : ''; ?>">
                                <input type="radio" name="shipping" value="standard" 
                                    <?php echo $current_method === 'standard' ? 'checked' : ''; ?>
                                    <?php echo $requires_freight ? 'disabled' : ''; ?>>
                                <div class="option-details">
                                    <p class="option-name">Standard Shipping</p>
                                    <p class="option-time">5–7 business days</p>
                                </div>
                                <p class="option-price">$<?php echo number_format($standard_price, 2); ?></p>
                            </label>

                            <label class="shipping-option <?php echo $requires_freight ? 'is-disabled' : ''; ?>">
                                <input type="radio" name="shipping" value="express" 
                                    <?php echo $current_method === 'express' ? 'checked' : ''; ?>
                                    <?php echo $requires_freight ? 'disabled' : ''; ?>>
                                <div class="option-details">
                                    <p class="option-name">Express Shipping</p>
                                    <p class="option-time">2–3 business days</p>
                                </div>
                                <p class="option-price">$<?php echo number_format($express_price, 2); ?></p>
                            </label>

                            <label class="shipping-option <?php echo !$requires_freight ? 'is-disabled' : ''; ?>">
                                <input type="radio" name="shipping" value="white-glove" 
                                    <?php echo $current_method === 'white-glove' ? 'checked' : ''; ?>
                                    <?php echo !$requires_freight ? 'disabled' : ''; ?>>
                                <div class="option-details">
                                    <p class="option-name">White Glove Delivery</p>
                                    <p class="option-time">7–10 business days</p>
                                </div>
                                <p class="option-price">$<?php echo number_format($white_glove_price, 2); ?></p>
                            </label>

                            <label class="shipping-option <?php echo !$requires_freight ? 'is-disabled' : ''; ?>">
                                <input type="radio" name="shipping" value="freight" 
                                    <?php echo $current_method === 'freight' ? 'checked' : ''; ?>
                                    <?php echo !$requires_freight ? 'disabled' : ''; ?>>
                                <div class="option-details">
                                    <p class="option-name">Freight Shipping</p>
                                    <p class="option-time">10–14 business days</p>
                                </div>
                                <p class="option-price">$<?php echo number_format($freight_price, 2); ?></p>
                            </label>
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
                                       value="<?php echo htmlspecialchars($active_code); ?>" 
                                       <?php echo $active_code ? 'disabled' : ''; ?>>
                                
                                <button type="button" id="apply-promo-btn" style="<?php echo $active_code ? 'display:none;' : ''; ?>">Apply</button>
                                <button type="button" id="remove-promo-btn" class="remove-btn" style="<?php echo $active_code ? '' : 'display:none;'; ?>">Remove</button>
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

    <script>
        window.allProducts = <?php echo json_encode($products); ?>;
        window.shippingPrice = <?php echo isset($_SESSION['shipping_price']) ? $_SESSION['shipping_price'] : 0; ?>;
    </script>


    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo asset('js/wishlist/wishlist.js?v=2'); ?>"></script>
    <script src="<?php echo asset('js/cart/summary.js?v=2'); ?>"></script>
    <script src="<?php echo asset('js/cart/quantity.js?v=2'); ?>"></script>
    <script src="<?php echo asset('js/cart/shipping.js?v=2'); ?>"></script>
    <script src="<?php echo asset('js/cart/promo.js?v=2'); ?>"></script>
</body>

</html>