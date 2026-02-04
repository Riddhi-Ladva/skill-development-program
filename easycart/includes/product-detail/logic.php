<?php
require_once __DIR__ . '/../bootstrap/session.php';
require_once __DIR__ . '/../db_functions.php';
require_once ROOT_PATH . '/includes/shipping/services.php';

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}

// Fetch Product
$product = get_product_by_id($product_id);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Prepare View Variables
$brand = [
    'name' => $product['brand_name'] ?? 'Generic'
];

$category = [
    'name' => ucfirst($product['category'] ?? 'Uncategorized')
];

// Fetch Related Products (Same category, excluding current would be ideal but simple limit works for now)
// We fetch 5 strings to verify we get others, then slice in view or just show 4.
$related_products = get_products([
    'category' => $product['category'] ?? 'all',
    'limit' => 4,
    'sort' => 'featured'
]);