<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

$favicon_directory = Template::findPath('favicons/');

if ($favicon_directory === null) {
  $favicon = Template::findPath('favicon.ico');
  if ($favicon !== null) {
    echo '<link rel="shortcut icon" href="'.xtc_href_link('templates/'.Template::resolve('favicon.ico'),'', $request_type, false).'" />'."\n";
  }

  return;
}

// Group the effective files once; Template::files() already provides inheritance and sorting.
$favicon_groups = [
  'favicon' => [],
  'apple_touch_icon' => [],
  'safari_pinned_tab' => [],
  'mstile' => [],
  'android_touch_icon' => [],
];

foreach (Template::files('favicons/') as $favicon_path) {
  $favicon_name = basename($favicon_path);
  $favicon_group = null;

  if (str_starts_with($favicon_name, 'favicon')) {
    $favicon_group = 'favicon';
  } elseif (str_starts_with($favicon_name, 'apple-touch-icon')) {
    $favicon_group = 'apple_touch_icon';
  } elseif (str_starts_with($favicon_name, 'safari-pinned-tab')) {
    $favicon_group = 'safari_pinned_tab';
  } elseif (str_starts_with($favicon_name, 'mstile')) {
    $favicon_group = 'mstile';
  } elseif (str_starts_with($favicon_name, 'android-chrome') || str_starts_with($favicon_name, 'web-app-manifest')) {
    $favicon_group = 'android_touch_icon';
  }

  if ($favicon_group === null) {
    continue;
  }

  $favicon_groups[$favicon_group][] = [
    'name' => $favicon_name,
    'extension' => pathinfo($favicon_path, PATHINFO_EXTENSION),
    'url' => xtc_href_link('templates/'.Template::resolve('favicons/'.$favicon_name), '', $request_type, false),
  ];
}

// favicon
foreach ($favicon_groups['favicon'] as $favicon) {
  preg_match('/(\d+)x(\d+)/', $favicon['name'], $match);
  if ($favicon['extension'] == 'ico') {
    echo '<link rel="shortcut icon" href="'.$favicon['url'].'" />'."\n";
  } else {
    echo '<link rel="icon" type="image/'.$favicon['extension'].(($favicon['extension'] == 'svg') ? '+xml' : '').'"'.((isset($match[0]) && $match[0] != '') ? ' sizes="'.$match[0].'"' : '').' href="'.$favicon['url'].'" />'."\n";
  }
}

// apple touch icon
foreach ($favicon_groups['apple_touch_icon'] as $apple_touch_icon) {
  preg_match('/(\d+)x(\d+)/', $apple_touch_icon['name'], $match);
  echo '<link rel="apple-touch-icon"'.((isset($match[0]) && $match[0] != '') ? ' sizes="'.$match[0].'"' : '').' href="'.$apple_touch_icon['url'].'" />'."\n";
}
if (count($favicon_groups['apple_touch_icon']) > 0) {
  echo '<meta name="apple-mobile-web-app-title" content="'.encode_htmlspecialchars(TITLE).'" />'."\n";
}

// safari icon
foreach ($favicon_groups['safari_pinned_tab'] as $safari_pinned_tab) {
  preg_match('/(\d+)x(\d+)/', $safari_pinned_tab['name'], $match);
  echo '<link rel="mask-icon"'.((isset($match[0]) && $match[0] != '') ? ' sizes="'.$match[0].'"' : '').' href="'.$safari_pinned_tab['url'].'" color="#888888" />'."\n";
}

// windows icon
if (count($favicon_groups['mstile']) > 0) {
  $browserconfig = '<?xml version="1.0" encoding="utf-8"?><browserconfig><msapplication><tile>';
  foreach ($favicon_groups['mstile'] as $mstile) {
    preg_match('/(\d+)x(\d+)/', $mstile['name'], $match);
    if (isset($match[0]) && $match[0] != '') {
      if ($match[1] > $match[2]) {
        $browserconfig .= '<wide'.$match[0].'logo src="'.$mstile['url'].'"/>';
      } else {
        $browserconfig .= '<square'.$match[0].'logo src="'.$mstile['url'].'"/>';
      }
    }
  }
  $browserconfig .= '<TileColor>#ffffff</TileColor>';
  $browserconfig .= '</tile></msapplication></browserconfig>';

  $browserconfig_file_path = Template::findPath('favicons/browserconfig.xml');
  if ($browserconfig_file_path !== null) {
    $browserconfig_file = is_writeable($browserconfig_file_path) ? filemtime($browserconfig_file_path) : false;
    if ($browserconfig_file && (time() - $browserconfig_file > 86400 || filesize($browserconfig_file_path) == 0)) {
      file_put_contents($browserconfig_file_path, $browserconfig, LOCK_EX);
    }
  }

  echo '<meta name="msapplication-TileColor" content="#ffffff" />'."\n";
  echo '<meta name="theme-color" content="#ffffff" />'."\n";
  if ($browserconfig_file_path !== null) {
    echo '<meta name="msapplication-config" content="'.xtc_href_link('templates/'.Template::resolve('favicons/browserconfig.xml'), '', $request_type, false).'" />'."\n";
  }
}

// android touch icon
if (count($favicon_groups['android_touch_icon']) > 0) {
  $manifest_array = [
    'name' => encode_htmlspecialchars(TITLE),
    'short_name' => encode_htmlspecialchars(TITLE),
    'icons' => [],
    'theme_color' => '#ffffff',
    'background_color' => '#ffffff',
    'display' => 'standalone',
  ];

  foreach ($favicon_groups['android_touch_icon'] as $android_touch_icon) {
    preg_match('/(\d+)x(\d+)/', $android_touch_icon['name'], $match);
    if (isset($match[0]) && $match[0] != '') {
      $manifest_array['icons'][] = [
        'src' => $android_touch_icon['url'],
        'sizes' => $match[0],
        'type' => 'image/'.$android_touch_icon['extension'],
      ];
    }
  }

  if (count($manifest_array['icons']) > 0) {
    $manifest_file_path = Template::findPath('favicons/site.webmanifest');
    if ($manifest_file_path !== null) {
      $manifest_file = is_writeable($manifest_file_path) ? filemtime($manifest_file_path) : false;
      if ($manifest_file && ($manifest_file < (time() - 86400) || filesize($manifest_file_path) == 0)) {
        file_put_contents($manifest_file_path, json_encode($manifest_array), LOCK_EX);
      }
      echo '<link rel="manifest" href="'.xtc_href_link('templates/'.Template::resolve('favicons/site.webmanifest'), '', $request_type, false).'" />'."\n";
    }
  }
}
