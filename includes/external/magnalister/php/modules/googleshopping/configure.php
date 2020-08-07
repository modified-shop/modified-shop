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

class GoogleshoppingConfigure extends MagnaCompatibleConfigure {
    /**
     * Checks if customer is authenticated on googleshopping and pops up the oauth window if not
     */
    protected function processAuth() {
        $auth = getDBConfigValue($this->marketplace.'.authed', $this->mpID, false);

        $this->isAuthed = $auth && $auth['state'] && $auth['expire'] > time();

        if ((!$this->isAuthed && $this->isPostRequest()) || $this->shouldForceTokenRegeneration()) {
            try {
                $r = MagnaConnector::gi()->submitRequest(array(
                    'ACTION' => 'GetTokenCreationLink',
                ));

                $link = $r['DATA']['tokenCreationLink'];
                echo "<script>window.open('".$link."');</script>";
                return;
            } catch (MagnaException $e) {
                $e->setCriticalStatus(false);
                setDBConfigValue($this->marketplace.'.autherror', $this->mpID, $e->getErrorArray(), false);
                $this->boxes .= $this->renderAuthError();
                return;
            }
        }
		try {
            $r = MagnaConnector::gi()->submitRequest(array(
                'ACTION' => 'IsAuthed',
            ));
		} catch (MagnaException $e) {
			$e->setCriticalStatus(false);
			setDBConfigValue($this->marketplace.'.autherror', $this->mpID, $e->getErrorArray(), false);
			$this->boxes .= $this->renderAuthError();
			return;
		}
    }

    public static function renderShippingTemplate() {
        echo '<script>
            jQuery(document).ready(function() {
                jQuery("#shippingtemplate").click(function(e){
                    
					jQuery.unblockUI();
                    e.preventDefault();
                });
              });
          </script>';

        $html = '<button id="shippingtemplate" type="button" class="ml-button mlbtn-action">+</button>';

        return $html;
    }

    public static function renderShippingButton($caption) {
        echo '<script>
            jQuery(document).ready(function() {
                
                function getShippingTemplateElements() {
                   var $startNode = jQuery("#shippingtemplate").parent().parent().next();
                   var nodes = [$startNode];
                   var $current = $startNode;
                   
                   for (var i=0; i < 5; i++) {
                       $current = $current.next();
                       
                       nodes.push($current);
                   }
                                      
                   return jQuery(nodes);
                }
                                  
                var $elems = getShippingTemplateElements();
                var elemsHidden = true;
                
                $elems.each(function() {
                    jQuery(this).hide();
                });
                                
                jQuery("#shippingtemplate").on("click", function() {
                    
                    if (elemsHidden) {
                        $elems.each(function() {
                            jQuery(this).show("slow");
                        }); 
                        $(this).text("-");
                        
                    } else {
                        $elems.each(function() {
                            jQuery(this).hide("slow");
                        });
                        $(this).text("+");
                    }
                    
                    elemsHidden = !elemsHidden;
                   
                });
                
                
                jQuery("#submitshippingtemplate").click(function(e){
                    e.preventDefault();
                    
                    jQuery.blockUI(blockUILoading);
                    
                    jQuery.ajax({
                        url: window.location.href
                                .replace("mode=conf", "mode=shipping")
                                .concat("&kind=ajax"),
                        data: {
                            title: jQuery("#config_googleshopping_shipping_title").val(),
                            originCountry: jQuery("#config_googleshopping_shipping_originCountry").val(),
                            currencyValue: jQuery("#config_googleshopping_shipping_currencyValue").val(),
                            primaryCost: jQuery("#config_googleshopping_shipping_primaryCost").val(),
                            secondaryCost: "1-3"//toDo: make this dynamic
                        },
                        type: "POST",
                    }).done(function(data) {
                      jQuery.unblockUI();
                      window.location.reload();
                    }).error(function() {
                      jQuery.unblockUI();
                    });
                });
              });
          </script>';

        $html = '<input name="submitshippingtemplate" value="'.$caption['button'].'" id="submitshippingtemplate" type="button" class="ml-button mlbtn-action" />';

        return $html;
    }

    public static function renderShippingTemplatesFromApi() {
        $shippingTemplates = GoogleshoppingApiConfigValues::gi()->getShippingTemplates();
        if (empty($shippingTemplates['services'])) {
            return '';
        }

        $accountId = $shippingTemplates['accountId'];

        $parsedTemplates = array_map(function ($item) use ($accountId) {
            $value = sprintf('%s:%s:%s:%s', $accountId, $item['name'], $item['currency'], $item['deliveryCountry']);
            return '<option value="'.$value.'">'.
                sprintf('%s (%s) (%s)', $item['name'], $item['currency'], $item['deliveryCountry']).'</option>';
        }, $shippingTemplates['services']);

        return '<select name="shippingtemplate" id="shippingtempate">'.
            implode('', $parsedTemplates).
            '</select>';
    }

    protected function finalizeForm() {
        parent::finalizeForm();
        if (!$this->isAuthed) {
            $this->form = array(
                'login' => $this->form['login']
            );
            return;
        }

        if (empty($_POST['conf']['googleshopping.merchantid']) || !is_numeric($_POST['conf']['googleshopping.merchantid'])) {
            unset($_POST['conf']['googleshopping.merchantid']);
        }
    }

    protected function loadChoiseValues() {
        parent::loadChoiseValues();
        mlGetLanguages($this->form['prepare']['fields']['lang']['morefields']['webshop']);
        $this->mlGetSupportedLanguagesForTargetCountry($this->form['prepare']['fields']['lang']['morefields']['googleshopping']);
        $this->mlGetTargetCountries($this->form['login']['fields'][3]);
        $this->mlGetShopCurrencies($this->form['login']['fields'][4]);
    }
    
    private function mlGetTargetCountries(&$form){
        global $magnaConfig;
        $countries =  $magnaConfig['googleshopping']['targetCountry'];
        $form['values'] = array();
        foreach ($countries as $key => $country) {
            $form['values'][$key] = $country;
        }
    }

    private function isPostRequest() {
        return !empty($_POST);
    }

    private function shouldForceTokenRegeneration() {
        return $this->isPostRequest() && isset($_POST['forceTokenRegeneration']);
    }

    public static function renderForceRegenerateTokenButton($params) {
        return '<button type="submit" name="forceTokenRegeneration" class="ml-button mlbtn-action">'.$params[0]['button'].'</button>';
    }

    /**
     *
     * @param $form
     */
    public function mlGetShopCurrencies(&$form) {
        $currencies = MagnaDB::gi()->fetchArray('SELECT * FROM currencies');
        $form['values'] = array();
        foreach ($currencies as $value) {
            $form['values'][$value['code']] = $value['title'];
        }
    }

    /**
     * On GoogleShopping configuration tab load
     *
     * @param $form
     */
    private function mlGetSupportedLanguagesForTargetCountry(&$form) {
        global $magnaConfig;
        $languages = $magnaConfig['googleshopping']['languages'];
        if ($selectedTargetCountry = getDBConfigValue($this->marketplace.'.targetCountry', $this->mpID, false)) {
            $targetCountryLanguages = $languages[$selectedTargetCountry];
            foreach ($targetCountryLanguages as $value) {
                $form['values'][$value['code']] = $value['title'] . " ({$value['code']}) ";
            }
        }
        if ($selectedTargetCountry !== 'UA') {
            $form['values'][$languages['GB'][0]['code']] = $languages['GB'][0]['title']." ({$languages['GB'][0]['code']}) ";
        }
    }
}