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
    )
{
  require_once (DIR_FS_INC.'get_order_total.inc.php');
  $total = get_order_total($last_order);

  $currency_query = xtc_db_query("SELECT currency
                                    FROM " . TABLE_ORDERS . "
                                   WHERE orders_id = '" . (int)$last_order . "'");
  $currency = xtc_db_fetch_array($currency_query);
  
  $script = '<script type="text/javascript">';
  $script_js = '<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>';
  if (defined('MODULE_COOKIE_CONSENT_STATUS') 
      && MODULE_COOKIE_CONSENT_STATUS == 'true' 
      && (in_array(8, $_SESSION['tracking']['allowed']) 
          || defined('COOKIE_CONSENT_NO_TRACKING')
          )
      )
  {
    $script = '<script async data-type="text/javascript" type="as-oil" data-purposes="8" data-managed="as-oil">';
    $script_js = '<script async data-src="//www.googleadservices.com/pagead/conversion.js" data-type="text/javascript" type="as-oil" data-purposes="8" data-managed="as-oil"></script>';
  }
?>
<div style="height:0px;overflow:hidden;">
<!-- Google Code for Purchase Conversion Page -->
<?php echo $script; ?>
  /* <![CDATA[ */
  var google_conversion_id = <?php echo GOOGLE_CONVERSION_ID; ?>;
  var google_conversion_language = "<?php echo GOOGLE_LANG; ?>";
  var google_conversion_label = "<?php echo GOOGLE_CONVERSION_LABEL; ?>";
  var google_conversion_value = <?php echo number_format($total, 2); ?>;
  var google_conversion_currency = "<?php echo $currency['currency']; ?>";
  var google_remarketing_only = false;
  /* ]]> */
</script>
<?php echo $script_js; ?>
</div>
<?php
}
?>