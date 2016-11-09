<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:
   (c) 2003 XT-Commerce
   (c) 2003	nextcommerce (customer_memo.php,v 1.6 2003/08/18); www.nextcommerce.org
   --------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
?>
  <td class="dataTableConfig col-left" style="vertical-align:top;"><?php echo ENTRY_MEMO; ?></td>
  <td class="dataTableConfig col-single-right">
  <?php
    $memo_query = xtc_db_query("SELECT *
                                  FROM " . TABLE_CUSTOMERS_MEMO . "
                                 WHERE customers_id = '" . (int)$_GET['cID'] . "'
                              ORDER BY memo_date DESC");
    if (xtc_db_num_rows($memo_query) > 0) {
      while ($memo_values = xtc_db_fetch_array($memo_query)) {
        $poster_query = xtc_db_query("SELECT customers_firstname, customers_lastname FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '" . $memo_values['poster_id'] . "'");
        $poster_values = xtc_db_fetch_array($poster_query);
        ?>
        <div style="margin:2px; padding:2px; border: 1px solid; border-color: #cccccc;">
        <table style="width:100%">
          <tr>
            <td class="main" style="width:120px; border:none; padding:2px;"><strong><?php echo TEXT_DATE; ?></strong>:</td>
            <td class="main" style="border:none; padding:2px;"><?php echo xtc_date_short($memo_values['memo_date']); ?></td>
          </tr>
          <tr>
            <td class="main" style="border:none; padding:2px;"><strong><?php echo TEXT_TITLE; ?></strong>:</td>
            <td class="main" style="border:none; padding:2px;"><?php echo $memo_values['memo_title']; ?></td>
          </tr>
          <tr>
            <td class="main" style="border:none; padding:2px;"><strong><?php echo TEXT_POSTER; ?></strong>:</td>
            <td class="main" style="border:none; padding:2px;"><?php echo $poster_values['customers_lastname'] . ' ' . $poster_values['customers_firstname']; ?></td>
          </tr>
          <tr>
            <td class="main" style="border:none; padding:2px; vertical-align:top;"><strong><?php echo ENTRY_MEMO; ?></strong>:</td>
            <td class="main" style="border:none; padding:2px;"><?php echo $memo_values['memo_text']; ?></td>
          </tr>
          <tr>        
            <td class="txta-r" colspan="2" style="border:none; padding:2px;"><a style="text-decoration:none;" href="<?php echo xtc_href_link(basename($PHP_SELF), 'cID=' . (int)$_GET['cID'] . '&action=edit&special=remove_memo&mID=' . $memo_values['memo_id']); ?>" class="button" onclick="return confirmLink('<?php echo DELETE_ENTRY; ?>', '', this)"><?php echo BUTTON_DELETE; ?></a></td> 
          </tr>
        </table>
        </div>
      <?php
      }
      echo '<br/>';
    }
    ?>
    <div style="margin:2px; padding:2px; border: 1px solid; border-color: #cccccc;">
      <table style="width:100%">
        <tr>
          <td class="main" style="width:80px; border:none; padding:2px;"><strong><?php echo TEXT_TITLE; ?></strong>:</td>
          <td class="main" style="border:none; padding:2px;"><?php echo xtc_draw_input_field('memo_title', ((isset($cInfo->memo_title)) ? $cInfo->memo_title : ''), 'style="width:100%; max-width:676px;"'); ?></td>
        </tr>
        <tr>
          <td class="main" style="border:none; padding:2px; vertical-align:top;"><strong><?php echo ENTRY_MEMO; ?></strong>:</td>
          <td class="main" style="border:none; padding:2px;"><?php echo xtc_draw_textarea_field('memo_text', 'soft', '80', '8', ((isset($cInfo->memo_text)) ? $cInfo->memo_text : ''), 'style="width:99%; max-width:676px;"'); ?></td>
        </tr>
        <tr>        
          <td class="txta-r" colspan="2" style="border:none; padding:2px;"><input type="submit" class="button" value="<?php echo BUTTON_INSERT; ?>"></td> 
        </tr>
      </table>
    </div>
  </td>