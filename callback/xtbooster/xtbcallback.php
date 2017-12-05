<?php
##
## xs:booster v1.042 für xt:Commerce und Gambio
## Copyright (c) 2008-2009 xs:booster Limited
## http://www.xsbooster.com
##
## Licensed under GNU/GPL
##

@set_time_limit(0);
if (!defined('DIR_FS_DOCUMENT_ROOT')) {
    chdir('../../');
    require_once 'includes/application_top.php';	# This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
}
require (DIR_WS_CLASSES.'xtbooster.php');

function xsb_session_register($var) {
	if (!isset($_SESSION[$var])) {
		if (isset(${$var})) {
			$_SESSION[$var] = ${$var};
		} else {
			$_SESSION[$var] = null;
		}
	}
}

function xsb_session_unregister($var) {
	if (isset($_SESSION[$var])) {
		unset($_SESSION[$var]);
	}
}

$xtb = new xtbooster_base;
$xtb->config();

if(defined('XTB_CHECKOUT_PROCESS'))
{
  if(is_array($_SESSION['xtb0']['tx']))
  {
  	foreach($_SESSION['xtb0']['tx'] as $tx) {
		xtc_db_query("UPDATE xtb_auctions SET QUANTITY_CHECKED_OUT=QUANTITY_CHECKED_OUT+".$tx['XTB_QUANTITYPURCHASED']." WHERE _EBAY_ITEM_ID=".$tx['_EBAY_ITEM_ID']);
		xtc_db_query("UPDATE xtb_transactions SET XTB_CHECKOUT_TS=UNIX_TIMESTAMP(), XTC_ORDER_ID='".$last_order."' WHERE XTB_KEY='".$tx['XTB_KEY']."'");
	}
  }
  xsb_session_unregister('xtb0');
  xsb_session_unregister('xtb1');
}
else
{

if(isset($_GET['reverse']))
{
	if(is_array($_SESSION['xtb2']))
	{
		$_SESSION=$_SESSION['xtb2'];
		$_SESSION['cart']->reset();

		require_once (DIR_FS_INC.'xtc_write_user_info.inc.php');
		xtc_write_user_info((int) $_SESSION['customer_id']);

		header("Location: ./".(defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/')."xtbooster.php?xtb_module=list&filter=1");
		exit;
	}
}

if(isset($_GET['item'])&&isset($_GET['key']))
{
	if(preg_match("/xtbooster\.php/",$_SERVER['HTTP_REFERER']))
	{
		if(!function_exists("_sess_open")&&STORE_SESSIONS=='mysql')
			require(DIR_WS_FUNCTIONS.'sessions.php');

		$xtb2 = $_SESSION;
		unset($xtb2['xtb2']);
		xtc_session_destroy();
		unset ($_SESSION['customer_id']);
		unset ($_SESSION['customer_default_address_id']);
		unset ($_SESSION['customer_first_name']);
		unset ($_SESSION['customer_country_id']);
		unset ($_SESSION['customer_zone_id']);
		unset ($_SESSION['comments']);
		unset ($_SESSION['user_info']);
		unset ($_SESSION['customers_status']);
		unset ($_SESSION['selected_box']);
		unset ($_SESSION['navigation']);
		unset ($_SESSION['shipping']);
		unset ($_SESSION['payment']);
		unset ($_SESSION['ccard']);
		unset ($_SESSION['gv_id']);
		unset ($_SESSION['cc_id']);
		// GV Code End
		$_SESSION['cart']->reset();
	
		if(STORE_SESSIONS=='mysql')
			session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');

     	xtc_session_start();
		xtc_session_register("xtb2"); # hidden admin session
		$_SESSION['xtb2']=$xtb2;

		$_SESSION['cart'] = new shoppingCart();
	}

	xsb_session_register('xtb0');
	xsb_session_register('xtb1');
	
	# - herausfinden um welchen ebay user es sich handelt
	# - transaktionsids des users finden und loopen, um warenkorb zu fuellen
	
	$q = xtc_db_query("SELECT * FROM xtb_transactions as t LEFT JOIN xtb_auctions as a ON (a._EBAY_ITEM_ID=t.XTB_ITEM_ID) WHERE t.XTB_KEY = '".$_GET['key']."' AND MD5(CONCAT(t.XTB_ITEM_ID,'',t.XTB_EBAY_USERID)) = '".$_GET['item']."' AND t.XTB_CHECKOUT_TS=0");
	if(xtc_db_num_rows($q)!=1) die("Sorry, die Auktion konnte nicht gefunden werden. Bitte wenden Sie sich an den Kundenservice");
	$tx = xtc_db_fetch_array($q);
	$XTB_EBAY_USERID = $tx['XTB_EBAY_USERID'];
	
	$allbasket = true;
	$auctions = array();

	$q = xtc_db_query("SELECT * FROM xtb_transactions as t LEFT JOIN xtb_auctions as a ON (a._EBAY_ITEM_ID=t.XTB_ITEM_ID) WHERE t.XTB_EBAY_USERID='".$XTB_EBAY_USERID."' AND t.XTB_CHECKOUT_TS=0");
	if(xtc_db_num_rows($q)==0) die("Sorry, die Auktion konnte nicht gefunden werden. Bitte wenden Sie sich an den Kundenservice");
	while($tx1=xtc_db_fetch_array($q)) {
		if($tx1['XTB_REDIRECT_USER_TO']!='basket') { $allbasket=false; }
		if(@isset($tx1['DESCRIPTION'])) unset($tx1['DESCRIPTION']);
		$auctions[]=$tx1;
	}	

	$requestx = "ACTION: TradeTemplateFetch";
	$res = $xtb->parse($xtb->exec($requestx));
	$_SESSION['xtb0']['DEFAULT_CUSTOMER_GROUP']=$res['DEFAULT_CUSTOMER_GROUP'];
	$_SESSION['xtb0']['tx']=$auctions;

	if( $allbasket ) {

		#
		# Die Weiterleitung auf den Warenkorb ermoeglicht die Zusammenfuehrung mehrerer Auktionen in einen Warenkorb
		# since v1.041
		#
		
		foreach($auctions as $tx) {
			if($_SESSION['language_charset']!='utf-8')
				foreach($tx as $key=>$value) $tx[$key] = utf8_decode($value);
			$products_qty = ($products_qty>MAX_PRODUCTS_QTY) ? MAX_PRODUCTS_QTY : $tx['XTB_QUANTITYPURCHASED'];
			$_SESSION['cart']->add_cart((int) $tx['products_id'], $_SESSION['cart']->get_quantity(xtc_get_uprid($tx['products_id'], 1)) + xtc_remove_non_numeric($products_qty),1);
		}

		xtc_redirect(xtc_href_link("shopping_cart.php"));

	} else {

		# Zusammenfuehrung der Auktionen nur bei Weiterleitung aller Produkte auf Warenkorb moeglich
		#

		$products_qty = $tx['XTB_QUANTITYPURCHASED'];
		$products_qty = ($products_qty>MAX_PRODUCTS_QTY) ? MAX_PRODUCTS_QTY : $tx['XTB_QUANTITYPURCHASED'];

		if($tx['XTB_REDIRECT_USER_TO']=='basket') {
			$_SESSION['cart']->add_cart((int) $tx['products_id'], $_SESSION['cart']->get_quantity(xtc_get_uprid($tx['products_id'], 1)) + xtc_remove_non_numeric($products_qty),1);
			xtc_redirect(xtc_href_link("shopping_cart.php"));
		} elseif($tx['XTB_REDIRECT_USER_TO']=='product') {
			xtc_redirect(xtc_href_link("product_info.php?". xtc_product_link($tx['products_id']))); # since v1.0321
		} elseif($tx['XTB_REDIRECT_USER_TO']=='create_guest_account') { # since v1.0310
			$_SESSION['cart']->add_cart((int) $tx['products_id'], $_SESSION['cart']->get_quantity(xtc_get_uprid($tx['products_id'], 1)) + xtc_remove_non_numeric($products_qty),1);
			xtc_redirect(xtc_href_link("create_guest_account.php"));
		} elseif($tx['XTB_REDIRECT_USER_TO']=='create_account') { # since v1.0310
			$_SESSION['cart']->add_cart((int) $tx['products_id'], $_SESSION['cart']->get_quantity(xtc_get_uprid($tx['products_id'], 1)) + xtc_remove_non_numeric($products_qty),1);
			xtc_redirect(xtc_href_link("create_account.php"));
		}
	}

	exit;
}

if(isset($_GET['inquire'])) {
	switch($_GET['inquire']) {
		case 'version':
			$res = $xtb->exec("ACTION:ReportInquiredInfo\nCASE:version\nCASE_VERSION:".XTBOOSTER_VERSION."\nREMOTE_ADDR:".$_SERVER["REMOTE_ADDR"]."\n"); $res = $xtb->parse($res);
			if($res['RESULT']!='SUCCESS') { echo "FAILURE;".time().";".md5(microtime()); break; }
			break;
		case 'sqlaction':
			$res = $xtb->exec("ACTION:ReportInquiredInfo\nCASE:sqlaction\nCASEACTION:inquire\nCASE_VERSION:".XTBOOSTER_VERSION."\nREMOTE_ADDR:".$_SERVER["REMOTE_ADDR"]."\n"); $res = $xtb->parse($res);
			if($res['RESULT']!='SUCCESS') { echo "FAILURE;".time().";".md5(microtime()); break; }
			$QUERY = trim($res['QUERY']); $INQUIRY_ID = $res['INQUIRY_ID'];
			$QUERY = str_replace("#TABLE_AUCTIONS#","xtb_auctions",$QUERY);
			$QUERY = str_replace("#TABLE_TRANSACTIONS#","xtb_transactions",$QUERY);
			$rs = xtc_db_query($QUERY); $SQLACTION_RESULT = xtc_db_affected_rows()." rows affected";			
			$res = $xtb->exec("ACTION:ReportInquiredInfo\nCASE:sqlaction\nCASEACTION:report\nINQUIRY_ID:".$INQUIRY_ID."\nSQLACTION_RESULT:$SQLACTION_RESULT\nCASE_VERSION:".XTBOOSTER_VERSION."\n");
			break;
		default:
			echo "NOT_AVAILABLE;".time().";".md5(microtime());
			break;
	}
	exit;
}


if(isset($_GET['showmessage'])&&isset($_GET['key']))
{
        # HTML-Mail vom Server abrufen und anzeigen
        $res = $xtb->exec("ACTION:ShowHtmlMail\nKEY:".$_GET['key']);
        $res = $xtb->parse($res);
        echo $res['MAIL_BODY'];
        exit;
}

$sql = "SELECT * FROM xtb_auctions WHERE _EBAY_ITEM_ID = '".$_GET['EID']."' AND _XTB_ITEM_HASH='".$_GET['ITEM_HASH']."' AND _EBAY_QUANTITY_BUYED < QUANTITY";
$products_query = xtc_db_query($sql);
if(xtc_db_num_rows($products_query)==1)
{
	#
	# Status der Auktion ueber xs:booster System abrufen und
	# anschliessend E-Mail Template mit Hash zurück an xs:booster uebergeben..
	# Dann wird ueber xs:booster eine eBay interne Nachricht an den Kaeufer
	# zugestellt und dieser ueber den Link zum Checkout informiert.
	#
	$x = xtc_db_fetch_array($products_query);
	$res = $xtb->exec("ACTION: TransactionInformations\nITEM_ID: ".$x['_EBAY_ITEM_ID']."\nXTB_STATUS_ID: ".$_GET['XSI']."\n");
	$res_ti = $res = $xtb->parse($res);

	#
	# Produktdaten fuer die E-Mail zusammenstellen
	#
	$product_data_query = xtc_db_query("SELECT * FROM " . TABLE_PRODUCTS . " p, " . TABLE_LANGUAGES . " l, " . TABLE_PRODUCTS_DESCRIPTION . " pd left join ".TABLE_PRODUCTS_IMAGES." as pi ON (pi.products_id = pd.products_id) WHERE p.products_id = pd.products_id AND pd.language_id = l.languages_id AND l.code = '" . $_GET['TEMPLATES_LANGUAGE']. "' AND p.products_id = '".$x['products_id']."'");
	if(xtc_db_num_rows($product_data_query) < 1)
	{ # nix da: Sprache falsch gesetzt? Versuch's mal ohne
		$product_data_query = xtc_db_query("SELECT * FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd left join ".TABLE_PRODUCTS_IMAGES." as pi ON (pi.products_id = pd.products_id) WHERE p.products_id = pd.products_id AND p.products_id = '".$x['products_id']."'");
	}
	$product_data = xtc_db_fetch_array($product_data_query);
	$article_title = base64_encode(stripslashes(utf8_decode($product_data['products_name'])));
	$article_subtitle = base64_encode(stripslashes(utf8_decode($product_data['products_short_description'])));
	$article_description = base64_encode(stripslashes($product_data['products_description']));
	$article_number = base64_encode(stripslashes($product_data['products_model']));
	$article_vpe = base64_encode(stripslashes($product_data['products_vpe_value']));
	if(0 === strpos($product_data['products_image'],'http://'))
	{ $images[0] = base64_encode(stripslashes($product_data['products_image'])); }
	else
	{ $images[0] = base64_encode(HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_POPUP_IMAGES.stripslashes($product_data['products_image'])); }
	do {
		if(empty($product_data['image_name'])) continue;
		if(0 === strpos($product_data['image_name'],'http://'))
		{ $images[$product_data['image_nr']] = base64_encode(stripslashes($product_data['image_name'])); }
		else
		{ $images[$product_data['image_nr']] = base64_encode(HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_POPUP_IMAGES.stripslashes($product_data['image_name'])); }
	} while($product_data = xtc_db_fetch_array($product_data_query));
	$key = md5(microtime().rand(1,999999));
	$x = "ACTION: TransactionSendMail\nKEY:".$key."\nITEM_TXID: ".$res['ITEM_TXID']."\n";
	$x .="ARTICLE_TITLE: -=$article_title\nARTICLE_SUBTITLE: -=$article_subtitle\n"
	   . "ARTICLE_DESCRIPTION: -=$article_description\nARTICLE_NUMBER: -=$article_number\n"
	   . "ARTICLE_VPE: -=$article_vpe\n";
	$i=0; while(isset($images[$i])){
		$x .= "PICTURE_" . (int)($i+1) .": -=".$images[$i] . "\n";
		$i++;
	      }
	$x .="\n";

	##
	## Daten des Kaeufers lokal speichern und xs:booster zum
	## Versand der E-Mail ueber eBay veranlassen. $key referenzieren..
	##
	
	$res = $xtb->exec($x);
	$res = $xtb->parse($res);

	if($res['RESULT']!='SUCCESS')
	{
		echo "FAILURE;".time().";".md5(microtime());
		exit;
	}

	$sql = "INSERT INTO xtb_transactions (
				XTB_ITEM_ID,
				XTB_KEY,
				XTB_AMOUNTPAID,
				XTB_AMOUNTPAID_CURRENCY,
				XTB_QUANTITYPURCHASED,
				XTB_EBAY_USERID,
				XTB_EBAY_EMAIL,
				XTB_EBAY_SITE,
				XTB_EBAY_NAME,
				XTB_EBAY_STREET,
				XTB_EBAY_CITY,
				XTB_EBAY_STATEORPROVINCE,
				XTB_EBAY_COUNTRYNAME,
				XTB_EBAY_PHONE,
				XTB_EBAY_POSTALCODE,
				XTB_EBAY_TS,
				XTB_REDIRECT_USER_TO,
				XTB_ALLOW_USER_CHQTY
			)
			VALUES
			(
				'".$res_ti['ITEMID']."',
				'$key',
				'".$res_ti['AMOUNTPAID']."',
				'".$res_ti['AMOUNTPAID_CURRENCY']."',
				'".$res_ti['QUANTITYPURCHASED']."',
				'".$res_ti['USERID']."',
				'".$res_ti['EMAIL']."',
				'".$res_ti['SITE']."',
				'".$res_ti['NAME']."',
				'".$res_ti['STREET']."',
				'".$res_ti['CITYNAME']."',
				'".$res_ti['STATEORPROVINCE']."',
				'".$res_ti['COUNTRYNAME']."',
				'".$res_ti['PHONE']."',
				'".$res_ti['POSTALCODE']."',
				UNIX_TIMESTAMP(),
				'".$res_ti['XTB_REDIRECT_USER_TO']."',
				'".$res_ti['XTB_ALLOW_USER_CHQTY']."'
			)
	";
	xtc_db_query($sql);

	# Anzahl der erworbenen Auktionen erhoehen
	xtc_db_query("UPDATE xtb_auctions SET _EBAY_QUANTITY_BUYED = _EBAY_QUANTITY_BUYED+".$res_ti['QUANTITYPURCHASED']." WHERE _EBAY_ITEM_ID=".$res_ti['ITEMID']);
} else {
	echo "FAILURE;".time().";".md5(microtime());
	exit;
}

echo "OK;".time().";".md5(microtime());
exit;
}

?>
