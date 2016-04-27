<?php
  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

  if (defined('MODULE_SHIPCLOUD_STATUS') && MODULE_SHIPCLOUD_STATUS == 'True') {
    ?>
    <div class="heading"><?php echo TABLE_HEADING_SHIPCLOUD; ?></div>
    <?php echo xtc_draw_form('carriers', FILENAME_ORDERS, xtc_get_all_get_params(array('action')) . 'action=custom&subaction=inserttracking'); ?>
    <table cellspacing="0" cellpadding="5" class="table borderall">
      <tr>
        <td class="smallText" align="center" style="width:100px;"><strong><?php echo TABLE_HEADING_CARRIER; ?></strong></td>
        <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_PARCEL_ID; ?></strong></td>
        <td class="smallText" align="center" style="width:155px;"><strong><?php echo TABLE_HEADING_ACTION; ?></strong></td>
      </tr>
      <?php
        $service_array = array(
          array('id' => 'standard', 'text' => TEXT_SHIPCLOUD_STANDARD),
          array('id' => 'one_day', 'text' => TEXT_SHIPCLOUD_ONE_DAY),
          array('id' => 'one_day_early', 'text' => TEXT_SHIPCLOUD_ONE_DAY_EARLY),
          array('id' => 'returns', 'text' => TEXT_SHIPCLOUD_RETURNS),
          array('id' => 'letter', 'text' => TEXT_SHIPCLOUD_LETTER),
          array('id' => 'parcel_letter', 'text' => TEXT_SHIPCLOUD_PARCEL_LETTER),
          array('id' => 'books', 'text' => TEXT_SHIPCLOUD_BOOKS),
        );
        $parcel_array = array();
        $dim_array = explode(';', preg_replace("'[\r\n\s]+'", '', MODULE_SHIPCLOUD_PARCEL));
        for ($p=0, $pn=count($dim_array); $p<$pn; $p++) {
          if ($dim_array[$p] != '') {
            $parcel_array[] = array('id' => $dim_array[$p], 'text' => str_replace(',', 'cm x ', $dim_array[$p] .'cm'));
          }
        }
        $tracking_array = get_tracking_link($oID, $lang_code);
        if (count($tracking_array) > 0) {
          foreach($tracking_array as $tracking) {
            if ($tracking['external'] == '1') {
              ?>
              <tr>
                <td class="smallText" align="center"><?php echo $tracking['carrier_name']; ?></td>
                <td class="smallText" align="left"><?php echo $tracking['parcel_id']; ?></td>
                <td class="smallText" align="center">
                  <a href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&tID='.$tracking['tracking_id'].'&action=custom&subaction=deletetracking'); ?>"><?php echo xtc_image(DIR_WS_ICONS.'cross.gif', ICON_DELETE); ?></a>
                  <?php
                  if ($tracking['sc_label_url'] != '') {
                    echo '<a style="margin-left:10px;" target="_blank" href="'.$tracking['sc_label_url'].'">'.xtc_image(DIR_WS_ICONS.'icon_pdf.gif', DOWNLOAD_LABEL).'</a>';
                  }
                  ?>
                </td>
              <tr>
              <?php
            }
          }
        }
      ?>
      <tr>
        <?php
          echo '<td class="smallText" align="center" colspan="2">';
          echo xtc_draw_pull_down_menu('carrier_id', $carriers, $carriers[0]).'&nbsp;';
          echo xtc_draw_pull_down_menu('service', $service_array, $service_array[0]).'&nbsp;';
          echo xtc_draw_pull_down_menu('parcel', $parcel_array, $parcel_array[0]).'&nbsp;';
          echo xtc_draw_input_field('description', '' , 'style="width:350px;vertical-align:top;" placeholder="'.TEXT_CARRIER_PLACEHOLDER.'"');
          echo '</td>';
        ?>
        <td class="smallText" align="center">
          <input class="button" style="display:block; width:155px; margin: 7px auto" type="submit" value="<?php echo CREATE_LABEL; ?>">
          <input class="button" style="display:block; width:155px; margin: 7px auto" type="submit" name="quote" value="<?php echo CHECK_LABEL_PRICE; ?>">
        </td>
      </tr>
    </table>
    </form>
    <?php
  }
?>