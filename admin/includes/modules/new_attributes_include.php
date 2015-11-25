<?php
/* --------------------------------------------------------------
   $Id: new_attributes_include.php 6142 2013-12-05 12:24:44Z web28 $

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

  define('ATTR_EQ_PREFIX', (defined('MODULE_PRICE_WEIGHT_PREFIX_STATUS') && MODULE_PRICE_WEIGHT_PREFIX_STATUS == 'true'  ? true : false));
  
  // include needed functions
  require_once(DIR_FS_INC .'xtc_get_tax_rate.inc.php');
  require_once(DIR_FS_INC .'xtc_get_tax_class_id.inc.php');
  require(DIR_FS_CATALOG.DIR_WS_CLASSES . 'xtcPrice.php');
  $xtPrice = new xtcPrice(DEFAULT_CURRENCY,$_SESSION['customers_status']['customers_status_id']);

  $noStylingClass = '';
  $noStyling = '';
  if (!defined('NEW_ATTRIBUTES_STYLING') || (defined('NEW_ATTRIBUTES_STYLING') && NEW_ATTRIBUTES_STYLING != 'true')) {
    $noStylingClass = ' class="noStyling" ';
    $noStyling = ' noStyling';
  }

  //NEW SORT SELECTION
  if (isset($_GET['option_order_by']) && $_GET['option_order_by']) {
    $option_order_by = $_GET['option_order_by'];
    $_POST['current_product_id'] = (int)$_GET['current_product_id'];
  } else {
    $option_order_by = 'products_options_sortorder,products_options_id';
  }
  $options = array();
  $options[] = (array ('id' => 'products_options_sortorder', 'text' => TEXT_SORTORDER));
  $options[] = (array ('id' => 'products_options_id', 'text' => TEXT_OPTION_ID));
  $options[] = (array ('id' => 'products_options_name', 'text' => TEXT_OPTION_NAME));
  $options_dropdown_order = xtc_draw_pull_down_menu('selected', $options, $option_order_by, $noStylingClass.'onchange="go_option()" ') ."\n";

  $prefix_array = array(
    array('id' => '+', 'text' => '&nbsp;+&nbsp;'),
    array('id' => '-', 'text' => '&nbsp;-&nbsp;')
  );
  if (ATTR_EQ_PREFIX === true) {
    $prefix_array[] = array('id' => '=', 'text' => '&nbsp;=&nbsp;');
  }

  //Anzahl Spalten
  $colspan = 8;

?>
  <script type="text/javascript">
  <!--
  function go_option() {
    if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
      location = "<?php echo xtc_href_link(FILENAME_NEW_ATTRIBUTES, 'option_page=' . (isset($_GET['option_page']) ? (int)$_GET['option_page'] : 1)).'&current_product_id='. (int)$_POST['current_product_id'].$iframe; ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
    }
  }
  //-->
  </script>
  <tr>
     <td>
     
    <div class="pageHeading pdg2"><?php echo $pageTitle; ?></div>
    <div class="main pdg2">
      <?php echo SORT_ORDER;
      echo xtc_draw_form('option_order_by', FILENAME_NEW_ATTRIBUTES, '', 'post');
      echo $options_dropdown_order; 
      ?>
      </form>
    </div>

<?php echo xtc_draw_form('SUBMIT_ATTRIBUTES', FILENAME_NEW_ATTRIBUTES . str_replace('&','?',$iframe), '', 'post', 'id="SUBMIT_ATTRIBUTES" enctype="multipart/form-data"'); ?>
<input type="hidden" name="current_product_id" value="<?php echo (int)$_POST['current_product_id']; ?>">
<input type="hidden" name="action" value="change">
<?php
//BOF - web28 - 2010-12-14 - NEW edit products attributes
echo '<input type="hidden" name="products_options_id" value="' . (isset($products_options_id) ? $products_options_id : '')  . '">';
echo '<input type="hidden" name="option_order_by" value="' . $option_order_by . '">';
$_POST['cpath'] = isset($_GET['cpath']) ? $_GET['cpath'] : (isset($_POST['cpath']) ? $_POST['cpath']: '') ;
if ($_POST['cpath'] != '') {
  $param ='cPath='. $_POST['cpath'] . '&current_product_id='. (int)$_POST['current_product_id'] . $oldaction.$oldpage ;
  echo '<input type="hidden" name="cpath" value="' . $_POST['cpath'] . '">';
  echo '<input type="hidden" name="oldaction" value="' . str_replace('&oldaction=','',$oldaction) . '">';
  echo '<input type="hidden" name="page" value="' . str_replace('&page=','',$oldpage) . '">';
} else {
  $param = '';
}
//EOF - web28 - 2010-12-14 - NEW edit products attributes
?>


<div class="main" style="margin:10px 0;">
    <a class="button button_save" style="display:none;"><?php echo ATTR_SAVE_ACTIVE;?></a>
    <?php
       echo xtc_button(BUTTON_SAVE,'submit','name="button_submit"');
       if (!isset($_GET['iframe'])) {
         echo '&emsp;'. xtc_button_link(BUTTON_BACK, xtc_href_link(FILENAME_NEW_ATTRIBUTES, $param));
       }
   ?>
</div>

<div id="attributes">
<?php

  require(DIR_WS_MODULES . 'new_attributes_functions.php');
  
  // BOF VPE_MOD
  $attr_get_vpe = xtc_db_query("SELECT products_vpe_id, 
                                       products_vpe_name 
                                  FROM products_vpe 
                                 WHERE language_id = '" . (int)$_SESSION['languages_id'] . "'
                              ");
  $attr_vpe_data = array(array('id' => 0, 'text' => ''));
  while($attr_vpe = xtc_db_fetch_array($attr_get_vpe)) {
    $attr_vpe_data[] = array('id' => $attr_vpe['products_vpe_id'], 'text' => $attr_vpe['products_vpe_name']);
  }
  // EOF VPE_MOD

  // Lets get all of the possible options
  // NEW SORT SELECTION
  $query = "SELECT *
              FROM ".TABLE_PRODUCTS_OPTIONS."
             WHERE products_options_id LIKE '%'
               AND language_id = '" . (int)$_SESSION['languages_id'] . "'
          ORDER BY ". $option_order_by;

  $result = xtc_db_query($query);
  $matches = xtc_db_num_rows($result);
  
  $products_tax_rate = xtc_get_tax_rate(xtc_get_tax_class_id($_POST['current_product_id']));
  $countOptions = $countValues = 0;

  if ($matches) {
    while ($line = xtc_db_fetch_array($result)) {
      $countOptions++;
      $current_product_option_name = $line['products_options_name'];
      $current_product_option_id = $line['products_options_id'];
      // Print the Option Name
      $output = '<div style="margin-bottom:20px;clear:both;">';
      $output .= '<table id="attrtable-'.$current_product_option_id.'" class="attributes collapse">'. PHP_EOL;
      $output .= '<thead>'. PHP_EOL;
      $output .= '<tr id="oid-' . $current_product_option_id . '" class="dataTableHeadingRow">'. PHP_EOL;
      $output .= '<th class="dataTableHeadingContent txta-l nobr" style="width:150px;">'.xtc_draw_checkbox_field('set_'.$current_product_option_id, $current_product_option_id, false, '', 'class="select_all'.$noStyling.'"' .' disabled="disabled"').'&nbsp;&nbsp;<strong style="padding-right:10px;">' . $current_product_option_name . '</strong></th>'. PHP_EOL;
      $output .= '<th class="dataTableHeadingContent" style="width:95px"><strong>'.SORT_ORDER.'</strong></th>'. PHP_EOL;
      $output .= '<th class="dataTableHeadingContent" style="width:135px"><strong>'.ATTR_MODEL.'</strong></th>'. PHP_EOL;
      $output .= '<th class="dataTableHeadingContent" style="width:135px"><strong>'.ATTR_EAN.'</strong></th>'. PHP_EOL;
      $output .= '<th class="dataTableHeadingContent" style="width:100px"><strong>'.ATTR_STOCK.'</strong></th>'. PHP_EOL;
      $output .= '<th class="dataTableHeadingContent" style="width:180px"><strong>'.ATTR_VPE.'</strong></th>'. PHP_EOL;
      $output .= '<th class="dataTableHeadingContent" style="min-width:135px;"><strong>'.ATTR_WEIGHT.'&nbsp;&nbsp;&nbsp;</strong></th>'. PHP_EOL;
      $output .= '<th class="dataTableHeadingContent" style="min-width:135px;"><strong>'.ATTR_PRICE.'&nbsp;&nbsp;&nbsp;</strong></th>'. PHP_EOL;
      $output .= '</tr>'. PHP_EOL;
      $output .= '</thead>'. PHP_EOL;
      
      $output .= '<tbody>'. PHP_EOL;

      // Find all of the Current Option's Available Values
      //$values_order_by = 'products_options_values_id';
      $values_order_by = 'products_options_values_name';
      $sortv = 'ASC';
      $query2 = xtc_db_query(
            "SELECT a.products_options_id, 
                    a.products_options_values_id, 
                    b.products_options_values_name
               FROM ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." a 
          LEFT JOIN ".TABLE_PRODUCTS_OPTIONS_VALUES." b 
                    ON a.products_options_values_id = b.products_options_values_id
              WHERE a.products_options_id = '" . (int)$current_product_option_id . "' 
                AND b.language_id = '" . (int)$_SESSION['languages_id'] . "'
           ORDER BY " . $values_order_by . " " . $sortv
        );
        
      $matches2 = xtc_db_num_rows($query2);
      
      $isChecked = false;


      if ($matches2) {
        $i = 0;
        while ($line = xtc_db_fetch_array($query2)) {
          $countValues++;
          $i++;
          $rowClass = rowClass($i) . ' oid-'.$current_product_option_id;
          $current_value_id = $line['products_options_values_id'];
          $isSelected = checkAttribute($current_value_id, $_POST['current_product_id'], $current_product_option_id);
          $checked = ($isSelected) ? true : false;
          $disable = ($checked === false) ? ' disabled="true" ' : ' ';
          
          if ($isSelected) {
            $isChecked = true;
          }

          $current_value_name = $line['products_options_values_name'];
          
          $attr_array['options_values_price'] = (isset($attr_array['options_values_price']) ? $attr_array['options_values_price'] : 0);
          
          // brutto Admin
          if (PRICE_IS_BRUTTO=='true') {
            $attribute_value_price_calculate = xtc_round($attr_array['options_values_price'] * ((100 + $products_tax_rate) / 100),PRICE_PRECISION);
            // brutto Admin Price netto
            $attribute_value_price_calculate_netto = '<span style="font-size:11px">&nbsp;'.TEXT_NETTO .'<strong>'. xtc_round($attr_array['options_values_price'],PRICE_PRECISION).'</strong></span>  ';
          } else {
            $attribute_value_price_calculate = xtc_round($attr_array['options_values_price'],PRICE_PRECISION);
            $attribute_value_price_calculate_netto = '';
          }
  
          // Print the Current Value Name
          $output .= '<tr class="' . $rowClass . '">'. PHP_EOL;
          //1st col
          $output .= '<td class="main nobr">'. PHP_EOL;
          $output .= xtc_draw_checkbox_field('optionValues[]', $current_value_id, $checked, '', 'class="cbx_optval cb check_'.$current_product_option_id.$noStyling.'"').'&nbsp;&nbsp;' . $current_value_name . '&nbsp;&nbsp;'. PHP_EOL;
          $output .= '</td>'. PHP_EOL;
          
          $output .= '<td class="main nobr"><input'.$disable.'type="text" name="' . $current_value_id . '_sortorder" value="' . (isset($attr_array['sortorder'])?$attr_array['sortorder']:'') . '" size="8"></td>'. PHP_EOL;
          $output .= '<td class="main nobr"><input'.$disable.'type="text" name="' . $current_value_id . '_model" value="' . (isset($attr_array['attributes_model'])?$attr_array['attributes_model']:'') . '" size="15"></td>'. PHP_EOL;
          $output .= '<td class="main nobr"><input'.$disable.'type="text" name="' . $current_value_id . '_ean" value="' . (isset($attr_array['attributes_ean'])?$attr_array['attributes_ean']:'') . '" size="15"></td>'. PHP_EOL;
          $output .= '<td class="main nobr"><input'.$disable.'type="text" name="' . $current_value_id . '_stock" value="' . (isset($attr_array['attributes_stock'])?$attr_array['attributes_stock']:'') . '" size="10"></td>'. PHP_EOL;
                    
          //VPE
          $output .= '<td class="main nobr" align="left"><input'.$disable.'type="text" name="' . $current_value_id . '_vpe_value" value="' . (isset($attr_array['attributes_vpe_value'])? (double)$attr_array['attributes_vpe_value']:'') . '" size="10"> '. PHP_EOL;
          $output .=  xtc_draw_pull_down_menu($current_value_id . '_vpe_id',$attr_vpe_data,(isset($attr_array['attributes_vpe_id'])?$attr_array['attributes_vpe_id']:''), $noStylingClass . $disable). PHP_EOL;
          $output .=  '</td>'. PHP_EOL;
          
          //Weight
          $output .= '<td class="main nobr">'. PHP_EOL;
          $output .= xtc_draw_pull_down_menu($current_value_id . '_weight_prefix', $prefix_array, (isset($attr_array['weight_prefix'])?$attr_array['weight_prefix']:''), $noStylingClass . $disable). PHP_EOL;
          $output .= '<input'.$disable.'type="text" name="' . $current_value_id . '_weight" value="' . (isset($attr_array['options_values_weight']) ? $attr_array['options_values_weight'] : '') . '" size="10">'. PHP_EOL;
          $output .= '</td>'. PHP_EOL;
              
          ///Price
          $output .= '<td class="main nobr">'. PHP_EOL;
          $output .= xtc_draw_pull_down_menu($current_value_id . '_prefix', $prefix_array, (isset($attr_array['price_prefix'])?$attr_array['price_prefix']:''), $noStylingClass . $disable). PHP_EOL;
          $output .= '<input'.$disable.'type="text" name="' . $current_value_id . '_price" value="' . $attribute_value_price_calculate . '" size="10">'. $attribute_value_price_calculate_netto. PHP_EOL;
          $output .= '</td>'. PHP_EOL;
          $output .= '</tr>'. PHP_EOL;
          
          // Download function start
          if (strtoupper($current_product_option_name) == 'DOWNLOADS') {
            $output .= '<tr class="downloads oid-'.$current_product_option_id.'">'. PHP_EOL;
           // $output .= '<td colspan="2">File: <input type="file" name="' . $current_value_id . "_download_file"></td>';
            $output .= '<td class="main">&nbsp;</td>';
            $output .= '<td class="main" colspan="'.(int)($colspan - 1) .'" style="white-space: nowrap; background: #ccc; padding: 4px;">'.xtc_draw_pull_down_menu($current_value_id . '_download_file', xtc_getDownloads(), (isset($attr_dl_array['products_attributes_filename'])?$attr_dl_array['products_attributes_filename']:''), $noStylingClass . $disable). PHP_EOL;
            $output .= '&nbsp;&nbsp;&nbsp;'.DL_COUNT.' <input'.$disable.'type="text" name="' . $current_value_id . '_download_count" value="' . (isset($attr_dl_array['products_attributes_maxcount'])?$attr_dl_array['products_attributes_maxcount']:'') . '" size="6">'. PHP_EOL;
            $output .= '&nbsp;&nbsp;&nbsp;'.DL_EXPIRE.' <input'.$disable.'type="text" name="' . $current_value_id . '_download_expire" value="' . (isset($attr_dl_array['products_attributes_maxdays'])?$attr_dl_array['products_attributes_maxdays']:'') . '" size="6"></td>'. PHP_EOL;
            $output .= '</tr>'. PHP_EOL;
          }
          // Download function end

          if ($i == $matches2 ) $i = 0;
        } //while query2
      } else {
        $output .= '<tr>'. PHP_EOL;
        $output .= '<td class="main"><small>No values under this option.</small></td>'. PHP_EOL;
        $output .= '</tr>'. PHP_EOL;
      }
      if ($isChecked) {
        $output = str_replace('dataTableHeadingContent','dataTableHeadingContent attr-chk',$output);
      }
      $output .= '</tbody>'. PHP_EOL;
      $output .= '</table></div>'. PHP_EOL;
      echo $output;
    }
  }
  
  echo '<div class="pdg2"><small>Options: ' . $countOptions . ' | Values: ' . $countValues . '</small></div>';
?>
  
</div>

<div class="main" style="margin:10px 0;">
    <a class="button button_save" style="display:none;"><?php echo ATTR_SAVE_ACTIVE;?></a>
    <?php
    echo xtc_button(BUTTON_SAVE,'submit','name="button_submit"');
    if (!isset($_GET['iframe'])) {
      echo '&emsp;' . xtc_button_link(BUTTON_BACK, xtc_href_link(FILENAME_NEW_ATTRIBUTES, $param));
    }
    echo isset($_GET['options_id']) ? '<input type="hidden" name="get_options_id" value="'.$_GET['options_id'].'">'. PHP_EOL : '';
    ?>
</div>

</form>
</td>
</tr>