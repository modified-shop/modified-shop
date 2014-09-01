<?php
/* --------------------------------------------------------------
   $Id: new_attributes_include.php 2891 2012-05-18 18:54:35Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(new_attributes_functions); www.oscommerce.com
   (c) 2003 nextcommerce (new_attributes_include.php,v 1.11 2003/08/21); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contributions:
   New Attribute Manager v4b        Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  // include needed functions
  require_once(DIR_FS_INC .'xtc_get_tax_rate.inc.php');
  require_once(DIR_FS_INC .'xtc_get_tax_class_id.inc.php');
  require(DIR_FS_CATALOG.DIR_WS_CLASSES . 'xtcPrice.php');
  $xtPrice = new xtcPrice(DEFAULT_CURRENCY,$_SESSION['customers_status']['customers_status_id']);

  //NEW SORT SELECTION
  if (isset($_GET['option_order_by']) && $_GET['option_order_by']) {
   $option_order_by = $_GET['option_order_by'];
   $_POST['current_product_id'] = $_GET['current_product_id'];
  } else {
   $option_order_by = 'products_options_id';
  }
  $options = array();
  $options[] = (array ('id' => 'products_options_id', 'text' => TEXT_OPTION_ID));
  $options[] = (array ('id' => 'products_options_name', 'text' => TEXT_OPTION_NAME));
  $options[] = (array ('id' => 'products_options_sortorder', 'text' => TEXT_SORTORDER));
  $options_dropdown_order = xtc_draw_pull_down_menu('selected', $options, $option_order_by, 'onchange="go_option()" ') ."\n";

  //Anzahl Spalten
  $colspan = 9;

?>
  <script type="text/javascript"><!--
  function go_option() {
    if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
      location = "<?php echo xtc_href_link(FILENAME_NEW_ATTRIBUTES, 'option_page=' . ($_GET['option_page'] ? $_GET['option_page'] : 1)).'&current_product_id='. $_POST['current_product_id']; ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
    }
  }
  //--></script>
  <tr>
    <td class="pageHeading" colspan="<?php echo $colspan;?>"><?php echo $pageTitle; ?></td>
  </tr>
  <tr>
    <td class="main" colspan="<?php echo $colspan;?>">
      <?php echo SORT_ORDER; ?>
      <form name="option_order_by" action="<?php echo FILENAME_NEW_ATTRIBUTES ?>">
      <?php echo $options_dropdown_order; ?>
      </form>
    </td>
  </tr>
<form action="<?php echo FILENAME_NEW_ATTRIBUTES; ?>" method="post" name="SUBMIT_ATTRIBUTES" enctype="multipart/form-data"><input type="hidden" name="current_product_id" value="<?php echo $_POST['current_product_id']; ?>"><input type="hidden" name="action" value="change">
<?php
echo xtc_draw_hidden_field(xtc_session_name(), xtc_session_id());

//BOF - web28 - 2010-12-14 - NEW edit products attributes
echo '<input type="hidden" name="products_options_id" value="' . (isset($products_options_id) ? $products_options_id : '')  . '">';
echo '<input type="hidden" name="option_order_by" value="' . $option_order_by . '">';
$_POST['cpath'] = isset($_GET['cpath']) ? $_GET['cpath'] : (isset($_POST['cpath']) ? $_POST['cpath']: '') ;
if ($_POST['cpath'] != '') {
  $param ='cPath='. $_POST['cpath'] . '&current_product_id='. $_POST['current_product_id'];
  echo '<input type="hidden" name="cpath" value="' . $_POST['cpath'] . '">';
} else {
  $param = '';
}
//EOF - web28 - 2010-12-14 - NEW edit products attributes

  require(DIR_WS_MODULES . 'new_attributes_functions.php');

  // Lets get all of the possible options
  // NEW SORT SELECTION
  $query = "SELECT *
              FROM ".TABLE_PRODUCTS_OPTIONS."
             WHERE products_options_id LIKE '%'
               AND language_id = '" . $_SESSION['languages_id'] . "'
          ORDER BY ". $option_order_by;

  $result = xtc_db_query($query);
  $matches = xtc_db_num_rows($result);

  if ($matches) {
    while ($line = xtc_db_fetch_array($result)) {
      $current_product_option_name = $line['products_options_name'];
      $current_product_option_id = $line['products_options_id'];
      // Print the Option Name
      echo '<tr class="dataTableHeadingRow">'. PHP_EOL;
      echo '<td class="dataTableHeadingContent" style="width:150px"><strong>' . $current_product_option_name . '</strong></td>'. PHP_EOL;
      echo '<td class="dataTableHeadingContent" style="width:80px"><strong>'.SORT_ORDER.'</strong></td>'. PHP_EOL;
      echo '<td class="dataTableHeadingContent" style="width:150px"><strong>'.ATTR_MODEL.'</strong></td>'. PHP_EOL;
      echo '<td class="dataTableHeadingContent" style="width:150px"><strong>'.ATTR_EAN.'</strong></td>'. PHP_EOL;
      echo '<td class="dataTableHeadingContent" style="width:150px"><strong>'.ATTR_STOCK.'</strong></td>'. PHP_EOL;
      echo '<td colspan="2" class="dataTableHeadingContent"><strong>'.ATTR_WEIGHT.'</strong></td>'. PHP_EOL;
      //echo '<td class="dataTableHeadingContent"><strong>'.ATTR_PREFIXWEIGHT.'</strong></td>';
      echo '<td colspan="2" class="dataTableHeadingContent"><strong>'.ATTR_PRICE.'</strong></td>'. PHP_EOL;
      //echo '<td class="dataTableHeadingContent"><strong>'.ATTR_PREFIXPRICE.'</strong></td>';
      echo '</tr>'. PHP_EOL;

      // Find all of the Current Option's Available Values
      $query2 = "SELECT *
                   FROM ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS."
                  WHERE products_options_id = '" . $current_product_option_id . "'
               ORDER BY products_options_values_id ASC";
      $result2 = xtc_db_query($query2);
      $matches2 = xtc_db_num_rows($result2);

      if ($matches2) {
        $i = 0;
        while ($line = xtc_db_fetch_array($result2)) {
          $i++;
          $rowClass = rowClass($i);
          $current_value_id = $line['products_options_values_id'];
          $isSelected = checkAttribute($current_value_id, $_POST['current_product_id'], $current_product_option_id);
          $checked = ($isSelected) ? ' checked="checked"' : '';

          $query3 = "SELECT *
                       FROM ".TABLE_PRODUCTS_OPTIONS_VALUES."
                      WHERE products_options_values_id = '" . $current_value_id . "'
                        AND language_id = '" . $_SESSION['languages_id'] . "'";
          $result3 = xtc_db_query($query3);
          while($line = xtc_db_fetch_array($result3)) {
            $current_value_name = $line['products_options_values_name'];
            // Print the Current Value Name
            echo '<tr class="' . $rowClass . '">'. PHP_EOL;
            echo '<td class="main">'. PHP_EOL;
            echo '<input type="checkbox" name="optionValues[]" value="' . $current_value_id . '"' . $checked . '>&nbsp;&nbsp;' . $current_value_name . '&nbsp;&nbsp;'. PHP_EOL;
            echo '</td>'. PHP_EOL;
            echo '<td class="main" align="left"><input type="text" name="' . $current_value_id . '_sortorder" value="' . (isset($attr_array['sortorder'])?$attr_array['sortorder']:'') . '" size="8"></td>'. PHP_EOL;
            echo '<td class="main" align="left"><input type="text" name="' . $current_value_id . '_model" value="' . (isset($attr_array['attributes_model'])?$attr_array['attributes_model']:'') . '" size="15"></td>'. PHP_EOL;
            echo '<td class="main" align="left"><input type="text" name="' . $current_value_id . '_ean" value="' . (isset($attr_array['attributes_ean'])?$attr_array['attributes_ean']:'') . '" size="15"></td>'. PHP_EOL;
            echo '<td class="main" align="left"><input type="text" name="' . $current_value_id . '_stock" value="' . (isset($attr_array['attributes_stock'])?$attr_array['attributes_stock']:'') . '" size="10"></td>'. PHP_EOL;
            echo '<td style="width:35px;" class="main" align="left">'. PHP_EOL;
            echo '   <select name="' . $current_value_id . '_weight_prefix">'. PHP_EOL;
            echo '     <option value="+"' . (isset($attr_array['posCheck_weight'])?$attr_array['posCheck_weight']:'') . '>+</option>'. PHP_EOL;
            echo '     <option value="-"' . (isset($attr_array['negCheck_weight'])?$attr_array['negCheck_weight']:'') . '>-</option>'. PHP_EOL;
            echo '    </select>'. PHP_EOL;
            echo '  </td>'. PHP_EOL;
            echo '<td width="10%" class="main" align="left"><input type="text" name="' . $current_value_id . '_weight" value="' . (isset($attr_array['options_values_weight'])?$attr_array['options_values_weight']:'') . '" size="10"></td>'. PHP_EOL;
            // brutto Admin
            if (PRICE_IS_BRUTTO=='true'){
              $attribute_value_price_calculate = $xtPrice->xtcFormat(xtc_round((isset($attr_array['options_values_price'])?$attr_array['options_values_price']:0)*((100+(xtc_get_tax_rate(xtc_get_tax_class_id($_POST['current_product_id']))))/100),PRICE_PRECISION),false);
            } else {
              $attribute_value_price_calculate = xtc_round((isset($attr_array['options_values_price'])?$attr_array['options_values_price']:0),PRICE_PRECISION);
            }
            echo '<td style="width:35px;" class="main" align="left">'. PHP_EOL;
            echo '   <select name="' . $current_value_id . '_prefix">'. PHP_EOL;
            echo '     <option value="+"' . (isset($attr_array['posCheck'])?$attr_array['posCheck']:'') . '>+</option>'. PHP_EOL;
            echo '     <option value="-"' . (isset($attr_array['negCheck'])?$attr_array['negCheck']:'') . '>-</option>'. PHP_EOL;
            echo '    </select>'. PHP_EOL;
            echo '  </td>'. PHP_EOL;
            echo '<td style="white-space: nowrap;" class="main" align="left"><input type="text" name="' . $current_value_id . '_price" value="' . $attribute_value_price_calculate . '" size="10">'. PHP_EOL;
            // brutto Admin
            if (PRICE_IS_BRUTTO=='true'){
               echo '<span style="font-size:11px">'.TEXT_NETTO .'<strong>'.$xtPrice->xtcFormat(xtc_round((isset($attr_array['options_values_price'])?$attr_array['options_values_price']:0),PRICE_PRECISION),true).'</strong></span>  '. PHP_EOL;
            }
            echo '</td>'. PHP_EOL;
            echo '</tr>'. PHP_EOL;
            
            // Download function start
            if(strtoupper($current_product_option_name) == 'DOWNLOADS') {
              echo '<tr>'. PHP_EOL;
             // echo '<td colspan="2">File: <input type="file" name="' . $current_value_id . "_download_file"></td>';
              echo '<td class="main" colspan="'.$colspan .'" style="white-space: nowrap; background: #ccc; padding: 4px;">'.xtc_draw_pull_down_menu($current_value_id . '_download_file', xtc_getDownloads(), (isset($attr_dl_array['products_attributes_filename'])?$attr_dl_array['products_attributes_filename']:''), ''). PHP_EOL;
              echo '&nbsp;&nbsp;&nbsp;'.DL_COUNT.' <input type="text" name="' . $current_value_id . '_download_count" value="' . (isset($attr_dl_array['products_attributes_maxcount'])?$attr_dl_array['products_attributes_maxcount']:'') . '" size="6">'. PHP_EOL;
              echo '&nbsp;&nbsp;&nbsp;'.DL_EXPIRE.' <input type="text" name="' . $current_value_id . '_download_expire" value="' . (isset($attr_dl_array['products_attributes_maxdays'])?$attr_dl_array['products_attributes_maxdays']:'') . '" size="6"></td>'. PHP_EOL;
              echo '</tr>'. PHP_EOL;
            }
            // Download function end
          }
          if ($i == $matches2 ) $i = 0;
        }
      } else {
        echo '<tr>'. PHP_EOL;
        echo '<td class="main"><small>No values under this option.</small></td>'. PHP_EOL;
        echo '</tr>'. PHP_EOL;
      }
    }
  }
?>
  <tr>
    <td colspan="<?php echo $colspan;?>" class="main" style="border-top: 1px solid #a3a3a3"><br />
      <?php
      echo xtc_button(BUTTON_SAVE) . '&nbsp;';
      echo xtc_button_link(BUTTON_BACK, xtc_href_link(FILENAME_NEW_ATTRIBUTES, $param));
      ?>
    </td>
  </tr>
</form>