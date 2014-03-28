<?php
/* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
  //<link rel="stylesheet" type="text/css" href="includes/lang_tabs_menu/lang_tabs_menu.css">
  ?>
  <script type="text/javascript" src="includes/lang_tabs_menu/lang_tabs_menu.js"></script>
  <?php
  $langtabs = '<div class="tablangmenu"><ul>';
  $csstabstyle = 'border: 1px solid #aaaaaa; padding: 4px; width: 99%; margin-top: -1px; margin-bottom: 10px; float: left;background: #F3F3F3;';
  $csstab = '<style type="text/css">' .  '#tab_lang_0' . '{display: block;' . $csstabstyle . '}';
  $csstab_nojs = '<style type="text/css">';
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $tabtmp = "\'tab_lang_$i\'," ;
    $langtabs.= '<li onclick="showTab('. $tabtmp. $n.')" style="cursor: pointer;" id="tabselect_' . $i .'">' .xtc_image(DIR_WS_LANGUAGES . $languages[$i]['directory'] .'/admin/images/'. $languages[$i]['image'], $languages[$i]['name']) . ' ' . $languages[$i]['name'].  '</li>';
    if($i > 0) $csstab .= '#tab_lang_' . $i .'{display: none;' . $csstabstyle . '}';
    $csstab_nojs .= '#tab_lang_' . $i .'{display: block;' . $csstabstyle . '}';
  }
  $csstab .= '</style>';
  $csstab_nojs .= '</style>';
  $langtabs.= '</ul></div>';
  //echo $csstab;
  //echo $langtabs;
  ?>
  <?php if (USE_ADMIN_LANG_TABS != 'false') { ?>
  <script type="text/javascript">
    $.get("includes/lang_tabs_menu/lang_tabs_menu.css", function(css) {
      $("head").append("<style type='text/css'>"+css+"<\/style>");
    });
    document.write('<?php echo ($csstab);?>');
    document.write('<?php echo ($langtabs);?>');
    //alert ("TEST");
  </script>
  <?php 
  } else { 
    echo ($csstab_nojs);
  }
  ?>
  <noscript>
    <?php echo ($csstab_nojs);?>
  </noscript>
