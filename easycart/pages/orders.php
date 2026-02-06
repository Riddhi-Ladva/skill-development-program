<?php
require_once '../includes/orders/logic.php';
require_once ROOT_PATH . '/includes/auth/guard.php';
auth_guard();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="View your EasyCart order history">
    <title>My Orders - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.1'); ?>">
</head>

<body class="orders-page">
    <?php include '../includes/header.php'; ?>

    <main id="main-content">
        <div class="account-container">


            <section class="account-main">
                <header class="page-header">
                    <h1>My Orders</h1>
                    <p>View and track your previous purchases</p>
                </header>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert error">
                        <?php
                        if ($_GET['error'] === 'not_found')
                            echo "Order not found or access denied.";
                        else
                            echo "An error occurred while fetching your order details.";
                        ?>
                    </div>
                <?php endif; ?>

                <div class="orders-list">
                    <?php if (empty($orders)): ?>
                        <div class="empty-state">
                            <p>You haven't placed any orders yet.</p>
                            <a href="<?php echo url('pages/products.php'); ?>" class="btn primary">Start Shopping</a>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td class="order-id"><?php echo htmlspecialchars($o['order_number']); ?></td>
                                        <td class="order-date"><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
                                        <td class="order-method">
                                            <?php echo ucfirst(htmlspecialchars($o['shipping_method'] ?? 'Standard')); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo htmlspecialchars($o['status']); ?>">
                                                <?php echo ucfirst(htmlspecialchars($o['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="order-total">$<?php echo number_format($o['grand_total'], 2); ?></td>
                                        <td>
                                            <a href="<?php echo url('pages/order-detail.php?id=' . $o['id']); ?>"
                                                class="view-link">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <?php if (isset($total_pages) && $total_pages > 1): ?>
                        <nav class="pagination" aria-label="Order pages">
                            <ul>
                                <!-- Previous -->
                                <?php if ($page > 1): ?>
                                    <li><a href="?page=<?php echo $page - 1; ?>" aria-label="Previous page">Prev</a></li>
                                <?php endif; ?>

                                <!-- Page Numbers -->
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li>
                                        <a href="?page=<?php echo $i; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"
                                            <?php echo $i === $page ? 'aria-current="page"' : ''; ?>>
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <!-- Next -->
                                <?php if ($page < $total_pages): ?>
                                    <li><a href="?page=<?php echo $page + 1; ?>" aria-label="Next page">Next</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>

</html>