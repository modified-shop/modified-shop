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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/Configurator.php');
include_once(DIR_MAGNALISTER_INCLUDES.'lib/configFunctions.php');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/FileBrowserHelper.php');

class MagnaCompatibleConfigure extends MagnaCompatibleBase {
	protected $marketplace = '';
	protected $marketplaceTitle = '';
	protected $mpID = 0;
	protected $lang = '';
	protected $isAjax = false;
	protected $authConfigKeys = array();
	protected $missingConfigKeys = array();

	protected $form = array ();

	/**
	 * Write HTML for alert boxes in here.
	 *
	 * Always concatenate this string to not delete previous boxes.
	 *
	 * @var string
	 */
	protected $boxes = '';
	
	protected $isAuthed = false;

	protected $exchangeRateField = false; // for exchange rate alert: leave false for default
	
	public function __construct(&$params) {
		global $_modules, $_lang;
		
		parent::__construct($params);
		$this->marketplaceTitle = $_modules[$this->marketplace]['title'];
		
		$this->lang = $_lang;
		
		$this->resources['url']['mode'] = 'conf';
	}
	
	protected function formExists($name) {
		$path = '%s/%s.form';
		$lpath = sprintf($path, strtolower($this->marketplace), $name);
		if (file_exists(DIR_MAGNALISTER_FS.'config/'.$this->lang.'/'.$lpath)) {
			return $lpath;
		}
		$lpath = sprintf($path, 'modules', $name);
		if (file_exists(DIR_MAGNALISTER_FS.'config/'.$this->lang.'/'.$lpath)) {
			return $lpath;
		}
		return false;
	}
	
	protected function getFormFiles() {
		return array (
			'login', 'prepare', 'checkin', 
			'price', 'inventorysync', 'orders',
			'setImagePath'
		);
	}
	
	protected function getForms($files = array()) {
		if (empty($files)) {
			$files = $this->getFormFiles();
		}
		$forms = array();
		foreach ($files as $f) {
			if (($f = $this->formExists($f)) === false) continue;
			$forms[$f] = array();
		}
		return $forms;
	}
	
	protected function loadConfigFormFile($file, $replace = array(), $unset = array()) {
		$fC = file_get_contents($file);
		$replace['http:\/\/www.IhrShop.de\/'] = trim(json_encode(HTTP_SERVER.DIR_WS_CATALOG), ' "\/').'\/';
		if (!empty($replace)) {
			$fC = str_replace(array_keys($replace), array_values($replace), $fC);
		}
		$fC = json_decode($fC, true);
		if (!empty($unset)) {
			foreach ($unset as $key) {
				unset($fC[$key]);
			}
		}
		return $fC;
	}

	protected function loadConfigForm($files, $replace = array()) {
		//echo print_m($files, __METHOD__);
		$form = array();
		foreach ($files as $file => $options) {
			$fC = $this->loadConfigFormFile(
				DIR_MAGNALISTER_FS.'config/'.$this->lang.'/'.$file,
				$replace,
				array_key_exists('unset', $options) ? $options['unset'] : array()
			);
            //if (!is_array($fC)) {
            //    $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
            //    $baseurlpart = explode('magnalister.php', $_SERVER["REQUEST_URI"]);
            //    $sPHPFileName = str_replace('.form', '.php', $file);
            //    file_put_contents(DIR_MAGNALISTER_FS.'config/'.$this->lang.'/'.$sPHPFileName, file_get_contents(DIR_MAGNALISTER_FS.'config/'.$this->lang.'/'.$file));
            //    $pageURL .= $_SERVER["SERVER_NAME"].($_SERVER["SERVER_PORT"] != "80" ? ":".$_SERVER["SERVER_PORT"] : '').$baseurlpart[0];
            //    $pageURL .= DIR_MAGNALISTER_WS.'config/'.$this->lang.'/'.$sPHPFileName;
            //    $fC = json_decode(file_get_contents($pageURL), true);
            //}
			if (!is_array($fC)) {
				$this->boxes .= '<p class="errorBox">'.$file.' could not be loaded.</p>';
				continue;
			}
			//echo var_dump_pre($fC, $file);
			$form = array_merge_recursive_simple($form, $fC);
		}
		return $form;
	}

