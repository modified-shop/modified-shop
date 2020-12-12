<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

?>
<script type="text/javascript">
var ajax_type = 'POST';
var dataStr = '';
ajaxCall(dataStr);

function ajaxCall(dataStr) {
  if (debug) console.log('dataStr: ' + dataStr);
  if (debug) console.log('url:' + decodeEntities(ajax_url));

  jQuery.ajax({
    url: decodeEntities(ajax_url),
    type: ajax_type,
    timeout: 300000,
    dataType: 'json',
    data : dataStr,
    error: function(xhr, status, error) {
      alert(xhr.responseText);
    },
    success: function(data){
      JStoPHPResponse(data);
    }
  });
}

function JStoPHPResponse(data) {
  var response = data ;
  if (debug) console.log('response:' + $.param(response));
  
  var data_ok = '';
  
  if ('nr' in response && response.nr != '' && response.nr != 'undefined') {
    data_ok += '<div>' + '<?php echo constant("TEXT_INFO_".strtoupper($process)); ?>' + (response.nr) + '<?php echo TEXT_INFO_FROM;?>' + (response.num_tables) + '</div>';
  }
  if ('actual_table' in response && response.actual_table != '' && response.actual_table != 'undefined') {
    data_ok += '<div>' + '<?php echo TEXT_INFO_LAST; ?>' + response.actual_table + '</div>';
  }
  data_ok += '<div>' + '<?php echo TEXT_INFO_CALLS; ?>' + response.aufruf + '</div>';
  data_ok += '<div>' + '<?php echo TEXT_INFO_ROWS; ?>' + response.anzahl_zeilen + '</div>';
  data_ok += '<div>' + '<?php echo TEXT_INFO_TIME; ?>' +  response.time  + '</div>';
  
  $('#data_ok').html(data_ok);
  
  if ('nr' in response) {
    updateProgressBar(response.num_tables,response.nr,'backup');
  }
  
  var maxReloadsText = '';
  if (response.aufruf > maxReloads) {
    response.nr = response.num_tables;
    maxReloadsText = '<span>' + '<?php echo TEXT_INFO_MAX_RELOADS; ?>' + maxReloads + '</span>';
  }

  if (('nr' in response && response.nr < response.num_tables)
      || ('fileEOF' in response && response.fileEOF != 1)
      )
  {
     var dataStrNew = $.param(response);
     if (debug) console.log('$.param:' + dataStrNew); 
     ajaxCall(dataStrNew);
  } else if (typeof continue_url !== 'undefined') {
    $(location).attr('href', decodeEntities(continue_url));
  } else {
    var infoText = '<?php echo constant("TEXT_INFO_DO_".strtoupper($process)."_OK");?>';
    var infoWait = '<?php echo constant("TEXT_INFO_FINISH");?>';
    if (maxReloadsText != '') infoText = maxReloadsText;
    $('#info_wait').html('&nbsp;');
    $('#info_text').html(infoText);
    $('#process_info_wait').html(infoWait);
    $('#button_back').html(button_back);
    $('.processing_bar').hide();
    $('.process_wrapper').show();
    $('#backup_precents').show();
  }
}

function updateProgressBar(total,counter,type) {
  precent = (counter *100/total).toFixed(0); //+ '%';
  $('#'+ type + '_process').css('width',precent + '%');
  $('#'+ type + '_precents').html(precent + '%');
 
  if (debug) console.log('precent:' + precent); 
  if (debug) console.log('type:' + type);
}

function decodeEntities(encodedString) {
  var textArea = document.createElement('textarea');
  textArea.innerHTML = encodedString;
  return textArea.value;
}
</script>
