<?php
/**
 * Refined User Dashboard Page
 * 
 * Responsibility: Premium overview of order activity and spending trends.
 */

require_once __DIR__ . '/../includes/bootstrap/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth/guard.php';

// Auth Guard
auth_guard();

$user_id = $_SESSION['user_id'];
$pdo = getDbConnection();

// Fetch Profile and Metrics
try {
    // 1. User Info for greeting
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_email = $user_data ? $user_data['email'] : 'User';
    $display_name = ucfirst(explode('@', $user_email)[0]);

    // 2. Metrics
    // Total Orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales_order WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $total_orders = $stmt->fetchColumn();

    // Total Spent
    $stmt = $pdo->prepare("SELECT SUM(grand_total) FROM sales_order WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $total_spent = (float) ($stmt->fetchColumn() ?: 0);

} catch (PDOException $e) {
    error_log("Dashboard Load Error: " . $e->getMessage());
    $display_name = "User";
    $total_orders = 0;
    $total_spent = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.2'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/pages/dashboard.css?v=1.1'); ?>">
    <!-- Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="dashboard-page">
    <?php include '../includes/header.php'; ?>

    <main id="main-content">
        <div class="account-container">


            <section class="account-main">
                <header class="page-header">
                    <h1>Hi
                        <?php echo htmlspecialchars($display_name); ?>,
                    </h1>
                    <p>Welcome to your dashboard overview</p>
                </header>

                <!-- Metrics Grid -->
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-icon">📦</div>
                        <div class="metric-info">
                            <span class="metric-label">Total Orders</span>
                            <span class="metric-value">
                                <?php echo number_format($total_orders); ?>
                            </span>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon">💳</div>
                        <div class="metric-info">
                            <span class="metric-label">Total Amount Spent</span>
                            <span class="metric-value">$
                                <?php echo number_format($total_spent, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Spending Overview Chart -->
                <div class="chart-section">
                    <h2>Spending Overview</h2>
                    <div class="chart-container">
                        <div id="no-data-message" style="display: none;">No spending data available. Start exploring our
                            products!</div>
                        <canvas id="spendingChart" style="display: none;"></canvas>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <!-- Load Chart Logic -->
    <script src="<?php echo asset('js/dashboard/dashboard-chart.js?v=1.1'); ?>"></script>
</body>

</html>