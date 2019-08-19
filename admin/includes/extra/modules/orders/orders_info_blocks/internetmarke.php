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

  if (defined('MODULE_INTERNETMARKE_STATUS') && MODULE_INTERNETMARKE_STATUS == 'true') {
    ?>
    <div class="heading"><?php echo TABLE_HEADING_INTERNETMARKE; ?></div>
    <?php echo xtc_draw_form('internetmarke', FILENAME_ORDERS, xtc_get_all_get_params(array('action')) . 'action=custom&subaction=im_insert'); ?>
      <table cellspacing="0" cellpadding="5" class="table borderall">
        <tr>
          <td class="smallText" align="center" style="width:100px;"><strong><?php echo TABLE_HEADING_CARRIER; ?></strong></td>
          <td class="smallText" align="center"><strong><?php echo TABLE_HEADING_LETTER_ID; ?></strong></td>
          <td class="smallText" align="center" style="width:100px;"><strong><?php echo TABLE_HEADING_DATE; ?></strong></td>
          <td class="smallText" align="center" style="width:155px;"><strong><?php echo TABLE_HEADING_ACTION; ?></strong></td>
        </tr>
        <?php
          $tracking_array = get_tracking_link($oID, $lang_code);
          if (count($tracking_array) > 0) {
            foreach($tracking_array as $tracking) {
              if ($tracking['external'] == '1'
                  && $tracking['im_orders_id'] != ''
                  )
              {
                ?>
                <tr>
                  <td class="smallText" align="center"><?php echo $tracking['carrier_name']; ?></td>
                  <td class="smallText" align="left"><?php echo $tracking['parcel_id']; ?></td>
                  <td class="smallText" align="center"><?php echo xtc_date_short($tracking['date_added']); ?></td>
                  <td class="smallText" align="center">
                    <a href="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.$oID.'&tID='.$tracking['tracking_id'].'&action=custom&subaction=im_delete'); ?>"><?php echo xtc_image(DIR_WS_ICONS.'cross.gif', ICON_DELETE); ?></a>
                    <?php
                    if ($tracking['im_url'] != '') {
                      echo '<a style="margin-left:10px;" href="'.$tracking['im_url'].'">'.xtc_image(DIR_WS_ICONS.'icon_pdf.gif', DOWNLOAD_LABEL).'</a>';
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
            require_once(DIR_FS_EXTERNAL.'internetmarke/internetmarke.php');
            $internetmarke = new mod_internetmarke($oID);
            $PageFormats = $internetmarke->getPageFormats(MODULE_INTERNETMARKE_PAGEFORMATS);
            $id = key($PageFormats);
        
            $row_array = array();
            for($i = 1, $n = $PageFormats[$id]['labelY']; $i <= $n; $i ++) {
              $row_array[] = array('id' => $i, 'text' => $i);
            }
        
            $column_array = array();
            for($i = 1, $n = $PageFormats[$id]['labelX']; $i <= $n; $i ++) {
              $column_array[] = array('id' => $i, 'text' => constant('TEXT_IM_COLUMN_'.$i));
            }
      
            $price_array = array();
            $price_query = xtc_db_query("SELECT *
                                           FROM `internetmarke`
                                          WHERE SEL != 0");
            if (xtc_db_num_rows($price_query) > 0) {
              while ($price = xtc_db_fetch_array($price_query)) {
                $price_array[] = array(
                  'id' => $price['PROID'],
                  'text' => $price['PRODNAME'],
                );
              }
            }
            if (count($price_array) > 0) {
            ?>
              <td class="smallText" align="center" style="padding:0;" colspan="3">
                <table cellpadding="5">
                  <tr>
                    <td class="smallText" style="border:none;"><?php echo TEXT_IM_FORMAT.'<br>'.xtc_draw_pull_down_menu('format', $PageFormats, $id, 'id="im_format" style="width:270px;"'); ?></td>
                    <td class="smallText" style="white-space:nowrap; border:none;"><?php echo TEXT_IM_ROW.'<br>'.xtc_draw_pull_down_menu('row', $row_array, '', 'id="im_row"'); ?></td>
                    <td class="smallText" style="white-space:nowrap; border:none;"><?php echo TEXT_IM_COLUMN.'<br>'.xtc_draw_pull_down_menu('column', $column_array, '', 'id="im_column"'); ?></td>
                    <td class="smallText" style="border:none;"><?php echo TEXT_IM_PORTO.'<br>'.xtc_draw_pull_down_menu('product', $price_array, '', 'style="width:320px;"'); ?></td>
                  </tr>
                </table>
              </td>
              <td class="smallText" align="center">
                <br>
                <input class="button" type="submit" value="<?php echo TEXT_IM_LABEL; ?>">
              </td>
            <?php
            } else {
              echo '<td colspan="4" class="txta-c warning_message">'.TEXT_INTERNETMARKE_PORTO.'</td>';
            }
            ?>
        </tr>
      </table>
    </form>
    <script type="text/javascript">
      $('#im_format').on('change', function() {
        get_im_service();
      });

      $(document).ready(function(){
        get_im_service();
      });

      function get_im_service() {
        var im_format = $('#im_format').val();
        var lang = "<?php echo $_SESSION['language_code']; ?>";

        $.get('../ajax.php', {ext: 'get_im_service', format: im_format, language: lang, speed: 1}, function(data) {
          if (data != '' && data != undefined) { 
            <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
              $('#im_row').replaceWith('<select id="im_row" name="row" class="SlectBox" style="visibility: hidden;"></select>');
              $('#im_row').nextAll('.optWrapper').replaceWith('<div class="optWrapper"><ul class="options" id="im_data_row"></ul></div>');
              $('#im_column').replaceWith('<select id="im_column" name="column" class="SlectBox" style="visibility: hidden;"></select>');
              $('#im_column').nextAll('.optWrapper').replaceWith('<div class="optWrapper"><ul class="options" id="im_data_column"></ul></div>');
            <?php } else { ?>
              $('#im_row').replaceWith('<select id="im_row" name="row" class="SlectBox"></select>');
              $('#im_column').replaceWith('<select id="im_column" name="column" class="SlectBox"></select>');        
            <?php } ?>
      
            $.each(data.row, function(id, arr) {
              $('<option value="'+arr.id+'">'+arr.text+'</option>').appendTo('#im_row');
              <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
                $('<li data-val="'+arr.id+'"><label>'+arr.text+'</label></li>').appendTo('#im_data_row');        
              <?php } ?>
            });

            $.each(data.column, function(id, arr) {
              $('<option value="'+arr.id+'">'+arr.text+'</option>').appendTo('#im_column');
              <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
                $('<li data-val="'+arr.id+'"><label>'+arr.text+'</label></li>').appendTo('#im_data_column');        
              <?php } ?>
            });    

            <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
              $('.SlectBox').not('.noStyling').SumoSelect({ createElems: 'mod', placeholder: '-'});
            <?php } ?>
          }
        });
      }
    </script>
    <?php
  }
?>