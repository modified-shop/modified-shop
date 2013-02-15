<?php
/* --------------------------------------------------------------
   $Id: categories_specials.php 2360 2011-11-18 15:10:57Z franky-n-xtcm $

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

function showSpecialsBox() {
    global $pInfo; //web28 - 2010-07-27 - show products_price
    // include localized categories specials strings
    require_once(DIR_FS_LANGUAGES . $_SESSION['language'] . '/admin/categories_specials.php');

    //BOF web28 - 2010-07-27 - show products_price
    if (PRICE_IS_BRUTTO == 'true') {
      $products_price_sp = xtc_round($pInfo->products_price * ((100 + xtc_get_tax_rate($pInfo->products_tax_class_id)) / 100), PRICE_PRECISION);
      $products_price_netto_sp = TEXT_NETTO.'<strong>'.($pInfo->products_price*(xtc_get_tax_rate($sInfo->products_tax_class_id)+100)/100).'</strong>  ';
    } else {
      $products_price_sp = xtc_round($pInfo->products_price, PRICE_PRECISION);
      $products_price_netto_sp = '';
    }
    //EOF web28 - 2010-07-27 - show products_price

      // if editing an existing product
    if(isset($_GET['pID'])) {
      $specials_query = "SELECT p.products_tax_class_id,
                                p.products_id,
                                p.products_price,
                                pd.products_name,
                                s.specials_id,
                                s.specials_quantity,
                                s.specials_new_products_price,
                                s.specials_date_added,
                                s.specials_last_modified,
                                s.expires_date,
                                s.status
                           FROM " . TABLE_PRODUCTS . " p,
                                " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                " . TABLE_SPECIALS . " s
                          WHERE p.products_id = pd.products_id
                            AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                            AND p.products_id = s.products_id
                            AND s.products_id = '" . (int)$_GET['pID'] . "'";  //DokuMan - 2011-11-8 - added missing s.status from SP1b
      $specials_query = xtDBquery($specials_query);
      // if there exists already a special for this product
      if(xtc_db_num_rows($specials_query, true) > 0) {
        $special = xtc_db_fetch_array($specials_query, true);
        $sInfo = new objectInfo($special);
      }
    }
    $price=$sInfo->products_price;
    $new_price=$sInfo->specials_new_products_price;
    $new_price_netto = ''; //web28 - 2010-07-27 - show special_price netto
    if (PRICE_IS_BRUTTO=='true') {
      $price_netto=xtc_round($price,PRICE_PRECISION);
      if ($price > 0) {
        $new_price_netto= TEXT_NETTO.'<strong>'.xtc_round($new_price,PRICE_PRECISION).'</strong>'; //web28 - 2010-07-27 - show special_price netto
      }
      $price= ($price*(xtc_get_tax_rate($sInfo->products_tax_class_id)+100)/100);
      $new_price= ($new_price*(xtc_get_tax_rate($sInfo->products_tax_class_id)+100)/100);
    }
    $price=xtc_round($price,PRICE_PRECISION);
    $new_price=xtc_round($new_price,PRICE_PRECISION);
    // build the expires date in the format YYYY-MM-DD
    if(isset($_GET['pID']) and xtc_db_num_rows($specials_query, true) > 0 and $sInfo->expires_date != 0) {
      $expires_date = substr($sInfo->expires_date, 0, 4)."-".
              substr($sInfo->expires_date, 5, 2)."-".
              substr($sInfo->expires_date, 8, 2);
    } else {
      $expires_date = "";
    }

    if ($sInfo->status == 1) {
      $status = 'checked="checked"';
    } else {
      $status='';
    }

    // tell the storing script if to update existing special,
    // or to insert a new one
    echo xtc_draw_hidden_field('specials_action',
        ((isset($_GET['pID']) && xtc_db_num_rows($specials_query, true) > 0)
          ? "update"
          : "insert"
        )
      );
    echo xtc_draw_hidden_field('tax_rate', xtc_get_tax_rate($pInfo->products_tax_class_id)); //web28 - 2010-07-27 - add hidden field
    echo xtc_draw_hidden_field('products_price_hidden', $pInfo->products_price); //web28 - 2010-07-27 - FIX wrong specials price
    if(isset($_GET['pID']) and xtc_db_num_rows($specials_query, true) > 0) {
      echo xtc_draw_hidden_field('specials_id', $sInfo->specials_id);
    }
    ?>

<script type="text/javascript">
  var specialExpires = new ctlSpiffyCalendarBox("specialExpires", "new_product", "specials_expires","btnDate2","<?php echo $expires_date; ?>",2);
</script>
<script language="JavaScript" type="text/JavaScript">
  function showSpecial() {
    //alert(document.getElementById("special").style.display);
  if (document.getElementById("special").style.display =="none" || document.getElementById("special").style.display =="") {
    document.getElementById("special").style.display="block";
    document.getElementById('butSpecial').innerHTML= '<a href="JavaScript:showSpecial()" class="button">&laquo; Sonderangebot</a>';
  } else {
    document.getElementById("special").style.display="none";
    document.getElementById('butSpecial').innerHTML= '<a href="JavaScript:showSpecial()" class="button">Sonderangebot &raquo;</a>';
    }
  }
</script>
<style type='text/css'>#special{display: none;}</style>
<noscript>
<style type="text/css">#special{display: block;}</style>
</noscript>
  <div id="special">
    <div style="padding: 8px 0px 3px 5px;">
      <table border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td class="main">
            <strong><?php echo SPECIALS_TITLE; ?></strong>
          </td>
        </tr>
      </table>
    </div>
    <table bgcolor="f3f3f3" style="width: 100%; border: 1px solid; border-color: #aaaaaa; padding:5px;">      
      <tr>
        <td>
          <table width="100%" border="0" cellpadding="3" cellspacing="0" style="border: 0px dotted black;">
            <?php if(!isset($_GET['pID'])) { ?>
            <tr>
              <td class="main"><?php echo TEXT_SPECIALS_NO_PID; ?>&nbsp;</td>             
            </tr>
            <?php } else { ?>
            <tr>
              <td class="main"><?php echo TEXT_PRODUCTS_PRICE; ?>&nbsp;</td>
              <td class="main"><?php echo $products_price_sp; ?>&nbsp;&nbsp;&nbsp;<?php echo $products_price_netto_sp; ?></td>
              <td class="main">&nbsp;</td>
            </tr>            
            <tr>
              <td class="main" style="width:270px;">
                <?php echo TEXT_SPECIALS_SPECIAL_PRICE; ?>&nbsp;
              </td>
              <td class="main" style="width:250px;">
                <?php echo xtc_draw_input_field('specials_price', $new_price, 'style="width: 135px"'). '&nbsp;&nbsp;&nbsp;' . $new_price_netto;?>
              </td>
              <td class="main" style="width:340px;">
                &nbsp;
                <?php if(isset($_GET['pID']) and xtc_db_num_rows($specials_query, true) > 0) { ?>
                <input type="checkbox" name="specials_delete" value="true" id="input_specials_delete"  onclick="if(this.checked==true)return confirm('<?php echo TEXT_INFO_DELETE_INTRO; ?>');"style="vertical-align:middle;"/><label for="input_specials_delete">&nbsp;<?php echo TEXT_INFO_HEADING_DELETE_SPECIALS; ?></label>
                <?php } ?>
              </td>
            </tr>
            <tr>
              <td class="main">
                <?php echo TEXT_SPECIALS_SPECIAL_QUANTITY; ?>&nbsp;
              </td>
              <td class="main">
                <?php echo xtc_draw_input_field('specials_quantity', $sInfo->specials_quantity, 'style="width: 135px"');?>
              </td>
              <td class="main">
                &nbsp;
              </td>
            </tr>
            <?php if(isset($_GET['pID']) and xtc_db_num_rows($specials_query, true) > 0) { ?>
              <tr>
                <td class="main"><?php echo TEXT_INFO_DATE_ADDED; ?></td>
                <td class="main"><?php echo xtc_date_short($sInfo->specials_date_added); ?></td>
                <td class="main">&nbsp;</td>
              </tr>
              <tr>
                <td class="main"><?php echo TEXT_INFO_LAST_MODIFIED; ?></td>
                <td class="main"><?php echo xtc_date_short($sInfo->specials_last_modified); ?></td>
                <td class="main">&nbsp;</td>
              </tr>
            <?php } ?>
            <tr>
              <td class="main">
                <?php echo TEXT_SPECIALS_EXPIRES_DATE; ?>
              </td>
              <td class="main">
                <script type="text/javascript">specialExpires.writeControl(); specialExpires.dateFormat="yyyy-MM-dd";</script>
                <noscript>
                <?php echo  xtc_draw_input_field('specials_expires', $expires_date ,'style="width: 135px"'); ?>
                </noscript>
              </td>
              <td class="main">
                &nbsp;
                <?php if(isset($_GET['pID']) and xtc_db_num_rows($specials_query, true) > 0) { ?>
                <input type="checkbox" name="specials_status" value="1" id="input_specials_status"  style="vertical-align:middle;" <?php echo $status;?>/><label for="input_specials_status">&nbsp;<?php echo TEXT_EDIT_STATUS; ?></label>
                <?php } ?>
              </td>
            </tr>
            <tr>
              <td colspan="3" class="main" style="padding:3px; background: #D8D8D8;">
                <?php echo TEXT_SPECIALS_PRICE_TIP; ?>
              </td>
            </tr>
            <?php } ?>
          </table>
        </td>
      </tr>
    </table>
  </div>
<?php
}

function saveSpecialsData($products_id) {
    // decide whether to insert a new special,
    // or to update an existing one
  if($_POST['specials_action'] == "insert" && isset($_POST['specials_price']) && !empty($_POST['specials_price'])) {
    // insert a new special, code taken from /admin/specials.php, and modified
    if(!isset($_POST['specials_quantity']) or empty($_POST['specials_quantity'])) {
      $_POST['specials_quantity'] = 0;
    }
    if (PRICE_IS_BRUTTO=='true' && substr($_POST['specials_price'], -1) != '%'){
      $_POST['specials_price'] = ($_POST['specials_price']/($_POST['tax_rate']+100)*100);  //web28 - 2010-07-27 - tax_rate from  hidden field
    }
    if (substr($_POST['specials_price'], -1) == '%')  {
      $_POST['specials_price'] = ($_POST['products_price_hidden'] - (($_POST['specials_price'] / 100) * $_POST['products_price_hidden'])); //web28 - 2010-07-27 - products_price_hidden from  hidden field
    }
    $expires_date = '';
    if ($_POST['specials_expires']) {
      $expires_date = str_replace("-", "", $_POST['specials_expires']);
    }
    
    $sql_data_array = array('products_id' => $products_id,
                            'specials_quantity' => (int)$_POST['specials_quantity'],
                            'specials_new_products_price' => xtc_db_prepare_input($_POST['specials_price']),
                            'specials_date_added' => 'now()',
                            'expires_date' => $expires_date,
                            'status' => '1'
                            );
    xtc_db_perform (TABLE_SPECIALS,$sql_data_array);

  } elseif($_POST['specials_action'] == "update" && isset($_POST['specials_price']) && isset($_POST['specials_quantity'])) {
    // update the existing special for this product, code taken from /admin/specials.php, and modified
    if (PRICE_IS_BRUTTO=='true' && substr($_POST['specials_price'], -1) != '%'){
      $sql="SELECT tr.tax_rate
              FROM " . TABLE_TAX_RATES . " tr,
                   " . TABLE_PRODUCTS . " p
             WHERE tr.tax_class_id = p. products_tax_class_id
               AND p.products_id = '". $products_id . "' ";
      $tax_query = xtc_db_query($sql);
      $tax = xtc_db_fetch_array($tax_query);
      $_POST['specials_price'] = ($_POST['specials_price']/($_POST['tax_rate']+100)*100); //web28 - 2010-07-27 - tax_rate from  hidden field
    }
    if (substr($_POST['specials_price'], -1) == '%')  {
      $_POST['specials_price'] = ($_POST['products_price_hidden'] - (($_POST['specials_price'] / 100) * $_POST['products_price_hidden'])); //web28 - 2010-07-27 - products_price_hidden from  hidden field
    }

    $expires_date = 'NULL';
    if ($_POST['specials_expires'] && $_POST['specials_status'] == 1) { //DokuMan - 2011-11-8 - from SP1b
      $expires_date = str_replace("-", "", $_POST['specials_expires']);
    }
    
    $sql_data_array = array(
                      'specials_quantity' => (int)$_POST['specials_quantity'],
                      'specials_new_products_price' => xtc_db_prepare_input($_POST['specials_price']),
                      'specials_date_added' => 'now()',
                      'expires_date' => $expires_date,
                      'status' => (int)$_POST['specials_status']
                      );

    //$sql_data_array['specials_attribute'] = (int)$_POST['specials_attribute'];

    xtc_db_perform (TABLE_SPECIALS,$sql_data_array, 'update', "specials_id = '" . xtc_db_input($_POST['specials_id'])  . "'" );    
  }
  if(isset($_POST['specials_delete'])) {
    // delete existing special for this product, code taken from /admin/specials.php, and modified
    xtc_db_query("DELETE FROM " . TABLE_SPECIALS . " WHERE specials_id = '" . xtc_db_input($_POST['specials_id']) . "'");
  }
}
?>