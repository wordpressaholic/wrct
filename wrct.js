var wrct_init;

jQuery(function($){

  // responsive layout
  var resize_timer,
      window_width;

  // resize event listener
  $( window ).on( 'resize.wrct', resize );

  function resize(e){
    console.log("resizing");
    clearTimeout( resize_timer );
    var new_window_width = window.innerWidth;
    if( new_window_width != window_width ){
      window_width = new_window_width;
      resize_timer = setTimeout( tables_responsive, 250);
    }
  }

  // orientation change event listener
  $( window ).on( 'orientationchange', function( e ) {
    tables_responsive();
  } );

  // toggle table responsive mode
  function tables_responsive( $table ){

    if( ! window_width ){
      window_width = window.innerWidth;
    }

    if( ! $table ){
      $table = $( '.wrct-table' );
    }

    $table.each( function(){
      var $this = $( this ),
          responsive_layout_class = 'wrct-responsive-layout';

      // reset for general & responsive 1 column layouts
      $this.removeClass( responsive_layout_class + ' wrct-responsive-layout-2-columns wrct-responsive-layout-3-columns' );
      $(".wrct-row", $this).css({
        "width": "",
        "margin-right": "",
        "margin-bottom": "",
      })

      // remove notices
      $this.find('.wrct-notice-row, .wrct-notice-cell').remove();

      if( window_width < $this.attr( 'data-wrct-responsive-layout-below' ) ){
      // responsive layout
        $this.addClass( responsive_layout_class );
        var gap = $this.attr( 'data-wrct-responsive-layout-columns-gap-horizontal' );
        $(".wrct-row", $this).css({
          "margin-right": gap+"px"
        })
        // multiple-columns
        if( window_width > $this.attr( 'data-wrct-responsive-layout-3-columns-above' ) ){
        //-- 3
          // assign class
          $this.addClass( 'wrct-responsive-layout-3-columns' );
          // assign width
          $(".wrct-row", $this).css({
            "width": "calc(33.3% - "+(gap * 2 / 3)+"px)",
          })
        }else if( window_width > $this.attr( 'data-wrct-responsive-layout-2-columns-above' ) ){
        //-- 2
          // assign class
          $this.addClass( 'wrct-responsive-layout-2-columns' );
          // assign width
          $(".wrct-row", $this).css({
            "width": "calc(50% - "+(gap / 2)+"px)",
          })
        }

      }else{
      // regular layout

      }

    } )
  }

  // radio button change listener
  $('body').on('change.wrct', '.wrct-radio-button', radio_button_change);

  function radio_button_change(){
    var $radio_button = $(this),
        $cell = $radio_button.closest('.wrct-cell'),
        $radio_buttons = $cell.find('.wrct-radio-button'),
        option_index = $radio_buttons.index($radio_button) + 1, // + 1 accounts for reset op.
        $select_box = $cell.find('.wrct-select');

    $select_box[0].selectedIndex = option_index;
    $select_box.trigger('change');

  }

  // radio button label click listener
  $('body').on('click.wrct', '.wrct-table .wrct-radio-label', radio_label_handler);

  function radio_label_handler(e){
    var $this = $(this),
        $radio = $this.prev('input[type="radio"]');

    $radio.trigger('click');

  }

  // select box change listener
  $('body').on('change.wrct', '.wrct-select', select_box_change);

  function select_box_change(){
    var $select_box = $(this),
        $product_row = $select_box.closest('.wrct-row');

    hide_notice( $product_row );

    // options not selected
    if( ! check_selections($product_row) ){
      return;
    }

    var selected_variation = get_selected_variation($product_row);

    // variation does not exist
    if( ! selected_variation ){
      display_notice( wrct_i18n.i18n_no_matching_variations_text, $product_row );
      return;
    }

    // apply selected variation
    set_price_qty_and_stock($product_row);

    // // out of stock
    // if( ! selected_variation.is_in_stock ){
    //   display_notice(selected_variation.availability_html, $product_row);
    //   return;
    // }

    hide_notice( $product_row );
  }

  // check if necessary selections have been made in product row
  function check_selections($product_row){
    var passed = true;
    $('.wrct-select', $product_row).each(function(){
      if( ! $(this).val() ){
        passed = false;
        return false;
      }
    })

    return passed;
  }

  // updates the price and stock on the DOM based on selected variation
  function set_price_qty_and_stock($product_row){

    var $table = $product_row.closest('.wrct-table'),
        selected_variation = get_selected_variation($product_row);

    // price
    if( ! $product_row.attr('data-wrct-price-text' ) ){
      var $price = $product_row.find('[data-wrct-name="price"] .wrct-cell-val');

      if( selected_variation ){
        $price.html( selected_variation.price_html );
      }else{
        $price.html( $product_row.attr('data-wrct-price-range') );
      }
    }

    // stock
    $product_row.find('[data-wrct-name="stock"] .wrct-cell-val').html( selected_variation.availability_html );

    // qty
    $product_row.find('.input-text.qty').attr({ 'max': selected_variation.max_qty, 'min': selected_variation.min_qty }).val(1);

  }

  function get_selected_variation($product_row){
    // var variations = get_variations($product_row),
    var variations = JSON.parse( $product_row.attr('data-product_variations') ),
        selected_attrs = get_selected_attrs($product_row),
        matching_variation = get_matching_variation(selected_attrs, variations);

    if( ! matching_variation ){
      return false;
    }else{
      return matching_variation;
    }
  }

  function get_variations($product_row){
    var variations = $product_row.data('wrct-variations');

    if( ! variations && $product_row.attr('data-wrct-compressed-variations') ){
      variations = decompress_variations( JSON.parse( $product_row.attr('data-wrct-compressed-variations') ) );
      $product_row.data('wrct-variations', variations);
    }

    return variations;
  }

  function decompress_variations(c_variations){
    var variations = [];
    $.each( c_variations.variations, function( index, variaton ){
      var obj = {};
      $.each( variaton, function(key, val){ // may be obj/arr
        obj[ c_variations.keys[key] ] = c_variations.values[val]
      } )
      variations.push(obj);
    } )

    return variations;
  }

  function get_selected_attrs($product_row){
    var attrs = {};

    $('.wrct-select', $product_row).each(function(){
      var $this = $(this),
          name = $this.closest('.wrct-cell').attr('data-wrct-name'),
          val = $this[0].selectedIndex ? $this.val() : null;

      attrs[name] = val;
    })

    return attrs;
  }

  function get_matching_variation(selected_attrs, variations){

    var matching_variation = false;

    $.each(variations, function(index, variation){
      if( ! variation.attributes ) return;

      var match = true;
      $.each(selected_attrs, function(attr_name, attr_val){
        if( variation.attributes["attribute_"+attr_name] != attr_val ){
          match = false;
          return false;
        }
      })

      if(match){
        matching_variation = variation;
        return false;
      }
    })

    return matching_variation;
  }

  // button click listener
  $('body').on('click.wrct', '.wrct-button', button_click);

  function button_click(e){
    var $button = $(this),
        link_code = $button.attr('data-wrct-link-code'),
        $product_row = $button.closest('.wrct-row');

    if( link_code !== "%post%" ){
      e.preventDefault();

      // prepare AJAX data
      var data = {
        action : 'wrct_add_to_cart',
        "add-to-cart" : $product_row.attr('data-wrct-product-id'),
        product_id : $product_row.attr('data-wrct-product-id'),
        quantity: $product_row.find(".wrct-quantity").length ? $product_row.find(".wrct-quantity").val() : 1
      };

      // variable product
      if( $product_row.attr('data-product_variations') ){
        if( ! check_selections($product_row) ){
          alert(wrct_i18n.i18n_make_a_selection_text);
          return false;
        }

        var selected_variation = get_selected_variation($product_row);
        if( selected_variation && selected_variation.attributes ){
          $.extend(data, selected_variation.attributes);
          data.variation_id = selected_variation['variation_id'];
        }else{
          alert(wrct_i18n.i18n_unavailable_text);
          return false;
        }
      }

      // display notice
      data.return_notice = ( link_code == "%ajax-cart%" );

      $.ajax({
        url: wrct_i18n.ajax_url,
        method: 'POST',
        beforeSend: function(){
          disable_button($button);
          hide_notice($product_row);
        },
        data: data
      })
      .done(function( response ) {

        if ( ! response ) return;

        enable_button($button);

        // reveal the wc notice
        if( response.notice ){
          var $response_notice = $("<div>" + response.notice + "</div>"),
              link_html = $response_notice.find('a').length ? $response_notice.find('a').attr('class', 'wrct-notice-link').detach()[0].outerHTML : "",
              text_html = "<div class='wrct-notice-text'>" + ( $response_notice.find('li').length ? $response_notice.find('li:first-child').html() : $response_notice.html() ) + "</div>";

          // display_notice( response.notice, $product_row);
          display_notice( text_html + link_html, $product_row);
        }

				if ( response.error ) {
        // error

				}else if( response.success ){
        // success
          if( link_code == "%ajax-cart%" ){
  				  $( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, false ] );
          }else{
            window.location = $button.attr('href');
          }
        }

      })

    }

  }

  function disable_button($button){
    $button.addClass("wrct-disabled");
  }

  function enable_button($button){
    $button.removeClass("wrct-disabled");
  }

  function display_notice(notice, $product_row){
    var responsive_layout = $product_row.closest(".wrct-table").hasClass("wrct-responsive-layout");

    // responsive layout
    if(responsive_layout){
      var $earlier_notice = $product_row.children(".wrct-notice-cell"),
          $notice = $("<td class='wrct-notice-cell'><div class='wrct-notice'>"+ notice +"</div></td>");

      if( ! $earlier_notice.length ){
        $notice.appendTo($product_row);
        var $notice_div = $(".wrct-notice", $notice);
        $notice_div.slideUp(0);
        $notice_div.slideDown();
      }else{
        $earlier_notice.replaceWith($notice);
      }

    // regular layout
    }else{
      var $earlier_notice = $product_row.next(".wrct-notice-row"),
          cols_count = $product_row.children(".wrct-cell").length,
          border = $product_row.children(".wrct-cell:first-child").css("border-bottom"),
          $notice = $("<tr class='wrct-notice-row' style='border-bottom:"+ border +";'><td colspan='"+ cols_count +"' class='wrct-notice-cell'><div class='wrct-notice'>"+ notice +"</div></td></tr>");

      if( ! $earlier_notice.length ){
        $notice.insertAfter($product_row);
        var $notice_div = $(".wrct-notice", $notice);
        $notice_div.slideUp(0);
        $notice_div.slideDown();
      }else{
        $earlier_notice.replaceWith($notice);
      }
    }

  }

  function hide_notice($product_row){
    var responsive_layout = $product_row.closest(".wrct-table").hasClass("wrct-responsive-layout");

    // responsive layout
    if(responsive_layout){
      var $earlier_notice_container = $product_row.children(".wrct-notice-cell"),
          $earlier_notice_div = $earlier_notice_container.length ? $earlier_notice_container.find(".wrct-notice") : false;

    // regular layout
    }else{
      var $earlier_notice_container = $product_row.next(".wrct-notice-row"),
          $earlier_notice_div = $earlier_notice_container.length ? $earlier_notice_container.find(".wrct-notice") : false;

    }

    if( $earlier_notice_div ){
      $earlier_notice_div.slideUp(200, function(){
        $earlier_notice_container.remove();
      })
    }

  }

  // returns formatted price with currency symbol and price correctly positioned
  function formatted_price( $currency_symbol, $price, price_format ){
    return price_format.replace( '%1$s', $currency_symbol ).replace( '%2$s', $price ).replace( '&nbsp;', ' ' );
  }

  // responsive drop-down toggle
  $('body').on('click', '.wrct-responsive-layout-accordion .wrct-cell[data-wrct-name="name"]', function(){
    var $this = $(this),
        $product_row = $this.closest('.wrct-row'),
        height_contract = $product_row.attr('data-wrct-height-contract'),
        height_expand = $product_row.attr('data-wrct-height-expand');

    $product_row.toggleClass('wrct-responsive-layout-accordion-expand');
  })

  // search
  $('body').on('submit', '.wrct-search-form', function(e){
    e.preventDefault();

    var $this = $(this),
        $input = $this.find('.wrct-search-input'),
        name = $input.attr('name')
        keyword = $input.val(),
        query = '?' + name + '=' + keyword,
        $container = $this.closest('.wrct');
    attempt_ajax( $container, query );
  })
  //-- clear search
  $('body').on('click', '.wrct-search-clear', function(e){
    e.preventDefault();
    var $this = $(this),
        $form = $this.closest('.wrct-search-form'),
        $input = $form.find('.wrct-search-input');
    $input.val("");
    $form.submit();
  })
  //-- clear search from "no results" msg
  $('body').on('click', '.wrct-no-search-results-message', function(e){
    e.preventDefault();
    var $this = $(this),
        $submit = $this.closest('.wrct').find('.wrct-search-clear');
    $submit.trigger('click');
  })

  // sorting
  //-- via select box
  $('body').on('change', '.wrct-sorting-input', function(e){
    var $this = $(this),
        name = $this.attr('name')
        orderby_val = $this.val(),
        query = '?' + name + '=' + orderby_val,
        $container = $this.closest('.wrct');
    attempt_ajax( $container, query );
  })
  //-- via column heading
  $('body').on('click', '.wrct-sortable.wrct-heading', function(e){
    var $heading = $(this),
        orderby = $heading.attr('data-wrct-name'),
        limited_order = $heading.attr('data-wrct-limited-order'),
        current_order = $heading.attr('data-wrct-current-order'),
        order = $heading.hasClass('wrct-sorting-asc') ? 'DESC': 'ASC',
        $table = $heading.closest('.wrct-table'),
        $container = $table.closest( '.wrct' ),
        table_id = $table.attr('data-wrct-table-id'),
        $target = $(e.target),
        $icons = $heading.find('wrct-sorting-icon'),
        query = '';

    // only one way ordering permitted
    if( limited_order ){
      if( current_order == limited_order ){
        query = ''
      }else{
        query = '?wrct-orderby-' + table_id + '=' + orderby + '&wrct-order-' + table_id + '=' + limited_order;
      }

    }else if( $target.hasClass( 'wrct-sorting-icon' ) ){
    // icon is clicked (in a 2 icon set)

      // asc icon clicked
      if( $target.hasClass('wrct-sorting-asc-icon') ){

        // cancel sorting (button re-click)
        if( current_order === 'ASC' ){
          query = '';
        }else{
          query = '?wrct-orderby-' + table_id + '=' + orderby + '&wrct-order-' + table_id + '=' + 'ASC';
        }

      // desc icon clicked
      }else if( $target.hasClass('wrct-sorting-desc-icon') ){

        // cancel sorting (button re-click)
        if( current_order === 'DESC' ){
          query = '';
        }else{
          query = '?wrct-orderby-' + table_id + '=' + orderby + '&wrct-order-' + table_id + '=' + 'DESC';
        }

      }

    // heading is clicked (not on icon)
    }else{

      // action sequence: clear > asc > desc > clear ...
      if( $heading.hasClass('wrct-sorting-desc') ){
        query = '';
      }else{
        query = '?wrct-orderby-' + table_id + '=' + orderby + '&wrct-order-' + table_id + '=' + order;
      }

    }

    // highlight icon
    $heading.removeClass('wrct-sorting-desc wrct-sorting-asc');
    if( query ){
      if( query.substr( -3 ) == 'ASC' ){
        $heading.addClass('wrct-sorting-asc');
      }else if(query.substr( -4 ) == 'DESC'){
        $heading.addClass('wrct-sorting-desc');
      }
    }

    attempt_ajax( $container, query );

  })

  //-- price sort variations
  function price_sort_variations($table){
    $table.each(function(){
      var $this = $(this),
          orderby = $this.attr('data-wrct-orderby'),
          order = $this.attr('data-wrct-order') || 'ASC',
          $rows = $this.find('.wrct-row');

      if( orderby != 'price' ){
        return;
      }

      $rows.each(function(){
        var $row = $(this),
            variations_attr = $row.attr( 'data-wrct-variations' );

        // target variable products
        if( variations_attr && variations_attr.length > 3 ){
          var variations = JSON.parse( variations_attr ),
              price = order === 'ASC' ? $row.attr('data-wrct-min-price') : $row.attr('data-wrct-max-price'),
              target_variation = false;

          //narrow down on price matching variation
          $.each( variations, function( key, variation_details ){
            if( variation_details['__price'] == price ){
              target_variation = variation_details;
              return false;
            }
          } )

          if( target_variation ){
            $.each( target_variation, function( key, val ){
              // target attribute keys
              if( key.substring(0,2) !== "__" ){
                $row.find( '[data-wrct-name='+ key +']' ).find( '.wrct-select ' ).val( val );
              }
            } )

            $row.find( '.wrct-select' ).first().trigger('change');
          }

        }

      })

    })
  }

  // pagination
  $('body').on('click', '.wrct-pagination .page-numbers:not(.dots):not(.current)', function(e){
    e.preventDefault( );
    var $this = $( this );
    // $this.html('<i class="fa fa-spin fa-refresh"></i>');
    attempt_ajax( $this.closest('.wrct'), $this.attr('href') );
  })

  // ajax
  function attempt_ajax( $container, query ){

    var ajax_key = $container.attr( 'data-wrct-ajax-key' );

    $.ajax({
      url: wrct_i18n.ajax_url + query,
      method: 'POST',
      beforeSend: function(){
        $container.addClass('wrct-loading');
      },
      data: {
        'action' : 'wrct_ajax',
        'wrct-ajax-key': ajax_key
      },
    })
    .done(function( response ) {
      // success
      if( response && response.indexOf('wrct-table') !== -1 ){
        var $new_container = $(response).addClass('wrct-loading').find('.wrct-loading-screen').hide().end();
        wrct_init($new_container);
        $container.replaceWith( $new_container );
        setTimeout( function(){ $new_container.removeClass('wrct-loading').find('.wrct-loading-screen').css('display', ''); }, 1 );

      // fail
      }else{
        window.location = query;

      }
    });
  }

  // initialize
  wrct_init = function($container){

    // target $tables
    if( $container && ! $container.hasClass('wrct-pre-init') ){ // target single
      var $table = $('.wrct-table', $container);
    }else{ // target all
      var $table = $('.wrct.wrct-pre-init .wrct-table');
    }

    // no $tables targetted
    if( ! $table.length ){
      return;
    }

    tables_responsive($table);
    price_sort_variations($table);
    $table.closest('.wrct').removeClass('wrct-pre-init');

  };

  // let's go!
  wrct_init();

})
