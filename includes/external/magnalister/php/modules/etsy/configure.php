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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');
require_once(DIR_MAGNALISTER_MODULES.'etsy/EtsyHelper.php');

class EtsyConfigure extends MagnaCompatibleConfigure {

    protected function getAuthValuesFromPost() {
        $aData = parent::getAuthValuesFromPost();

        $sLanguage = trim($_POST['conf'][$this->marketplace.'.shop.language']);
        if (empty($sLanguage)) {
            unset($_POST['conf'][$this->marketplace.'.shop.language']);
        }

        if (!is_array($aData)) $aData = array();
        return array_merge($aData, array('LANGUAGE' => $sLanguage));
    }

    protected function getFormFiles() {
        return array (
            'login', 'prepare', 'checkin',
            'price', 'inventorysync',
            'orders', 'etsyOrderStatus',
            'setImagePath'
        );
    }

    public static function EtsyGetToken($args, &$value = '') {
        global $_MagnaSession, $_url;
        $expires = getDBConfigValue('etsy.token.expires', $_MagnaSession['mpID'], '');
        $apiRequest = 'GetTokenCreationLink';
        $buttonId = 'requestToken';
        /*if ($this->isAuthed) $firstToken = ''; // TODO schau wie das isAuthed besser lösen
        else*/ $firstToken = ' mlbtn-action';
        return '<input class="ml-button'.$firstToken.' mlbtn-action" type="button" value="'.ML_EBAY_BUTTON_TOKEN_NEW.'" id="'.$buttonId.'"/>
        '.$expires.'
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
        $(\'#'.$buttonId.'\').click(function() {
                jQuery.blockUI(blockUILoading);
                jQuery.ajax({
                        \'method\': \'get\',
                        \'url\': \''.toURL($_url, array('what' => $apiRequest, 'kind' => 'ajax'), true).'\',
                        \'success\': function (data) {
                                // some shop systems attach error messages, warnings or even notices
                                // to the output, which would be fatal here, so we strip it away
                                if (data.indexOf(\'<style\') > 0) {
                                        data=data.substring(0, data.indexOf(\'<style\'));
                                }
                                jQuery.unblockUI();
                                myConsole.log(\'ajax.success\', data);
                                if (data == \'error\') {
                                        $(\'<div></div>\')
                                                .attr(\'title\', '.json_encode(ML_EBAY_ERROR_CREATE_TOKEN_LINK_HEADLINE).')
                                                .html('.json_encode(ML_EBAY_ERROR_CREATE_TOKEN_LINK_TEXT).')
                                                .jDialog();
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

	public static function languageMatching($args, &$value = '') {
		global $_MagnaSession;

		$aEtsyLanguages = array (
			'de' => 'Deutsch',
			'en' => 'English',
			'es' => 'Espa&ntilde;ol',
			'fr' => 'Fran&ccedil;ais',
			'it' => 'Italiano',
			'ja' => '&#x65E5;&#x672C;&#x8A9E;',
			'nl' => 'Nederlands',
			'pl' => 'Polski',
			'pt' => 'Português',
			'ru' => '&#1056;&#1091;&#1089;&#1089;&#1082;&#1080;&#1081;',
		);
		$html = '<select id="config_etsy_shop_language" name="conf[etsy.shop.language]">'."\n";
		foreach ($aEtsyLanguages as $iso => $lang) {
			if (getDBConfigValue('etsy.shop.language', $_MagnaSession['mpID'], '') === $iso) {
				$selected = 'selected = "selected" ';
			} else {
				$selected = '';
			}
			$html .= '    <option '.$selected.'value="'.$iso.'">'.$lang.' </option>'."\n";
		}
		$html .= "</select>\n";
		return $html;
	}

    protected function loadChoiseValues() {
        parent::loadChoiseValues();
        mlGetOrderStatus($this->form['orderSyncState']['fields']['shippedstatus']);
        mlGetOrderStatus($this->form['orderSyncState']['fields']['cancelstatus']);
        if ($this->isAuthed) {
            $this->form['prepare']['fields']['shippingprofile']['values'] = EtsyHelper::showShippingProfiles();
        } else {
            $this->form['prepare']['fields']['shippingprofile']['values'] = array('');
        }
        # Etsy changes the "whenmade" entry "2020-...." each year
        $tmpWhenmadeValues = array_slice($this->form['prepare']['fields']['whenmade']['values'], 0, 1);
        $tmpWhenmadeValues['2020_'.date('Y')] = '2020-'.date('Y');
        array_shift($this->form['prepare']['fields']['whenmade']['values']);
        array_shift($this->form['prepare']['fields']['whenmade']['values']);
        $this->form['prepare']['fields']['whenmade']['values'] = array_merge($tmpWhenmadeValues, $this->form['prepare']['fields']['whenmade']['values']);
    }

    public static function zeroStockSyncConfirmationPopup() {
        ob_start();
?><script type="text/javascript">/*<!CDATA[*/
    $(document).ready(function() {
        $('select[id="config_etsy_stocksync_tomarketplace"]').data('ml-oldvalue', $('select[id="config_etsy_stocksync_tomarketplace"]').val());
    });
    $('select[id="config_etsy_stocksync_tomarketplace"]').change(function() {
      var sel=$(this);
      if (sel.val() != 'auto_zero_stock') {
        sel.data('ml-oldvalue', sel.val());
        return true;
      }
      sel.val(sel.data('ml-oldvalue'));
		$('<div></div>').html('<?php echo ML_TEXT_ETSY_WARNING_ZERO_STOCK_COST ?>').jDialog({
			title: '<?php echo ML_TITLE_ETSY_WARNING_ZERO_STOCK_COST ?>',
			buttons: {
				'<?php echo ML_BUTTON_LABEL_ABORT; ?>': function() {
					jQuery(this).dialog('close');
				},
				'<?php echo ML_BUTTON_LABEL_ACCEPT; ?>': function() {
					sel.data('ml-oldvalue', 'auto_zero_stock');
					sel.val('auto_zero_stock');
					jQuery(this).dialog('close');
				}
			}
		})
    });
/*]]>*/</script>
<?php
        $sPopup = ob_get_clean();
        return $sPopup;
    }

    public function process() {
        parent::process();
        echo $this->zeroStockSyncConfirmationPopup();
    }

    protected function finalizeForm() {
        parent::finalizeForm();

        if (    (isset($_POST['conf'][$this->marketplace.'.ShippingProfile']) && empty($_POST['conf'][$this->marketplace.'.ShippingProfile']))
            || empty($this->form['prepare']['fields']['shippingprofile']['values'])
        ) {
            $aResponse = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetShippingProfiles'
            ));

            if (!empty($aResponse['ERRORS'])) {
                foreach ($aResponse['ERRORS'] as $sError) {
                    $this->boxes .= '<p class="errorBox">'.$sError.'</p>';
                }
            }
        }
    }

}
if (isset($_GET['what'])) {
    if ($_GET['what'] == 'GetTokenCreationLink') {
        $iframeURL = 'error';
        try {
            //*
            $result = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'GetTokenCreationLink'
            ));
            $iframeURL = $result['DATA']['tokenCreationLink'];
            //*/
        } catch (MagnaException $e) {
            echo print_m($e, '$e');
        }
        echo $iframeURL;
        #require(DIR_WS_INCLUDES . 'application_bottom.php');
        exit();
    }
}

