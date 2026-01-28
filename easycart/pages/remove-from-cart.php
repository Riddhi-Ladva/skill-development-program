<?php
// Start output buffering to prevent whitespace from includes breaking JSON
ob_start();

require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../data/products.php';
require_once '../includes/shipping.php';

// Clear any buffered output
ob_end_clean();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['product_id'] ?? null;

    if ($productId) {
        // Remove from session
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }

        // Calculate new totals
        $totalItems = 0;
        $subtotal = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $id => $quantity) {
                if (isset($products[$id])) {
                    $subtotal += $products[$id]['price'] * $quantity;
                    $totalItems += $quantity;
                }
            }
        }

        // Recalculate shipping based on new subtotal
        $shipping_method = $_SESSION['shipping_method'] ?? 'standard';
        $shipping_cost = ($subtotal > 0) ? calculateShippingCost($shipping_method, $subtotal) : 0;

        // Use the reusable function for all calculations
        $totals = calculateCheckoutTotals($_SESSION['cart'] ?? [], $products, $shipping_cost);

        // Calculate costs for all shipping methods for UI update
        $shipping_options = [
            'standard' => ($subtotal > 0) ? calculateShippingCost('standard', $subtotal) : 0,
            'express' => ($subtotal > 0) ? calculateShippingCost('express', $subtotal) : 0,
            'white-glove' => ($subtotal > 0) ? calculateShippingCost('white-glove', $subtotal) : 0,
            'freight' => ($subtotal > 0) ? calculateShippingCost('freight', $subtotal) : 0
        ];

        echo json_encode([
            'success' => true,
            'totals' => [
                'subtotal' => '$' . number_format($totals['subtotal'], 2),
                'shipping' => '$' . number_format($totals['shipping'], 2),
                'tax' => '$' . number_format($totals['tax'], 2),
                'grandTotal' => '$' . number_format($totals['total'], 2),
                'totalItems' => $totalItems
            ],
            'shippingOptions' => [
                'standard' => '$' . number_format($shipping_options['standard'], 2),
                'express' => '$' . number_format($shipping_options['express'], 2),
                'white-glove' => '$' . number_format($shipping_options['white-glove'], 2),
                'freight' => '$' . number_format($shipping_options['freight'], 2)
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
