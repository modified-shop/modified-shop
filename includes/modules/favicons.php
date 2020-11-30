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
  if (is_array($favicon_array) && count($favicon_array) > 0) {
    natcasesort($favicon_array);
    foreach ($favicon_array as $favicon) {
      $favicon_type = pathinfo($favicon);
      $favicon = basename($favicon);
      preg_match('/(\d+)x(\d+)/', $favicon, $match);
      if ($favicon_type['extension'] == 'ico') {
        echo '<link rel="shortcut icon" href="'.xtc_href_link($ws_dir.$favicon, '', $request_type, false).'" />'."\n";
      } else {
        echo '<link rel="icon" type="image/'.$favicon_type['extension'].'"'.((isset($match[0]) && $match[0] != '') ? ' sizes="'.$match[0].'"' : '').' href="'.xtc_href_link($ws_dir.$favicon, '', $request_type, false).'" />'."\n";
      }
    }
  }

  // apple touch icon
  $apple_touch_icon_array = glob($fs_dir.'apple-touch-icon*');
  if (is_array($apple_touch_icon_array) && count($apple_touch_icon_array) > 0) {
    natcasesort($apple_touch_icon_array);
    foreach ($apple_touch_icon_array as $apple_touch_icon) {
      $apple_touch_icon = basename($apple_touch_icon);
      preg_match('/(\d+)x(\d+)/', $apple_touch_icon, $match);
      echo '<link rel="apple-touch-icon"'.((isset($match[0]) && $match[0] != '') ? ' sizes="'.$match[0].'"' : '').' href="'.xtc_href_link($ws_dir.$apple_touch_icon, '', $request_type, false).'" />'."\n";
    }
  }

  // safari icon
  $apple_touch_icon_array = glob($fs_dir.'safari-pinned-tab*');
  if (is_array($apple_touch_icon_array) && count($apple_touch_icon_array) > 0) {
    natcasesort($apple_touch_icon_array);
    foreach ($apple_touch_icon_array as $apple_touch_icon) {
      $apple_touch_icon = basename($apple_touch_icon);
      preg_match('/(\d+)x(\d+)/', $apple_touch_icon, $match);
      echo '<link rel="mask-icon"'.((isset($match[0]) && $match[0] != '') ? ' sizes="'.$match[0].'"' : '').' href="'.xtc_href_link($ws_dir.$apple_touch_icon, '', $request_type, false).'" color="#888888" />'."\n";
    }
  }

  // windows icon
  $mstile_array = glob($fs_dir.'mstile*');
  if (is_array($mstile_array) && count($mstile_array) > 0) {
    natcasesort($mstile_array);
    $browserconfig = '<?xml version="1.0" encoding="utf-8"?><browserconfig><msapplication><tile>';
    foreach ($mstile_array as $mstile) {
      $mstile = basename($mstile);
      preg_match('/(\d+)x(\d+)/', $mstile, $match);
      if (isset($match[0]) && $match[0] != '') {
        if ($match[1] > $match[2]) {
          $browserconfig .= '<wide'.$match[0].'logo src="'.xtc_href_link($ws_dir.$mstile, '', $request_type, false).'"/>';
        } else {
          $browserconfig .= '<square'.$match[0].'logo src="'.xtc_href_link($ws_dir.$mstile, '', $request_type, false).'"/>';
        }
      }
    }
    $browserconfig .= '<TileColor>#ffffff</TileColor>';
    $browserconfig .= '</tile></msapplication></browserconfig>';
    $browserconfig_file_path = $fs_dir.'browserconfig.xml';
    $browserconfig_file = is_writeable($browserconfig_file_path) ? filemtime($browserconfig_file_path) : false;
    if ($browserconfig_file && (time() - $browserconfig_file > 86400 || filesize($browserconfig_file_path) == 0)) {
      file_put_contents($browserconfig_file_path, $browserconfig, LOCK_EX);
    }
    echo '<meta name="msapplication-TileColor" content="#ffffff" />'."\n";
    echo '<meta name="theme-color" content="#ffffff" />'."\n";
    echo '<meta name="msapplication-config" content="'.xtc_href_link($ws_dir.'browserconfig.xml','', $request_type, false).'" />'."\n";
  }
  
  // android touch icon
  $android_touch_icon_array = glob($fs_dir.'android-chrome*');
  if (is_array($android_touch_icon_array) && count($android_touch_icon_array) > 0) {
    natcasesort($android_touch_icon_array);
    $manifest_array = array('name' => encode_htmlspecialchars(TITLE),
                            'short_name' => encode_htmlspecialchars(TITLE),
                            'icons' => array(),
                            'theme_color' => '#ffffff',
                            'background_color' => '#ffffff',
                            'display' => 'standalone'
                           );
    foreach ($android_touch_icon_array as $android_touch_icon) {
      $android_touch_icon_type = pathinfo($android_touch_icon);
      $android_touch_icon = basename($android_touch_icon);
      preg_match('/(\d+)x(\d+)/', $android_touch_icon, $match);
      if (isset($match[0]) && $match[0] != '') {
        $manifest_array['icons'][] = array('src' => xtc_href_link($ws_dir.$android_touch_icon, '', $request_type, false),
                                           'sizes' => $match[0],
                                           'type' => 'image/'.$android_touch_icon_type['extension']
                                           );
      }
    }  
    if (count($manifest_array['icons']) > 0) {
      $manifest_file_path = $fs_dir.'site.webmanifest';
      $manifest_file = is_writeable($manifest_file_path) ? filemtime($manifest_file_path) : false;
      if ($manifest_file && ($manifest_file < (time() - 86400) || filesize($manifest_file_path) == 0)) {
        file_put_contents($manifest_file_path, json_encode($manifest_array), LOCK_EX);
      }
      echo '<link rel="manifest" href="'.xtc_href_link($ws_dir.'site.webmanifest', '', $request_type, false).'" />'."\n";
    }
  }
} else {
  echo '<link rel="shortcut icon" href="'.xtc_href_link('templates/'.CURRENT_TEMPLATE.'/favicon.ico','', $request_type, false).'" />'."\n";
}
?>