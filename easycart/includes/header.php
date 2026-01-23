<?php
// Include configuration
require_once __DIR__ . '/config.php';
?>
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
        <div class="header-actions">
            <a href="<?php echo url('pages/login.php'); ?>" class="action-link" aria-label="Login">Login</a>
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            if ($current_page !== 'login.php' && $current_page !== 'signup.php'):
                ?>
                <a href="<?php echo url('pages/cart.php'); ?>" class="action-link cart-link" aria-label="View cart">Cart (
                    <?php echo array_sum($_SESSION['cart']); ?>)
                </a>
            <?php endif; ?>
        </div>
    </div>
    <nav id="main-navigation" aria-label="Main navigation">
        <ul>
            <li><a href="<?php echo url('index.php'); ?>">Home</a></li>
            <li><a href="<?php echo url('pages/products.php'); ?>">Products</a></li>
            <li><a href="<?php echo url('pages/products.php?category=electronics'); ?>">Electronics</a></li>
            <li><a href="<?php echo url('pages/products.php?category=clothing'); ?>">Clothing</a></li>
            <li><a href="<?php echo url('pages/products.php?category=home'); ?>">Home & Garden</a></li>
            <li><a href="<?php echo url('pages/products.php?category=sports'); ?>">Sports</a></li>
        </ul>
    </nav>
</header>