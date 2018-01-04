<?php
  /* --------------------------------------------------------------
   $Id: extra_menu.php 5568 2013-09-08 13:47:05Z web28 $

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
    foreach(auto_include(DIR_FS_ADMIN.'includes/extra/menu/','php') as $file) require ($file);
    return $add_contents;
  }
}

// subMenue($admin_access_name, $filename, $linktext, $parameters);
if (!function_exists('subMenue')){ // zweite ebene
    function subMenue($admin_access_name = '', $filename = '', $linktext= '', $parameters = '', $ssl = 'NONSSL'){
        global $admin_access;

        ## magnalister
        if (!is_array($admin_access) || count($admin_access) < 1) {
          $admin_access = array();
          if (($_SESSION['customers_status']['customers_status_id'] == '0')) {
            $admin_access_query = xtc_db_query("SELECT * FROM " . TABLE_ADMIN_ACCESS . " WHERE customers_id = ".(int)$_SESSION['customer_id']);
            $admin_access = xtc_db_fetch_array($admin_access_query);
          }
        }
        ## magnalister

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
        global $add_contents, $admin_access;

        ## magnalister
        if (!is_array($add_contents) || count($add_contents) < 1) {
          $add_contents = extraMenue();
        }
        ## magnalister

        $html = '';
        if(isset($add_contents[$box]) && count($add_contents[$box]) > 0) {
            //foreach ($add_contents[$box] as $keyname => $key) {
            foreach ($add_contents[$box] as $key) {
                //check for 2nd level
                if (is_array($key) && !isset($key['admin_access_name'])) {
                  $LinkSub = $LinkSubEnd = $html2 = '';
                  foreach ($key as $key2) {
                    if (isset($key2['has_subs']) ) {
                      if (isset($admin_access[$key2['admin_access_name']]) && $admin_access[$key2['admin_access_name']] == '1') {
                        $LinkSub = '<li><a href="#" class="menuBoxContentLinkSub"> -' . $key2['boxname'] . '</a><ul>';
                        $LinkSubEnd = '</ul></li>';
                      }
                    } else {
                      $html2.= subMenue($key2['admin_access_name'],
                                   $key2['filename'],
                                   $key2['boxname'],
                                   $key2['parameters'],
                                   $key2['ssl']
                                  );
                    }
                  }
                  $html.= $LinkSub . $html2 . $LinkSubEnd;
                } else {
                $html.= subMenue($key['admin_access_name'],
                                 $key['filename'],
                                 $key['boxname'],
                                 $key['parameters'],
                                 $key['ssl']
                                );
                }
            }
        }
        return $html;
    }
}

$add_contents = array();
$add_contents = extraMenue();
?>