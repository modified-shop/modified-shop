<?php
/**
 * This is the completion script for the Klarna Mobile class (class.KlarnaMobile.php)
 * This page should include the following functions
 *
 * fetchProduct ($iProductId, $oKlarna);
 * fetchRedirectUrl ($iProductId);
 * processOrder ($mProductId, $iKlarnaReference, $iTelNo)
 *
 * Please see each function for the appropriate description of the function.
 *
 * @package       Klarna Standard Mobile Kassa API
 * @version     1.0
 * @since         1.0 - 4 maj 2011
 * @link        http://integration.klarna.com/
 * @copyright    Copyright (c) 2011 Klarna AB (http://klarna.com)
 */
class KlarnaMobile extends KlarnaMobileImpl
{
    /**
     * Fetches the product and adds it as an article to the klarna class. No need to return any data.
     * Articles need to be set for fraud purpose, incorrect article means no_risk invoice. Hereby klarna will not take any risks.
     *
     * @param mixed $mProductId The product identified. Either int or string. Adapted according shop functionality
     * @param Klarna $oKlarna The Klarna class object. Used to set any articles
     * @return void
     */
    protected function fetchProduct($mProductId) {
        global $currencies, $currency;

        require_once(DIR_WS_CLASSES . 'language.php');
        $lng = new language();

        if (isset($HTTP_GET_VARS['language']) && tep_not_null($HTTP_GET_VARS['language'])) {
            $lng->set_language($HTTP_GET_VARS['language']);
        } else {
            $lng->get_browser_language();
        }

        $language = $lng->language['directory'];
        $languages_id = $lng->language['id'];

        $product_info_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$mProductId . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
        $aProduct_info = tep_db_fetch_array($product_info_query);

        $sArtNo    = (MODULE_PAYMENT_KLARNA_ARTNO == 'id' || MODULE_PAYMENT_KLARNA_ARTNO == '' ? $aProduct_info['id'] : $aProduct_info['name']);

        $iTax    = tep_get_tax_rate($aProduct_info['products_tax_class_id']);

        if(DISPLAY_PRICE_WITH_TAX == 'true') {
            $iPrice_with_tax = $currencies->get_value($currency) * $aProduct_info['products_price'];
        } else {
            $iPrice_with_tax = $currencies->get_value($currency) * $aProduct_info['products_price'] * (($iTax/100)+1);
        }

        // Add goods
        $this->oKlarna->addArticle(
            1, //Quantity
            $sArtNo, //Article number
            $aProduct_info['products_name'], //Article name/title
            $iPrice_with_tax, // Price
            $iTax, //25% VAT
            0, // Discount
            KlarnaFlags::INC_VAT // Flag incl. excl vat
        );

        $this->iSum    += $iPrice_with_tax;
    }

    /**
     * When a purchase is made, the script returns an redirect-URL or a message. When the URL is returned, the user is re-directed to this page.
     * This could be an URL to a downloadable file (WARNING! SHOULD ALWAYS BE A DYNAMIC URL!), or a link to a "Thank you"/confirmation page (Comon for donation purposes)
     *
     * @param mixed $mProductId The product identified. Either int or string. Adapted according shop functionality
     * @return mixed Either NULL or FALSE if no URL is available, or STRING with full URL when URL is available.
     */
    protected function fetchRedirectUrl ($mProductId) {
        return "http://www.klarna.com";
    }

    /**
     * When a purchase is made (approved as well) it might needs to be added to the merchants order system. In this function you can define how the order of a product should be handled.
     *
     * @param mixed $mProductId The product identified. Either int or string. Adapted according shop functionality
     * @param integer $iKlarnaReference The reference returned by Klarna
     * @param string $sTelNo The telephone number used to make a purchase
     * @return void
     */
    protected function processOrder ($mProductId, $iKlarnaReference, $iTelNo) {
        // Here you should implement the functionality of how the product is managed.
    }
}
