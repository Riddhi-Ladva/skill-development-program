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

// Determine current page and category for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_category = isset($_GET['category']) ? $_GET['category'] : '';

// Check if categories are already loaded (from page logic), otherwise fetch them
if (!isset($categories)) {
    if (isset($all_categories)) {
        $categories = $all_categories;
    } else {
        // Fallback: fetch directly if page logic didn't provide
        require_once __DIR__ . '/db_functions.php';
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
        if (isset($_SESSION['user_id'])) {
            if (!function_exists('get_user_wishlist')) {
                require_once __DIR__ . '/db_functions.php';
            }
            echo json_encode(get_user_wishlist($_SESSION['user_id']));
        } else {
            echo '[]';
        }
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
            <h1><a href="<?php echo url('index.php'); ?>">EasyCart</a></h1>
        </div>
        <div class="search-bar">
            <form action="<?php echo url('pages/products.php'); ?>" method="get" role="search">
                <input type="search" id="search-input" name="q" placeholder="Search products..."
                    aria-label="Search products">
                <button type="submit">Search</button>
            </form>
        </div>
        <!-- Global Notification Container -->
        <div id="global-notification-container"></div>
        <div class="header-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo url('pages/dashboard.php'); ?>" class="action-link" aria-label="Dashboard">Dashboard</a>
                <a href="<?php echo url('pages/logout.php'); ?>" class="action-link" aria-label="Logout">Logout</a>
            <?php else: ?>
                <a href="<?php echo url('pages/login.php'); ?>" class="action-link" aria-label="Login">Login</a>
            <?php endif; ?>
            <?php
            $initial_wishlist_count = 0;
            if (isset($_SESSION['user_id'])) {
                if (!function_exists('get_user_wishlist')) {
                    require_once __DIR__ . '/db_functions.php';
                }
                $initial_wishlist_count = count(get_user_wishlist($_SESSION['user_id']));
            }
            ?>
            <a href="<?php echo url('pages/cart.php#wishlist-section'); ?>"
                class="action-link icon-wrapper wishlist-link <?php echo $initial_wishlist_count > 0 ? 'has-items' : ''; ?>"
                aria-label="View wishlist">
                <i class="wishlist-icon" aria-hidden="true"></i>
                <span class="icon-badge" id="header-wishlist-count"><?php echo $initial_wishlist_count; ?></span>
            </a>
            <?php
            if ($current_page !== 'login.php' && $current_page !== 'signup.php'):
                if (!function_exists('getCartCount')) {
                    require_once dirname(__DIR__) . '/includes/cart/services.php';
                }
                $header_cart_count = getCartCount();
                ?>
                <a href="<?php echo url('pages/cart.php'); ?>"
                    class="action-link icon-wrapper cart-link <?php echo $header_cart_count > 0 ? 'has-items' : ''; ?> <?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>"
                    aria-label="View cart">
                    <i class="cart-icon">🛒</i>
                    <span class="icon-badge" id="header-total-items"><?php echo $header_cart_count; ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <nav id="main-navigation" aria-label="Main navigation">
        <ul>
            <li><a href="<?php echo url('index.php'); ?>"
                    class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="<?php echo url('pages/products.php'); ?>"
                    class="<?php echo ($current_page == 'products.php' && empty($current_category)) ? 'active' : ''; ?>">Products</a>
            </li>
            <?php foreach ($categories as $slug => $cat_info): ?>
                <li><a href="<?php echo url('pages/products.php?category=' . $slug); ?>"
                        class="<?php echo ($current_page == 'products.php' && $current_category == $slug) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat_info['name']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <script src="<?php echo asset('js/utils/confirm.js'); ?>"></script>
</header>