<?php
/**
 * Product Listing Page
 * 
 * Responsibility: Displays a grid of products with filtering and sorting options.
 * 
 * Why it exists: To allow users to browse, search, and filter products to find what they want.
 * 
 * When it runs: When a user clicks "Shop Now", "Products", or a category link.
 */

// Load the bootstrap file for session and configuration
require_once '../includes/bootstrap/session.php';

// Data files (Database simulation)
require_once ROOT_PATH . '/data/products.php';
require_once ROOT_PATH . '/data/brands.php';
require_once ROOT_PATH . '/includes/shipping/services.php';

// Get filters from URL
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = $_GET['category'] ?? 'all';

// If category is an array (from multiple selection), default to 'all' for the header display
if (is_array($category)) {
    $category = 'all';
}

// Multi-value filters
$selected_categories = isset($_GET['category']) && is_array($_GET['category']) ? $_GET['category'] : [];
$selected_brands = isset($_GET['brand_id']) && is_array($_GET['brand_id']) ? $_GET['brand_id'] : [];
$selected_prices = isset($_GET['price']) && is_array($_GET['price']) ? $_GET['price'] : [];
$selected_rating = isset($_GET['rating']) ? (float) $_GET['rating'] : 0;
$selected_availability = isset($_GET['availability']) && is_array($_GET['availability']) ? $_GET['availability'] : [];
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';

// Backward compatibility for single values (header or brand section links)
if (isset($_GET['category']) && is_string($_GET['category']) && $_GET['category'] !== '') {
    if (!in_array($_GET['category'], $selected_categories)) {
        $selected_categories[] = $_GET['category'];
    }
}
if (isset($_GET['brand_id']) && is_string($_GET['brand_id']) && $_GET['brand_id'] !== '') {
    if (!in_array($_GET['brand_id'], $selected_brands)) {
        $selected_brands[] = (int) $_GET['brand_id'];
    }
}

// Ensure $brands is available for the closure
$all_brands = $brands ?? [];

// Updated Filtering Logic
if ($query !== '' || !empty($selected_categories) || !empty($selected_brands) || !empty($selected_prices) || $selected_rating > 0 || !empty($selected_availability)) {
    $products = array_filter($products, function ($product) use ($query, $selected_categories, $selected_brands, $selected_prices, $selected_rating, $selected_availability, $all_brands) {
        // 1. Search Query Filter
        if ($query !== '') {
            $brand_name = isset($all_brands[$product['brand_id']]) ? $all_brands[$product['brand_id']]['name'] : '';
            $search_text = $product['name'] . ' ' . $brand_name . ' ' . $product['category'];
            if (stripos($search_text, $query) === false)
                return false;
        }

        // 2. Category Filter
        if (!empty($selected_categories)) {
            $category_match = false;
            foreach ($selected_categories as $cat) {
                if (strtolower($product['category']) === strtolower($cat)) {
                    $category_match = true;
                    break;
                }
            }
            if (!$category_match)
                return false;
        }

        // 2.1 Brand Filter
        if (!empty($selected_brands)) {
            if (!in_array($product['brand_id'], $selected_brands)) {
                return false;
            }
        }

        // 3. Price Filter
        if (!empty($selected_prices)) {
            $price_match = false;
            foreach ($selected_prices as $range) {
                switch ($range) {
                    case '0-25':
                        if ($product['price'] < 25)
                            $price_match = true;
                        break;
                    case '25-50':
                        if ($product['price'] >= 25 && $product['price'] <= 50)
                            $price_match = true;
                        break;
                    case '50-100':
                        if ($product['price'] > 50 && $product['price'] <= 100)
                            $price_match = true;
                        break;
                    case '100-200':
                        if ($product['price'] > 100 && $product['price'] <= 200)
                            $price_match = true;
                        break;
                    case '200+':
                        if ($product['price'] > 200)
                            $price_match = true;
                        break;
                }
                if ($price_match)
                    break;
            }
            if (!$price_match)
                return false;
        }

        // 4. Rating Filter
        if ($selected_rating > 0 && $product['rating'] < $selected_rating) {
            return false;
        }

        // 5. Availability (Assume all products in data are in-stock for now)
        if (!empty($selected_availability)) {
            if (!in_array('in-stock', $selected_availability)) {
                return false;
            }
        }

        return true;
    });
}

