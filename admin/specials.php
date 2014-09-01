<?php
/* --------------------------------------------------------------
   $Id: specials.php 1125 2005-07-28 09:59:44Z novalis $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(specials.php,v 1.38 2002/05/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (specials.php,v 1.9 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require('includes/application_top.php');

  require(DIR_FS_CATALOG.DIR_WS_CLASSES . 'xtcPrice.php');
  $xtPrice = new xtcPrice(DEFAULT_CURRENCY,$_SESSION['customers_status']['customers_status_id']);

  require_once(DIR_FS_INC .'xtc_get_tax_rate.inc.php');


  switch ($_GET['action']) {
    case 'setflag':
      xtc_set_specials_status($_GET['id'], $_GET['flag']);
      xtc_redirect(xtc_href_link(FILENAME_SPECIALS, '', 'NONSSL'));
      break;
    case 'insert':
      // insert a product on special

     if (PRICE_IS_BRUTTO=='true' && substr($_POST['specials_price'], -1) != '%'){
     //BOF - Dokuman - 2009-08-19 - BUGFIX: #0000264 specials are no longer deactivated when stock check = false
     //$sql="select tr.tax_rate from " . TABLE_TAX_RATES . " tr, " . TABLE_PRODUCTS . " p  where tr.tax_class_id = p. products_tax_class_id  and p.products_id = '". $_POST['products_up_id'] . "' ";
     
        $sql="select tr.tax_rate from " . TABLE_TAX_RATES . " tr, " . TABLE_PRODUCTS . " p  where tr.tax_class_id = p. products_tax_class_id  and p.products_id = '". $_POST['products_id'] . "' ";
     //EOF - Dokuman - 2009-08-19 - BUGFIX: #0000264 specials are no longer deactivated when stock check = false
        
        $tax_query = xtc_db_query($sql);
        $tax = xtc_db_fetch_array($tax_query);
        $_POST['specials_price'] = ($_POST['specials_price']/($tax['tax_rate']+100)*100);
     }
     
     if (substr($_POST['specials_price'], -1) == '%')  {
     	$new_special_insert_query = xtc_db_query("select products_id,products_tax_class_id, products_price from " . TABLE_PRODUCTS . " where products_id = '" . (int)$_POST['products_id'] . "'");
        $new_special_insert = xtc_db_fetch_array($new_special_insert_query);
        $_POST['products_price'] = $new_special_insert['products_price'];
      $_POST['specials_price'] = ($_POST['products_price'] - (($_POST['specials_price'] / 100) * $_POST['products_price']));
      }


      // BOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1
      /*
      $expires_date = '';
      if ($_POST['day'] && $_POST['month'] && $_POST['year']) {
        $expires_date = $_POST['year'];
        $expires_date .= (strlen($_POST['month']) == 1) ? '0' . $_POST['month'] : $_POST['month'];
        $expires_date .= (strlen($_POST['day']) == 1) ? '0' . $_POST['day'] : $_POST['day'];
      }
      */
      $expires_date = '';
      if ($_POST['specials_expires']) {
        $expires_date = str_replace("-", "", $_POST['specials_expires']);
      }
      // EOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1
	  
      xtc_db_query("insert into " . TABLE_SPECIALS . " (products_id, specials_quantity, specials_new_products_price, specials_date_added, expires_date, status) values ('" . $_POST['products_id'] . "', '" . $_POST['specials_quantity'] . "', '" . $_POST['specials_price'] . "', now(), '" . $expires_date . "', '1')");
      xtc_redirect(xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page']));
      break;

    case 'update':
      // update a product on special
      if (PRICE_IS_BRUTTO=='true' && substr($_POST['specials_price'], -1) != '%'){
        $sql="select tr.tax_rate from " . TABLE_TAX_RATES . " tr, " . TABLE_PRODUCTS . " p  where tr.tax_class_id = p. products_tax_class_id  and p.products_id = '". $_POST['products_up_id'] . "' ";
        $tax_query = xtc_db_query($sql);
        $tax = xtc_db_fetch_array($tax_query);
        $_POST['specials_price'] = ($_POST['specials_price']/($tax[tax_rate]+100)*100);
     }

      if (substr($_POST['specials_price'], -1) == '%')  {
      $_POST['specials_price'] = ($_POST['products_price'] - (($_POST['specials_price'] / 100) * $_POST['products_price']));
      }
      // BOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1
      /*
      $expires_date = '';
      if ($_POST['day'] && $_POST['month'] && $_POST['year']) {
        $expires_date = $_POST['year'];
        $expires_date .= (strlen($_POST['month']) == 1) ? '0' . $_POST['month'] : $_POST['month'];
        $expires_date .= (strlen($_POST['day']) == 1) ? '0' . $_POST['day'] : $_POST['day'];
      }
      */
      $expires_date = '';
      if ($_POST['specials_expires']) {
        $expires_date = str_replace("-", "", $_POST['specials_expires']);
      }
      // EOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1

      xtc_db_query("update " . TABLE_SPECIALS . " set specials_quantity = '" . $_POST['specials_quantity'] . "', specials_new_products_price = '" . $_POST['specials_price'] . "', specials_last_modified = now(), expires_date = '" . $expires_date . "' where specials_id = '" . $_POST['specials_id'] . "'");
      xtc_redirect(xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials_id));
      break;

    case 'deleteconfirm':
      $specials_id = xtc_db_prepare_input($_GET['sID']);

      xtc_db_query("delete from " . TABLE_SPECIALS . " where specials_id = '" . xtc_db_input($specials_id) . "'");

      xtc_redirect(xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page']));
      break;
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>"> 
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script type="text/javascript" src="includes/general.js"></script>
<?php
  if ( ($_GET['action'] == 'new') || ($_GET['action'] == 'edit') ) {
?>
<!-- BOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1 //-->
<!--
<link rel="stylesheet" type="text/css" href="includes/javascript/calendar.css">
<script type="text/javascript" src="includes/javascript/calendarcode.js"></script>
//-->
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script type="text/javascript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<!-- EOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1 //-->
<?php
  }
?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onLoad="SetFocus();">
<!-- BOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1 //-->
<!--
<div id="popupcalendar" class="text"></div>
//-->
<div id="spiffycalendar" class="text"></div>
<!-- EOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1 //-->
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td class="boxCenter" width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  if ( ($_GET['action'] == 'new') || ($_GET['action'] == 'edit') ) {
    $form_action = 'insert';
    if ( ($_GET['action'] == 'edit') && ($_GET['sID']) ) {
	  $form_action = 'update';

      $product_query = xtc_db_query("select p.products_tax_class_id,
                                            p.products_id,
                                            pd.products_name,
                                            p.products_price,
                                            s.specials_quantity,
                                            s.specials_new_products_price,
                                            s.expires_date from
                                            " . TABLE_PRODUCTS . " p,
                                            " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                            " . TABLE_SPECIALS . "
                                            s where p.products_id = pd.products_id
                                            and pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                                            and p.products_id = s.products_id
                                            and s.specials_id = '" . (int)$_GET['sID'] . "'");
      $product = xtc_db_fetch_array($product_query);

      $sInfo = new objectInfo($product);
	  
      // BOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1
	  // build the expires date in the format YYYY-MM-DD	  
	  if ($sInfo->expires_date != 0){
		$expires_date = substr($sInfo->expires_date, 0, 4)."-".
						substr($sInfo->expires_date, 5, 2)."-".
						substr($sInfo->expires_date, 8, 2);
      }	else $expires_date = "";				  
      // EOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1	  
				
    } else {
      $sInfo = new objectInfo(array());

      // create an array of products on special, which will be excluded from the pull down menu of products
      // (when creating a new product on special)
      $specials_array = array();
      $specials_query = xtc_db_query("select
                                      p.products_id from
                                      " . TABLE_PRODUCTS . " p,
                                      " . TABLE_SPECIALS . " s
                                      where s.products_id = p.products_id");

      while ($specials = xtc_db_fetch_array($specials_query)) {
        $specials_array[] = $specials['products_id'];
      }
    }
?>
<!-- BOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1 //-->
<script type="text/javascript">
  var specialExpires = new ctlSpiffyCalendarBox("specialExpires", "new_special", "specials_expires","btnDate1","<?php echo $expires_date; ?>",2);
</script>
<!-- EOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1 //-->
      <tr><form name="new_special" <?php echo 'action="' . xtc_href_link(FILENAME_SPECIALS, xtc_get_all_get_params(array('action', 'info', 'sID')) . 'action=' . $form_action, 'NONSSL') . '"'; ?> method="post"><?php if ($form_action == 'update') echo xtc_draw_hidden_field('specials_id', $_GET['sID']); ?>
        <td><br /><table border="0" cellspacing="0" cellpadding="2">
          
                <td class="main"><?php echo TEXT_SPECIALS_PRODUCT; echo ($sInfo->products_name) ? "" :  ''; ?>&nbsp;</td>
	   <?php
		$price=$sInfo->products_price;
		$new_price=$sInfo->specials_new_products_price;
		if (PRICE_IS_BRUTTO=='true'){
 			$price_netto=xtc_round($price,PRICE_PRECISION);
			$new_price_netto=xtc_round($new_price,PRICE_PRECISION);
            $price= ($price*(xtc_get_tax_rate($sInfo->products_tax_class_id)+100)/100);
			$new_price= ($new_price*(xtc_get_tax_rate($sInfo->products_tax_class_id)+100)/100);
		}
		$price=xtc_round($price,PRICE_PRECISION);
		$new_price=xtc_round($new_price,PRICE_PRECISION);

		echo '<input type="hidden" name="products_up_id" value="' . $sInfo->products_id . '">';
	   ?>      
          <td class="main"><?php echo ($sInfo->products_name) ? $sInfo->products_name . ' <small>(' . $xtPrice->xtcFormat($price,true). ')</small>' : xtc_draw_products_pull_down('products_id', 'style="font-size:10px"', $specials_array); echo xtc_draw_hidden_field('products_price', $sInfo->products_price); ?></td>
	  </tr>
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_SPECIAL_PRICE; ?>&nbsp;</td>
            <td class="main"><?php echo xtc_draw_input_field('specials_price', $new_price);?> </td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_SPECIAL_QUANTITY; ?>&nbsp;</td>
            <td class="main"><?php echo xtc_draw_input_field('specials_quantity', $sInfo->specials_quantity);?> </td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_EXPIRES_DATE; ?>&nbsp;</td>
<!-- BOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1 //-->
<!--
            <td class="main"><?php echo xtc_draw_input_field('day', substr($sInfo->expires_date, 8, 2), 'size="2" maxlength="2" class="cal-TextBox"') . xtc_draw_input_field('month', substr($sInfo->expires_date, 5, 2), 'size="2" maxlength="2" class="cal-TextBox"') . xtc_draw_input_field('year', substr($sInfo->expires_date, 0, 4), 'size="4" maxlength="4" class="cal-TextBox"'); ?><a class="so-BtnLink" href="javascript:calClick();return false;" onMouseOver="calSwapImg('BTN_date', 'img_Date_OVER',true);" onMouseOut="calSwapImg('BTN_date', 'img_Date_UP',true);" onclick="calSwapImg('BTN_date', 'img_Date_DOWN');showCalendar('new_special','dteWhen','BTN_date');return false;"><?php echo xtc_image(DIR_WS_IMAGES . 'cal_date_up.gif', 'Calendar', '22', '17', 'align="absmiddle" name="BTN_date"'); ?></a></td>
//-->
            <td class="main"><script type="text/javascript">specialExpires.writeControl(); specialExpires.dateFormat="yyyy-MM-dd";</script>
                <noscript>
                <?php echo  xtc_draw_input_field('specials_expires', $expires_date ,'style="width: 130px"'); ?>
                </noscript>
            </td>
<!-- EOF - Tomcraft - 2009-11-06 - SPIFFY CAL 2.1 //-->
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><br /><?php echo TEXT_SPECIALS_PRICE_TIP; ?></td>
            <td class="main" align="right" valign="top"><br /><?php echo (($form_action == 'insert') ? '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_INSERT . '"/>' : '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_UPDATE . '"/>'). '&nbsp;&nbsp;&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $_GET['sID']) . '">' . BUTTON_CANCEL . '</a>'; ?></td>
          </tr>
        </table></td>
      </form></tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRODUCTS_PRICE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $specials_query_raw = "select p.products_id, pd.products_name,p.products_tax_class_id, p.products_price, s.specials_id, s.specials_new_products_price, s.specials_date_added, s.specials_last_modified, s.expires_date, s.date_status_change, s.status from " . TABLE_PRODUCTS . " p, " . TABLE_SPECIALS . " s, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and pd.language_id = '" . $_SESSION['languages_id'] . "' and p.products_id = s.products_id order by pd.products_name";
    $specials_split = new splitPageResults($_GET['page'], '20', $specials_query_raw, $specials_query_numrows);
    $specials_query = xtc_db_query($specials_query_raw);
    while ($specials = xtc_db_fetch_array($specials_query)) {
 
 		$price=$specials['products_price'];
		$new_price=$specials['specials_new_products_price'];
		if (PRICE_IS_BRUTTO=='true'){
 			$price_netto=xtc_round($price,PRICE_PRECISION);
			$new_price_netto=xtc_round($new_price,PRICE_PRECISION);
            $price= ($price*(xtc_get_tax_rate($specials['products_tax_class_id'])+100)/100);
			$new_price= ($new_price*(xtc_get_tax_rate($specials['products_tax_class_id'])+100)/100);
		}
		$specials['products_price']=xtc_round($price,PRICE_PRECISION);
		$specials['specials_new_products_price']=xtc_round($new_price,PRICE_PRECISION);
    
      if ( ((!$_GET['sID']) || ($_GET['sID'] == $specials['specials_id'])) && (!$sInfo) ) {
        $products_query = xtc_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . $specials['products_id'] . "'");
        $products = xtc_db_fetch_array($products_query);
        $sInfo_array = xtc_array_merge($specials, $products);
        $sInfo = new objectInfo($sInfo_array);
        $sInfo->specials_new_products_price = $specials['specials_new_products_price'];
        $sInfo->products_price = $specials['products_price'];
      }

      if ( (is_object($sInfo)) && ($specials['specials_id'] == $sInfo->specials_id) ) {
        echo '                  <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '                  <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'pointer\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials['specials_id']) . '\'">' . "\n";
      }
?>
                <td  class="dataTableContent"><?php echo $specials['products_name']; ?></td>
                <td  class="dataTableContent" align="right"><span class="oldPrice">
                
                <?php
                
        
                
                
                 echo $xtPrice->xtcFormat($specials['products_price'],true); ?>
                </span> <span class="specialPrice">
                <?php echo $xtPrice->xtcFormat($specials['specials_new_products_price'],true); ?>
                </span></td>
                <td  class="dataTableContent" align="right">
<?php
      if ($specials['status'] == '1') {
        echo xtc_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . xtc_href_link(FILENAME_SPECIALS, 'action=setflag&flag=0&id=' . $specials['specials_id'], 'NONSSL') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
      } else {
        echo '<a href="' . xtc_href_link(FILENAME_SPECIALS, 'action=setflag&flag=1&id=' . $specials['specials_id'], 'NONSSL') . '">' . xtc_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . xtc_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
      }
?></td>
<!-- BOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
<!--
                <td class="dataTableContent" align="right"><?php if ( (is_object($sInfo)) && ($specials['specials_id'] == $sInfo->specials_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials['specials_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
-->
                <td class="dataTableContent" align="right"><?php if ( (is_object($sInfo)) && ($specials['specials_id'] == $sInfo->specials_id) ) { echo xtc_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ICON_ARROW_RIGHT); } else { echo '<a href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $specials['specials_id']) . '">' . xtc_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
<!-- EOF - Tomcraft - 2009-06-10 - added some missing alternative text on admin icons -->
      </tr>
<?php
    }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $specials_split->display_count($specials_query_numrows, '20', $_GET['page'], TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?></td>
                    <td class="smallText" align="right"><?php echo $specials_split->display_links($specials_query_numrows, '20', MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (!$_GET['action']) {
?>
                  <tr> 
                    <td colspan="2" align="right"><?php echo '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&action=new') . '">' . BUTTON_NEW_PRODUCTS . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($_GET['action']) {
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</b>');

      $contents = array('form' => xtc_draw_form('specials', FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br /><b>' . $sInfo->products_name . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br /><input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_DELETE . '"/>&nbsp;<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id) . '">' . BUTTON_CANCEL . '</a>');
      break;

    default:
      if (is_object($sInfo)) {
        $heading[] = array('text' => '<b>' . $sInfo->products_name . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=edit') . '">' . BUTTON_EDIT . '</a> <a class="button" onclick="this.blur();" href="' . xtc_href_link(FILENAME_SPECIALS, 'page=' . $_GET['page'] . '&sID=' . $sInfo->specials_id . '&action=delete') . '">' . BUTTON_DELETE . '</a>');
        $contents[] = array('text' => '<br />' . TEXT_INFO_DATE_ADDED . ' ' . xtc_date_short($sInfo->specials_date_added));
        $contents[] = array('text' => '' . TEXT_INFO_LAST_MODIFIED . ' ' . xtc_date_short($sInfo->specials_last_modified));
        $contents[] = array('align' => 'center', 'text' => '<br />' . xtc_product_thumb_image($sInfo->products_image, $sInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
        $contents[] = array('text' => '<br />' . TEXT_INFO_ORIGINAL_PRICE . ' ' . $xtPrice->xtcFormat($sInfo->products_price,true));
        $contents[] = array('text' => '' . TEXT_INFO_NEW_PRICE . ' ' . $xtPrice->xtcFormat($sInfo->specials_new_products_price,true));
        $contents[] = array('text' => '' . TEXT_INFO_PERCENTAGE . ' ' . number_format(100 - (($sInfo->specials_new_products_price / $sInfo->products_price) * 100)) . '%');

        $contents[] = array('text' => '<br />' . TEXT_INFO_EXPIRES_DATE . ' <b>' . xtc_date_short($sInfo->expires_date) . '</b>');
        $contents[] = array('text' => '' . TEXT_INFO_STATUS_CHANGE . ' ' . xtc_date_short($sInfo->date_status_change));
      }
      break;
  }
  if ( (xtc_not_null($heading)) && (xtc_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
}
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>