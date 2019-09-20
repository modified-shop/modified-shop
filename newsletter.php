<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce www.oscommerce.com 
   (c) 2003	 nextcommerce www.nextcommerce.org
   (c) 2003 XT-Commerce
   
   XTC-NEWSLETTER_RECIPIENTS RC1 - Contribution for XT-Commerce http://www.xt-commerce.com
   by Matthias Hinsche http://www.gamesempire.de
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

require ('includes/application_top.php');

if (!defined('MODULE_NEWSLETTER_STATUS') || MODULE_NEWSLETTER_STATUS == 'false') {
  xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
}

defined('DISPLAY_PRIVACY_CHECK') or define('DISPLAY_PRIVACY_CHECK', 'true');

// captcha
$use_captcha = array('newsletter');
if (defined('MODULE_CAPTCHA_ACTIVE')) {
  $use_captcha = explode(',', MODULE_CAPTCHA_ACTIVE);
}
defined('MODULE_CAPTCHA_CODE_LENGTH') or define('MODULE_CAPTCHA_CODE_LENGTH', 6);
defined('MODULE_CAPTCHA_LOGGED_IN') or define('MODULE_CAPTCHA_LOGGED_IN', 'True');

// create smarty elements
$smarty = new Smarty;

// include needed functions
require_once (DIR_FS_INC.'xtc_validate_email.inc.php');
require_once (DIR_FS_INC.'secure_form.inc.php');

// include needed classes
require_once (DIR_WS_CLASSES.'class.newsletter.php');
require_once (DIR_WS_CLASSES.'modified_captcha.php');

$mod_captcha = $_mod_captcha_class::getInstance();

$info_message = '';
$newsletter = new newsletter();
$privacy = isset($_POST['privacy']) && $_POST['privacy'] == 'privacy' ? true : false;

if (isset ($_GET['action']) && ($_GET['action'] == 'process')) {
  $error = false;
  $email = xtc_db_prepare_input($_POST['email']);
  if (DISPLAY_PRIVACY_CHECK == 'true' && empty($privacy)) {
    $error = true;
    $messageStack->add('newsletter', ENTRY_PRIVACY_ERROR);
  }

  if (strlen($email) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
    $error = true;
    $messageStack->add('newsletter', ENTRY_EMAIL_ADDRESS_ERROR);
  } elseif (xtc_validate_email($email) == false) {
    $error = true;
    $messageStack->add('newsletter', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
  }
  
  if (check_secure_form($_POST) === false) {
    $messageStack->add('newsletter', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    $error = true;
  }
  
  if ($error === false) {
    if (!in_array('newsletter', $use_captcha) || (isset($_SESSION['customer_id']) && MODULE_CAPTCHA_LOGGED_IN == 'False')) {
      $newsletter->auto = true;
    }
    $newsletter->AddUser($_POST['check'], strtoupper($_POST['vvcode']), $email);
    $info_message = $newsletter->message;
  } else {
    if ($messageStack->size('newsletter') > 0) {
      $info_message = $messageStack->output('newsletter');
      $newsletter->message_class = 'error';
    }
  }
}

// Accountaktivierung per Emaillink
if (isset ($_GET['action']) && ($_GET['action'] == 'activate')) {
  $newsletter->ActivateAddress($_GET['key'], $_GET['email']);
  $info_message = $newsletter->message;
  if ($newsletter->message_class == 'info') {
    $smarty->assign('activated', true);
  }
}

// Accountdeaktivierung per Emaillink
if (isset ($_GET['action']) && ($_GET['action'] == 'remove')) {
  $newsletter->RemoveFromList($_GET['key'], $_GET['email']);
  $info_message = $newsletter->message;
}

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

$breadcrumb->add(NAVBAR_TITLE_NEWSLETTER, xtc_href_link(FILENAME_NEWSLETTER, '', 'SSL'));

require (DIR_WS_INCLUDES.'header.php');

if (in_array('newsletter', $use_captcha) && (!isset($_SESSION['customer_id']) || MODULE_CAPTCHA_LOGGED_IN == 'True')) {
  $smarty->assign('VVIMG', $mod_captcha->get_image_code());
  $smarty->assign('INPUT_CODE', $mod_captcha->get_input_code());
}

$smarty->assign('text_newsletter', TEXT_NEWSLETTER);
$smarty->assign('info_message', $info_message);
if ($newsletter->message_class != '') {
  $smarty->assign('message_class', $newsletter->message_class);
}
$smarty->assign('FORM_ACTION', xtc_draw_form('sign', xtc_href_link(FILENAME_NEWSLETTER, 'action=process', 'SSL')).secure_form());
$smarty->assign('INPUT_EMAIL', xtc_draw_input_field('email', ((isset($_GET['email']) && xtc_db_input($_GET['email'])!='') ? xtc_db_input($_GET['email']):((isset($_POST['email']) && xtc_db_input($_POST['email']))?xtc_db_input($_POST['email']):''))));

if(isset($_POST['check']) && $_POST['check'] == 'inp') {$inp = 'true'; $del = '';}
if(isset($_POST['check']) && $_POST['check'] == 'del') {$inp = ''; $del = 'true';}	

$smarty->assign('CHECK_INP', xtc_draw_radio_field('check', 'inp', ((isset($inp)) ? $inp : ''), 'id="inp"'));
$smarty->assign('CHECK_DEL', xtc_draw_radio_field('check', 'del', ((isset($del)) ? $del : ''), 'id="del"'));
$smarty->assign('BUTTON_SEND', xtc_image_submit('button_send.gif', IMAGE_BUTTON_SEND));
$smarty->assign('FORM_END', '</form>');
if (DISPLAY_PRIVACY_CHECK == 'true') {
  $smarty->assign('PRIVACY_CHECKBOX', xtc_draw_checkbox_field('privacy', 'privacy', $privacy, 'id="privacy"'));
}
$smarty->assign('PRIVACY_LINK', $main->getContentLink(2, MORE_INFO, $request_type));
$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/newsletter.html');
$smarty->assign('main_content', $main_content);

$smarty->assign('language', $_SESSION['language']);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>