<?php
/* --------------------------------------------------------------
   $Id: products_attributes_values.php 3235 2012-07-16 14:08:23Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(new_attributes_change); www.oscommerce.com
   (c) 2003	 nextcommerce (new_attributes_change.php,v 1.8 2003/08/14); www.nextcommerce.org
   (c) 2006  xt-commerce(new_attributes_select.php 901 2005-04-29); www.xt-commerce.com

   Released under the GNU General Public License

   products_attribtues_values (c) www.rpa-com.de
   --------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  //BOF Auswahldropdown
  $options_dropdown_select = '';
  $options = xtc_db_query("-- products_attributes.php
                          SELECT products_options_id,
                                 products_options_name
                            FROM " . TABLE_PRODUCTS_OPTIONS . "
                           WHERE language_id = '" . (int)$_SESSION['languages_id'] . "'
                        ORDER BY products_options_name");
  while ($options_values = xtc_db_fetch_array($options)) {
    $selected2 = isset($_GET['option_id']) && $_GET['option_id'] == $options_values['products_options_id'] ? 'selected="selected"' : '';
    $options_dropdown_select .= '<option name="' . $options_values['products_options_name'] . '" value="' . $options_values['products_options_id'] . '"' . $selected2 . '>' . $options_values['products_options_name'] . ' ID-' . $options_values['products_options_id']. '</option>';
  }
  //BOF Auswahldropdown

  if ($_GET['action'] != 'delete_option_value') {

    $from = " FROM ".TABLE_PRODUCTS_OPTIONS." po ," . TABLE_PRODUCTS_OPTIONS_VALUES." pov ";
    $and = " AND pov2po.products_options_id = po.products_options_id ";

    if (isset ($_GET['search_optionsname'])){
      $and .= " AND (po.products_options_name LIKE '%".$_GET['search_optionsname']."%' or pov.products_options_values_name LIKE '%".$_GET['search_optionsname']."%') ";
    }

    $values = "-- products_attributes.php
              SELECT DISTINCT
                     pov.products_options_values_id,
                     pov.products_options_values_name,
                     po.products_options_name,
                     pov2po.products_options_id
                     ".$from."
           LEFT JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po ON pov.products_options_values_id = pov2po.products_options_values_id
               WHERE pov.language_id = '" . (int)$_SESSION['languages_id'] . "'
                 AND po.language_id = '".(int)$_SESSION['languages_id']."'
                     ".$and."
            ORDER BY pov.products_options_values_id";

    //BOF Seitenschaltung
    if (defined('MAX_ROW_LISTS_ATTR_VALUES')) {
      $per_page = (int)MAX_ROW_LISTS_ATTR_VALUES;
    } else {
      $per_page = (int)MAX_ROW_LISTS_OPTIONS; //aus Sprachdatei (veraltet)
    }

    if (!$_GET['value_page']) {
      $_GET['value_page'] = 1;
    }
    $prev_value_page = $_GET['value_page'] - 1;
    $next_value_page = $_GET['value_page'] + 1;

    $value_query = xtc_db_query($values);

    $value_page_start = ($per_page * $_GET['value_page']) - $per_page;
    $num_rows = xtc_db_num_rows($value_query);

    if ($num_rows <= $per_page) {
      $num_pages = 1;
    } else if (($num_rows % $per_page) == 0) {
      $num_pages = ($num_rows / $per_page);
    } else {
      $num_pages = ($num_rows / $per_page) + 1;
    }
    $num_pages = (int) $num_pages;

    $values = $values . " LIMIT $value_page_start, $per_page";

    $value_pages = '';
    // Previous
    if ($prev_value_page) {
      $value_pages .= '<a href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_order_by=' . $option_order_by . '&value_page=' . $prev_value_page.'&search_optionsname='.$_GET['search_optionsname'].$option_filter) . '"> &lt;&lt; </a> | ';
    }

    for ($i = 1; $i <= $num_pages; $i++) {
      if ($i != $_GET['value_page']) {
        $value_pages .= '<a href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_order_by=' . $option_order_by . '&value_page=' . $i.'&search_optionsname='.$_GET['search_optionsname'].$option_filter) . '">' . $i . '</a> | ';
      } else {
        $value_pages .= '<strong><font color=red>' . $i . '</font></strong> | ';
      }
    }

    // Next
    if ($_GET['value_page'] != $num_pages) {
      $value_pages .= '<a href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_order_by=' . $option_order_by . '&value_page=' . $next_value_page.'&search_optionsname='.$_GET['search_optionsname'].$option_filter) . '"> &gt;&gt;</a> ';
    }
    //EOF Seitenschaltung
  }

  /*#############################*/

