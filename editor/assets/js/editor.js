jQuery(function($){
  var model = wrct.model,
      view = wrct.view,
      controller = wrct.controller,
      data = wrct.data;

  if( ! data.query.course_ids ){
    data.query.course_ids = "";
  }

  // mvc init
  controller.init();
  view.render_editor();

  // sortable init
  jQuery(".wrct-sortable").sortable();

  // color picker init
  $('.wrct-color-picker').spectrum({
    showInput: true,
    allowEmpty: true,
    showAlpha: true,
    preferredFormat: 'rgb',
    showButtons: false,
  });

})
