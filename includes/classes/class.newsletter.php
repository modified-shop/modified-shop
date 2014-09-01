<?php

/*------------------------------------------------------------------------------
   $Id: class.newsletter.php 4203 2013-01-10 20:36:14Z Tomcraft1980 $
   
   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2005 xt:Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce www.oscommerce.com 
   (c) 2003	 nextcommerce www.nextcommerce.org
   
   XTC-NEWSLETTER_RECIPIENTS RC1 - Contribution for XT-Commerce http://www.xt-commerce.com
   by Matthias Hinsche http://www.gamesempire.de
   
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class newsletter {
	var $message, $message_id;
	
	function newsletter() {
		$this->auto = false;
	}

	function RemoveFromList($key, $mail) {
		require_once (DIR_FS_INC.'xtc_validate_password.inc.php');
		
	$check_mail_query = xtc_db_query("select customers_email_address, mail_key from ".TABLE_NEWSLETTER_RECIPIENTS." where customers_email_address = '".xtc_db_input($mail)."' and mail_key = '".xtc_db_input($key)."'");
	if (!xtc_db_num_rows($check_mail_query)) {
		$this->message = TEXT_EMAIL_NOT_EXIST;
		$this->message_id = 1;
		
	} else {
		$check_mail = xtc_db_fetch_array($check_mail_query);
		if (!xtc_validate_password($mail, $key)) {
			$this->message = TEXT_EMAIL_DEL_ERROR;
			$this->message_id = 2;
		} else {
			$del_query = xtc_db_query("delete from ".TABLE_NEWSLETTER_RECIPIENTS." where  customers_email_address ='".xtc_db_input($mail)."' and mail_key = '".xtc_db_input($key)."'");
			$this->message = TEXT_EMAIL_DEL;
			$this->message_id = 3;
		}
	}

	}

	function ActivateAddress($key, $email) {
		
	$check_mail_query = xtc_db_query("select mail_key from ".TABLE_NEWSLETTER_RECIPIENTS." where customers_email_address = '".xtc_db_input($email)."'");
	if (!xtc_db_num_rows($check_mail_query)) {
		$this->message = TEXT_EMAIL_NOT_EXIST;
		$this->message_id = 4;
	} else {
		$check_mail = xtc_db_fetch_array($check_mail_query);
		if (!$check_mail['mail_key'] == $_GET['key']) {
			$this->message = TEXT_EMAIL_ACTIVE_ERROR;
			$this->message_id = 5;
		} else {
			xtc_db_query("update ".TABLE_NEWSLETTER_RECIPIENTS." set mail_status = '1' where customers_email_address = '".xtc_db_input($email)."'");
			$this->message = TEXT_EMAIL_ACTIVE;
			$this->message_id = 6;
		}
	}

	}
	
	function AddUserAuto($mail) {
		$this->auto = true;
		$this->AddUser('inp',-1,$mail);
				
	}

	function AddUser($check,$postCode,$mail) {
//		global $db;
			// Check if email exists 
			
			$this->generateCode();

	if (($check == 'inp') && (($postCode == $_SESSION['vvcode'] && $_SESSION['vvcode']!='') || $this->auto==true)) {

		$check_mail_query = xtc_db_query("select customers_email_address, mail_status from ".TABLE_NEWSLETTER_RECIPIENTS." where customers_email_address = '".xtc_db_input($mail)."'");
		if (!xtc_db_num_rows($check_mail_query)) {
		

			if (isset ($_SESSION['customer_id'])) {
				$customers_id = $_SESSION['customer_id'];
				$customers_status = $_SESSION['customers_status']['customers_status_id'];
				$customers_firstname = $_SESSION['customer_first_name'];
				$customers_lastname = $_SESSION['customer_last_name'];
			} else {

				$check_customer_mail_query = xtc_db_query("select customers_id, customers_status, customers_firstname, customers_lastname, customers_email_address from ".TABLE_CUSTOMERS." where customers_email_address = '".xtc_db_input($mail)."'");
				if (!xtc_db_num_rows($check_customer_mail_query)) {
					$customers_id = '0';
					$customers_status = '1';
					$customers_firstname = TEXT_CUSTOMER_GUEST;
					$customers_lastname = '';
				} else {
					$check_customer = xtc_db_fetch_array($check_customer_mail_query);
					$customers_id = $check_customer['customers_id'];
					$customers_status = $check_customer['customers_status'];
					$customers_firstname = $check_customer['customers_firstname'];
					$customers_lastname = $check_customer['customers_lastname'];
				}

			}

			$sql_data_array = array ('customers_email_address' => xtc_db_input($mail), 'customers_id' => xtc_db_input($customers_id), 'customers_status' => xtc_db_input($customers_status), 'customers_firstname' => xtc_db_input($customers_firstname), 'customers_lastname' => xtc_db_input($customers_lastname), 'mail_status' => '0', 'mail_key' => xtc_db_input($this->vlCode), 'date_added' => 'now()');
			xtc_db_perform(TABLE_NEWSLETTER_RECIPIENTS, $sql_data_array);

			$this->message = TEXT_EMAIL_INPUT;
			$this->message_id = 7;


				$this->sendRequestMail($mail);
					

		} else {
			$check_mail = xtc_db_fetch_array($check_mail_query);

			if ($check_mail['mail_status'] == '0') {

				$this->message = TEXT_EMAIL_EXIST_NO_NEWSLETTER;
				$this->message_id = 8;

				$this->sendRequestMail($mail);

			} else {
				$this->message = TEXT_EMAIL_EXIST_NEWSLETTER;
				$this->message_id = 9;
			}

		}

	} else {
		$this->message = TEXT_WRONG_CODE;
		$this->message_id = 10;
	}

	if (($check == 'del') && ($postCode == $_SESSION['vvcode'])) {
		
		$check_mail_query = xtc_db_query("select customers_email_address from ".TABLE_NEWSLETTER_RECIPIENTS." where customers_email_address = '".xtc_db_input($mail)."'");
		if (!xtc_db_num_rows($check_mail_query)) {
			$this->message = TEXT_EMAIL_NOT_EXIST;
			$this->message_id = 11;
		} else {
			$del_query = xtc_db_query("delete from ".TABLE_NEWSLETTER_RECIPIENTS." where customers_email_address ='".xtc_db_input($mail)."'");
			$this->message = TEXT_EMAIL_DEL;
			$this->message_id = 12;
		}
	}
		
		

	}

	function sendRequestMail($mail) {
				
		$smarty = new Smarty;
		$link = xtc_href_link(FILENAME_NEWSLETTER, 'action=activate&email='.xtc_db_input($mail).'&key='.$this->vlCode, 'NONSSL');

		// assign language to template for caching
		$smarty->assign('language', $_SESSION['language']);
		$smarty->assign('tpl_path', 'templates/'.CURRENT_TEMPLATE.'/');
		$smarty->assign('logo_path', HTTP_SERVER.DIR_WS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/img/');

		// assign vars
		$smarty->assign('EMAIL', xtc_db_input($mail));
		$smarty->assign('LINK', $link);
		// dont allow cache
		$smarty->caching = false;

		$html_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/newsletter_mail.html');
		$txt_mail = $smarty->fetch(CURRENT_TEMPLATE.'/mail/'.$_SESSION['language'].'/newsletter_mail.txt');
		
		$email_subject = $mailer->subject;

		if (SEND_EMAILS == true) {
				xtc_php_mail(EMAIL_SUPPORT_ADDRESS, EMAIL_SUPPORT_NAME, xtc_db_input($mail), '', '', EMAIL_SUPPORT_REPLY_ADDRESS, EMAIL_SUPPORT_REPLY_ADDRESS_NAME, '', '', TEXT_EMAIL_SUBJECT, $html_mail, $txt_mail);
			}
		
		

	}

	function generateCode() {
		require_once (DIR_FS_INC.'xtc_random_charcode.inc.php');
		$this->vlCode = xtc_random_charcode(32);
	}
	
	function RemoveLinkAdmin($key,$mail) {
		
		return HTTP_CATALOG_SERVER.DIR_WS_CATALOG.FILENAME_CATALOG_NEWSLETTER.'?action=remove&email='.$mail.'&key='.$key;
		
		
	}
		
}

?>