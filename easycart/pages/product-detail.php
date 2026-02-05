<?php require_once '../includes/product-detail/logic.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="<?php echo htmlspecialchars($product['name'] . ' - ' . $product['description']); ?>">
    <title>
        <?php echo htmlspecialchars($product['name']); ?> - EasyCart
    </title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.1'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/components/shipping-labels.css'); ?>">
    <style>
        .thumbnail-gallery button.active {
            border-color: var(--color-primary);
        }

        /* Delivery Selection Highlighting */
        .delivery-option {
            border: 1px solid var(--color-border-light);
            border-radius: var(--border-radius-md);
            padding: var(--spacing-3);
            margin-bottom: var(--spacing-2);
            transition: all var(--transition-fast);
            cursor: pointer;
        }

        .delivery-option:hover {
            border-color: var(--color-primary);
        }

        .delivery-option.selected {
            background-color: rgba(37, 99, 235, 0.05);
            border-color: var(--color-primary);
            box-shadow: 0 0 0 1px var(--color-primary);
        }

        .delivery-option label {
            display: block;
            cursor: pointer;
            margin: 0;
            width: 100%;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <main id="main-content">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="<?php echo url('index.php'); ?>">Home</a></li>
                <li><a href="<?php echo url('pages/products.php'); ?>">Products</a></li>
                <li><a href="products.php?category=<?php echo urlencode($product['category']); ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                </li>
                <li aria-current="page">
                    <?php echo htmlspecialchars($product['name']); ?>
                </li>
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
                    <button type="button" class="active">
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
                    <h1>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h1>
                    <p class="product-brand">Brand:
                        <?php echo htmlspecialchars($brand['name']); ?>
                    </p>
                </header>

                <section class="product-pricing">
                    <h2 class="visually-hidden">Pricing Information</h2>
                    <p class="current-price">$
                        <?php echo number_format($product['price'], 2); ?>
                    </p>
                    <p class="original-price">$129.99</p>
                    <p class="discount-badge">Save 38%</p>
                    <p class="stock-status">In Stock</p>
                    <?php
                    $shipping = getShippingEligibility($product['price']);
                    ?>
                    <p class="shipping-info">
                        <span class="shipping-label <?php echo $shipping['class']; ?>">
                            <?php echo $shipping['icon']; ?>
                            <?php echo $shipping['label']; ?>
                        </span>
                    </p>
                </section>

                <section class="product-actions">
                    <h2 class="visually-hidden">Purchase Actions</h2>
                    <form onsubmit="return false;">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="quantity-input" style="margin-bottom: 10px;">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="10">
                        </div>
                        <button type="button" class="add-to-cart add-to-cart-trigger"
                            data-product-id="<?php echo $product_id; ?>">🛒 Add to Cart</button>
                    </form>
                    <button type="button" class="buy-now-button">Buy Now</button>
                    <button type="button" class="wishlist-button" data-product-id="<?php echo $product_id; ?>">
                        <span class="heart-icon" aria-hidden="true"></span>
                        <span class="button-text">Add to Wishlist</span>
                    </button>
                </section>
            </div>
        </article>

        <section class="product-details-tabs">
            <h2 class="visually-hidden">Product Information</h2>
            <div class="tabs-navigation">
                <button type="button" class="tab-button active" aria-selected="true"
                    data-tab="description">Description</button>
                <button type="button" class="tab-button" data-tab="specifications">Specifications</button>
            </div>

            <div class="tab-content">
                <section id="description" class="tab-panel active">
                    <h3>Product Description</h3>
                    <p>
                        <?php echo htmlspecialchars($product['description']); ?>
                    </p>
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

            </div>
        </section>

        <section class="related-products">
            <h2>You May Also Like</h2>
            <div class="product-grid">
                <?php foreach ($related_products as $rel_product): ?>
                    <?php if ($rel_product['id'] == $product_id)
                        continue; ?>
                    <article class="product-card">
                        <img src="<?php echo htmlspecialchars($rel_product['image']); ?>"
                            alt="<?php echo htmlspecialchars($rel_product['name']); ?>">
                        <h3><a
                                href="product-detail.php?id=<?php echo $rel_product['id']; ?>"><?php echo htmlspecialchars($rel_product['name']); ?></a>
                        </h3>
                        <p class="product-price">$<?php echo number_format($rel_product['price'], 2); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo asset('js/products/detail.js'); ?>"></script>
    <script src="<?php echo asset('js/cart/add-to-cart.js'); ?>?v=<?php echo time(); ?>"></script>
</body>

</html>