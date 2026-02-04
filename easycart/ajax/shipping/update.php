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
require_once ROOT_PATH . '/includes/db_functions.php';
// Removed ajax_auth_guard()
require_once ROOT_PATH . '/includes/cart/services.php';
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

        // VALIDATION PIPELINE
        // Fetch all products for calculation
        $all_products = get_products([]);
        $products_indexed = [];
        foreach ($all_products as $p) {
            $products_indexed[$p['id']] = $p;
        }

        // 1. Get detailed breakdown first
        $cart_details = calculateCartDetails($_SESSION['cart'] ?? [], $products_indexed);

        // 2. Calculate Raw Subtotal from details
        $subtotal = 0;
        foreach ($cart_details as $item) {
            $subtotal += $item['final_total'];
        }

        // 3. Calculate Shipping Constraints (Uses Effective Subtotal internally)
        $constraints = calculateCartShippingConstraints($cart_details);

        // 4. Validate Requested Method
        // logic: We try to set the requested method, but validateShippingMethod might override it if it's invalid.
        $validated_method = validateShippingMethod($method, $constraints);

        // If the user tried to select 'standard' but 'freight' is required, $validated_method will be 'freight'.
        $_SESSION['shipping_method'] = $validated_method;

        // 5. Calculate Shipping Cost (using the VALIDATED method)
        $shipping_cost = (count($_SESSION['cart'] ?? []) > 0) ? calculateShippingCost($validated_method, $subtotal) : 0;

        // 6. Calculate Finals
        $totals = calculateCheckoutTotals($subtotal, $shipping_cost);

        // 7. Shipping Options
        $shipping_options = [
            'standard' => (count($_SESSION['cart'] ?? []) > 0) ? calculateShippingCost('standard', $subtotal) : 0,
            'express' => (count($_SESSION['cart'] ?? []) > 0) ? calculateShippingCost('express', $subtotal) : 0,
            'white-glove' => (count($_SESSION['cart'] ?? []) > 0) ? calculateShippingCost('white-glove', $subtotal) : 0,
            'freight' => (count($_SESSION['cart'] ?? []) > 0) ? calculateShippingCost('freight', $subtotal) : 0
        ];

        // Return updated values to the frontend
        echo json_encode([
            'success' => true,
            'method' => $validated_method, // Return what we actually set
            'totals' => [
                'subtotal' => '$' . number_format($totals['subtotal'], 2),
                'shipping' => '$' . number_format($totals['shipping'], 2),
                'promo_discount' => isset($totals['promo_discount']) ? '-$' . number_format($totals['promo_discount'], 2) : '$0.00',
                'tax' => '$' . number_format($totals['tax'], 2),
                'grandTotal' => '$' . number_format($totals['total'], 2)
            ],
            'shippingOptions' => [
                'standard' => '$' . number_format($shipping_options['standard'], 2),
                'express' => '$' . number_format($shipping_options['express'], 2),
                'white-glove' => '$' . number_format($shipping_options['white-glove'], 2),
                'freight' => '$' . number_format($shipping_options['freight'], 2)
            ],
            'shippingConstraints' => $constraints // NEW: Required by JS
        ]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid shipping type']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
