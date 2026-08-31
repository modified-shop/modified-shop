<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_GUARANTEE_LABELS_STATUS')
      && MODULE_GUARANTEE_LABELS_STATUS == 'true'
      && isset($_GET['subaction'])
      && $_GET['subaction'] == 'guarantee'
      && isset($_GET['opID'])
      )
  {
    require_once(DIR_FS_INC.'guarantee_labels_order.inc.php');

    $guarantee_oID = (int)$_GET['oID'];
    $guarantee_opID = (int)$_GET['opID'];

    $guarantee_snapshot = guarantee_labels_order_products($guarantee_oID);
    $guarantee_snapshot = isset($guarantee_snapshot[$guarantee_opID]) ? $guarantee_snapshot[$guarantee_opID] : false;

    // a value the admin just entered survives a failed save, so nothing has to be typed twice
    $guarantee_value = array(
      'manufacturers_name' => ($guarantee_snapshot === false) ? '' : $guarantee_snapshot['manufacturers_name'],
      'manufacturers_model' => ($guarantee_snapshot === false) ? '' : $guarantee_snapshot['manufacturers_model'],
      'garan_duration' => ($guarantee_snapshot === false) ? '' : $guarantee_snapshot['garan_duration'],
    );

    if (isset($_SESSION['guarantee_labels_post']) && (int)$_SESSION['guarantee_labels_post']['opID'] === $guarantee_opID) {
      $guarantee_value = array_merge($guarantee_value, $_SESSION['guarantee_labels_post']['values']);
      unset($_SESSION['guarantee_labels_post']);
    }

    // the confirmation is only demanded once the customer has seen the order
    $guarantee_sent_query = xtc_db_query("SELECT orders_status_history_id
                                            FROM ".TABLE_ORDERS_STATUS_HISTORY."
                                           WHERE orders_id = '".$guarantee_oID."'
                                             AND customer_notified = '1'");
    $guarantee_sent = (xtc_db_num_rows($guarantee_sent_query) > 0);
    ?>
    <table class="tableBoxCenter collapse">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent" colspan="2"><b><?php echo TEXT_GUARANTEE_LABELS_SNAPSHOT_HEADING; ?></b></td>
      </tr>
      <tr class="dataTableRow">
        <td class="dataTableContent" colspan="2"><?php echo TEXT_GUARANTEE_LABELS_SNAPSHOT_INFO; ?></td>
      </tr>
      <?php
        echo xtc_draw_form('guarantee_labels', FILENAME_ORDERS_EDIT, 'action=custom', 'post', 'enctype="multipart/form-data"');
        echo xtc_draw_hidden_field('subaction', 'guarantee');
        echo xtc_draw_hidden_field('oID', $guarantee_oID);
        echo xtc_draw_hidden_field('opID', $guarantee_opID);
        echo xtc_draw_hidden_field('pID', (int)$_GET['pID']);
      ?>
      <tr class="dataTableRow">
        <td class="dataTableContent" style="width:280px;"><?php echo TEXT_GUARANTEE_LABELS_SNAPSHOT_MANUFACTURER; ?></td>
        <td class="dataTableContent"><?php echo xtc_draw_input_field('manufacturers_name', $guarantee_value['manufacturers_name'], 'size="40"'); ?></td>
      </tr>
      <tr class="dataTableRow">
        <td class="dataTableContent"><?php echo TEXT_GUARANTEE_LABELS_SNAPSHOT_MODEL; ?></td>
        <td class="dataTableContent"><?php echo xtc_draw_input_field('manufacturers_model', $guarantee_value['manufacturers_model'], 'size="40"'); ?></td>
      </tr>
      <tr class="dataTableRow">
        <td class="dataTableContent"><?php echo TEXT_GUARANTEE_LABELS_SNAPSHOT_DURATION; ?></td>
        <td class="dataTableContent"><?php echo xtc_draw_input_field('garan_duration', $guarantee_value['garan_duration'], 'size="10"'); ?></td>
      </tr>
      <tr class="dataTableRow">
        <td class="dataTableContent" style="vertical-align:top;"><?php echo TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS; ?></td>
        <td class="dataTableContent">
          <?php
            if ($guarantee_snapshot !== false && $guarantee_snapshot['terms_filename'] !== null) {
              echo '<p>'.xtc_draw_radio_field('terms_action', 'keep', true).' '.TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_KEEP.
                   ' &ndash; '.encode_htmlspecialchars($guarantee_snapshot['terms_filename']).'</p>';
              echo '<p>'.xtc_draw_radio_field('terms_action', 'remove').' '.TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_REMOVE.'</p>';
            } else {
              echo '<p>'.xtc_draw_radio_field('terms_action', 'keep', true).' '.TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_NONE.'</p>';
            }

            echo '<p>'.xtc_draw_radio_field('terms_action', 'replace').' '.TEXT_GUARANTEE_LABELS_SNAPSHOT_TERMS_REPLACE.
                 ' <input type="file" name="terms_file" /></p>';
          ?>
        </td>
      </tr>
      <tr class="dataTableRow">
        <td class="dataTableContent" style="vertical-align:top;"><?php echo TEXT_GUARANTEE_LABELS_SNAPSHOT_PREVIEW; ?></td>
        <td class="dataTableContent">
          <?php
            $guarantee_preview = guarantee_labels_order_label($guarantee_oID, $guarantee_opID);
            echo ($guarantee_preview !== '') ? $guarantee_preview : TEXT_GUARANTEE_LABELS_SNAPSHOT_PREVIEW_NONE;
          ?>
        </td>
      </tr>
      <?php if ($guarantee_sent) { ?>
      <tr class="dataTableRow">
        <td class="dataTableContent">&nbsp;</td>
        <td class="dataTableContent"><?php echo xtc_draw_checkbox_field('confirm', '1').' '.TEXT_GUARANTEE_LABELS_SNAPSHOT_CONFIRM; ?></td>
      </tr>
      <?php } ?>
      <tr class="dataTableRow">
        <td class="dataTableContent">&nbsp;</td>
        <td class="dataTableContent">
          <?php
            echo '<input type="submit" name="guarantee_save" class="button" onclick="this.blur();" value="'.BUTTON_SAVE.'"/>';
            echo '<input type="submit" name="guarantee_from_product" class="button" onclick="this.blur();" value="'.TEXT_GUARANTEE_LABELS_SNAPSHOT_FROM_PRODUCT.'"/>';
            echo '<a class="button" href="'.xtc_href_link(FILENAME_ORDERS_EDIT, 'edit_action=products&oID='.$guarantee_oID).'">'.BUTTON_CANCEL.'</a>';
          ?>
        </td>
      </tr>
      </form>
    </table>
    <br /><br />
    <?php
    unset($guarantee_snapshot, $guarantee_value, $guarantee_preview, $guarantee_sent);
  }
