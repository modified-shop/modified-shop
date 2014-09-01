<?php
/**
 * @version SOFORT Gateway 5.2.0 - $Date: 2012-09-06 13:49:09 +0200 (Thu, 06 Sep 2012) $
 * @author SOFORT AG (integration@sofort.com)
 * @link http://www.sofort.com/
 *
 * Copyright (c) 2012 SOFORT AG
 *
 * $Id: sofortOrderSynchronisation.php 3751 2012-10-10 08:36:20Z gtb-modified $
 */

require_once(DIR_FS_CATALOG."callback/sofort/ressources/scripts/sofortOrderShopTools.php");
require_once(DIR_FS_CATALOG."callback/sofort/helperFunctions.php");

class sofortOrderSynchronisation {
	
	/**
	 * if cart is edited at shop, send new data to SOFORT
	 * @param string $transactionId
	 * @param array	 $articles
	 * @param string $comment
	 */
	public function editArticlesSofort($transactionId, $articles, $comment = '') {
		$PnagInvoice = new PnagInvoice(shopGetConfigKey(), $transactionId);
		$sofortArticles = array();
		$orderId = '';
		
		$taxLow = 0;
		$taxHigh = 0;
		$subtotal = 0;
		$shipping = 0;
		$discount = array();
		$agio = array();
		
		foreach($articles as $article) {
			if ($orderId == '') {
				$orderId = (int)$article['articleOrdersId'];
			}
			
			if($article['articleQuantity'] != 'delete'){
				array_push($sofortArticles, array(
					'itemId'		=> $article['articleId'],
					'productNumber' => $article['articleNumber'],
					'title'			=> HelperFunctions::convertEncoding($article['articleTitle'],3),
					'description'	=> HelperFunctions::convertEncoding($article['articleDescription'],3),
					'quantity'		=> $article['articleQuantity'],
					'unitPrice'		=> number_format($article['articlePrice'], 2, '.', ''),
					'tax'			=> number_format($article['articleTax'], 2, '.', ''))
				);
			}
			
			switch($article['articleType']){
				case 'shipping': 	$shipping = $article['articlePrice'];
									break;
				case 'discount':	$splitItemId = explode('|', $article['articleId']);
									$discountClass = $splitItemId[1];
									array_push($discount, array(
											'class' => $discountClass,
											'value' => $article['articlePrice']));
									break;
				case 'agio':		$splitItemId = explode('|', $article['articleId']);
									$agioClass = $splitItemId[1];
									array_push($agio , array(
											'class' => $agioClass,
											'value' => $article['articlePrice']));
									break;
				case 'product':		$subtotal += ($article['articleQuantity'] * $article['articlePrice']);
									break;
			}
			
			switch ($article['articleTax']){
				case 7:		$taxLow  += ($article['articleQuantity']*$article['articlePrice']);
							break;
				case 19:	$taxHigh += ($article['articleQuantity']*$article['articlePrice']);
							break;
			}
		}
		
		$lastShopTotal = HelperFunctions::getLastFieldValueFromSofortTable($orderId,'amount');
		$time = strftime('%Y-%m-%d %H:%M:%S', time());
		
		$errors = $PnagInvoice->updateInvoice($transactionId, $sofortArticles, $comment);
		$warnings = $PnagInvoice->getWarnings();
		
		if($errors){
			return array(
					'errors'   => $errors,
					'warnings' => $warnings
			);
		}
		
		$PnagInvoice->refreshTransactionData();
		
		$orderStatus = shopDbQuery('SELECT orders_status FROM '.TABLE_ORDERS.' WHERE orders_id = "'.$orderId.'"');
		$orderStatus = shopDbFetchArray($orderStatus);
		
		$this->_insertNewTotalCommentToHistory($orderId, $orderStatus['orders_status'], $time, $PnagInvoice, $lastShopTotal);
		$this->editArticlesShop($PnagInvoice, $orderId);
		
		return false;
	}
	
	
	/**
	 * cart was edited at SOFORT-backend, apply changes in shop
	 * @param PnagInvoice $PnagInvoice
	 */
	public function editArticlesShop(PnagInvoice $PnagInvoice, $orderNumber) {
		$lng = $PnagInvoice->getLanguageCode();
		$newAmount = $PnagInvoice->getAmount();
		$invoiceArticles = $PnagInvoice->getItems();
		
		foreach ($invoiceArticles as $article) {
			$getTotalItems = explode('|', $article->itemId);
			
			if(count($getTotalItems) > '1'){
				$sofortIdArray[$getTotalItems[0]] = $getTotalItems[0];
				$sofortArticleArray[$getTotalItems[0]] = $article;
			} else {
				$ordersProductsId = $this->_getOrderProductsId($article->itemId, $orderNumber);
				$sofortIdArray[$ordersProductsId] = $ordersProductsId;
				$sofortArticleArray[$ordersProductsId] = $article;
			}
		}
		
		$shopProductsQuery = shopDbQuery("SELECT orders_products_id FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id = '".$orderNumber."'");
		
		while ($shopProductsResult = shopDbFetchArray($shopProductsQuery)) {
			$shopArticleArray[] = $shopProductsResult['orders_products_id'];
		}
		
		$taxLow = 0;
		$taxHigh = 0;
		$subtotal = 0;
		
		foreach ($shopArticleArray as $shopArticle){
			if (!in_array($shopArticle,$sofortIdArray)){
				$this->_sofortRestock($this->_getItemId($shopArticle, $orderNumber), $orderNumber, 0);
				$this->_deleteShopOrderArticle($shopArticle, $PnagInvoice->getStatusReason());
			} else {
				$qty = $sofortArticleArray[$shopArticle]->quantity;
				$price = $sofortArticleArray[$shopArticle]->unitPrice;
				$itemId = $sofortArticleArray[$shopArticle]->itemId;
				
				$this->_sofortRestock($itemId, $orderNumber, $qty);
				$this->_updateShopOrderArticle($shopArticle, $qty, $price, $PnagInvoice->getStatusReason());
				
				if ($sofortArticleArray[$shopArticle]->tax == '7.00'){
					$taxLow  += ($sofortArticleArray[$shopArticle]->quantity * $sofortArticleArray[$shopArticle]->unitPrice);
				} elseif ($sofortArticleArray[$shopArticle]->tax == '19.00'){
					$taxHigh += ($sofortArticleArray[$shopArticle]->quantity * $sofortArticleArray[$shopArticle]->unitPrice);
				}
				
				$subtotal += ($sofortArticleArray[$shopArticle]->quantity * $sofortArticleArray[$shopArticle]->unitPrice);
			}
		}
		
		$shipping = 0;
		$discount = array();
		$agio = array();
		
		foreach ($sofortIdArray as $sofortId){
			if (!in_array($sofortId, $shopArticleArray)){
				switch($sofortId){
					case 'shipping': 	$shipping = $sofortArticleArray[$sofortId]->unitPrice;
										break;
					case 'discount':	$splitItemId = explode('|', $sofortArticleArray[$sofortId]->itemId);
										$discountClass = $splitItemId[1];
										array_push($discount, array(
												'class' => $discountClass,
												'value' => $sofortArticleArray[$sofortId]->unitPrice
												)
										);
										break;
					case 'agio':		$splitItemId = explode('|', $sofortArticleArray[$sofortId]->itemId);
										$agioClass = $splitItemId[1];
										array_push($agio , array(
												'class' => $agioClass,
												'value' => $sofortArticleArray[$sofortId]->unitPrice
												)
										);
										break;
					default:			$this->_sofortRestock($sofortArticleArray[$sofortId]->itemId, $orderNumber, $sofortArticleArray[$sofortId]->quantity);
										$this->_insertShopOrderArticle($sofortArticleArray[$sofortId], $orderNumber, $lng);
										$subtotal += ($sofortArticleArray[$sofortId]->quantity * $sofortArticleArray[$sofortId]->unitPrice);
										break;
				}
				
				if ($sofortArticleArray[$sofortId]->tax == '7.00'){
					$taxLow += ($sofortArticleArray[$sofortId]->unitPrice*$sofortArticleArray[$sofortId]->quantity);
				} elseif ($sofortArticleArray[$sofortId]->tax == '19.00'){
					$taxHigh += ($sofortArticleArray[$sofortId]->unitPrice*$sofortArticleArray[$sofortId]->quantity);
				}
			}
		}
		
		$status = $PnagInvoice->getStatusReason();
		$this->_updateShopTotals($taxLow, $taxHigh, $subtotal, $newAmount, $orderNumber, $shipping, $discount, $agio, $status);
	}
	
	
	/**
	 * get SOFORT-ItemID from table sofort_products
	 * @param int $ordersProductsId
	 * @param int $ordersId
	 */
	protected function _getItemId ($ordersProductsId, $ordersId) {
		$query = "SELECT item_id FROM sofort_products WHERE orders_products_id = ".$ordersProductsId;
		$product = shopDbCheckAndFetchOne($query);
		return $product['item_id'];
	}
	
	
	/**
	 * get latest item-quantity
	 * @param int $itemId
	 * @param int $ordersId
	 */
	protected function _getLatestQuantity($itemId, $ordersId){
		$qry = "SELECT products_quantity FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_products_id = '".$this->_getOrderProductsId($itemId, $ordersId)."'";
		$res = shopDbCheckAndFetchOne($qry);
		return $res['products_quantity'];
	}
	
	
	/**
	 * get number of different products
	 * @param int $ordersId
	 */
	protected function _getNumberOfOrderProducts ($ordersId) {
		$query = "SELECT order_products_id FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id = ".$ordersId;
		$number = shopDbGetNumRows($query);
		return $number;
	}
	
	
	/**
	 * get orders_products_id from table sofort_products
	 * @param int $itemId
	 * @param int $ordersId
	 */
	protected function _getOrderProductsId ($itemId, $ordersId) {
		$query = shopDbQuery("SELECT orders_products_id FROM sofort_products WHERE item_id = '".$itemId."' AND orders_id = '".$ordersId."'");
		$product = shopDbFetchArray($query);
		return $product['orders_products_id'];
	}
	
	
	/**
	 * update quantity and price in shop order table
	 * @param int	 $ordersProductsId
	 * @param mixed	 $quantity
	 * @param float	 $unitPrice
	 * @param string $status
	 */
	protected function _updateShopOrderArticle($ordersProductsId, $quantity, $unitPrice, $status){
		$finalPrice = $quantity * $unitPrice;
		
		if ($quantity == 'delete'){
			// article was marked for removal which was already handled in editArticlesSofort() line 39
		} elseif ($quantity == 0){
			$this->_deleteShopOrderArticle($ordersProductsId, $status);
		} else {
			$query = "UPDATE ".TABLE_ORDERS_PRODUCTS." SET products_quantity = '".$quantity."', products_price = '".$unitPrice."', final_price = '".$finalPrice."' WHERE orders_products_id ='".$ordersProductsId."'";
			shopDbQuery($query);
		}
	}
	
	
	/**
	 * update shop totals
	 * @param float	 $taxLow
	 * @param float	 $taxHigh
	 * @param float	 $subtotal
	 * @param float	 $newAmount
	 * @param int	 $ordersId
	 * @param array	 $shipping
	 * @param array	 $discount
	 * @param array	 $agio
	 * @param string $status
	 * @param string $currency
	 */
protected function _updateShopTotals($taxLow, $taxHigh, $subtotal, $newAmount, $ordersId, $shipping, $discount, $agio, $status, $currency = 'EUR'){
		if ($status == 'canceled' || $status == 'confirmation_period_expired' || $status == 'refunded'){
			// in case of cancellation keep old data for replicability
			return;
		}
		
		shopDbQuery('UPDATE '.TABLE_ORDERS_TOTAL.' SET value = "'.$newAmount.'", text = "<b>'.number_format($newAmount,2,",",".").' '.$currency.'</b>" WHERE orders_id = "'.$ordersId.'" AND class = "ot_total"');
		shopDbQuery('UPDATE '.TABLE_ORDERS_TOTAL.' SET value = "'.$subtotal.'" , text = "'.number_format($subtotal,2,",",".").' '.$currency.'" WHERE orders_id = "'.$ordersId.'" AND class = "ot_subtotal"');
		
		$shippingText = number_format($shipping ,2,",",".").' '.$currency;
		$this->_checkAndUpdateTotal($ordersId, 'ot_shipping', 'Versandkosten:', $shippingText, $shipping, MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER);
		
		if ($taxLow  != 0) $taxLowValue  = (($taxLow /107)* 7);
		$taxLowText = number_format($taxLowValue ,2,",",".").' '.$currency;
		$this->_checkAndUpdateTotal($ordersId, 'ot_tax', 'zzgl. 7% MwSt.:', $taxLowText, $taxLowValue, MODULE_ORDER_TOTAL_TAX_SORT_ORDER, ' AND title LIKE  "%7%"');
		
		if ($taxHigh != 0) $taxHighValue = (($taxHigh/119)*19);
		$taxHighText = number_format($taxHighValue ,2,",",".").' '.$currency;
		$this->_checkAndUpdateTotal($ordersId, 'ot_tax', 'zzgl. 19% MwSt.:', $taxHighText, $taxHighValue, MODULE_ORDER_TOTAL_TAX_SORT_ORDER, ' AND title LIKE  "%19%"');
		
		
		if(count($discount)!=0){
			foreach ($discount as $position){
				$text = "<span style='color: red;'>".number_format($position['value'],2,',','.')." ".$currency."</span>";
				$this->_checkAndUpdateTotal($ordersId, $position['class'], 'Rabatt:', $text, $position['value'], MODULE_ORDER_TOTAL_DISCOUNT_SORT_ORDER);
			}
		}
		
		if(count($agio)!=0){
			foreach ($agio as $position){
				$text = number_format($position['value'],2,",",".").' '.$currency;
				$this->_checkAndUpdateTotal($ordersId, $position['class'], 'Zuschlag:', $text, $position['value'], MODULE_ORDER_TOTAL_DISCOUNT_SORT_ORDER);
			}
		}
	}
	
	
	/**
	 * Checks if total exists; updates or deletes if true; inserts if false
	 * @param int	 $ordersId
	 * @param string $class
	 * @param string $title
	 * @param string $text
	 * @param float	 $value
	 * @param string $addCond
	 */
	protected function _checkAndUpdateTotal($ordersId, $class, $title, $text, $value, $sortOrder, $addCond = ''){
		if(shopDbNumRows(shopDbQuery('SELECT * FROM '.TABLE_ORDERS_TOTAL.' WHERE orders_id = "'.$ordersId.'" AND class = "'.$class.'"'.$addCond)) != 0) {
			if ($value != 0 || $class == 'ot_shipping'){
				shopDbQuery('UPDATE '.TABLE_ORDERS_TOTAL.' SET value = "'.$value.'" , text = "'.$text.'" WHERE orders_id = "'.$ordersId.'" AND class = "'.$class.'"'.$addCond);
			} else {
				shopDbQuery('DELETE FROM '.TABLE_ORDERS_TOTAL.' WHERE orders_id = "'.$ordersId.'" AND class = "'.$class.'"'.$addCond);
			}
		} else {
			if ($value != 0 || $class == 'ot_shipping'){
				$data = array(
						'value'		=> $value,
						'text'		=> $text,
						'orders_id' => $ordersId,
						'class'		=> $class,
						'title'		=> $title,
						'sort_order'=> $sortOrder
				);
				shopDbPerform(TABLE_ORDERS_TOTAL, $data);
			}
		}
	}
	
	
	/**
	 * delete article from shop order
	 * @param int	 $ordersProductsId
	 * @param string $status
	 */
	protected function _deleteShopOrderArticle($ordersProductsId, $status){
		if ($status == 'canceled' || $status == 'refunded' || $status == 'confirmation_period_expired'){
			// in case of cancellation keep old data for replicability
		} else {
			$query = "DELETE FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_products_id = '".$ordersProductsId."'";
			shopDbQuery($query);
			$query = "DELETE FROM ".TABLE_ORDERS_PRODUCTS_ATTRIBUTES." WHERE orders_products_id = '".$ordersProductsId."'";
			shopDbQuery($query);
		}
	}
	
	
	/**
	 * insert article in shop order (e.g. during an undo operation)
	 * @param object $sofortItem
	 * @param int	 $ordersId
	 * @param string $lng
	 */
	protected function _insertShopOrderArticle($sofortItem, $ordersId, $lng){
		$itemId = $sofortItem->itemId;
		
		$splitItemId = explode('{',$itemId);
		$productId = $splitItemId[0];
		
		if(count($splitItemId) == '1'){
			$hasAttributes = false;
		} else {
			$hasAttributes = true;
			
			for ($i=1;$i<count($splitItemId);++$i){
				$attrId = explode('}',$splitItemId[$i]);
				$attributes[] = array(
						'optionsId'		  => $attrId[0],
						'optionsValuesId' => $attrId[1]
				);
			}
		}
		
		$data = array(
				'orders_id'			=> $ordersId,
				'products_id'		=> $productId,
				'products_model'	=> $sofortItem->productNumber,
				'products_name'		=> HelperFunctions::convertEncoding($sofortItem->title,2),
				'products_price'	=> $sofortItem->unitPrice,
				'final_price'		=> ($sofortItem->unitPrice * $sofortItem->quantity),
				'products_tax'		=> $sofortItem->tax,
				'products_quantity' => $sofortItem->quantity,
				'allow_tax'			=> '1',
		);
		shopDbPerform(TABLE_ORDERS_PRODUCTS, $data);
		$insertId = xtc_db_insert_id();
		
		shopDbQuery('UPDATE sofort_products SET orders_products_id ="'.$insertId.'" WHERE orders_id = "'.$ordersId.'" AND item_id = "'.$itemId.'"');
		
		if($hasAttributes) {
			$lngId = shopDbFetchArray(shopDbQuery("SELECT languages_id FROM ".TABLE_LANGUAGES." WHERE code = '".$lng."'"));
			
			foreach($attributes as $attribute){
				$queryTpa = shopDbQuery("SELECT options_values_price, price_prefix FROM ".TABLE_PRODUCTS_ATTRIBUTES." WHERE products_id ='".$productId."' AND options_id = '".$attribute['optionsId']."' AND options_values_id ='".$attribute['optionsValuesId']."'");
				$resultTpa = shopDbFetchArray($queryTpa);
				
				$queryTpo = shopDbQuery("SELECT products_options_name FROM ".TABLE_PRODUCTS_OPTIONS." WHERE products_options_id = '".$attribute['optionsId']."' AND language_id = '".$lngId['languages_id']."'");
				$resultTpo = shopDbFetchArray($queryTpo);

				$queryTpov = shopDbQuery("SELECT products_options_values_name FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." WHERE products_options_values_id = '".$attribute['optionsValuesId']."' AND language_id = '".$lngId['languages_id']."'");
				$resultTpov = shopDbFetchArray($queryTpov);
				
				$data = array(
						'orders_id'				  => $ordersId,
						'orders_products_id'	  => $insertId,
						'products_options'		  => $resultTpo['products_options_name'],
						'products_options_values' => $resultTpov['products_options_values_name'],
						'options_values_price'	  => $resultTpa['options_values_price'],
						'price_prefix'			  => $resultTpa['price_prefix']
				);
				shopDbPerform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $data);
			}
		}
	}
	
	
	/**
	 * restock shop articles during cartsynchronization, cancelation etc.
	 * @param string $itemId
	 * @param int	 $ordersId
	 * @param int	 $newQty
	 * @param int	 $oldQty
	 */
	protected function _sofortRestock($itemId, $ordersId, $newQty, $oldQty = 'aaaa') {
		if(!is_numeric($oldQty)){
			$oldQty = $this->_getLatestQuantity($itemId, $ordersId);
		}
		
		$splitItemId = explode('{',$itemId);
		$productId = $splitItemId[0];
		
		for ($i=1;$i<count($splitItemId);++$i){
			$attrId = explode('}',$splitItemId[$i]);
			$optionsId[] = $attrId[0];
			$optionsValuesId[] = $attrId[1];
		}
		
		$diff = $oldQty - $newQty;
		
		$updateTP = "UPDATE ".TABLE_PRODUCTS." SET products_quantity = products_quantity + ".$diff." WHERE products_id = '".$productId."'";
		shopDbQuery($updateTP);
		
		if(isset($optionsId) && isset($optionsValuesId) && (substr(HelperFunctions::getIniValue('shopsystemVersion'),0,3)!='osc') && (substr(HelperFunctions::getIniValue('shopsystemVersion'),0,3)!='zen')){
			for($i=0;$i<count($optionsId);++$i){
				$updateTPA = "UPDATE ".TABLE_PRODUCTS_ATTRIBUTES." SET attributes_stock = attributes_stock + ".$diff." WHERE products_id = '".$productId."' AND options_id = '".$optionsId[$i]."' AND options_values_id ='".$optionsValuesId[$i]."'";
				shopDbQuery($updateTPA);
			}
		}
	}
	
	
	/**
	 * inserts a "new total" comment into shop order status history
	 * @param int		  $orderId
	 * @param string	  $status
	 * @param date		  $time
	 * @param PnagInvoice $PnagInvoice
	 * @param float		  $lastShopTotal
	 */
	protected function _insertNewTotalCommentToHistory($orderId, $status, $time, PnagInvoice $PnagInvoice, $lastShopTotal){
		$newTotal = $PnagInvoice->getAmount();
		
		if ($newTotal > $lastShopTotal) {
			$comment = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CART_RESET.' '.MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CURRENT_TOTAL.' '.$newTotal.' Euro '.MODULE_PAYMENT_SOFORT_SR_TRANSLATE_TIME.': '.$time;
		} else {
			$comment = MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CART_EDITED.' '.MODULE_PAYMENT_SOFORT_SR_TRANSLATE_CURRENT_TOTAL.' '.$newTotal.' Euro '.MODULE_PAYMENT_SOFORT_SR_TRANSLATE_TIME.': '.$time;
		}
		
		$sqlDataArray = array(
				'orders_id'			=> (int)$orderId,
				'orders_status_id'	=> $status,
				'date_added'		=> 'now()',
				'customer_notified' => 0,
				'comments'			=> $comment,
		);
		shopDbPerform(TABLE_ORDERS_STATUS_HISTORY, $sqlDataArray);
		
		$sofortOrdersId = (shopDbFetchArray(shopDbQuery('SELECT id FROM sofort_orders WHERE orders_id = "'.$orderId.'"')));
		HelperFunctions::insertSofortOrdersNotification($sofortOrdersId['id'], $PnagInvoice, $comment, $comment);
	}
}
?>