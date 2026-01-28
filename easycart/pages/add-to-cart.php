<?php
// Start output buffering to prevent whitespace from includes breaking JSON
ob_start();

require_once '../includes/session.php';
require_once '../data/products.php';

// Clear any buffered output
ob_end_clean();

header('Content-Type: application/json');

// Get input (support both JSON and Form data)
$input_json = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input_json['product_id']) ? (int) $input_json['product_id'] : (isset($_POST['product_id']) ? (int) $_POST['product_id'] : null);
$quantity = isset($input_json['quantity']) ? (int) $input_json['quantity'] : (isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1);

if ($product_id && isset($products[$product_id])) {
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add to cart (Increment if exists)
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    // Enforce max quantity of 10 per item rule (optional but consistent with other pages)
    if ($_SESSION['cart'][$product_id] > 10) {
        $_SESSION['cart'][$product_id] = 10;
    }

    // Calculate total items
    $total_items = array_sum($_SESSION['cart']);

    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart',
        'totalItems' => $total_items,
        'cart' => $_SESSION['cart'] // Optional debug info
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
}
?>