<?php
require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="EasyCart - Your one-stop shop for quality products at great prices">
    <title>EasyCart - Home</title>
    <link rel="stylesheet" href="/easycart/css/main.css?v=1.1">

<body>
    <?php include 'includes/header.php'; ?>

    <main id="main-content">
        <section class="hero-section">
            <article class="hero-banner">
                <h2>Summer Sale - Up to 50% Off</h2>
                <p>Discover amazing deals on thousands of products</p>
                <a href="/easycart/pages/products.php" class="cta-button">Shop Now</a>
            </article>
        </section>

        <section class="featured-categories">
            <h2>Shop by Category</h2>
            <div class="category-grid">
                <article class="category-card">
                    <h3>Electronics</h3>
                    <p>Latest gadgets and tech</p>
                    <a href="/easycart/pages/products.php?category=electronics">Explore Electronics</a>
                </article>
                <article class="category-card">
                    <h3>Clothing</h3>
                    <p>Fashion for everyone</p>
                    <a href="/easycart/pages/products.php?category=clothing">Explore Clothing</a>
                </article>
                <article class="category-card">
                    <h3>Home & Garden</h3>
                    <p>Make your space beautiful</p>
                    <a href="/easycart/pages/products.php?category=home">Explore Home</a>
                </article>
                <article class="category-card">
                    <h3>Sports & Outdoors</h3>
                    <p>Gear for active lifestyle</p>
                    <a href="/easycart/pages/products.php?category=sports">Explore Sports</a>
                </article>
            </div>
        </section>

        <section class="featured-products">
            <h2>Featured Products</h2>
            <div class="product-grid">
                <article class="product-card">
                    <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400"
                        alt="Wireless Headphones">
                    <h3>Wireless Headphones</h3>
                    <p class="product-price">$79.99</p>
                    <p class="product-rating">4.5 stars (245 reviews)</p>
                    <a href="/easycart/pages/product-detail.php?id=1">View Details</a>
                </article>
                <article class="product-card">
                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400" alt="Smart Watch">
                    <h3>Smart Watch</h3>
                    <p class="product-price">$199.99</p>
                    <p class="product-rating">4.8 stars (892 reviews)</p>
                    <a href="/easycart/pages/product-detail.php?id=2">View Details</a>
                </article>
                <article class="product-card">
                    <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400" alt="Running Shoes">
                    <h3>Running Shoes</h3>
                    <p class="product-price">$89.99</p>
                    <p class="product-rating">4.6 stars (523 reviews)</p>
                    <a href="/easycart/pages/product-detail.php?id=3">View Details</a>
                </article>
                <article class="product-card">
                    <img src="https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=400" alt="Coffee Maker">
                    <h3>Coffee Maker</h3>
                    <p class="product-price">$129.99</p>
                    <p class="product-rating">4.7 stars (334 reviews)</p>
                    <a href="/easycart/pages/product-detail.php?id=4">View Details</a>
                </article>
            </div>
            <a href="/easycart/pages/products.php" class="view-all-link">View All Products</a>
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
</body>

</html>