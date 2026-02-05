<?php require_once '../includes/login/logic.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to your EasyCart account">
    <title>Login - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.1'); ?>">
</head>

<body>
    <!-- HEADER ADDED: minimal auth page header (logo + back to home) -->
    <header id="site-header">
        <div class="header-top">
            <div class="logo">
                <h1><a href="<?php echo url('index.php'); ?>">EasyCart</a></h1>
            </div>
            <div class="header-actions">
                <a href="<?php echo url('index.php'); ?>" class="action-link">Back to Home</a>
                <a href="<?php echo url('pages/signup.php'); ?>" class="action-link">Sign Up</a>
            </div>
        </div>
    </header>

    <main id="main-content">
        <div class="login-container">
            <section class="login-form">
                <header class="form-header">
                    <h1>Login to Your Account</h1>
                    <p>Welcome back! Please enter your details</p>
                </header>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error"
                        style="background: #fee2e2; border: 1px solid #ef4444; padding: 10px; margin-bottom: 20px; border-radius: 4px; color: #b91c1c;">
                        <?php foreach ($errors as $error): ?>
                            <p style="margin: 0;"><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($loginSuccessMessage): ?>
                    <div class="alert alert-success"
                        style="background: #d1fae5; border: 1px solid #10b981; padding: 10px; margin-bottom: 20px; border-radius: 4px; color: #047857;">
                        <p style="margin: 0;"><?php echo htmlspecialchars($loginSuccessMessage); ?></p>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="post" novalidate>
                    <fieldset>
                        <legend class="visually-hidden">Login credentials</legend>

                        <div class="form-group">
                            <label for="email">Email Address <abbr title="required">*</abbr></label>
                            <input type="email" id="email" name="email" required autocomplete="email"
                                placeholder="Enter your email">
                        </div>

                        <div class="form-group">
                            <label for="password">Password <abbr title="required">*</abbr></label>
                            <input type="password" id="password" name="password" required
                                autocomplete="current-password" placeholder="Enter your password" minlength="8">
                        </div>

                        <div class="form-options">
                            <label class="remember-me">
                                <input type="checkbox" name="remember" value="yes">
                                Remember me
                            </label>
                            <a href="#" class="forgot-password">Forgot password?</a>
                        </div>

                        <input type="hidden" name="guest_wishlist_data" id="guest-wishlist-data">
                        <button type="submit" class="login-button">Login</button>
                    </fieldset>
                </form>

                <div class="divider">
                    <span>Or continue with</span>
                </div>

                <section class="social-login">
                    <h2 class="visually-hidden">Social login options</h2>
                    <button type="button" class="social-button google">
                        <span class="icon">G</span>
                        Continue with Google
                    </button>
                    <button type="button" class="social-button facebook">
                        <span class="icon">f</span>
                        Continue with Facebook
                    </button>
                    <button type="button" class="social-button apple">
                        <span class="icon">Apple</span>
                        Continue with Apple
                    </button>
                </section>

                <footer class="form-footer">
                    <p>
                        Don't have an account?
                        <a href="signup.php">Sign up for free</a>
                    </p>
                </footer>
            </section>

            <aside class="login-benefits">
                <h2>Benefits of Having an Account</h2>
                <ul>
                    <li>
                        <h3>Fast Checkout</h3>
                        <p>Save your information for quick and easy checkout</p>
                    </li>
                    <li>
                        <h3>Order Tracking</h3>
                        <p>Track your orders and view your order history</p>
                    </li>
                    <li>
                        <h3>Wishlist</h3>
                        <p>Save products to buy later</p>
                    </li>
                    <li>
                        <h3>Exclusive Offers</h3>
                        <p>Get access to member-only deals and promotions</p>
                    </li>
                    <li>
                        <h3>Easy Returns</h3>
                        <p>Simplified return process for your orders</p>
                    </li>
                </ul>
            </aside>
        </div>

        <section class="security-info">
            <h2>Your Security is Our Priority</h2>
            <div class="security-features">
                <article class="security-item">
                    <h3>Encrypted Connection</h3>
                    <p>All data is transmitted securely using SSL encryption</p>
                </article>
                <article class="security-item">
                    <h3>Privacy Protected</h3>
                    <p>We never share your personal information</p>
                </article>
                <article class="security-item">
                    <h3>Secure Payment</h3>
                    <p>Your payment information is processed securely</p>
                </article>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo asset('js/auth/auth-validation.js'); ?>"></script>
    <script>
        document.querySelector('form[action="login.php"]').addEventListener('submit', function () {
            const wishlist = localStorage.getItem('wishlist');
            if (wishlist) {
                document.getElementById('guest-wishlist-data').value = wishlist;
            }
        });
    </script>
</body>

</html>