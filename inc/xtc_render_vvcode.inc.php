<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function vvcode_render_code($code) {
  if (!empty($code)) {

    // load fonts
    $ttf=array();
    if ($dir= opendir(DIR_WS_INCLUDES.'fonts/')){
      while  (($file = readdir($dir)) !==false) {
        if (is_file(DIR_WS_INCLUDES.'fonts/'.$file) and (strstr(strtoupper($file),'.TTF'))){
            $ttf[]=DIR_FS_CATALOG.'/includes/fonts/'.$file;
        }
      }
      closedir($dir);
    }
    $width = 240;
    $height = 50;

    $imgh = imagecreate($width, $height);

    $background = imagecolorallocate($imgh, 196, 196, 196);
    $fonts = imagecolorallocate($imgh, 112, 112, 112);
    $lines = imagecolorallocate($imgh, 220, 148, 002);
    imagefill($imgh, 0, 0, $background);

    $x = mt_rand(0, 20);
    $y = mt_rand(20, 40);
    for ($i = $x, $z = $y; $i < $width && $z < $width;) {
        imageLine($imgh, $i, 0, $z, $height, $lines);
        $i += $x;
        $z += $y;
    }

    $x = mt_rand(0, 20);
    $y = mt_rand(20, 40);
    for ($i = $x, $z = $y; $i < $width && $z < $width;) {
        imageLine($imgh, $z, 0, $i, $height, $lines);
        $i += $x;
        $z += $y;
    }

    $x = mt_rand(0, 10);
    $y = mt_rand(10, 20);
    for ($i = $x, $z = $y; $i < $height && $z < $height;) {
        imageLine($imgh, 0, $i, $width, $z, $lines);
        $i += $x;
        $z += $y;
    }

    $x = mt_rand(0, 10);
    $y = mt_rand(10, 20);
    for ($i = $x, $z = $y; $i < $height && $z < $height;) {
        imageLine($imgh, 0, $z, $width, $i, $lines);
        $i += $x;
        $z += $y;
    }

    for ($i = 0; $i < strlen($code); $i++) {
        $font = $ttf[(int)mt_rand(0, count($ttf)-1)];
        $size = mt_rand(30, 36);
        $rand = mt_rand(1,20);
        $direction = mt_rand(0,1);

      if ($direction == 0) {
       $angle = 0-$rand;
      } else {
         $angle = $rand;
      }
      if (function_exists('imagettftext')) {
              imagettftext($imgh, $size, $angle, 15+(36*$i) , 38, $fonts, $font, substr($code, $i, 1));
      } else {
        $tc = ImageColorAllocate ($imgh, 0, 0, 0); //Schriftfarbe - schwarz
              ImageString($imgh, $size, 26+(36*$i),20, substr($code, $i, 1), $tc);
      }
    }

    header('Content-Type: image/jpeg');
    imagejpeg($imgh);
    imagedestroy($imgh);
  }
}
?>