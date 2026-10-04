# Aura Fashion – Complete Feature Testing Guide

Use this checklist after activating the theme + WooCommerce and running **Appearance → Aura Demo Import**.

---

## 0. Setup (do once)

1. **Plugins**: Activate **WooCommerce** (and optionally a payment gateway for full checkout).
2. **Theme**: Appearance → Themes → Activate **Aura Fashion**.
3. **Demo**: Appearance → **Aura Demo Import** → **Import Full Demo Content**.
4. **Permalinks**: Settings → Permalinks → Save (important for shop, lookbook, tracking).
5. **Woo pages**: WooCommerce → Status → Tools → Create default WooCommerce pages if missing; confirm Shop, Cart, Checkout, My Account.
6. **Menus**: Appearance → Menus → Primary Menu assigned to “Primary”.
7. **Aura Options**: WP Admin → **Aura Options**
   - Store notice: leave default or edit
   - Free shipping threshold: `100`
   - Exit popup code: `AURA10`
   - Loyalty: enable if desired
   - GA4 / Meta Pixel: optional for analytics tests
8. **Customize**: Appearance → Customize → logo, hero title/description, colors, ESP (optional).

**Demo data you get:**
- 5 categories (Women, Men, Accessories, Sale, New In)
- 12 simple products + 1 variable product (Size + Color) with variations
- Product badges (New, Bestseller, Limited, Eco)
- Low stock on some items (for “Only X left”)
- Sale prices + sale end dates (countdown)
- Coupons: `AURA10` (10%), `WELCOME15` (15%), `FREESHIP`
- Pages: Wishlist, About, Contact, FAQ, Order Tracking
- 3 Lookbook entries
- 3 Style Journal posts
- Primary menu with all links

---

## 1. Homepage & Design

| # | Test | How | Pass? |
|---|------|-----|-------|
| 1.1 | Hero | Front page shows badge, title, description, Shop Now + View Sale | |
| 1.2 | Categories grid | 4 category cards link to Women / Men / Accessories / Sale | |
| 1.3 | Featured products | Grid of featured products with images/placeholders, prices, badges | |
| 1.4 | Dark / Light mode | Header toggle; preference saved in localStorage; refresh keeps mode | |
| 1.5 | Mega menu | Hover primary categories (if mega enabled in Options); panel appears | |
| 1.6 | Mobile menu | Resize or phone: hamburger opens/closes; links work | |
| 1.7 | Footer | Footer menu, copyright, any newsletter block | |
| 1.8 | Animated cards | Product cards have hover lift / image scale | |

---

## 2. Shop & Filters

| # | Test | How | Pass? |
|---|------|-----|-------|
| 2.1 | Shop page | /shop/ lists products in grid | |
| 2.2 | Category pages | /product-category/women/ etc. filter correctly | |
| 2.3 | AJAX filters | Sidebar: price, category, attribute filters; results update without full reload; skeleton loaders show | |
| 2.4 | Sorting | Default Woo sort (popularity, price, date) works | |
| 2.5 | Badges | New / Bestseller / Limited / Eco visible on cards | |
| 2.6 | Stock badge | Products with stock ≤ 10 show “Only X left” | |
| 2.7 | Sale badge | Sale products show sale price and % or “Sale” | |

---

## 3. Product Card & Quick Actions

| # | Test | How | Pass? |
|---|------|-----|-------|
| 3.1 | Quick View | Click Quick View icon/button on a card → modal with image, price, short desc, Add to cart | |
| 3.2 | AJAX Add to Cart | From card or Quick View: add without leaving page; mini-cart / notice updates | |
| 3.3 | Wishlist heart | Click heart → added (localStorage + logged-in user meta); click again → removed | |
| 3.4 | Wishlist page | Open Wishlist page (shortcode); products listed; share bar (Copy / Email / X / FB / WhatsApp) | |

---

## 4. Single Product

| # | Test | How | Pass? |
|---|------|-----|-------|
| 4.1 | Gallery | Main image + thumbs; zoom / lightbox if enabled | |
| 4.2 | Sticky summary | On desktop, product summary stays in view while scrolling gallery | |
| 4.3 | Sticky mobile ATC | On mobile, add-to-cart bar sticks at bottom | |
| 4.4 | Variation swatches | Open **Aura Essential Tee – Size & Color**: size/color buttons instead of dropdowns; selecting updates price/stock | |
| 4.5 | Size Guide | Button opens size guide modal (content from Customizer if set) | |
| 4.6 | Stock countdown | Product with stock ≤ 10 shows “Only X left!” | |
| 4.7 | Sale countdown | Sale product with end date shows countdown timer | |
| 4.8 | Product video | If you set meta `_aura_product_video` (YouTube/Vimeo URL), video appears | |
| 4.9 | Tabs | Description / Additional / Reviews tabs work | |
| 4.10 | Review photos | Submit a review with image upload; photo shows under review | |
| 4.11 | Related / Upsells | Related products section under product | |
| 4.12 | Recently Viewed | Visit 2–3 products then open another: “Recently Viewed” section appears | |

