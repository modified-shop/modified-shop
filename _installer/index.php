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
    if (isset($_GET['action']) && $_GET['action'] == 'shop') {
      if (is_file(DIR_FS_CATALOG.'/includes/local/configure.php')) {
        is_make_nonwriteable(DIR_FS_CATALOG.'/includes/local/configure.php');
      }
      if (is_file(DIR_FS_CATALOG.'/includes/configure.php')) {
        is_make_nonwriteable(DIR_FS_CATALOG.'/includes/configure.php');
      }
      
      if (!isset($unlinked_files)) {
        $unlinked_files = array(
          'error' => array(
            'files' => array(),
            'dir' => array(),
          ),
          'success' => array(
            'files' => array(),
            'dir' => array(),
          ),
        );
      }
      
      // remove installer
      rrmdir(DIR_WS_INSTALLER);
      
      // reset session
      xtc_session_destroy();
      xtc_session_reset();

      xtc_redirect(xtc_href_link('', '', $request_type));
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

  if ($upgrade === true) {
    $javascriptcheck = '
    <script type="text/javascript">
      $(document).ready(function(){	
        $(".start_row").show();	
      });
    </script>
    ';
    $smarty->assign('JAVASCRIPTCHECK', $javascriptcheck);
    $smarty->assign('BUTTON_INSTALL', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.'install_step1.php', '', $request_type).'">'.BUTTON_INSTALL.'</a>');
    $smarty->assign('BUTTON_UPDATE', '<a href="'.xtc_href_link(DIR_WS_INSTALLER.'update.php', '', $request_type).'">'.BUTTON_UPDATE.'</a>');
    $smarty->assign('BUTTON_SHOP', '<a href="'.xtc_href_link(DIR_WS_INSTALLER, 'action=shop', $request_type).'">'.BUTTON_SHOP.'</a>');

    $smarty->assign('LINK_INSTALL', xtc_href_link(DIR_WS_INSTALLER.'install_step1.php', '', $request_type));
    $smarty->assign('LINK_UPDATE', xtc_href_link(DIR_WS_INSTALLER.'update.php', '', $request_type));
    $smarty->assign('LINK_SHOP', xtc_href_link(DIR_WS_INSTALLER, 'action=shop', $request_type));

    $module_content = $smarty->fetch('start.html');
  } else {
    xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.'install_step1.php', '', $request_type));
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