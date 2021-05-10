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
            if ($tracking['external'] == '1' && $tracking['sc_id'] != '') {
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
          require_once(DIR_FS_EXTERNAL.'shipcloud/class.shipcloud.php');
          $shipcloud = new shipcloud($oID);
          
          $sc_carriers = array();
          $sc_carriers_array = $shipcloud->get_carriers();
          
          foreach ($sc_carriers_array as $sc_data) {
            $sc_carriers[] = array(
              'id' => $sc_data['name'],
              'text' => $sc_data['display_name'],
            );
          }
          $insurance_array = array(
            array('id' => '0', 'text' => TEXT_SHIPCLOUD_INSURANCE_NO),
            array('id' => '1', 'text' => TEXT_SHIPCLOUD_INSURANCE_YES),
          );
          $type_array = array(
            array('id' => 'parcel', 'text' => TEXT_SHIPCLOUD_PARCEL),
          );

          echo '<td class="smallText" align="center" colspan="2"><span id="sc_data">';
          echo xtc_draw_pull_down_menu('carrier_id', $sc_carriers, $sc_carriers[0]['id'], 'id="sc_carrier"').'&nbsp;';
          echo xtc_draw_pull_down_menu('service', $service_array, $service_array[0]['id'], 'id="sc_service"').'&nbsp;';
          echo xtc_draw_pull_down_menu('type', $type_array, $type_array[0]['id'], 'id="sc_type"').'&nbsp;';
          echo xtc_draw_pull_down_menu('parcel', $parcel_array, $parcel_array[0]['id']).'&nbsp;';
          if ($order->info['pp_total'] > '500') {
            echo xtc_draw_pull_down_menu('insurance', $insurance_array, $insurance_array[0]['id'], 'id="sc_insurance"').'&nbsp;';
          }
          echo xtc_draw_input_field('weight', '' , 'style="width:100px;vertical-align:top;" placeholder="'.TEXT_WEIGHT_PLACEHOLDER.'"').'</span>';
          echo xtc_draw_input_field('description_1', '' , 'id="sc_description_1" style="width:570px;vertical-align:top;" placeholder="'.TEXT_CARRIER_PLACEHOLDER_1.'"');
          echo xtc_draw_input_field('description_2', '' , 'id="sc_description_2" style="width:570px;vertical-align:top;" placeholder="'.TEXT_CARRIER_PLACEHOLDER_2.'"');
          echo '</td>';
        ?>
        <td class="smallText" align="center">
          <input class="button" style="display:block; width:155px; margin: 7px auto" type="submit" value="<?php echo CREATE_LABEL; ?>">
          <input class="button" style="display:block; width:155px; margin: 7px auto" type="submit" name="quote" value="<?php echo CHECK_LABEL_PRICE; ?>">
        </td>
      </tr>
    </table>
    </form>
    <script type="text/javascript">
      $('#sc_carrier').on('change', function() {
        get_sc_service();
      });

      $(document).ready(function(){
        get_sc_service();
      });

      function get_sc_service() {
        var sc_carrier = $('#sc_carrier').val();
        var lang = "<?php echo $_SESSION['language_code']; ?>";

        $.get('../ajax.php', {ext: 'get_sc_service', carrier: sc_carrier, language: lang, speed: 1}, function(data) {
          if (data != '' && data != undefined) { 
            <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
              $('#sc_service').replaceWith('<select id="sc_service" name="service" class="SlectBox" style="visibility: hidden;"></select>');
              $('#sc_service').nextAll('.optWrapper').replaceWith('<div class="optWrapper"><ul class="options" id="service"></ul></div>');
              $('#sc_type').replaceWith('<select id="sc_type" name="type" class="SlectBox" style="visibility: hidden;"></select>');
              $('#sc_type').nextAll('.optWrapper').replaceWith('<div class="optWrapper"><ul class="options" id="type"></ul></div>');
            <?php } else { ?>
              $('#sc_service').replaceWith('<select id="sc_service" name="service" class="SlectBox"></select>');
              $('#sc_type').replaceWith('<select id="sc_type" name="type" class="SlectBox"></select>');        
            <?php } ?>
        
            $.each(data.carrier, function(id, arr) {
              $('<option value="'+arr.id+'">'+arr.text+'</option>').appendTo('#sc_service');
              <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
                $('<li data-val="'+arr.id+'"><label>'+arr.text+'</label></li>').appendTo('#service');        
              <?php } ?>
            });

            $.each(data.parcel, function(id, arr) {
              $('<option value="'+arr.id+'">'+arr.text+'</option>').appendTo('#sc_type');
              <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
                $('<li data-val="'+arr.id+'"><label>'+arr.text+'</label></li>').appendTo('#type');        
              <?php } ?>
            });
      
            if (sc_carrier != 'dhl' && sc_carrier != 'dhl_express') {
              $('#sc_insurance').hide();
              if (sc_carrier == 'ups') {
                $('#sc_description_1').show();
                $('#sc_description_2').show();
              } else {
                $('#sc_description_1').hide();
                $('#sc_description_2').hide();
              }
            } else {
              if (sc_carrier != 'dhl') {
                $('#sc_insurance').hide();
              } else {
                $('#sc_insurance').show();
              }
              $('#sc_description_1').hide();
              $('#sc_description_2').show();
            }

            <?php if (NEW_SELECT_CHECKBOX == 'true') { ?>
              $('.SlectBox').not('.noStyling').SumoSelect({ createElems: 'mod', placeholder: '-'});
              if (sc_carrier != 'dhl') {
                $('#sc_insurance').nextAll('.SlectBox').hide();
              } else {
                $('#sc_insurance').nextAll('.SlectBox').show();
              }
            <?php } ?>
        
            $('#sc_description_1').css('width', $('#sc_data').width());
            $('#sc_description_2').css('width', $('#sc_data').width());
          }
        });
      }
    </script>
    <?php
  }
?>