<?php
$orders = [
    'EC2026-001234' => [
        'id' => 'EC2026-001234',
        'date' => 'January 15, 2026',
        'items' => [
            ['product_id' => 1, 'quantity' => 1],
            ['product_id' => 3, 'quantity' => 1]
        ],
        'total' => 182.56,
        'status' => 'delivered'
    ],
    'EC2026-001189' => [
        'id' => 'EC2026-001189',
        'date' => 'January 10, 2026',
        'items' => [
            ['product_id' => 2, 'quantity' => 1]
        ],
        'total' => 215.99,
        'status' => 'shipped'
    ],
    'EC2026-001067' => [
        'id' => 'EC2026-001067',
        'date' => 'January 5, 2026',
        'items' => [
            ['product_id' => 4, 'quantity' => 1],
            ['product_id' => 12, 'quantity' => 2]
        ],
        'total' => 205.17,
        'status' => 'processing'
    ],
    'EC2025-009821' => [
        'id' => 'EC2025-009821',
        'date' => 'December 20, 2025',
        'items' => [
            ['product_id' => 6, 'quantity' => 2]
        ],
        'total' => 86.38,
        'status' => 'delivered'
    ],
    'EC2025-009654' => [
        'id' => 'EC2025-009654',
        'date' => 'December 10, 2025',
        'items' => [
            ['product_id' => 7, 'quantity' => 1]
        ],
        'total' => 53.99,
        'status' => 'cancelled'
    ]
];
?>