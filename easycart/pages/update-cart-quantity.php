<?php
// Start output buffering to prevent whitespace from includes breaking JSON
ob_start();

require_once '../includes/session.php';
require_once '../data/products.php';
require_once '../includes/shipping.php';

// Clear any buffered output (like whitespace from includes)
ob_end_clean();

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id']) || !isset($input['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$product_id = $input['product_id'];
$quantity = (int) $input['quantity'];

// Validate quantity
if ($quantity < 1) {
    $quantity = 1;
}
if ($quantity > 10) {
    $quantity = 10; // Enforce max 10 as per UI
}

// Update session
if (isset($_SESSION['cart'])) {
    $_SESSION['cart'][$product_id] = $quantity;

    // Recalculate totals with new quantity
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $id => $qty) {
        if (isset($products[$id])) {
            $subtotal += $products[$id]['price'] * $qty;
        }
    }

    // Recalculate shipping based on new subtotal
    $shipping_method = $_SESSION['shipping_method'] ?? 'standard';
    $shipping_cost = calculateShippingCost($shipping_method, $subtotal);

    // Calculate all totals
    $totals = calculateCheckoutTotals($_SESSION['cart'], $products, $shipping_cost);

    // Calculate costs for all shipping methods for UI update
    $shipping_options = [
        'standard' => calculateShippingCost('standard', $subtotal),
        'express' => calculateShippingCost('express', $subtotal),
        'white-glove' => calculateShippingCost('white-glove', $subtotal),
        'freight' => calculateShippingCost('freight', $subtotal)
    ];

    echo json_encode([
        'success' => true,
        'totals' => [
            'subtotal' => '$' . number_format($totals['subtotal'], 2),
            'shipping' => '$' . number_format($totals['shipping'], 2),
            'tax' => '$' . number_format($totals['tax'], 2),
            'grandTotal' => '$' . number_format($totals['total'], 2)
        ],
        'shippingOptions' => [
            'standard' => '$' . number_format($shipping_options['standard'], 2),
            'express' => '$' . number_format($shipping_options['express'], 2),
            'white-glove' => '$' . number_format($shipping_options['white-glove'], 2),
            'freight' => '$' . number_format($shipping_options['freight'], 2)
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Session not active']);
}
?>