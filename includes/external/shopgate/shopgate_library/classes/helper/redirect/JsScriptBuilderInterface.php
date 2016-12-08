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
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain
 * unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */
interface Shopgate_Helper_Redirect_JsScriptBuilderInterface
{
	/**
	 * @param string $pageType
	 * @param array  $parameters [string, string]
	 *
	 * @return string
	 */
	public function buildTags($pageType, $parameters = array());

	/**
	 * Sets the file path of javascript template
	 * to use
	 *
	 * @param string $filePath
	 *
	 * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
	 */
	public function setJsTemplateFilePath($filePath);

	/**
	 * Helps set all parameters at once
	 *
	 * @param array $params - array(key => value)
	 *
	 * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
	 */
	public function setSiteParameters($params);

	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
	 */
	public function setSiteParameter($key, $value);

	/**
	 * @param string $file
	 *
	 * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
	 */
	public function setTemplateFile($file);

	/**
	 * Prints a value to JS script to prevent
	 * web app redirect
	 *
	 * @param bool $param
	 *
	 * @return Shopgate_Helper_Redirect_JsScriptBuilderInterface
	 */
	public function suppressWebAppRedirect($param);
}
