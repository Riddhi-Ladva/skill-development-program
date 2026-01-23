<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../data/products.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse our wide selection of products at EasyCart">
    <title>Products - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.1'); ?>">

<body>
    <?php include '../includes/header.php'; ?>

    <main id="main-content">
        <section class="page-header">
            <h1>All Products</h1>
            <p id="product-count-display">Showing <?php echo count($products); ?> products</p>
        </section>

        <div class="products-container">
            <aside class="filters-sidebar" aria-label="Product filters">
                <section class="filter-group">
                    <h2>Categories</h2>
                    <form>
                        <fieldset>
                            <legend>Select categories</legend>
                            <label>
                                <input type="checkbox" name="category" value="electronics">
                                Electronics
                            </label>
                            <label>
                                <input type="checkbox" name="category" value="clothing">
                                Clothing
                            </label>
                            <label>
                                <input type="checkbox" name="category" value="home">
                                Home & Garden
                            </label>
                            <label>
                                <input type="checkbox" name="category" value="sports">
                                Sports & Outdoors
                            </label>
                            <label>
                                <input type="checkbox" name="category" value="books">
                                Books
                            </label>
                        </fieldset>
                    </form>
                </section>

                <section class="filter-group">
                    <h2>Price Range</h2>
                    <form>
                        <fieldset>
                            <legend>Select price range</legend>
                            <label>
                                <input type="checkbox" name="price" value="0-25">
                                Under $25
                            </label>
                            <label>
                                <input type="checkbox" name="price" value="25-50">
                                $25 - $50
                            </label>
                            <label>
                                <input type="checkbox" name="price" value="50-100">
                                $50 - $100
                            </label>
                            <label>
                                <input type="checkbox" name="price" value="100-200">
                                $100 - $200
                            </label>
                            <label>
                                <input type="checkbox" name="price" value="200+">
                                $200 & Above
                            </label>
                        </fieldset>
                    </form>
                </section>

                <section class="filter-group">
                    <h2>Customer Rating</h2>
                    <form>
                        <fieldset>
                            <legend>Minimum rating</legend>
                            <label>
                                <input type="radio" name="rating" value="4">
                                4 Stars & Up
                            </label>
                            <label>
                                <input type="radio" name="rating" value="3">
                                3 Stars & Up
                            </label>
                            <label>
                                <input type="radio" name="rating" value="2">
                                2 Stars & Up
                            </label>
                        </fieldset>
                    </form>
                </section>

                <section class="filter-group">
                    <h2>Availability</h2>
                    <form>
                        <fieldset>
                            <legend>Stock status</legend>
                            <label>
                                <input type="checkbox" name="availability" value="in-stock">
                                In Stock
                            </label>
                            <label>
                                <input type="checkbox" name="availability" value="pre-order">
                                Pre-Order
                            </label>
                        </fieldset>
                    </form>
                </section>

                <button type="button" class="apply-filters-button">Apply Filters</button>
                <button type="button" class="clear-filters-button">Clear All</button>
            </aside>

            <div class="products-main">
                <section class="sorting-controls">
                    <label for="sort-select">Sort by:</label>
                    <select id="sort-select" name="sort">
                        <option value="featured">Featured</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="rating">Customer Rating</option>
                        <option value="newest">Newest Arrivals</option>
                    </select>
                </section>

                <section class="product-listing">
                    <h2 class="visually-hidden">Product Results</h2>
                    <div class="product-grid">
                        <?php foreach ($products as $id => $product): ?>
                            <article class="product-card">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <h3><a
                                        href="product-detail.php?id=<?php echo $id; ?>"><?php echo htmlspecialchars($product['name']); ?></a>
                                </h3>
                                <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
                                <p class="product-rating"><?php echo $product['rating']; ?> stars
                                    (<?php echo number_format($product['reviews']); ?> reviews)</p>
                                <p class="product-shipping"><?php echo htmlspecialchars($product['shipping']); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <nav class="pagination" aria-label="Product pages">
                    <ul>
                        <li><a href="products.php?page=1" aria-current="page">1</a></li>
                        <li><a href="products.php?page=2">2</a></li>
                        <li><a href="products.php?page=3">3</a></li>
                        <li><a href="products.php?page=4">4</a></li>
                        <li><a href="products.php?page=5">5</a></li>
                        <li><a href="products.php?page=2" aria-label="Next page">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo asset('js/products.js'); ?>"></script>
</body>

</html>