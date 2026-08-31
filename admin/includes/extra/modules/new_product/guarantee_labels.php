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
      )
  {
    // manufacturer and model are edited above, they are repeated here because both end up on the label
    $guarantee_manufacturer = '';
    if ((int)$pInfo->manufacturers_id > 0) {
      foreach ($manufacturers_array as $guarantee_entry) {
        if ($guarantee_entry['id'] == $pInfo->manufacturers_id) {
          $guarantee_manufacturer = $guarantee_entry['text'];
          break;
        }
      }
    }

    $guarantee_model = trim((string)$pInfo->products_manufacturers_model);

    // the pull down above lists every manufacturer, the label only takes an active one
    $guarantee_manufacturer_active = true;
    if ((int)$pInfo->manufacturers_id > 0) {
      $guarantee_status_query = xtc_db_query("SELECT manufacturers_status
                                                FROM ".TABLE_MANUFACTURERS."
                                               WHERE manufacturers_id = '".(int)$pInfo->manufacturers_id."'");

      if (xtc_db_num_rows($guarantee_status_query) > 0) {
        $guarantee_status = xtc_db_fetch_array($guarantee_status_query);
        $guarantee_manufacturer_active = ((int)$guarantee_status['manufacturers_status'] === 1);
      }
    }

    // the guarantee conditions are kept as a regular article attachment, so only their state is shown
    $guarantee_terms = array();
    if ((int)$pInfo->products_id > 0) {
      $guarantee_terms_query = xtc_db_query("SELECT languages_id,
                                                    content_name,
                                                    content_file
                                               FROM ".TABLE_PRODUCTS_CONTENT."
                                              WHERE products_id = '".(int)$pInfo->products_id."'
                                                AND content_type = 'garan_terms'");
      while ($guarantee_terms_data = xtc_db_fetch_array($guarantee_terms_query)) {
        // a row without its file is worse than no row: it looks maintained and sends nothing
        $guarantee_terms_data['file_exists'] = is_file(DIR_FS_CATALOG.'media/products/'.$guarantee_terms_data['content_file']);
        $guarantee_terms[(int)$guarantee_terms_data['languages_id']] = $guarantee_terms_data;
      }
    }
    ?>
    <div style="clear:both;"></div>
    <div class="main div_header"><b><?php echo TEXT_GUARANTEE_LABELS_HEADING; ?></b><?php echo draw_tooltip(TEXT_GUARANTEE_LABELS_INFO); ?></div>
    <div class="clear div_box mrg5">
      <table class="tableInput border0">
        <tr>
          <td style="width:250px; line-height: 35px;"><span class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER; ?></span></td>
          <td><span class="main"><?php
            echo ($guarantee_manufacturer !== '') ? encode_htmlspecialchars($guarantee_manufacturer) : TEXT_GUARANTEE_LABELS_NONE;
            if ($guarantee_manufacturer !== '' && $guarantee_manufacturer_active === false) {
              echo ' <span class="error">'.TEXT_GUARANTEE_LABELS_MANUFACTURER_INACTIVE.'</span>';
            }
          ?></span></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER_MODEL; ?></span></td>
          <td><span class="main"><?php echo ($guarantee_model !== '') ? encode_htmlspecialchars($guarantee_model) : TEXT_GUARANTEE_LABELS_NONE; ?></span></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_GUARANTEE_LABELS_DURATION; ?></span><?php echo draw_tooltip(TEXT_GUARANTEE_LABELS_INFO_RULES); ?></td>
          <td><span class="main"><?php
            echo xtc_draw_input_field('products_garan_duration', $pInfo->products_garan_duration, 'style="width: 155px"');
            if ((int)$pInfo->products_id > 0) {
              require_once(DIR_FS_INC.'guarantee_labels_output.inc.php');
              if (guarantee_labels_product_physical($pInfo->products_id) === false) {
                echo '<br /><span class="error">'.TEXT_GUARANTEE_LABELS_VIRTUAL.'</span>';
              }
            }
          ?></span></td>
        </tr>
        <?php if ((int)$pInfo->products_id > 0) { ?>
        <tr>
          <td style="vertical-align:top; padding-top:8px;"><span class="main"><?php echo TEXT_GUARANTEE_LABELS_TERMS; ?></span><?php echo draw_tooltip(TEXT_GUARANTEE_LABELS_INFO_TERMS); ?></td>
          <td>
            <span class="main">
              <?php
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $guarantee_language_id = (int)$languages[$i]['id'];
                  echo xtc_image(DIR_WS_LANGUAGES.$languages[$i]['directory'].'/admin/images/'.$languages[$i]['image'], $languages[$i]['name']).' ';

                  if (!isset($guarantee_terms[$guarantee_language_id])) {
                    echo TEXT_GUARANTEE_LABELS_TERMS_MISSING;
                  } elseif ($guarantee_terms[$guarantee_language_id]['file_exists'] === false) {
                    echo '<span class="error">'.sprintf(TEXT_GUARANTEE_LABELS_TERMS_FILE_MISSING, encode_htmlspecialchars($guarantee_terms[$guarantee_language_id]['content_name'])).'</span>';
                  } else {
                    echo encode_htmlspecialchars($guarantee_terms[$guarantee_language_id]['content_name']);
                  }

                  echo '<br />';
                }
              ?>
            </span>
          </td>
        </tr>
        <?php } ?>
      </table>
    </div>
    <?php
  }
