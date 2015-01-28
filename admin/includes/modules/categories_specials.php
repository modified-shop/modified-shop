<?php
/* --------------------------------------------------------------
   $Id: categories_specials.php 4476 2013-02-15 19:37:01Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:
   (c) 2006 Web4Business GmbH - Designs - Modules. www.web4business.ch
   --------------------------------------------------------------*/

defined("_VALID_XTC") or die("Direct access to this location isn't allowed.");

// include localized categories specials strings
require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/categories_specials.php');

if (PRICE_IS_BRUTTO == 'true') {
  $products_price_sp = xtc_round($pInfo->products_price * ((100 + xtc_get_tax_rate($pInfo->products_tax_class_id)) / 100), PRICE_PRECISION);
  $products_price_netto_sp = TEXT_NETTO.'<strong>'. xtc_round($pInfo->products_price,PRICE_PRECISION)  .'</strong>  ';
} else {
  $products_price_sp = xtc_round($pInfo->products_price, PRICE_PRECISION);
  $products_price_netto_sp = '';
}

// if editing an existing product
if (isset($_GET['pID'])) {
  $specials_query = "SELECT p.products_tax_class_id,
                            p.products_id,
                            p.products_price,
                            pd.products_name,
                            s.specials_id,
                            s.specials_quantity,
                            s.specials_new_products_price,
                            s.specials_date_added,
                            s.specials_last_modified,
                            s.start_date,
                            s.expires_date,
                            s.status
                       FROM " . TABLE_PRODUCTS . " p
                       JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                            ON p.products_id = pd.products_id
                               AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                       JOIN " . TABLE_SPECIALS . " s
                            ON p.products_id = s.products_id
                               AND s.products_id = '" . (int)$_GET['pID'] . "'";

  $specials_query = xtDBquery($specials_query);
  // if there exists already a special for this product
  if(xtc_db_num_rows($specials_query, true) > 0) {
    $special = xtc_db_fetch_array($specials_query, true);
    $sInfo = new objectInfo($special);
  }
}
$price = $sInfo->products_price;
$new_price = $sInfo->specials_new_products_price;
$new_price_netto = '';
if (PRICE_IS_BRUTTO=='true') {
  $price_netto = xtc_round($price,PRICE_PRECISION);
  if ($price > 0) {
    $new_price_netto = TEXT_NETTO.'<strong>'.xtc_round($new_price,PRICE_PRECISION).'</strong>';
  }
  $price = ($price*(xtc_get_tax_rate($sInfo->products_tax_class_id)+100)/100);
  $new_price = ($new_price*(xtc_get_tax_rate($sInfo->products_tax_class_id)+100)/100);
}
$price = xtc_round($price,PRICE_PRECISION);
$new_price = xtc_round($new_price,PRICE_PRECISION);

// build the expires date in the format YYYY-MM-DD
if(isset($_GET['pID']) and xtc_db_num_rows($specials_query, true) > 0 and $sInfo->expires_date != 0) {
  $expires_date = date('Y-m-d', strtotime($sInfo->expires_date));
} else {
  $expires_date = "";
}

// build the start date in the format YYYY-MM-DD
if(isset($_GET['pID']) and xtc_db_num_rows($specials_query, true) > 0 and $sInfo->start_date != 0) {
  $start_date = date('Y-m-d', strtotime($sInfo->start_date));
} else {
  $start_date = "";
}

// tell the storing script if to update existing special,
// or to insert a new one
echo xtc_draw_hidden_field('specials_action', ((isset($_GET['pID']) && xtc_db_num_rows($specials_query, true) > 0) ? "update" : "insert"));
echo xtc_draw_hidden_field('tax_rate', xtc_get_tax_rate($pInfo->products_tax_class_id));
echo xtc_draw_hidden_field('products_price_hidden', $pInfo->products_price);
if(isset($_GET['pID']) and xtc_db_num_rows($specials_query, true) > 0) {
  echo xtc_draw_hidden_field('specials_id', $sInfo->specials_id);
}

