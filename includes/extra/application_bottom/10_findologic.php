<?php
/******** Findologic **********/
if (defined('MODULE_FINDOLOGIC_STATUS') && MODULE_FINDOLOGIC_STATUS == 'True' && MODULE_FINDOLOGIC_AUTOCOMPLETE == 'True') {
  echo '
  <script
      type="text/javascript"
      src="https://secure.findologic.com/autocomplete/require.js"
      data-main="https://secure.findologic.com/autocomplete/' . strtoupper(md5(MODULE_FINDOLOGIC_SHOP_ID)) . '/autocomplete.js">
  </script>
  ';
}
/******** Findologic **********/
?>