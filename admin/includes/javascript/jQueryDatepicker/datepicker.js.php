<?php
/* -----------------------------------------------------------------------------------------
   datepicker.js.php 
   Version 1.00 (c) by web28 www.rpa-com.de
   ---------------------------------------------------------------------------------------*/
?>

<link type="text/css" href="includes/javascript/jQueryDatepicker/mod.datepick.css" rel="stylesheet" />
<script type="text/javascript" src="includes/javascript/jQueryDatepicker/jquery.plugin.min.js"></script>
<script type="text/javascript" src="includes/javascript/jQueryDatepicker/jquery.datepick.min.js"></script>

<?php
// set language, if file not exists language = english
if (file_exists('includes/javascript/jQueryDatepicker/jquery.datepick-' . strtolower($_SESSION['language_code']) . '.js')) {
  echo '<script type="text/javascript" src="includes/javascript/jQueryDatepicker/jquery.datepick-' . strtolower($_SESSION['language_code']) . '.js"></script>';
}
?>
<script type="text/javascript" src="includes/javascript/jQueryDatepicker/jquery.datepick.ext.min.js"></script>

<script type="text/javascript">
$(function() {
  $.datepick.setDefaults({
    renderer: $.datepick.weekOfYearRenderer, 
    firstDay: 1, 
    showOtherMonths: true,
    dateFormat: 'yyyy-mm-dd'
  });
});
</script>