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
    require_once(DIR_FS_EXTERNAL.'dhl/DHLInternetmarke.php');
    $DHLInternetmarke = new DHLInternetmarke(array());
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
              if ($tracking['external'] == '3'
                  || ($tracking['external'] == '1' && !empty($tracking['im_orders_id']))
                  )
              {
                ?>
                <tr>
                  <td class="smallText" align="center"><?php echo encode_htmlspecialchars($tracking['carrier_name']); ?></td>
                  <td class="smallText" align="left"><?php echo encode_htmlspecialchars($tracking['parcel_id']); ?></td>
                  <td class="smallText" align="center"><?php echo xtc_date_short($tracking['date_added']); ?></td>
                  <td class="smallText" align="center">
                    <button type="submit"
                            formmethod="post"
                            formaction="<?php echo xtc_href_link(FILENAME_ORDERS, 'oID='.(int)$oID.'&tID='.(int)$tracking['tracking_id'].'&action=custom&subaction=im_delete'); ?>"
                            onclick="return confirm('<?php echo htmlspecialchars(TEXT_IM_LABEL_DELETE_CONFIRM, ENT_QUOTES, $_SESSION['language_charset']); ?>');"
                            style="border:0;background:transparent;padding:0;cursor:pointer;">
                      <?php echo xtc_image(DIR_WS_ICONS.'cross.gif', ICON_DELETE); ?>
                    </button>
                    <?php
                    $label_url = isset($tracking['im_url']) && is_scalar($tracking['im_url']) ? trim((string)$tracking['im_url']) : '';
                    $label_url_scheme = ($label_url != '') ? parse_url($label_url, PHP_URL_SCHEME) : false;
                    if (filter_var($label_url, FILTER_VALIDATE_URL) !== false
                        && is_string($label_url_scheme)
                        && in_array(strtolower($label_url_scheme), array('http', 'https'), true)
                        )
                    {
                      echo '<a style="margin-left:10px;" href="'.htmlspecialchars($label_url, ENT_QUOTES, $_SESSION['language_charset']).'">'.xtc_image(DIR_WS_ICONS.'icon_pdf.gif', DOWNLOAD_LABEL).'</a>';
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
            $result = $DHLInternetmarke->getPageFormats(MODULE_INTERNETMARKE_PAGEFORMATS);
            $row_array = array();
            $column_array = array();
            $format_service_array = array();

            if (isset($result['formats'])
                && is_array($result['formats'])
                && count($result['formats']) > 0
                )
            {
              foreach ($result['formats'] as $format_id => $format) {
                $result['formats'][$format_id]['text'] = encode_htmlspecialchars($format['text']);
                $format_service_array[$format_id] = array(
                  'row' => array(),
                  'column' => array(),
                );

                for ($i = 1, $n = $format['labelY']; $i <= $n; $i++) {
                  $format_service_array[$format_id]['row'][] = array('id' => $i, 'text' => $i);
                }

                for ($i = 1, $n = $format['labelX']; $i <= $n; $i++) {
                  $column_text = defined('TEXT_IM_COLUMN_'.$i) ? constant('TEXT_IM_COLUMN_'.$i) : $i;
                  $format_service_array[$format_id]['column'][] = array('id' => $i, 'text' => $column_text);
                }
              }

              $id = key($result['formats']);
              $row_array = $format_service_array[$id]['row'];
              $column_array = $format_service_array[$id]['column'];
            }
            
            $price_array = array();
            $price_query = xtc_db_query("SELECT *
                                           FROM `internetmarke`
                                          WHERE SEL != 0");
            if (xtc_db_num_rows($price_query) > 0) {
              while ($price = xtc_db_fetch_array($price_query)) {
                $price_array[] = array(
                  'id' => $price['PROID'],
                  'text' => encode_htmlspecialchars($price['PRODNAME']).' - '.trim(format_price($price['PROPR'], 1, $order->info['currency'], 0, 0))
                );
              }
            }
            if (count($price_array) > 0
                && count($row_array) > 0
                && count($column_array) > 0
                ) 
            {
            ?>
              <td class="smallText" align="center" style="padding:0;" colspan="3">
                <table cellpadding="5">
                  <tr>
                    <td class="smallText" style="border:none;"><?php echo '<div style="margin-bottom:8px;">'.TEXT_IM_FORMAT.'</div>'.xtc_draw_pull_down_menu('format', $result['formats'], $id, 'id="im_format" style="width:270px;"'); ?></td>
                    <td class="smallText" style="white-space:nowrap; border:none;"><?php echo '<div style="margin-bottom:8px;">'.TEXT_IM_ROW.'</div>'.xtc_draw_pull_down_menu('row', $row_array, '', 'id="im_row"'); ?></td>
                    <td class="smallText" style="white-space:nowrap; border:none;"><?php echo '<div style="margin-bottom:8px;">'.TEXT_IM_COLUMN.'</div>'.xtc_draw_pull_down_menu('column', $column_array, '', 'id="im_column"'); ?></td>
                    <td class="smallText" style="border:none;"><?php echo '<div style="margin-bottom:8px;">'.TEXT_IM_PORTO.'</div>'.xtc_draw_pull_down_menu('product', $price_array, '', 'style="width:320px;"'); ?></td>
                  </tr>
                </table>
              </td>
              <td class="smallText" align="center">
                <div style="margin-bottom:8px;">&nbsp;</div>
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
      var im_service_formats = <?php echo json_encode($format_service_array, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

      $('#im_format').on('change', function() {
        update_im_positions();
      });

      $(document).ready(function() {
        update_im_positions();
      });

      function update_im_positions() {
        var im_format = $('#im_format').val();
        var data = im_service_formats[im_format];

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
      }
    </script>
    <?php
  }
