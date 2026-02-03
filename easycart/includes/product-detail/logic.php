<?php
require_once '../includes/bootstrap/session.php';
require_once ROOT_PATH . '/data/products.php';
require_once ROOT_PATH . '/data/brands.php';
require_once ROOT_PATH . '/data/categories.php';
require_once ROOT_PATH . '/includes/shipping/services.php';

$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = isset($products[$product_id]) ? $products[$product_id] : null;

if (!$product) {
    header('Location: products.php');
    exit;
}

$brand = isset($brands[$product['brand_id']]) ? $brands[$product['brand_id']] : ['name' => 'Generic'];
$category = isset($categories[$product['category']]) ? $categories[$product['category']] : ['name' => 'Uncategorized'];
?>