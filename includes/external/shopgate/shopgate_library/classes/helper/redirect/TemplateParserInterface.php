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
interface Shopgate_Helper_Redirect_TemplateParserInterface
{
	const FUNCTION_NAME_HEX        = 'hex';
	const FUNCTION_NAME_URLENCODED = 'urlencoded';
	const FUNCTION_NAME_BASE64     = 'base64';
	const FUNCTION_NAME_ESCAPED    = 'escaped';
	
	/**
	 * @param $template
	 *
	 * @return Shopgate_Model_Redirect_HtmlTagVariable[]
	 */
	public function getVariables($template);
	
	/**
	 * @param string                                  $template
	 * @param Shopgate_Model_Redirect_HtmlTagVariable $variable
	 * @param string                                  $replacement
	 *
	 * @return string
	 */
	public function process($template, $variable, $replacement);
	
	/**
	 * @param string $value
	 * @param string $functionName
	 *
	 * @return string
	 */
	public function filterVariableValue($value, $functionName = '');
}