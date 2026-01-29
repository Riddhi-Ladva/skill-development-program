<?php
/**
 * MY STUDY NOTES: AJAX - Update Quantity
 * 
 * What happens here? -> When I click [+] or [-] in the cart, JavaScript 
 * sends the new quantity here. 
 * 
 * Why so much code? -> Because changing just ONE quantity affects 
 * EVERYTHING: Subtotal, Shipping, Tax, and the Grand Total.
 * So, I have to recalculate the whole world and send it back to the UI.
 */

ob_start();

require_once __DIR__ . '/../../includes/bootstrap/session.php';
require_once ROOT_PATH . '/data/products.php';
require_once ROOT_PATH . '/includes/cart/services.php';
require_once ROOT_PATH . '/includes/shipping/services.php';
require_once ROOT_PATH . '/includes/tax/services.php';

ob_end_clean();

header('Content-Type: application/json');

// Decoding JSON input from JS
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id']) || !isset($input['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$product_id = $input['product_id'];
$quantity = (int) $input['quantity'];

// Business Rule: Keep quantity between 1 and 10
if ($quantity < 1)
    $quantity = 1;
if ($quantity > 10)
    $quantity = 10;

// Update the Session
if (isset($_SESSION['cart'])) {
    $_SESSION['cart'][$product_id] = $quantity;

    // STEP 1: Get new subtotal
    $subtotal = calculateSubtotal($_SESSION['cart'], $products);

    // STEP 2: Figure out shipping (Rule: if cart is empty, shipping is 0)
    $shipping_method = $_SESSION['shipping_method'] ?? 'standard';
    $shipping_cost = (count($_SESSION['cart']) > 0) ? calculateShippingCost($shipping_method, $subtotal) : 0;

    // STEP 3: Get the math summary (tax, grand total)
    $totals = calculateCheckoutTotals($subtotal, $shipping_cost);

    // STEP 4: Calculate all shipping options so the UI can update the labels
    $shipping_options = [
        'standard' => (count($_SESSION['cart']) > 0) ? calculateShippingCost('standard', $subtotal) : 0,
        'express' => (count($_SESSION['cart']) > 0) ? calculateShippingCost('express', $subtotal) : 0,
        'white-glove' => (count($_SESSION['cart']) > 0) ? calculateShippingCost('white-glove', $subtotal) : 0,
        'freight' => (count($_SESSION['cart']) > 0) ? calculateShippingCost('freight', $subtotal) : 0
    ];

    // Send everything back so JS can just "plug it in" to the HTML
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
