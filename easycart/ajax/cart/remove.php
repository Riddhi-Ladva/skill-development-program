<?php
/**
 * MY STUDY NOTES: AJAX - Remove from Cart
 * 
 * What happens here? -> Clicking "Remove" in the cart sends the ID here.
 * PHP kicks it out of the session array.
 * 
 * Wait, why so much code again? -> Just like the "Update" handler, 
 * removing an item changes the Subtotal, which changes Shipping, 
 * which changes Tax. I have to update the whole Order Summary!
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
    // Get the product ID to remove
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['product_id'] ?? null;

    if ($productId) {
        // DROP THE ITEM: Kick it out of the session array
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }

        // RECALCULATION PARTY: EVERYTHING changes now
        $subtotal = calculateSubtotal($_SESSION['cart'], $products);

        // Determine current total items for the badge
        $totalItems = array_sum($_SESSION['cart']);

        // Figure out shipping (Rule: if cart is empty, shipping is 0)
        $shipping_method = $_SESSION['shipping_method'] ?? 'standard';
        $shipping_cost = ($subtotal > 0) ? calculateShippingCost($shipping_method, $subtotal) : 0;

        // Aggregate all totals (Tax, Grand Total)
        $totals = calculateCheckoutTotals($subtotal, $shipping_cost);

        // Calculate all shipping options so the UI labels can update
        $shipping_options = [
            'standard' => ($subtotal > 0) ? calculateShippingCost('standard', $subtotal) : 0,
            'express' => ($subtotal > 0) ? calculateShippingCost('express', $subtotal) : 0,
            'white-glove' => ($subtotal > 0) ? calculateShippingCost('white-glove', $subtotal) : 0,
            'freight' => ($subtotal > 0) ? calculateShippingCost('freight', $subtotal) : 0
        ];

        // Send all the fresh math back to the UI
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
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
