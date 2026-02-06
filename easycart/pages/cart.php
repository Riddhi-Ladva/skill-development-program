<?php 
require_once '../includes/cart/logic.php'; 
// Auth guard removed to allow guest access
// require_once ROOT_PATH . '/includes/auth/guard.php';
// auth_guard();
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

            <?php if (!empty($cart_items)): ?>
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
                                <input type="radio" name="shipping" value="<?php echo htmlspecialchars($option['code']); ?>"
                                    <?php echo $current_method === $option['code'] ? 'checked' : ''; ?>
                                    <?php echo $is_disabled ? 'disabled' : ''; ?>>
                                <div class="option-details">
                                    <p class="option-name"><?php echo htmlspecialchars($option['title']); ?></p>
                                    <p class="option-time"><?php echo htmlspecialchars($option['time']); ?></p>
                                </div>
                                <p class="option-price">$<?php echo number_format($option['cost'], 2); ?></p>
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
                        <?php foreach ($payment_methods as $pm): ?>
                            <li><?php echo htmlspecialchars($pm['title']); ?></li>
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




        </div>

        <section class="cart-wishlist" id="wishlist-section">
            <div class="section-header">
                <h2>Your Wishlist</h2>
            </div>
            <div class="wishlist-carousel-container">
                <div class="wishlist-items" id="wishlist-items-container">
                    <?php
                    // FETCH WISHLIST ITEMS (DB)
                    $wishlist_items = [];
                    if (isset($_SESSION['user_id'])) {
                        if (!function_exists('get_user_wishlist_details')) {
                            require_once ROOT_PATH . '/includes/db_functions.php';
                        }
                        $wishlist_items = get_user_wishlist_details($_SESSION['user_id']);
                    }
                    ?>
                    
                    <!-- If SSR found items (User), render them. JS will take over for Guest or manipulations. -->
                    <?php if (empty($wishlist_items)): ?>
                        <!-- Empty state rendered by PHP initially for guests too. JS will replace if it finds LS data. -->
                        <div class="wishlist-empty">Your wishlist is empty. Items you save will appear here.</div>
                    <?php else: ?>
                        <?php foreach ($wishlist_items as $w_item): ?>
                        <article class="wishlist-card" data-product-id="<?php echo $w_item['id']; ?>">
                            <div class="item-image">
                                <img src="<?php echo htmlspecialchars($w_item['image']); ?>" alt="<?php echo htmlspecialchars($w_item['name']); ?>">
                            </div>
                            <div class="item-info">
                                <h3><a href="product-detail.php?id=<?php echo $w_item['id']; ?>"><?php echo htmlspecialchars($w_item['name']); ?></a></h3>
                                <p class="item-price">$<?php echo number_format($w_item['price'], 2); ?></p>
                                <p class="item-stock" style="font-size: 0.8em; color: <?php echo $w_item['is_in_stock'] ? 'green' : 'red'; ?>;">
                                    <?php echo $w_item['is_in_stock'] ? 'In Stock' : 'Out of Stock'; ?>
                                </p>
                            </div>
                            <div class="item-actions">
                                <button type="button" class="action-btn add-to-cart-from-wishlist" 
                                        data-id="<?php echo $w_item['id']; ?>"
                                        <?php echo $w_item['is_in_stock'] ? '' : 'disabled'; ?>>
                                    Add to Cart
                                </button>
                                <button type="button" class="remove-wishlist" data-id="<?php echo $w_item['id']; ?>">Remove</button>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>


        <section class="recommended-products">
            <h2>Frequently Bought Together</h2>
            <div class="product-grid">
                <?php foreach ($recommended_products as $rec_prod): ?>
                <article class="product-card">
                    <img src="<?php echo htmlspecialchars($rec_prod['image']); ?>" alt="<?php echo htmlspecialchars($rec_prod['name']); ?>">
                    <h3><a href="product-detail.php?id=<?php echo $rec_prod['id']; ?>"><?php echo htmlspecialchars($rec_prod['name']); ?></a></h3>
                    <p class="product-price">$<?php echo number_format($rec_prod['price'], 2); ?></p>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <script>
        window.allProducts = <?php echo json_encode($products); ?>;
        window.shippingPrice = <?php echo isset($_SESSION['shipping_price']) ? $_SESSION['shipping_price'] : 0; ?>;
    </script>


    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo asset('js/cart/summary.js?v=2'); ?>"></script>
    <script src="<?php echo asset('js/cart/quantity.js?v=2'); ?>"></script>
    <script src="<?php echo asset('js/cart/shipping.js?v=2'); ?>"></script>
    <script src="<?php echo asset('js/cart/promo.js?v=2'); ?>"></script>
</body>

</html>