<?php require_once '../includes/signup/logic.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Create your EasyCart account and start shopping">
    <title>Sign Up - EasyCart</title>
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=1.1'); ?>">
</head>

<body>
    <!-- HEADER ADDED: minimal auth page header (logo + back to home) -->
    <header id="site-header">
        <div class="header-top">
            <div class="logo">
                <h1><a href="<?php echo url('index'); ?>">EasyCart</a></h1>
            </div>
            <div class="header-actions">
                <a href="<?php echo url('index'); ?>" class="action-link">Back to Home</a>
                <a href="<?php echo url('login'); ?>" class="action-link">Login</a>
            </div>
        </div>
    </header>

    <main id="main-content">
        <div class="signup-container">
            <section class="signup-form">
                <header class="form-header">
                    <h1>Create Your Account</h1>
                    <p>Join thousands of happy shoppers</p>
                </header>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error"
                        style="background: #fee2e2; border: 1px solid #ef4444; padding: 10px; margin-bottom: 20px; border-radius: 4px; color: #b91c1c;">
                        <ul style="margin: 0; padding-left: 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?php echo url('signup'); ?>" method="post" novalidate>
                    <fieldset>
                        <legend class="visually-hidden">Personal Information</legend>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="first-name">First Name <abbr title="required">*</abbr></label>
                                <input type="text" id="first-name" name="first-name" required autocomplete="given-name"
                                    placeholder="John">
                            </div>

                            <div class="form-group">
                                <label for="last-name">Last Name <abbr title="required">*</abbr></label>
                                <input type="text" id="last-name" name="last-name" required autocomplete="family-name"
                                    placeholder="Doe">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address <abbr title="required">*</abbr></label>
                            <input type="email" id="email" name="email" required autocomplete="email"
                                placeholder="john.doe@example.com">
                            <p class="field-hint">We'll send your order confirmations to this email</p>
                        </div>

                        <div class="form-group">
                            <label for="password">Password <abbr title="required">*</abbr></label>
                            <input type="password" id="password" name="password" required autocomplete="new-password"
                                minlength="8" placeholder="Create a strong password">
                            <p class="field-hint">Must be at least 8 characters</p>
                        </div>

                        <div class="form-group">
                            <label for="confirm-password">Confirm Password <abbr title="required">*</abbr></label>
                            <input type="password" id="confirm-password" name="confirm-password" required
                                autocomplete="new-password" minlength="8" placeholder="Re-enter your password">
                        </div>
                    </fieldset>

                    <button type="submit" class="signup-button">Create Account</button>
                </form>

                <footer class="form-footer">
                    <p>
                        Already have an account?
                        <a href="<?php echo url('login'); ?>">Login here</a>
                    </p>
                </footer>
            </section>

            <aside class="signup-benefits">
                <h2>Why Join EasyCart?</h2>
                <ul>
                    <li>
                        <h3>Exclusive Member Deals</h3>
                        <p>Get access to special discounts and early access to sales</p>
                    </li>
                    <li>
                        <h3>Fast & Easy Checkout</h3>
                        <p>Save your shipping and payment information for quick purchases</p>
                    </li>
                    <li>
                        <h3>Order Tracking</h3>
                        <p>Track all your orders in one convenient location</p>
                    </li>
                    <li>
                        <h3>Personalized Recommendations</h3>
                        <p>Discover products tailored to your interests</p>
                    </li>
                    <li>
                        <h3>Wishlist & Save for Later</h3>
                        <p>Keep track of products you love</p>
                    </li>
                    <li>
                        <h3>Easy Returns</h3>
                        <p>Hassle-free returns on all orders</p>
                    </li>
                    <li>
                        <h3>Birthday Rewards</h3>
                        <p>Receive special offers on your birthday</p>
                    </li>
                    <li>
                        <h3>24/7 Customer Support</h3>
                        <p>Priority support for all members</p>
                    </li>
                </ul>

                <section class="testimonials">
                    <h3>What Our Customers Say</h3>
                    <blockquote>
                        <p>"EasyCart has made online shopping so convenient. The member deals are amazing!"</p>
                        <footer>- Sarah M.</footer>
                    </blockquote>
                    <blockquote>
                        <p>"Fast shipping and great customer service. Highly recommend creating an account."</p>
                        <footer>- John D.</footer>
                    </blockquote>
                </section>
            </aside>
        </div>

        <section class="trust-indicators">
            <h2>Shop with Confidence</h2>
            <div class="trust-grid">
                <article class="trust-item">
                    <h3>Secure & Encrypted</h3>
                    <p>Your personal information is protected with 256-bit SSL encryption</p>
                </article>
                <article class="trust-item">
                    <h3>Privacy Protected</h3>
                    <p>We never share your personal information to third parties</p>
                </article>
                <article class="trust-item">
                    <h3>No Spam</h3>
                    <p>You control your email preferences and can unsubscribe anytime</p>
                </article>
                <article class="trust-item">
                    <h3>Free to Join</h3>
                    <p>Creating an account is always free with no hidden fees</p>
                </article>
            </div>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
    <script src="<?php echo asset('js/auth/auth-validation.js'); ?>"></script>
</body>

</html>