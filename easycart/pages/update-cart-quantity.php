<?php
require_once '../includes/session.php';

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
    // If quantity is 0 or less, maybe remove from cart? 
    // Requirement says "read latest quantity", usually 0 removes. 
    // But UI limit is min="1" in cart.php input. 
    // Let's safe guard to min 1 for update, explicit remove handles deletion.
    $quantity = 1;
}
if ($quantity > 10) {
    $quantity = 10; // Enforce max 10 as per UI
}

// Update session
if (isset($_SESSION['cart'])) {
    $_SESSION['cart'][$product_id] = $quantity;
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Session not active']);
}
?>