<?php
/**
 * Orders Logic
 * 
 * Responsibility: Fetches order list or single order details for the logged-in user.
 * Includes security checks to ensure users only access their own data.
 */

require_once dirname(__DIR__) . '/bootstrap/session.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/db_functions.php';

// Authentication guard
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = getDbConnection();

// Mode: Detail or List
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($order_id) {
    // --- Single Order Detail Mode ---
    try {
        // 1. Fetch Order with User Ownership Check
        $stmt = $pdo->prepare("
            SELECT * 
            FROM sales_order 
            WHERE id = :id AND user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['id' => $order_id, 'user_id' => $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            // Unauthorized or Not Found
            header('Location: orders.php?error=not_found');
            exit;
        }

        // 2. Fetch Order Items
        $stmt_items = $pdo->prepare("
            SELECT * 
            FROM sales_order_items 
            WHERE order_id = :order_id
            ORDER BY name ASC
        ");
        $stmt_items->execute(['order_id' => $order_id]);
        $order_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

        // 3. Fetch Shipping Address
        $stmt_addr = $pdo->prepare("
            SELECT * 
            FROM sales_order_address 
            WHERE order_id = :order_id AND address_type = 'shipping'
            LIMIT 1
        ");
        $stmt_addr->execute(['order_id' => $order_id]);
        $order_address = $stmt_addr->fetch(PDO::FETCH_ASSOC);

        // 4. Fetch Payment Info
        $stmt_pay = $pdo->prepare("
            SELECT * 
            FROM sales_order_payment 
            WHERE order_id = :order_id
            LIMIT 1
        ");
        $stmt_pay->execute(['order_id' => $order_id]);
        $order_payment = $stmt_pay->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Order Detail fetch error: " . $e->getMessage());
        header('Location: orders.php?error=db_error');
        exit;
    }
} else {
    // --- Orders List Mode ---
    $orders = [];
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $limit = 5; // Fixed page size
    $offset = ($page - 1) * $limit;
    $total_orders = 0;
    $total_pages = 0;

    try {
        // 1. Get Total Count
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM sales_order WHERE user_id = :user_id");
        $stmt_count->execute(['user_id' => $user_id]);
        $total_orders = $stmt_count->fetchColumn();
        $total_pages = ceil($total_orders / $limit);

        // 2. Get Paginated Orders
        $stmt = $pdo->prepare("
            SELECT id, order_number, created_at, grand_total, status, shipping_method
            FROM sales_order 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        // Bind parameters strictly as integers for LIMIT/OFFSET
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Orders List fetch error: " . $e->getMessage());
    }
}
?>