?>
<!-- #################### VALUE ################## //-->
<?php
 // ############ BOF DELETE ############ //
if ($_GET['action'] == 'delete_option_value') {
  $values = xtc_db_query("-- products_attributes.php
                          SELECT products_options_values_id,
                                 products_options_values_name
                            FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                           WHERE products_options_values_id = '" . $_GET['value_id'] . "'
                             AND language_id = '" . $_SESSION['languages_id'] . "'"
                        );
  $values_values = xtc_db_fetch_array($values);
?>
                <table border="0" cellspacing="0" cellpadding="2" class="option-values-table">
                  <tr>
                    <td colspan="3" class="pageHeading">&nbsp;<?php echo $values_values['products_options_values_name']; ?>&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="3"><?php echo xtc_black_line(); ?></td>
                  </tr>
<?php
$products = xtc_db_query("-- products_attributes.php
                          SELECT p.products_id,
                                 pd.products_name,
                                 po.products_options_name
                            FROM " . TABLE_PRODUCTS . " p,
                                 " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                                 " . TABLE_PRODUCTS_OPTIONS . " po,
                                 " . TABLE_PRODUCTS_DESCRIPTION . " pd
                           WHERE pd.products_id = p.products_id
                             AND pd.language_id = '" . $_SESSION['languages_id'] . "'
                             AND po.language_id = '" . $_SESSION['languages_id'] . "'
                             AND pa.products_id = p.products_id
                             AND pa.options_values_id='" . $_GET['value_id'] . "'
                             AND po.products_options_id = pa.options_id
                        ORDER BY pd.products_name"
                        );
if (xtc_db_num_rows($products)) {
 //Produkt zugeordnet - Warnung - Optionswert kann nicht gelöscht werden
?>
                        <tr class="dataTableHeadingRow">
                          <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
                          <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_PRODUCT; ?>&nbsp;</td>
                          <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</td>
                        </tr>
                        <tr>
                          <td colspan="3"><?php echo xtc_black_line(); ?></td>
                        </tr>
  <?php
  while ($products_values = xtc_db_fetch_array($products)) {
  $rows++;
  ?>
                          <tr class="<?php echo ($rows % 2) ? 'attributes-even' : 'attributes-odd'; ?>">
                            <td align="center" class="smallText">&nbsp;<?php echo $products_values['products_id']; ?>&nbsp;</td>
                            <td class="smallText">&nbsp;<?php echo $products_values['products_name']; ?>&nbsp;</td>
                            <td class="smallText">&nbsp;<?php echo $products_values['products_options_name']; ?>&nbsp;</td>
                          </tr>
  <?php
  }
  ?>
                        <tr>
                          <td colspan="3"><?php echo xtc_black_line(); ?></td>
                        </tr>
                        <tr>
                          <td class="main" colspan="3>" style="background-color: #d4d4d4;">
                            <div style="margin:10px 0";>
                              <?php echo TEXT_WARNING_OF_DELETE; ?>&nbsp;&nbsp;&nbsp;
                              <?php  //BOF - webkiste - auf der selben Seite bleiben
                              echo xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'value_page=' . $_GET['value_page'], 'NONSSL'));
                              //EOF - webkiste - auf der selben Seite bleiben ?>
                            <div>
                          </td>
                        </tr>
                        <tr>
                          <td colspan="3"><?php echo xtc_black_line(); ?></td>
                        </tr>
