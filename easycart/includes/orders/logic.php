<?php
require_once dirname(__DIR__) . '/bootstrap/session.php';
require_once ROOT_PATH . '/config/db.php';
// Product data is now handled via DB queries if needed, or services
require_once ROOT_PATH . '/includes/db_functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$orders = [];

try {
    $pdo = getDbConnection();

    // Fetch orders for this user
    $stmt = $pdo->prepare("
        SELECT id, order_number, created_at as date, grand_total as total, status 
        FROM sales_order 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    $order_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($order_rows as $row) {
        $order_id = $row['id'];

        // Fetch items for each order
        $stmt_items = $pdo->prepare("SELECT product_id, qty_ordered as quantity FROM sales_order_items WHERE order_id = :order_id");
        $stmt_items->execute(['order_id' => $order_id]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        $row['items'] = $items;
        $orders[] = $row;
    }
} catch (PDOException $e) {
    error_log("Orders fetch error: " . $e->getMessage());
}
?>