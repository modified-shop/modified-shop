<?php
/* --------------------------------------------------------------
   $Id: products_attributes_options.php 3235 2012-07-16 14:08:23Z web28 $

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
   
   products_attribtues_options (c) www.rpa-com.de
   --------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if ($_GET['action'] != 'delete_product_option') {

  $options_dropdown_order = '';
  $options = array();
  $options[] = (array ('id' => 'products_options_id', 'text' => TEXT_OPTION_ID));
  $options[] = (array ('id' => 'products_options_name', 'text' => TEXT_OPTION_NAME));
  $options[] = (array ('id' => 'products_options_sortorder', 'text' => TEXT_SORTORDER));
  $options_dropdown_order = xtc_draw_pull_down_menu('selected', $options, $_GET['option_order_by'], 'onchange="go_option()" ') ."\n";

  //BOF Sortierung nach...
  if ($_GET['option_order_by']) {
    $option_order_by = $_GET['option_order_by'];
  } else {
    $option_order_by = 'products_options_id';
  }

  //BOF Seitenschaltung
  $option_page = (int)$_GET['option_page'];
  if (defined('MAX_ROW_LISTS_ATTR_OPTIONS')) {
    $per_page = (int)MAX_ROW_LISTS_ATTR_OPTIONS;
  } else {
    $per_page = (int)MAX_ROW_LISTS_OPTIONS; //aus Sprachdatei (veraltet)
  }
  
  if (isset($_GET['searchoption'])) {
    $options = "-- products_attributes.php
      SELECT * FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE language_id = '" . $_SESSION['languages_id'] . "' AND products_options_name LIKE '%" . $_GET['searchoption'] . "%' ORDER BY " . $option_order_by;
  } else {
    $options = "-- products_attributes.php
      SELECT * FROM " . TABLE_PRODUCTS_OPTIONS . " WHERE language_id = '" . (int)$_SESSION['languages_id'] . "' ORDER BY " . $option_order_by;
  }
  if (!$option_page) {
    $option_page = 1;
  }
  $prev_option_page = $option_page-1;
  $next_option_page = $option_page+1;
  $option_query = xtc_db_query($options);
  $option_page_start = ($per_page*$option_page) -$per_page;
  $num_rows = xtc_db_num_rows($option_query);
  if ($num_rows <= $per_page) {
    $num_pages = 1;
  } else if (($num_rows%$per_page) == 0) {
    $num_pages = ($num_rows/$per_page);
  } else {
    $num_pages = ($num_rows/$per_page) +1;
  }
  $num_pages = (int)$num_pages;
  $options = $options . " LIMIT $option_page_start, $per_page";

  // Previous
  $option_pages = '';
  if ($prev_option_page) {
   $option_pages .= '<a href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_page=' . $prev_option_page . '&searchoption=' . $_GET['searchoption']) . '"> &lt;&lt; </a> | ';
  }
  for ($i = 1;$i <= $num_pages;$i++) {
    if ($i != $option_page) {
      $option_pages .= '<a href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_page=' . $i . '&searchoption=' . $_GET['searchoption']) . '">' . $i . '</a> | ';
    } else {
      $option_pages .= '<strong><font color=red>' . $i . '</font></strong> | ';
    }
  }
  // Next
  if ($option_page != $num_pages) {
    $option_pages .= '<a href="' . xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_page=' . $next_option_page . '&searchoption=' . $_GET['searchoption']) . '"> &gt;&gt; </a>';
  }
  //EOF Seitenschaltung

}

  // ############  BOF DELETE  ############ //
if ($_GET['action'] == 'delete_product_option') {
  $options = xtc_db_query("-- products_attributes.php
                          SELECT products_options_id,
                                 products_options_name
                            FROM " . TABLE_PRODUCTS_OPTIONS . "
                           WHERE products_options_id = '" . (int)$_GET['option_id'] . "'
                             AND language_id = '" . (int)$_SESSION['languages_id'] . "'"
                          );
  $options_values = xtc_db_fetch_array($options);
?>
                  <table border="0" cellspacing="0" cellpadding="2" class="option-table">
                    <tr>
                      <td class="pageHeading" colspan="3">&nbsp;<?php echo $options_values['products_options_name']; ?>&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="3"><?php echo xtc_black_line(); ?></td>
                    </tr>
<?php
  $products = xtc_db_query("-- products_attributes.php
                          SELECT p.products_id,
                                 pd.products_name,
                                 pov.products_options_values_name
                            FROM " . TABLE_PRODUCTS . " p,
                                 " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov,
                                 " . TABLE_PRODUCTS_ATTRIBUTES . " pa,
                                 " . TABLE_PRODUCTS_DESCRIPTION . " pd
                           WHERE pd.products_id = p.products_id
                             AND pov.language_id = '" . (int)$_SESSION['languages_id'] . "'
                             AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                             AND pa.products_id = p.products_id
                             AND pa.options_id='" . (int)$_GET['option_id'] . "'
                             AND pov.products_options_values_id = pa.options_values_id order by pd.products_name"
                         );

  if (xtc_db_num_rows($products)) {
  //Produkt zugeordnet - Warnung - Attributemerkmal kann nicht gelöscht werden
?>
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
                      <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_PRODUCT; ?>&nbsp;</td>
                      <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_VALUE; ?>&nbsp;</td>
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
                      <td class="smallText">&nbsp;<?php echo $products_values['products_options_values_name']; ?>&nbsp;</td>
                    </tr>
<?php
}
?>
                    <tr>
                      <td colspan="3"><?php echo xtc_black_line(); ?></td>
                    </tr>
                    <tr>
                      <td colspan="3" class="main" style="background-color: #d4d4d4;">
                        <div style="margin:10px 0";>
                          <?php echo TEXT_WARNING_OF_DELETE; ?>&nbsp;&nbsp;&nbsp;
                          <?php echo xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_page=' . $_GET['option_page'] . '&attribute_page=' . $attribute_page, 'NONSSL'));?>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="3"><?php echo xtc_black_line(); ?></td>
                    </tr>
<?php
} else {
//Produkt nicht zugeordnet - Ok - Attributemerkmal kann gelöscht werden
?>
                    <tr>
                      <td class="main" colspan="3" style="background-color: #d4d4d4;">
                      <div style="margin:10px 0";>
                      <?php echo TEXT_OK_TO_DELETE; ?>&nbsp;&nbsp;&nbsp;
                      <?php echo xtc_button_link(BUTTON_DELETE, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_option&option_id=' . $_GET['option_id']. '&option_page=' . $_GET['option_page'], 'NONSSL'));?>&nbsp;&nbsp;&nbsp;
                      <?php echo xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'order_by=' . $order_by . '&page=' . $page.'&option_page=' . $_GET['option_page'], 'NONSSL'));?>&nbsp;
                      </div>
                    </tr>
                    <tr>
                      <td colspan="3"><?php echo xtc_black_line(); ?></td>
                    </tr>
<?php
}
?>
                  </table>
<?php
  // ############  EOF DELETE  ############ //
} else {
  // ############  BOF DEFAULT  ############ //
?>
                  <table border="0" cellspacing="0" cellpadding="2" class="option-table">
                    <tr>
                      <td colspan="4" class="pageHeading">&nbsp;<?php echo HEADING_TITLE_OPT; ?>&nbsp;                          
                          <form name="option_order_by" action="<?php echo FILENAME_PRODUCTS_ATTRIBUTES; ?>">
                             <?php echo $options_dropdown_order; ?>
                          </form>&nbsp;&nbsp;
                          <form name="search" action="<?php echo FILENAME_PRODUCTS_ATTRIBUTES; ?>" method="GET">
                            <span class="main"><?php echo TEXT_SEARCH; ?></span> <input type="text" name="searchoption" size="20" value="<?php echo $_GET['searchoption']; ?>">
                            <input name="<?php echo xtc_session_name(); ?>" type="hidden" value="<?php echo xtc_session_id(); ?>" />
                          </form>
                      </td>
                    </tr>
                    <tr>
                      <td colspan="4" class="smallText"><?php echo $option_pages;?></td>
                    </tr>
                    <tr>
                      <td colspan="4"><?php echo xtc_black_line(); ?></td>
                    </tr>
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_ID; ?>&nbsp;</td>
                      <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_SORTORDER; ?>&nbsp;</td>
                      <td class="dataTableHeadingContent">&nbsp;<?php echo TABLE_HEADING_OPT_NAME; ?>&nbsp;</td>
                      <td class="dataTableHeadingContent" align="center">&nbsp;<?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                    </tr>
                    <tr>
                      <td colspan="4"><?php echo xtc_black_line(); ?></td>
                    </tr>
<?php
// ############  BOF NEW ENTRY  ############ //
if ($_GET['action'] != 'update_option') {
  $max_options_id_query = xtc_db_query("-- products_attributes.php
                                        select max(products_options_id) + 1 as next_id from " . TABLE_PRODUCTS_OPTIONS
                                      );
  $max_options_id_values = xtc_db_fetch_array($max_options_id_query);
  $next_id = $max_options_id_values['next_id'];
  if ($next_id < 1) $next_id = 1;

  $inputs = '';
  for ($i = 0, $n = sizeof($languages);$i < $n;$i++) {
    $lang_img = '<span style="float:left; padding-top:2px;">'. xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'].'/admin/images/'.$languages[$i]['image'], $languages[$i]['name']) . '</span>';
    $inputs.= $lang_img . '&nbsp;<input type="text" name="option_name[' . $languages[$i]['id'] . ']" style="width:200px;">&nbsp;<br />';
  }
  ?>
                    <tr>
                      <td colspan="4"><?php echo xtc_black_line(); ?></td>
                    </tr>
                    <form name="options" action="<?php echo xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=add_product_options&option_page=' . $option_page, 'NONSSL');?>" method="post">
                    <input type="hidden" name="products_options_id" value="<?php echo $next_id;?>">
                    <tr style="background-color: #d4d4d4;">
                       <td align="center" class="smallText">&nbsp;<?php echo $next_id; ?>&nbsp;</td>
                      <td class="smallText"><?php echo TABLE_HEADING_SORTORDER . ':&nbsp;<input type="text" name="products_options_sortorder" style="width:80px;" value="' . $option_name['products_options_sortorder'] . '">'; ?></td>
                      <td class="smallText"><?php echo $inputs; ?></td>
                      <td align="center" class="smallText">&nbsp;<?php echo xtc_button(BUTTON_INSERT); ?>&nbsp;</td>
                    </tr>
                    </form>
                    <tr>
                      <td colspan="4"><?php echo xtc_black_line(); ?></td>
                    </tr>
<?php
}
// ############  EOF NEW ENTRY  ############ //

$options = xtc_db_query($options);
while ($options_values = xtc_db_fetch_array($options)) {
  $rows++;
  // ############  BOF UPDATE  ############ //
  if (($_GET['action'] == 'update_option') && ($_GET['option_id'] == $options_values['products_options_id'])) {
    $inputs = '';
    for ($i = 0, $n = sizeof($languages);$i < $n;$i++) {
      $option_name = xtc_db_query("-- products_attributes.php
                                  SELECT products_options_name
                                    FROM " . TABLE_PRODUCTS_OPTIONS . "
                                   WHERE products_options_id = '" . $options_values['products_options_id'] . "'
                                     AND language_id = '" . $languages[$i]['id'] . "'"
                                 );
      $option_name = xtc_db_fetch_array($option_name);      
      $lang_img = '<span style="float:left; padding-top:2px;">'. xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'].'/admin/images/'.$languages[$i]['image'], $languages[$i]['name']) . '</span>';
      $inputs.= $lang_img . '&nbsp;<input type="text" name="option_name[' . $languages[$i]['id'] . ']" style="width:200px;" value="' . $option_name['products_options_name'] . '">&nbsp;<br />';
    }
  ?>
                    <tr>
                      <td colspan="4"><?php echo xtc_black_line(); ?></td>
                    </tr>
                    <form name="option" action="<?php echo xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_option_name&option_page='.$_GET['option_page'], 'NONSSL') ;?>" method="post">
                    <tr style="background-color: #d4d4d4;">
                       <td align="center" class="smallText">
                        <?php echo $options_values['products_options_id']; ?><input type="hidden" name="option_id" value="<?php echo $options_values['products_options_id']; ?>">
                      </td>
                      <td align="left" class="smallText">
                        <?php echo TABLE_HEADING_SORTORDER; ?>:&nbsp;<input type="text" name="products_options_sortorder" style="width:80px;" value="<?php echo $options_values['products_options_sortorder']; ?>">
                      </td>
                      <td class="smallText"><?php echo $inputs; ?></td>
                      <td align="center" class="smallText">
                        <?php echo xtc_button(BUTTON_UPDATE); ?>&nbsp;
                        <?php //BOF - webkiste - auf der selben Seite bleiben ?>
                        <?php echo xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_page=' . $_GET['option_page'] .'', 'NONSSL')); ?>
                        <?php //EOF - webkiste - auf der selben Seite bleiben ?>
                      </td>
                    </tr>
                    </form>
                    <tr>
                      <td colspan="4"><?php echo xtc_black_line(); ?></td>
                    </tr>
  <?php
  // ############ EOF UPDATE  ############ //
  } else {
     //Standard
  ?>
                      <tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
                        <td align="center" class="smallText">&nbsp;<?php echo $options_values["products_options_id"]; ?>&nbsp;</td>
                        <td class="smallText">&nbsp;<?php echo $options_values["products_options_sortorder"]; ?>&nbsp;</td>
                        <td class="smallText">&nbsp;<?php echo $options_values["products_options_name"]; ?>&nbsp;</td>
                        <td align="center" class="smallText">
                          <?php echo xtc_button_link(BUTTON_EDIT, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=update_option&option_id=' . $options_values['products_options_id'] . '&option_order_by=' .
                          $option_order_by . '&option_page=' . $option_page, 'NONSSL')); ?>&nbsp;&nbsp;
                           <?php
                          //BOF - webkiste - auf der selben Seite bleiben
                          echo xtc_button_link(BUTTON_DELETE, xtc_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'action=delete_product_option&option_id=' . $options_values['products_options_id'] .'&option_page=' . $_GET['option_page'] , 'NONSSL'));
                          //EOF - webkiste - auf der selben Seite bleiben
                          ?>
                        </td>
                      </tr>
  <?php
  }
}
?>
</table>
<?php
// ############  EOF DEFAULT  ############ //
  }
?>
<br />
<!-- options eof //-->