---

## 5. Cart & Checkout

| # | Test | How | Pass? |
|---|------|-----|-------|
| 5.1 | Cart page | Products, quantity update, remove, totals | |
| 5.2 | Free shipping bar | Cart under $100 shows progress; at/above $100 shows “You’ve unlocked free shipping” | |
| 5.3 | Cross-sells | Cross-sell products display on cart | |
| 5.4 | Coupon | Apply `AURA10` → 10% off; invalid code shows error | |
| 5.5 | Checkout | Fill billing; free shipping bar still visible; place order (use test gateway) | |
| 5.6 | Order received | Thank-you page; if GA4/Pixel IDs set, purchase event fires (check network or Tag Assistant) | |

---

## 6. Account & Personalization

| # | Test | How | Pass? |
|---|------|-----|-------|
| 6.1 | My Account | Login / Register; dashboard | |
| 6.2 | Saved addresses | Add billing/shipping; styled cards; “use this address” on checkout | |
| 6.3 | Buy Again | After completing an order, dashboard or order details show “Buy Again” | |
| 6.4 | Order Tracking | Order Tracking page: enter order ID + email → timeline status | |
| 6.5 | Size profile | Account form: save preferred size; product page can show size hint | |
| 6.6 | Wishlist sync | Add items while logged out; log in → wishlist merges | |
| 6.7 | Loyalty points | If enabled: complete order → points recorded (check user meta / Options) | |

---

## 7. Marketing & Conversion

| # | Test | How | Pass? |
|---|------|-----|-------|
| 7.1 | Exit-intent popup | Move mouse toward top of browser (desktop) or scroll up; popup with code `AURA10` appears (once per session) | |
| 7.2 | Newsletter | Footer or popup form: submit email (ESP keys in Customizer for real send; otherwise form still validates) | |
| 7.3 | Instagram | If Customizer has embed/token, homepage Instagram section shows | |
| 7.4 | Store notice | Top bar shows store notice from Aura Options | |

---

## 8. Lookbook & Journal

| # | Test | How | Pass? |
|---|------|-----|-------|
| 8.1 | Lookbook archive | /lookbook/ or menu link → list of looks | |
| 8.2 | Single look | Open a look → description + linked products (Shop the Look) | |
| 8.3 | Journal | Blog index shows Style Journal posts; single post readable | |

---

## 9. Pages & Templates

| # | Test | How | Pass? |
|---|------|-----|-------|
| 9.1 | About | Template content and layout | |
| 9.2 | Contact | Form submits (or at least validates) | |
| 9.3 | FAQ | Accordion / Q&A expand | |
| 9.4 | 404 | Visit a bad URL → custom 404 with search / shop link | |

---

## 10. Admin & Options

| # | Test | How | Pass? |
|---|------|-----|-------|
| 10.1 | Aura Options | All sections save: notice, shipping threshold, exit code, badges rules, analytics IDs | |
| 10.2 | Product badges metabox | Edit a product → Aura Badges; toggle New/Bestseller/Limited/Eco; front shows | |
| 10.3 | Auto badges | New by publish age; Bestseller by sales (if configured) | |
| 10.4 | Bulk Import Guide | Aura Options → Bulk Import Guide page loads | |
| 10.5 | Demo Import again | Re-run is safe (skips existing products, refreshes pages/menu) | |

---

## 11. Performance & SEO (spot checks)

| # | Test | How | Pass? |
|---|------|-----|-------|
| 11.1 | Lazy load | Images below fold have loading="lazy" or theme lazy | |
| 11.2 | Schema | View source on product: Product JSON-LD present | |
| 11.3 | Scripts deferred | Theme JS deferred where possible | |

---

## 12. Quick regression after changes

1. Clear caches (plugin + browser).
2. Homepage → Shop → Product → Add to cart → Cart → Checkout (or cart only).
3. Quick View + Wishlist + Exit popup.
4. Variable product swatches.
5. Mobile: sticky ATC + menu.

---

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 404 on shop / lookbook | Settings → Permalinks → Save |
| No products | Run Demo Import; check WooCommerce is active |
| Swatches not buttons | Open the variable product “Aura Essential Tee – Size & Color”; clear cache |
| Free shipping bar missing | Aura Options → free shipping threshold = 100; cart under/over that amount |
| Exit popup never shows | Use desktop, move cursor to top edge; or clear sessionStorage / new session |
| Wishlist empty after login | Theme merges localStorage with user meta on login; ensure cookies allowed |

---

## Fake data reference (after import)

**Coupons**
- `AURA10` – 10% off  
- `WELCOME15` – 15% off  
- `FREESHIP` – free shipping flag  

**Variable product for swatches**  
- **Aura Essential Tee – Size & Color** (S/M/L/XL × Black/White/Navy/Ivory)

**Low stock (countdown)**  
- Satin Slip Dress (6), Minimalist Watch (7), Aura Silk Blouse (8)

**Sale + countdown**  
- Several sale products with `_aura_sale_end` ~14 days ahead

Use this list to systematically click through every feature. Mark the Pass column as you go.
