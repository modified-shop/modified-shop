/*-----------------------
  jquery.products_tags.js Vers. 1.00
  (c) 2015 by web28 - www.rpa-com.de
-------------------------*/

function set_elm_width(id) {
  var elm = $('#'+id).find('.tag');
  var width = 150;
  elm.each(function() {
    //console.log('width:'+ $(this).html());
    //console.log('width:'+ $(this).outerWidth());
    if (parseInt($(this).outerWidth()) > width) {
      width = parseInt($(this).outerWidth());
    }
  });
  elm.width(width);
}

function show_tag_content(id) {
  $('[id^=tab_tag_]').hide();
  //console.log('id:'+ id);
  $('#'+id).show();
  set_elm_width(id);
}

$(document).ready(function($) {
  $('#ptags').change(function () {
    show_tag_content($(this).val());
  });
  var li = $('.ptags').find('li');
  li.each(function() {
    //console.log('data-val:'+ $(this).attr('data-val'));
    if ($('#'+ $(this).attr('data-val') ).hasClass('flag')) {
      //console.log('data-val:'+ 'FLAG');
      $(this).addClass('li-flag');
    }
  });
  show_tag_content($('#ptags').val());
  set_elm_width($('#ptags').val());
});