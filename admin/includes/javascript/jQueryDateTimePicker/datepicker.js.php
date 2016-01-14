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
      timepicker:false, 
      format:'Y-m-d'
    });
    $('#Datepicker2').datetimepicker({
      timepicker:false, 
      format:'Y-m-d'
    });    
    // specials
    $('#DatepickerSpecials').datetimepicker({
      timepicker:false, 
      format:'Y-m-d'
    });
    $('#DatepickerSpecialsStart').datetimepicker({
      timepicker:false, 
      format:'Y-m-d'
    });  
    // product
    $('#DatepickerProduct').datetimepicker({
      timepicker:false, 
      format:'Y-m-d'
    });
  });
</script>
