<?php 
/* -------------------------------------------------------------------------------------
jquery.backup_db.js.php
Vers. 1.00 (c) www.rpa-com.de
* ----------------------------------------------------------------------------------- */

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

?>

<script type="text/javascript">
var debug = false;
var ajax_url = 'backup_db.php?ajax=1&action=readdb<?php echo SID ? '&'. SID : '';?>';
var ajax_type = 'POST';
var dataStr = '';

var maxReloads = <?php echo MAX_RELOADS;?>;

ajaxCall(dataStr);

function ajaxCall(dataStr) {
    if (debug) console.log('dataStr: ' + dataStr);
    if (debug) console.log('url:' + ajax_url);
    //return;
		jQuery.ajax({
			url: ajax_url,
			type: ajax_type,
			timeout: 300000, //Set a timeout (in milliseconds) for the request. 
			dataType: 'json',
      data : dataStr,
			error: function() {
        //('.img_'+response.type).css('display','none');
				alert('Error loading json data!');
			},
			success: function(data){
        JStoPHPResponse(data);
			}
		})
}

function JStoPHPResponse(data) {
    // Antwort des Server ggf. verarbeiten
    var response = data ;
    if (debug) console.log('response:' + $.param(response));
    
    var data_ok = '<div><b>' + 'Tabellen gesichert: ' + (response.nr) + ' von ' + (response.num_tables) + '</b></div>';
    data_ok += '<div><b>' + '<br />Zuletzt bearbeitet: ' + response.actual_table + '</b></div>';
    data_ok += '<div><b>' + '<br />Seitenaufrufe: ' + response.aufruf + '</b></div>';
    data_ok += '<div><b>' + '<br />Scriptlaufzeit: ' +  response.time  + '</b></div>';
    
    $('#data_ok').html(data_ok);
    
    updateProgressBar(response.num_tables,response.nr,'backup');
    
    var maxReloadsText = '';
    if (response.aufruf > maxReloads) {
      response.nr = response.num_tables;
      maxReloadsText = '<span>' + '<?php echo (defined('TEXT_MAX_RELOADS') ? TEXT_MAX_RELOADS : 'Maximale Seitenreloads wurden erreicht: ');?>' + maxReloads + '</span>';
    }

    if (response.nr < response.num_tables) {
       var dataStrNew = $.param(response) //jquery build http query string
       if (debug) console.log('$.param:' + dataStrNew); 
       ajaxCall(dataStrNew);
    } else {
      //$('#info_wait').css('display','none');
      $('#info_wait').html('&nbsp;');
      
      var infoText = '<?php echo TEXT_INFO_DO_BACKUP_OK;?>';
      if (maxReloadsText != '') infoText = maxReloadsText;
      $('#info_text').html(infoText);
    
      var button_back = '<a href="backup.php<?php echo SID ? '?'. SID : '';?>" class="button">'+ '<?php echo BUTTON_BACK;?>' +'</a>';
      $('#button_back').html(button_back);
      
    }
}

function updateProgressBar(total,counter,type) {
  precent = (counter *100/total).toFixed(0); //+ '%';
  $('#'+ type + '_process').css('width',precent + '%');
  $('#'+ type + '_precents').html(precent + '%');
 
  if (debug) console.log('precent:' + precent); 
  if (debug) console.log('type:' + type);
}
</script>
