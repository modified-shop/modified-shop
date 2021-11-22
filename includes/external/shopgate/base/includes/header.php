<?php
/**
 *
 * Copyright Shopgate Inc.
 *
 * Licensed under the GNU General Public License, Version 2 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU General Public License, Version 2
 *
 */
// compatibility to older versions
$shopgateMobileHeader = '';
$shopgateJsHeader     = '';

if (MODULE_PAYMENT_SHOPGATE_STATUS == 'True') {
    include_once DIR_FS_CATALOG
        . 'includes/external/shopgate/vendor/autoload.php';
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
                if (isset($_GET['manufacturers_id'])
                    && $brand = shopgateGetManufactureNameById(
                        $_GET['manufacturers_id']
                    )
                ) {
                    $shopgateJsHeader = $shopgateRedirector->buildScriptBrand(
                        $brand
                    );
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
                $shopgateJsHeader = $shopgateRedirector->buildScriptSearch(
                    implode(' ', $search_keywords)
                );
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
        "select manufacturers_name from " . TABLE_MANUFACTURERS
        . " where manufacturers_id = '" . (int)$id . "'"
    );
    $manufacturers       = xtc_db_fetch_array($manufacturers_query, true);
    if (is_array($manufacturers) && count($manufacturers) == 1) {
        return $manufacturers['manufacturers_name'];
    }

    return false;
}
