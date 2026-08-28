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
    $guarantee_manufacturer = TEXT_GUARANTEE_LABELS_NONE;
    if ((int)$pInfo->manufacturers_id > 0) {
      foreach ($manufacturers_array as $guarantee_entry) {
        if ($guarantee_entry['id'] == $pInfo->manufacturers_id) {
          $guarantee_manufacturer = $guarantee_entry['text'];
          break;
        }
      }
    }

    $guarantee_model = trim((string)$pInfo->products_manufacturers_model);
    if ($guarantee_model === '') {
      $guarantee_model = TEXT_GUARANTEE_LABELS_NONE;
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
        $guarantee_terms[(int)$guarantee_terms_data['languages_id']] = $guarantee_terms_data;
      }
    }
    ?>
    <div style="clear:both;"></div>
    <div class="main div_header"><b><?php echo TEXT_GUARANTEE_LABELS_HEADING; ?></b><?php echo draw_tooltip(TEXT_GUARANTEE_LABELS_INFO); ?></div>
    <div class="clear div_box mrg5">
      <table class="tableInput border0">
        <tr>
          <td style="width:250px; line-height: 35px;"><span class="main"><?php echo TEXT_GUARANTEE_LABELS_MANUFACTURER; ?></span></td>
          <td><span class="main"><?php echo htmlspecialchars($guarantee_manufacturer); ?></span></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_GUARANTEE_LABELS_MODEL; ?></span></td>
          <td><span class="main"><?php echo htmlspecialchars($guarantee_model); ?></span></td>
        </tr>
        <tr>
          <td><span class="main"><?php echo TEXT_GUARANTEE_LABELS_DURATION; ?></span><?php echo draw_tooltip(TEXT_GUARANTEE_LABELS_INFO_RULES); ?></td>
          <td><span class="main"><?php echo xtc_draw_input_field('products_garan_duration', $pInfo->products_garan_duration, 'style="width: 155px"'); ?></span></td>
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

                  if (isset($guarantee_terms[$guarantee_language_id])) {
                    echo htmlspecialchars($guarantee_terms[$guarantee_language_id]['content_name']);
                  } else {
                    echo TEXT_GUARANTEE_LABELS_TERMS_MISSING;
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
