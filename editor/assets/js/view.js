(function($){
  var model = wrct.model,
      view = wrct.view,
      controller = wrct.controller,
      data = wrct.data;

  view.render = function($table, $editor, data){
    if(typeof $table === 'undefined' ){
      var $table = $('.table');
    }

    if(typeof $editor === 'undefined' ){
      var $editor = $('.editor');
    }

    view.render_editor();

  }

  view.close_wait = function(){
    vex.closeAll()
  };

  // editor

  view.render_editor = function(){
    view.render_columns();
    view.render_styling();
  }

  view.render_columns = function(){

    var $editor = $(".wrct-editor");

    var $container = $(".columns-config-wrapper");

    $container.empty();

    $.each(data.columns, function (key, column_info){

      var $template = $(".wrct-template.column-config-row").clone();
      $template
        .removeClass( "wrct-template" )
        .appendTo( $container )
        .attr("data-wrct-name", column_info.name)
        .attr("data-wrct-heading", column_info.heading);

      var $name = $template.find( ".name" );
      $name
        // .attr( "title", $name.attr( "title" ) + column_info.name )
        .attr( "title", column_info.name )
        .html( column_info.name );

      if( typeof column_info.type !== 'undefined' && column_info.type == 'custom field' ){
        var txt = column_info.heading + " (cf: "+ column_info.name +")";
        $name.html( txt );
        $name.attr( "title", txt );
        $name.css( "text-transform", "none" );
      }

      var $heading = $template.find( ".heading" );
      $heading
        .find( "input" )
        .val( column_info.heading );

      var $hide_option = $template.find( ".hide input" );
      if( column_info.hide ){
        $hide_option.attr( "checked", "" );
      }else{
        $hide_option.removeAttr( "checked" );
      }

    })

    // additional columns- custom fields
    $('.wrct-additional-column.wrct-remove-custom-field-column').remove();
    var mkp = '';
    jQuery.each( data.columns, function( key, col_info ){
      if( typeof col_info.type !== 'undefined' && col_info.type === 'custom field' ){
        mkp += '<div class="wrct-additional-column wrct-remove-custom-field-column" data-wrct-name="'+ col_info.name +'"><i class="fa fa-times"></i><span>'+ col_info.heading +' (cf: '+ col_info.name +')</span></div>';
      }
    } )
    $('.wrct-additional-column:last').after(mkp);

  }

  view.render_styling = function(){

    var $editor = $(".wrct-editor");

    if(typeof data.styling === 'undefined'){
      data.styling = {css: ''};
    }

    $.each(data.styling, function(style_key, style_val){
      var $input = $('#wrct-' + style_key);
      if($input.is(':checkbox')){
        var current_val = $input.val();
        if(style_val === current_val){
          $input[0].checked = true;
        }else{
          $input[0].checked = false;
        }
      }else{
        $input.val(style_val);
      }
    })

  }

  view.set_categories_request = function(){
    $( '.wrct-editor' ).addClass('wrct-set-categories-request');
  }

  view.set_categories_request_over = function(){
    $( '.wrct-editor' ).removeClass('wrct-set-categories-request');
  }

  view.disable_categories = function(){
    $( '.wrct-category-options' ).find( 'input[type=checkbox]' ).attr( 'disabled', 'disabled' );
    $( '.wrct-editor' ).addClass( 'wrct-using-course-ids' );
  }

  view.enable_categories = function(){
    $( '.wrct-category-options' ).find( 'input[type=checkbox]' ).removeAttr( 'disabled' );
    $( '.wrct-editor' ).removeClass( 'wrct-using-course-ids' );
  }

  view.render_sc = function($sc, data){
    var data_json = JSON.stringify(data);
    $sc.find('[name=data]').val(data_json);
  }

  view.fetch_columns_request = function(){
    $('.wrct-editor').addClass('wrct-fetch-columns-request');
  }

  view.fetch_columns_ongoing = function(){
    $('.wrct-editor').addClass('wrct-fetch-columns-ongoing');
  }

  view.fetch_columns_failure = function(){
    $('.wrct-editor').addClass('wrct-fetch-columns-failure');
  }

  view.fetch_columns_over = function(){
    $('.wrct-editor').removeClass('wrct-fetch-columns-request wrct-fetch-columns-ongoing wrct-fetch-columns-failure');
  }

})(jQuery);
