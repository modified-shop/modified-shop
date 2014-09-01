<?php
require_once ('class.KlarnaAPI.php');
require_once ('class.KlarnaLanguagePack.php');

class KlarnaProductPrice {
    private $api;
    private $path;
    private $webroot;
    private $eid;
    private $checkout;
    private $dpoint;
    private $formatter;

    public function __construct ($api, $eid, $path, $webroot,
                                                            $checkout = null) {
        if (! $api instanceof Klarna) {
            throw new KlarnaApiException("api must be an instance of Klarna");
        }
        $this->api = $api;
        $this->path = $path;
        $this->eid = $eid;
        $this->webroot = $webroot;
        if ($checkout !== null) {
            $this->checkout = $checkout;
        }
        $this->dpoint = null;
        $this->formatter = null;
    }

    public function __setDecimalPoint($dpoint) {
        $this->dpoint = $dpoint;
    }

    /**
     * Set the formatter to an implementation of
     * interface.KlarnaFormatter.php
     */
    public function __setFormatter($formatter) {
        $this->formatter = $formatter;
    }

    private function format($price) {
        if ($this->formatter != null) {
            $formatted = $this->formatter->formatPrice($price);
        } else if ($this->dpoint != null) {
            $formatted = str_replace('.', $this->dpoint, $price);
        } else {
            $formatted = $price;
        }
        return $formatted;
    }

    public function __setCheckout($country, $currency,
                                    $lang, $price, $page, $types) {
        $this->checkout = new KlarnaAPI ($country, $lang, 'part', $price, $page,
                                            $this->api, $types, $this->path);
        $this->checkout->addSetupValue ('web_root', $this->webroot);
        $this->checkout->addSetupValue ('path_img', $this->webroot);
        $this->checkout->addSetupValue ('path_js', $this->webroot);
        $this->checkout->addSetupValue ('path_css', $this->webroot);
        if ($country  == KlarnaCountry::DE) {
            $this->checkout->addSetupValue ('asterisk', '*');
        }
        $this->checkout -> setCurrency($currency);
    }

    public function show($price, $currency, $country, $lang = null,
                                $page = null, $setupValues = null) {
        if (!is_numeric ($country)) {
            $country = KlarnaCountry::fromCode ($country);
        } else {
            $country = intval ($country);
        }
        if ($price > 250 && $country == KlarnaCountry::NL) {
            return;
        }

        // we will always use the language for the country to get the correct
        // terms and conditions aswell as the correct name for 'Klarna Konto'
        $lang = KlarnaLanguage::getCode ($this -> api ->
            getLanguageForCountry ($country));

        if( $page === null || ($page != KlarnaFlags::PRODUCT_PAGE &&
                                $page != KlarnaFlags::CHECKOUT_PAGE)) {
            $page = KlarnaFlags::PRODUCT_PAGE;
        }

        if ( !$this->api->checkCountryCurrency($country, $currency)) {
            return false;
        }

        $types = array(KlarnaPClass::CAMPAIGN,
                    KlarnaPClass::ACCOUNT,
                    KlarnaPClass::FIXED);
        if ($this->checkout === null) {
            $this->__setCheckout($country, $currency, $lang, $price, $page, $types);
        }

        $kLang = new KlarnaLanguagePack($this->path . '/data/language.xml');

        if (is_array($setupValues)) {
            foreach($setupValues as $name => $value) {
                $this->checkout->addSetupValue($name, $value);
            }
        }

        if ($price > 0 && count ($this->checkout->aPClasses) > 0) {
            $monthlyCost = array();
            $minRequired = array();

            $sMonthDefault = null;

            $sTableHtml = "";
            foreach ($this->checkout->aPClasses as $pclass) {
                if ($sMonthDefault === null ||
                        $pclass['monthlyCost'] < $sMonthDefault) {
                        $sMonthDefault = $this->format($pclass['monthlyCost']);
                }

                if ($pclass['pclass']->getType() == KlarnaPClass::ACCOUNT) {
                    $pp_title = $kLang->fetch('PPBOX_account', $lang);
                } else {
                    $pp_title = $pclass['pclass']->getMonths() . " " .
                                    $kLang->fetch('PPBOX_th_month', $lang);
                }
                $pp_price = $this->format($pclass['monthlyCost']);
                $sTableHtml .= $this->checkout->retrieveHTML(null, array(
                                  'pp_title' => html_entity_decode ($pp_title),
                                  'pp_price' => $pp_price
                                ), $this->path . 'html/pp_box_template.html');
            }

            $aValues = array();
            $aValues['defaultMonth'] = $sMonthDefault;
            $aValues['monthTable']   = $sTableHtml;
            $aValues['eid']          = $this->eid;
            $aValues['country']      = KlarnaCountry::getCode ($country);
            if ($country == KlarnaCountry::NL) {
                $aValues['nlBanner'] = "<div class='nlBanner'>" .
                                       "<img src='{$this->webroot}checkout/" .
                                       "notice_nl.jpg' /></div>";
            } else {
                $aValues['nlBanner'] = "";
            }

            return $this->checkout->retrieveHTML($aValues, null, $this->path .
                                        'html/productPrice/layout.html');
        }
    }
}