	protected function loadChoiseValues() {
		if (isset($this->form['prepare']['fields']['lang'])) {
			mlGetLanguages($this->form['prepare']['fields']['lang']);
		}
		if (isset($this->form['prepare']['fields']['manufacturerfilter'])) {
			mlGetManufacturers($this->form['prepare']['fields']['manufacturerfilter']);
		}
		
		if (isset($this->form['price']['fields']['whichprice'])) {
			mlGetCustomersStatus($this->form['price']['fields']['whichprice'], false);
			if (!empty($this->form['price']['fields']['whichprice'])) {
				$this->form['price']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
				ksort($this->form['price']['fields']['whichprice']['values']);
			} else {
				unset($this->form['price']['fields']['whichprice']);
			}
		}
		if (isset($this->form['orders']['fields']['openstatus'])) {
			mlGetOrderStatus($this->form['orders']['fields']['openstatus']);
			mlGetCustomersStatus($this->form['orders']['fields']['customersgroup']);
		}
		if (isset($this->form['orders']['fields']['defaultshipping'])) {
			mlGetShippingModules($this->form['orders']['fields']['defaultshipping']);
		}
		if (isset($this->form['orders']['fields']['defaultpayment'])) {
			mlGetPaymentModules($this->form['orders']['fields']['defaultpayment']);
		}
		if (isset($this->form['checkin']['fields']['imagepath'])) {
			$this->form['checkin']['fields']['imagepath']['default'] =
				defined('DIR_WS_CATALOG_POPUP_IMAGES')
					? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES
					: HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES;
		}
        if (isset( $this->form['magnalisterinvoice'])) {
            try {
                $result = MagnaConnector::gi()->submitRequest(array(
                    'ACTION'    => 'GetLink2PublicInvoicesDirectory',
                    'SUBSYSTEM' => $this->marketplace,
                ));
                $invoiceDirButtonText = $this->form['magnalisterinvoice']['fields']['invoice.invoicedir']['buttontext'];
                $this->form['magnalisterinvoice']['fields']['invoice.invoicedir']['value'] =
                    '<a class="ml-button" target="_blank" title="'.$invoiceDirButtonText.'" href="'.$result['DATA'].'">'.$invoiceDirButtonText.'</a>';
            } catch (MagnaException $ex) {
            }
        }
	}

