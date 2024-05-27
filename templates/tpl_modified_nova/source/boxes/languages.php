<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  // include smarty
  include(DIR_FS_BOXES_INC . 'smarty_default.php');

  // set cache id
  $cache_id = md5('lID:'.$_SESSION['language'].'|site:'.basename($PHP_SELF).'|params:'.xtc_get_all_get_params());

  if (!$box_smarty->is_cached(CURRENT_TEMPLATE.'/boxes/box_languages.html', $cache_id) || !$cache) {

    if (!isset($lng) || (isset($lng) && !is_object($lng))) {
      require_once(DIR_WS_CLASSES . 'language.php');
      $lng = new language;
    }

    if (count($lng->catalog_languages) > 1 && strpos(basename($PHP_SELF), 'checkout') === false) {

      $language_array = array();
      foreach ($lng->catalog_languages as $key => $value) {
        $lng_link_url = xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('language', 'currency')) . 'language=' . $key, $request_type);
        if ($lng_link_url != '#') {
          $language_array[] = array('id' => $key, 'text' => $value['name']);
        }
      }
    
      if (count($language_array) > 1) {
        $hidden_get_variables = '';

        $params_array = xtc_get_params_array(xtc_get_all_get_params(array('language', 'currency')));
        if (count($params_array) > 0) {
          foreach ($params_array as $k => $v) {
            if (is_array($v)) {
              foreach ($v as $kk => $vv) {
                $hidden_get_variables .= xtc_draw_hidden_field($k.'['.$kk.']', $vv);
              }
            } else {
              $hidden_get_variables .= xtc_draw_hidden_field($k, $v);
            }
          }
        }

        $box_content = xtc_draw_form('language', xtc_href_link(basename($PHP_SELF), '', $request_type, false), 'get', '')
                       . xtc_draw_pull_down_menu('language', $language_array, $_SESSION['language_code'], 'aria-label="language" onchange="this.form.submit();"')
                       . $hidden_get_variables . xtc_hide_session_id()
                       . '</form>';

        $box_smarty->assign('BOX_CONTENT', $box_content);
      }
    }
  }

  $box_languages = $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_languages.html', $cache_id);

  $smarty->assign('box_LANGUAGES', $box_languages);
