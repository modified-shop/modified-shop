<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


defined('ENCODE_DEFINED_CHARSETS') or define('ENCODE_DEFINED_CHARSETS','ISO-8859-1,ISO-8859-15,UTF-8,cp866,cp1251,cp1252,KOI8-R,BIG5,GB2312,BIG5-HKSCS,Shift_JIS,EUC-JP'); 
defined('ENCODE_DEFAULT_CHARSET') or define('ENCODE_DEFAULT_CHARSET', 'ISO-8859-15');

/*
 * helper functions
 */
function get_third_party_payments() {
  $payment_allowed = explode(';', MODULE_PAYMENT_PAYPAL_PLUS_THIRDPARTY_PAYMENT);
    
  require_once (DIR_FS_CATALOG . 'includes/classes/payment.php');
  $payment_modules = new payment;

  $selection = array();
  if (is_array($payment_modules->modules)) {
    reset($payment_modules->modules);
    while (list(, $value) = each($payment_modules->modules)) {
      $class = substr($value, 0, strrpos($value, '.'));
      if (isset($GLOBALS[$class]) && $GLOBALS[$class]->enabled && in_array($class, $payment_allowed)) {
        $module_selection = $GLOBALS[$class]->selection();
        if (is_array($module_selection)) {
          $selection[] = $module_selection;
        }
      }
    }
  }
  
  return $selection;
}


/*
 * compatibility functions
 */
if (!function_exists('draw_on_off_selection')) {
  function draw_on_off_selection($name, $select_array, $key_value, $params = '') {
    $string = '';
    for ($i = 0, $n = sizeof($select_array); $i < $n; $i++) {
      $string .= '<input id="'.$name.'_'.$i.'" type="radio" name="'.$name.'" value="'.$select_array[$i]['id'].'" '.$params;
      if ($key_value == $select_array[$i]['id']) $string .= ' checked="checked"';
      $string .= '><label for="'.$name.'_'.$i.'">'.$select_array[$i]['text'].'</label><br/>';
    }
    return $string;
  }
}


if (!function_exists('xtc_cfg_save_max_display_results')) {
  function xtc_cfg_save_max_display_results($cfg_key) {
    if (isset($_POST[$cfg_key])) {
      $configuration_value = preg_replace('/[^0-9-]/','',$_POST[$cfg_key]);
      $configuration_value = xtc_db_prepare_input($configuration_value);
      $configuration_query = xtc_db_query("SELECT configuration_key,
                                                  configuration_value
                                             FROM " . TABLE_CONFIGURATION . "
                                            WHERE configuration_key = '" . xtc_db_input($cfg_key) . "'
                                         ");
      if (xtc_db_num_rows($configuration_query) > 0) {
        //update
        xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                         SET configuration_value ='" . xtc_db_input($configuration_value) . "',
                             last_modified = NOW()
                       WHERE configuration_key='" . xtc_db_input($cfg_key) . "'");
      } else {
        //new entry
        $sql_data_array = array(
          'configuration_key' => $cfg_key,
          'configuration_value' => $configuration_value,
          'configuration_group_id' => '1000',
          'sort_order' => '-1',
          'last_modified' => 'now()',
          'date_added' => 'now()'
          );
        xtc_db_perform(TABLE_CONFIGURATION,$sql_data_array);
      }
      return $configuration_value;
    }
    return defined($cfg_key) && (int)constant($cfg_key) > 0 ? constant($cfg_key) : 20;
  }
}


if (!function_exists('encode_utf8')) {
  function encode_utf8($in_str) {
    if (strtolower($_SESSION['language_charset']) == 'utf-8') {
      $cur_encoding = mb_detect_encoding($in_str);
      if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8")) {
        return $in_str;
      } else {
        return mb_convert_encoding($in_str,"UTF-8","ISO-8859-15");
      }
    } else {
      return $in_str;
    }
  }
}


if (!function_exists('decode_utf8')) {
  function decode_utf8($in_str) {
    if (strtolower($_SESSION['language_charset']) != 'utf-8') {
      $cur_encoding = mb_detect_encoding($in_str);
      if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8")) {
        return mb_convert_encoding($in_str,"ISO-8859-15","UTF-8");
      } else {
        return $in_str;
      }
    } else {
      return $in_str;
    }
  }
}


if (!function_exists('draw_input_per_page')) {
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
}


if (!function_exists('decode_htmlentities')) {
  function decode_htmlentities ($string, $flags = ENT_COMPAT, $encoding = '') {
    $supported_charsets = explode(',',strtoupper(ENCODE_DEFINED_CHARSETS));  
    $default_charset = isset($_SESSION['language_charset']) && in_array(strtoupper($_SESSION['language_charset']), $supported_charsets) ? strtoupper($_SESSION['language_charset']) : ENCODE_DEFAULT_CHARSET;
    $encoding = !empty($encoding) && in_array(strtoupper($encoding), $supported_charsets) ? strtoupper($encoding) : $default_charset;
    return html_entity_decode($string, $flags , $encoding);
  }
}

?>