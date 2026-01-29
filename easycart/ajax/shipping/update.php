<?php
/**
 * MY STUDY NOTES: AJAX - Change Shipping Method
 * 
 * What happens here? -> When the user clicks a different shipping 
 * radio button (like "Express"), JavaScript sends that choice here.
 * 
 * Goal: Update the session with the new method and send back updated tax 
 * and grand total info.
 */

// Start output buffering to prevent whitespace from includes breaking JSON
ob_start();

// Load bootstrap (session and config)
require_once __DIR__ . '/../../includes/bootstrap/session.php';

// Load data and modular services
require_once ROOT_PATH . '/data/products.php';
require_once ROOT_PATH . '/includes/cart/services.php';
require_once ROOT_PATH . '/includes/shipping/services.php';
require_once ROOT_PATH . '/includes/tax/services.php';

// Clear any buffered output
ob_end_clean();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $method = $data['type'] ?? 'standard';

    // Business Rule: Valid shipping types allowed in our system
    $valid_methods = ['standard', 'express', 'white-glove', 'freight'];

    if (in_array($method, $valid_methods)) {

        // Step 1: Get subtotal from the session cart
        $subtotal = calculateSubtotal($_SESSION['cart'] ?? [], $products);

        // Step 2: Remember this method in the session!
        $_SESSION['shipping_method'] = $method;

        // Step 3: Math out the new cost based on the method
        $shipping_cost = (count($_SESSION['cart'] ?? []) > 0) ? calculateShippingCost($method, $subtotal) : 0;

        // Step 4: Get final totals including the NEW TAX (Tax depends on shipping cost!)
        $totals = calculateCheckoutTotals($subtotal, $shipping_cost);

        // Step 5: Recalculate all shipping choices for the radio button labels
        $shipping_options = [
            'standard' => (count($_SESSION['cart'] ?? []) > 0) ? calculateShippingCost('standard', $subtotal) : 0,
            'express' => (count($_SESSION['cart'] ?? []) > 0) ? calculateShippingCost('express', $subtotal) : 0,
            'white-glove' => (count($_SESSION['cart'] ?? []) > 0) ? calculateShippingCost('white-glove', $subtotal) : 0,
            'freight' => (count($_SESSION['cart'] ?? []) > 0) ? calculateShippingCost('freight', $subtotal) : 0
        ];

        // Return updated values to the frontend
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
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid shipping type']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
