/*
 * $Id: jquery.sidebar.js 87 2016-06-06 09:38:11Z Tomcraft $
 *
 * id, col_left = gesamte sidebar
 * class, sidebar_btn = schalter zum umschalten
 * class, sidebar_marker = Markierung für die Art der Ansicht, wenn hier die font-size > 0, dann mobile
 * layout_navbar, layout_logo, layout_content, layout_footer
 * col_left, sidebar_filler
 */
var marker = null;
var markerSize = null;
var windowWidth = null;
var heightleft = null;

$(function() {
  marker = $('#sidebar_marker');
  markerSize = marker.css('font-size');
  windowWidth = $(window).width(); 
   
  resetSidebarFiller();
  $('#layout_wrap').addClass('wrap_sidebar_inactive');
  
  $('.sidebar_closer').click(function() {
    $('.sidebar_btn').trigger('click');
  });
  
  $('.box_header').click(function() {
    if (marker.css('font-size') != '0px') {
      var content = $(this).next();
      if (content.css('display') == 'none') {
        $(this).removeClass('sidebar_inactive');
        $(this).addClass('sidebar_active');
      } else {
        $(this).removeClass('sidebar_active');
        $(this).addClass('sidebar_inactive');
      }
      content.toggle(300, function() {
        resizeSidebarFiller();
      });
    }
  });
  
  $('.sidebar_btn').click(function() {
    if (marker.css('font-size') != '0px') {
      $('.sidebar_layer').toggle(300);
      if ($('#col_left_overlay').css('display') != 'block') {
        sidebarOpen();
      } else {
        sidebarClose(0);
        $('.sidebar_closer').hide(300);
      }
      
      $('#col_left_overlay').toggle(300, function () {
        resizeSidebarFiller();
        $('.sidebar_closer').focusout();
        $('.sidebar_closer').blur();
        if ($('#col_left_overlay').css('display') == 'block') {
          if ($('.sidebar_closer').css('display') == 'none') {
            $('.sidebar_closer').show();
          }
        }
      });
    }
  });
  
  $(window).resize(function() {
    if (markerSize != marker.css('font-size')) {
      /* Nur beim Wechsel */
      if (marker.css('font-size') == '0px') {
        /* Desktop */
        sidebarClose(0);
        $('.box_sidebar').show();
        if ($('#col_left_overlay').css('display') == 'none') {
          $('#col_left_overlay').show();
        }
      } else {
        /* Mobile */
        sidebarClose(0);
        setSidebarBoxState();
        if ($('#col_left_overlay').css('display') == 'block') {
          $('.sidebar_layer').hide();
          $('#col_left_overlay').hide();
          $('.sidebar_closer').hide();
        }
      }
      resizeSidebarFiller();
      markerSize = marker.css('font-size');
    }
    if ($(window).width() != windowWidth) {
      sidebarClose(1);
      windowWidth = $(window).width();
    }
  });

  $("body").bind('keyup.escape', function(e) {
    if (e.keyCode == 27) {
      sidebarClose(1);
    }
  });

  function setSidebarBoxState() {
    $('.box_header').removeClass('sidebar_active');
    $('.box_header').addClass('sidebar_inactive');
    $('.box_sidebar').hide();
    
    $('#loginBox').find('.box_header').removeClass('sidebar_inactive');
    $('#loginBox').find('.box_header').addClass('sidebar_active');
    $('#loginBox').find('.box_sidebar').show();
  }
      
  function sidebarOpen() {
    setSidebarBoxState();
    var moveLeft = marker.css('background-position').split(" ");
    moveLeft = moveLeft[0]; /*x position */
    $('#layout_wrap').animate({ marginLeft: moveLeft }, 300);
    $('.copyright').animate({ marginLeft: moveLeft }, 300); 
    $('#layout_wrap').css('position', 'fixed');
    $('#layout_wrap').height(window.innerHeight);
    $('html,body').css('overflow-x', 'hidden');
  }

  function sidebarClose(mode) {
    /* close Sidebar */
    if ($('#layout_wrap').css('margin-left') != '0px') {
      setSidebarBoxState();
      resizeSidebarFiller();
      resetSidebarFiller();
      $('#layout_wrap').animate({ marginLeft: "0px" }, 300);
      $('.copyright').animate({ marginLeft: "0px" }, 300);
      if (mode != '0') {
        $('.sidebar_layer').hide();
        $('.sidebar_closer').hide();
        if (marker.css('font-size') != '0px') {
          $('#col_left_overlay').hide();
        } else {
          $('#col_left_overlay').show();
          $('.box_sidebar').show();
        }
      }
    }
  }

  function resetSidebarFiller() {
    $('#layout_wrap').css('position', '');
    $('#layout_wrap').css('height', '');
    $('#col_left').css('height', '');
    $('#col_left').css('min-height', '');
    $('#col_left_overlay').css('overflow-y', '');
    $('html,body').css('overflow-x', '');
  }

  function resizeSidebarFiller() {
    if (marker.css('font-size') == '0px') {
      /* Desktop */
      resetSidebarFiller();
    } else {
      /* Mobile */
      if ($('#layout_wrap').css('margin-left') != '0px') {
        var adminspacer = ($(".adminspacer").length ? parseInt($('.adminspacer').css('height')) : 0); 
        heightleft = $('.col_left_inner').height() + parseInt($('#col_left_overlay').css('padding-top')) + adminspacer;
        if ($(window).height() !== window.innerHeight) {
          heightleft += Math.abs($(window).height() - window.innerHeight);
        }
        $('#col_left_overlay').css('overflow-y', '');
        if (heightleft > window.innerHeight) {
          $('#col_left_overlay').css('overflow-y', 'auto');
        }
        $('#col_left').css("height", heightleft);
        $('#col_left').css('min-height', window.innerHeight);
      } else {
        resetSidebarFiller();
      }
    }
  }
});