	protected function renderAuthError() {
		$authError = getDBConfigValue($this->marketplace.'.autherror', $this->mpID, '');
		$mpTimeOut = false;
		$errors = array();
		if (is_array($authError) && !empty($authError) 
			&& isset($authError['ERRORS']) && !empty($authError['ERRORS'])
		) {
			foreach ($authError['ERRORS'] as $err) {
				$errors[] = fixHTMLUTF8Entities($err['ERRORMESSAGE']);
				if (isset($err['ERRORCODE']) && ($err['ERRORCODE'] == 'MARKETPLACE_TIMEOUT')) {
					$mpTimeOut = true;
				}
			}
		}
		if ($mpTimeOut) {
			return '<p class="errorBox">
				<span class="error bold larger">'.ML_ERROR_LABEL.':</span>
				'.ML_ERROR_MARKETPLACE_TIMEOUT.'
			</p>';
		}
		return '<p class="errorBox">
			<span class="error bold larger">'.ML_ERROR_LABEL.':</span>
			'.sprintf(ML_MAGNACOMPAT_ERROR_ACCESS_DENIED, $this->marketplaceTitle).(
				(!empty($errors))
					? '<br /><br />'.implode('<br />', $errors)
					: ''
			).'</p>';
	}
	
	protected function processPasswordFromPost($key, $val) {
		/* password already saved */
		if (empty($val) && (getDBConfigValue($this->marketplace.'.'.$key, $this->mpID) == '__saved__')) {
			return '__saved__';
		}
		/* Invalid passwords */
		if (empty($val)
		    /*               Windows                                Mac                */
			|| (strpos($val, '&#9679;') !== false) || (strpos($val, '&#8226;') !== false)
		) {
			return false;
		}

		return $val;
	}
	
	protected function getAuthValuesFromPost() {
		$nUser = trim($_POST['conf'][$this->marketplace.'.username']);
		$nPass = trim($_POST['conf'][$this->marketplace.'.password']);
		$nPass = $this->processPasswordFromPost('password', $nPass);
		
		if (empty($nUser)) {
			unset($_POST['conf'][$this->marketplace.'.username']);
		}
		if ($nPass === false) {
			unset($_POST['conf'][$this->marketplace.'.password']);
			return false;
		}
		return array (
			'USERNAME' => $nUser,
			'PASSWORD' => $nPass,
		);
	}
	
	protected function processAuth() {
		$auth = getDBConfigValue($this->marketplace.'.authed', $this->mpID, false);
		$missingKeys = array();
		if ((!is_array($auth) || !$auth['state'])
			&& allRequiredConfigKeysAvailable($this->authConfigKeys, $this->mpID, false, $missingKeys)
			&& !(
				array_key_exists('conf', $_POST)
				&& allRequiredConfigKeysAvailable($this->authConfigKeys, $this->mpID, $_POST['conf'])
			)
		) {
			$this->boxes .= $this->renderAuthError();
		}
		#echo print_m($missingKeys, '$missingKeys');
		#echo print_m($this->authConfigKeys, '$this->authConfigKeys');
		if (!array_key_exists('conf', $_POST)) {
			$this->isAuthed = is_array($auth) && isset($auth['state']) && $auth['state'];
			return;
		}
		
		if (($request = $this->getAuthValuesFromPost()) !== false) {
			setDBConfigValue($this->marketplace.'.authed', $this->mpID, array (
				'state' => false,
				'expire' => time()
			), true);

            if($this->needToSetCredential()) {
                if (empty($request)) return;
                foreach ($request as $v) {
                    if (empty($v)) {
                        return;
                    }
                }

                $request['ACTION'] = 'SetCredentials';
                #echo print_m(json_indent(json_encode($request)));
                try {
                    $result = MagnaConnector::gi()->submitRequest($request);
                    $this->boxes .= '
					<p class="successBox">'.ML_GENERIC_STATUS_LOGIN_SAVED.'</p>';
					
			} catch (MagnaException $e) {
				$this->boxes .= '
					<p class="errorBox">'.ML_GENERIC_STATUS_LOGIN_SAVEERROR.'</p>';

                }
            }
			try {
				$r = MagnaConnector::gi()->submitRequest(array(
					'ACTION' => 'IsAuthed',
				));
				#echo print_m($r, '$r');
				$auth = array (
					'state' => true,
					'expire' => time() + 60 * 30
				);
				$this->isAuthed = true;
				setDBConfigValue($this->marketplace.'.authed', $this->mpID, $auth, true);
			} catch (MagnaException $e) {
				$e->setCriticalStatus(false);
				setDBConfigValue($this->marketplace.'.autherror', $this->mpID, $e->getErrorArray(), false);
				$this->boxes .= $this->renderAuthError();
			}
			
		} else {
			$this->boxes .= '
				<p class="errorBox">'.ML_ERROR_INVALID_PASSWORD.'</p>';
		}
		
	}
	
	/* Can be extended by extending classes */
	protected function finalizeForm() {
		// Tracking-Code-Matching only one of both settings for carrier is set display notice
		if (( isset($_POST['conf'][$this->marketplace.'.orderstatus.carrier.default'])
				&& isset($_POST['conf'][$this->marketplace.'.orderstatus.carrier.dbmatching.table']['table'])
				&& isset($_POST['conf'][$this->marketplace.'.orderstatus.trackingcode.dbmatching.table']['table'])
			)
			&& (( empty($_POST['conf'][$this->marketplace.'.orderstatus.carrier.default'])
					&& empty($_POST['conf'][$this->marketplace.'.orderstatus.carrier.dbmatching.table']['table'])
				)
				&& !empty($_POST['conf'][$this->marketplace.'.orderstatus.trackingcode.dbmatching.table']['table'])
			)
		) {
			$this->boxes .= '<p class="errorBox">'.ML_GENERIC_ERROR_TRACKING_CODE_MATCHING.'</p>';
		}

        if (isset($this->form['erpinvoice']['fields'])) {
            foreach ($this->form['erpinvoice']['fields'] as &$aField) {
                $aField['default'] = MLFileBrowserHelper::gi()->getAndGenerateErpDirectoryPath(DIR_MAGNALISTER_FS.$aField['default']);

            }
        }
	}

	protected function loadChoiseValuesAfterProcessPOST() { }

	public function process() {
		$this->form = $this->loadConfigForm(
			$this->getForms(), 
			array(
				'_#_platform_#_' => $this->marketplace,
				'_#_platformName_#_' => $this->marketplaceTitle
			)
		);
		$this->processAuth();
		$this->loadChoiseValues();
		$this->finalizeForm();
		
		$cG = new MLConfigurator($this->form, $this->mpID, 'conf_magnacompat');
		$cG->setRenderTabIdent(true);
		$allCorrect = $cG->processPOST();
		$this->loadChoiseValuesAfterProcessPOST();

		if ($this->isAjax) {
			echo $cG->processAjaxRequest();
		} else {
			echo $this->boxes;
			if (array_key_exists('sendTestmail', $_POST)) {
				if ($allCorrect) {
					if (sendTestMail($this->mpID)) {
						echo '<p class="successBox">'.ML_GENERIC_TESTMAIL_SENT.'</p>';
					} else {
						echo '<p class="successBox">'.ML_GENERIC_TESTMAIL_SENT_FAIL.'</p>';
					}
				} else {
					echo '<p class="noticeBox">'.ML_GENERIC_NO_TESTMAIL_SENT.'</p>';
				}
			}
			echo $cG->renderConfigForm();
			echo $cG->exchangeRateAlert($this->exchangeRateField);

			//require_once(DIR_MAGNALISTER_INCLUDES . 'lib/classes/ShopAddOns.php');
			//ML_ShopAddOns::generateConfigPopupOnCombobox('FastSyncInventory', "config_{$this->marketplaceTitle}_stocksync_tomarketplace", "#config_{$this->marketplaceTitle}", "$(this).val() == 'auto_fast'");
		}
	}

    public static function invoicePreview($args, &$value = '') {
        global $_MagnaSession, $_url;
        return '<input class="ml-button" type="button" value="Vorschau" id="ml-amazon-invoice-preview"/>
	
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$(\'#ml-amazon-invoice-preview\').click(function() {
		jQuery.blockUI(blockUILoading);
		jQuery.ajax({
			\'method\': \'get\',
			\'url\': \''.toURL($_url, array('what' => 'TestInvoiceGeneration', 'kind' => 'ajax'), true).'\',
			\'success\': function (data) {
				if (data.indexOf(\'<style\') > 0) {
					data=data.substring(0, data.indexOf(\'<style\'));
				}
				jQuery.unblockUI();
				myConsole.log(\'ajax.success\', data);
				if (data === \'error\') {
				} else {
                    var hwin = window.open(data, "popup", "resizable=yes,scrollbars=yes");
                    if (hwin.focus) {
                        hwin.focus();
                    }
				}
			}
		});
	});
});
/*]]>*/</script>';
    }


    protected function invoiceOptionJS() {
        ob_start();
        ?>
        <script type="text/javascript">/*<![CDATA[*/
            $(document).ready(function () {
                var invoiceMagnalisterGenerator = $(".ml-magnalisterInvoiceGenerator");
                var invoiceERPGenerator = $(".ml-erpInvoice");
                var invoiceOption = $('.ml-uploadInvoiceOption');
                invoiceOption.on('change', function () {
                    invoiceERPGenerator.hide();
                    invoiceERPGenerator.find('*').prop( "disabled", true );
                    invoiceMagnalisterGenerator.hide();
                    invoiceMagnalisterGenerator.find('*').prop( "disabled", true );
                    if (this.value === 'magna') {
                        invoiceMagnalisterGenerator.show();
                        invoiceMagnalisterGenerator.find('*').prop( "disabled", false );
                    } else if (this.value === 'erp') {
                        invoiceERPGenerator.show();
                        invoiceERPGenerator.find('*').prop( "disabled", false );
                    }
                });
                invoiceOption.change();

            });
            /*]]>*/</script>
        <?php
        $sJSOutPut = ob_get_clean();
        return $sJSOutPut;
    }

    protected function needToSetCredential() {
        return true;
    }
}


if (isset($_GET['what'])) {
    if ($_GET['what'] === 'TestInvoiceGeneration') {
        $iframeURL = 'error';
        try {
            //*
            $result = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'TestInvoiceGeneration'
            ));
            $iframeURL = $result['DATA']['URL'];
            //*/
        } catch (MagnaException $e) {
        }
        echo $iframeURL;
        exit();
    }
}
