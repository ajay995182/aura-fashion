/**
 * Aura Fashion – Main JS
 * Quick View, AJAX Cart, Wishlist, Newsletter, Mobile Menu, Search
 */
(function ($) {
  'use strict';

  const Aura = {
    wishlistKey: 'aura_wishlist',

    init() {
      this.clearWishlistAfterLogout();
      this.mobileMenu();
      this.searchOverlay();
      this.wishlist();
      this.quickView();
      this.ajaxAddToCart();
      this.newsletter();
      this.sizeGuide();
      this.quantityButtons();
      this.escapeKey();
      this.exitIntent();
      this.saleCountdowns();
      this.reviewFormEnctype();
      this.themeMode();
      this.megaMenu();
      this.skeletons();
    },

    /**
     * After logout PHP sets cookie aura_clear_wishlist=1.
     * Clear localStorage wishlist so the previous user's items do not remain for the guest.
     */
    clearWishlistAfterLogout() {
      const match = document.cookie.match(/(?:^|;\s*)aura_clear_wishlist=1(?:;|$)/);
      if (!match) return;
      try {
        localStorage.removeItem(this.wishlistKey);
      } catch (e) {}
      this.updateWishlistCount();
      // Expire the cookie so it only runs once
      const expire = 'Thu, 01 Jan 1970 00:00:00 GMT';
      document.cookie = 'aura_clear_wishlist=; expires=' + expire + '; path=/';
      try {
        if (typeof COOKIEPATH !== 'undefined') {
          document.cookie = 'aura_clear_wishlist=; expires=' + expire + '; path=' + (COOKIEPATH || '/');
        }
      } catch (e) {}
    },

    /* ---------- Mobile Menu ---------- */
    mobileMenu() {
      const $toggle = $('.mobile-menu-toggle');
      const $nav = $('.mobile-nav');
      const $overlay = $('.mobile-nav-overlay');
      const $close = $('.mobile-nav-close');

      $toggle.on('click', () => {
        $nav.addClass('active');
        $overlay.addClass('active');
        $('body').css('overflow', 'hidden');
      });

      const close = () => {
        $nav.removeClass('active');
        $overlay.removeClass('active');
        $('body').css('overflow', '');
      };

      $close.on('click', close);
      $overlay.on('click', close);
    },

    /* ---------- Search Overlay ---------- */
    searchOverlay() {
      const $overlay = $('.search-overlay');
      $('.search-toggle').on('click', (e) => {
        e.preventDefault();
        $overlay.addClass('active').attr('aria-hidden', 'false');
        $overlay.find('input').focus();
      });
      $('.search-close, .search-overlay').on('click', function (e) {
        if (e.target === this || $(e.target).hasClass('search-close')) {
          $overlay.removeClass('active').attr('aria-hidden', 'true');
        }
      });
    },

    /* ---------- Wishlist (localStorage) ---------- */
    getWishlist() {
      try {
        return JSON.parse(localStorage.getItem(this.wishlistKey) || '[]');
      } catch {
        return [];
      }
    },

    saveWishlist(ids) {
      localStorage.setItem(this.wishlistKey, JSON.stringify(ids));
      this.updateWishlistCount();
      // Sync to server if logged in
      if (typeof auraUser !== 'undefined' && auraUser.isLoggedIn) {
        $.post(auraData.ajaxUrl, {
          action: 'aura_sync_wishlist',
          nonce: auraData.nonce,
          ids: ids,
        });
      }
    },

    /** Load wishlist: merge server + local for logged-in users */
    loadWishlistFromServer(callback) {
      const localIds = this.getWishlist();
      $.post(auraData.ajaxUrl, {
        action: 'aura_get_wishlist',
        nonce: auraData.nonce,
        ids: localIds,
      }).done((res) => {
        if (res.success && res.data.ids) {
          localStorage.setItem(this.wishlistKey, JSON.stringify(res.data.ids));
          this.updateWishlistCount();
          if (callback) callback(res.data.ids);
        } else if (callback) {
          callback(localIds);
        }
      }).fail(() => {
        if (callback) callback(localIds);
      });
    },

    updateWishlistCount() {
      const count = this.getWishlist().length;
      $('.wishlist-count').text(count).toggle(count > 0);
    },

    wishlist() {
      // Sync from server if logged in, then update UI
      this.loadWishlistFromServer(() => {
        this.updateWishlistCount();
        const list = this.getWishlist();
        $('.wishlist-toggle').each(function () {
          const id = parseInt($(this).data('product-id'), 10);
          if (list.includes(id)) {
            $(this).addClass('active').attr('aria-pressed', 'true');
          }
        });
      });

      // Toggle heart buttons
      $(document).on('click', '.wishlist-toggle', (e) => {
        e.preventDefault();
        const $btn = $(e.currentTarget);
        const id = parseInt($btn.data('product-id'), 10);
        if (!id) return;

        let list = this.getWishlist();
        if (list.includes(id)) {
          list = list.filter((i) => i !== id);
          $btn.removeClass('active').attr('aria-pressed', 'false');
          this.toast(auraData.i18n.removedWishlist);
        } else {
          list.push(id);
          $btn.addClass('active').attr('aria-pressed', 'true');
          this.toast(auraData.i18n.addedWishlist);
        }
        this.saveWishlist(list);
      });

      // Mark already wishlist items on page load
      const list = this.getWishlist();
      $('.wishlist-toggle').each(function () {
        const id = parseInt($(this).data('product-id'), 10);
        if (list.includes(id)) {
          $(this).addClass('active').attr('aria-pressed', 'true');
        }
      });

      // Wishlist page loader
      if ($('#aura-wishlist-products').length) {
        this.loadWishlistPage();
      }
    },

    loadWishlistPage() {
      const $grid = $('#aura-wishlist-products');
      const $empty = $('#aura-wishlist-empty');
      const $share = $('#wishlist-share-bar');

      // Always merge server wishlist first when logged in
      this.loadWishlistFromServer((ids) => {
      if (!ids.length) {
        $grid.hide();
        $empty.removeClass('hidden');
        $share.hide();
        return;
      }

      $.post(auraData.ajaxUrl, {
        action: 'aura_load_wishlist',
        nonce: auraData.nonce,
        ids: ids,
      })
        .done((res) => {
          if (res.success && res.data.html) {
            $grid.html(res.data.html).show();
            $empty.addClass('hidden');
            $share.show();
            // re-mark hearts
            const list = this.getWishlist();
            $grid.find('.wishlist-toggle').each(function () {
              if (list.includes(parseInt($(this).data('product-id'), 10))) {
                $(this).addClass('active');
              }
            });
          } else {
            $grid.hide();
            $empty.removeClass('hidden');
            $share.hide();
          }
        })
        .fail(() => {
          $grid.hide();
          $empty.removeClass('hidden');
        });
      }); // end loadWishlistFromServer

      // Share buttons
      $(document).on('click', '[data-share]', (e) => {
        e.preventDefault();
        const type = $(e.currentTarget).data('share');
        const url = window.location.href;
        const text = encodeURIComponent(document.title + ' – My Wishlist');

        if (type === 'copy') {
          navigator.clipboard.writeText(url).then(() => this.toast(auraData.i18n.shareCopied));
        } else if (type === 'email') {
          window.location.href = `mailto:?subject=${text}&body=${encodeURIComponent(url)}`;
        } else if (type === 'twitter') {
          window.open(`https://twitter.com/intent/tweet?text=${text}&url=${encodeURIComponent(url)}`, '_blank');
        } else if (type === 'facebook') {
          window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
        } else if (type === 'whatsapp') {
          window.open(`https://wa.me/?text=${text}%20${encodeURIComponent(url)}`, '_blank');
        }
      });
    },

    /* ---------- Quick View ---------- */
    quickView() {
      const $modal = $('#aura-quick-view');
      const $body = $modal.find('.quick-view-body');

      $(document).on('click', '.quick-view-btn', (e) => {
        e.preventDefault();
        const id = $(e.currentTarget).data('product-id');
        if (!id) return;

        $modal.removeAttr('hidden').addClass('active');
        $('body').css('overflow', 'hidden');
        $body.html('<div class="products-loading active"><div class="spinner"></div></div>');

        $.post(auraData.ajaxUrl, {
          action: 'aura_quick_view',
          nonce: auraData.nonce,
          product_id: id,
        })
          .done((res) => {
            if (res.success) {
              $body.html(res.data.html);
            } else {
              $body.html('<p style="padding:40px;text-align:center;">' + auraData.i18n.error + '</p>');
            }
          })
          .fail(() => {
            $body.html('<p style="padding:40px;text-align:center;">' + auraData.i18n.error + '</p>');
          });
      });

      const closeQV = () => {
        $modal.removeClass('active').attr('hidden', true);
        $('body').css('overflow', '');
      };

      $modal.on('click', '.quick-view-close, .quick-view-backdrop', closeQV);
    },

    /* ---------- AJAX Add to Cart ---------- */
    ajaxAddToCart() {
      $(document).on('click', '.ajax_add_to_cart, .single_add_to_cart_button', function (e) {
        const $btn = $(this);
        // Let WooCommerce handle variable products on single page with its own form
        if ($btn.closest('form.variations_form').length && !$btn.hasClass('ajax_add_to_cart')) {
          return; // use default WC behaviour + our listener below
        }

        // Simple product / loop buttons
        if ($btn.hasClass('ajax_add_to_cart') || $btn.hasClass('product_type_simple')) {
          e.preventDefault();
          const productId = $btn.data('product_id') || $btn.val();
          const qty = $btn.closest('form').find('input.qty').val() || 1;

          $btn.addClass('loading').prop('disabled', true);

          $.post(auraData.ajaxUrl, {
            action: 'aura_add_to_cart',
            nonce: auraData.nonce,
            product_id: productId,
            quantity: qty,
          })
            .done((res) => {
              if (res.success) {
                $('.cart-count').text(res.data.cart_count).show();
                Aura.showCartNotice(res.data.message);
                $(document.body).trigger('added_to_cart', [res.data, '', $btn]);
              } else if (res.data && res.data.require_login) {
                Aura.showLoginRequiredNotice(res.data.message, res.data.login_url);
              } else {
                Aura.toast(res.data?.message || auraData.i18n.error);
              }
            })
            .fail(() => Aura.toast(auraData.i18n.error))
            .always(() => $btn.removeClass('loading').prop('disabled', false));
        }
      });

      // Also listen to WC native added_to_cart for fragments
      $(document.body).on('added_to_cart', function (e, fragments, cart_hash, $button) {
        if (fragments && fragments['div.widget_shopping_cart_content']) {
          // update count if available
        }
        Aura.showCartNotice(auraData.i18n.addedToCart);
      });
    },

    showCartNotice(msg) {
      const $n = $('#aura-cart-notice');
      if (!$n.length) {
        this.toast(msg);
        return;
      }
      $n.find('.notice-text').text(msg);
      $n.find('.notice-login-link').remove();
      $n.find('a.btn').show();
      $n.addClass('show');
      setTimeout(() => $n.removeClass('show'), 4000);
    },

    /**
     * Guest tried to add to cart: show message + link to login/register.
     */
    showLoginRequiredNotice(msg, loginUrl) {
      const $n = $('#aura-cart-notice');
      const url = loginUrl || (auraData.accountUrl || '/my-account/');
      const text = msg || (auraData.i18n && auraData.i18n.loginToCart) || 'Please log in to add items to your cart.';
      const cta = (auraData.i18n && auraData.i18n.loginRegister) || 'Log in / Create account';

      if ($n.length) {
        $n.find('.notice-text').text(text);
        $n.find('.notice-login-link').remove();
        // Hide "View Cart" for this notice; show login CTA instead
        $n.find('a.btn').hide();
        $n.append(
          $('<a class="notice-login-link btn btn-sm btn-primary" href="' + url + '">' + cta + '</a>')
        );
        $n.addClass('show');
        setTimeout(() => {
          $n.removeClass('show');
          $n.find('.notice-login-link').remove();
          $n.find('a.btn').show();
        }, 8000);
      } else {
        this.toast(text);
        if (window.confirm(text + '\n\n' + cta + '?')) {
          window.location.href = url;
        }
      }
    },

    toast(msg) {
      // simple reuse of cart notice for toasts
      this.showCartNotice(msg);
    },

    /* ---------- Newsletter ---------- */
    newsletter() {
      $('.newsletter-form').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const email = $form.find('input[type="email"]').val();
        const $msg = $form.siblings('.newsletter-message');

        $.post(auraData.ajaxUrl, {
          action: 'aura_newsletter',
          nonce: auraData.nonce,
          email: email,
        })
          .done((res) => {
            $msg.removeClass('success error').addClass(res.success ? 'success' : 'error').text(res.data.message);
            if (res.success) $form[0].reset();
          })
          .fail(() => {
            $msg.removeClass('success').addClass('error').text(auraData.i18n.error);
          });
      });
    },

    /* ---------- Size Guide ---------- */
    sizeGuide() {
      const $modal = $('#aura-size-guide');
      $(document).on('click', '#open-size-guide, .size-guide-btn', (e) => {
        e.preventDefault();
        $modal.removeAttr('hidden').addClass('active');
        $('body').css('overflow', 'hidden');
      });
      $modal.on('click', '.quick-view-close, .quick-view-backdrop', () => {
        $modal.removeClass('active').attr('hidden', true);
        $('body').css('overflow', '');
      });
    },

    /* ---------- Quantity +/- ---------- */
    quantityButtons() {
      $(document).on('click', '.qty-plus', function () {
        const $input = $(this).siblings('input.qty');
        const max = parseInt($input.attr('max'), 10) || 999;
        let val = parseInt($input.val(), 10) || 1;
        if (val < max) $input.val(val + 1).trigger('change');
      });
      $(document).on('click', '.qty-minus', function () {
        const $input = $(this).siblings('input.qty');
        let val = parseInt($input.val(), 10) || 1;
        if (val > 1) $input.val(val - 1).trigger('change');
      });
    },

    /* ---------- Escape closes modals ---------- */
    escapeKey() {
      $(document).on('keydown', (e) => {
        if (e.key === 'Escape') {
          $('.quick-view-modal.active, .size-guide-modal.active, .search-overlay.active, .mobile-nav.active, .aura-exit-popup.active')
            .removeClass('active')
            .attr('hidden', true);
          $('.mobile-nav-overlay').removeClass('active');
          $('body').css('overflow', '');
        }
      });
    },

    /* ---------- Exit-Intent Popup ---------- */
    exitIntent() {
      const $popup = $('#aura-exit-popup');
      if (!$popup.length) return;

      let shown = false;
      const show = () => {
        if (shown || document.cookie.indexOf('aura_exit_shown') !== -1) return;
        shown = true;
        $popup.removeAttr('hidden').addClass('active');
        $('body').css('overflow', 'hidden');
        // Cookie for 7 days
        const d = new Date();
        d.setTime(d.getTime() + 7 * 24 * 60 * 60 * 1000);
        document.cookie = 'aura_exit_shown=1;expires=' + d.toUTCString() + ';path=/';
      };

      // Desktop: mouse leaves viewport top
      document.addEventListener('mouseout', (e) => {
        if (e.clientY < 10 && e.relatedTarget === null) show();
      });

      // Mobile: after 40s on page or scroll to 70%
      let mobileTimer = setTimeout(show, 40000);
      $(window).on('scroll', function onScroll() {
        const scrolled = ($(window).scrollTop() + $(window).height()) / $(document).height();
        if (scrolled > 0.7) {
          show();
          $(window).off('scroll', onScroll);
          clearTimeout(mobileTimer);
        }
      });

      const close = () => {
        $popup.removeClass('active').attr('hidden', true);
        $('body').css('overflow', '');
      };

      $popup.on('click', '.aura-exit-close, .aura-exit-backdrop, .aura-exit-dismiss', close);

      // Copy discount code
      $popup.on('click', '.aura-copy-code', function () {
        const code = $(this).data('code');
        navigator.clipboard.writeText(code).then(() => {
          Aura.toast(auraData.i18n.shareCopied || 'Copied!');
        });
      });
    },

    /* ---------- Per-product Sale Countdowns ---------- */
    saleCountdowns() {
      /* Product-level countdowns */
      $('.aura-product-countdown').each(function () {
        const $el = $(this);
        const end = new Date($el.data('end')).getTime();
        if (!end || isNaN(end)) return;

        const tick = () => {
          let diff = Math.max(0, end - Date.now());
          const d = Math.floor(diff / 86400000); diff %= 86400000;
          const h = Math.floor(diff / 3600000); diff %= 3600000;
          const m = Math.floor(diff / 60000); diff %= 60000;
          const s = Math.floor(diff / 1000);
          $el.find('[data-d]').text(String(d).padStart(2, '0'));
          $el.find('[data-h]').text(String(h).padStart(2, '0'));
          $el.find('[data-m]').text(String(m).padStart(2, '0'));
          $el.find('[data-s]').text(String(s).padStart(2, '0'));
          if (d + h + m + s === 0) $el.fadeOut();
        };
        tick();
        setInterval(tick, 1000);
      });

      /* Homepage Season Sale (#sale-countdown) */
      $('#sale-countdown, .sale-countdown').each(function () {
        const $el = $(this);
        let endRaw = $el.attr('data-end') || $el.data('end');
        if (!endRaw) return;
        const end = new Date(endRaw).getTime();
        if (!end || isNaN(end)) return;

        const tick = () => {
          let diff = Math.max(0, end - Date.now());
          const d = Math.floor(diff / 86400000); diff %= 86400000;
          const h = Math.floor(diff / 3600000); diff %= 3600000;
          const m = Math.floor(diff / 60000); diff %= 60000;
          const s = Math.floor(diff / 1000);
          $el.find('[data-days], [data-d]').text(String(d).padStart(2, '0'));
          $el.find('[data-hours], [data-h]').text(String(h).padStart(2, '0'));
          $el.find('[data-mins], [data-m]').text(String(m).padStart(2, '0'));
          $el.find('[data-secs], [data-s]').text(String(s).padStart(2, '0'));
          if (d + h + m + s === 0) {
            $el.find('.number').text('00');
          }
        };
        tick();
        setInterval(tick, 1000);
      });
    },

    /* ---------- Review form enctype for photo uploads ---------- */
    reviewFormEnctype() {
      const $form = $('#review_form, .aura-review-form, #commentform');
      if ($form.length) {
        $form.attr('enctype', 'multipart/form-data');
      }
    },


    /* ---------- Theme mode (dark/light) ---------- */
    themeMode() {
      const key = 'aura_theme_mode';
      const $btn = $('#aura-mode-toggle');
      const apply = (mode) => {
        if (mode === 'light') {
          $('body').addClass('aura-light-mode');
        } else {
          $('body').removeClass('aura-light-mode');
        }
        try { localStorage.setItem(key, mode); } catch (e) {}
      };
      // Init from storage
      let mode = 'dark';
      try { mode = localStorage.getItem(key) || 'dark'; } catch (e) {}
      apply(mode);

      $btn.on('click', () => {
        mode = $('body').hasClass('aura-light-mode') ? 'dark' : 'light';
        apply(mode);
      });
    },

    /* ---------- Mega Menu ---------- */
    megaMenu() {
      const $panel = $('#aura-mega-panel');
      if (!$panel.length) return;
      let timer;
      // Show on Shop / category hover in primary menu
      $('.primary-menu a').on('mouseenter', function () {
        const text = $(this).text().toLowerCase();
        if (text.includes('shop') || $(this).data('mega') || $(this).hasClass('has-mega')) {
          clearTimeout(timer);
          $panel.removeAttr('hidden').addClass('active');
        }
      });
      $('.primary-nav, #aura-mega-panel').on('mouseleave', function () {
        timer = setTimeout(() => {
          $panel.removeClass('active').attr('hidden', true);
        }, 200);
      });
      $('.primary-nav, #aura-mega-panel').on('mouseenter', function () {
        clearTimeout(timer);
      });
    },

    /* ---------- Skeleton screens for AJAX filters ---------- */
    skeletons() {
      // When shop filters start loading, inject skeletons
      $(document).on('aura:filters:start', function () {
        const $results = $('.shop-results');
        if (!$results.find('.aura-skeleton-grid').length) {
          $results.prepend(
            '<div class="aura-skeleton-grid products-grid">' +
            Array(8).fill(0).map(() =>
              '<div class="skeleton-card"><div class="skeleton-img skeleton-pulse"></div><div class="skeleton-line skeleton-pulse"></div><div class="skeleton-line short skeleton-pulse"></div></div>'
            ).join('') +
            '</div>'
          );
        }
        $results.addClass('is-loading');
      });
      $(document).on('aura:filters:end', function () {
        $('.shop-results').removeClass('is-loading');
        $('.aura-skeleton-grid').remove();
      });
    },

  };

  $(document).ready(() => Aura.init());
})(jQuery);
