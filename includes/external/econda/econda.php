<?php
/* -----------------------------------------------------------------------------------------
   $Id: econda.php ???? 2005-11-29 14:50:00Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2005 osCommerce(econda.php,v 1.42 2003/06/10); www.econda.de

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------

   Copyright (c) 2005 ECONDA GmbH Karlsruhe
   All rights reserved.

   ECONDA GmbH
   Haid-und-Neu-Str. 7
   76131 Karlsruhe
   Tel. +49 (721) 6635726
   Fax +49 (721) 66499070
   info@econda.de
   www.econda.de

*/


echo "\n<!-- Econda-Monitor -->\n";

// cPath = id1_id2 => name1/name2
function product_path_by_name($product, $lang) {
	require_once (DIR_FS_INC.'xtc_get_product_path.inc.php');
	$product_path_by_id = xtc_get_product_path($product);
	$product_categories_id = explode("_" , $product_path_by_id);

	$new_product_path_by_name = '';

	for ($i = 0, $n = sizeof($product_categories_id); $i < $n; $i++) {
		$product_path_by_name_query = xtc_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . $product_categories_id[$i] . "' and language_id = '". (int)$lang ."'");
		$product_path_by_name = xtc_db_fetch_array($product_path_by_name_query);
		$new_product_path_by_name .= $product_path_by_name['categories_name'];
		if (($i+1) < $n) {
			$new_product_path_by_name .= '/';
		}
	}
	return $new_product_path_by_name;
}

function product_to_EMOSItem($product, $lang, $quant, $cedit_id = 0) {
	require_once (DIR_FS_INC.'xtc_get_tax_rate.inc.php');
	require_once (DIR_FS_CATALOG.'includes/classes/xtcPrice.php');
	$product_to_emos_query = xtc_db_query("select p.products_id, pd.products_name, p.products_model, p.products_price, p.products_tax_class_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$product. "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$lang . "'");
	$product_to_emos = xtc_db_fetch_array($product_to_emos_query);
	$emos_xtPrice = new xtcPrice(DEFAULT_CURRENCY,$_SESSION['customers_status']['customers_status_id']);
	$product_to_emos_price = $emos_xtPrice->xtcGetPrice($product_to_emos['products_id'], false, $quant, $product_to_emos['products_tax_class_id'], $product_to_emos['products_price'], '', $cedit_id);
	if (ECONDA_PRICE_IS_BRUTTO == 'false') {
		$product_to_emos_price = sprintf("%0.2f",($product_to_emos_price / ((xtc_get_tax_rate($product_to_emos['products_tax_class_id']) + 100) / 100)));
	}
	$item = new EMOS_Item();
	$item->productID = $product_to_emos['products_id'];
	$item->productName = $product_to_emos['products_name'];
	$item->price = $product_to_emos_price;
	$item->productGroup = product_path_by_name((int)$product, (int)$lang)."/". $product_to_emos['products_name'];
	$item->quantity = (int)$quant;
	return $item;
}

global $breadcrumb;
global $product;
global $shop_content_data;
global $listing_split;
global $_GET;

// new instance
$emos = new EMOS();
$emos->prettyPrint();

// Startseite >> Katalog >> Kategorie >> .. => Startseite/Katalog/Kategorie/..
$emos->addContent($breadcrumb->econda());

// login erfolgreich
if (isset($_SESSION['login_success']) && $_SESSION['login_success']) {
	$emos->addLogin($_SESSION['customer_id'],'0');
	unset($_SESSION['login_success']);
}

