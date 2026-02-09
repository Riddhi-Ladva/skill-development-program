<?php
/**
 * Global Footer
 * 
 * Responsibility: Renders the site information, links, and copyright notice.
 * 
 * Why it exists: To provide a consistent footer across all pages.
 * 
 * When it runs: Included at the bottom of every page.
 */
?>
<footer id="site-footer">

    <div class="footer-content">
        <section class="footer-section">
            <h3>About EasyCart</h3>
            <p>Your trusted online shopping destination for quality products at competitive prices.</p>
        </section>
        <section class="footer-section">
            <h3>Customer Service</h3>
            <ul>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">Shipping Information</a></li>
                <li><a href="#">Returns & Exchanges</a></li>
                <li><a href="#">FAQs</a></li>
            </ul>
        </section>
        <section class="footer-section">
            <h3>My Account</h3>
            <ul>
                <li><a href="<?php echo url('login'); ?>">Login</a></li>
                <li><a href="<?php echo url('signup'); ?>">Create Account</a></li>
                <li><a href="<?php echo url('orders'); ?>">Order History</a></li>
                <li><a href="<?php echo url('cart'); ?>">Shopping Cart</a></li>
            </ul>
        </section>
        <section class="footer-section">
            <h3>Policies</h3>
            <ul>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
                <li><a href="#">Cookie Policy</a></li>
            </ul>
        </section>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 EasyCart. All rights reserved.</p>
    </div>
</footer>

<script src="<?php echo asset('js/wishlist/wishlist.js?v=2'); ?>"></script>