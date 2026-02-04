<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Fetch all brands from the database
 * Returns an array indexed by brand ID
 */
function get_all_brands()
{
    $pdo = getDbConnection();
    // Updated table name: catalog_brand
    $stmt = $pdo->prepare("SELECT id, name, logo_url as logo FROM catalog_brand ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
}

/**
 * Fetch all categories from the database
 */
function get_all_categories()
{
    $pdo = getDbConnection();
    // Updated table name: catalog_category_entity
    $stmt = $pdo->prepare("SELECT * FROM catalog_category_entity ORDER BY name");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categories = [];
    foreach ($rows as $row) {
        $slug = $row['slug']; // Slug is now a required column

        $categories[$slug] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'slug' => $slug,
            'description' => '', // Schema doesn't have description in category table yet, safely omitting
        ];
    }
    return $categories;
}

/**
 * Fetch products with filtering and sorting
 */
function get_products($args = [])
{
    $pdo = getDbConnection();

    // Default arguments
    $defaults = [
        'query' => '',
        'category' => [], // array of slugs
        'brand_id' => [], // array of IDs
        'price_range' => [], // array of strings '0-25' etc
        'rating' => 0,
        'availability' => [],
        'sort' => 'featured',
        'limit' => null,
        'offset' => 0
    ];

    $params = array_merge($defaults, $args);
    $bindings = [];

    // Base Query
    // Updated tables:
    // catalog_product_entity (p)
    // catalog_brand (b)
    // catalog_product_price (pp) - Need to join this for price
    // catalog_product_images (pi) - Need to join for main image
    // catalog_category_products (ccp) & catalog_category_entity (c) - for categories

    // Note: Rating/Reviews are not in the provided schema. I will mock them consistently or use default 0. 
    // Assuming 'status' smallint 1=enabled.

    $sql = "SELECT p.id, p.name, p.status, b.name as brand_name, p.brand_id,
            COALESCE(pp.price, 0) as price,
            pi.image_path as image,
            -- Subquery for category slug (first one found)
            (SELECT c.slug FROM catalog_category_entity c 
             JOIN catalog_category_products ccp ON ccp.category_id = c.id 
             WHERE ccp.product_id = p.id LIMIT 1) as category,
             -- Mocking rating/reviews as they are not in schema
             4.5 as rating,
             0 as reviews
            FROM catalog_product_entity p
            LEFT JOIN catalog_brand b ON p.brand_id = b.id
            LEFT JOIN catalog_product_price pp ON pp.product_id = p.id AND pp.customer_group_id = 0
            LEFT JOIN catalog_product_images pi ON pi.product_id = p.id AND pi.is_main = TRUE
            WHERE p.status = 1"; // 1 = enabled

    // 1. Search Query
    if (!empty($params['query'])) {
        $sql .= " AND (p.name ILIKE :query OR b.name ILIKE :query)";
        // Description is in catalog_product_attribute potentially? Schema has 'catalog_product_attribute' but it's EAVish. 
        // For simplicity, searching name and brand.
        $bindings[':query'] = '%' . $params['query'] . '%';
    }

    // 2. Category Filter
    if (!empty($params['category'])) {
        $cats = is_array($params['category']) ? $params['category'] : [$params['category']];
        if (!empty($cats)) {
            $placeholders = [];
            foreach ($cats as $i => $c) {
                $key = ":cat_$i";
                $placeholders[] = $key;
                $bindings[$key] = $c; // slug
            }
            $in_clause = implode(',', $placeholders);

            $sql .= " AND EXISTS (
                SELECT 1 FROM catalog_category_products ccp 
                JOIN catalog_category_entity c ON ccp.category_id = c.id 
                WHERE ccp.product_id = p.id AND c.slug IN ($in_clause)
            )";
        }
    }

    // 3. Brand Filter
    if (!empty($params['brand_id'])) {
        $brands = is_array($params['brand_id']) ? $params['brand_id'] : [$params['brand_id']];
        if (!empty($brands)) {
            $placeholders = [];
            foreach ($brands as $i => $id) {
                $key = ":brand_$i";
                $placeholders[] = $key;
                $bindings[$key] = $id;
            }
            $sql .= " AND p.brand_id IN (" . implode(',', $placeholders) . ")";
        }
    }

    // 4. Rating Filter - Skipped as column doesn't exist in schema

    // 5. Price Ranges
    if (!empty($params['price_range'])) {
        $price_sql_parts = [];
        foreach ($params['price_range'] as $range) {
            switch ($range) {
                case '0-25':
                    $price_sql_parts[] = "(pp.price < 25)";
                    break;
                case '25-50':
                    $price_sql_parts[] = "(pp.price >= 25 AND pp.price <= 50)";
                    break;
                case '50-100':
                    $price_sql_parts[] = "(pp.price > 50 AND pp.price <= 100)";
                    break;
                case '100-200':
                    $price_sql_parts[] = "(pp.price > 100 AND pp.price <= 200)";
                    break;
                case '200+':
                    $price_sql_parts[] = "(pp.price > 200)";
                    break;
            }
        }
        if (!empty($price_sql_parts)) {
            $sql .= " AND (" . implode(' OR ', $price_sql_parts) . ")";
        }
    }

    // 6. Availability - using catalog_product_inventory
    if (!empty($params['availability'])) {
        // Checking for 'in-stock'
        if (in_array('in-stock', $params['availability'])) {
            $sql .= " AND EXISTS (SELECT 1 FROM catalog_product_inventory inv WHERE inv.product_id = p.id AND inv.is_in_stock = TRUE)";
        }
    }

    // Sorting
    switch ($params['sort']) {
        case 'price-low':
            $sql .= " ORDER BY pp.price ASC";
            break;
        case 'price-high':
            $sql .= " ORDER BY pp.price DESC";
            break;
        // Rating sort skipped
        case 'newest':
            $sql .= " ORDER BY p.id DESC";
            break;
        case 'featured':
        default:
            $sql .= " ORDER BY p.id ASC";
            break;
    }

    // Limit / Offset
    if ($params['limit']) {
        $sql .= " LIMIT :limit";
        $bindings[':limit'] = (int) $params['limit'];
    }
    if ($params['offset']) {
        $sql .= " OFFSET :offset";
        $bindings[':offset'] = (int) $params['offset'];
    }

    $stmt = $pdo->prepare($sql);
    foreach ($bindings as $k => $v) {
        if (is_int($v)) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($k, $v, PDO::PARAM_STR);
        }
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch limited number of products for homepage
 */
