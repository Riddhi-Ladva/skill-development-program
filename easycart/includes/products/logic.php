<?php
require_once '../includes/bootstrap/session.php';
require_once ROOT_PATH . '/data/products.php';
require_once ROOT_PATH . '/data/brands.php';
require_once ROOT_PATH . '/includes/shipping/services.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = $_GET['category'] ?? 'all';

if (is_array($category)) {
    $category = 'all';
}

$selected_categories = isset($_GET['category']) && is_array($_GET['category']) ? $_GET['category'] : [];
$selected_brands = isset($_GET['brand_id']) && is_array($_GET['brand_id']) ? $_GET['brand_id'] : [];
$selected_prices = isset($_GET['price']) && is_array($_GET['price']) ? $_GET['price'] : [];
$selected_rating = isset($_GET['rating']) ? (float) $_GET['rating'] : 0;
$selected_availability = isset($_GET['availability']) && is_array($_GET['availability']) ? $_GET['availability'] : [];
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'featured';

if (isset($_GET['category']) && is_string($_GET['category']) && $_GET['category'] !== '') {
    if (!in_array($_GET['category'], $selected_categories)) {
        $selected_categories[] = $_GET['category'];
    }
}
if (isset($_GET['brand_id']) && is_string($_GET['brand_id']) && $_GET['brand_id'] !== '') {
    if (!in_array($_GET['brand_id'], $selected_brands)) {
        $selected_brands[] = (int) $_GET['brand_id'];
    }
}

$all_brands = $brands ?? [];

if ($query !== '' || !empty($selected_categories) || !empty($selected_brands) || !empty($selected_prices) || $selected_rating > 0 || !empty($selected_availability)) {
    $products = array_filter($products, function ($product) use ($query, $selected_categories, $selected_brands, $selected_prices, $selected_rating, $selected_availability, $all_brands) {
        if ($query !== '') {
            $brand_name = isset($all_brands[$product['brand_id']]) ? $all_brands[$product['brand_id']]['name'] : '';
            $search_text = $product['name'] . ' ' . $brand_name . ' ' . $product['category'];
            if (stripos($search_text, $query) === false)
                return false;
        }

        if (!empty($selected_categories)) {
            $category_match = false;
            foreach ($selected_categories as $cat) {
                if (strtolower($product['category']) === strtolower($cat)) {
                    $category_match = true;
                    break;
                }
            }
            if (!$category_match)
                return false;
        }

        if (!empty($selected_brands)) {
            if (!in_array($product['brand_id'], $selected_brands)) {
                return false;
            }
        }

        if (!empty($selected_prices)) {
            $price_match = false;
            foreach ($selected_prices as $range) {
                switch ($range) {
                    case '0-25':
                        if ($product['price'] < 25)
                            $price_match = true;
                        break;
                    case '25-50':
                        if ($product['price'] >= 25 && $product['price'] <= 50)
                            $price_match = true;
                        break;
                    case '50-100':
                        if ($product['price'] > 50 && $product['price'] <= 100)
                            $price_match = true;
                        break;
                    case '100-200':
                        if ($product['price'] > 100 && $product['price'] <= 200)
                            $price_match = true;
                        break;
                    case '200+':
                        if ($product['price'] > 200)
                            $price_match = true;
                        break;
                }
                if ($price_match)
                    break;
            }
            if (!$price_match)
                return false;
        }

        if ($selected_rating > 0 && $product['rating'] < $selected_rating) {
            return false;
        }

        if (!empty($selected_availability)) {
            if (!in_array('in-stock', $selected_availability)) {
                return false;
            }
        }

        return true;
    });
}

if (!empty($products) && $sort !== 'featured') {
    uasort($products, function ($a, $b) use ($sort) {
        switch ($sort) {
            case 'price-low':
                return $a['price'] <=> $b['price'];
            case 'price-high':
                return $b['price'] <=> $a['price'];
            case 'rating':
                return $b['rating'] <=> $a['rating'];
            case 'newest':
                return $b['id'] <=> $a['id'];
            default:
                return 0;
        }
    });
}
?>