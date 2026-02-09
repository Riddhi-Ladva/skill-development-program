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
require_once ROOT_PATH . '/includes/db-functions.php';
require_once __DIR__ . '/../../includes/auth/guard.php';

// Restore auth guard: REMOVED for guest access
// ajax_auth_guard();

ob_end_clean();

// Tell the browser: "The stuff I'm about to echo is JSON, not HTML!"
header('Content-Type: application/json');

// Decoding the JSON data sent from my JavaScript file
$input_json = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input_json['product_id']) ? (int) $input_json['product_id'] : null;
$quantity = isset($input_json['quantity']) ? (int) $input_json['quantity'] : 1;

// Validate product existence before adding to cart
$product = get_product_by_id($product_id);
if ($product_id && $product) {

    // SYNC WITH DB or Session
    if (isset($_SESSION['user_id'])) {
        add_to_cart_db($_SESSION['user_id'], $product_id, $quantity);
    } else {
        // Guest - Session Cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }

    // Load necessary services for the response
    require_once ROOT_PATH . '/includes/cart/services.php';
    require_once ROOT_PATH . '/includes/shipping/services.php';

    // Fetch all products for calculation (since services still expect the array)
    // TEMPORARY: For performance, we should refactor services to only need cart items.
    $all_products = get_products([]);
    $products_indexed = [];
    foreach ($all_products as $p) {
        $products_indexed[$p['id']] = $p;
    }

    // Fetch current cart from DB or Session for response calculation
    $cart = [];
    if (isset($_SESSION['user_id'])) {
        $cart = get_cart_items_db($_SESSION['user_id']);
    } else {
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    }

    // Calculate new total items count for header badge update
    $total_items = getCartCount();

    // VALIDATION PIPELINE: Ensure shipping method is valid after adding item
    // 1. Get detailed breakdown
    $cart_details = calculateCartDetails($cart, $products_indexed);

    // 2. Calculate Shipping Constraints
    $constraints = calculateCartShippingConstraints($cart_details);

    // 3. Validate and Auto-Correct Shipping Method
    $current_method = $_SESSION['shipping_method'] ?? 'standard';
    $validated_method = validateShippingMethod($current_method, $constraints);
    $_SESSION['shipping_method'] = $validated_method; // Persist correction

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