/**
 * Product Listing Helper
 * 
 * Responsibility: Updates dynamic UI elements on the product listing page, 
 * specifically the total product count.
 * 
 * Why it exists: To ensure the "Showing X products" text is always accurate 
 * even if filters are applied.
 * 
 * When it runs: Runs when the user loads the products page.
 */
document.addEventListener('DOMContentLoaded', () => {
    const productGrid = document.querySelector('.product-grid');
    const countDisplay = document.getElementById('product-count-display');

    if (!productGrid || !countDisplay) return;

    /**
     * Updates the product count text based on the number of cards in the DOM.
     */
    const updateProductCount = () => {
        const productCount = productGrid.querySelectorAll('.product-card').length;
        countDisplay.textContent = `Showing ${productCount} products`;
    };

    // Initial count on page load
    updateProductCount();

    // Observe changes to the product grid to update the count automatically
    const observer = new MutationObserver(() => {
        updateProductCount();
    });

    observer.observe(productGrid, { childList: true });
});
