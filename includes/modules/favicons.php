<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

$ws_dir = 'templates/'.CURRENT_TEMPLATE.'/favicons/';
$fs_dir = DIR_FS_CATALOG.$ws_dir;

if (is_dir($fs_dir)) {
  // favicon
  $favicon_array = glob($fs_dir.'favicon*');
  natcasesort($favicon_array);
  if (count($favicon_array) > 0) {
    foreach ($favicon_array as $favicon) {
      $favicon_type = pathinfo($favicon);
      $favicon = basename($favicon);
      preg_match('/(\d+)x(\d+)/', $favicon, $match);
      if (isset($match[0]) && $match[0] != '') {
        echo '<link rel="icon" type="image/'.$favicon_type['extension'].'" href="'.xtc_href_link($ws_dir.$favicon, '', $request_type, false).'" sizes="'.$match[0].'" />'."\n";
      } else {
        if ($favicon_type['extension'] == 'ico') {
          echo '<link rel="shortcut icon" type="image/x-icon" href="'.xtc_href_link($ws_dir.$favicon, '', $request_type, false).'" />'."\n";
          echo '<link rel="icon" type="image/x-icon" href="'.xtc_href_link($ws_dir.$favicon, '', $request_type, false).'" />'."\n";
        } else {
          echo '<link rel="icon" type="image/'.$favicon_type['extension'].'" href="'.xtc_href_link($ws_dir.$favicon, '', $request_type, false).'" />'."\n";
        }
      }
    }
  }

  // apple touch icon
  $apple_touch_icon_array = glob($fs_dir.'apple-touch-icon*');
  natcasesort($apple_touch_icon_array);
  if (count($apple_touch_icon_array) > 0) {
    foreach ($apple_touch_icon_array as $apple_touch_icon) {
      $apple_touch_icon = basename($apple_touch_icon);
      preg_match('/(\d+)x(\d+)/', $apple_touch_icon, $match);
      if (isset($match[0]) && $match[0] != '') {
        echo '<link rel="apple-touch-icon" href="'.xtc_href_link($ws_dir.$apple_touch_icon, '', $request_type, false).'" sizes="'.$match[0].'" />'."\n";
      } else {    
        echo '<link rel="apple-touch-icon" href="'.xtc_href_link($ws_dir.$apple_touch_icon, '', $request_type, false).'" />'."\n";
      }
    }
    echo '<meta name="apple-mobile-web-app-title" content="'.encode_htmlspecialchars(TITLE).'" />'."\n";
  }

  // windows icon
  $mstile_array = glob($fs_dir.'mstile*');
  natcasesort($mstile_array);
  if (count($mstile_array) > 0) {
    $browserconfig = '<?xml version="1.0" encoding="utf-8"?><browserconfig><msapplication><tile>';
    foreach ($mstile_array as $mstile) {
      $mstile = basename($mstile);
      preg_match('/(\d+)x(\d+)/', $mstile, $match);
      if (isset($match[0]) && $match[0] != '') {
        if ($match[1] == '144' && $match[2] == '144') {
          echo '<meta name="msapplication-TileImage" content="'.xtc_href_link($ws_dir.$mstile, '', $request_type, false).'" />'."\n"; 
        }
        if ($match[1] > $match[2]) {
          $browserconfig .= '<wide'.$match[0].'logo src="'.xtc_href_link($ws_dir.$mstile, '', $request_type, false).'"/>';
          echo '<meta name="msapplication-wide'.$match[0].'logo" content="'.xtc_href_link($ws_dir.$mstile, '', $request_type, false).'" />'."\n"; 
        } else {
          $browserconfig .= '<square'.$match[0].'logo src="'.xtc_href_link($ws_dir.$mstile, '', $request_type, false).'"/>';
          echo '<meta name="msapplication-square'.$match[0].'logo" content="'.xtc_href_link($ws_dir.$mstile, '', $request_type, false).'" />'."\n";       
        }
      }
    }
    echo '<meta name="msapplication-TileColor" content="#ffffff" />'."\n";
    echo '<meta name="theme-color" content="#ffffff">'."\n";
    echo '<meta name="msapplication-navbutton-color" content="#ffffff" />'."\n";
    echo '<meta name="msapplication-tooltip" content="'.encode_htmlspecialchars(TITLE).'" />'."\n";
    $browserconfig .= '</tile></msapplication></browserconfig>';
    $browserconfig_file_path = $fs_dir.'browserconfig.xml';
    $browserconfig_file = is_writeable($browserconfig_file_path) ? filemtime($browserconfig_file_path) : false;
    if ($browserconfig_file && (time() - $browserconfig_file > 86400 || filesize($browserconfig_file_path) == 0)) {
      file_put_contents($browserconfig_file_path, $browserconfig, LOCK_EX);
    }
    echo '<meta name="msapplication-config" content="'.xtc_href_link($ws_dir.'browserconfig.xml','', $request_type, false).'" />'."\n";
  }

  // android touch icon
  $android_touch_icon_array = glob($fs_dir.'android-chrome*');
  natcasesort($android_touch_icon_array);
  if (count($android_touch_icon_array) > 0) {
    $manifest_array = array('name' => encode_htmlspecialchars(TITLE),
                            'icons' => array()
                            );
    foreach ($android_touch_icon_array as $android_touch_icon) {
      $android_touch_icon_type = pathinfo($android_touch_icon);
      $android_touch_icon = basename($android_touch_icon);
      preg_match('/(\d+)x(\d+)/', $android_touch_icon, $match);
      if (isset($match[0]) && $match[0] != '') {
        echo '<link rel="icon" type="image/png" href="'.xtc_href_link($ws_dir.$android_touch_icon, '', $request_type, false).'" sizes="'.$match[0].'" />'."\n";
        $manifest_array['icons'][] = array('src' => xtc_href_link($ws_dir.$android_touch_icon, '', $request_type, false),
                                           'sizes' => $match[0],
                                           'type' => 'image/'.$android_touch_icon_type['extension'],
                                           'density' => ''.($match[1] / 48).''
                                           );
      }
    }  
    if (count($manifest_array['icons']) > 0) {
      $manifest_file_path = $fs_dir.'manifest.json';
      $manifest_file = is_writeable($manifest_file_path) ? filemtime($manifest_file_path) : false;
      if ($manifest_file && ($manifest_file < (time() - 86400) || filesize($manifest_file_path) == 0)) {
        file_put_contents($manifest_file_path, json_encode($manifest_array), LOCK_EX);
      }
      echo '<link rel="manifest" href="'.xtc_href_link($ws_dir.'manifest.json', '', $request_type, false).'" />'."\n";
    }
  }

  // application name
  echo '<meta name="application-name" content="'.encode_htmlspecialchars(TITLE).'" />'."\n";
} else {
  echo '<link rel="shortcut icon" type="image/x-icon" href="'.xtc_href_link('templates/'.CURRENT_TEMPLATE.'/favicon.ico','', $request_type, false).'" />'."\n";
}
?>