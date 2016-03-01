<?php
/* -----------------------------------------------------------------------------------------
   $Id: download.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(download.php,v 1.9 2003/02/13); www.oscommerce.com 
   (c) 2003 nextcommerce (download.php,v 1.7 2003/08/17); www.nextcommerce.org
   (c) 2006 xtCommerce (download.php 831 2005-03-13)

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// For downloads we don't need gzip
$gzip_off = true;
include ('includes/application_top.php');

// include needed functions
require_once (DIR_FS_INC.'xtc_random_name.inc.php');
require_once (DIR_FS_INC.'xtc_unlink_temp_dir.inc.php');
if (!function_exists('xtc_date_long')) {
	require_once (DIR_FS_INC.'xtc_date_long.inc.php');
}

// function readfile
function readfile_chunked($file, $chunksize) {
  $buffer = '';
  // Small files don't need to be chunked
  if (filesize($file) <= $chunksize) {
    $status = readfile($file);
    return $status;
  }
  $handle = fopen($file, 'rb');
  if ($handle === false)
    return false;
  while (!feof($handle)) {
    echo fread($handle, $chunksize);
  }
  $status = fclose($handle);

  return $status;
}

// init Smarty
$smarty = new Smarty();

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

if (isset($_SESSION['customer_id'])) {
  $_SESSION['customer_id_download'] = $_SESSION['customer_id'];
}

if (isset ($_GET['action']) && ($_GET['action'] == 'process')) {
	$email_address = xtc_db_prepare_input($_POST['email_address']);
  $check_email_query = xtc_db_query("SELECT customers_email_address,
                                            customers_id
                                       FROM ".TABLE_ORDERS." 
                                      WHERE orders_id = '".(int)$_GET['order']."'");
	$check_email = xtc_db_fetch_array($check_email_query);
	if ($email_address == $check_email['customers_email_address']) {
    $_SESSION['customer_id_download'] = $check_email['customers_id'];
  } else {
		$messageStack->add_session('download', ENTRY_EMAIL_ADDRESS_CHECK_ERROR, 'error');
		xtc_redirect(xtc_href_link(FILENAME_DOWNLOAD, xtc_get_all_get_params(array('action')), 'SSL'));
  }
}

if (isset ($_GET['order']) && is_numeric($_GET['order']) && isset ($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['key']) && strlen($_GET['key']) == '32') {
  // check for Geust Accounts
  if (!isset($_SESSION['customer_id_download'])) {
    $smarty->assign('FORM_ACTION', xtc_draw_form('downloads', xtc_href_link(FILENAME_DOWNLOAD, xtc_get_all_get_params().'action=process', 'SSL')));
    $smarty->assign('INPUT_MAIL', xtc_draw_input_field('email_address'));
    $smarty->assign('FORM_END', '</form>');
    $smarty->assign('BUTTON_LOGIN', xtc_image_submit('button_confirm.gif', IMAGE_BUTTON_LOGIN));
  } else {
    // Check that order_id, download_id and key match
    $check_status_query = xtc_db_query("SELECT o.orders_id,
                                               o.orders_status,
                                               o.date_purchased,
                                               opd.orders_products_download_id
                                          FROM ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd
                                          JOIN ".TABLE_ORDERS." o
                                               ON o.orders_id=opd.orders_id
                                         WHERE opd.orders_id = '".(int)$_GET['order']."' 
                                           AND opd.orders_products_download_id = '".(int)$_GET['id']."'
                                           AND opd.download_key = '".xtc_db_input($_GET['key'])."'");
    if (xtc_db_num_rows($check_status_query) > 0) {
      $check_status = xtc_db_fetch_array($check_status_query);
      $allowed_status = explode(',', DOWNLOAD_MIN_ORDERS_STATUS);
      if (in_array($check_status['orders_status'], $allowed_status)) {
        // status allowed for download
        $downloads_query = xtc_db_query("SELECT opd.orders_products_filename
                                           FROM ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd
                                           JOIN ".TABLE_ORDERS." o
                                                ON o.orders_id = opd.orders_id
                                          WHERE o.orders_id = '".$check_status['orders_id']."'
                                            AND opd.orders_products_download_id = '".$check_status['orders_products_download_id']."'
                                            AND opd.orders_products_filename != ''
                                            AND DATE_SUB(CURDATE(), INTERVAL opd.download_maxdays DAY) <= '".$check_status['date_purchased']."'
                                            AND opd.download_count > '0'
                                            AND opd.download_key = '".xtc_db_input($_GET['key'])."'
                                            AND o.customers_id = '".(int)$_SESSION['customer_id_download']."'");
        if (xtc_db_num_rows($downloads_query) > 0) {
          $downloads = xtc_db_fetch_array($downloads_query);
                
          if (!file_exists(DIR_FS_DOWNLOAD.$downloads['orders_products_filename'])) {
            $smarty->assign('dl_not_found', 'true');
            $smarty->assign('dl_prevented', 'true');
          } else {
            // Now decrement counter
            xtc_db_query("update ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." set download_count = download_count-1 where orders_products_download_id = '".(int) $_GET['id']."'");

            if (DOWNLOAD_BY_REDIRECT == 'true') {
              // This will work only on Unix/Linux hosts
              xtc_unlink_temp_dir(DIR_FS_DOWNLOAD_PUBLIC);
              $tempdir = xtc_random_name();
              umask(0000);
              mkdir(DIR_FS_DOWNLOAD_PUBLIC.$tempdir, 0777);
              if (!symlink(DIR_FS_DOWNLOAD.$downloads['orders_products_filename'], DIR_FS_DOWNLOAD_PUBLIC.$tempdir.'/'.$downloads['orders_products_filename'])) {
                link(DIR_FS_DOWNLOAD.$downloads['orders_products_filename'], DIR_FS_DOWNLOAD_PUBLIC.$tempdir.'/'.$downloads['orders_products_filename']); 
              }
              xtc_redirect(DIR_WS_DOWNLOAD_PUBLIC.$tempdir.'/'.$downloads['orders_products_filename']);
            } else {
              //Set chunk size for download
              $chunksize = 1 * (1024 * 1024);
              // Now send the file with header() magic
              header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
              header("Last-Modified: ".gmdate("D,d M Y H:i:s")." GMT");
              header("Cache-Control: no-cache, must-revalidate");
              header("Pragma: no-cache");
              header("Content-Type: Application/octet-stream");
              header("Content-Length: ".filesize(DIR_FS_DOWNLOAD.$downloads['orders_products_filename']));
              header("Content-disposition: attachment; filename=\"".$downloads['orders_products_filename']."\"");
              // This will work on all systems, but will need considerable resources
              // We could also loop with fread($fp, 4096) to save memory
              readfile_chunked(DIR_FS_DOWNLOAD . $downloads['orders_products_filename'], $chunksize);
            }
          }
        } else {
          $smarty->assign('dl_exceeded', 'true');
          $smarty->assign('dl_prevented', 'true');
        }
      } else {
        // Show Downloadlink
        // Get all downloadable products in that order
        $downloads_query = xtc_db_query("select o.customers_id,
                                                o.customers_email_address,
                                                op.products_name, 
                                                opd.orders_products_download_id, 
                                                opd.orders_products_filename, 
                                                opd.download_count,
                                                opd.orders_products_id,
                                                if(opd.download_maxdays = 0, current_date, date(o.date_purchased)) + interval opd.download_maxdays + 1 day - interval 1 second download_expiry 
                                           FROM ".TABLE_ORDERS." o
                                           JOIN ".TABLE_ORDERS_PRODUCTS." op 
                                                on op.orders_id = o.orders_id
                                           JOIN ".TABLE_ORDERS_PRODUCTS_DOWNLOAD." opd 
                                                on opd.orders_products_id = op.orders_products_id
                                          WHERE o.orders_id = '".$check_status['orders_id']."'
                                            AND opd.orders_products_filename != ''
                                            AND opd.download_key = '".xtc_db_input($_GET['key'])."'
                                            AND o.customers_id = '".(int)$_SESSION['customer_id_download']."'");

        if (xtc_db_num_rows($downloads_query) > 0) {
          $jj = 0;
          while ($downloads = xtc_db_fetch_array($downloads_query)) {
            // The link will appear only if:
            // - Download remaining count is > 0, AND
            // - The file is present in the DOWNLOAD directory, AND EITHER
            // - No expiry date is enforced (maxdays == 0), OR
            // - The expiry date is not reached
            if ($downloads['download_count'] > 0 && 
                strtotime($downloads['download_expiry']) > time() && 
                file_exists(DIR_FS_DOWNLOAD.$downloads['orders_products_filename']) && 
                in_array($check_status['orders_status'], $allowed_status))
            {
              $dl[$jj]['allowed'] = true;
            }
            $dl[$jj]['pic_link'] = xtc_href_link(FILENAME_DOWNLOAD, 'order='.$check_status['orders_id'].'&id='.$downloads['orders_products_download_id'].'&key='.md5($check_status['orders_id'].$downloads['orders_products_id'].$downloads['customers_id'].$downloads['customers_email_address'].$downloads['orders_products_filename']));
            $dl[$jj]['download_link'] = '<a href="'.xtc_href_link(FILENAME_DOWNLOAD, 'order='.$check_status['orders_id'].'&id='.$downloads['orders_products_download_id'].'&key='.md5($check_status['orders_id'].$downloads['orders_products_id'].$downloads['customers_id'].$downloads['customers_email_address'].$downloads['orders_products_filename'])).'">'.$downloads['products_name'].'</a>';
            $dl[$jj]['date'] = xtc_date_long($downloads['download_expiry']);
            $dl[$jj]['count'] = $downloads['download_count'];
            $jj ++;
          }
          $smarty->assign('dl_prevented', 'true');
        } else {
          die(DOWNLOAD_NOT_ALLOWED);
        }
      }
    } else {
      die(DOWNLOAD_NOT_ALLOWED);
    }
    // Button Back to Account History for customers only
    if (isset ($_SESSION['customer_id'])) {
      $smarty->assign('BUTTON_BACK','<a href="' . xtc_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id='.(int)$_GET['order'], 'SSL') . '">' . xtc_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>');
    }
  }
} else {
  die(DOWNLOAD_NOT_ALLOWED);
}

if ($messageStack->size('download') > 0)
	$smarty->assign('error', $messageStack->output('download'));

$smarty->assign('dl', (isset($dl) ? $dl : array()));
$smarty->assign('language', $_SESSION['language']);
$smarty->assign('tpl_path', DIR_WS_BASE.'templates/'.CURRENT_TEMPLATE.'/');

$smarty->caching = 0;
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/downloads.html');

$breadcrumb->add(NAVBAR_TITLE_DOWNLOAD, xtc_href_link(FILENAME_DOWNLOAD, '', 'SSL'));
require (DIR_WS_INCLUDES.'header.php');

$smarty->assign('language', $_SESSION['language']);
$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
	$smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');

include ('includes/application_bottom.php');
?>