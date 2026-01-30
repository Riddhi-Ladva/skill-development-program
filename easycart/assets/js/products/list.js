/**
 * Product Listing Helper
 * 
 * Responsibility: Updates dynamic UI elements on the product listing page, 
 * specifically the total product count and client-side filtering.
 */
document.addEventListener('DOMContentLoaded', () => {
    const productGrid = document.querySelector('.product-grid');
    const countDisplay = document.getElementById('product-count-display');

    if (!productGrid || !countDisplay) return;

    /**
     * Updates the product count text based on the visible cards.
     */
    const updateProductCount = () => {
        // Count only visible products
        const visibleCount = Array.from(productGrid.querySelectorAll('.product-card'))
            .filter(card => card.style.display !== 'none')
            .length;

        countDisplay.textContent = `Showing ${visibleCount} products`;
    };

    // Client-Side Shipping Filter Logic
    const expressInput = document.getElementById('filter-express');
    const freightInput = document.getElementById('filter-freight');

    // Only proceed if elements exist
    if (expressInput && freightInput) {
        console.log('Shipping filters initialized');

        const filterProducts = () => {
            const showExpress = expressInput.checked;
            const showFreight = freightInput.checked;

            console.log(`Filtering: Express=${showExpress}, Freight=${showFreight}`);

            // If neither or both are checked, show all (reset filtering)
            const showAll = (!showExpress && !showFreight) || (showExpress && showFreight);

            const cards = document.querySelectorAll('.product-card');

            cards.forEach(card => {
                const priceEl = card.querySelector('.product-price');
                if (!priceEl) return;

                // Extract price safely. Assuming text like "$1,234.56"
                const priceText = priceEl.textContent.trim();
                const cleanPrice = priceText.replace(/[^0-9.]/g, '');
                const price = parseFloat(cleanPrice);

                if (isNaN(price)) {
                    console.warn('Could not parse price for card', card);
                    return;
                }

                // Logic: <= 300 is Express, > 300 is Freight
                const isExpress = price <= 300;
                const isFreight = price > 300;

                let isVisible = true;

                if (!showAll) {
                    if (showExpress && !isExpress) isVisible = false;
                    if (showFreight && !isFreight) isVisible = false;
                }

                card.style.display = isVisible ? '' : 'none';
            });

            updateProductCount();
        };

        // Run immediately on page load (State is persisted by PHP)
        filterProducts();
    }

    // Initial count on page load
    updateProductCount();
});
