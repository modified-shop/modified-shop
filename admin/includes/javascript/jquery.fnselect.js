/*!
 * jquery.fnselect - v1.0.0
 * 2015-03-16 
 *
 * Copyright by web28, www.rpa-com.de
 */

$(document).ready(function () {
    $('.selectBox').each(function () {
      var elmName = $(this).attr('name').toLowerCase();
      //Fallback if .fnSelectWrap not exists
      if (! $(this).closest(".fnSelectWrap").length ) {
          console.log("has no parent with the class .fnSelectWrap");
          $(this).wrap('<div class="fnSelectWrap '+ elmName +'">');
      }
      console.log('outerWidth:' + elmName + '|' + $(this).outerWidth());
      var selWidth = parseInt( $(this).outerWidth());
      //Fix element is disabled
      if (selWidth < 1) {
          var clone = $(this).clone();
          clone.css("visibility","hidden");
          $('body').append(clone);
          selWidth = parseInt(clone.outerWidth());
          clone.remove();
          console.log('cloneWidth' + selWidth);
      }
      selWidth += 27;
      $('.'+elmName).width(selWidth);
      console.log('div outerWidth:' + $('.'+elmName).outerWidth());
      $(this).addClass( "fnSelect" );
      $(this).width($('.'+elmName).outerWidth());
    });
});