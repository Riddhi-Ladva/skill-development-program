/**
 * Product Detail Page UI Interactions
 * 
 * Responsibility: Handles client-side behavior for the product detail page, 
 * like switching images and navigating between description/review tabs.
 * 
 * Why it exists: To make the product detail page interactive and provide 
 * a better user experience without reloading.
 * 
 * When it runs: Runs on DOMContentLoaded when the user is on a product detail page.
 */
document.addEventListener('DOMContentLoaded', () => {
    // Image Switching Logic
    const mainImage = document.querySelector('.main-image img');
    const thumbnails = document.querySelectorAll('.thumbnail-gallery button');

    if (mainImage && thumbnails.length > 0) {
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', () => {
                const img = thumbnail.querySelector('img');
                if (img) {
                    // Update main image src and alt
                    mainImage.src = img.src.replace('w=200', 'w=600'); // Assuming unsplash w param
                    mainImage.alt = img.alt.replace('thumbnail', 'Main view');

                    // Update active state
                    thumbnails.forEach(t => t.classList.remove('active'));
                    thumbnail.classList.add('active');
                }
            });
        });
    }

    // Tab Navigation Logic
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanels = document.querySelectorAll('.tab-panel');

    if (tabButtons.length > 0) {
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetTab = button.getAttribute('data-tab');

                // Update button states
                tabButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.setAttribute('aria-selected', 'false');
                });
                button.classList.add('active');
                button.setAttribute('aria-selected', 'true');

                // Update panel visibility
                tabPanels.forEach(panel => {
                    panel.classList.remove('active');
                });
                const activePanel = document.getElementById(targetTab);
                if (activePanel) {
                    activePanel.classList.add('active');
                }
            });
        });
    }

    // Wishlist Logic
    const wishlistBtn = document.querySelector('.wishlist-button');
    if (wishlistBtn) {
        wishlistBtn.addEventListener('click', async () => {
            const productId = wishlistBtn.dataset.productId;
            if (!productId) return;

            // Visual feedback - loading
            const originalText = wishlistBtn.querySelector('.button-text').textContent;
            wishlistBtn.querySelector('.button-text').textContent = 'Adding...';
            wishlistBtn.disabled = true;

            try {
                const response = await fetch('../ajax/wishlist/add.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ product_id: productId })
                });

                const result = await response.json();

                if (result.success) {
                    wishlistBtn.querySelector('.button-text').textContent = 'Added to Wishlist';
                    wishlistBtn.classList.add('active'); // Style for filled heart
                    // Optional: Show toast
                } else {
                    if (result.error === 'auth_required') {
                        window.location.href = '../pages/login.php';
                    } else if (result.error === 'already_exists') {
                        wishlistBtn.querySelector('.button-text').textContent = 'In Wishlist';
                        wishlistBtn.classList.add('active');
                    } else {
                        alert(result.message || 'Error adding to wishlist');
                        wishlistBtn.querySelector('.button-text').textContent = originalText;
                        wishlistBtn.disabled = false;
                    }
                }
            } catch (error) {
                console.error('Wishlist error:', error);
                wishlistBtn.querySelector('.button-text').textContent = originalText;
                wishlistBtn.disabled = false;
            }
        });
    }

});
