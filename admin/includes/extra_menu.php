<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

//scandir for etra menu
if (!function_exists('extraMenue')) {
  function extraMenue() {
    $add_contents = array();
    if ($extra_files = @scandir(DIR_WS_INCLUDES . 'extra/menu')) {      
      foreach ($extra_files as $filename) {
        $file_ext = end(explode('.',$filename));
        if($file_ext == 'php') {          
          require_once(DIR_WS_INCLUDES . 'extra/menu/' . $filename);
        }
      }
    }
    return $add_contents;
  }
}

// subMenue($admin_access_name, $filename, $linktext, $parameters);
if (!function_exists('subMenue')){ // zweite ebene
    function subMenue($admin_access_name = '', $filename = '', $linktext= '', $parameters = '', $ssl = 'NONSSL'){
        global $admin_access;
        $html = '';
        if (isset($admin_access[$admin_access_name]) && $admin_access[$admin_access_name] == '1') {

            if (!$filename && defined('FILENAME_'.strtoupper($admin_access_name))) {
                 $filename = constant('FILENAME_'.strtoupper($admin_access_name));
            }
            if (!$linktext && defined('BOX_'.strtoupper($admin_access_name))) {
                 $linktext = constant('BOX_'.strtoupper($admin_access_name));
            }
            //error info
            if ($filename) {
              $ssl = $ssl == ''? 'NONSSL': $ssl;
              $html = '<li><a href="' . xtc_href_link($filename, $parameters, $ssl) . '" class="menuBoxContentLink"> -' . $linktext . '</a></li>';
            } else {
              echo 'ERROR --- '. 'AdminAccess: '. $admin_access_name . '|FileName: NO FILENAME DEFINED<br>';
            }            
        }
        return $html;
    }
}

// dynamics Adds();
if (!function_exists('dynamicsAdds')){ // Menüpunkte dynamisch ergänzen
    function dynamicsAdds($box){
        global $add_contents;
        if(isset($add_contents[$box]) && count($add_contents[$box] > 0)) {
            $html = '';
            foreach ($add_contents[$box] as $key) {
                $html.= subMenue($key['admin_access_name'],
                                 $key['filename'],
                                 $key['boxname'],
                                 $key['parameters'],
                                 $key['ssl']
                                );
            }
        }
        return $html;
    }
}

$add_contents = array();
$add_contents = extraMenue();
