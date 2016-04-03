<?php 
/* -------------------------------------------------------------------------------------
jquery.image_processing.php
Vers. 3.52 (c) www.rpa-com.de
* ----------------------------------------------------------------------------------- */

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

?>

<script type="text/javascript">

var aip_debug = false;

var total = 0;

<?php
if (defined('EDITION') && EDITION == 'GAMBIO_GX2') {
?>
$(document).ready(function() { 
  $('.lower_file_ext').css('display','none');
});
<?php
}
?>

jQuery(document).submit(function(e){
    var form = jQuery(e.target);
    if (form.is("#form_image_processing")) { // check if this is the form that you want
        e.preventDefault();
        $('.ajax_responce').show();
        $('.ajax_imgname').show();
        $('.ajax_loading').show();
        $('.ajax_ready_info').hide();
        $('.ajax_btn_back').hide();
        $('.ajax_count').html('0');
        updateProgressBar(1,'image',0);
        var ajax_url = form.attr("action");
        ajax_url += ''<?php echo SID ? '&'. SID : '';?>;
        var dataStr = form.serialize();
        ajaxCall(ajax_url, dataStr);
    }
});


function ajaxCall(ajax_url, dataStr) {
    if (aip_debug) console.log('dataStr: ' + dataStr);
    if (aip_debug) console.log('ajax_url:' + ajax_url);
    //return;
    jQuery.ajax({
      url: ajax_url,
      type: 'POST',
      timeout: 60000, //Set a timeout (in milliseconds) for the request. 
      dataType: 'json',
      data : dataStr,
      error: function() {
        //alert('Error loading json data!');
        alert('Error!');
      },
      success: function(data) {
        JStoPHPResponse(data);
      }
    })
}


function JStoPHPResponse(data) {
    // Antwort des Server ggf. verarbeiten
    var response = data ;
    if (aip_debug) console.log('response:' + $.param(response));
    if (aip_debug) console.log('ajax_url:' + response.ajax_url);
    
    //$.each(response, function( key, value ) {
      //console.log('key: ' + key + ' | value: ' + value);
    //});
$('.ajax_imgname').html(response.imgname);
    $('.ajax_count').html(response.count);
    updateProgressBar(response.total,'image',response.start);
    //return;
    if (response.start < response.total) {
       //new ajax call
       var dataStrNew = $.param(response) //jquery build http query string
       if (aip_debug) console.log('$.param:' + dataStrNew); 
       ajaxCall(response.ajax_url, dataStrNew);
    } else {
      //ready
      $('.ajax_imgname').hide();
      $('.ajax_loading').hide();
      $('.ajax_ready_info').show();
      $('.ajax_btn_back').show();
    }
}


function updateProgressBar(total,type,counter,imgname,laufzeit) {
  precent = (counter *100/total).toFixed(1); //+ '%';
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


$(document).ready(function() {  
  //
});
</script>