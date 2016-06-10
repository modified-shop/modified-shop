/*
  $Id: jquery.sidebar.js 33 2016-04-15 08:26:09Z GTB $

* id, col_left = gesamte sidebar
* class, sidebar_btn = schalter zum umschalten
* class, sidebar_marker = Markierung für die Art der Ansicht, wenn hier die font-size > 0, dann mobile
* layout_navbar, layout_logo, layout_content, layout_footer
* col_left, sidebar_filler
*/
var marker = null;
var markerSize = null;
var contentHeight = null;
var windowWidth = null;
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
        if ($('#col_left').css('display') != 'block') {
          sidebarOpen();
        } else {
          sidebarClose(0);
          $('.sidebar_closer').hide(300);
        }
        $('#col_left').toggle(300, function() {
          resizeSidebarFiller();
          $('.sidebar_closer').focusout();
          $('.sidebar_closer').blur();
          if ($('#col_left').css('display') == 'block') {
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
          if ($('#col_left').css('display') == 'none') {
            $('#col_left').css('display', 'block');
          }
        } else {
          /* Mobile */
          sidebarClose(0);
          setSidebarBoxState();
          if ($('#col_left').css('display') == 'block') {
            $('.sidebar_layer').hide();
            $('#col_left').hide();
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
      var moveLeft = marker.css('background-position');
      moveLeft = moveLeft.substr(0,moveLeft.indexOf(" "));
      $('#layout_wrap').animate({ marginLeft: moveLeft }, 300);
      $('.copyright').animate({ marginLeft: moveLeft }, 300);        
      $('html').css('overflow-x', 'hidden');
      $('body').css('overflow-x', 'hidden');
    }

    function sidebarClose(mode) {
      //close Sidebar
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
            $('#col_left').hide();
          } else {
            $('#col_left').css('display', 'block');
            $('.box_sidebar').show();
          }
        }
      }
    }

    function resetSidebarFiller() {
      $('.col_left_inner').css('min-height', '');
      $('#sidebar_filler').css('height', '');
      $('#col_left').css('height', '');
      $('html').css('overflow-x', '');
      $('body').css('overflow-x', '');
    }

    function resizeSidebarFiller() {
      if ($('#sidebar_filler').height() == null
          || $('#layout_navbar').height() == null
          || $('#layout_logo').height() == null
          || $('#layout_content').height() == null
          || $('#layout_footer').height() == null
          ) 
      {
        return false;
      } else {
        if (marker.css('font-size') == '0px') {
          /* Desktop */
          resetSidebarFiller();
        } else {
          /* Mobile */
          contentHeight = $('#layout_navbar').height() + $('#layout_logo').height() + $('#layout_content').height() + $('#layout_footer').height();
          contentHeight += 27;
          var colLeftHeight = $('.col_left_inner').height();
          if (contentHeight > colLeftHeight) {
            $('.col_left_inner').css('min-height',contentHeight+'px');
          }else if (colLeftHeight > contentHeight) {
            $('#sidebar_filler').height(colLeftHeight-contentHeight);
          }
          $('#col_left').height($('.col_left_inner').height());
        }
      }
    }
});
