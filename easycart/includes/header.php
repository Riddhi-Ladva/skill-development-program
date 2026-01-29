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
?>

<!-- Global EasyCart configuration for JavaScript -->
<script>
    window.EasyCart = {
        baseUrl: '<?php echo BASE_PATH; ?>',
        ajaxUrl: '<?php echo url('ajax'); ?>'
    };
</script>

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
            <a href="<?php echo url('pages/login.php'); ?>" class="action-link" aria-label="Login">Login</a>
            <a href="<?php echo url('pages/cart.php#wishlist-section'); ?>"
                class="action-link icon-wrapper wishlist-link <?php echo (isset($_SESSION['wishlist']) && count($_SESSION['wishlist']) > 0) ? 'has-items' : ''; ?>"
                aria-label="View wishlist">
                <i class="wishlist-icon" aria-hidden="true"></i>
                <span class="icon-badge" id="header-wishlist-count">0</span>
            </a>
            <?php
            if ($current_page !== 'login.php' && $current_page !== 'signup.php'):
                $header_cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
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
            <li><a href="<?php echo url('pages/products.php?category=electronics'); ?>"
                    class="<?php echo ($current_page == 'products.php' && $current_category == 'electronics') ? 'active' : ''; ?>">Electronics</a>
            </li>
            <li><a href="<?php echo url('pages/products.php?category=clothing'); ?>"
                    class="<?php echo ($current_page == 'products.php' && $current_category == 'clothing') ? 'active' : ''; ?>">Clothing</a>
            </li>
            <li><a href="<?php echo url('pages/products.php?category=home'); ?>"
                    class="<?php echo ($current_page == 'products.php' && $current_category == 'home') ? 'active' : ''; ?>">Home
                    & Garden</a></li>
            <li><a href="<?php echo url('pages/products.php?category=sports'); ?>"
                    class="<?php echo ($current_page == 'products.php' && $current_category == 'sports') ? 'active' : ''; ?>">Sports</a>
            </li>
        </ul>
    </nav>
    <script src="<?php echo asset('js/utils/confirm.js'); ?>"></script>
</header>