$arrow = 'arrow_down.gif';
if (isset($sInfo->specials_quantity) 
    || isset($sInfo->specials_new_products_price) 
    || isset($sInfo->specials_date_added) 
    || $sInfo->expires_date > 0
   ) 
{
  $arrow = 'arrow_down_red.gif';
  if ($sInfo->status == 1) {
    $arrow = 'arrow_down_green.gif';
  }
}
echo SPECIALS_TITLE;
?>
<img onMouseOver="javascript:this.style.cursor='pointer';" src="images/<?php echo $arrow; ?>" height="16" width="16" onclick="javascript:toggleBox('special');" style="vertical-align: middle;">
<div id="special" class="longDescription">
  <table class="tableInput">
    <?php if(!isset($_GET['pID'])) { ?>
    <tr>
      <td class="main"><?php echo TEXT_SPECIALS_NO_PID; ?></td>             
    </tr>
    <?php } else { ?>
    <?php /*
    <tr>
      <td class="main"><?php echo TEXT_PRODUCTS_PRICE; ?></td>
      <td class="main"><?php echo $products_price_sp . '&nbsp;' . $products_price_netto_sp; ?></td>
    </tr>
    */ ?>        
    <tr>
      <td class="main" style="width:300px;"><?php echo TEXT_SPECIALS_SPECIAL_PRICE; ?></td>
      <td class="main" style="width:250px;"><?php echo xtc_draw_input_field('specials_price', $new_price, 'style="width: 135px"') . draw_tooltip(TEXT_CATSPECIALS_SPECIAL_PRICE_TT) . (($new_price_netto != '') ? '<br/>'.$new_price_netto : '');?></td>
    </tr>
    <tr>
      <td class="main"><?php echo TEXT_SPECIALS_SPECIAL_QUANTITY; ?></td>
      <td class="main"><?php echo xtc_draw_input_field('specials_quantity', $sInfo->specials_quantity, 'style="width: 135px"') . draw_tooltip(TEXT_CATSPECIALS_SPECIAL_QUANTITY_TT);?></td>
    </tr>
    <?php if(isset($_GET['pID']) and xtc_db_num_rows($specials_query, true) > 0) { ?>
      <tr>
        <td class="main"><?php echo TEXT_INFO_DATE_ADDED; ?></td>
        <td class="main"><?php echo xtc_date_short($sInfo->specials_date_added); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo TEXT_INFO_LAST_MODIFIED; ?></td>
        <td class="main"><?php echo xtc_date_short($sInfo->specials_last_modified); ?></td>
      </tr>
    <?php } ?>
    <tr>
      <td class="main"><?php echo TEXT_SPECIALS_START_DATE; ?></td>
      <td class="main"><?php echo xtc_draw_input_field('specials_start', $start_date ,'id="DatepickerSpecialsStart" style="width: 135px"') . draw_tooltip(TEXT_CATSPECIALS_START_DATE_TT.SPECIALS_DATE_START_TT); ?></td>
    </tr>
    <tr>
      <td class="main"><?php echo TEXT_SPECIALS_EXPIRES_DATE; ?></td>
      <td class="main"><?php echo xtc_draw_input_field('specials_expires', $expires_date ,'id="DatepickerSpecials" style="width: 135px"') . draw_tooltip(TEXT_CATSPECIALS_EXPIRES_DATE_TT.SPECIALS_DATE_END_TT); ?></td>
    </tr>
    <?php if(isset($_GET['pID']) and xtc_db_num_rows($specials_query, true) > 0) { ?>
    <tr>
      <td class="main"><?php echo TEXT_EDIT_STATUS; ?></td>
      <td class="main"><?php echo xtc_draw_pull_down_menu('specials_status', $product_status_array, $sInfo->status, 'style="width: 160px"'); ?></td>
    </tr>
    <tr>
      <td class="main"><label for="input_specials_delete"><?php echo TEXT_INFO_HEADING_DELETE_SPECIALS; ?></label></td>
      <td class="main"><input type="checkbox" name="specials_delete" value="true" id="input_specials_delete"  onclick="if(this.checked==true)return confirm('<?php echo TEXT_INFO_DELETE_INTRO; ?>');"style="vertical-align:middle;"/></td>
    </tr>
    <?php } ?>
    <?php } ?>
  </table>
</div>
<?php
function saveSpecialsData($products_id) {
  // insert or update specials
  if (isset($_POST['specials_price']) && !empty($_POST['specials_price'])) {
    if (!isset($_POST['specials_quantity']) || empty($_POST['specials_quantity'])) {
      $_POST['specials_quantity'] = 0;
    }
    if (PRICE_IS_BRUTTO=='true' && substr($_POST['specials_price'], -1) != '%'){
      $_POST['specials_price'] = ($_POST['specials_price']/($_POST['tax_rate']+100)*100);  //web28 - 2010-07-27 - tax_rate from  hidden field
    }
    if (substr($_POST['specials_price'], -1) == '%')  {
      $_POST['specials_price'] = ($_POST['products_price_hidden'] - (($_POST['specials_price'] / 100) * $_POST['products_price_hidden'])); //web28 - 2010-07-27 - products_price_hidden from  hidden field
    }

    $expires_date = isset($_POST['specials_expires']) && !empty($_POST['specials_expires']) ? date('Y-m-d H:i:s', strtotime($_POST['specials_expires'].' 23:59:59')) : '';
    $start_date = isset($_POST['specials_start']) && !empty($_POST['specials_start']) ? date('Y-m-d H:i:s', strtotime($_POST['specials_start'].' 00:00:00')) : '';
    
    $sql_data_array = array('products_id' => $products_id,
                            'specials_quantity' => (int)$_POST['specials_quantity'],
                            'specials_new_products_price' => xtc_db_prepare_input($_POST['specials_price']),
                            'specials_date_added' => 'now()',
                            'specials_last_modified' => 'now()',
                            'start_date' => $start_date,
                            'expires_date' => $expires_date,
                            'status' => ((!isset($_POST['specials_status'])) ? (int)$_POST['specials_status'] : '1')
                            );
    
    if ($_POST['specials_action'] == 'insert') {
      unset($sql_data_array['specials_last_modified']);
      xtc_db_perform(TABLE_SPECIALS, $sql_data_array);
    } else {
      unset($sql_data_array['specials_date_added']);
      xtc_db_perform(TABLE_SPECIALS, $sql_data_array, 'update', "specials_id = '" . (int)$_POST['specials_id']  . "'" );    
    }
  } 
  
  // delete specials
  if(isset($_POST['specials_delete'])) {
    xtc_db_query("DELETE FROM " . TABLE_SPECIALS . " WHERE specials_id = '" . xtc_db_input($_POST['specials_id']) . "'");
  }
}
?>