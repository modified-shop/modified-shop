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
  require_once(DIR_FS_CATALOG.DIR_ADMIN.'includes/modules/check_requirements.php');
  
  // check permissions
  require_once('includes/check_permissions.php');
  
  if ($error === true) {
    $smarty->assign('PERMISSION_ARRAY', $permission_array);
    $smarty->assign('REQUIREMENT_ARRAY', $requirement_array);
    
    if (count($permission_array['file_permission']) > 0
        || count($permission_array['folder_permission']) > 0
        || count($permission_array['rfolder_permission']) > 0
        )
    {
      // ftp
      $smarty->assign('INPUT_FTP_HOST', xtc_draw_input_fieldNote(array('name' => 'ftp_host')));
      $smarty->assign('INPUT_FTP_PORT', xtc_draw_input_fieldNote(array('name' => 'ftp_port')));
      $smarty->assign('INPUT_FTP_PATH', xtc_draw_input_fieldNote(array('name' => 'ftp_path')));
      $smarty->assign('INPUT_FTP_USER', xtc_draw_input_fieldNote(array('name' => 'ftp_user')));    
      $smarty->assign('INPUT_FTP_PASS', xtc_draw_password_fieldNote(array('name' => 'ftp_pass')));    

      // form
      $smarty->assign('BUTTON_BACK', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.'index.php', '', $request_type).'">'.BUTTON_BACK.'</a>');
      $smarty->assign('FORM_ACTION', xtc_draw_form('ftp', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type), 'post').xtc_draw_hidden_field('action', 'ftp'));
      $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
      $smarty->assign('FORM_END', '</form>');
    }

    if ($messageStack->size('ftp_message') > 0) {
      $smarty->assign('error_message', $messageStack->output('ftp_message'));
    }

    $smarty->assign('language', $_SESSION['language']);
    $smarty->assign('ERROR', $smarty->fetch('error.html'));
    $smarty->clear_assign('error_message');
  }  
