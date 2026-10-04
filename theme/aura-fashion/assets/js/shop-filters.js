/**
 * Aura Fashion – AJAX Shop Filters
 */
(function ($) {
  'use strict';

  const Filters = {
    state: {
      page: 1,
      orderby: 'menu_order',
      min_price: 0,
      max_price: 9999,
      categories: [],
      attributes: {},
      on_sale: false,
    },

    init() {
      if (!$('.shop-layout').length) return;

      this.bindEvents();
      // Initial state from URL if any
      this.readUrl();
    },

    bindEvents() {
      // Category checkboxes
      $(document).on('change', '.filter-category', () => {
        this.state.categories = $('.filter-category:checked')
          .map(function () { return $(this).val(); })
          .get();
        this.state.page = 1;
        this.fetch();
      });

      // Attribute checkboxes (color, size, material)
      $(document).on('change', '.filter-attr', (e) => {
        const $cb = $(e.target);
        const tax = $cb.data('taxonomy');
        if (!this.state.attributes[tax]) this.state.attributes[tax] = [];
        if ($cb.is(':checked')) {
          this.state.attributes[tax].push($cb.val());
        } else {
          this.state.attributes[tax] = this.state.attributes[tax].filter((v) => v !== $cb.val());
        }
        this.state.page = 1;
        this.fetch();
      });

      // On sale
      $(document).on('change', '#filter-on-sale', (e) => {
        this.state.on_sale = $(e.target).is(':checked');
        this.state.page = 1;
        this.fetch();
      });

      // Price
      $(document).on('change', '#min-price, #max-price', () => {
        this.state.min_price = parseFloat($('#min-price').val()) || 0;
        this.state.max_price = parseFloat($('#max-price').val()) || 9999;
        this.state.page = 1;
        this.fetch();
      });

      // Orderby
      $(document).on('change', '.woocommerce-ordering select, #aura-orderby', (e) => {
        this.state.orderby = $(e.target).val();
        this.state.page = 1;
        this.fetch();
      });

      // Reset
      $(document).on('click', '.filter-reset', (e) => {
        e.preventDefault();
        $('.filter-category, .filter-attr, #filter-on-sale').prop('checked', false);
        $('#min-price').val('');
        $('#max-price').val('');
        this.state = {
          page: 1,
          orderby: 'menu_order',
          min_price: 0,
          max_price: 9999,
          categories: [],
          attributes: {},
          on_sale: false,
        };
        this.fetch();
      });

      // Pagination (delegated)
      $(document).on('click', '.aura-pagination a', (e) => {
        e.preventDefault();
        const page = $(e.target).data('page');
        if (page) {
          this.state.page = page;
          this.fetch();
          $('html, body').animate({ scrollTop: $('.shop-results').offset().top - 100 }, 300);
        }
      });
    },

    readUrl() {
      const params = new URLSearchParams(window.location.search);
      if (params.has('min_price')) this.state.min_price = parseFloat(params.get('min_price'));
      if (params.has('max_price')) this.state.max_price = parseFloat(params.get('max_price'));
      if (params.has('orderby')) this.state.orderby = params.get('orderby');
    },

    fetch() {
      const $grid = $('.products-grid, ul.products');
      const $loading = $('.products-loading');
      const $count = $('.shop-results-count');

      $grid.css('opacity', 0.4);
      $loading.addClass('active');
      $(document).trigger('aura:filters:start');

      $.post(auraData.ajaxUrl, {
        action: 'aura_filter_products',
        nonce: auraData.nonce,
        page: this.state.page,
        orderby: this.state.orderby,
        min_price: this.state.min_price,
        max_price: this.state.max_price,
        categories: this.state.categories,
        attributes: this.state.attributes,
        on_sale: this.state.on_sale ? 1 : 0,
      })
        .done((res) => {
          if (res.success) {
            $grid.html(res.data.html).css('opacity', 1);
            if ($count.length) {
              $count.text(res.data.found + ' products');
            }
            // rebuild pagination if needed
            this.renderPagination(res.data.max_page);
          }
        })
        .fail(() => {
          $grid.css('opacity', 1);
        })
        .always(() => {
          $loading.removeClass('active');
          $(document).trigger('aura:filters:end');
        });
    },

    renderPagination(maxPage) {
      const $pag = $('.aura-pagination');
      if (!$pag.length || maxPage <= 1) {
        $pag.empty();
        return;
      }
      let html = '';
      for (let i = 1; i <= maxPage; i++) {
        html += `<a href="#" data-page="${i}" class="${i === this.state.page ? 'current' : ''}">${i}</a>`;
      }
      $pag.html(html);
    },
  };

  $(document).ready(() => Filters.init());
})(jQuery);
