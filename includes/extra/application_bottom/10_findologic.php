<?php
/******** Findologic **********/
if (defined('MODULE_FINDOLOGIC_STATUS') && MODULE_FINDOLOGIC_STATUS == 'True' && MODULE_FINDOLOGIC_AUTOCOMPLETE == 'True') {
  
  require_once (DIR_FS_EXTERNAL.'findologic/findologic_config.inc.php');
  
  echo '
  <script
      type="text/javascript"
      src="https://secure.findologic.com/autocomplete/require.js"
      data-main="https://secure.findologic.com/autocomplete/' . strtoupper(md5(FL_SHOP_ID)) . '/autocomplete.js">
  </script>
  ';
}
/******** Findologic **********/
?>