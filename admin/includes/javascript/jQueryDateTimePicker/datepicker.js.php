<?php
/* -----------------------------------------------------------------------------------------
   $Id: shipcloud.php 2011-11-24 modified-shop $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
?>
<link type="text/css" href="includes/javascript/jQueryDateTimePicker/jquery.datetimepicker.css" rel="stylesheet" />
<script type="text/javascript" src="includes/javascript/jQueryDateTimePicker/jquery.datetimepicker.full.min.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $.datetimepicker.setLocale('<?php echo $_SESSION["language_code"]; ?>');    
    // banner manager, coupon admin
    $('#Datepicker1').datetimepicker({
      dayOfWeekStart:1,
      timepicker:false, 
      format:'Y-m-d',
      scrollInput:false
    });
    $('#Datepicker2').datetimepicker({
      dayOfWeekStart:1,
      timepicker:false, 
      format:'Y-m-d',
      scrollInput:false
    });    
    // specials
    $('#DatepickerSpecials').datetimepicker({
      dayOfWeekStart:1,
      timepicker:false, 
      format:'Y-m-d',
      scrollInput:false
    });
    $('#DatepickerSpecialsStart').datetimepicker({
      dayOfWeekStart:1,
      timepicker:false, 
      format:'Y-m-d',
      scrollInput:false
    });  
    // product
    $('#DatepickerProduct').datetimepicker({
      dayOfWeekStart:1,
      timepicker:false, 
      format:'Y-m-d',
      scrollInput:false
    });
  });
</script>
