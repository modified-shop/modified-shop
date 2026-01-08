<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_PRODUCTS_TARIFF_STATUS')
      && MODULE_PRODUCTS_TARIFF_STATUS == 'true'
      && isset($_GET['subaction'])
      && $_GET['subaction'] == 'tariff'
      )
  {
    $products_tariff_query = xtc_db_query("SELECT * 
                                             FROM ".TABLE_ORDERS_PRODUCTS." 
                                            WHERE orders_id = '".(int)$_GET['oID']."' 
                                              AND orders_products_id = '".(int)$_GET['opID']."'");
    if (xtc_db_num_rows($products_tariff_query) > 0) {
      $countries_array = array(array('id' => '', 'text' => TEXT_NONE));
      $countries_query = xtc_db_query("SELECT countries_iso_code_2,
                                              countries_name
                                         FROM ".TABLE_COUNTRIES."
                                     ORDER BY countries_name");
      while ($countries = xtc_db_fetch_array($countries_query)) {
        $countries_array[] = array('id' => $countries['countries_iso_code_2'], 'text' => $countries['countries_name']);
      }
      ?>
      <!-- Tariff Anfang //-->
      <table class="tableBoxCenter collapse">
        <tr class="dataTableHeadingRow">
          <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCT_ID;?></b></td>
          <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCTS_TARIFF;?></b></td>
          <td class="dataTableHeadingContent" style="width:310px"><b><?php echo TEXT_PRODUCTS_ORIGIN;?></b></td>
          <td class="dataTableHeadingContent"><b><?php echo TEXT_PRODUCTS_TARIFF_TITLE;?></b></td>
          <td class="dataTableHeadingContent">&nbsp;</td>
        </tr>
        <?php
          while($products_tariff = xtc_db_fetch_array($products_tariff_query)) {
            echo xtc_draw_form('product_tariff_edit', FILENAME_ORDERS_EDIT, 'action=custom&subaction=tariff', 'post');
              echo xtc_draw_hidden_field('oID', (int)$_GET['oID']);
              echo xtc_draw_hidden_field('opID', (int)$_GET['opID']);
              echo xtc_draw_hidden_field('pID', (int)$_GET['pID']);
              ?>
              <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo $products_tariff['products_id']; ?></td>
                <td class="dataTableContent"><?php echo xtc_draw_input_field('products_tariff', $products_tariff['products_tariff']);?></td>
                <td class="dataTableContent"><?php echo xtc_draw_pull_down_menu('products_origin', $countries_array, $products_tariff['products_origin']);?></td>
                <td class="dataTableContent"><?php echo xtc_draw_input_field('products_tariff_title', $products_tariff['products_tariff_title']);?></td>
                <td class="dataTableContent txta-c">
                  <?php
                    echo '<input type="submit" name="product_tariff_edit" class="button" onclick="this.blur();" value="'.BUTTON_SAVE.'"/>';
                  ?>
                </td>
              </tr>
            </form>
            <?php
          }
        ?>
      </table>
      <br /><br />
      <!-- Tariff Ende //-->
      <?php
    }
  }