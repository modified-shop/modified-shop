<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'generic/genericFunctions.php');

function updateIdealoInventoryByEdit($mpID, $updateData) {
    $updateItem = genericInventoryUpdateByEdit($mpID, $updateData);        
    if (!is_array($updateItem)) {
        return false;
    }
    try {
        $result = MagnaConnector::gi()->submitRequest(array(
            'ACTION' => 'UpdateItems',
            'SUBSYSTEM' => 'ComparisonShopping',
            'SEARCHENGINE' => 'idealo',
            'MARKETPLACEID' => $mpID,
            'DATA' => array($updateItem),
        ));
        #echo print_m($result, '$result');
    } catch (MagnaException $e) {
        if ($e->getCode() == MagnaException::TIMEOUT) {
            $e->saveRequest();
            $e->setCriticalStatus(false);
        }
        #echo print_m($e->getErrorArray(), '$error');
    }
}

function idealoGetShippingMethods(&$form) {
    if (!class_exists('Shipping')) {
        require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/Shipping.php');
    }
    $shippingClass = new Shipping();
    $shippingMethods = $shippingClass->getShippingMethods();
    $form['values'] = array(
        '__ml_lump' => ML_COMPARISON_SHOPPING_LABEL_LUMP,
        '__ml_config' => ML_COMPARISON_SHOPPING_LABEL_CONFIG
    );
    if (SHOPSYSTEM == 'gambio') {
        $form['values']['__ml_gambio'] = ML_COMPARISON_SHOPPING_LABEL_ARTICLE_SHIPPING_COSTS;
    }
    if (MagnaDB::gi()->columnExistsInTable('products_weight', TABLE_PRODUCTS)) {
        $form['values']['__ml_weight'] = ML_LABEL_SHIPPINGCOSTS_EQ_ARTICLEWEIGHT;
    }
    if (!empty($shippingMethods)) {
        foreach ($shippingMethods as $method) {
            if ($method['code'] == 'gambioultra') continue;
            $form['values'][$method['code']] = fixHTMLUTF8Entities($method['title']);
        }
    }
    unset($shippingClass);
}