// Sorting Logic
if (!empty($products) && $sort !== 'featured') {
    uasort($products, function ($a, $b) use ($sort) {
        switch ($sort) {
            case 'price-low':
                return $a['price'] <=> $b['price'];
            case 'price-high':
                return $b['price'] <=> $a['price'];
            case 'rating':
                return $b['rating'] <=> $a['rating'];
            case 'newest':
                return $b['id'] <=> $a['id'];
            default:
                return 0;
        }
    });
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse our wide selection of products at EasyCart">
    <title>Products - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.1'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/shipping-labels.css'); ?>">

<body>
    <?php include '../includes/header.php'; ?>

    <main id="main-content">
        <section class="page-header">
            <h1>
                <?php if ($query): ?>
                    Search Results for "<?php echo htmlspecialchars($query); ?>"
                <?php elseif ($category): ?>
                    <?php echo ucfirst(htmlspecialchars($category)); ?> Products
                <?php else: ?>
                    All Products
                <?php endif; ?>
            </h1>
            <p id="product-count-display">Showing <?php echo count($products); ?> products</p>
        </section>

        <div class="products-container">
            <aside class="filters-sidebar" aria-label="Product filters">
                <form action="products.php" method="GET" id="filter-form">
                    <!-- Persist search query -->
                    <?php if ($query): ?>
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($query); ?>">
                    <?php endif; ?>

                    <section class="filter-group">
                        <h2>Categories</h2>
                        <fieldset>
                            <legend class="visually-hidden">Select categories</legend>
                            <?php
                            $categories = ['electronics', 'clothing', 'home', 'sports', 'books'];
                            foreach ($categories as $cat):
                                $checked = in_array($cat, $selected_categories) ? 'checked' : '';
                                ?>
                                <label>
                                    <input type="checkbox" name="category[]" value="<?php echo $cat; ?>" <?php echo $checked; ?>>
                                    <?php echo ucfirst($cat === 'home' ? 'Home & Garden' : ($cat === 'sports' ? 'Sports & Outdoors' : $cat)); ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    </section>

                    <section class="filter-group">
                        <h2>Brands</h2>
                        <fieldset>
                            <legend class="visually-hidden">Select brands</legend>
                            <?php
                            foreach ($all_brands as $brand_id => $brand_info):
                                $checked = in_array($brand_id, $selected_brands) ? 'checked' : '';
                                ?>
                                <label>
                                    <input type="checkbox" name="brand_id[]" value="<?php echo $brand_id; ?>" <?php echo $checked; ?>>
                                    <?php echo htmlspecialchars($brand_info['name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    </section>

                    <section class="filter-group">
                        <h2>Price Range</h2>
                        <fieldset>
                            <legend class="visually-hidden">Select price range</legend>
                            <?php
                            $price_ranges = [
                                '0-25' => 'Under $25',
                                '25-50' => '$25 - $50',
                                '50-100' => '$50 - $100',
                                '100-200' => '$100 - $200',
                                '200+' => '$200 & Above'
                            ];
                            foreach ($price_ranges as $value => $label):
                                $checked = in_array($value, $selected_prices) ? 'checked' : '';
                                ?>
                                <label>
                                    <input type="checkbox" name="price[]" value="<?php echo $value; ?>" <?php echo $checked; ?>>
                                    <?php echo $label; ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                    </section>

                    <section class="filter-group">
                        <h2>Customer Rating</h2>
                        <fieldset>
                            <legend class="visually-hidden">Minimum rating</legend>
                            <?php
                            for ($i = 4; $i >= 2; $i--):
                                $checked = ($selected_rating == $i) ? 'checked' : '';
                                ?>
                                <label>
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" <?php echo $checked; ?>>
                                    <?php echo $i; ?> Stars & Up
                                </label>
                            <?php endfor; ?>
                        </fieldset>
                    </section>

                    <section class="filter-group">
                        <h2>Availability</h2>
                        <fieldset>
                            <legend class="visually-hidden">Stock status</legend>
                            <label>
                                <input type="checkbox" name="availability[]" value="in-stock" <?php echo in_array('in-stock', $selected_availability) ? 'checked' : ''; ?>>
                                In Stock
                            </label>
                            <label>
                                <input type="checkbox" name="availability[]" value="pre-order" <?php echo in_array('pre-order', $selected_availability) ? 'checked' : ''; ?>>
                                Pre-Order
                            </label>
                        </fieldset>
                    </section>

                    <section class="filter-group">
                        <h2>Shipping</h2>
                        <fieldset id="shipping-filters">
                            <legend class="visually-hidden">Filter by shipping eligibility</legend>
                            <?php $selected_shipping = isset($_GET['shipping']) && is_array($_GET['shipping']) ? $_GET['shipping'] : []; ?>
                            <label>
                                <input type="checkbox" id="filter-express" name="shipping[]" value="express" 
                                    <?php echo in_array('express', $selected_shipping) ? 'checked' : ''; ?>>
                                Express Eligible
                            </label>
                            <label>
                                <input type="checkbox" id="filter-freight" name="shipping[]" value="freight" 
                                    <?php echo in_array('freight', $selected_shipping) ? 'checked' : ''; ?>>
                                Freight Required
                            </label>
                        </fieldset>
                    </section>

                    <button type="submit" class="apply-filters-button">Apply Filters</button>
                    <a href="products.php" class="clear-filters-button"
                        style="text-decoration: none; display: inline-block; text-align: center; width: 100%; border: 1px solid var(--color-border); padding: var(--spacing-2); border-radius: var(--border-radius-md); margin-top: var(--spacing-2); color: var(--color-text-primary); font-weight: var(--font-weight-medium);">Clear
                        All</a>
                </form>
            </aside>

            <div class="products-main">
                <section class="results-header"
                    style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-6); padding-bottom: var(--spacing-4); border-bottom: 1px solid var(--color-border-light);">
                    <p class="product-count"
                        style="color: var(--color-text-secondary); font-size: var(--font-size-sm); font-weight: var(--font-weight-medium);">
                        Showing <strong><?php echo count($products); ?></strong> products
                    </p>
                    <section class="sorting-controls">
                        <label for="sort-select">Sort by:</label>
                        <select id="sort-select" name="sort" form="filter-form" onchange="this.form.submit()">
                            <option value="featured" <?php echo $sort === 'featured' ? 'selected' : ''; ?>>Featured
                            </option>
                            <option value="price-low" <?php echo $sort === 'price-low' ? 'selected' : ''; ?>>Price: Low to
                                High</option>
                            <option value="price-high" <?php echo $sort === 'price-high' ? 'selected' : ''; ?>>Price: High
                                to Low</option>
                            <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Customer Rating
                            </option>
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest Arrivals
                            </option>
                        </select>
                    </section>
                </section>

                <section class="product-listing">
                    <h2 class="visually-hidden">Product Results</h2>
                    <div class="product-grid">
                        <?php if (empty($products)): ?>
                            <div class="no-results-message"
                                style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: var(--bg-secondary); border-radius: var(--radius-lg);">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                                <h3>No products found</h3>
                                <p>We couldn't find any products matching "<?php echo htmlspecialchars($query); ?>".</p>
                                <p>Try checking your spelling or using more general keywords.</p>
                                <a href="products.php" class="btn btn-primary"
                                    style="display: inline-block; margin-top: 1rem; text-decoration: none; padding: 0.8rem 1.5rem; background: var(--primary-color); color: white; border-radius: var(--radius-md);">View
                                    All Products</a>
                            </div>
                        <?php else: ?>
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
                                    <?php
                                    $shipping = getShippingEligibility($product['price']);
                                    ?>
                                    <p class="shipping-info" style="margin: 5px 0;">
                                        <span class="shipping-label <?php echo $shipping['class']; ?>">
                                            <?php echo $shipping['icon']; ?>         <?php echo $shipping['label']; ?>
                                        </span>
                                    </p>
                                    <button class="add-to-cart-btn btn btn-primary"
                                        style="width: 100%; margin-top: 10px; padding: 8px; background: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer;"
                                        data-product-id="<?php echo $id; ?>">
                                        Add to Cart
                                    </button>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
    <script src="<?php echo asset('js/products/list.js'); ?>"></script>
    <script src="<?php echo asset('js/cart/add-to-cart.js'); ?>?v=<?php echo time(); ?>"></script>
</body>

</html>