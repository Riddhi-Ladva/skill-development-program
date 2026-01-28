<?php
// Start output buffering to prevent whitespace from includes breaking JSON
ob_start();

require_once '../includes/session.php';
require_once '../includes/shipping.php';
require_once '../data/products.php';

// Clear any buffered output
ob_end_clean();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $method = $data['type'] ?? 'standard';

    // Valid shipping types
    $valid_methods = ['standard', 'express', 'white-glove', 'freight'];

    if (in_array($method, $valid_methods)) {
        // Calculate subtotal from cart
        $subtotal = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $id => $quantity) {
                if (isset($products[$id])) {
                    $subtotal += $products[$id]['price'] * $quantity;
                }
            }
        }

        // Store only the method in session (NOT the cost)
        $_SESSION['shipping_method'] = $method;

        // Calculate shipping cost based on current subtotal
        $shipping_cost = calculateShippingCost($method, $subtotal);

        // Use the reusable function for all calculations
        $totals = calculateCheckoutTotals($_SESSION['cart'], $products, $shipping_cost);

        // Calculate costs for all shipping methods for UI update (even if subtotal hasn't changed, ensures consistency)
        $shipping_options = [
            'standard' => calculateShippingCost('standard', $subtotal),
            'express' => calculateShippingCost('express', $subtotal),
            'white-glove' => calculateShippingCost('white-glove', $subtotal),
            'freight' => calculateShippingCost('freight', $subtotal)
        ];

        echo json_encode([
            'success' => true,
            'method' => $method,
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
        echo json_encode(['success' => false, 'message' => 'Invalid shipping type']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
