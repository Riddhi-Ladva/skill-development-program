<?php
/**
 * MY STUDY NOTES: AJAX - Add to Cart
 * 
 * What happens here? -> When I click "Add to Cart" on a product, JavaScript 
 * sends the product ID here. PHP then saves it in the SESSION.
 * 
 * Why AJAX? -> So the page doesn't blink or reload. It feels much smoother.
 * 
 * Data Flow:
 * JavaScript (fetch) -> This File -> Session Cart -> Returns new total count.
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

// Safety check: Don't add if the product isn't real
if ($product_id && isset($products[$product_id])) {

    // Reminder: $_SESSION['cart'] is just an array like { 1 => 2, 5 => 1 } (ID => Qty)
    $_SESSION['cart'][$product_id] = $quantity;

    // RULE: Don't let them add more than 10 of one item.
    if ($_SESSION['cart'][$product_id] > 10) {
        $_SESSION['cart'][$product_id] = 10;
    }

    // New total items for the header badge (e.g., "3 items in cart")
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
