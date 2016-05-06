<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  ## Banktransfer
  if ($order->info['payment_method'] == 'banktransfer') {
    $banktransfer_query = xtc_db_query("SELECT banktransfer_owner,
                                               banktransfer_number,
                                               banktransfer_bankname,
                                               banktransfer_blz,
                                               banktransfer_iban,
                                               banktransfer_bic,
                                               banktransfer_status,
                                               banktransfer_prz,
                                               banktransfer_fax,
                                               banktransfer_owner_email
                                          FROM ".TABLE_BANKTRANSFER."
                                         WHERE orders_id = '".$oID."'");
    if (xtc_db_num_rows($banktransfer_query) == 1) {
      $banktransfer = xtc_db_fetch_array($banktransfer_query);
      if ($banktransfer['banktransfer_bankname'] 
          || $banktransfer['banktransfer_blz'] 
          || $banktransfer['banktransfer_number'] 
          || $banktransfer['banktransfer_iban']
          ) 
      {
        ?>
        <tr>
          <td colspan="2"><?php echo xtc_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_BANK_OWNER; ?></td>
          <td class="main"><?php echo $banktransfer['banktransfer_owner']; ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_BANK_NUMBER; ?></td>
          <td class="main"><?php echo $banktransfer['banktransfer_number']; ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_BANK_BLZ; ?></td>
          <td class="main"><?php echo $banktransfer['banktransfer_blz']; ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_BANK_IBAN; ?></td>
          <td class="main"><?php echo $banktransfer['banktransfer_iban']; ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_BANK_BIC; ?></td>
          <td class="main"><?php echo $banktransfer['banktransfer_bic']; ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_BANK_NAME; ?></td>
          <td class="main"><?php echo $banktransfer['banktransfer_bankname']; ?></td>
        </tr>
        <tr>
          <td class="main"><?php echo TEXT_BANK_OWNER_EMAIL; ?></td>
          <td class="main"><?php echo $banktransfer['banktransfer_owner_email']; ?></td>
        </tr>
        <?php  if ($banktransfer['banktransfer_status'] == 0) { ?>
          <tr>
            <td class="main"><?php echo TEXT_BANK_STATUS; ?></td>
            <td class="main"><?php echo "OK"; ?></td>
          </tr>
        <?php } else { ?>
          <tr>
            <td class="main"><?php echo TEXT_BANK_STATUS; ?></td>
            <td class="main"><?php echo $banktransfer['banktransfer_status']; ?></td>
          </tr>
          <?php
          $bt_status = (int) $banktransfer['banktransfer_status'];
          $error_val = defined('TEXT_BANK_ERROR_'.$bt_status) ? constant('TEXT_BANK_ERROR_'.$bt_status) : '';
          ?>
          <tr>
            <td class="main"><?php echo TEXT_BANK_ERRORCODE; ?></td>
            <td class="main"><?php echo $error_val; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_BANK_PRZ; ?></td>
            <td class="main"><?php echo $banktransfer['banktransfer_prz']; ?></td>
          </tr>
        <?php } ?>
        <tr>
          <td class="main" colspan="2"><a class="button" href="<?php echo xtc_href_link(FILENAME_ORDERS, 'action=custom&subaction=deletebanktransfer&oID='.$oID); ?>"><?php echo BUTTON_DELETE_BANKTRANSFER; ?></a></td>
        </tr>
        <?php
      }
      if ($banktransfer['banktransfer_fax']) {
      ?>
        <tr>
          <td class="main"><?php echo TEXT_BANK_FAX; ?></td>
          <td class="main"><?php echo $banktransfer['banktransfer_fax']; ?></td>
        </tr>
      <?php
      }
    }
  }
?>