// $current_page = basename($PHP_SELF);
// $current_page = split('\?', basename($_SERVER['PHP_SELF'])); $current_page = $current_page[0]; // for BadBlue(Win32) webserver compatibility
$current_page = join('',preg_grep("/.+\.php$/", preg_split("/\?|\//", $_SERVER['PHP_SELF'])));
switch ($current_page) {
	case FILENAME_PRODUCT_INFO:
		if (is_object($product) && $product->isProduct() === true) {
			$item = product_to_EMOSItem($product->data['products_id'],$_SESSION['languages_id'], 1);
			$emos->addDetailView($item);
		}
		break;
	case FILENAME_CONTENT:
		if ($_GET['coID'] == '7') $emos->addContact($shop_content_data['content_heading']);
		break;
	case FILENAME_ADVANCED_SEARCH_RESULT:
		$numRows = $listing_split->number_of_rows;
		if(!$numRows) $numRows = 0;
		if ($error == 0 || $keyerror == 1) $emos->addSearch($_GET['keywords'],$numRows);
		break;
	case FILENAME_CREATE_ACCOUNT:
		if($messageStack){
			if ($messageStack->size('create_account') > 0) { // Registrierung fehlerhaft
				$emos->addRegister('0','1'); // no customer_id given, dummy id
				$emos->addOrderProcess("2_Anmelden/Neu");
			} elseif ($_SESSION['customer_id']) { // Registrierung erfolgreich
				$emos->addRegister($_SESSION['customer_id'],'0');
				$emos->addOrderProcess("2_Anmelden/Erfolg");
			}
		}
		break;
	case FILENAME_LOGIN:
		if (!$_SESSION['login_success']) { // Login fehlerhaft
			$emos->addLogin('0','1'); // no customer_id given, dummy id
			$emos->addOrderProcess("2_Anmelden/Fehler");
		} else {
			$emos->addOrderProcess("2_Anmelden");
		}
		break;
	case FILENAME_SHOPPING_CART:
		$emos->addOrderProcess("1_Warenkorb");
		if ($_SESSION['econda_cart']) {
			for ($i=0, $n=sizeof($_SESSION['econda_cart']); $i<$n; $i++) {
				if ($_SESSION['econda_cart'][$i]['todo'] == 'update') {
					if ($_SESSION['econda_cart'][$i]['cart_qty'] == $_SESSION['econda_cart'][$i]['old_qty']) {
						// nop
					} else {
						$new_qty = $_SESSION['econda_cart'][$i]['cart_qty'] - $_SESSION['econda_cart'][$i]['old_qty'];
						$item = product_to_EMOSItem($_SESSION['econda_cart'][$i]['id'],$_SESSION['languages_id'], abs($new_qty));
						if ($new_qty < 0) {
							$emos->removeFromBasket($item);
						} else {
							$emos->addToBasket($item);
						}
					}
				} elseif ($_SESSION['econda_cart'][$i]['todo'] == 'add') {
					$item = product_to_EMOSItem($_SESSION['econda_cart'][$i]['id'],$_SESSION['languages_id'], $_SESSION['econda_cart'][$i]['cart_qty'] - $_SESSION['econda_cart'][$i]['old_qty']);
					$emos->addToBasket($item);
				} elseif ($_SESSION['econda_cart'][$i]['todo'] == 'del') {
					$item = product_to_EMOSItem($_SESSION['econda_cart'][$i]['id'],$_SESSION['languages_id'], $_SESSION['econda_cart'][$i]['cart_qty']);
					$emos->removeFromBasket($item);
				}
			}
			unset($_SESSION['econda_cart']);
		}
	break;
		case FILENAME_CHECKOUT_SHIPPING:
		$emos->addOrderProcess("3_Versand/");
	break;
		case FILENAME_CHECKOUT_SHIPPING_ADDRESS:
		$emos->addOrderProcess("3_Versand/Lieferadresse");
		break;
	case FILENAME_CHECKOUT_PAYMENT:
		$emos->addOrderProcess("4_Zahlung");
		break;
	case FILENAME_CHECKOUT_PAYMENT_ADDRESS:
		$emos->addOrderProcess("4_Zahlung/Rechnungsadresse");
		break;
	case FILENAME_CHECKOUT_CONFIRMATION:
		$emos->addOrderProcess("5_Bestaetigung");
		break;
	case FILENAME_CHECKOUT_SUCCESS:
		$emos->addOrderProcess("6_Erfolg");
		// billing daten
		$last_orders_query = xtc_db_query("select orders_id, customers_city, customers_postcode, customers_country from "
					. TABLE_ORDERS . " where customers_id = '" . (int)$_SESSION['customer_id']
					. "' order by date_purchased desc limit 1");
		$last_orders = xtc_db_fetch_array($last_orders_query);
		// basket daten
		$last_orders_products_query = xtc_db_query("select products_id, products_quantity, products_price, products_tax from "
						. TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$last_orders['orders_id']
						. "' order by orders_products_id");
		$count = 0;
		$basket = array();
		$last_orders_totalprice = 0;
		while ($last_orders_products = xtc_db_fetch_array($last_orders_products_query)) {
			if (ECONDA_PRICE_IS_BRUTTO == 'false') {
				$last_orders_totalprice += $last_orders_products['products_price'] * $last_orders_products['products_quantity'] / (1+$last_orders_products['products_tax']/100);
			} else {
				$last_orders_totalprice += $last_orders_products['products_price'] * $last_orders_products['products_quantity'];
			}
			$item = product_to_EMOSItem($last_orders_products['products_id'],$_SESSION['languages_id'], $last_orders_products['products_quantity']);
			$basket[$count] = $item;
			$count++;
		}
		$emos->addEmosBillingPageArray($last_orders['orders_id'],
			$_SESSION['customer_id'],
			sprintf("%0.2f",$last_orders_totalprice),
			$last_orders['customers_country'],
			$last_orders['customers_postcode'],
			$last_orders['customers_city']);
		$emos->addEmosBasketPageArray($basket);
		break;
	default:
	break;
}
// output

echo $emos->toString();

echo "\n<!-- Econda-Monitor -->\n";
?>
