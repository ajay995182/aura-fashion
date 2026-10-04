# Aura Fashion v1.9.0 – Full Feature Audit

Status legend: **DONE** = implemented in theme | **PLUGIN** = intentional plugin/service | **WC** = native WooCommerce enhanced by theme

---

## 1. Product & Shopping Experience

| Feature | Status | Location |
|---------|--------|----------|
| Quick View modal | **DONE** | `ajax-handlers.php` + `main.js` + modal in `woocommerce.php` footer |
| AJAX Add to Cart | **DONE** | `ajax-handlers.php` `aura_add_to_cart` + `main.js` + product cards |
| Variation swatches | **DONE** | `inc/woocommerce.php` (hides selects, builds color/size buttons) |
| Size Guide popup | **DONE** | Button on product + modal + Customizer HTML |
| Recently Viewed | **DONE** | Cookie tracker + section after single/shop |
| Related / Upsell | **DONE** | Filters to 4 products; upsell + related on single |
| Cross-sell | **DONE** | Cart cross-sells (4 cols) + styling |
| AJAX product filters | **DONE** | `archive-product.php` sidebar + `shop-filters.js` + AJAX |
| Wishlist share | **DONE** | Copy / Email / X / Facebook / WhatsApp on wishlist page |
| Stock countdown | **DONE** | "Only X left" when stock ≤ 10 |
| Product video | **DONE** | Meta `_aura_product_video` YouTube/Vimeo/MP4 |

## 2. Marketing & Conversion

| Feature | Status | Location |
|---------|--------|----------|
| Mailchimp / Klaviyo / Brevo | **DONE** | `inc/marketing.php` + Customizer ESP section |
| Exit-intent popup | **DONE** | Markup + JS mouseleave/scroll + discount code |
| Free shipping progress bar | **DONE** | Cart + checkout + AJAX fragments; Options panel threshold |
| Sale product countdown | **DONE** | Meta `_aura_sale_end` + Woo sale end fallback |
| Abandoned cart recovery | **PLUGIN** + **DONE** hooks | Email captured at checkout; admin guide to recovery plugins |
| Loyalty / referral | **DONE** basic | Points on completed orders; Options toggle; full referral = plugin |
| Instagram feed | **DONE** | Homepage section + Customizer embed/token |
| Reviews with photos | **DONE** | Upload field + display under review |

## 3. User Account & Personalization

| Feature | Status | Location |
|---------|--------|----------|
| Saved addresses / checkout | **WC** + **DONE** UI | Styled address cards + "saved address loaded" notice |
| Order tracking page | **DONE** | Shortcode + template + My Account endpoint + timeline |
| Wishlist sync (logged-in) | **DONE** | user meta `aura_wishlist` merge with localStorage |
| Buy Again / reorder | **DONE** | Dashboard section + order "Buy Again" button |
| Personal size profile | **DONE** | Account form + product page size hint |

## 4. Design & Page Improvements

| Feature | Status | Location |
|---------|--------|----------|
| Enhanced single product | **DONE** | Gallery, sticky summary, sticky mobile ATC, custom tabs |
| Lookbook / Shop the Look | **DONE** | CPT `aura_lookbook` + archive/single + product IDs |
| Style Journal (blog) | **DONE** | `home.php` + `single.php` |
| Mega Menu | **DONE** | Category panel + JS hover |
| Dark / Light mode | **DONE** | Header toggle + localStorage |
| Animated product cards | **DONE** | CSS stagger + hover (v1.9 polish) |
| Custom 404 | **DONE** | `404.php` |
| Skeleton screens | **DONE** | AJAX filter loading skeletons |
| UI polish (pills, swatches, bars) | **DONE** | `assets/css/ui-refresh.css` v1.9 |

## 5. Performance, SEO & Technical

| Feature | Status | Location |
|---------|--------|----------|
| Lazy load + WebP + LCP priority | **DONE** | `inc/performance.php` |
| Critical CSS / defer JS | **DONE** | Inline critical CSS, defer theme scripts, preconnect |
| Schema (Product, FAQ, Org, Breadcrumb) | **DONE** | JSON-LD in `wp_head` |
| One-click demo import | **DONE** | Appearance → Aura Demo Import (rich data in v1.9) |
| Child theme | **DONE** | Optional `aura-fashion-child/` |
| WPML / Polylang ready | **DONE** | Text domain + string registration |
| Currency switcher | **PLUGIN** | Compatible; admin notice |

## 6. Admin & Store Management

| Feature | Status | Location |
|---------|--------|----------|
| Theme options panel | **DONE** | Admin menu **Aura Options** |
| Product badges New/Bestseller/Limited/Eco | **DONE** | Metabox + auto rules + CSS |
| Bulk import guidance | **DONE** | Aura Options → Bulk Import Guide |
| Analytics (GA4 + Pixel + purchase) | **DONE** | Options IDs + thank-you events; plugin guide |

## 7. Demo data (v1.9)

| Item | Details |
|------|---------|
| Categories | Women, Men, Accessories, Sale, New In |
| Simple products | 12 with prices, sales, stock, badges, SKUs |
| Variable product | "Aura Essential Tee – Size & Color" (S/M/L/XL × Black/White/Navy/Ivory) for swatches |
| Coupons | AURA10 (10%), WELCOME15 (15%), FREESHIP |
| Lookbooks | 3 Shop-the-Look entries |
| Journal posts | 3 Style Journal articles |
| Pages | Wishlist, About, Contact, FAQ, Order Tracking |
| Menu | Primary menu with all links |
| Options | Free shipping $100, exit code AURA10, store notice |

---

## After install

1. Activate Aura Fashion + WooCommerce  
2. Settings → Permalinks → Save  
3. Appearance → **Aura Demo Import** → Import Full Demo Content  
4. Aura Options → configure notice, shipping, GA4/Pixel  
5. Customize → colors, hero, ESP API keys  
6. Create/confirm coupon matching exit-popup code (`AURA10`)  
7. Follow **TESTING.md** for full feature checklist  

---

## Testing

See **TESTING.md** in the theme root for a complete step-by-step test plan for every feature.
