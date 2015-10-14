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
// compatibility to older versions
$shopgateMobileHeader = '';
$shopgateJsHeader     = '';

if (MODULE_PAYMENT_SHOPGATE_STATUS == 'True') {

    include_once DIR_FS_CATALOG
        . 'includes/external/shopgate/shopgate_library/shopgate.php';
    include_once DIR_FS_CATALOG
        . 'includes/external/shopgate/base/shopgate_config.php';


    try {
        $shopgateCurrentLanguage = isset($_SESSION['language_code'])
            ? strtolower($_SESSION['language_code']) : 'de';
        $shopgateHeaderConfig    = new ShopgateConfigModified();
        $shopgateHeaderConfig->loadByLanguage($shopgateCurrentLanguage);

        if ($shopgateHeaderConfig->checkUseGlobalFor(
            $shopgateCurrentLanguage
        )
        ) {
            $shopgateRedirectThisLanguage = in_array(
                $shopgateCurrentLanguage,
                $shopgateHeaderConfig->getRedirectLanguages()
            );
        } else {
            $shopgateRedirectThisLanguage = true;
        }

        if ($shopgateRedirectThisLanguage) {
            // SEO modules fix (for Commerce:SEO and others): if session variable was set,
            // SEO did a redirect and most likely cut off our GET parameter
            // => reconstruct here, then unset the session variable
            if (!empty($_SESSION['shopgate_redirect'])) {
                $_GET['shopgate_redirect'] = 1;
                unset($_SESSION['shopgate_redirect']);
            }

            // instantiate and set up redirect class
            $shopgateBuilder    = new ShopgateBuilder($shopgateHeaderConfig);
            $shopgateRedirector = $shopgateBuilder->buildRedirect();

            if (($product instanceof product) && $product->isProduct
                && !empty($product->pID)
            ) {
                $shopgateJsHeader = $shopgateRedirector->buildScriptItem(
                    $product->pID
                );
            } elseif (!empty($current_category_id)) {
                $shopgateJsHeader = $shopgateRedirector->buildScriptCategory(
                    $current_category_id
                );
            } elseif (shopgateIsHomepage()) {
                if (isset($_GET['manufacturers_id']) && $brand = shopgateGetManufactureNameById($_GET['manufacturers_id'])) {
                    $shopgateJsHeader = $shopgateRedirector->buildScriptBrand($brand);
                } else {
                    $shopgateJsHeader = $shopgateRedirector->buildScriptShop();
                }
            } elseif (!empty($search_keywords) && is_array($search_keywords)) {

                $invalidSearchPattern = array(
                    'and',
                    'or',
                    '(',
                    ')'
                );
                foreach ($search_keywords as $key => $keyword) {
                    if (in_array($keyword, $invalidSearchPattern)) {
                        unset($search_keywords[$key]);
                    }
                }
                $shopgateJsHeader = $shopgateRedirector->buildScriptSearch(implode(' ', $search_keywords));

            } else {
                $shopgateJsHeader = $shopgateRedirector->buildScriptDefault();
            }
        }
    } catch (ShopgateLibraryException $e) {
    }
}

function shopgateIsHomepage()
{
    $scriptName = explode('/', $_SERVER['SCRIPT_NAME']);
    $scriptName = end($scriptName);

    if ($scriptName != 'index.php') {
        return false;
    }

    return true;
}

/**
 * @param int $id
 *
 * @return string
 */
function shopgateGetManufactureNameById($id)
{
    $manufacturers_query = xtDBquery(
        "select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$id . "'"
    );
    $manufacturers       = xtc_db_fetch_array($manufacturers_query, true);
    if (is_array($manufacturers) && count($manufacturers) == 1) {
        return $manufacturers['manufacturers_name'];
    }

    return false;
}
