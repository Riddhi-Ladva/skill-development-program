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

                <?php include ROOT_PATH . '/includes/cart/components/cart-items.php'; ?>
            </section>

            <?php include ROOT_PATH . '/includes/cart/components/cart-summary.php'; ?>




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
                            require_once ROOT_PATH . '/includes/db-functions.php';
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
                                    <img src="<?php echo htmlspecialchars($w_item['image']); ?>"
                                        alt="<?php echo htmlspecialchars($w_item['name']); ?>">
                                </div>
                                <div class="item-info">
                                    <h3><a
                                            href="<?php echo url('product-detail?id=' . $w_item['id']); ?>"><?php echo htmlspecialchars($w_item['name']); ?></a>
                                    </h3>
                                    <p class="item-price">$<?php echo number_format($w_item['price'], 2); ?></p>
                                    <p class="item-stock"
                                        style="font-size: 0.8em; color: <?php echo $w_item['is_in_stock'] ? 'green' : 'red'; ?>;">
                                        <?php echo $w_item['is_in_stock'] ? 'In Stock' : 'Out of Stock'; ?>
                                    </p>
                                </div>
                                <div class="item-actions">
                                    <button type="button" class="action-btn add-to-cart-from-wishlist"
                                        data-id="<?php echo $w_item['id']; ?>" <?php echo $w_item['is_in_stock'] ? '' : 'disabled'; ?>>
                                        Add to Cart
                                    </button>
                                    <button type="button" class="remove-wishlist"
                                        data-id="<?php echo $w_item['id']; ?>">Remove</button>
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
                        <img src="<?php echo htmlspecialchars($rec_prod['image']); ?>"
                            alt="<?php echo htmlspecialchars($rec_prod['name']); ?>">
                        <h3><a
                                href="<?php echo url('product-detail?id=' . $rec_prod['id']); ?>"><?php echo htmlspecialchars($rec_prod['name']); ?></a>
                        </h3>
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