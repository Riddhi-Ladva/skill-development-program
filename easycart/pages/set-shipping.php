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
        $_SESSION['shipping_type'] = $type;
        $_SESSION['shipping_price'] = $prices[$type];

        echo json_encode([
            'success' => true,
            'type' => $type,
            'price' => $prices[$type]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid shipping type']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
