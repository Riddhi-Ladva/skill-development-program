<?php
require_once '../includes/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $type = $data['type'] ?? 'standard';

    $prices = [
        'standard' => 0,
        'express' => 9.99,
        'next-day' => 19.99
    ];

    if (array_key_exists($type, $prices)) {
        $_SESSION['shipping'] = [
            'type' => $type,
            'price' => $prices[$type]
        ];

        // Calculate Totals to return
        require_once '../data/products.php';
        $subtotal = 0;
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $id => $quantity) {
                if (isset($products[$id])) {
                    $subtotal += $products[$id]['price'] * $quantity;
                }
            }
        }
        $shipping = $prices[$type];
        $tax = $subtotal * 0.08;
        $total = $subtotal + $shipping + $tax;

        echo json_encode([
            'success' => true,
            'type' => $type,
            'totals' => [
                'subtotal' => '$' . number_format($subtotal, 2),
                'shipping' => $shipping == 0 ? 'FREE' : '$' . number_format($shipping, 2),
                'tax' => '$' . number_format($tax, 2),
                'grandTotal' => '$' . number_format($total, 2)
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid shipping type']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
