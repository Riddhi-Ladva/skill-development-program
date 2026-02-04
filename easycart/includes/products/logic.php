<?php
require_once __DIR__ . '/../bootstrap/session.php';
require_once __DIR__ . '/../db_functions.php';
require_once ROOT_PATH . '/includes/shipping/services.php';

// --- 1. Parameter Normalization for View State & SQL ---

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Handle Category Input (string or array)
// View expects $selected_categories as valid array of strings
$selected_categories = [];
if (isset($_GET['category'])) {
    if (is_array($_GET['category'])) {
        $selected_categories = $_GET['category'];
    } elseif (is_string($_GET['category']) && $_GET['category'] !== '') {
        $selected_categories[] = $_GET['category'];
    }
}
// Default 'all' handling if present in legacy links
if (in_array('all', $selected_categories)) {
    $selected_categories = [];
}

// Handle Brand Input
$selected_brands = [];
if (isset($_GET['brand_id'])) {
    if (is_array($_GET['brand_id'])) {
        $selected_brands = array_map('intval', $_GET['brand_id']);
    } elseif (is_string($_GET['brand_id']) && $_GET['brand_id'] !== '') {
        $selected_brands[] = (int) $_GET['brand_id'];
    }
}

// Handle Price
$selected_prices = isset($_GET['price']) && is_array($_GET['price']) ? $_GET['price'] : [];

// Handle Rating
$selected_rating = isset($_GET['rating']) ? (float) $_GET['rating'] : 0;

// Handle Availability
$selected_availability = isset($_GET['availability']) && is_array($_GET['availability']) ? $_GET['availability'] : [];

// Handle Sort
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';

// --- 2. Fetch Data ---

// Fetch Brands for Filter UI
$all_brands = get_all_brands();

// Fetch Categories for Filter UI
$all_categories = get_all_categories();

// Fetch Products from DB
$filters = [
    'query' => $query,
    'category' => $selected_categories,
    'brand_id' => $selected_brands,
    'price_range' => $selected_prices,
    'rating' => $selected_rating,
    'availability' => $selected_availability,
    'sort' => $sort
];

// --- 3. View Logic ---

// Determine Page Header / Category Name
$category = null;
if (count($selected_categories) === 1) {
    $slug = $selected_categories[0];
    if (isset($all_categories[$slug])) {
        $category = $all_categories[$slug]['name'];
    }
}

$products = get_products($filters);

// Note: No need for manual PHP pagination here yet as UI passes everything to one page or specific params. 
// If pagination is needed, we would use $filters['limit'] and $filters['offset'].
// The current view seems to show "Showing X products" and has pagination links but logic file didn't implement real pagination (it was slicing in view? No, view logic showed all or filtered). 
// The view has pagination links: products.php?page=1... 
// The original logic file I read didn't seem to implement slicing, it was just filtering.
// So I will stick to returning the filtered set.