(function($){

  var model = wrct.model,
      view = wrct.view,
      controller = wrct.controller,
      data = wrct.data;

  model.shift_column = function(name, direction){ // e.data is direction

    // set new order for headings
    var curr_index = 0;
    $.each(data.columns, function(key, column){
      if( column.name === name ){
        curr_index = key;
        return false;
      }
    })

    var copy = jQuery.extend({}, data.columns[curr_index]),
        adjacent = false;

    if( curr_index === 0 && direction === 'left' ){
      data.columns.push(copy);
      data.columns.shift();

    }else if( curr_index === (data.columns.length - 1) && direction === 'right' ){
      data.columns.unshift(copy);
      data.columns.pop();

    }else{
      if(direction === 'right'){
        adjacent = data.columns[ curr_index + 1 ];
        data.columns.splice(curr_index + 2, 0, copy);
        data.columns.splice(curr_index, 1);

      }else if(direction === 'left'){
        adjacent = data.columns[ curr_index - 1 ];
        data.columns.splice(curr_index - 1, 0, copy);
        data.columns.splice(curr_index + 1, 1);

      }
    }

  }

  model.set_column_positions = function( col_order_arr ){
    var new_columns = [];
    $.each(col_order_arr, function(key, column_name){
      var data_col_index = model.get_column_index_by_name(column_name);
      new_columns.push( data.columns[data_col_index] );
    })

    data.columns = new_columns;
  }

  model.change_column_heading = function( name, heading ){

    $.each(data.columns, function(key, column){
      if( column.name === name ){
        column.heading = heading;
        return false;
      }
    })

  }

  model.get_column_index_by_name = function(name){
    var index = false;
    $.each(data.columns, function(key, column_data){
      if(column_data.name === name){
        index = key;
      }
    })
    return index;
  }

  model.add_column = function(col_info){ // obj
    data.columns.push(col_info);
  }

  model.remove_column = function(col_name){ // string
    var col_index = model.get_column_index_by_name(col_name);
    data.columns.splice(col_index, 1);
  }

  model.toggle_additional_columns = function(additional_columns){
    $.each( additional_columns, function( column_name, column_info ){
      var data_col_index = model.get_column_index_by_name( column_name ),
          enable = column_info.enable;

      var column_exists = (data_col_index !== false); // can be 0

      if(column_exists && enable){ // is already active
        // - do nothing
      }

      if(column_exists && ! enable){ // needs to be disabled
        data.columns.splice(data_col_index, 1);
      }

      if( ! column_exists && enable){ // needs to be enabled
        data.columns.push({
          heading: column_info.heading,
          name: column_name
        })
      }

      if( ! column_exists && ! enable){ // not enabled and not needed
        // - do nothing
      }

    } )
  }

  // set a template for the responsive drop down summary
  model.set_responsive_summary_template = function(template){ // string
    data.query.responsive_summary_template = template;
  }

  // categories
  model.set_categories = function(category_slugs){ // array
    data.query.categories = category_slugs;
  }

  // max-posts
  model.set_max_posts = function(max_posts){ // string
    data.query.max_posts = max_posts;
  }

  // course-ids
  model.set_course_ids = function(course_ids){ // arr
    data.query.course_ids = course_ids;
  }

  // default orderby
  model.set_default_orderby = function(default_orderby){ // arr
    data.query.default_orderby = default_orderby;
  }

  // pagination
  model.toggle_pagination = function(toggle){ // bool
    data.query.pagination = toggle;
  }

  // search
  model.toggle_search = function(toggle){ // bool
    data.query.search = toggle;
  }

  // sorting
  model.toggle_sorting = function(toggle){ // bool
    data.query.sorting = toggle;
  }

  // query args
  model.set_query_args = function(query_args){ // string
    data.query.query_args = query_args;
  }

  // fetch column data from server
  model.fetch_columns = function(controller_callbacks){ // obj

    // tokens help ensure against request overlap
    if( typeof model.fetch_columns_token === 'undefined' ){
      model.fetch_columns_token = 1;
    }else{
      model.fetch_columns_token++;
    }

    // send across the data object
    // backend should not return data with courses. Too heavy.

    var success = false;

    $.ajax({
      type: "POST",
      url: ajaxurl,

      data: {
        action: 'wrct_fetch_columns',
        wrct_columns: JSON.stringify(data.columns),
        wrct_query: JSON.stringify(data.query),
        wrct_nonce: $('.wrct-form').find("input[name='nonce']").val(),
        wrct_token: model.fetch_columns_token,
      },
      success: function(result){

        success = true;
        result = JSON.parse( result );
        if(result.token == model.fetch_columns_token){
          // request is current
          data.columns = result.columns;
          controller_callbacks['success'](result);
        }

      },
      failure: function( ){
          controller_callbacks['failure']();
      },
      complete: function(){
        if( ! success ){
          controller_callbacks['failure']();
        }
      }
    });

  }

  model.update_buttons = function(key, val){
    if( typeof data.buttons === 'undefined' ){
      data.buttons = {};
    }
    data.buttons[key] = val;
  }

  model.edit_buttons = function(link1, label1, target1, link2, label2, target2, link3, label3, target3, course_ids){
    var $table = $('.table'),
        data = $table.data('wrct');
    $.each(data.courses, function(key, course_data){
      if(course_data.__id == course_ids){
        course_data['Buttons'] = {
          link1 : link1,
          label1 : label1,
          target1 : target1,

          link2 : link2,
          label2 : label2,
          target2 : target2,

          link3 : link3,
          label3 : label3,
          target3 : target3,
        };
        return false;
      }
    })
    view.render($table, $('.editor'), data);
  }

  model.hide_column = function(hide_status, name){
    $.each(data.columns, function(key, column_data){
      if(column_data.name == name){
        column_data.hide = hide_status;
      }
    })
  }

  model.set_style = function(key, val){
    if(typeof data.styling !== 'object' || data.styling.constructor.toString().indexOf("Array") != -1 ){
      data.styling = {};
    }
    data.styling[key] = val;
    view.render_sc($('.wrct-sc'), data);
  }

  model.apply_theme = function(selected_theme_name, themes){
    data.styling = jQuery.extend( {}, data.styling, themes[selected_theme_name] );
  }

})(jQuery);
