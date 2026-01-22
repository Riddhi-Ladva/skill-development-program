<?php
require_once '../includes/session.php';
require_once '../data/products.php';
require_once '../data/brands.php';
require_once '../data/categories.php';

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = isset($products[$product_id]) ? $products[$product_id] : null;

if (!$product) {
    header('Location: products.php');
    exit;
}

$brand = isset($brands[$product['brand_id']]) ? $brands[$product['brand_id']] : ['name' => 'Generic'];
$category = isset($categories[$product['category']]) ? $categories[$product['category']] : ['name' => 'Uncategorized'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="<?php echo htmlspecialchars($product['name'] . ' - ' . $product['description']); ?>">
    <title><?php echo htmlspecialchars($product['name']); ?> - EasyCart</title>
    <link rel="stylesheet" href="/easycart/css/main.css?v=1.1">

<body>
    <?php include '../includes/header.php'; ?>

    <main id="main-content">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="../index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a
                        href="products.php?category=<?php echo urlencode($product['category']); ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                </li>
                <li aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
            </ol>
        </nav>

        <article class="product-detail">
            <div class="product-images">
                <section class="main-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?> - Main view">
                </section>
                <section class="thumbnail-gallery">
                    <h2 class="visually-hidden">Product Images</h2>
                    <button type="button">
                        <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=200"
                            alt="Headphones front view thumbnail">
                    </button>
                    <button type="button">
                        <img src="https://images.unsplash.com/photo-1484704849700-f032a568e944?w=200"
                            alt="Headphones side view thumbnail">
                    </button>
                    <button type="button">
                        <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?w=200"
                            alt="Headphones folded thumbnail">
                    </button>
                    <button type="button">
                        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=200"
                            alt="Headphones with case thumbnail">
                    </button>
                </section>
            </div>

            <div class="product-info">
                <header class="product-header">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="product-brand">Brand: <?php echo htmlspecialchars($brand['name']); ?></p>
                    <div class="product-rating">
                        <p><?php echo $product['rating']; ?> out of 5 stars</p>
                        <a href="#reviews"><?php echo number_format($product['reviews']); ?> customer reviews</a>
                    </div>
                </header>

                <section class="product-pricing">
                    <h2 class="visually-hidden">Pricing Information</h2>
                    <p class="current-price">$<?php echo number_format($product['price'], 2); ?></p>
                    <p class="original-price">$129.99</p>
                    <p class="discount-badge">Save 38%</p>
                    <p class="stock-status">In Stock</p>
                    <p class="shipping-info">Free shipping on orders over $50</p>
                </section>

                <section class="product-actions">
                    <h2 class="visually-hidden">Purchase Actions</h2>
                    <form action="add-to-cart.php" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="quantity-input" style="margin-bottom: 10px;">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="10">
                        </div>
                        <button type="submit" class="add-to-cart">🛒 Add to Cart</button>
                    </form>
                    <button type="button" class="buy-now-button">Buy Now</button>
                    <button type="button" class="wishlist-button">Add to Wishlist</button>
                </section>

                <section class="delivery-info">
                    <h2>Delivery Options</h2>
                    <dl>
                        <dt>Standard Shipping (5-7 business days)</dt>
                        <dd>Free</dd>
                        <dt>Express Shipping (2-3 business days)</dt>
                        <dd>$9.99</dd>
                        <dt>Next Day Delivery</dt>
                        <dd>$19.99</dd>
                    </dl>
                    <p class="return-policy">30-day return policy. <a href="#">Learn more</a></p>
                </section>
            </div>
        </article>

        <section class="product-details-tabs">
            <h2 class="visually-hidden">Product Information</h2>
            <div class="tabs-navigation">
                <button type="button" class="tab-button active" aria-selected="true">Description</button>
                <button type="button" class="tab-button">Specifications</button>
                <button type="button" class="tab-button">Reviews</button>
            </div>

            <div class="tab-content">
                <section id="description" class="tab-panel active">
                    <h3>Product Description</h3>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                    <h4>Key Features</h4>
                    <ul>
                        <li>Active Noise Cancellation (ANC) technology</li>
                        <li>Bluetooth 5.0 for stable connectivity</li>
                        <li>30-hour battery life with ANC on</li>
                        <li>Comfortable memory foam ear cushions</li>
                        <li>Foldable design with carrying case</li>
                        <li>Built-in microphone for hands-free calls</li>
                        <li>Compatible with voice assistants</li>
                    </ul>
                </section>

                <section id="specifications" class="tab-panel">
                    <h3>Technical Specifications</h3>
                    <table>
                        <tbody>
                            <tr>
                                <th>Driver Size</th>
                                <td>40mm</td>
                            </tr>
                            <tr>
                                <th>Frequency Response</th>
                                <td>20Hz - 20kHz</td>
                            </tr>
                            <tr>
                                <th>Impedance</th>
                                <td>32 Ohm</td>
                            </tr>
                            <tr>
                                <th>Bluetooth Version</th>
                                <td>5.0</td>
                            </tr>
                            <tr>
                                <th>Wireless Range</th>
                                <td>33 feet (10 meters)</td>
                            </tr>
                            <tr>
                                <th>Battery Type</th>
                                <td>Rechargeable Lithium-ion</td>
                            </tr>
                            <tr>
                                <th>Charging Time</th>
                                <td>2.5 hours</td>
                            </tr>
                            <tr>
                                <th>Weight</th>
                                <td>8.8 oz (250g)</td>
                            </tr>
                            <tr>
                                <th>Color Options</th>
                                <td>Black, White, Blue, Red</td>
                            </tr>
                            <tr>
                                <th>Package Contents</th>
                                <td>Headphones, USB-C charging cable, 3.5mm audio cable, carrying case, user manual</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section id="reviews" class="tab-panel">
                    <h3>Customer Reviews</h3>
                    <div class="reviews-summary">
                        <p class="average-rating">4.5 out of 5 stars</p>
                        <p>Based on 245 reviews</p>
                        <dl class="rating-breakdown">
                            <dt>5 stars</dt>
                            <dd>165 reviews (67%)</dd>
                            <dt>4 stars</dt>
                            <dd>50 reviews (20%)</dd>
                            <dt>3 stars</dt>
                            <dd>20 reviews (8%)</dd>
                            <dt>2 stars</dt>
                            <dd>7 reviews (3%)</dd>
                            <dt>1 star</dt>
                            <dd>3 reviews (1%)</dd>
                        </dl>
                    </div>

                    <article class="review-item">
                        <header class="review-header">
                            <p class="reviewer-name">John D.</p>
                            <p class="review-rating">5 out of 5 stars</p>
                            <time datetime="2026-01-15">January 15, 2026</time>
                        </header>
                        <h4 class="review-title">Amazing sound quality!</h4>
                        <p class="review-text">These headphones exceeded my expectations. The noise cancellation is
                            fantastic, and the battery lasts all day. Highly recommended!</p>
                        <p class="review-helpful">23 people found this helpful</p>
                    </article>

                    <article class="review-item">
                        <header class="review-header">
                            <p class="reviewer-name">Sarah M.</p>
                            <p class="review-rating">4 out of 5 stars</p>
                            <time datetime="2026-01-10">January 10, 2026</time>
                        </header>
                        <h4 class="review-title">Great value for money</h4>
                        <p class="review-text">Very comfortable for long listening sessions. The only downside is
                            they're a bit bulky for travel, but the sound quality makes up for it.</p>
                        <p class="review-helpful">15 people found this helpful</p>
                    </article>

                    <article class="review-item">
                        <header class="review-header">
                            <p class="reviewer-name">Mike R.</p>
                            <p class="review-rating">5 out of 5 stars</p>
                            <time datetime="2026-01-05">January 5, 2026</time>
                        </header>
                        <h4 class="review-title">Perfect for work from home</h4>
                        <p class="review-text">The microphone quality is excellent for video calls. Battery life is as
                            advertised. Very happy with this purchase.</p>
                        <p class="review-helpful">12 people found this helpful</p>
                    </article>

                    <button type="button" class="load-more-reviews">Load More Reviews</button>
                </section>
            </div>
        </section>

        <section class="related-products">
            <h2>You May Also Like</h2>
            <div class="product-grid">
                <article class="product-card">
                    <img src="https://images.unsplash.com/photo-1572569511254-d8f925fe2cbb?w=400"
                        alt="Wireless Earbuds">
                    <h3><a href="product-detail.php?id=13">Wireless Earbuds</a></h3>
                    <p class="product-price">$49.99</p>
                    <p class="product-rating">4.4 stars (567 reviews)</p>
                </article>
                <article class="product-card">
                    <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400" alt="Headphone Stand">
                    <h3><a href="product-detail.php?id=14">Headphone Stand</a></h3>
                    <p class="product-price">$19.99</p>
                    <p class="product-rating">4.7 stars (234 reviews)</p>
                </article>
                <article class="product-card">
                    <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400"
                        alt="Audio Cable Premium">
                    <h3><a href="product-detail.php?id=15">Audio Cable Premium</a></h3>
                    <p class="product-price">$12.99</p>
                    <p class="product-rating">4.6 stars (189 reviews)</p>
                </article>
                <article class="product-card">
                    <img src="https://images.unsplash.com/photo-1484704849700-f032a568e944?w=400" alt="Headphone Case">
                    <h3><a href="product-detail.php?id=16">Headphone Case</a></h3>
                    <p class="product-price">$14.99</p>
                    <p class="product-rating">4.5 stars (312 reviews)</p>
                </article>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>