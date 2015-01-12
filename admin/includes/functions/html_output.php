<?php
  /* --------------------------------------------------------------
   $Id: html_output.php 4250 2013-01-11 15:09:59Z gtb-modified $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.26 2002/08/06); www.oscommerce.com
   (c) 2003 nextcommerce (html_output.php,v 1.7 2003/08/18); www.nextcommerce.org
   (c) 2006 xt-commerce (html_output.php 1125 2005-07-28)

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  // The HTML href link wrapper function
  function xtc_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session = true) {
  
    $parameters = str_replace('&amp;', '&', $parameters); //  making link W3C-Conform
  
    if (!xtc_not_null($page)) {
      die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine the page link!<br /><br />Function used:<br /><br />xtc_href_link(\'' . $page . '\', \'' . $parameters . '\', \'' . $connection . '\')</strong>');
    }
    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_ADMIN;
    } elseif ($connection == 'SSL') {
      if (defined('ENABLE_SSL') && ENABLE_SSL == 'true') {
        $link = (defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTPS_CATALOG_SERVER) . DIR_WS_ADMIN;
      } else {
        $link = HTTP_SERVER . DIR_WS_ADMIN;
      }
    } else {
      die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL<br /><br />Function used:<br /><br />xtc_href_link(\'' . $page . '\', \'' . $parameters . '\', \'' . $connection . '\')</strong>');
    }
    if ($parameters == '') {
      $link .= $page . (($add_session === true) ? '?' . SID : '');
    } else {
      $link .= $page . '?' . $parameters . (($add_session === true) ? '&' . SID : '');
    }
    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') )
      $link = substr($link, 0, -1);
    
    $link = str_replace('&', '&amp;', $link); // making link W3C-Conform
  
    return $link;
  }

  // The HTML href link wrapper function for frontend
  function xtc_catalog_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session = false) {
  
    $parameters = str_replace('&amp;', '&', $parameters); //  making link W3C-Conform

    if (!xtc_not_null($page)) {
      $page = 'index.php';
    }
    
    if ($page == 'index.php' && !xtc_not_null($parameters)) {
      $page = '';
    }

    if ($connection == 'NONSSL') {
      $link = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL_CATALOG == 'true') {
        $link = HTTPS_CATALOG_SERVER . DIR_WS_CATALOG;
      } else {
        $link = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
      }
    } else {
      die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL<br /><br />Function used:<br /><br />xtc_href_link(\'' . $page . '\', \'' . $parameters . '\', \'' . $connection . '\')</strong>');
    }
    if ($parameters == '') {
      $link .= $page . (($add_session === true) ? '?' . SID : '');
    } else {
      $link .= $page . '?' . $parameters . (($add_session === true) ? '&' . SID : '');
    }
    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') )
      $link = substr($link, 0, -1);
      
    $link = str_replace('&', '&amp;', $link); // making link W3C-Conform
  
    return $link;
  }

  // The HTML image wrapper function
  function xtc_image($src, $alt = '', $width = '', $height = '', $params = '') {
    $params  = preg_replace("'\s+=\s+'",'=',$params);
    $params  = preg_replace("'\s+:\s+'",'=',$params);
    if (strpos($params,'style="') !== false &&  strpos($params,'border:') === false) {
      $params = str_replace('style="','style="border:0;',$params);
    } else {
      $params .= ' style="border:0;"';
    }
    $image = '<img src="' . $src . '" alt="' . $alt . '"';
    if ($alt) {
      $image .= ' title="' . $alt . '"';
    }
    if ($width) {
      $image .= ' width="' . $width . '"';
    }
    if ($height) {
      $image .= ' height="' . $height . '"';
    }
    if ($params) {
      $image .= ' ' . $params;
    }
    $image .= '>';
    return $image;
  }

  // Draw a 1 pixel black line
  function xtc_black_line() {
    return xtc_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '1');
  }

  // Output a separator either through whitespace, or with an image
  function xtc_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return xtc_image(DIR_WS_IMAGES . $image, '', $width, $height);
  }

  // javascript to dynamically update the states/provinces list when the country is changed
  // TABLES: zones
  function xtc_js_zone_list($country, $form, $field) {
    $countries_query = xtc_db_query("select distinct zone_country_id from " . TABLE_ZONES . " order by zone_country_id");
    $num_country = 1;
    $output_string = '';
    while ($countries = xtc_db_fetch_array($countries_query)) {
      if ($num_country == 1) {
        $output_string .= '  if (' . $country . ' == "' . $countries['zone_country_id'] . '") {' . "\n";
      } else {
        $output_string .= '  } else if (' . $country . ' == "' . $countries['zone_country_id'] . '") {' . "\n";
      }
      $states_query = xtc_db_query("select zone_name, zone_id from " . TABLE_ZONES . " where zone_country_id = '" . $countries['zone_country_id'] . "' order by zone_name");
      $num_state = 1;
     while ($states = xtc_db_fetch_array($states_query)) {
        if ($num_state == '1') $output_string .= '    ' . $form . '.' . $field . '.options[0] = new Option("' . html_entity_decode(PLEASE_SELECT, ENT_COMPAT, strtoupper($_SESSION['language_charset']))  . '", "");' . "\n";
        $output_string .= '    ' . $form . '.' . $field . '.options[' . $num_state . '] = new Option("' . $states['zone_name'] . '", "' . $states['zone_id'] . '");' . "\n";
        $num_state++;
      }
      $num_country++;
    }
    $output_string .= '  } else {' . "\n" .
                      '    ' . $form . '.' . $field . '.options[0] = new Option("' . html_entity_decode(TYPE_BELOW, ENT_COMPAT, strtoupper($_SESSION['language_charset'])). '", "");' . "\n" .
                      '  }' . "\n";
    return $output_string;
  }

  // Output a form
  function xtc_draw_form($name, $action, $parameters = '', $method = 'post', $params = '') {
    $form = '<form name="' . $name . '" action="';
    if ($parameters) {
      $form .= xtc_href_link($action, $parameters);
    } else {
      $form .= xtc_href_link($action);
    }
    $form .= '" method="' . $method . '"';
    if ($params) {
      $form .= ' ' . $params;
    }
    $form .= '>';
    
    // secure form with a random token
    if (isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName']) && $method == 'post') {
      $form .= '<input type="hidden" name="'.$_SESSION['CSRFName'].'" value="'.$_SESSION['CSRFToken'].'">';
    }

    return $form;
  }

  // Output a form input field
  function xtc_draw_input_field($name, $value = '', $parameters = '', $required = false, $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . $type . '" name="' . $name . '"';
    if ( isset($GLOBALS[$name]) && ($reinsert_value) ) {
      $field .= ' value="' . encode_htmlspecialchars(trim($GLOBALS[$name])) . '"';
    } elseif ($value != '') {
      $field .= ' value="' . encode_htmlspecialchars(trim($value)) . '"';
    }
    if ($parameters != '') {
      $field .= ' ' . $parameters;
    }
    $field .= '>';
    if ($required)
      $field .= TEXT_FIELD_REQUIRED;
    return $field;
  }

  // Output a form small input field
  function xtc_draw_small_input_field($name, $value = '', $parameters = '', $required = false, $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . $type . '" size="3" name="' . $name . '"';
    if ( isset($GLOBALS[$name]) && ($reinsert_value) ) {
      $field .= ' value="' . encode_htmlspecialchars(trim($GLOBALS[$name])) . '"';
    } elseif ($value != '') {
      $field .= ' value="' . encode_htmlspecialchars(trim($value)) . '"';
    }
    if ($parameters != '') {
      $field .= ' ' . $parameters;
    }
    $field .= '>';
    if ($required)
      $field .= TEXT_FIELD_REQUIRED;
    return $field;
  }

  // Output a form password field
  function xtc_draw_password_field($name, $value = '', $required = false, $parameters = '') {
    $params = strpos($parameters,'maxlength') !== false ? '' : 'maxlength="40"';
    if ($parameters != '') {
      $params .= ' ' . $parameters;
    }
    $field = xtc_draw_input_field($name, $value, $params, $required, 'password', false);
    return $field;
  }

  // Output a form filefield
  function xtc_draw_file_field($name, $required = false,$parameters = '') {
    $field = xtc_draw_input_field($name, '', $parameters, $required, 'file');
    return $field;
  }

  // Output a selection field - alias function for xtc_draw_checkbox_field() and xtc_draw_radio_field()
  function xtc_draw_selection_field($name, $type, $value = '', $checked = false, $compare = '', $parameters = '') {
    $selection = '<input type="' . $type . '" name="' . $name . '"';
    if ($value != '') {
      $selection .= ' value="' . $value . '"';
    }
    if ( ($checked == true) || (isset($GLOBALS[$name]) && ($GLOBALS[$name] == 'on')) || ($value && isset($GLOBALS[$name]) && ($GLOBALS[$name] == $value)) || ($value && ($value == $compare)) ) {
      $selection .= ' checked="checked"';
    }
    
    if (xtc_not_null($parameters)) $selection .= ' ' . $parameters;
    
    $selection .= '>';
    return $selection;
  }

  // Output a form checkbox field
  function xtc_draw_checkbox_field($name, $value = '', $checked = false, $compare = '', $parameters = '') {
    return xtc_draw_selection_field($name, 'checkbox', $value, $checked, $compare, $parameters);
  }

  // Output a form radio field
  function xtc_draw_radio_field($name, $value = '', $checked = false, $compare = '', $parameters = '') {
    return xtc_draw_selection_field($name, 'radio', $value, $checked, $compare, $parameters);
  }

  // Output a form textarea field
  function xtc_draw_textarea_field($name, $wrap, $width, $height, $text = '', $params = '', $reinsert_value = true) {
    $field = '<textarea id="'.$name.'" name="' . $name . '" wrap="' . $wrap . '" cols="' . $width . '" rows="' . $height . '"';
    if ($params) $field .= ' ' . $params;
    $field .= '>';
    if ( isset($GLOBALS[$name]) && ($reinsert_value) ) {
      $field .= $GLOBALS[$name];
    } elseif ($text != '') {
      $field .= $text;
    }
    $field .= '</textarea>';
    return $field;
  }

  // Output a form hidden field
  function xtc_draw_hidden_field($name, $value = '') {
    $field = '<input type="hidden" name="' . $name . '" value="';
    if ($value != '') {
      $field .= trim($value);
    } else {
      $field .= trim(isset($GLOBALS[$name])?$GLOBALS[$name]:'');
    }
    $field .= '">';
    return $field;
  }

  // Output a form pull down menu
  function xtc_draw_pull_down_menu($name, $values, $default = '', $params = '', $required = false) {
    $field = '<select name="' . $name . '"';
    if ($params) $field .= ' ' . $params;
    $field .= '>';
    if (is_array($values)) {
      foreach ($values as $key=>$val) {
        $field .= '<option value="' .$val['id'] . '"';
        if ( ((strlen($val['id']) > 0) && isset($GLOBALS[$name]) && ($GLOBALS[$name] == $val['id'])) || ($default == $val['id']) ) {
          $field .= ' SELECTED';
        }
        $field .= '>' . $val['text'] . '</option>';
      }
    }
    $field .= '</select>';
    if ($required)
      $field .= TEXT_FIELD_REQUIRED;
    return $field;
  }

  /**
   * xtc_sorting()
   *
   * @param string $page, $sort
   * @return string (2 sorting arrows)
   */
  function xtc_sorting($page,$sort) {
    $nav= '<br /><a href="'.xtc_href_link($page, xtc_get_all_get_params(array('action','sorting')).'sorting='.$sort).'">';
    $nav.= xtc_image(DIR_WS_ICONS . 'sort_down.gif', '', '20' ,'20').'</a>';
    $nav.= '<a href="'.xtc_href_link($page, xtc_get_all_get_params(array('action','sorting')).'sorting='.$sort.'-desc').'">';
    $nav.= xtc_image(DIR_WS_ICONS . 'sort_up.gif', '', '20' ,'20').'</a>';    
    return $nav;
  }
  
  /**
   * draw_input_per_page()
   *
   * @param string $PHP_SELF, $cfg_max_display_results_key
   * @param integer $page_max_display_results
   * @return string
   */
  function draw_input_per_page($PHP_SELF,$cfg_max_display_results_key,$page_max_display_results) {
    $output = '<div class="clear"></div>'. PHP_EOL;
    $output .= '<div class="smallText pdg2 flt-l">'. PHP_EOL;
    $output .= xtc_draw_form('cfg_max', basename($PHP_SELF)). PHP_EOL;         
    $output .= DISPLAY_PER_PAGE.xtc_draw_input_field($cfg_max_display_results_key, $page_max_display_results, 'style="width: 40px"'). PHP_EOL; 
    $output .= '<input type="submit" class="button" onclick="this.blur();" value="' . BUTTON_SAVE . '"/>'. PHP_EOL; 
    $output .=  '</form>'. PHP_EOL; 
    $output .= '</div>'. PHP_EOL; 
    return $output;
  }
  
  /**
   * draw_tooltip()
   *
   * @param string $text
   * @return string
   */
  function draw_tooltip($text) {
    $output = '<span class="tooltip"><span>'.xtc_image(DIR_WS_ICONS.'tooltip_icon.png').'<em>'.$text.'</em></span></span>'. PHP_EOL; 
    return $output;
  }
?>