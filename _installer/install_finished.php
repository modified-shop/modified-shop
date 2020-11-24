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
  
  // Database
  require_once (DIR_FS_INC.'db_functions_'.DB_MYSQL_TYPE.'.inc.php');
  require_once (DIR_FS_INC.'db_functions.inc.php');

  // make a connection to the database... now
  xtc_db_connect() or die('Unable to connect to database server!');

  // load configuration
  $configuration_query = xtc_db_query('SELECT configuration_key, configuration_value FROM '.TABLE_CONFIGURATION);
  while ($configuration = xtc_db_fetch_array($configuration_query)) {
    defined($configuration['configuration_key']) OR define($configuration['configuration_key'], stripslashes($configuration['configuration_value']));
  }
  
  // language
  require_once(DIR_FS_INSTALLER.'lang/'.$_SESSION['language'].'.php');
  
  if (isset($_GET['action']) && $_GET['action'] == 'install') {
    $payment_method = $_GET['code'];
    if (is_file(DIR_WS_MODULES.'payment/'.$payment_method.'.php')) {
      include_once(DIR_WS_MODULES.'payment/'.$payment_method.'.php');
      $module = new $payment_method();
      $module->install();
      if (defined('MODULE_PAYMENT_INSTALLED')) {
        $installed = array();
        if (MODULE_PAYMENT_INSTALLED != '') {
          $installed = explode(';', MODULE_PAYMENT_INSTALLED);
        }
        $installed[] = $payment_method.'.php';
        xtc_db_query("UPDATE ".TABLE_CONFIGURATION." SET configuration_value = '" . implode(';', $installed) . "', last_modified = now() where configuration_key = 'MODULE_PAYMENT_INSTALLED'");
      } else {
        xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_PAYMENT_INSTALLED', '".$payment_method.".php', '6', '0', now())");
      }
    } else {
      $messageStack->add_session('install_finished', ERROR_MODULES_PAYMENT);
    }
    xtc_redirect(xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), '', $request_type));
  }
  
  // smarty
  $smarty = new Smarty();
  $smarty->setTemplateDir(__DIR__.'/templates')
         ->registerResource('file', new EvaledFileResource())
         ->setConfigDir(__DIR__.'/lang')
         ->SetCaching(0);
  
  $payment_methods_array = array(
    'paypalclassic',
    'paypalplus',

    'paypallink',
    'paypalpluslink',
  );
  
  $directory_array = array(
    'installed' => array(),
    'uninstalled' => array(),
  );
  
  foreach ($payment_methods_array as $payment_method) {
    if (is_file(DIR_WS_MODULES.'payment/'.$payment_method.'.php')) {
      include_once(DIR_WS_MODULES.'payment/'.$payment_method.'.php');
      if (is_file(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/'.$payment_method.'.php')) {
        include_once(DIR_WS_LANGUAGES.$_SESSION['language'].'/modules/payment/'.$payment_method.'.php');
      }
      $module = new $payment_method();
      $payment = array(
        'CODE' => $payment_method,
        'NAME' => $module->title,
        'DESCRIPTION' => $module->description,
        'BUTTON_INSTALL' => '<a href="'.xtc_href_link(DIR_WS_INSTALLER.basename($PHP_SELF), 'action=install&code='.$payment_method, $request_type).'">'.BUTTON_PAYMENT_INSTALL.'</a>',
      );
      if (method_exists($module,'check')) {
        if ($module instanceof $payment_method && $module->check() > 0) {
          $directory_array['installed'][] = $payment;
        } else {
          $directory_array['uninstalled'][] = $payment;
        }
      }
    }
  }
  
  if ($messageStack->size('install_finished') > 0) {
    $smarty->assign('error', $messageStack->output('install_finished'));
  }
    
  $smarty->assign('BUTTON_SHOP', '<a href="'.xtc_href_link('', '', $request_type).'">'.BUTTON_SHOP.'</a>');
  $smarty->assign('payment_methods', $directory_array);  
  $smarty->assign('language', $_SESSION['language']);
  $module_content = $smarty->fetch('install_finished.html');
  
  require ('includes/header.php');
  $smarty->assign('module_content', $module_content);
  $smarty->assign('logo', xtc_href_link(DIR_WS_INSTALLER.'images/logo_head.png', '', $request_type));
  
  if (!isset($_SESSION['installed'])) {
    $version = get_database_version();
    $img_link = 'https://images.modified-shop.org/modified'.preg_replace('/\D/', '', $version['plain']).'.gif';
    $smarty->assign('logo', $img_link);
    $_SESSION['installed'] = true;
  }
  
  if (!defined('RM')) {
    $smarty->load_filter('output', 'note');
  }
  $smarty->display('index.html');
  require_once ('includes/application_bottom.php');
?>