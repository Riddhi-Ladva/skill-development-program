/**
 * Wishlist Persistence Logic
 * Handles heart icon toggle and localStorage saving.
 */
document.addEventListener('DOMContentLoaded', () => {
    const wishlistBtn = document.querySelector('.wishlist-button');
    if (!wishlistBtn) return;

    // Ensure productId is a string for consistent comparison
    const productId = String(wishlistBtn.dataset.productId);
    const btnText = wishlistBtn.querySelector('.button-text');

    /**
     * Gets the current wishlist from localStorage as an array of strings
     * @returns {string[]}
     */
    const getWishlist = () => {
        try {
            const stored = localStorage.getItem('wishlist');
            const data = stored ? JSON.parse(stored) : [];
            return Array.isArray(data) ? data.map(String) : [];
        } catch (e) {
            console.error('Error parsing wishlist from localStorage:', e);
            return [];
        }
    };

    /**
     * Updates the UI state of the wishlist button
     * @param {boolean} isInWishlist 
     */
    const updateUI = (isInWishlist) => {
        if (isInWishlist) {
            wishlistBtn.classList.add('active');
            if (btnText) btnText.textContent = 'Remove from Wishlist';
        } else {
            wishlistBtn.classList.remove('active');
            if (btnText) btnText.textContent = 'Add to Wishlist';
        }
    };

    /**
     * Re-check and update UI on load
     */
    let wishlist = getWishlist();
    updateUI(wishlist.includes(productId));

    // Toggle logic on click
    wishlistBtn.addEventListener('click', (e) => {
        e.preventDefault(); // Prevent accidental form submission if within a form

        wishlist = getWishlist();
        const isInWishlist = wishlist.includes(productId);

        if (isInWishlist) {
            // Remove from wishlist
            wishlist = wishlist.filter(id => id !== productId);
        } else {
            // Add to wishlist
            wishlist.push(productId);
        }

        // Save back to localStorage
        localStorage.setItem('wishlist', JSON.stringify(wishlist));

        // Update UI
        updateUI(!isInWishlist);

        // Notify other components (like global header)
        window.dispatchEvent(new CustomEvent('wishlistUpdated', {
            detail: { wishlist, productId, action: isInWishlist ? 'remove' : 'add' }
        }));
    });
});
