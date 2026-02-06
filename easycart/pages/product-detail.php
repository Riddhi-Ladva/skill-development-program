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
                    <img src="<?php echo htmlspecialchars($gallery[0]['image_path'] ?? $product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?> - Main view" id="featured-image">
                </section>
                <section class="thumbnail-gallery">
                    <h2 class="visually-hidden">Product Images</h2>
                    <?php foreach ($gallery as $index => $img): ?>
                        <button type="button" class="<?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($img['image_path']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?> thumbnail <?php echo $index + 1; ?>">
                        </button>
                    <?php endforeach; ?>
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
                    <p class="current-price">$<?php echo number_format($product['price'], 2); ?></p>

                    <?php if (!empty($product['original_price']) && $product['original_price'] > $product['price']): ?>
                        <p class="original-price">$<?php echo number_format($product['original_price'], 2); ?></p>
                        <?php
                        $saving = $product['original_price'] - $product['price'];
                        $percentage = round(($saving / $product['original_price']) * 100);
                        ?>
                        <p class="discount-badge">Save <?php echo $percentage; ?>%</p>
                    <?php endif; ?>

                    <?php if ($product['is_in_stock']): ?>
                        <p class="stock-status in-stock">In Stock (<?php echo $product['stock_qty']; ?> available)</p>
                    <?php else: ?>
                        <p class="stock-status out-of-stock">Out of Stock</p>
                    <?php endif; ?>

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
                            <input type="number" id="quantity" name="quantity" value="1" min="1"
                                max="<?php echo max(1, $product['stock_qty']); ?>" <?php echo !$product['is_in_stock'] ? 'disabled' : ''; ?>>
                        </div>
                        <button type="button" class="add-to-cart add-to-cart-trigger"
                            data-product-id="<?php echo $product_id; ?>" <?php echo !$product['is_in_stock'] ? 'disabled' : ''; ?>>
                            <?php echo $product['is_in_stock'] ? '🛒 Add to Cart' : 'Out of Stock'; ?>
                        </button>
                    </form>
                    <?php if ($product['is_in_stock']): ?>
                        <button type="button" class="buy-now-button add-to-cart-trigger"
                            data-product-id="<?php echo $product_id; ?>">Buy Now</button>
                    <?php endif; ?>
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
                        <?php
                        $features = !empty($product['features']) ? json_decode($product['features'], true) : [];
                        if (!empty($features) && is_array($features)):
                            foreach ($features as $feature):
                                ?>
                                <li><?php echo htmlspecialchars($feature); ?></li>
                                <?php
                            endforeach;
                        else:
                            ?>
                            <li>No specific features listed.</li>
                        <?php endif; ?>
                    </ul>
                </section>

                <section id="specifications" class="tab-panel">
                    <h3>Technical Specifications</h3>
                    <table>
                        <tbody>
                            <?php
                            $specs = !empty($product['specifications']) ? json_decode($product['specifications'], true) : [];
                            if (!empty($specs) && is_array($specs)):
                                foreach ($specs as $key => $value):
                                    ?>
                                    <tr>
                                        <th><?php echo htmlspecialchars($key); ?></th>
                                        <td><?php echo htmlspecialchars($value); ?></td>
                                    </tr>
                                    <?php
                                endforeach;
                            else:
                                ?>
                                <tr>
                                    <td colspan="2">No specifications available.</td>
                                </tr>
                            <?php endif; ?>
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