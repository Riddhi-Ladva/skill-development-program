<?php require_once 'includes/index/logic.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EasyCart - Your one-stop shop for quality products at great prices">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.1'); ?>">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <main id="main-content">
        <!-- 
        Hero Section 
        Purpose: Visual hook for the user with current promotions.
        -->
        <section class="hero-section">
            <article class="hero-banner">
                <h2>Winter Sale - Up to 50% Off</h2>
                <p>Discover amazing deals on thousands of products</p>
                <a href="<?php echo url('products'); ?>" class="cta-button">Shop Now</a>
            </article>
            <img class="hero-img hero-img--left" src="<?= url('assets/img/hero-left.png') ?>" alt="Decorative left"
                aria-hidden="true">
            <img class="hero-img hero-img--right" src="<?= url('assets/img/hero-right.png') ?>" alt="Decorative right"
                aria-hidden="true">
        </section>

        <!-- 
        Category Navigation 
        Purpose: Quick links to filtered product listings.
        -->
        <section class="featured-categories">
            <h2>Shop by Category</h2>
            <div class="category-grid">
                <?php foreach ($categories as $slug => $category_info): ?>
                    <article class="category-card">
                        <h3><?php echo htmlspecialchars($category_info['name']); ?></h3>
                        <p><?php echo htmlspecialchars($category_info['description'] ?? 'Explore our collection'); ?></p>
                        <a href="<?php echo url('products?category=' . $slug); ?>">Explore
                            <?php echo htmlspecialchars($category_info['name']); ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="popular-brands">
            <div class="section-container">
                <h2>Popular Brands</h2>
                <div class="brand-grid">
                    <?php foreach ($brands as $id => $brand): ?>
                        <a href="<?php echo url('products?brand_id=' . $id); ?>" class="brand-card"
                            aria-label="View <?php echo htmlspecialchars($brand['name']); ?> products">
                            <img src="<?php echo url($brand['logo']); ?>"
                                alt="<?php echo htmlspecialchars($brand['name']); ?> Logo">
                            <span class="brand-name"><?php echo htmlspecialchars($brand['name']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- 
        Featured Products
        Purpose: Hardcoded selection of top-selling items to drive immediate engagement.
        -->

        <section class="featured-products">
            <h2>Featured Products</h2>
            <div class="product-grid">
                <?php foreach ($featured_products as $product): ?>
                    <article class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div
                            style="text-align: center; display: flex; flex-direction: column; flex-grow: 1; padding: var(--spacing-4) var(--spacing-4) var(--spacing-12) var(--spacing-4);">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <?php if (!empty($product['brand_name'])): ?>
                                <p class="product-brand"
                                    style="font-size: 0.9em; color: var(--color-text-muted); margin-bottom: 5px;">
                                    <?php echo htmlspecialchars($product['brand_name']); ?>
                                </p>
                            <?php endif; ?>
                            <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>

                            <div class="button-group">
                                <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                    🛒 Add to Cart
                                </button>
                                <a href="<?php echo url('product-detail?id=' . $product['id']); ?>"
                                    style="font-size: var(--font-size-xs); color: var(--color-primary); margin-top: 5px; display: block; text-decoration: none;">View
                                    Details</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="<?php echo url('products'); ?>" class="view-all-link">View All Products</a>
            </div>
        </section>

        <section class="promotional-banner">
            <article class="promo-content">
                <h2>Free Shipping on Orders Over $50</h2>
                <p>Shop now and save on delivery costs</p>
            </article>
        </section>

        <section class="info-sections">
            <article class="info-card">
                <h3>Fast Delivery</h3>
                <p>Get your orders delivered in 2-3 business days</p>
            </article>
            <article class="info-card">
                <h3>Secure Payment</h3>
                <p>Your transactions are safe and encrypted</p>
            </article>
            <article class="info-card">
                <h3>Easy Returns</h3>
                <p>30-day return policy on all products</p>
            </article>
            <article class="info-card">
                <h3>24/7 Support</h3>
                <p>Our team is always here to help you</p>
            </article>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="<?php echo asset('js/cart/add-to-cart.js'); ?>?v=<?php echo time(); ?>"></script>
</body>

</html>