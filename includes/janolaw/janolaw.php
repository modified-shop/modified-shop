<?php
/* -----------------------------------------------------------------------------------------
   $Id: janolaw.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2010 Gambio OHG (janolaw.php 2010-06-08 gambio)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class janolaw {
  var $m_user_id = false;
  var $m_shop_id = false;

  var $m_cache_seconds = 7200; // new content reload after 2 hours

  function janolaw() {
    if(defined('MODULE_JANOLAW_USER_ID')) {
      $this->m_user_id = xtc_cleanName(MODULE_JANOLAW_USER_ID);
    }
    if(defined('MODULE_JANOLAW_SHOP_ID')) {
      $this->m_shop_id = xtc_cleanName(MODULE_JANOLAW_SHOP_ID);
    }
    if($this->get_status() == true) {
      // phantom call for creating checkout cache-file
      $this->get_page_content('agb', true, true, 'checkout-agb');
      $this->get_page_content('datenschutzerklaerung', true, true, 'checkout-datenschutzerklaerung');
      $this->get_page_content('impressum', true, true, 'checkout-impressum');
      $this->get_page_content('widerrufsbelehrung', true, true, 'checkout-widerrufsbelehrung');
    }
  }

  function get_status() {
    if(defined('MODULE_JANOLAW_STATUS') == false || MODULE_JANOLAW_STATUS == 'False') {
      // module not found or not activated.
      return false;
    }
    // module installed and active
    return true;
  }


  /**
   * write the content into files
   * $p_page_name can be agb, datenschutzerklaerung, impressum, wiederrufsbelehrung
   * $p_include_mode can be true = without css or false = with css from janolaw
   * $p_html_format can be true = html formated content or false =  text formated content
   * $p_cache_filename is the filename the content is stored

   * @param string $p_page_name
   * @param bool $p_include_mode
   * @param bool $p_html_format
   * @param string $p_cache_filename
   * @return void
   **/

  function get_page_content($p_page_name, $p_include_mode=true, $p_html_format=true, $p_cache_filename='') {

    $c_page_name = xtc_cleanName($p_page_name);

    if($p_include_mode) {
      $t_include_mode_suffix = '_include';
    } else {
      $t_include_mode_suffix = '';
    }

    if($p_html_format) {
      $t_format_suffix = 'html';
    } else {
      $t_format_suffix = 'txt';
    }


    if($p_cache_filename != '') {
      $t_cache_file = DIR_FS_CATALOG . 'media/content/'. xtc_cleanName($p_cache_filename) .'.'. $t_format_suffix;
    }

    $t_create_cache = false;

    if(file_exists($t_cache_file) == false) {
      $t_create_cache = true;
    } elseif(filesize($t_cache_file) < 100) {
      $t_create_cache = true;
    } elseif(filemtime($t_cache_file) < time() - $this->m_cache_seconds) {
      $t_create_cache = true;
    }

    // load page and create cache
    if($t_create_cache) {
      // build source url for getting page content
      $protocol = 'http://';
      $domain = 'www.janolaw.de';
      $t_source_url = $protocol . $domain . '/agb-service/shops/'.
                      $this->m_user_id .'/'.
                      $this->m_shop_id .'/'.
                      $c_page_name.
                      $t_include_mode_suffix.'.'.$t_format_suffix;

      $urlcheck = false;
      if(function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $protocol . $domain);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch,CURLOPT_VERBOSE,false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $execution = curl_exec($ch);
        $errorno = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($errorno>=200 && $errorno<300) {
          $urlcheck = true;
        }
      }

      if ($urlcheck == true) {

        $t_content = '';

        // load page from janolaw site
        if(function_exists('file_get_contents')) {
          $t_content .= @file_get_contents($t_source_url);
        }

        if(empty($t_content) && function_exists('curl_init')) {
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $t_source_url);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $t_content .= curl_exec($ch);
          curl_close($ch);
        }

        // looking for success
        if($t_content != false || strlen($t_content) > 100) {
          // write page content to cache file on success
          $fp = fopen($t_cache_file, 'w+');
          fwrite($fp, $t_content);
          fclose($fp);
        }
      }
    }
    // return file name
    return $t_cache_file;
  }
}
?>