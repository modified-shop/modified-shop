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
  require_once (DIR_FS_INC . 'xtc_href_link.inc.php');

  // The HTML href link wrapper function for frontend
  function xtc_catalog_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session = false) {
    return xtc_href_link($page, $parameters, $connection, $add_session, true, true, true);
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
    $image = '<img src="' . $src . '"';
    if ($alt != '') {
      $image .= ' alt="' . $alt . '" title="' . $alt . '"';
    }
    if ($width != '') {
      $image .= ' width="' . $width . '"';
    }
    if ($height != '') {
      $image .= ' height="' . $height . '"';
    }
    if ($params != '') {
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
    $form = '<form name="' . $name . '"';
    $form .= ' action="'.xtc_href_link($action, $parameters, 'NONSSL' , false).'"';
    $form .= ' method="' . $method . '"';
    $form .= ($params ? ' ' . $params : '');
    $form .= '>';

    // add session if is in url
    if (isset($_GET[xtc_session_name()]) && $_GET[xtc_session_name()] == xtc_session_id()) {
      $form .= '<input type="hidden" name="'.xtc_session_name().'" value="'.xtc_session_id().'">';
    }
    // secure form with a random token
    if (CSRF_TOKEN_SYSTEM == 'true' && isset($_SESSION['CSRFToken']) && isset($_SESSION['CSRFName']) && strtolower($method) == 'post') {
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
    $parameters  = preg_replace("'\s+=\s+'",'=',$parameters);
    $parameters .= strpos($parameters,'id=') !== false ? '' : ' id='.$name;
    if (NEW_SELECT_CHECKBOX == 'true' && strpos($parameters,'noStyling') === false) {
      $parameters = (strpos($parameters,'class="') !== false ? str_replace('class="', 'class="fileInput ',$parameters) : $parameters . ' class="fileInput"');
    }
      
    $field = xtc_draw_input_field($name, '', $parameters, false, 'file');

    if (NEW_SELECT_CHECKBOX == 'true' && strpos($parameters,'noStyling') === false) {
      $input_txt = defined('FILEUPLOAD_INPUT_TXT') ? FILEUPLOAD_INPUT_TXT : 'No file';
      $btn_txt = defined('FILEUPLOAD_BTN_TXT') ? FILEUPLOAD_BTN_TXT : 'Search';
      $field = '
      <div class="inputBtnSection">
      <input id="finput_'.$name.'" class="disableInputField" placeholder="'.$input_txt.'" disabled="disabled" />
      <label class="fileUpload">
        '.$field.'
        <span class="uploadBtn">'.$btn_txt.'</span>
      </label>
      </div>';
    }
    if ($required)
      $field .= TEXT_FIELD_REQUIRED;
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
    $addtag = '';
    if (NEW_SELECT_CHECKBOX == 'true' && strpos($parameters,'noStyling') === false) {
      $addtag = '<em>&nbsp;</em>';
      $parameters  = preg_replace("'\s+=\s+'",'=',$parameters);
      $parameters = (strpos($parameters,'class="') !== false ? str_replace('class="', 'class="ChkBox ',$parameters) : $parameters . ' class="ChkBox"');
    }
    if (xtc_not_null($parameters)) $selection .= ' ' . $parameters;
    
    $selection .= '>'.$addtag;
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
  function xtc_draw_pull_down_menu($name, $values, $default = '', $params = '', $required = false, $addwrap = true) {
    $field = '<select name="' . $name . '"';
    if (!is_array($values) && $values == 'checkbox') {
      $values = array(
          array('id'=> 0,'text'=> CFG_TXT_NO),
          array('id'=> 1,'text'=> CFG_TXT_YES)
      );
    }
    if ($addwrap && NEW_SELECT_CHECKBOX == 'true' && strpos($params,'noStyling') === false) {
      $params  = preg_replace("'\s+=\s+'",'=',$params);
      $params = (strpos($params,'class="') !== false ? str_replace('class="', 'class="SlectBox ',$params) : $params . ' class="SlectBox"');
      $params = (strpos($params,'style="') !== false ? str_replace('style="', 'style="visibility: hidden; ',$params) : $params . ' style="visibility: hidden;"');
    }
    if ($params) $field .= ' ' . $params;
    $field .= '>' . PHP_EOL;
    $li = '';
    $selText = '';
    if (is_array($values)) {
      foreach ($values as $key=>$val) {
        $field .= '<option value="' .$val['id'] . '"';
        $li .= '<li data-val="' .$val['id'] . '"';
        if ( ((strlen($val['id']) > 0) && isset($GLOBALS[$name]) && ($GLOBALS[$name] == $val['id'])) || ($default == $val['id']) ) {
          $field .= ' selected="selected"';
          //$li .= ' class="selected"';
          $selText = $val['text'];
        }
        $field .= '>' . $val['text'] . '</option>' . PHP_EOL;
        $li .= '>'. PHP_EOL .'<label>' . $val['text'] . '</label>'. PHP_EOL . '</li>' . PHP_EOL;
      }
    }
    $field .= '</select>'. PHP_EOL;
    if ($required) {
      $field .= TEXT_FIELD_REQUIRED;
    }
    if ($addwrap && NEW_SELECT_CHECKBOX == 'true' && strpos($params,'noStyling') === false) {
      $name = str_replace(array('[',']'),array('_',''),$name); //fix for name is array:  example[...]
      $add = '<p class="CaptionCont SlectBox">'. PHP_EOL;
      $add .= '<span>'.$selText.'</span>'. PHP_EOL;
      $add .= '<label><i></i></label></p>'. PHP_EOL;
      $add .= '<div class="optWrapper">'. PHP_EOL . '<ul class="options">' . PHP_EOL . $li . PHP_EOL . '</ul>' . PHP_EOL . '</div>'. PHP_EOL;
      $field = '<div class="SumoSelect '. strtolower($name) .'" tabindex="0">' . $field . $add . '</div>';
    }
    return $field;
  }

  /**
   * xtc_sorting()
   *
   * @param string $page, $sort
   * @return string (2 sorting arrows)
   */
  function xtc_sorting($page,$sort) {
    $nav= '<br /><a href="'.xtc_href_link($page, xtc_get_all_get_params(array('action','sorting')).'sorting='.$sort).'" title="'.TEXT_SORT_ASC.'">';
    $nav.= xtc_image(DIR_WS_ICONS . 'sort_down.gif', TEXT_SORT_ASC, '20' ,'20').'</a>';
    $nav.= '<a href="'.xtc_href_link($page, xtc_get_all_get_params(array('action','sorting')).'sorting='.$sort.'-desc').'" title="'.TEXT_SORT_DESC.'">';
    $nav.= xtc_image(DIR_WS_ICONS . 'sort_up.gif', TEXT_SORT_DESC, '20' ,'20').'</a>';    
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
    $output .= '<input type="submit" class="button" onclick="this.blur();" title="' . BUTTON_SAVE . '" value="' . BUTTON_SAVE . '"/>'. PHP_EOL; 
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
    $output = '<span class="tooltip">'.xtc_image(DIR_WS_ICONS.'tooltip_icon.png').'<em>'.$text.'</em></span>'. PHP_EOL; 
    return $output;
  }
  
  /**
   * draw_on_off_selection()
   *
   * @param string $name
   * @param array $select_array
   * @param mixed $key_value
   * @param string $params
   * @return string
   */
  function draw_on_off_selection($name, $select_array, $key_value, $params = '') {
    $string = '';
    if (NEW_SELECT_CHECKBOX == 'true') {
      $string .= '<span class="cfg_select_option">';
    }
    if (!is_array($select_array) && $select_array == 'checkbox') {
      $select_array = array(
         array('id'=> 1,'text'=> CFG_TXT_YES),
         array('id'=> 0,'text'=> CFG_TXT_NO),
      );
    }
    for ($i = 0, $n = sizeof($select_array); $i < $n; $i++) {
      $string .= '<input id="cfg_so_'.strtolower($name).($i?"_$i":'').'" type="radio" name="'.$name.'" value="'.$select_array[$i]['id'].'"';
      if ($key_value == $select_array[$i]['id']) $string .= ' checked="checked"';
      $string .= ($params ? ' ' . $params : '');
      $string .= '><label for="cfg_so_'.strtolower($name).($i?"_$i":'').'" class="'.($key_value == $select_array[$i]['id'] ? 'cfg_so_before ':'').'cfg_sov_'.($select_array[$i]['id'] ? 'true' : 'false').'">';
      $string .= $select_array[$i]['text'] . '</label>';
      if (NEW_SELECT_CHECKBOX != 'true') {
        $string .= '<br/>';
      }
    }
    if (NEW_SELECT_CHECKBOX == 'true') {
      $string .= '</span>';
    }
    return $string;
  }
?>