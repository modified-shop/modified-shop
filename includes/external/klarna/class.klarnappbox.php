<?php
/**
 * PartPayment widget
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
require_once 'class.KlarnaCore.php';

/**
 * Class handling the proper showing of the partpayment box.
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class Klarna_PPbox
{
    private $_enabled;
    private $_klarna;
    private $_checkout;
    private $_terms_link;
    private $_lang_wo_tax;
    private $_currency;
    private $_country;
    private $_language;

    /**
     * The constructor
     *
     * @param int|string $country country
     *
     * @return void
     */
    public function __construct($country = null)
    {
        global $currency;

        $this->_country = KlarnaUtils::deduceCountry('part');
        $this->_language = KlarnaUtils::getLanguageCode();
        $this->_currency = $currency;

		if($country === null && $this->_country == null){
			$this->_country = strtoupper($_SESSION['language_code']);
		}

        if ($this->_country === null) {
            Klarna::printDebug(
                __METHOD__,
                array(
                    'currency' => $this->_currency,
                    'language' => $this->_language,
                )
            );
            $this->_enabled = false;
            return;
        }

        $this->_utils = new KlarnaUtils($this->_country);

        $this->_enabled = KlarnaConstant::isEnabled('part', $this->_country);

        if ($this->_enabled === true
            && !KlarnaConstant::isActivated('part', $this->_country)
        ) {
            $this->_enabled = false;
        }
        $this->_locale = KiTT::locale(
            $this->_country, $this->_language, $this->_currency
        );

    }

    /**
     * Show the ppBox for given price.
     *
     * @param float $price price
     * @param int   $page  KlarnaFlags page flag
     *
     * @return void
     */
    public function showPPBox($price, $page = KlarnaFlags::PRODUCT_PAGE)
    {
        if (!$this->_enabled) {
            return;
        }

        if (!KiTT_CountryLogic::isBelowLimit($this->_country, $price)) {
            return;
        }

        $templateLoader = KiTT::templateLoader($this->_locale);

        $cssLoader = $templateLoader->load('css.mustache');
        $jsLoader = $templateLoader->load('javascript.mustache');

        $merchantID = KlarnaConstant::merchantID(
            KiTT::PART,
            $this->_country
        );

        $styles = array(
            DIR_WS_BASE."includes/external/klarna/template/css/oscstyle.css",
            DIR_WS_BASE."includes/modules/payment/klarna/productprice/style.css",
            //EXTERNAL_KITT . "pp/v1.0/pp.css?eid=" . $merchantID
            DIR_WS_BASE."includes/external/klarna/KITT/css/pp.css",
        );
        if (KlarnaConstant::isLegacyShop()) {
            $styles[] = "includes/external/klarna/template/css/klarna22rc2a.css";
        }

        $html = $cssLoader->render(array('styles' => $styles));

        $html .= $jsLoader->render(
            array(
                "scripts" => array(
                    EXTERNAL_KITT . "core/v1.0/js/klarna.min.js",
                    EXTERNAL_KITT . "res/v1.0/js/klarna.lib.min.js",
                    EXTERNAL_KITT . "pp/v1.0/js/productprice.min.js"
                )
            )
        );

        $html .= "<div style='clear: both;'></div>";
        $html .= KiTT::partPaymentBox($this->_locale, $price, $page)->show();
        return $html;
    }
}