function get_featured_products($limit = 4)
{
    return get_products(['limit' => $limit, 'sort' => 'featured']);
}

/**
 * Fetch single product by ID
 */
function get_product_by_id($id)
{
    $pdo = getDbConnection();

    // EAV Description fetch
    // catalog_product_attribute table: product_id, attribute_code, value
    // We'll subselect description.

    $sql = "SELECT p.id, p.name, p.status, b.name as brand_name, p.brand_id, p.sku,
            COALESCE(pp.price, 0) as price,
            pi.image_path as image,
            (SELECT c.slug FROM catalog_category_entity c 
             JOIN catalog_category_products ccp ON ccp.category_id = c.id 
             WHERE ccp.product_id = p.id LIMIT 1) as category,
            (SELECT value FROM catalog_product_attribute pa WHERE pa.product_id = p.id AND pa.attribute_code = 'description' LIMIT 1) as description,
            4.5 as rating,
            0 as reviews
            FROM catalog_product_entity p
            LEFT JOIN catalog_brand b ON p.brand_id = b.id
            LEFT JOIN catalog_product_price pp ON pp.product_id = p.id AND pp.customer_group_id = 0
            LEFT JOIN catalog_product_images pi ON pi.product_id = p.id AND pi.is_main = TRUE
            WHERE p.id = :id AND p.status = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
/**
 * Fetch or create an active cart for the user
 * @param int $user_id
 * @return int Cart ID
 */
function get_user_cart_id($user_id)
{
    $pdo = getDbConnection();

    // Check for active cart
    $stmt = $pdo->prepare("SELECT id FROM sales_cart WHERE user_id = :user_id AND is_active = TRUE LIMIT 1");
    $stmt->execute([':user_id' => $user_id]);
    $cart_id = $stmt->fetchColumn();

    if (!$cart_id) {
        // Create new cart
        $stmt = $pdo->prepare("INSERT INTO sales_cart (user_id, session_id, is_active, created_at, updated_at) VALUES (:user_id, :session_id, TRUE, NOW(), NOW()) RETURNING id");
        $session_id = (session_status() === PHP_SESSION_ACTIVE) ? session_id() : 'cli-session';
        $stmt->execute([
            ':user_id' => $user_id,
            ':session_id' => $session_id
        ]);
        $cart_id = $stmt->fetchColumn();
    }

    return $cart_id;
}

/**
 * Add or update item in DB cart
 */
function add_to_cart_db($user_id, $product_id, $qty = 1)
{
    $pdo = getDbConnection();
    $cart_id = get_user_cart_id($user_id);

    // Check if item exists
    $stmt = $pdo->prepare("SELECT id, qty FROM sales_cart_items WHERE cart_id = :cart_id AND product_id = :product_id");
    $stmt->execute([
        ':cart_id' => $cart_id,
        ':product_id' => $product_id
    ]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update
        $new_qty = $existing['qty'] + $qty;
        $stmt = $pdo->prepare("UPDATE sales_cart_items SET qty = :qty, updated_at = NOW() WHERE id = :id");
        $stmt->execute([':qty' => $new_qty, ':id' => $existing['id']]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO sales_cart_items (cart_id, product_id, qty, added_at, updated_at) VALUES (:cart_id, :product_id, :qty, NOW(), NOW())");
        $stmt->execute([
            ':cart_id' => $cart_id,
            ':product_id' => $product_id,
            ':qty' => $qty
        ]);
    }
}

/**
 * Update cart quantity in DB
 */
function update_cart_qty_db($user_id, $product_id, $qty)
{
    $pdo = getDbConnection();
    $cart_id = get_user_cart_id($user_id);

    $stmt = $pdo->prepare("UPDATE sales_cart_items SET qty = :qty, updated_at = NOW() WHERE cart_id = :cart_id AND product_id = :product_id");
    $stmt->execute([
        ':qty' => $qty,
        ':cart_id' => $cart_id,
        ':product_id' => $product_id
    ]);
}

/**
 * Remove item from DB cart
 */
function remove_from_cart_db($user_id, $product_id)
{
    $pdo = getDbConnection();
    $cart_id = get_user_cart_id($user_id);

    $stmt = $pdo->prepare("DELETE FROM sales_cart_items WHERE cart_id = :cart_id AND product_id = :product_id");
    $stmt->execute([
        ':cart_id' => $cart_id,
        ':product_id' => $product_id
    ]);
}

/**
 * Fetch cart items from DB
 */
function get_cart_items_db($user_id)
{
    $pdo = getDbConnection();

    $sql = "SELECT sci.product_id, sci.qty 
            FROM sales_cart_items sci
            JOIN sales_cart sc ON sci.cart_id = sc.id
            WHERE sc.user_id = :user_id AND sc.is_active = TRUE";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cart = [];
    foreach ($rows as $row) {
        $cart[$row['product_id']] = $row['qty'];
    }
    return $cart;
}

/**
 * Completely clear user cart (delete items)
 */
function clear_user_cart_db($user_id)
{
    $pdo = getDbConnection();

    // Deleting items is cleaner for "starting fresh" per requirements
    $stmt = $pdo->prepare("
        DELETE FROM sales_cart_items 
        WHERE cart_id IN (SELECT id FROM sales_cart WHERE user_id = :user_id)
    ");
    $stmt->execute([':user_id' => $user_id]);

    // Also mark cart as inactive if needed, but the requirement says start fresh.
    // We'll just delete items for now to ensure no persistence.
}
