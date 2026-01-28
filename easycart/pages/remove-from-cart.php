<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
require_once '../data/products.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $productId = $data['product_id'] ?? null;

    if ($productId) {
        // Remove from session
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }

        // Recalculate totals
        $subtotal = 0;
        $totalItems = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $id => $quantity) {
                if (isset($products[$id])) {
                    $subtotal += $products[$id]['price'] * $quantity;
                    $totalItems += $quantity;
                }
            }
        }

        $shipping_data = $_SESSION['shipping'] ?? ['type' => 'standard', 'price' => 0];
        $shipping = ($subtotal > 0) ? $shipping_data['price'] : 0;

        $tax = $subtotal * 0.08;
        $total = $subtotal + $shipping + $tax;

        echo json_encode([
            'success' => true,
            'totals' => [
                'subtotal' => '$' . number_format($subtotal, 2),
                'shipping' => $shipping == 0 ? 'FREE' : '$' . number_format($shipping, 2),
                'tax' => '$' . number_format($tax, 2),
                'grandTotal' => '$' . number_format($total, 2),
                'totalItems' => $totalItems
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
