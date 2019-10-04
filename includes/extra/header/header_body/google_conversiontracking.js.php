<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2016 [www.modified-shop.org]

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// GOOGLE CONV. TRACKING
if (basename($PHP_SELF) == FILENAME_CHECKOUT_SUCCESS 
    && GOOGLE_CONVERSION == 'true'
    && $_SESSION['tracking']['allow'] === true
    )
{
  require_once (DIR_FS_INC.'get_order_total.inc.php');
  $total = get_order_total($last_order);

  $currency_query = xtc_db_query("SELECT currency
                                    FROM " . TABLE_ORDERS . "
                                   WHERE orders_id = '" . (int)$last_order . "'");
  $currency = xtc_db_fetch_array($currency_query);
?>
<div style="height:0px;overflow:hidden;">
<!-- Google Code for Purchase Conversion Page -->
<script type="text/javascript">
  /* <![CDATA[ */
  var google_conversion_id = <?php echo GOOGLE_CONVERSION_ID; ?>;
  var google_conversion_language = "<?php echo GOOGLE_LANG; ?>";
  var google_conversion_label = "<?php echo GOOGLE_CONVERSION_LABEL; ?>";
  var google_conversion_value = <?php echo number_format($total, 2); ?>;
  var google_conversion_currency = "<?php echo $currency['currency']; ?>";
  var google_remarketing_only = false;
  /* ]]> */
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
<noscript>
  <div style="display:inline;">
    <img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/<?php echo GOOGLE_CONVERSION_ID; ?>/?value=<?php echo number_format($total, 2); ?>&amp;currency_code=<?php echo $currency['currency']; ?>&amp;label=<?php echo GOOGLE_CONVERSION_LABEL; ?>&amp;guid=ON&amp;script=0"/>
  </div>
</noscript>
</div>
<?php
}
?>