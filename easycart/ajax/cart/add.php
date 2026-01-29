<?php
/**
 * AJAX Endpoint: Add to Cart
 *
 * Purpose: Receives product ID and quantity via POST, updates the session cart.
 * Output: JSON response containing success status and updated total item count.
 * Dependencies: session.php (for $_SESSION), products.php (for validation).
 */

// ob_start just prevents accidental space/errors from breaking the JSON response
ob_start();

// Need session.php to access $_SESSION['cart']
require_once __DIR__ . '/../../includes/bootstrap/session.php';

// Need the products file to double-check if the ID sent actually exists
require_once ROOT_PATH . '/data/products.php';

ob_end_clean();

// Tell the browser: "The stuff I'm about to echo is JSON, not HTML!"
header('Content-Type: application/json');

// Decoding the JSON data sent from my JavaScript file
$input_json = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input_json['product_id']) ? (int) $input_json['product_id'] : null;
$quantity = isset($input_json['quantity']) ? (int) $input_json['quantity'] : 1;

// Validate product existence before adding to cart
if ($product_id && isset($products[$product_id])) {

    // Add or update quantity in session cart
// Structure: $_SESSION['cart'][product_id] = quantity
    $_SESSION['cart'][$product_id] = $quantity;

    // Enforce business rule: Maximum 10 items per product
    if ($_SESSION['cart'][$product_id] > 10) {
        $_SESSION['cart'][$product_id] = 10;
    }

    // Calculate new total items count for header badge update
    $total_items = array_sum($_SESSION['cart']);

    // Send the success response back to JavaScript
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart',
        'totalItems' => $total_items
    ]);
} else {
    // If something went wrong, send a 400 error status
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
}