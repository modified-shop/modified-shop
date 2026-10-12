<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class PayonePdf {

  /**
   * download a PDF and replace $file only if the download is complete and valid
   *
   * @return bool
   */
  public static function download($url, $file, $userpwd = '') {
    $tmp_file = tempnam(dirname($file), 'payone_pdf_');
    if ($tmp_file === false) {
      return false;
    }

    $fp = fopen($tmp_file, 'wb');
    if ($fp === false) {
      unlink($tmp_file);
      return false;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    if ($userpwd != '') {
      curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $success = (curl_errno($ch) == 0 && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200);
    fclose($fp);

    if ($success
        && self::isValid($tmp_file)
        && rename($tmp_file, $file)
        )
    {
      return true;
    }

    if (is_file($tmp_file)) {
      unlink($tmp_file);
    }
    return false;
  }


  /**
   * check that $file exists and starts with a PDF header
   *
   * @return bool
   */
  public static function isValid($file) {
    if (!is_file($file)) {
      return false;
    }

    $fp = fopen($file, 'rb');
    if ($fp === false) {
      return false;
    }
    $header = fread($fp, 5);
    fclose($fp);

    return ($header === '%PDF-');
  }
}
