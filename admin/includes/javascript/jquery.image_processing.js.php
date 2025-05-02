<?php
 /*-------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/

  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
?>

<script>
  var aip_debug = false;
  var total = 0;

  jQuery(document).submit(function(e){
    var form = jQuery(e.target);
    if (form.is("#form_image_processing")) {
        e.preventDefault();
        $('.ajax_responce').show();
        $('.ajax_imgname').show();
        $('.ajax_loading').show();
        $('.ajax_ready_info').hide();
        $('.ajax_btn_back').hide();
        $('.ajax_count').html('0');
        updateProgressBar(1,'image',0);
        var ajax_url = form.attr("action");
        ajax_url += "&<?php echo xtc_session_name() . '=' . xtc_session_id(); ?>";
        var dataStr = form.serialize();
        ajaxCall(ajax_url, dataStr);
    }
  });


  function ajaxCall(ajax_url, dataStr) {
    if (aip_debug) console.log('dataStr: ' + dataStr);
    if (aip_debug) console.log('ajax_url:' + ajax_url);

    jQuery.ajax({
      url: ajax_url,
      type: 'POST',
      timeout: 60000, //Set a timeout (in milliseconds) for the request. 
      dataType: 'json',
      data : dataStr,
      error: function(xhr, status, error) {
        alert(xhr.responseText);
      },
      success: function(data) {
        JStoPHPResponse(data);
      }
    })
  }


  function JStoPHPResponse(data) {
    var response = data ;
    if (aip_debug) console.log('response:' + $.param(response));
    if (aip_debug) console.log('ajax_url:' + response.ajax_url);
    
    $('.ajax_imgname').html(response.imgname);
    $('.ajax_count').html(response.count);
    updateProgressBar(response.total,'image',response.start);

    if (response.start < response.total) {
       var dataStrNew = $.param(response)
       if (aip_debug) console.log('$.param:' + dataStrNew); 
       ajaxCall(response.ajax_url, dataStrNew);
    } else {
      $('.ajax_imgname').hide();
      $('.ajax_loading').hide();
      $('.ajax_ready_info').show();
      $('.ajax_btn_back').show();
    }
  }

  
  function updateProgressBar(total,type,counter,imgname,laufzeit) {
    precent = (counter *100/total).toFixed(1);
    if (precent > 100) precent = 100;
    $('#show_'+type+'_process').css('width',precent + '%');
    $('#'+ type + '_precents').html(precent + '%');
   
    if (aip_debug) console.log('precent:' + precent); 
    if (aip_debug) console.log('type:' + type);
  }


  function getReadableFileSizeString(fileSizeInBytes,precision) {
    if (typeof precision == "undefined") {
        precision = 2;
    }
    var i = -1;
    var byteUnits = [' kB', ' MB', ' GB', ' TB', 'PB', 'EB', 'ZB', 'YB'];
    do {
        fileSizeInBytes = fileSizeInBytes / 1024;
        i++;
    } while (fileSizeInBytes > 1024);

    return Math.max(fileSizeInBytes, 0).toFixed(precision) + byteUnits[i];
  };
</script>
