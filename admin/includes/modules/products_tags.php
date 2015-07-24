<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
?>

<div style="padding:5px">

  <?php  
  $options_query = xtc_db_query("SELECT *
                                   FROM " . TABLE_PRODUCTS_TAGS_OPTIONS . "
                                  WHERE languages_id = '".$_SESSION['languages_id']."'
                                    AND (filter = '1' OR status = '1')
                               ORDER BY sort_order, options_name, options_description");
  
  if (xtc_db_num_rows($options_query) > 0) {
    $module_content = array();
    while ($options = xtc_db_fetch_array($options_query)) {
      $values_query = xtc_db_query("SELECT *
                                      FROM " . TABLE_PRODUCTS_TAGS_VALUES . "
                                     WHERE options_id = '".$options['options_id']."'
                                       AND languages_id = '".$_SESSION['languages_id']."'
                                  ORDER BY sort_order, values_name, values_description");

      if (xtc_db_num_rows($values_query) > 0) {        
        $module_values_content = array();
        while ($values = xtc_db_fetch_array($values_query)) {
          $module_values_content[] = array('checkbox' => xtc_draw_checkbox_field('product_tags['.$options['options_id'].']['.$values['values_id'].']', 'on', ((xtc_get_tags_status((int)$_GET['pID'], $options['options_id'], $values['values_id']) == 1) ? true : false)),
                                           'title' => (($values['values_name'] != '') ? $values['values_name'] : $values['values_description'])
                                           );
        }                        
        $module_content[] = array('title' => (($options['options_name'] != '') ? $options['options_name'] : $options['options_description']),
                                  'content' => $module_values_content
                                  );
      }
    }
  }
  
  if (count($module_content) > 0) {
    ?>
    <script type="text/javascript" src="includes/lang_tabs_menu/tag_tabs_menu.js"></script>
    <br/>
    <div style="padding:5px;clear:both;">
      <?php
      $langtabs = '<div class="tablangmenu"><ul class="ultabs">';
      $csstabstyle = 'border: 1px solid #aaaaaa; padding: 4px; width: 99%; margin-top: -1px; margin-bottom: 10px; float: left;background: #F3F3F3;';
      $csstab = '<style type="text/css">' .  '#tab_tag_0' . '{display: block;' . $csstabstyle . '}';
      $csstab_nojs = '<style type="text/css">';
      for ($i = 0, $n = sizeof($module_content); $i < $n; $i++) {
        $tabtmp = "\'tab_tag_$i\'," ;
        $langtabs.= '<li onclick="showTabTag('. $tabtmp. $n.')" style="cursor: pointer;" id="tab_tag_select_' . $i .'">' . $module_content[$i]['title'].  '</li>';
        if($i > 0) $csstab .= '#tab_tag_' . $i .'{display: none;' . $csstabstyle . '}';
        $csstab_nojs .= '#tab_tag_' . $i .'{display: block;' . $csstabstyle . '}';
      }
      $csstab .= '</style>';
      $csstab_nojs .= '</style>';
      $langtabs.= '</ul></div>';
      ?>
      <?php if (USE_ADMIN_LANG_TABS != 'false') { ?>
      <script type="text/javascript">
        document.write('<?php echo ($csstab);?>');
        document.write('<?php echo ($langtabs);?>');
      </script>
      <?php 
      } else { 
        echo ($csstab_nojs);
      }
      ?>
      <noscript>
        <?php echo ($csstab_nojs);?>
      </noscript>
      <?php
      for ($i = 0, $n = sizeof($module_content); $i < $n; $i++) {
        echo ('<div id="tab_tag_' . $i . '">');
        ?>
        <div class="main" style="padding: 3px; line-height:20px;">
          <?php
            foreach ($module_content[$i]['content'] as $content) {
              echo '<div style="float:left;min-width:150px;">'.$content['checkbox'] . ' ' . $content['title'].'</div>';
            }
          ?>
          <div style="clear:both;"></div>
        </div>
        <?php
        echo ('</div>');
      }
      ?>
    </div>
    <div style="clear:both;"></div>
    <?php
  }
  
  function xtc_get_tags_status($products_id, $options_id, $values_id) {
    $tags_query = xtc_db_query("SELECT *
                                  FROM ".TABLE_PRODUCTS_TAGS."
                                 WHERE products_id = '".$products_id."'
                                   AND options_id = '".$options_id."'
                                   AND values_id = '".$values_id."'");
    return xtc_db_num_rows($tags_query);
  }
?>