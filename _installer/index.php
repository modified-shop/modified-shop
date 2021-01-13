<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
  

  require_once ('includes/application_top.php');

  if ($upgrade === true) {  
    // Database
    $db_type = get_mysql_type();
    require_once (DIR_FS_INC.'db_functions_'.$db_type.'.inc.php');
    require_once (DIR_FS_INC.'db_functions.inc.php');

    // make a connection to the database... now
    xtc_db_connect() or die('Unable to connect to database server!');

    // load configuration
    $configuration_query = xtc_db_query('SELECT configuration_key, configuration_value FROM '.TABLE_CONFIGURATION);
    while ($configuration = xtc_db_fetch_array($configuration_query)) {
      defined($configuration['configuration_key']) OR define($configuration['configuration_key'], stripslashes($configuration['configuration_value']));
    }
  }

  // language
  require_once(DIR_FS_INSTALLER.'lang/'.$_SESSION['language'].'.php');

  // smarty
  $smarty = new Smarty();
  $smarty->setTemplateDir(__DIR__.'/templates')
         ->registerResource('file', new EvaledFileResource())
         ->setConfigDir(__DIR__.'/lang')
         ->SetCaching(0);

  // check for errors
  $error = false;
  
  // check requirements
  require_once('includes/check_requirements.php');
  
  // check permissions
  require_once('includes/check_permissions.php');

  // set all files to be deleted
  require_once('includes/delete_files.php');

  // set all directories to be deleted
  require_once('includes/delete_dirs.php');
  
  if ($error === true) {
    $smarty->assign('PERMISSION_ARRAY', $permission_array);
    $smarty->assign('REQUIREMENT_ARRAY', $requirement_array);
    $smarty->assign('UNLINKED_ARRAY', $unlinked_files);
    
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
      $smarty->assign('INPUT_FTP_PASS', xtc_draw_input_fieldNote(array('name' => 'ftp_pass')));    

      // form
      $smarty->assign('FORM_ACTION', xtc_draw_form('ftp', xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type), 'post').xtc_draw_hidden_field('action', 'ftp'));
      $smarty->assign('BUTTON_SUBMIT', '<button type="submit">'.BUTTON_SUBMIT.'</button>');
      $smarty->assign('FORM_END', '</form>');
    }

    if ($messageStack->size('ftp_message') > 0) {
      $smarty->assign('error', $messageStack->output('ftp_message'));
    }

    $smarty->assign('language', $_SESSION['language']);
    $module_content = $smarty->fetch('error.html');
  } else {
    if ($upgrade === true) {
	  $javascriptcheck = '
        <script type="text/javascript">
		  $(document).ready(function(){	
  			$(".cssButtonRow").show();	
		  });
		</script>
      ';
      $smarty->assign('JAVASCRIPTCHECK', $javascriptcheck);
      $smarty->assign('BUTTON_INSTALL', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.'install_step1.php', '', $request_type).'">'.BUTTON_INSTALL.'</a>');
      $smarty->assign('BUTTON_UPDATE', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.'update.php', '', $request_type).'">'.BUTTON_UPDATE.'</a>');
      $module_content = $smarty->fetch('start.html');
    } else {
      xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.'install_step1.php', '', $request_type));
    }
  }
    
  require ('includes/header.php');
  $smarty->assign('module_content', $module_content);
  
  $language_array = array(
    array(
      'link' => xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'language=de', $request_type),
      'code' => 'de',
    ),
    array(
      'link' => xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'language=en', $request_type),
      'code' => 'en',
    )
  );
  $smarty->assign('language_array', $language_array);
  $smarty->assign('logo', xtc_href_link(DIR_WS_INSTALLER.'images/logo_head.png', '', $request_type));
  
  if (!defined('RM')) {
    $smarty->load_filter('output', 'note');
  }
  $smarty->display('index.html');
  require_once ('includes/application_bottom.php');
?>