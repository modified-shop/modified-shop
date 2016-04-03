<?php
/* --------------------------------------------------------------
   $Id: customer_memo.php 5140 2013-07-18 15:09:39Z web28 $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce


   Released under the GNU General Public License 
   --------------------------------------------------------------
   based on:
   (c) 2003	 nextcommerce (customer_memo.php,v 1.6 2003/08/18); www.nextcommerce.org
   
   --------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
?>
    <td class="dataTableConfig col-left"><?php echo ENTRY_MEMO; ?></td>
    <td class="dataTableConfig col-single-right">
    <?php
      $memo_query = xtc_db_query("SELECT *
                                    FROM " . TABLE_CUSTOMERS_MEMO . "
                                   WHERE customers_id = '" . (int)$_GET['cID'] . "'
                                ORDER BY memo_date DESC");
      while ($memo_values = xtc_db_fetch_array($memo_query)) {
        $poster_query = xtc_db_query("SELECT customers_firstname, customers_lastname FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '" . $memo_values['poster_id'] . "'");
        $poster_values = xtc_db_fetch_array($poster_query);
        ?>
        <table style="width:100%">
          <tr>
            <td class="main"><strong><?php echo TEXT_DATE; ?></strong>: <?php echo $memo_values['memo_date']; ?><br/><strong><?php echo TEXT_TITLE; ?></strong>: <?php echo $memo_values['memo_title']; ?><strong><br/><?php echo TEXT_POSTER; ?></strong>:<?php echo $poster_values['customers_lastname']; ?> <?php echo $poster_values['customers_firstname']; ?></td>
          </tr>
          <tr>
            <td class="main" style="border: 1px solid; border-color: #cccccc; width:140px;"><?php echo $memo_values['memo_text']; ?></td>
          </tr>
          <tr>        
            <td><a href="<?php echo xtc_href_link(FILENAME_CUSTOMERS, 'cID=' . (int)$_GET['cID'] . '&action=edit&special=remove_memo&mID=' . $memo_values['memo_id']); ?>" class="button" onclick="return confirm('<?php echo DELETE_ENTRY; ?>')"><?php echo BUTTON_DELETE; ?></a></td> 
          </tr>
        </table>
      <?php
      }
      ?>
      <table style="width:100%">
        <tr>
          <td class="main" style="border-top: 1px solid; border-color: #cccccc;">
            <div class="main mrg5"><strong><?php echo TEXT_TITLE ?></strong>:<?php echo xtc_draw_input_field('memo_title', ((isset($cInfo->memo_title)) ? $cInfo->memo_title : '')); ?></div>
            <?php echo xtc_draw_textarea_field('memo_text', 'soft', '80', '5', ((isset($cInfo->memo_text)) ? $cInfo->memo_text : '')); ?><br />
            <div class="main mrg5"><input type="submit" class="button" value="<?php echo BUTTON_INSERT; ?>"></div>
          </td>
        </tr>
      </table>
    </td>    