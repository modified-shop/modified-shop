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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MagnaError {

	private static $instance = null;
	private $exceptionCollection = array();

	private function __construct() {}
	private function __clone() {}

	public static function gi() {
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __destruct() {
		if (!empty($this->exceptionCollection)) {
			//@file_put_contents(dirname(__FILE__).'/allErrors.log', '==== '.date('Y-m-d H:i:s')." ====\n".print_r($this->exceptionCollection, true), FILE_APPEND);
		}
	}

	public function addMagnaException($e) {
		$this->exceptionCollection[] = $e;
	}

	public function getExceptionCollection() {
		return $this->exceptionCollection;
	}

	public function exceptionsToJSON() {
		$jsonArray = array();
		if (!empty($this->exceptionCollection)) {

			foreach ($this->exceptionCollection as $exc) {
				if (!$exc->isCritical()) continue;
				$eContent = array();
				$response = $exc->getErrorArray();

				$eContent['MESSAGE'] = $exc->getMessage();
				if ($exc->getCode() == NO_RESPONSE) {
					$eContent['API_ACTION'] = '';
					$eContent['ERRORS'] = '';
				} else {
					$eContent['API_ACTION'] = $response['REQUEST']['ACTION'];
					$eContent['ERRORS'] = $response['ERRORS'];
				}
				$jsonArray[] = $eContent;
			}
		}
		return json_encode($jsonArray);
	}
	
	public function exceptionsToHTML($toJSON = true) {
		$html = '';
		if (!empty($this->exceptionCollection)) {
			$idOdd = true;
			foreach ($this->exceptionCollection as $exc) {
				if (!$exc->isCritical()) continue;
				if ($html == '') {
					$html = '
						<table><thead><tr>
							<th class="action">'.ML_ERROR_LABEL_API_FUNCTION.'</th>
							<th class="level">'.ML_ERROR_LABEL_LEVEL.'</th>
							<th class="type">'.ML_ERROR_LABEL_SUBSYSTEM.'</th>
							<th class="message">'.ML_ERROR_LABEL_MESSAGE.'</th>
						</tr></thead>';
				}
				$response = $exc->getErrorArray();
				if (!is_array($response) || !isset($response['ERRORS'])) {
					$response = array(
						'ERRORS' => array(array(
							'SUBSYSTEM' => 'Core',
							'ERRORLEVEL' => 'FATAL',
							'ERRORMESSAGE' => $exc->getMessage(),
							'REPLY' => $exc->getErrorArray(),
						))
					);
				}
				$response['REQUEST'] = $exc->getRequest();
				$response['ACTION'] = $exc->getAction();
				$response['SUBSYSTEM'] = $exc->getSubsystem();

				$html .= '<tbody class="'.(($idOdd = !$idOdd) ? 'odd' : 'even').'">
					<tr>
						<td class="action" rowspan="'.count($response['ERRORS']).'">'.
							$response['ACTION'].
							((MAGNA_DEBUG) 
								? '<textarea wrap="off" spellcheck="false" readonly="readonly">'.print_r($exc->getDebugBacktrace(), true).'</textarea>'
								: '').
						'</td>
				';
				foreach ($response['ERRORS'] as $error) {
					/* Fatale Fehler in PHP */
					if (array_key_exists('REPLY', $error) && !is_array($error['REPLY']) && (stripos($error['REPLY'], 'error') !== false)) {
						$error['SUBSYSTEM'] = 'PHP';
						$error['ERRORMESSAGE'] = $error['REPLY'];
						unset($error['REPLY']);
					}
					if (array_key_exists('REPLY', $error) && is_array($error['REPLY'])) {
						$error['REPLY'] = print_r($error['REPLY'], true);
					}
					if ($error['SUBSYSTEM'] == 'PHP') {
						$error['ERRORMESSAGE'] = str_replace(
							array('href=\'', '<br />'), 
							array('href=\'http://www.php.net/', ''),
							$error['ERRORMESSAGE']
						);
					} else {
						$error['ERRORMESSAGE'] = fixHTMLUTF8Entities($error['ERRORMESSAGE']);
					}
					$html .= '
							<td class="level '.strtolower($error['ERRORLEVEL']).'">'.$error['ERRORLEVEL'].'</td>
							<td class="type">'.$response['SUBSYSTEM'].'</td>';

					$errorMessage = $error['ERRORMESSAGE'];
					unset($error['ERRORLEVEL']);
					unset($error['SUBSYSTEM']);
					unset($error['ERRORMESSAGE']);
					unset($error['ERRORNUMBER']); // Ist bedeutungslos
					$addDebug = '';
					if (!empty($error)) {
						foreach ($error as $key => $value) {
							if (is_array($value)) {
								if (empty($value)) continue;
								$value = fixHTMLUTF8Entities(print_r($value, true));
							}
							$addDebug .= $key.': '.$value."\n";
						}
					} else if ((count($response['ERRORS']) == 1) && (!is_array($r = $exc->getResponse()))) {
						$addDebug = $r;
						$errorMessage = ML_INTERNAL_INVALID_RESPONSE;
					}
					$html .= '
							<td class="message">
								'.$errorMessage.(!empty($addDebug) ? '<pre>'.$addDebug.'</pre>' : '').'
							</td>
						</tr>
						<tr>';
				}
				$html = substr($html, 0, -4).'</tbody>';
			}
			if ($html != '') {
				$html .= '</table>';
			}
		}
		return $toJSON ? json_encode(trim($html)) : $html;
	}
}
