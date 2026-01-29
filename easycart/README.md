# EasyCart - My Study Guide 📚

This project is a simple but powerful e-commerce shopping cart built with **PHP**, **Vanilla JS**, and **AJAX**. I'm using it to learn how data flows from the browser to the server session and back.

## 🚀 How the App Works (The Big Picture)

1.  **Browser (UI):** Everything starts when a user clicks a button (like "Add to Cart" or "Change Quantity").
2.  **JavaScript (The Messenger):** A `fetch()` call in my JS files sends the request to a hidden PHP file (in the `ajax/` folder).
3.  **PHP Services (The Math Engine):** The specialist functions in `includes/` (like `calculateSubtotal` or `calculateTax`) do all the heavy lifting.
4.  **Session (The Memory):** PHP saves the cart data in `$_SESSION['cart']` so the items don't disappear when I refresh the page.
5.  **JSON (The Reply):** PHP sends back the new totals as a JSON object, and JS "plugs" those numbers into the HTML.

---

## 📂 Where to Look (My File Map)

| Folder / File | What it does (Study Note) |
| :--- | :--- |
| `includes/bootstrap/` | Sets up the project. `config.php` holds my base URLs and paths. |
| `includes/cart/services.php` | The heart of all cart math (Subtotal + Aggregating Totals). |
| `includes/shipping/services.php` | Determines shipping costs based on the business rules I set. |
| `includes/tax/services.php` | Calculates that fixed 18% tax on everything. |
| `ajax/` | Hidden PHP files that respond to my JS `fetch()` calls. |
| `assets/js/` | All the browser logic. `cart.js` handles the "Optimistic UI" updates. |
| `pages/` | The actual HTML templates (Header, Footer, Product Listing). |

---

## 🛠 Troubleshooting Guide (When things break)

### 1. The Cart isn't updating?
*   **Check the Session:** Make sure `session_start()` is called at the very top.
*   **Check AJAX URL:** Open the browser console (F12). If you see a "404 Not Found" error, the path in `config.php` might be wrong.
*   **Check the JSON:** If PHP has a typo, it might output a warning that breaks the JSON response. Always check the **Network** tab in F12 to see the raw response.

### 2. Shipping costs look weird?
*   **Recalculation:** Remember that shipping is recalculated *every time* you change a quantity. It depends on the current subtotal.
*   **Look in `shipping/services.php`:** This is where the rules (like the $80 cap for Express) live.

### 3. Images aren't showing up?
*   **Base URL:** Make sure `ASSET_URL` in `config.php` is pointing to the right place.
*   **Path Helper:** Always use the `asset()` helper function in PHP to generate the image source.

---

## 📝 Key Lessons Learned
*   **AJAX = No Refresh:** It feels much more premium when the page doesn't blink on every change.
*   **Modular PHP:** Keeping the math in `includes/` makes it easy to fix things in one place and have it update everywhere.
*   **Optimistic UI:** Updating the screen *before* the server replies makes the site feel lightning-fast.

---
*Created by Antigravity for Riddhi Ladva's learning journey.*
