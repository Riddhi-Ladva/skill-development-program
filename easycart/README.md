# EasyCart - Maintenance & Developer Guide

## 1. Project Overview
EasyCart is a functional e-commerce application built to demonstrate core full-stack concepts using vanilla PHP and JavaScript. It simulates a complete shopping flow from product browsing to cart management and checkout.

 **High-Level Flow:**
1.  **Browse**: Users view products loaded from static PHP arrays.
2.  **Cart Actions**: "Add to Cart" triggers AJAX requests to update the session state without page reloads.
3.  **Calculations**: All pricing logic (subtotal, shipping, tax) is centralized in PHP services and recalculated on every cart modification.
4.  **Checkout**: Users review the final summary (totals aggregated by PHP) before "placing" the order (simulated).

## 2. Tech Stack
*   **Backend**: PHP (7.4+) - Handles business logic, session state, and HTML rendering.
*   **Frontend**: JavaScript (Vanilla) - Manages UI interactivity and AJAX communication.
*   **Data Storage**: PHP Arrays - `data/` directory acts as a flat-file database.
*   **Styling**: CSS (BEM naming convention) - Custom styling without frameworks.
*   **State Management**: PHP `$_SESSION` - Stores cart contents and user choices.

## 3. Folder Structure & Responsibilities

| Directory | Role & Responsibility |
| :--- | :--- |
| **`pages/`** | **User-Facing Views**. Contains the actual pages visited by the browser (URL endpoints). Responsible for page layout and including necessary partials. |
| **`includes/`** | **Business Logic & Services**. The "Brain" of the application. Contains reusable functions for calculations (tax, shipping), shared UI components (header/footer), and bootstrap config. |
| **`ajax/`** | **API Endpoints**. Headless PHP scripts that accept JSON input and return JSON output. Used by JavaScript to modify state (add/remove items) dynamically. |
| **`data/`** | **Data Layer**. Mock database tables defined as PHP arrays. Read-only source of truth for Products, Brands, and Categories. |
| **`assets/`** | **Static Resources**. Frontend-only files. `js/` contains client-side logic; `css/` contains styles; `img/` contains media. |

## 4. Key Architectural Decisions

### Why Session-Based Cart?
We use `$_SESSION` to persist the cart because it mimics a server-side database transaction without the complexity of SQL. It ensures that cart data survives page reloads and is secure from simple client-side tampering.

### Why Server-Side Calculation (Source of Truth)?
All math (prices, tools, tax) happens in PHP.
*   **Reason**: Security and Consistency. If JS calculated the total, a user could manipulate the browser code to set the price to $0. By forcing PHP to recalculate everything on every request, we ensure the final price is always correct based on the backend rules.

### Why AJAX for Cart Operations?
To provide a modern, "app-like" feel. Reloading the entire page just to update a quantity number is poor UX. AJAX allows us to update specific DOM elements (badge count, total price) instantly.

### Why Recalculate Shipping on Every Step?
Shipping costs are dynamic (tiered based on subtotal). Since adding an item can push the subtotal over a threshold (e.g., Free Shipping > $50), we must re-evaluate the shipping rules after *every* cart modification.

## 5. Where to Find What (Code Logic Map)

| Feature | PHP Logic Location | JS Handler Location |
| :--- | :--- | :--- |
| **Product Data** | `data/products.php` | N/A |
| **Cart & Totals Math** | `includes/cart/services.php` | N/A (UI only reflects server data) |
| **Add to Cart** | `ajax/cart/add.php` | `assets/js/cart/add-to-cart.js` |
| **Remove Item** | `ajax/cart/remove.php` | `assets/js/cart/summary.js` (via confirm.js) |
| **Update Quantity** | `ajax/cart/update-qty.php` | `assets/js/cart/quantity.js` |
| **Shipping Rules** | `includes/shipping/services.php` | `assets/js/cart/shipping.js` (UI updates) |
| **Tax Rate** | `includes/tax/services.php` | N/A |
| **Header Badge** | `includes/header.php` | `assets/js/cart/summary.js` |
| **Global Config** | `includes/bootstrap/config.php` | N/A |

## 6. Common Maintenance Tasks

### How to change Shipping Rules?
1.  Open `includes/shipping/services.php`.
2.  Modify the `calculateShippingCost()` switch statement.
3.  AJAX endpoints will automatically use the new logic next time they run.

### How to change the Tax Rate?
1.  Open `includes/tax/services.php`.
2.  Update the percentage multiplier in `calculateTax()`.

### How to add a new Product?
1.  Open `data/products.php`.
2.  Add a new array entry with a unique ID.
3.  The product will immediately appear on the Products page.

### How to fix Cart Badge behavior?
*   **Initial Load**: Logic is in `includes/header.php` (PHP checks session).
*   **After Action**: Logic is in `assets/js/cart/summary.js` (JS updates DOM based on AJAX response).
*   *Note: Both must align (e.g., referencing correct IDs like `#cart-page-count`).*

## 7. Known Constraints
*   **Session-Only Persistence**: Logic relies on PHP Sessions. If the browser closes or session expires, data is lost (no database persistence).
*   **Mock Payment**: Checkout is a simulation; no actual payment processing occurs.
*   **Single Currency**: Hardcoded to USD formatting.
