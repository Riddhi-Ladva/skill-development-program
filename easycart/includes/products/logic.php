<?php
require_once __DIR__ . '/../bootstrap/session.php';
require_once __DIR__ . '/../db-functions.php';
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

// --- 2. Pagination Calculation ---
$p = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 9; // 3 rows * 3 columns
$offset = ($p - 1) * $limit;

// --- 3. Fetch Data ---

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
    'sort' => $sort,
    'limit' => $limit,
    'offset' => $offset
];

// Fetch matching products (paginated)
$products = get_products($filters);

// Fetch total count (ignores limit/offset)
$total_products = get_products_count($filters);
$total_pages = ceil($total_products / $limit);

// --- 4. Pagination Window Logic ---
// We'll show up to 5 neighboring pages around the current page
$max_visible_pages = 5;
$pagination_start = max(1, $p - floor($max_visible_pages / 2));
$pagination_end = min($total_pages, $pagination_start + $max_visible_pages - 1);

// Adjust start if end is at total_pages
if ($pagination_end === (float) $total_pages || $pagination_end === (int) $total_pages) {
    $pagination_start = max(1, $pagination_end - $max_visible_pages + 1);
}

// Ensure $p is within bounds to prevent empty pages if users hack the URL
if ($total_pages > 0 && $p > $total_pages) {
    header("Location: " . url('products') . "?page=" . $total_pages . (isset($_GET['q']) ? "&q=" . urlencode($_GET['q']) : ''));
    exit;
}

// --- 5. View Logic ---

// Determine Page Header / Category Name
$category = null;
if (count($selected_categories) === 1) {
    $slug = $selected_categories[0];
    if (isset($all_categories[$slug])) {
        $category = $all_categories[$slug]['name'];
    }
}