<?php
} else {
//Produkt nicht zugeordnet - Ok - Optionswert kann gelöscht werden
?>
                        <tr>
                          <td class="main" colspan="3>" style="background-color: #d4d4d4;">
                            <div style="margin:10px 0";>
                              <?php echo TEXT_OK_TO_DELETE; ?>
                              <?php //BOF - webkiste - auf der selben Seite bleiben
                              echo xtc_button_link(BUTTON_DELETE, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_value&value_id=' . $_GET['value_id'] . '&value_page=' . $_GET['value_page'] , 'NONSSL'));
                              echo xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_page=' . $option_page . '&value_page=' . $_GET['value_page'] . '&attribute_page=' . $attribute_page , 'NONSSL'));
                              //EOF - webkiste - auf der selben Seite bleiben ?>
                            </div>
                          </td>
                        </tr>
                        <tr>
                          <td colspan="<3"><?php echo xtc_black_line(); ?></td>
                        </tr>
<?php
    }
?>
                      </table>

<?php
// ############ EOF DELETE ############ //
  } else {
    $colspan = 4;
// ############ BOF DEFAULT ############ //
?>
                <table border="0" cellspacing="0" cellpadding="2" class="option-values-table">
                  <tr>
                    <td colspan="<?php echo $colspan;?>" class="pageHeading">&nbsp;<?php echo HEADING_TITLE_VAL; ?>&nbsp;&nbsp;&nbsp;
                      <form name="search" action="<?php echo FILENAME_PRODUCTS_ATTRIBUTES; ?>" method="GET">
                        <span  class="main"><?php  echo  TEXT_SEARCH;  ?></span> <input type="text" name="search_optionsname" size="20" value="<?php echo $_GET['search_optionsname']; ?>">
                        <input name="<?php echo xtc_session_name(); ?>" type="hidden" value="<?php echo xtc_session_id(); ?>" />
                      </form
                    </td>
                  </tr>
                  <tr>
                    <td colspan="<?php echo $colspan;?>" class="smallText"><?php echo $value_pages;?></td>
                  </tr>
                  <tr>
                    <td colspan="<?php echo $colspan;?>"><?php echo xtc_black_line(); ?></td>
                  </tr>
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
                    <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</td>
                    <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_VALUE; ?>&nbsp;</td>
                    <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                  </tr>
                  <tr>
                    <td colspan="<?php echo $colspan;?>"><?php echo xtc_black_line(); ?></td>
                  </tr>
<?php
// ############ BOF NEW ENTRY ############ //
  if ($_GET['action'] != 'update_option_value') {
    $max_values_id_query = xtc_db_query("-- products_attributes.php
                                          SELECT max(products_options_values_id) + 1 as next_id FROM " . TABLE_PRODUCTS_OPTIONS_VALUES
                                        );
    $max_values_id_values = xtc_db_fetch_array($max_values_id_query);
    $next_id = $max_values_id_values['next_id'];
    if ($next_id < 1) $next_id = 1;

    $inputs = '';
    for ($i = 0, $n = sizeof($languages);$i < $n;$i++) {
      $lang_img = '<span style="float:left; padding-top:2px;">'. xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'].'/admin/images/'.$languages[$i]['image'], $languages[$i]['name']) . '</span>';
      $inputs.= $lang_img . '&nbsp;<input type="text" name="value_name[' . $languages[$i]['id'] . ']" style="width:200px;">&nbsp;<br />';
    }
    ?>
                  <tr>
                    <td colspan="<?php echo $colspan;?>"><?php echo xtc_black_line(); ?></td>
                  </tr>
                  <form name="values" action="<?php echo xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=add_product_option_values&value_page=' . $_GET['value_page'].$option_filter, 'NONSSL');?>" method="post">
                  <tr style="background-color: #d4d4d4;">
                    <td align="center" class="smallText">&nbsp;<?php echo $next_id; ?>&nbsp;</td>
                    <td align="left" class="smallText">&nbsp;<select name="option_id"><?php echo $options_dropdown_select;?></select>&nbsp;</td>
                    <td class="smallText"><input type="hidden" name="value_id" value="<?php echo $next_id; ?>"><?php echo $inputs; ?></td>
                    <td align="center" class="smallText">&nbsp;<?php echo xtc_button(BUTTON_INSERT); ?>&nbsp;</td>
                  </tr>
                  </form>
                  <tr>
                    <td colspan="<?php echo $colspan;?>"><?php echo xtc_black_line(); ?></td>
                  </tr>

  <?php
  }
// ############ EOF NEW ENTRY ############ //
$values = xtc_db_query($values);
while ($values_values = xtc_db_fetch_array($values)) {
  $rows++;

  $option_id = '&option_id='.$values_values['products_options_id'];
  $options_name = $values_values['products_options_name'];
  $values_name = $values_values['products_options_values_name'];

  // ############ BOF UPDATE ############ //
  if (($_GET['action'] == 'update_option_value') && ($_GET['value_id'] == $values_values['products_options_values_id'])) {
    $inputs = '';
    for ($i = 0, $n = sizeof($languages);$i < $n;$i++) {
      $value_name = xtc_db_query("-- products_attributes.php
                                  SELECT products_options_values_name
                                    FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                                   WHERE products_options_values_id = '" . $values_values['products_options_values_id'] . "'
                                     AND language_id = '" . $languages[$i]['id'] . "'"
      );
      $value_name = xtc_db_fetch_array($value_name);
      $lang_img = '<span style="float:left; padding-top:2px;">'. xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'].'/admin/images/'.$languages[$i]['image'], $languages[$i]['name']) . '</span>';
      $inputs .= $lang_img . '&nbsp;<input type="text" name="value_name[' . $languages[$i]['id'] . ']" style="width:200px;" value="' . $value_name['products_options_values_name'] . '">&nbsp;<br />';
    }
?>
                    <tr>
                      <td colspan="<?php echo $colspan;?>"><?php echo xtc_black_line(); ?></td>
                    </tr>
                    <form name="values" action="<?php echo xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_value&value_page='.$_GET['value_page'].$page_info.$option_id, 'NONSSL');?>" method="post">
                    <tr style="background-color: #d4d4d4;">
                      <td align="center" class="smallText">&nbsp;<?php echo $values_values['products_options_values_id']; ?>
                        <input type="hidden" name="value_id" value="<?php echo $values_values['products_options_values_id']; ?>">
                        <input type="hidden" name="option_id" value="<?php echo $values_values['products_options_id']; ?>">
                      </td>
                      <td align="center" class="smallText">&nbsp;<select name="option_id"><?php echo $options_dropdown_select;?></select>&nbsp;</td>
                      <td class="smallText"><?php echo $inputs; ?></td>
                      <td align="center" class="smallText update">&nbsp;<?php echo xtc_button(BUTTON_UPDATE); ?>&nbsp;<?php echo xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'value_page='.$_GET['value_page'].$option_filter, 'NONSSL')); ?>&nbsp;</td>
                    </tr>
                    </form>
                    <tr>
                      <td colspan="<?php echo $colspan;?>"><?php echo xtc_black_line(); ?></td>
                    </tr>
<?php
// ############ EOF UPDATE ##############//
  } else {
// ############ BOF LISTE ##############//
?>
                  <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
                    <td align="center" class="smallText">&nbsp;<?php echo $values_values["products_options_values_id"]; ?>&nbsp;</td>
                    <td align="center" class="smallText">&nbsp;<?php echo $options_name; ?>&nbsp;</td>
                    <td class="smallText">&nbsp;<?php echo $values_name; ?>&nbsp;</td>
                    <td align="center" class="smallText">
                      <?php echo xtc_button_link(BUTTON_EDIT, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_option_value&value_id=' . $values_values['products_options_values_id'] . '&value_page=' . $_GET['value_page'].$option_id, 'NONSSL'));
                      //BOF - webkiste - auf der selben Seite bleiben
                      echo xtc_button_link(BUTTON_DELETE, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_option_value&value_id=' . $values_values['products_options_values_id'] . '&value_page=' . $_GET['value_page'], 'NONSSL'));
                      //EOF - webkiste - auf der selben Seite bleiben
                      ?>
                    </td>
                  </tr>
<?php
// ############ EOF LISTE ##############//
  }
}
?>
     </table>
<?php
 // ############ EOF DEFAULT ############ //
  }
?>
<!-- option value eof //-->