/**
 * MY STUDY NOTES: AJAX Add to Cart
 * 
 * Goal: Add a product to the cart without the whole page reloading.
 * 
 * How it works:
 * 1. Find the "Add to Cart" button.
 * 2. When clicked, STOP the browser from following the link (e.preventDefault).
 * 3. Send a "Fetch" request to the PHP backend.
 * 4. When PHP says "Done", update the count in the header.
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
                // The actual "Messenger" that goes to the server
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

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Success! Now update the numbers in the header
                    // data.totalItems is sent back by the PHP file
                    if (headerCount) {
                        headerCount.textContent = data.totalItems;
                        const cartLink = headerCount.closest('.icon-wrapper');
                        if (cartLink) {
                            cartLink.classList.toggle('has-items', parseInt(data.totalItems) > 0);
                        }
                    }
                    if (cartCountBadge) {
                        cartCountBadge.textContent = data.totalItems;
                    }

                    // Show a nice checkmark for 2 seconds
                    button.innerHTML = '✔ Added';
                    button.classList.add('btn-success');

                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                        button.classList.remove('btn-success');
                    }, 2000);
                } else {
                    // If PHP rejected it, show an error state
                    console.error('Failed to add to cart:', data.message);
                    button.innerHTML = 'Error';
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }, 2000);
                }

            } catch (error) {
                // If the internet goes out or the server crashes
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
