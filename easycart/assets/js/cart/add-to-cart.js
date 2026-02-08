/**
 * Add To Cart Component
 * 
 * Capability: Handles asynchronous addition of products to the shopping cart.
 * Interaction: Intercepts click events on specific CSS classes, prevents default navigation,
 * and submits data to the PHP backend via fetch API.
 */
document.addEventListener('DOMContentLoaded', () => {
    // Select all add to cart buttons (links in listing, buttons in details)
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn, .add-to-cart-trigger');
    const headerCount = document.getElementById('header-total-items');
    const cartCountBadge = document.querySelector('.cart-count');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            // Very Important: prevent the page from jumping or reloading
            e.preventDefault();

            // Dataset attributes are set in the HTML (e.g. data-product-id="1")
            const productId = button.dataset.productId;
            let quantity = 1;

            // If we are on the details page, look for a quantity input box
            const form = button.closest('form');
            if (form) {
                const qtyInput = form.querySelector('input[name="quantity"]');
                if (qtyInput) {
                    quantity = parseInt(qtyInput.value);
                }
            }

            if (!productId) {
                console.error('No product ID found');
                return;
            }

            // UI Feedback: Change button text so the user knows "something is happening"
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = 'Adding...';

            try {
                // Post to cart endpoint
                const response = await fetch(`${EasyCart.ajaxUrl}/cart/add.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    })
                });

                if (response.status === 401) {
                    // Redirect to login if unauthorized
                    window.location.href = `${EasyCart.baseUrl}/pages/login.php`;
                    return;
                }

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Update global header badge
                    // Update global header badge
                    if (headerCount) {
                        headerCount.textContent = data.totalItems;
                        const cartLink = headerCount.closest('.icon-wrapper');
                        if (cartLink) {
                            cartLink.classList.toggle('has-items', parseInt(data.totalItems) > 0);
                        }
                    }

                    // REFRESH CART UI (If we are on the cart page)
                    if (window.EasyCart && window.EasyCart.UI && window.EasyCart.UI.refreshCartHTML) {
                        window.EasyCart.UI.refreshCartHTML();
                    }

                    // Success Feedback
                    button.innerHTML = '✔ Added';
                    button.classList.add('btn-success');

                    // If "Buy Now", redirect to cart immediately
                    if (button.classList.contains('buy-now-button')) {
                        window.location.href = `${EasyCart.baseUrl}/pages/cart.php`;
                        return;
                    }

                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                        button.classList.remove('btn-success');
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Failed to add to cart');
                }

            } catch (error) {
                console.error('Cart Error:', error);
                button.innerHTML = 'Error';
                button.style.backgroundColor = '#ef4444'; // Red for error

                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    button.style.backgroundColor = ''; // Reset
                }, 2000);
            }
        });
    });
});
