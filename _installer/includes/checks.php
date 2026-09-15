<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // check for errors
  $error = false;
  
  // check requirements
  require_once('includes/check_requirements.php');
  
  // check permissions
  require_once('includes/check_permissions.php');
  
  if ($error === true
      && !in_array($_GET['action'], array('db_backup', 'readdb', 'db_restore', 'restoredb'))
      )
  {
    // list only failed and unknown requirements
    foreach ($requirement_array as $k => $requirement) {
      if ($requirement['status'] === true) {
        unset($requirement_array[$k]);
      }
    }

    $smarty->assign('PERMISSION_ARRAY', $permission_array);
    $smarty->assign('REQUIREMENT_ARRAY', $requirement_array);
    
    if (count($permission_array['file_permission']) > 0
        || count($permission_array['folder_permission']) > 0
        || count($permission_array['rfolder_permission']) > 0
        )
    {
      $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.'index.php', '', $request_type).'">'.BUTTON_BACK.'</a>');
    }

    $smarty->assign('language', $_SESSION['language']);
    $smarty->assign('ERROR', $smarty->fetch('error.html'));
    $smarty->clear_assign('error_message');
  }  
