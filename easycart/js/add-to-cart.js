/**
 * AJAX Add to Cart Functionality
 * Handles adding items to cart without page reload.
 */
document.addEventListener('DOMContentLoaded', () => {
    // Select all add to cart buttons (links in listing, buttons in details)
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn, .add-to-cart-trigger');
    const headerCount = document.getElementById('header-total-items'); // Span in header
    const cartCountBadge = document.querySelector('.cart-count'); // Badge in nav (if exists)

    addToCartButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();

            // Get Product ID and Quantity
            const productId = button.dataset.productId;
            let quantity = 1;

            // Check if there's a quantity input associated (for detail page)
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

            // Visual Feedback - Loading State
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = 'Adding...';

            try {
                const response = await fetch('add-to-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        quantity: quantity
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Update Header Count
                    if (headerCount) {
                        headerCount.textContent = data.totalItems;
                    }
                    if (cartCountBadge) {
                        cartCountBadge.textContent = data.totalItems;
                    }

                    // Success Feedback
                    button.innerHTML = '✔ Added';
                    button.classList.add('btn-success');

                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                        button.classList.remove('btn-success');
                    }, 2000);
                } else {
                    console.error('Failed to add to cart:', data.message);
                    button.innerHTML = 'Error';
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }, 2000);
                }

            } catch (error) {
                console.error('Error adding to cart:', error);
                button.innerHTML = 'Error';
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 2000);
            }
        });
    });
});
