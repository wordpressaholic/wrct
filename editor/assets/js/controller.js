if( typeof wrct === "undefined" ){
  var wrct = {
    model: {},
    view: {},
    controller: {},
    data: {},
  };
}

(function($){

  var model = wrct.model,
      view = wrct.view,
      controller = wrct.controller,
      data = wrct.data;

  controller.init = function(){

    controller.take_query_snapshot(data.query);

    // title
    $('body').on('blur', '.wrct-table-title', controller.update_table_title);

    // action link
    $('body').on('click', '.wrct-action', function(e){ e.preventDefault() });

    // switch editor tabs
    $('body').on('click', '.wrct-tab-label', controller.switch_editor_tabs);

    //-- init resume tab
    if( window.location.hash ){
      $('[data-wrct-tab="'+ window.location.hash.substr(1) +'"].wrct-tab-label').trigger('click');
    }

    // courses

    //-- categories
    $('body').on('change', '.wrct-category-options input[type="checkbox"]', controller.update_categories);

    //-- max-posts
    $('body').on('change', '.wrct-max-posts', controller.set_max_posts);

    //-- course ids
    $('body').on('change', '.wrct-course-ids', controller.set_course_ids);
    $('.wrct-course-ids').trigger('change');

    //-- default orderby
    $('body').on('change', '.wrct-default-orderby', controller.set_default_orderby);

    //-- pagination
    $('body').on('change', '.wrct-pagination', controller.toggle_pagination);

    //-- search
    $('body').on('change', '.wrct-search', controller.toggle_search);

    //-- sorting
    $('body').on('change', '.wrct-sorting', controller.toggle_sorting);

    //-- query args
    $('body').on('change', '.wrct-query-args', controller.set_query_args);

    // columns

    //-- shift position
    $('body').on('click', '.wrct-column-position i', controller.shift_column );

    //-- drag columns
    $('body').on('sortupdate', '.columns-config-wrapper', controller.set_column_positions );

    //-- hide column
    $('body').on('click', '.wrct-column-hide-option', controller.hide_column)

    //-- change heading
    $('body').on('change', '.wrct-column-heading-option', controller.change_column_heading);

    //-- enable additional columns
    $('body').on('change', '.wrct-additional-columns-options input[type=checkbox]', controller.toggle_additional_columns);

    //-- remove custom field columns
    $('body').on('click', '.wrct-remove-custom-field-column', controller.remove_custom_field_column);

    //-- toggle custom field details
    $('body').on('click', '.wrct-custom-field-details-toggle', controller.toggle_custom_field_details);

    //-- submit custom field
    $('body').on('click', '.wrct-submit-custom-field', controller.add_custom_field);

    //-- cancel custom field
    $('body').on('click', '.wrct-cancel-custom-field', controller.cancel_custom_field);

    //-- fetch columns
    $('body').on('click', '.wrct-fetch-columns-request-message a, .wrct-fetch-columns-failure-message a', controller.fetch_columns);

    //-- responsive drop-down summary template
    $('body').on('change', '.wrct-responsive-summary-template-input', controller.set_responsive_summary_template)

    // psuedo label for checkboxes. Adjacent span will focus them
    $('body').on('click', '.wrct-editor input[type=checkbox] + span', function(){
      $(this).prev().click();
    })

    // message triggers
    //-- open
    $('body').on('click', '.wrct-trigger-next-message', function(){
      $(this).hide().next().show();
    })
    //-- close
    $('body').on('click', '.wrct-message-close', function(){
      $(this).closest('.wrct-help-message').hide().prev('.wrct-trigger-next-message').show();
    })

    // styling
    $('body').on('change', '[data-wrct-tab="styling"] input:not([data-wrct-no]), [data-wrct-tab="styling"] textarea:not([data-wrct-no]), [data-wrct-tab="styling"] select:not([data-wrct-no])',  controller.styling );

    //-- apply style theme
    $('body').on('change', '[data-wrct-tab="styling"] #wrct-theme', controller.apply_theme );

    // buttons
    $('body').on('change', '.wrct-editor-tab-buttons input, .wrct-editor-tab-buttons select', controller.update_buttons);
    $('.wrct-editor-tab-buttons input, .wrct-editor-tab-buttons select').trigger('change');

    // submit
    //-- button click
    $('body').on('submit', '.wrct-form', controller.save_editor_data);
    // keyboard: Ctrl/Cmd + s
    $(window).bind('keydown', function( e ) {
      if ( e.ctrlKey || e.metaKey && String.fromCharCode( e.which ).toLowerCase( ) === 's' ){
         e.preventDefault( );
        $('.wrct-form').submit( );
      }
    });

  }

  // switch editor tabs
  controller.switch_editor_tabs = function(){
    var $this = $(this),
    tab = $this.attr('data-wrct-tab'),
    $labels = $this.siblings('.wrct-tab-label'),
    $contents = $this.siblings('.wrct-tab-content'),
    $target_content = $contents.filter('[data-wrct-tab='+ tab +']'),
    active_class = 'active';

    $labels.removeClass(active_class);
    $this.addClass(active_class);

    $contents.removeClass(active_class);
    $target_content.addClass(active_class);

    window.location.hash = tab;
  }

  // update categories
  controller.update_categories = function(){
    var categories = [];
    $('.wrct-category-options input[type="checkbox"]:checked').each(function(){
      categories.push($(this).val());
    })

    if( ! categories.length ){
      // no categories selected
      view.set_categories_request();
    }else{
      view.set_categories_request_over();
    }

    model.set_categories(categories);

    if( categories.length && controller.fetch_columns_requirement() ){
      controller.fetch_columns_request();
    }else{
      view.fetch_columns_over();
    }
  }

  // check if columns really need to be fetched
  controller.fetch_columns_requirement = function(){

    // cats
    //-- length
    if( controller.query_snapshot.categories.length !== data.query.categories.length ) return true;

    //-- contents
    if( data.query.categories.length ){
      var different = false;
      $.each( controller.query_snapshot.categories, function( key, val ){
        if( val !== data.query.categories[key] ){
          different = true;
        }
      } )

      if( different ) return true;
    }

    // different course ids
    //-- length
    if( controller.query_snapshot.course_ids !== data.query.course_ids ) return true;
    //-- contents
    if( data.query.course_ids ){
      snapshot_course_ids_arr = controller.query_snapshot.course_ids.split(",");
      data_course_ids_arr = data.query.course_ids.split(",");

      if( snapshot_course_ids_arr.length !== data_course_ids_arr.length ) return true;

      var diff = false;
      $.each( data_course_ids_arr, function( key, val ){
        if( -1 === $.inArray( val, snapshot_course_ids_arr ) ) diff= true;
      } )

      if( diff ) return true;
    }

    // snapshot and current query are the same
    return false;
  }

  // keeps a copy of the given query for future comparison
  controller.take_query_snapshot = function(query){
    controller.query_snapshot = jQuery.extend( {}, query );
  }

  // set a template for the responsive drop down summary
  controller.set_responsive_summary_template = function(){
    var template = $(this).val();
    model.set_responsive_summary_template( template );
  }

  // set max limit of courses that can be displayed
  controller.set_max_posts = function(){
    var max_posts = $('.wrct-max-posts').val();
    model.set_max_posts(max_posts);
  }

  // set course ids
  controller.set_course_ids = function(){
    var course_ids = $('.wrct-course-ids').val().trim();

    model.set_course_ids(course_ids);

    if( course_ids ){
      view.disable_categories( );
    }else{
      view.enable_categories( );
    }

    if( controller.fetch_columns_requirement() ){
      controller.fetch_columns_request();
    }else{
      view.fetch_columns_over();
    }

  }

  // set default orderby
  controller.set_default_orderby = function(){
    var default_orderby = $('.wrct-default-orderby').val();
    model.set_default_orderby(default_orderby);
  }

  // toggle pagination
  controller.toggle_pagination = function(){
    var toggle = !! $('.wrct-pagination:checked').length;
    model.toggle_pagination(toggle);
  }

  // toggle search
  controller.toggle_search = function(){
    var toggle = !! $('.wrct-search:checked').length;
    model.toggle_search(toggle);
  }

  // toggle sorting
  controller.toggle_sorting = function(){
    var toggle = !! $('.wrct-sorting:checked').length;
    model.toggle_sorting(toggle);
  }

  // query args
  controller.set_query_args = function(){
    var query_args = $('.wrct-query-args').val();
    model.set_query_args( query_args );
  }

  // fetch columns
  controller.fetch_columns_request = function(){
    view.fetch_columns_over();
    view.fetch_columns_request();
  }

  controller.fetch_columns = function (){

    view.fetch_columns_over();
    view.fetch_columns_ongoing();

    model.fetch_columns({
      // callback obj
      // result : action
      'failure' : function(){
        view.fetch_columns_over();
        setTimeout(
          function( ){
            view.fetch_columns_failure( );
          }, 1
        )
      },
      'success' : function( result ){
        view.fetch_columns_over( );
        view.render_columns( );
        controller.take_query_snapshot( result.query );
      },
    });

  }

  // hide columns
  controller.hide_column = function(){

    var $this = $(this),
        name = $this.closest('[data-wrct-name]').attr('data-wrct-name'),
        hide_status = $this.is(':checked');
    model.hide_column(hide_status, name);
  }

  // shift columns
  controller.shift_column = function(){

    var $this = $(this),
        name = $this.closest('[data-wrct-name]').attr('data-wrct-name'),
        direction = $this.hasClass('wrct-move-column-up') ? 'left' : 'right';
    model.shift_column(name, direction);

    view.render(null, $('.editor'), data);
  }

  controller.set_column_positions = function(){
    var col_order_arr = [];
    $(".column-config-row").each(function(){
      var $this = $(this),
          name = $this.attr("data-wrct-name");
      if(name){
        col_order_arr.push(name);
      }
    })

    model.set_column_positions(col_order_arr);
  }

  // change column heading
  controller.change_column_heading = function(){
    var $this = $(this),
        name = $this.closest('[data-wrct-name]').attr('data-wrct-name'),
        heading = $this.val();
    model.change_column_heading(name, heading);
    view.render_editor();
  }

  // toggle additional columns
  controller.toggle_additional_columns = function(e){
    var $options = $(".wrct-additional-columns-options input[type=checkbox]"),
        additional_columns = {};

    $options.each(function(){
      var $this = $(this),
          column_name = $this.val(),
          column_heading = $this.attr('data-wrct-heading'),
          enable;
      if( $this.prop("checked") ){
        column_enable = true;
      }else{
        column_enable = false;
      }
      additional_columns[column_name] = {
        enable: column_enable,
        heading: column_heading,
      }
    })

    model.toggle_additional_columns(additional_columns);
    view.render_columns();
  }

  // remove custom field column
  controller.remove_custom_field_column = function(){
    var $this = $(this),
        column_name = $this.attr('data-wrct-name');
    model.remove_column(column_name);
    view.render_columns();
  }

  // toggle custom field details
  controller.toggle_custom_field_details = function(){
    var $cf_details = $('.wrct-custom-field-details');
        $cf_details_toggle = $('.wrct-custom-field-details-toggle');
    $cf_details.add($cf_details_toggle).toggle();
  }

  //-- submit custom field
  controller.add_custom_field = function(){
    var name = $('[name=wrct-cf-name]').val().trim(),
        heading = $('[name=wrct-cf-heading]').val(),
        val_type = $('[name=wrct-cf-val-type]').val();

    var col_info = {
      name: name,
      heading: heading,
      type: 'custom field',
      val_type: val_type,
    }

    if( ! name ){
      $('[name=wrct-cf-name]').addClass('wrct-input-invalid');
      return;
    }else{
      $('[name=wrct-cf-name]').removeClass('wrct-input-invalid');
    }

    controller.toggle_custom_field_details();

    model.add_column(col_info);
    view.render_columns();
  }

  //-- cancel custom field
  controller.cancel_custom_field = function(){
    controller.toggle_custom_field_details();
  }

  // collect styling details
  controller.styling = function(e){
    var $this = $(this),
        key = $this.attr('id').substring(5),
        val = $this.val();
    if($this.is(':checkbox')){
      if(!$this[0].checked){
        val = '';
      }
    }

    model.set_style(key, val);
  }

  controller.apply_theme = function(e){
    var $this = $(this),
        selected_theme_name = $this.val(),
        themes = {
          'orange': {"header-bg-color":"#f16237","header-text-color":"#ffffff","odd-rows-bg-color":"#f7f7f7","even-rows-bg-color":"#ffffff","link-button-bg":"#ffffff","cart-checkout-button-text-color":"#ffffff","cart-checkout-button-bg":"#81ca00","link-button-text-color":"#000000","header-border-bottom-color":"#ce5f2f","rows-bg-color-responsive-odd":"#ffffff","rows-bg-color-odd":"#f9f9f9","rows-bg-color-even":"#ffffff","rows-bg-color-responsive-even":"#f9f9f9","header-bg-color-responsive-odd":"#f16237","header-bg-color-responsive-even":"#f76c42","price-alternate-text-color":"#bf5130","buttons-alternate-text-color":"#bf5130"},
          'black': {"header-bg-color":"#565656","header-text-color":"#ffffff","odd-rows-bg-color":"#f7f7f7","even-rows-bg-color":"#ffffff","link-button-bg":"#ffffff","cart-checkout-button-bg":"#4c4c4c","cart-checkout-button-text-color":"#e5e5e5","link-button-text-color":"#0a0a0a","header-text-color-responsive":"#ffffff","border-color":"","rows-bg-color-responsive-odd":"#f7f7f7","rows-bg-color-responsive-even":"#ffffff","header-bg-color-responsive-odd":"#595959","header-bg-color-responsive-even":"#7c7c7c","header-border-bottom-width":"2"},
          'blank': {"header-bg-color":"#f7f7f7","header-text-color":"#000000","odd-rows-bg-color":"#f7f7f7","even-rows-bg-color":"#ffffff","link-button-bg":"","cart-checkout-button-bg":"","cart-checkout-button-text-color":"","link-button-text-color":"","price-color":"#0a0000","price-alternate-text-color":"#0a0202","buttons-alternate-text-color":"#0a0000","rows-bg-color-odd":"#fbfbfb","header-text-color-responsive":"","border-color":"","header-bg-color-responsive-odd":"#f9f9f9","header-bg-color-responsive-even":"#ffffff","header-border-bottom-color":"#262626","header-border-bottom-width":"4","rows-bg-color-responsive-odd":"#f9f9f9","rows-bg-color-responsive-even":"#ffffff"},
        };
    if( selected_theme_name === "none" ){
      return;
    }
    model.apply_theme(selected_theme_name, themes);
    view.render(null, $('.wrct-editor'), data);
    $('.wrct-styling-row .wrct-color-picker').each(function(){
      var $this = $(this),
          val = $this.val();
      $this.iris('color', val);
      if(! val){
        $this.iris('color', '#fff');
        $this.val('').change();
      }
    })
  }

  controller.update_buttons = function( ){
    var $this = $(this),
        prop_val = $this.val(),
        relations = {
          'wrct-button-1-enable-option' : 'button-1-enable',
          'wrct-button-1-label-option' : 'button-1-label',
          'wrct-button-1-link-option' : 'button-1-link',
          'wrct-button-1-target-option' : 'button-1-target',

          'wrct-button-2-enable-option' : 'button-2-enable',
          'wrct-button-2-label-option' : 'button-2-label',
          'wrct-button-2-link-option' : 'button-2-link',
          'wrct-button-2-target-option' : 'button-2-target',

          'wrct-button-3-enable-option' : 'button-3-enable',
          'wrct-button-3-label-option' : 'button-3-label',
          'wrct-button-3-link-option' : 'button-3-link',
          'wrct-button-3-target-option' : 'button-3-target',
        };

    $.each(relations, function(elm_class, prop_key){
      if( $this.hasClass( elm_class ) ){
        model.update_buttons( prop_key, prop_val );
        return false;
      }
    })

  }

  controller.price_variation = function(e){
    var $this = $(this),
    val = $this.val(),
    $course_row = $this.closest('.row'),
    pv_string = $course_row.attr('data-wrct-price-variations'),
    $column = $this.closest('.column'),
    $price = $column.siblings('[data-wrct-name="Price"]'),
    price = $price.attr('data-wrct-default-price'),
    currency_symbol = $this.closest('.table').attr('data-wrct-currency-symbol'),
    has_price_text = $course_row.attr('data-wrct-price-text');

    if(has_price_text) return;
    if(!$price.length) return;
    if(!pv_string || pv_string.length < 3) return;

    var pv_json = JSON.parse( pv_string.split("&quot;").join('"') );

    $.each(pv_json, function (_price, all_variations_requirements){
      var match = true;
      $.each(all_variations_requirements, function(key, variation_requirement){
        var curr_val = $course_row.find('[data-wrct-name="'+ variation_requirement.key +'"] select').val();
        if(variation_requirement.val !== curr_val){
          match = false;
        }
      })
      if(match){
        price = _price
        return false;
      }
    })

    $price.find('span').text(currency_symbol + price);
  }

  controller.force_trigger_template_from_cell = function(e){
    var $target = $(e.target),
        $this = $(this);
    if($target.attr('data-wrct-trigger-template') || $target.is('select')){
      e.stopPropagation();
    }else{
      $this.find('[data-wrct-trigger-template]').trigger('click');
      e.preventDefault();
    }
  }

  controller.update_table_title = function(){
    var $this = $(this),
        new_title = $this.val();
    $('.wrct-sc [name="title"]').val(new_title);
  }

  // save JSON data to server
  controller.save_editor_data = function( e ){

    e.preventDefault();

    var $this = $(this), // form
        post_id = $this.find("input[name='post_id']").val(),
        title = $this.find("input[name='title']").val(),
        nonce = $this.find("input[name='nonce']").val(),
        json_data = JSON.stringify( data ),
        $button = $this.find( ".wrct-save" );

    if( ! $this.hasClass("wrct-saving") ){
      $.ajax( {
        type: "POST",
        url: ajaxurl,

        beforeSend: function(){
          $this.addClass("wrct-saving");
          $button.addClass("disabled");
        },

        data: {
          action: "wrct_save_editor_data",
          wrct_post_id: post_id,
          wrct_title: title,
          wrct_nonce: nonce,
          wrct_data: json_data,
        },

        success: function(){
          $this.removeClass("wrct-saving");
          $button.removeClass("disabled");
        }
      } );
    }

  }

  controller.escape_markup = function(markup){
    if(typeof markup === 'string'){
      var replaceMap = {
        '\\': '&#92;',
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        '\'': '&#39;',
        '/': '&#47;'
      };

      markup = markup.replace(/[&<>"'\/\\]/g, function (match) {
        return replaceMap[match];
      });
    }

    return markup;
  }

})(jQuery);
