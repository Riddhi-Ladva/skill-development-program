<?php
require_once '../includes/orders/logic.php';
require_once ROOT_PATH . '/includes/auth/guard.php';
auth_guard();

// $order, $order_items, $order_address, $order_payment are all populated by logic.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View details for order <?php echo htmlspecialchars($order['order_number']); ?>">
    <title>Order Details - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.2'); ?>">
</head>

<body class="order-detail-page">
    <?php include '../includes/header.php'; ?>

    <main id="main-content">
        <div class="account-container detail-view">
            <!-- Order Header -->
            <header class="detail-header">
                <a href="<?php echo url('orders'); ?>" class="back-link">← Back to My Orders</a>
                <div class="header-main">
                    <h1>Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                    <span class="status-badge <?php echo htmlspecialchars($order['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                    </span>
                </div>
                <p class="order-meta">Placed on <?php echo date('F d, Y \a\t H:i', strtotime($order['created_at'])); ?>
                </p>
            </header>

            <div class="detail-grid">
                <!-- LEFT COLUMN: Items -->
                <div class="detail-main-column">
                    <section class="detail-section items-card">
                        <h2>Order Items</h2>
                        <div class="table-responsive">
                            <table class="items-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>SKU</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Price</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="product-info">
                                                    <span
                                                        class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                                </div>
                                            </td>
                                            <td><code><?php echo htmlspecialchars($item['sku']); ?></code></td>
                                            <td class="text-center"><?php echo $item['qty_ordered']; ?></td>
                                            <td class="text-right">$<?php echo number_format($item['price'], 2); ?></td>
                                            <td class="text-right">$<?php echo number_format($item['row_total'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <!-- RIGHT COLUMN: Sidebar (Summary, Shipping, Payment) -->
                <aside class="detail-sidebar">
                    <!-- Price Breakdown Card -->
                    <section class="detail-section summary-card">
                        <h3>Order Summary</h3>
                        <div class="summary-breakdown">
                            <div class="summary-row">
                                <span class="summary-label">Subtotal</span>
                                <span class="summary-value">$<?php echo number_format($order['subtotal'], 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-label">Shipping
                                    (<?php echo ucfirst(htmlspecialchars($order['shipping_method'] ?? 'Standard')); ?>)</span>
                                <span
                                    class="summary-value">$<?php echo number_format($order['shipping_total'], 2); ?></span>
                            </div>
                            <?php if ($order['discount_total'] > 0): ?>
                                <div class="summary-row discount">
                                    <span class="summary-label">Discount</span>
                                    <span
                                        class="summary-value">-$<?php echo number_format($order['discount_total'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="summary-row">
                                <span class="summary-label">Tax</span>
                                <span class="summary-value">$<?php echo number_format($order['tax_total'], 2); ?></span>
                            </div>
                            <div class="summary-row grand-total">
                                <strong>Total</strong>
                                <strong>$<?php echo number_format($order['grand_total'], 2); ?></strong>
                            </div>
                        </div>
                    </section>

                    <section class="detail-section shipping-card">
                        <h3>Shipping Address</h3>
                        <?php if ($order_address): ?>
                            <address>
                                <p><strong><?php echo htmlspecialchars($order_address['street']); ?></strong></p>
                                <p><?php echo htmlspecialchars($order_address['city']); ?>,
                                    <?php echo htmlspecialchars($order_address['state']); ?>
                                    <?php echo htmlspecialchars($order_address['zip']); ?>
                                </p>
                                <p><?php echo htmlspecialchars($order_address['country']); ?></p>
                                <?php if ($order_address['phone']): ?>
                                    <a href="tel:<?php echo htmlspecialchars($order_address['phone']); ?>" class="phone-link">
                                        <?php echo htmlspecialchars($order_address['phone']); ?>
                                    </a>
                                <?php endif; ?>
                            </address>
                        <?php else: ?>
                            <p class="empty-text">No shipping address recorded.</p>
                        <?php endif; ?>
                    </section>

                    <!-- Billing Address Card (Conditional) -->
                    <?php if (isset($order['billing_same_as_shipping']) && !$order['billing_same_as_shipping'] && $order_billing_address): ?>
                        <section class="detail-section billing-card" style="margin-top: 1.5rem;">
                            <h3>Billing Address</h3>
                            <address>
                                <p><strong><?php echo htmlspecialchars($order_billing_address['street']); ?></strong></p>
                                <p><?php echo htmlspecialchars($order_billing_address['city']); ?>,
                                    <?php echo htmlspecialchars($order_billing_address['state']); ?>
                                    <?php echo htmlspecialchars($order_billing_address['zip']); ?>
                                </p>
                                <p><?php echo htmlspecialchars($order_billing_address['country']); ?></p>
                            </address>
                        </section>
                    <?php endif; ?>

                    <!-- Payment info Card -->
                    <section class="detail-section payment-card">
                        <h3>Payment Method</h3>
                        <?php if ($order_payment): ?>
                            <div class="payment-info-list">
                                <div class="payment-item">
                                    <span class="payment-label">Method</span>
                                    <span
                                        class="payment-value"><?php echo ucfirst(htmlspecialchars($order_payment['method'])); ?></span>
                                </div>
                                <!-- <?php if ($order_payment['last_4']): ?>
                                    <div class="payment-item">
                                        <span class="payment-label">Card</span>
                                        <span class="payment-value">••••
                                            <?php echo htmlspecialchars($order_payment['last_4']); ?></span>
                                    </div>
                                <?php endif; ?> -->
                                <div class="payment-item">
                                    <span class="payment-label">Status</span>
                                    <span class="payment-value">
                                        <span
                                            class="status-text"><?php echo ucfirst(htmlspecialchars($order_payment['status'])); ?></span>
                                    </span>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="empty-text">No payment information available.</p>
                        <?php endif; ?>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>