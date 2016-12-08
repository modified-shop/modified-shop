<?php
/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */

if (!defined('DS')) {
    define('DS', '/');
}

if (file_exists(dirname(__FILE__) . DS . 'dev.php')) {
    require_once(dirname(__FILE__).DS.'dev.php');
}

// core
require_once(dirname(__FILE__).DS.'classes'.DS.'core.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'apis.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'client.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'configuration.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'customers.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'orders.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'external_orders.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'items.php');
require_once(dirname(__FILE__).DS.'classes'.DS.'redirect.php');

// models (global / abstract)
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'Abstract.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'AbstractExport.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'XmlEmptyObject.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'XmlResultObject.php');

// models (catalog)
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Review.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Product.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Price.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'TierPrice.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Category.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'CategoryPath.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Shipping.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Manufacturer.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Visibility.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Property.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Stock.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Identifier.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Tag.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Relation.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Attribute.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Input.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Validation.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Option.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'AttributeGroup.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'catalog' . DS . 'Attribute.php');

// models (media)
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'media' . DS . 'Image.php');

// models (redirect)
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS . 'DeeplinkSuffix.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS . 'DeeplinkSuffixValue.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS
    . 'DeeplinkSuffixValueUnset.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS
    . 'DeeplinkSuffixValueDisabled.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS . 'HtmlTag.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS . 'HtmlTagAttribute.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'models' . DS . 'redirect' . DS . 'HtmlTagVariable.php');

// helpers
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'DataStructure.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'Pricing.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'String.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS
    . 'KeywordsManagerInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'KeywordsManager.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'LinkBuilderInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'LinkBuilder.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'MobileRedirectInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'MobileRedirect.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'RedirectorInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'Redirector.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS
    . 'SettingsManagerInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'SettingsManager.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'TagsGeneratorInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'TagsGenerator.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'TemplateParserInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'TemplateParser.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS
    . 'JsScriptBuilderInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'JsScriptBuilder.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'type' . DS
    . 'TypeInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'type' . DS . 'Js.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'redirect' . DS . 'type' . DS . 'Http.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'strategy' . DS
    . 'LoggingInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'legacy' . DS . 'ShopgateLogger.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'Obfuscator.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'strategy' . DS
    . 'DefaultLogging.php');

require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'stack_trace' . DS
    . 'GeneratorInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'stack_trace' . DS
    . 'GeneratorDefault.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'stack_trace' . DS
    . 'NamedParameterProviderInterface.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'logging' . DS . 'stack_trace' . DS
    . 'NamedParameterProviderReflection.php');

require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'error_handling' . DS . 'ExceptionHandler.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'error_handling' . DS . 'ErrorHandler.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'error_handling' . DS . 'ShutdownHandler.php');
require_once(dirname(__FILE__) . DS . 'classes' . DS . 'helper' . DS . 'error_handling' . DS . 'shutdown_handler' . DS
    . 'LastErrorProvider.php');

// vendors
require_once(dirname(__FILE__).DS.'vendors'.DS.'2d_is.php');
include_once(dirname(__FILE__).DS.'vendors'.DS.'JSON.php');
