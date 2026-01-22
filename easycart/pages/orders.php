<?php
require_once '../includes/session.php';
require_once '../data/orders.php';
require_once '../data/products.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View your EasyCart order history">
    <title>My Orders - EasyCart</title>
    <link rel="stylesheet" href="/easycart/css/main.css?v=1.1">

<body>
    <header id="site-header">
        <div class="header-top">
            <div class="logo">
                <h1><a href="../index.php">EasyCart</a></h1>
            </div>
            <div class="search-bar">
                <form action="products.php" method="get" role="search">
                    <input type="search" id="search-input" name="q" placeholder="Search products..."
                        aria-label="Search products">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="header-actions">
                <a href="#" class="action-link" aria-label="Logged in as John Doe">John D.</a>
                <a href="cart.php" class="action-link" aria-label="View cart">Cart
                    (<?php echo array_sum($_SESSION['cart']); ?>)</a>
            </div>
        </div>
        <nav id="main-navigation" aria-label="Main navigation">
            <ul>
                <li><a href="../index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="products.php?category=electronics">Electronics</a></li>
                <li><a href="products.php?category=clothing">Clothing</a></li>
                <li><a href="products.php?category=home">Home & Garden</a></li>
                <li><a href="products.php?category=sports">Sports</a></li>
            </ul>
        </nav>
    </header>

    <main id="main-content">
        <div class="account-container">
            <aside class="account-sidebar">
                <nav aria-label="Account navigation">
                    <h2>My Account</h2>
                    <ul>
                        <li><a href="orders.php" aria-current="page">Orders</a></li>
                        <li><a href="#">Account Details</a></li>
                        <li><a href="#">Address Book</a></li>
                        <li><a href="#">Payment Methods</a></li>
                        <li><a href="#">Wishlist</a></li>
                        <li><a href="#">Reviews</a></li>
                        <li><a href="#">Notifications</a></li>
                        <li><a href="login.php">Logout</a></li>
                    </ul>
                </nav>
            </aside>

            <section class="account-main">
                <header class="page-header">
                    <h1>My Orders</h1>
                    <p>View and track your orders</p>
                </header>

                <section class="orders-filter">
                    <h2 class="visually-hidden">Filter orders</h2>
                    <form>
                        <div class="filter-controls">
                            <div class="filter-group">
                                <label for="time-filter">Time Period:</label>
                                <select id="time-filter" name="time">
                                    <option value="all">All Orders</option>
                                    <option value="30days">Last 30 Days</option>
                                    <option value="6months">Last 6 Months</option>
                                    <option value="year">This Year</option>
                                    <option value="2025">2025</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="status-filter">Order Status:</label>
                                <select id="status-filter" name="status">
                                    <option value="all">All Statuses</option>
                                    <option value="processing">Processing</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="search-orders">
                                <label for="order-search" class="visually-hidden">Search orders</label>
                                <input type="search" id="order-search" name="search"
                                    placeholder="Search by order number or product">
                                <button type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="orders-list">
                    <h2 class="visually-hidden">Order History</h2>

                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>Order #<?php echo htmlspecialchars($order['id']); ?></td>
                                    <td>Placed on <?php echo htmlspecialchars($order['date']); ?></td>
                                    <td>
                                        <ul>
                                            <?php foreach ($order['items'] as $item_data):
                                                $product_name = isset($products[$item_data['product_id']]) ? $products[$item_data['product_id']]['name'] : 'Unknown Product';
                                                ?>
                                                <li><?php echo htmlspecialchars($product_name); ?> (Qty:
                                                    <?php echo $item_data['quantity']; ?>)
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </td>
                                    <td>$<?php echo number_format($order['total'], 2); ?></td>
                                    <td><span
                                            class="status-badge <?php echo htmlspecialchars($order['status']); ?>"><?php echo ucfirst(htmlspecialchars($order['status'])); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>

                <nav class="pagination" aria-label="Orders pagination">
                    <ul>
                        <li><a href="orders.php?page=1" aria-current="page">1</a></li>
                        <li><a href="orders.php?page=2">2</a></li>
                        <li><a href="orders.php?page=3">3</a></li>
                        <li><a href="orders.php?page=2" aria-label="Next page">Next</a></li>
                    </ul>
                </nav>
            </section>
        </div>

        <section class="help-section">
            <h2>Need Help with Your Order?</h2>
            <div class="help-options">
                <article class="help-option">
                    <h3>Track Your Package</h3>
                    <p>Get real-time updates on your order location</p>
                    <a href="#">Track Order</a>
                </article>
                <article class="help-option">
                    <h3>Return or Exchange</h3>
                    <p>Easy returns within 30 days of delivery</p>
                    <a href="#">Start Return</a>
                </article>
                <article class="help-option">
                    <h3>Contact Support</h3>
                    <p>Our team is here to help 24/7</p>
                    <a href="#">Get Help</a>
                </article>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>