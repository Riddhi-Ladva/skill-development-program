<header id="site-header">
    <div class="header-top">
        <div class="logo">
            <h1>EasyCart</h1>
        </div>
        <div class="search-bar">
            <form action="/easycart/pages/products.php" method="get" role="search">
                <input type="search" id="search-input" name="q" placeholder="Search products..."
                    aria-label="Search products">
                <button type="submit">Search</button>
            </form>
        </div>
        <div class="header-actions">
            <a href="/easycart/pages/login.php" class="action-link" aria-label="Login">Login</a>
            <a href="/easycart/pages/cart.php" class="action-link" aria-label="View cart">Cart (
                <?php echo array_sum($_SESSION['cart']); ?>)
            </a>
        </div>
    </div>
    <nav id="main-navigation" aria-label="Main navigation">
        <ul>
            <li><a href="/easycart/index.php">Home</a></li>
            <li><a href="/easycart/pages/products.php">Products</a></li>
            <li><a href="/easycart/pages/products.php?category=electronics">Electronics</a></li>
            <li><a href="/easycart/pages/products.php?category=clothing">Clothing</a></li>
            <li><a href="/easycart/pages/products.php?category=home">Home & Garden</a></li>
            <li><a href="/easycart/pages/products.php?category=sports">Sports</a></li>
        </ul>
    </nav>
</header>