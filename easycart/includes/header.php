<?php
/**
 * Global Header
 * 
 * Responsibility: Renders the site logo, search bar, navigation links, and account/cart buttons.
 * 
 * Why it exists: To provide consistent navigation and branding across all pages.
 * 
 * When it runs: Included at the top of every user-facing page.
 */

// Load configuration
require_once __DIR__ . '/bootstrap/config.php';

// Enforce Cache Control for any page with a header (User/Auth state visible)
require_once __DIR__ . '/auth/cache-control.php';

// Determine current page and category for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_category = isset($_GET['category']) ? $_GET['category'] : '';

// Check if categories are already loaded (from page logic), otherwise fetch them
if (!isset($categories)) {
    if (isset($all_categories)) {
        $categories = $all_categories;
    } else {
        // Fallback: fetch directly if page logic didn't provide
        require_once __DIR__ . '/db-functions.php';
        $categories = get_all_categories();
    }
}
?>

<!-- Global EasyCart configuration for JavaScript -->
<script>
    window.EasyCart = {
        baseUrl: '<?php echo BASE_PATH; ?>',
        ajaxUrl: '<?php echo url('ajax'); ?>',
        userId: <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>,
        wishlist: <?php
        $wishlist_ids = [];
        if (isset($_SESSION['user_id'])) {
            try {
                if (!function_exists('get_user_wishlist')) {
                    require_once __DIR__ . '/db-functions.php';
                }
                $wishlist_ids = get_user_wishlist($_SESSION['user_id']);
            } catch (Exception $e) {
                error_log("Wishlist Header Error: " . $e->getMessage());
            }
        }
        echo json_encode(is_array($wishlist_ids) ? $wishlist_ids : []);
        ?>
    };
</script>

<?php if (isset($_SESSION['clear_guest_wishlist'])): ?>
    <script>
        localStorage.removeItem('wishlist');
    </script>
    <?php unset($_SESSION['clear_guest_wishlist']); endif; ?>

<header id="site-header">
    <div class="header-top">
        <div class="logo">
            <h1><a href="<?php echo url('index'); ?>">EasyCart</a></h1>
        </div>
        <div class="search-bar">
            <form action="<?php echo url('products'); ?>" method="get" role="search">
                <input type="search" id="search-input" name="q" placeholder="Search products..."
                    aria-label="Search products">
                <button type="submit">Search</button>
            </form>
        </div>
        <!-- Global Notification Container -->
        <div id="global-notification-container"></div>
        <div class="header-actions">
            <!-- 1. Wishlist Section -->
            <?php
            if ($current_page !== 'login.php' && $current_page !== 'signup.php'):
                if (!function_exists('getWishlistCount')) {
                    require_once dirname(__DIR__) . '/includes/wishlist/services.php';
                }
                $initial_wishlist_count = getWishlistCount();
                ?>
                <a href="<?php echo url('cart#wishlist-section'); ?>"
                    class="action-link icon-wrapper wishlist-link <?php echo $initial_wishlist_count > 0 ? 'has-items' : ''; ?>"
                    aria-label="View wishlist">
                    <i class="wishlist-icon" aria-hidden="true"></i>
                    <span class="icon-badge" id="header-wishlist-count"><?php echo $initial_wishlist_count; ?></span>
                </a>
            <?php endif; ?>

            <!-- 2. Cart Section -->
            <?php
            if ($current_page !== 'login.php' && $current_page !== 'signup.php'):
                if (!function_exists('getCartCount')) {
                    require_once dirname(__DIR__) . '/includes/cart/services.php';
                }
                $header_cart_count = getCartCount();
                ?>
                <a href="<?php echo url('cart'); ?>"
                    class="action-link icon-wrapper cart-link <?php echo $header_cart_count > 0 ? 'has-items' : ''; ?> <?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>"
                    aria-label="View cart">
                    <i class="cart-icon">🛒</i>
                    <span class="icon-badge" id="header-total-items"><?php echo $header_cart_count; ?></span>
                </a>
            <?php endif; ?>

            <!-- 3. Profile / Account Section (Now Last) -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                // Fetch user name for a personal touch
                if (!isset($user_display_name)) {
                    $stmt = getDbConnection()->prepare("SELECT first_name, last_name, email FROM users WHERE id = :id");
                    $stmt->execute(['id' => $_SESSION['user_id']]);
                    $u = $stmt->fetch();
                    if ($u) {
                        if (!empty($u['first_name'])) {
                            $user_display_name = trim($u['first_name'] ?? '');
                        } else {
                            $user_display_name = 'My Profile';
                        }
                    } else {
                        $user_display_name = 'User';
                    }
                }
                ?>
                <div class="profile-dropdown-container">
                    <button class="action-link icon-wrapper profile-toggle" aria-label="Account menu" aria-haspopup="true"
                        id="profile-menu-toggle">

                        <i class="profile-icon">👤</i>
                        <span class="profile-label"><?php echo htmlspecialchars($user_display_name); ?></span>
                    </button>
                    <div class="profile-dropdown" id="profile-dropdown-menu">
                        <ul>
                            <li><a href="<?php echo url('dashboard'); ?>">Dashboard</a></li>
                            <li><a href="<?php echo url('orders'); ?>">Orders</a></li>
                            <li><a href="<?php echo url('edit-profile'); ?>">Edit Profile</a></li>
                            <li class="divider"></li>
                            <li><a href="<?php echo url('logout'); ?>" class="logout-link">Logout</a></li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo url('login'); ?>" class="action-link header-login-link" aria-label="Login">
                    <span>Login</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <nav id="main-navigation" aria-label="Main navigation">
        <ul>
            <li><a href="<?php echo url('index'); ?>"
                    class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="<?php echo url('products'); ?>"
                    class="<?php echo ($current_page == 'products.php' && empty($current_category)) ? 'active' : ''; ?>">Products</a>
            </li>
            <?php foreach ($categories as $slug => $cat_info): ?>
                <li><a href="<?php echo url('products?category=' . $slug); ?>"
                        class="<?php echo ($current_page == 'products.php' && $current_category == $slug) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat_info['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <script src="<?php echo asset('js/utils/confirm.js'); ?>"></script>

    <script src="<?php echo asset('js/layout/profile-dropdown.js'); ?>"></script>
</header>