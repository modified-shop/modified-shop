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
class Shopgate_Helper_Redirect_TemplateParser implements Shopgate_Helper_Redirect_TemplateParserInterface
{
	public function getVariables($template)
	{
		$matches = array();
		if (!preg_match_all('/{([\w:]+)}/', $template, $matches)) {
			return array();
		}
		
		$variables = array();
		foreach ($matches[1] as $variable) {
			$parts = explode(':', $variable);
			
			$variable = new Shopgate_Model_Redirect_HtmlTagVariable();
			$variable->setName($parts[0]);
			
			if (!empty($parts[1])) {
				$variable->setFunctionName($parts[1]);
			}
			
			$variables[] = $variable;
		}
		
		return $variables;
	}
	
	public function process($template, $variable, $replacement)
	{
		$variableString = '{' . $variable->getName();
		
		if ($variable->getFunctionName()) {
			$variableString .= ':' . $variable->getFunctionName();
		}
		
		$variableString .= '}';
		
		return str_replace(
			$variableString,
			$this->filterVariableValue($replacement, $variable->getFunctionName()),
			$template
		);
	}
	
	public function filterVariableValue($value, $functionName = '')
	{
		switch ($functionName) {
			case Shopgate_Helper_Redirect_TemplateParserInterface::FUNCTION_NAME_HEX:
				return bin2hex($value);
			case Shopgate_Helper_Redirect_TemplateParserInterface::FUNCTION_NAME_URLENCODED:
				return urlencode($value);
			case Shopgate_Helper_Redirect_TemplateParserInterface::FUNCTION_NAME_BASE64:
				return base64_encode($value);
			case Shopgate_Helper_Redirect_TemplateParserInterface::FUNCTION_NAME_ESCAPED:
				return addslashes(htmlentities($value));
		}
		
		return $value;
	}
}