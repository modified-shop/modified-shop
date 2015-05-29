<?php
/* -----------------------------------------------------------------------------------------
   $Id: google_conversiontracking.js.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
?>

<?php
global $last_order; // from checkout_success.php
$total_query = xtc_db_query("-- function.googleanalytics_universal.php
                       SELECT value
                         FROM " . TABLE_ORDERS_TOTAL . "
                        WHERE orders_id = '" . (int)$last_order . "' 
                          AND class='ot_total'");
$total = xtc_db_fetch_array($total_query);
?>

<!-- Google Code for Purchase Conversion Page -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = <?php echo GOOGLE_CONVERSION_ID; ?>;
var google_conversion_language = "<?php echo GOOGLE_LANG; ?>";
var google_conversion_format = "2";
var google_conversion_color = "ffffff";
var google_conversion_label = "<?php echo GOOGLE_CONVERSION_LABEL; ?>";
var google_conversion_value = "<?php echo $total['value']; ?>";
var google_remarketing_only = false;
/* ]]> */
</script>
<?php
//BOF - Dokuman - 2009-08-19 - BUGFIX: #0000223 SSL/NONSSL check for google conversiontracking
if ($request_type=='NONSSL') { 
//EOF - Dokuman - 2009-08-19 - BUGFIX: #0000223 SSL/NONSSL check for google conversiontracking
?>
<script language="JavaScript" src="http://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/<?php echo GOOGLE_CONVERSION_ID; ?>/?value=0&amp;label=<?php echo GOOGLE_CONVERSION_LABEL; ?>&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<?php
//BOF - Dokuman - 2009-08-19 - BUGFIX: #0000223 SSL/NONSSL check for google conversiontracking
}else{
?>
<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="https://www.googleadservices.com/pagead/conversion/<?php echo GOOGLE_CONVERSION_ID; ?>/?value=0&amp;label=<?php echo GOOGLE_CONVERSION_LABEL; ?>&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
<?php
}
//EOF - Dokuman - 2009-08-19 - BUGFIX: #0000223 SSL/NONSSL check for google conversiontracking
?>