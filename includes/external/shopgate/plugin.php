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

define("SHOPGATE_PLUGIN_VERSION", "2.9.39");

/**
 * Modified eCommerce Plugin for Shopgate
 */
class ShopgateModifiedPlugin extends ShopgatePlugin
{

    /**
     * @var ShopgateConfigModified
     */
    protected $config;

    /**
     * @var int
     */
    private $languageId;
    /**
     * @var int
     */
    private $countryId;
    /**
     * @var int
     */
    private $zoneId;

    /**
     * @var array
     */
    private $currency;

    /**
     * @var string
     */
    private $language = "german";

    /**
     * @var int
     */
    private $currencyId;

    protected $modifiedVersion;

    public function startup()
    {
        $this->requireFiles();

        $this->config = new ShopgateConfigModified();// initialize configuration
        if (!isset($_REQUEST['shop_number'])) {
            $this->config->loadFile();
        } else {
            $this->config->loadByShopNumber($_REQUEST['shop_number']);
        }

        $initHelper = new ShopgatePluginInitHelper();
        $initHelper->defineXtcValidationConstant();
        $initHelper->getDefaultLanguageData($this->config->getLanguage(), $this->languageId, $this->language);
        $initHelper->getDefaultCurrencyData(
            $this->config->getCurrency(), $this->exchangeRate, $this->currencyId, $this->currency
        );

        $this->countryId       = $initHelper->getDefaultCountryId($this->config->getCountry());// fetch country
        $this->modifiedVersion = $initHelper->getModifiedVersion();
        $this->zoneId          = $this->config->getTaxZoneId();

        return true;
    }

    public function registerCustomer($user, $pass, ShopgateCustomer $customer)
    {
        require_once(DIR_FS_INC . 'xtc_encrypt_password.inc.php');
        /** @var ShopgateCustomer $customer */
        $customer        = $customer->utf8Decode($this->config->getEncoding());
        $user            = $this->stringFromUtf8($user, $this->config->getEncoding());
        $userExistResult =
            xtc_db_query("SELECT count(1) AS exist FROM customers AS c WHERE c.customers_email_address = \"{$user}\";");
        $userCount       = xtc_db_fetch_array($userExistResult);
        $userCount       = $userCount['exist'];
        $encPass         = xtc_encrypt_password($pass);
        $date            = date("Y-m-d H:i:s");
        $customField     = new ShopgateCustomFieldModel();
        $couponModel     = new ShopgateCouponModel(
            $this->config, $this->languageId, $this->language, $this->currency, $this->countryId
        );

        if ((int)$userCount >= 1) {
            throw new ShopgateLibraryException(ShopgateLibraryException::REGISTER_USER_ALREADY_EXISTS, '', true);
        }

        if (!defined(TABLE_CUSTOMERS_MEMO)) {
            define(TABLE_CUSTOMERS_MEMO, "customers_memo");
        }

        if (!defined(TABLE_CUSTOMERS_INFO)) {
            define(TABLE_CUSTOMERS_INFO, "customers_info");
        }

        $customerData = array(
            'customers_firstname'     => $customer->getFirstName(),
            'customers_lastname'      => $customer->getLastName(),
            'customers_email_address' => $customer->getMail(),
            'customers_telephone'     => $customer->getPhone(),
            'customers_newsletter'    => 0,
            "customers_gender"        => $customer->getGender(),
            'customers_password'      => $encPass,
            'customers_date_added'    => $date,
            'customers_last_modified' => $date,
            'delete_user'             => 0,
            'customers_status'        => DEFAULT_CUSTOMERS_STATUS_ID,
        );
        $customerData =
            array_merge($customerData, $customField->prepareCustomFields(clone $customer, TABLE_CUSTOMERS));

        xtc_db_perform(TABLE_CUSTOMERS, $customerData);
        $userId = xtc_db_insert_id();

        if (ShopgateWrapper::db_column_exists(TABLE_CUSTOMERS, 'customers_cid')) {
            $query = "UPDATE " . TABLE_CUSTOMERS . " SET customers_cid = {$userId} WHERE customers_id = {$userId};";
            xtc_db_query($query);
        }

        $customersInfo = array(
            'customers_info_id'                         => $userId,
            'customers_info_number_of_logons'           => 1,
            'customers_info_date_account_created'       => $date,
            'customers_info_date_account_last_modified' => $date,
        );
        xtc_db_perform(TABLE_CUSTOMERS_INFO, $customersInfo);

        $memoData = array(
            'customers_id' => $userId,
            'memo_date'    => $date,
            'memo_title'   => 'Shopgate - Account angelegt',
            'memo_text'    => 'Account wurde von Shopgate angelegt',
        );
        xtc_db_perform(TABLE_CUSTOMERS_MEMO, $memoData);


        /** @var ShopgateAddress[] $addresses */
        $addressList    = $customer->getAddresses();
        $customerModel  = new ShopgateCustomerModel($this->config, $this->languageId);
        $defaultAddress = true;

        if ($customerModel->areAddressesEqual($addressList)) {
            array_pop($addressList);
        }

        foreach ($addressList as $address) {
            $stateCode    = ShopgateXtcMapper::getXtcStateCode($address->getState());
            $zoneQuery    =
                xtc_db_query("SELECT z.zone_id,z.zone_name FROM zones AS z WHERE z.zone_code = '" . $stateCode . "'");
            $zoneResult   = xtc_db_fetch_array($zoneQuery);
            $countryQuery = xtc_db_query(
                "SELECT c.countries_id FROM countries AS c WHERE c.countries_iso_code_2 ='" . $address->getCountry()
                . "'"
            );

            $countryResult = xtc_db_fetch_array($countryQuery);
            $addressData   = array(
                "customers_id"          => $userId,
                "entry_company"         => $address->getCompany(),
                "entry_zone_id"         => $zoneResult['zone_id'],
                "entry_country_id"      => $countryResult['countries_id'],
                "entry_firstname"       => $address->getFirstName(),
                "entry_lastname"        => $address->getLastName(),
                "entry_gender"          => $address->getGender(),
                "entry_street_address"  => $address->getStreet1(),
                "entry_postcode"        => $address->getZipcode(),
                "entry_city"            => $address->getCity(),
                "entry_state"           => $zoneResult['zone_name'],
                "address_date_added"    => "now()",
                "address_last_modified" => "now()",
            );

            $addressData =
                array_merge($addressData, $customField->prepareCustomFields(clone $address, TABLE_ADDRESS_BOOK));

            xtc_db_perform(TABLE_ADDRESS_BOOK, $addressData);
            if ($defaultAddress) {
                $addressId = xtc_db_insert_id();
                $query     = "UPDATE " . TABLE_CUSTOMERS
                    . " as c SET customers_default_address_id = {$addressId} WHERE c.customers_id={$userId}";
                xtc_db_query($query);
                $defaultAddress = false;
            }
        }
        $couponModel->insertWelcomeVoucher(
            $customer->getMail(), $customer->getFirstName() . " " . $customer->getLastName()
        );
    }

    public function createPluginInfo()
    {
        $initHelper      = new ShopgatePluginInitHelper();
        $modifiedVersion = $initHelper->getModifiedVersion();

        return array('modifed eCommerce Version' => $modifiedVersion);
    }

    protected function createCategoriesCsv()
    {
        $this->log("Start export categories tree...", ShopgateLogger::LOGTYPE_DEBUG);
        $categoryModel = new ShopgateCategoryModel();
        $categoryModel->setLanguageId($this->languageId);
        $maxOrder = $categoryModel->getCategoryMaxOrder($this->config->getReverseCategoriesSortOrder());

        $this->buildCategoriesTree(0, $maxOrder);

        $row = $this->buildDefaultCategoryRow();
        if ($this->config->getExportNewProductsCategory()) {
            $this->addCategoryRow(
                array_merge(
                    $row,
                    $categoryModel->getNewProductsCategoryData(
                        $this->config->getExportNewProductsCategoryId()
                    )

                )
            );
        }

        if ($this->config->getExportSpecialProductsCategory()) {
            $this->addCategoryRow(
                array_merge(
                    $row,
                    $categoryModel->getSpecialProductsCategoryData(
                        $this->config->getExportSpecialProductsCategoryId()
                    )
                )
            );
        }
    }

    protected function createItemsCsv()
    {
        $this->log("Start export items ...", ShopgateLogger::LOGTYPE_DEBUG);

        $itemModel = new ShopgateItemModel($this->config);
        $itemModel->setLog(ShopgateLogger::getInstance());
        $itemModel->setLanguageId($this->languageId);
        $itemModel->setDefaultCustomerPriceGroup(DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
        $itemModel->setStringHelper($this->getHelper(ShopgateObject::HELPER_STRING));
        $itemModel->setCurrencyData($this->currency);
        $itemModel->setCountryId($this->countryId);
        $itemModel->setZoneId($this->zoneId);
        $itemModel->setExchangeRate($this->exchangeRate);
        $itemModel->setModifiedVersion($this->modifiedVersion);

        if ($this->splittedExport) {
            $itemModel->setExportLimit($this->exportLimit);
            $itemModel->setExportOffset($this->exportOffset);
        }

        $this->log("execute SQL customer group ...", ShopgateLogger::LOGTYPE_DEBUG);// get customer-group first
        $customerGroupMaxPriceDiscount   = 0;
        $customerGroupDiscountAttributes = false;
        $itemModel->getDiscountToCustomerGroups($customerGroupMaxPriceDiscount, $customerGroupDiscountAttributes);

        $categoryReducedMap = array();
        $maxCatDepth        = $this->config->getMaximumCategoryExportDepth();
        if (!empty($maxCatDepth)) {
            $categoryReducedMap = $itemModel->getCategoryReducementMap($maxCatDepth);
        }

        $maxOrder = $minOrder = $addToOrderIndex = 0;
        $itemModel->getProductOrderValues($maxOrder, $minOrder, $addToOrderIndex);

        if ($this->config->getExportNewProductsCategory()) {
            $newProducts = $this->getNewProducts($itemModel);
        }

        if ($this->config->getExportSpecialProductsCategory()) {
            $specialProducts = $this->getSpecialProducts($itemModel);
        }
        $uids = !empty($_REQUEST['item_numbers']) && is_array($_REQUEST['item_numbers'])
            ? $_REQUEST['item_numbers']
            : '';
        $this->log("execute SQL get products ...", ShopgateLogger::LOGTYPE_DEBUG);
        $query = xtc_db_query($itemModel->getProductQuery($uids));

        while ($item = xtc_db_fetch_array($query)) {
            $this->log("start export products_id = " . $item["products_id"] . " ...", ShopgateLogger::LOGTYPE_DEBUG);
            $itemArr     = $this->buildDefaultItemRow();
            $orderInfos  = array();
            $tax_rate    = xtc_get_tax_rate($item["products_tax_class_id"], $this->countryId, $this->zoneId);
            $variations  =
                $this->getVariations($itemModel, $item["products_id"], $tax_rate);// Get variantions and input fields
            $inputFields = $this->getInputFields($itemModel, $item["products_id"]);
            $categories  = $this->getProductPath($item["products_id"]);// Get categories
            $images      = $this->getProductsImages($itemModel, $item);// Get Image Urls
            $deeplink    =
                $itemModel->generateDeepLinkToProduct($item['products_id'], $item['products_name']);// get deeplink

            $price    = $item["products_price"];// Calculate the price
            $oldPrice = '';
            $itemModel->calculateProductPrice(
                $item, $tax_rate, $customerGroupMaxPriceDiscount, $price, $oldPrice
            );

            $itemModel->setReverseItemSortOrder($this->config->getReverseItemsSortOrder());
            $category_numbers = $itemModel->getProductCategoryNumbers($item);
            // check if there is a category replacement map to reduce categories depth
            if (!empty($categoryReducedMap)) {
                foreach ($category_numbers as &$categoryNumber) {
                    // can possibly contain a split symbol "=>"
                    if (strpos($categoryNumber, '=>') !== false) {
                        $catNumberParts    = explode('=>', $categoryNumber);
                        $catNumberParts[0] = $categoryReducedMap[$catNumberParts[0]];
                        $categoryNumber    = implode('=>', $catNumberParts);
                    } else {
                        $categoryNumber = $categoryReducedMap[$categoryNumber];
                    }
                }
            }

            if ($this->config->getExportNewProductsCategory() && !empty($newProducts)) {
                foreach ($newProducts as $newProduct) {
                    if ($newProduct['products_id'] == $item['products_id']) {
                        $category_numbers[] = "{$this->config->getExportNewProductsCategoryId()}=>0";
                    }
                }
            }

            if ($this->config->getExportSpecialProductsCategory() && !empty($specialProducts)) {
                foreach ($specialProducts as $specialProduct) {
                    if ($specialProduct['products_id'] == $item['products_id']) {
                        $category_numbers[] = "{$this->config->getExportSpecialProductsCategoryId()}=>0";
                    }
                }
            }

            $price *= $this->exchangeRate;
            $price = $price * (1 + ($tax_rate / 100));

            if (!empty($oldPrice)) {
                $oldPrice = $oldPrice * $this->exchangeRate;
                $oldPrice = $this->formatPriceNumber($oldPrice * (1 + ($tax_rate / 100)));
            }

            $itemArr['item_number']                        = $item["products_id"];
            $itemArr['item_number_public']                 = $item['products_model'];
            $itemArr['manufacturer']                       = $item["manufacturers_name"];
            $itemArr['item_name']                          =
                trim(preg_replace('/<[^>]+>/', ' ', $item["products_name"]));
            $itemArr['description']                        =
                $itemModel->getDescriptionToProduct($item, $this->config->getExportDescriptionType());
            $itemArr['unit_amount']                        = $this->formatPriceNumber($price);
            $itemArr['currency']                           = $this->currency["code"];
            $itemArr['is_available']                       = $item["products_status"];
            $itemArr['available_text']                     = $itemModel->getAvailableText($item);
            $itemArr['url_deeplink']                       = $deeplink;
            $itemArr['urls_images']                        = $images;
            $itemArr['categories']                         = $categories;
            $itemArr['category_numbers']                   = implode("||", $category_numbers);
            $itemArr['use_stock']                          =
                (STOCK_ALLOW_CHECKOUT == 'true' || STOCK_CHECK != 'true') ? 0 : 1;
            $itemArr['active_status']                      =
                (STOCK_ALLOW_CHECKOUT == 'false' && STOCK_CHECK == 'true') ? "active" : "stock";
            $itemArr['stock_quantity']                     = $item['products_quantity'];
            $itemArr['weight']                             = $item["products_weight"] * 1000;
            $itemArr['tags']                               = trim($item["products_keywords"]);
            $itemArr['tax_percent']                        = $tax_rate;
            $itemArr['shipping_costs_per_order']           = 0;
            $itemArr['additional_shipping_costs_per_unit'] = 0;
            $itemArr['ean']                                = preg_replace("/\s+/i", '', $item["products_ean"]);
            $itemArr['last_update']                        = $item["products_last_modified"];
            $itemArr['block_pricing']                      = $this->getPackages($item, $tax_rate);
            $itemArr['age_rating']                         = $item["products_fsk18"] == 1 ? '18' : '';
            $itemArr['related_shop_item_numbers']          = $itemModel->getRelatedShopItems($item["products_id"]);
            $itemArr['basic_price']                        = $itemModel->getProductVPE($item, $price);
            $itemArr['is_highlight']                       = $item["products_startpage"];
            $itemArr['highlight_order_index']              = $item["products_startpage_sort"];

            if ($this->config->getReverseItemsSortOrder()) {
                $itemArr['sort_order'] =
                    $item["products_sort"] + $addToOrderIndex;// $addToOrderIndex to make positive sort_order
            } else {
                $itemArr['sort_order'] = ($maxOrder - $item["products_sort"]) + $addToOrderIndex;
            }

            if (!empty($orderInfos)) {
                $itemArr['internal_order_info'] = $this->jsonEncode($orderInfos);
            }

            if (!empty($oldPrice) && round($oldPrice, 2) > 0) {
                $itemArr['old_unit_amount'] = $oldPrice;
            } else {
                $itemArr['old_unit_amount'] = '';
            }

            if ($itemArr['available_text'] == 'Unbekannt') {
                $itemArr['is_available'] = 0;
            }

            if (!empty($inputFields)) {
                $itemArr['has_input_fields'] = "1";
                $itemArr                     = array_merge($itemArr, $inputFields);
            }

            if (!empty($variations)) {
                if ($variations["has_options"]) {
                    $itemArr['has_options'] = 1;

                    // fix for products with more than 10 options:
                    if (isset($variations['option_11'])) {
                        continue; // don't import
                    }

                    $this->addItemRow(array_merge($itemArr, $variations));
                } else {
                    if (isset($variations['has_options'])) {
                        unset($variations['has_options']);
                    }
                    $itemArr['has_children'] = 1;
                    $itemNumber              = $itemArr["item_number"];
                    $basePrice               = round($itemArr["unit_amount"], 2);
                    $baseOldPrice            = round($itemArr["old_unit_amount"], 2);
                    $baseWeight              = $itemArr["weight"];

                    $parentItemNumber = $itemArr["item_number"];
                    $isFirst          = true;

                    // Kinder haben gleiche Textfelder
                    if (!empty($inputFields)) {
                        $itemArr['has_input_fields'] = "1";
                        $itemArr                     = array_merge($itemArr, $inputFields);
                    }

                    foreach ($variations as $key => $variation) {
                        $price  = 0;
                        $weight = 0;
                        // Offset amount including tax without discounts (but with exchange rate, of set)
                        $originalOffsetAmount = 0;
                        if (!empty($variation["offset_amount"])) {
                            if (!empty($this->exchangeRate)) {
                                $variation["offset_amount"] *= $this->exchangeRate;
                                $variations[$key]["offset_amount"] = $variation["offset_amount"];
                            }
                            $originalOffsetAmount = $variation["offset_amount"] * (1 + ($tax_rate / 100));

                            // Variations also need to be discounted if products discount is set
                            if (!empty($productDiscount) && round($productDiscount, 2) > 0) {
                                if ($customerGroupDiscountAttributes) { // Seems to be buggy in gambio so it is ignored here
                                    $variation["offset_amount"]        =
                                        $itemModel->getDiscountPrice($variation["offset_amount"], $productDiscount);
                                    $variations[$key]["offset_amount"] = $variation["offset_amount"];
                                }
                            }
                            $price = $variation["offset_amount"] * (1 + ($tax_rate / 100));
                        }


                        if (isset($variation["offset_weight"])) {
                            $weight = $variation["offset_weight"] * 1000;
                        }

                        $hash = "";

                        for ($i = 1; $i < 10 && isset($variation["attribute_$i"]); $i++) {
                            $hash .= $variation["attribute_$i"];
                            $itemArr["attribute_$i"] =
                                htmlentities($variation["attribute_$i"], ENT_NOQUOTES, $this->config->getEncoding());
                        }

                        $hash = md5($hash);
                        $hash = substr($hash, 0, 5);
                        if (empty($variation)) {
                            $variation = array("order_info" => array());
                        }

                        // Set Order Info from parent product
                        if (!empty($variation['order_info']) && is_array($variation['order_info'])) {
                            $variation["order_info"] = array_merge($orderInfos, $variation['order_info']);
                        } else {
                            $variation["order_info"] = $orderInfos;
                        }

                        $variation["order_info"]["base_item_number"] = $itemNumber;

                        $itemArr['internal_order_info'] = $this->jsonEncode($variation["order_info"]);
                        $itemArr["item_number"]         = $itemNumber . ($isFirst ? "" : "_" . $hash);
                        if (!empty($variation["item_number"])) {
                            $itemArr["item_number_public"] = $variation["item_number"];
                        } else {
                            $itemArr['item_number_public'] = $item['products_model'];
                        }

                        $itemArr["unit_amount"] = $this->formatPriceNumber($basePrice + $price);
                        if (!empty($baseOldPrice) && round($baseOldPrice, 2) > 0) {
                            $itemArr["old_unit_amount"] =
                                $this->formatPriceNumber($baseOldPrice + $originalOffsetAmount);
                        } else {
                            $itemArr["old_unit_amount"] = '';
                        }
                        $itemArr["weight"] = $baseWeight + $weight;

                        if ($isFirst == false) {
                            $itemArr["use_stock"] =
                                (STOCK_ALLOW_CHECKOUT == 'true' || ATTRIBUTE_STOCK_CHECK != 'true') ? 0 : 1;
                        }

                        // Overwrite stock only if its set up in the configuration
                        if (ATTRIBUTE_STOCK_CHECK == 'true' && $isFirst == false) {
                            if (!empty($item["specials_new_products_price"]) && $item["specials_quantity"] > 0) {
                                $itemArr["stock_quantity"] = $variation["stock_quantity"] > $item["specials_quantity"]
                                    ? $item["specials_quantity"] : $variation["stock_quantity"];
                            } else {
                                $itemArr["stock_quantity"] = $variation["stock_quantity"];
                            }
                        }

                        $itemArr['properties'] = $itemModel->generatePropertiesToProduct($item);

                        $this->addItemRow($itemArr);

                        $isFirst                       = false;
                        $itemArr['has_children']       = 0;
                        $itemArr["parent_item_number"] = $parentItemNumber;
                    }
                }
            } else {
                $itemArr['has_children'] = 0;
                $itemArr['properties']   = $itemModel->generatePropertiesToProduct($item);
                $this->addItemRow($itemArr);
            }
        }
    }

    public function getCustomer($user, $pass)
    {
        // save the UTF-8 version for logging etc.
        $userUtf8 = $user;

        // decode the parameters if necessary to make them work with xtc_* functions
        $user = $this->stringFromUtf8($user, $this->config->getEncoding());
        $pass = $this->stringFromUtf8($pass, $this->config->getEncoding());

        // find customer
        $qry = "SELECT"

            // basic user information
            . " customer.customers_id,"
            . " customer.customers_cid,"
            . " status.customers_status_name,"
            . " status.customers_status_id,"
            . " customer.customers_gender,"
            . " customer.customers_firstname,"
            . " customer.customers_lastname,"
            . " date_format(customer.customers_dob,'%Y-%m-%d') as customers_birthday,"
            . " customer.customers_telephone,"
            . " customer.customers_email_address,"

            // additional information for password verification, default address etc.
            . " customer.customers_password,"
            . " customer.customers_default_address_id"

            . " FROM " . TABLE_CUSTOMERS . " AS customer"

            . " INNER JOIN " . TABLE_CUSTOMERS_STATUS . " AS status"
            . " ON customer.customers_status = status.customers_status_id"
            . " AND status.language_id = " . $this->languageId

            . " WHERE customers_email_address = '" . xtc_db_input($user) . "';";

        // user exists?
        $customerResult = xtc_db_query($qry);
        if (empty($customerResult)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_WRONG_USERNAME_OR_PASSWORD, 'User: ' . $userUtf8
            );
        }

        // password's correct?
        $customerData = xtc_db_fetch_array($customerResult);
        if (defined('PROJECT_MAJOR_VERSION')
            && !xtc_validate_password(
                $pass, $customerData['customers_password'], $customerData['customers_id']
            )
            || !defined('PROJECT_MAJOR_VERSION') && !xtc_validate_password($pass, $customerData['customers_password'])
        ) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_WRONG_USERNAME_OR_PASSWORD, 'User: ' . $userUtf8
            );
        }

        // fetch customers' addresses
        $qry = "SELECT"

            . " address.address_book_id,"
            . " address.entry_gender,"
            . " address.entry_firstname,"
            . " address.entry_lastname,"
            . " address.entry_company,"
            . " address.entry_street_address,"
            . " address.entry_postcode,"
            . " address.entry_city,"
            . " country.countries_iso_code_2,"
            . " zone.zone_code"


            . " FROM " . TABLE_ADDRESS_BOOK . " AS address"

            . " LEFT JOIN " . TABLE_COUNTRIES . " AS country"
            . " ON country.countries_id = address.entry_country_id"

            . " LEFT JOIN " . TABLE_ZONES . " AS zone"
            . " ON address.entry_zone_id = zone.zone_id"
            . " AND country.countries_id = zone.zone_country_id"

            . " WHERE address.customers_id = " . xtc_db_input($customerData['customers_id']) . ";";

        $addressResult = xtc_db_query($qry);
        if (empty($addressResult)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_NO_ADDRESSES_FOUND, 'User: ' . $userUtf8
            );
        }

        $addresses = array();
        while ($addressData = xtc_db_fetch_array($addressResult)) {
            try {
                $stateCode = ShopgateXtcMapper::getShopgateStateCode(
                    $addressData["countries_iso_code_2"], $addressData["zone_code"]
                );
            } catch (ShopgateLibraryException $e) {
                // if state code can't be mapped to ISO use xtc3 state code
                $stateCode = $addressData['zone_code'];
            }

            $address = new ShopgateAddress();
            $address->setId($addressData['address_book_id']);
            $address->setAddressType(ShopgateAddress::BOTH); // xtc3 doesn't make a difference
            $address->setGender($addressData["entry_gender"]);
            $address->setFirstName($addressData["entry_firstname"]);
            $address->setLastName($addressData["entry_lastname"]);
            $address->setCompany($addressData["entry_company"]);
            $address->setStreet1($addressData["entry_street_address"]);
            $address->setZipcode($addressData["entry_postcode"]);
            $address->setCity($addressData["entry_city"]);
            $address->setCountry($addressData["countries_iso_code_2"]);
            $address->setState($stateCode);

            // put default address in front, append the others
            if ($address->getId() == $customerData['customers_default_address_id']) {
                array_unshift($addresses, $address);
            } else {
                $addresses[] = $address;
            }
        }
        //modified only allows exactly one customer group
        $customerGroup = new ShopgateCustomerGroup();
        $customerGroup->setId($customerData['customers_status_id']);
        $customerGroup->setName($customerData['customers_status_name']);
        $customerGroups[] = $customerGroup;

        $customer = new ShopgateCustomer();
        $customer->setCustomerId($customerData["customers_id"]);
        $customer->setCustomerNumber($customerData["customers_cid"]);
        $customer->setCustomerGroup($customerData['customers_status_name']);
        $customer->setCustomerGroupId($customerData['customers_status_id']);
        $customer->setCustomerGroups($customerGroups);
        $customer->setGender($customerData["customers_gender"]);
        $customer->setFirstName($customerData["customers_firstname"]);
        $customer->setLastName($customerData["customers_lastname"]);
        $customer->setBirthday($customerData["customers_birthday"]);
        $customer->setPhone($customerData["customers_telephone"]);
        $customer->setMail($customerData["customers_email_address"]);
        $customer->setAddresses($addresses);
        $customer->setCustomerToken($this->getCustomerToken($customerData));

        try {
            // utf-8 encode the values recursively
            $customer = $customer->utf8Encode($this->config->getEncoding());
        } catch (ShopgateLibraryException $e) {
            // don't abort here
        }

        return $customer;
    }

    public function addOrder(ShopgateOrder $order)
    {
        // save UTF-8 payment info (to build proper json)
        $paymentInfoUtf8 = $order->getPaymentInfos();
        $couponModel     = new ShopgateCouponModel(
            $this->config, $this->languageId, $this->language, $this->currency, $this->countryId
        );
        $this->log('start add_order()', ShopgateLogger::LOGTYPE_DEBUG);

        // data needs to be utf-8 decoded for äöüß and the like to be saved correctly
        $order = $order->utf8Decode($this->config->getEncoding());
        if ($order instanceof ShopgateOrder) {
            ;
        } // for Eclipse auto-completion

        $this->log('db: duplicate_order', ShopgateLogger::LOGTYPE_DEBUG);

        // check that the order is not imported already
        $qry     = "
            SELECT
            o.*,
            so.shopgate_order_number
            FROM " . TABLE_ORDERS . " o
            INNER JOIN " . TABLE_SHOPGATE_ORDERS . " so ON (so.orders_id = o.orders_id)
            WHERE so.shopgate_order_number = '{$order->getOrderNumber()}'
        ";
        $result  = xtc_db_query($qry);
        $dbOrder = xtc_db_fetch_array($result);

        if (!empty($dbOrder)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DUPLICATE_ORDER, 'external_order_number: ' . $dbOrder["orders_id"],
                true
            );
        }

        // retrieve address information
        $delivery = $order->getDeliveryAddress();
        $invoice  = $order->getInvoiceAddress();

        // find customer
        $customerId = $order->getExternalCustomerId();

        $shopCustomer = array();
        if (!empty($customerId)) {
            $this->log('db: customer', ShopgateLogger::LOGTYPE_DEBUG);
            $result       = xtc_db_query("SELECT * FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '{$customerId}'");
            $shopCustomer = xtc_db_fetch_array($result);
        }
        if (empty($shopCustomer)) {
            $this->log('create Guest User', ShopgateLogger::LOGTYPE_DEBUG);
            $shopCustomer = $this->createGuestUser($order);
        }

        // get customers address
        $qry              = "SELECT * FROM `" . TABLE_ADDRESS_BOOK . "` AS `ab`
            WHERE `ab`.`customers_id` = '{$shopCustomer['customers_id']}'"
            . (!empty($shopCustomer['customers_default_address_id']) ? ("
                AND `ab`.`address_book_id` = '{$shopCustomer['customers_default_address_id']}'") : "") . ";";
        $qryResult        = xtc_db_query($qry);
        $customersAddress = xtc_db_fetch_array($qryResult);
        // get address format
        if (!empty($customersAddress)) {
            $addressFormatCustomer = $this->getAddressFormatId(null, $customersAddress['entry_country_id']);
        } else {
            $customersAddress = array(
                'entry_gender'         => $shopCustomer['customers_gender'],
                'entry_company'        => '',
                'entry_firstname'      => $shopCustomer['customers_firstname'],
                'entry_lastname'       => $shopCustomer['customers_lastname'],
                'entry_street_address' => '',
                'entry_suburb'         => '',
                'entry_postcode'       => '',
                'entry_city'           => '',
                'entry_state'          => '',
                'entry_country_id'     => '',
                'entry_zone_id'        => '',
            );
        }
        $addressFormatDelivery = $this->getAddressFormatId($delivery->getCountry());
        $addressFormatInvoice  = $this->getAddressFormatId($invoice->getCountry());
        if (empty($addressFormatCustomer)) {
            $addressFormatCustomer = $addressFormatInvoice;
        }
        if (empty($addressFormatCustomer)) {
            $addressFormatCustomer = $addressFormatDelivery;
        }

        $this->log('db: customer_status', ShopgateLogger::LOGTYPE_DEBUG);

        $result          = xtc_db_query(
            "SELECT * FROM " . TABLE_CUSTOMERS_STATUS
            . " WHERE language_id = '{$this->languageId}' AND customers_status_id = '{$shopCustomer["customers_status"]}'"
        );
        $customersStatus = xtc_db_fetch_array($result);
        if (empty($customersStatus)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_NO_CUSTOMER_GROUP_FOUND, print_r($shopCustomer, true)
            );
        }


        $this->log('before ShopgateMapper', ShopgateLogger::LOGTYPE_DEBUG);

        // map state codes (called "zone id" in shopsystem)
        $customersStateCode = $customersAddress['entry_state'];
        $invoiceStateCode   = $invoice->getState();
        $deliveryStateCode  = $delivery->getState();

        $this->log('db: countries', ShopgateLogger::LOGTYPE_DEBUG);

        $result           = xtc_db_query(
            "SELECT * FROM " . TABLE_COUNTRIES . " WHERE countries_id = '{$customersAddress['entry_country_id']}'"
        );
        $customersCountry = xtc_db_fetch_array($result);
        $result           = xtc_db_query(
            "SELECT * FROM " . TABLE_COUNTRIES . " WHERE countries_iso_code_2 = '{$delivery->getCountry()}'"
        );
        $deliveryCountry  = xtc_db_fetch_array($result);
        $result           = xtc_db_query(
            "SELECT * FROM " . TABLE_COUNTRIES . " WHERE countries_iso_code_2 = '{$invoice->getCountry()}'"
        );
        $invoiceCountry   = xtc_db_fetch_array($result);
        if (empty($customersCountry)) {
            $customersCountry = $invoiceCountry;
        }
        if (empty($customersCountry)) {
            $customersCountry = $deliveryCountry;
        }

        ///////////////////////////////////////////////////////////////////////
        // Save order
        ///////////////////////////////////////////////////////////////////////

        $orderData                              = array();
        $orderData["customers_id"]              = $shopCustomer["customers_id"];
        $orderData["customers_cid"]             = $shopCustomer["customers_cid"];
        $orderData["customers_vat_id"]          = $shopCustomer["customers_vat_id"];
        $orderData["customers_status"]          = $customersStatus["customers_status_id"];
        $orderData["customers_status_name"]     = $customersStatus["customers_status_name"];
        $orderData["customers_status_image"]    = $customersStatus["customers_status_image"];
        $orderData["customers_status_discount"] = 0;

        $orderData["customers_name"]              =
            $customersAddress['entry_firstname'] . " " . $customersAddress['entry_lastname'];
        $orderData["customers_firstname"]         = $customersAddress['entry_firstname'];
        $orderData["customers_lastname"]          = $customersAddress['entry_lastname'];
        $orderData["customers_company"]           = $customersAddress['entry_company'];
        $orderData["customers_street_address"]    = $customersAddress['entry_street_address'];
        $orderData["customers_suburb"]            = $customersAddress['entry_suburb'];
        $orderData["customers_city"]              = $customersAddress['entry_city'];
        $orderData["customers_postcode"]          = $customersAddress['entry_postcode'];
        $orderData["customers_state"]             = $customersStateCode;
        $orderData["customers_country"]           = $customersCountry['countries_name'];
        $orderData["customers_telephone"]         = $shopCustomer['customers_telephone'];
        $orderData["customers_email_address"]     = $shopCustomer['customers_email_address'];
        $orderData["customers_address_format_id"] = $addressFormatCustomer;

        $orderData["delivery_name"]               = $delivery->getFirstName() . " " . $delivery->getLastName();
        $orderData["delivery_firstname"]          = $delivery->getFirstName();
        $orderData["delivery_lastname"]           = $delivery->getLastName();
        $orderData["delivery_company"]            = $delivery->getCompany();
        $orderData["delivery_street_address"]     =
            $delivery->getStreet1() . (strlen($delivery->getStreet2()) > 0 ? (' ' . $delivery->getStreet2()) : '');
        $orderData["delivery_suburb"]             = "";
        $orderData["delivery_city"]               = $delivery->getCity();
        $orderData["delivery_postcode"]           = $delivery->getZipcode();
        $orderData["delivery_state"]              = $deliveryStateCode;
        $orderData["delivery_country"]            = $deliveryCountry["countries_name"];
        $orderData["delivery_country_iso_code_2"] = $delivery->getCountry();
        $orderData["delivery_address_format_id"]  = $addressFormatDelivery;

        $orderData["billing_name"]               = $invoice->getFirstName() . " " . $invoice->getLastName();
        $orderData["billing_firstname"]          = $invoice->getFirstName();
        $orderData["billing_lastname"]           = $invoice->getLastName();
        $orderData["billing_company"]            = $invoice->getCompany();
        $orderData["billing_street_address"]     =
            $invoice->getStreet1() . (strlen($invoice->getStreet2()) > 0 ? (' ' . $invoice->getStreet2()) : '');
        $orderData["billing_suburb"]             = "";
        $orderData["billing_city"]               = $invoice->getCity();
        $orderData["billing_postcode"]           = $invoice->getZipcode();
        $orderData["billing_state"]              = $invoiceStateCode;
        $orderData["billing_country"]            = $invoiceCountry["countries_name"];
        $orderData["billing_country_iso_code_2"] = $invoice->getCountry();
        $orderData["billing_address_format_id"]  = $addressFormatInvoice;

        // load all languages
        $qry = xtc_db_query("SELECT directory as dir FROM languages as l WHERE l.languages_id = {$this->languageId};");

        $languageDirectory = 'german';
        while ($row = xtc_db_fetch_array($qry)) {
            $languageDirectory = $row['dir'];
        }

        $shippingInfos      = $order->getShippingInfos();
        $shippingModuleName = strtolower($shippingInfos->getName());

        if (empty($shippingModuleName)
            || !file_exists(
                DIR_FS_LANGUAGES . $languageDirectory . '/modules/shipping/' . $shippingModuleName . '.php'
            )
        ) {
            $shippingModuleName = $this->config->getShipping();
        }

        if (empty($shippingModuleName)
            || !file_exists(
                DIR_FS_LANGUAGES . $languageDirectory . '/modules/shipping/' . $shippingModuleName . '.php'
            )
        ) {
            $shippingModuleName = 'flat';
        }

        require_once(DIR_FS_LANGUAGES . $languageDirectory . '/modules/shipping/' . $shippingModuleName . '.php');
        $shippingMethod = constant('MODULE_SHIPPING_' . strtoupper($shippingModuleName) . '_TEXT_TITLE');
        $shippingClass  = $shippingModuleName . '_' . $shippingModuleName;

        $orderData["shipping_method"] = $shippingMethod;
        $orderData["shipping_class"]  = $shippingClass;

        if (!$this->assertMinimumVersion('2.0.0.0')) {
            $orderData["cc_type"]    = "";
            $orderData["cc_owner"]   = "";
            $orderData["cc_number"]  = "";
            $orderData["cc_expires"] = "";
            $orderData["cc_start"]   = "";
            $orderData["cc_issue"]   = "";
            $orderData["cc_cvv"]     = "";
        }

        $orderData["comments"]   = "";

        $orderData["last_modified"]  = date('Y-m-d H:i:s');
        $orderData["date_purchased"] = $order->getCreatedTime('Y-m-d H:i:s');

        $orderData["currency"]       = $order->getCurrency();
        $orderData["currency_value"] = $this->exchangeRate;

        $orderData["account_type"] = "";

        $orderData["payment_method"] = "shopgate";
        $orderData["payment_class"]  = "shopgate";

        $orderData["customers_ip"] = "";
        $orderData["language"]     = $this->language;

        $orderData["afterbuy_success"] = 0;
        $orderData["afterbuy_id"]      = 0;

        $orderData["refferers_id"]    = 0;
        $orderData["conversion_type"] = "2";

        $orderData["orders_status"] = $this->config->getOrderStatusOpen();

        $orderData["orders_date_finished"] = null;

        /**
         * Add custom variables to order database if columns exist, else print
         * The objects are cloned as they are destructively manipulated
         */
        $customFieldObj = new ShopgateCustomFieldModel();
        $comment        = '';
        foreach (array(clone $delivery, clone $order, clone $invoice) as $object) {
            $orderData = array_merge($orderData, $customFieldObj->prepareCustomFields($object));
            $comment .= $customFieldObj->printShopgateCustomFields($object);
        }

        $this->log('db: save order', ShopgateLogger::LOGTYPE_DEBUG);

        // Speichere die Bestellung
        xtc_db_perform(TABLE_ORDERS, $orderData);
        $dbOrderId = xtc_db_insert_id();

        $this->log('db: save, id: ' . $dbOrderId, ShopgateLogger::LOGTYPE_DEBUG);

        $ordersShopgateOrder = array(
            "orders_id"                        => $dbOrderId,
            "shopgate_order_number"            => $order->getOrderNumber(),
            "is_paid"                          => $order->getIsPaid(),
            "is_shipping_blocked"              => $order->getIsShippingBlocked(),
            "payment_infos"                    => $this->jsonEncode($paymentInfoUtf8),
            "is_sent_to_shopgate"              => 0,
            "is_cancellation_sent_to_shopgate" => 0,
            "modified"                         => "now()",
            "created"                          => "now()",
        );
        xtc_db_perform(TABLE_SHOPGATE_ORDERS, $ordersShopgateOrder);

        $this->log('method: _insertStatusHistory() ', ShopgateLogger::LOGTYPE_DEBUG);
        $this->insertStatusHistory($order, $dbOrderId, $orderData['orders_status']);

        $this->log('method: _setOrderPayment() ', ShopgateLogger::LOGTYPE_DEBUG);
        $this->setOrderPayment($order, $dbOrderId, $orderData['orders_status']);

        $this->log('method: _insertOrderItems() ', ShopgateLogger::LOGTYPE_DEBUG);
        $this->insertOrderItems($order, $dbOrderId, $orderData['orders_status'], $couponModel);

        $this->log('method: _insertOrderTotal() ', ShopgateLogger::LOGTYPE_DEBUG);
        //todo return product infos for email
        $this->insertOrderTotal($order, $dbOrderId, $couponModel);

        /**
         * Print custom variables in the comments
         */
        $orderModel = new ShopgateOrderModel();
        $orderModel->setOrderId($dbOrderId);
        $orderModel->saveHistory($orderData['orders_status'], $comment);

        $this->log('db: update order ', ShopgateLogger::LOGTYPE_DEBUG);

        // Save status in order
        $orderUpdateData                  = array();
        $orderUpdateData["orders_status"] = $orderData["orders_status"];
        $orderUpdateData["last_modified"] = date('Y-m-d H:i:s');
        xtc_db_perform(TABLE_ORDERS, $orderUpdateData, "update", "orders_id = {$dbOrderId}");

        $this->log('method: _pushOrderToAfterbuy', ShopgateLogger::LOGTYPE_DEBUG);
        $this->pushOrderToAfterbuy($dbOrderId, $order);
        $this->log('method: _pushOrderToDreamRobot', ShopgateLogger::LOGTYPE_DEBUG);
        $this->pushOrderToDreamRobot($dbOrderId, $order);

        if ($this->config->getSendOrderConfirmationMail()) {
            $this->log('method: sendOrderEmail', ShopgateLogger::LOGTYPE_DEBUG);
            $this->sendOrderEmail($dbOrderId, $shopCustomer['customers_id']);
        }
        $this->log('return: end addOrder()', ShopgateLogger::LOGTYPE_DEBUG);

        return array(
            'external_order_id'     => $dbOrderId,
            'external_order_number' => $dbOrderId
        );
    }

    public function updateOrder(ShopgateOrder $order)
    {
        // save UTF-8 payment infos (to build proper json)
        $paymentInfosUtf8 = $order->getPaymentInfos();

        // data needs to be utf-8 decoded for äöüß and the like to be saved correctly
        /** @var ShopgateOrder $order */
        $order = $order->utf8Decode($this->config->getEncoding());

        $qry     = "
        SELECT
            o.*,
            so.shopgate_order_id,
            so.shopgate_order_number,
            so.is_paid,
            so.is_shipping_blocked,
            so.payment_infos
        FROM " . TABLE_ORDERS . " o
        INNER JOIN " . TABLE_SHOPGATE_ORDERS . " so ON (so.orders_id = o.orders_id)
        WHERE so.shopgate_order_number = '{$order->getOrderNumber()}'
        ";
        $result  = xtc_db_query($qry);
        $dbOrder = xtc_db_fetch_array($result);

        if ($dbOrder == false) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_ORDER_NOT_FOUND, "Shopgate order number: '{$order->getOrderNumber()}'."
            );
        }

        $errorOrderStatusIsSent                     = false;
        $errorOrderStatusAlreadySet                 = array();
        $statusShoppingSystemOrderIsPaid            = $dbOrder['is_paid'];
        $statusShoppingSystemOrderIsShippingBlocked = $dbOrder['is_shipping_blocked'];
        $status                                     = $dbOrder["orders_status"];

        // check if shipping is already done, then throw at end of method a OrderStatusIsSent - Exception
        if ($status == $this->config->getOrderStatusShipped()
            && ($statusShoppingSystemOrderIsShippingBlocked
                || $order->getIsShippingBlocked())
        ) {
            $errorOrderStatusIsSent = true;
        }

        if ($order->getUpdatePayment() == 1) {

            if (!is_null($statusShoppingSystemOrderIsPaid) && $order->getIsPaid() == $statusShoppingSystemOrderIsPaid
                && !is_null($dbOrder['payment_infos'])
                && $dbOrder['payment_infos'] == $this->jsonEncode($paymentInfosUtf8)
            ) {
                $errorOrderStatusAlreadySet[] = 'payment';
            }

            if (!is_null($statusShoppingSystemOrderIsPaid) && $order->getIsPaid() == $statusShoppingSystemOrderIsPaid) {
                // do not update is_paid
            } else {

                // Save order status
                $orderStatus                      = array();
                $orderStatus["orders_id"]         = $dbOrder["orders_id"];
                $orderStatus["orders_status_id"]  = $status;
                $orderStatus["date_added"]        = date('Y-m-d H:i:s');
                $orderStatus["customer_notified"] = false;
                if ($order->getIsPaid()) {
                    $orderStatus['comments'] = 'Bestellstatus von Shopgate geändert: Zahlung erhalten';
                } else {
                    $orderStatus['comments'] = 'Bestellstatus von Shopgate geändert: Zahlung noch nicht erhalten';
                }

                $orderStatus['comments'] =
                    $this->stringFromUtf8($orderStatus['comments'], $this->config->getEncoding());

                xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $orderStatus);

                // update the shopgate order status information
                $ordersShopgateOrder = array(
                    "is_paid"  => (int)$order->getIsPaid(),
                    "modified" => "now()",
                );
                xtc_db_perform(
                    TABLE_SHOPGATE_ORDERS, $ordersShopgateOrder, "update",
                    "shopgate_order_id = {$dbOrder['shopgate_order_id']}"
                );

                // update var
                $statusShoppingSystemOrderIsPaid = $order->getIsPaid();

                // Save status in order
                $orderData                  = array();
                $orderData["orders_status"] = $status;
                $orderData["last_modified"] = date('Y-m-d H:i:s');
                xtc_db_perform(TABLE_ORDERS, $orderData, "update", "orders_id = {$dbOrder['orders_id']}");

            }

            // update paymentinfos
            if (!is_null($dbOrder['payment_infos'])
                && $dbOrder['payment_infos'] != $this->jsonEncode(
                    $paymentInfosUtf8
                )
            ) {

                $dbPaymentInfo = $this->jsonDecode($dbOrder['payment_infos'], true);
                $paymentInfo   = $order->getPaymentInfos();
                $histories     = array();

                switch ($order->getPaymentMethod()) {
                    case ShopgateOrder::SHOPGATE:
                    case ShopgateOrder::INVOICE:
                    case ShopgateOrder::COD:
                        break;
                    case ShopgateOrder::PREPAY:

                        if (isset($dbPaymentInfo['purpose'])
                            && $paymentInfo['purpose'] != $dbPaymentInfo['purpose']
                        ) {
                            $comments = $this->stringFromUtf8(
                                "Shopgate: Zahlungsinformationen wurden aktualisiert: \n\nDer Kunde wurde angewiesen Ihnen das Geld mit dem Verwendungszweck \"",
                                $this->config->getEncoding()
                            );
                            $comments .= $paymentInfo["purpose"];
                            $comments .= $this->stringFromUtf8(
                                "\" auf Ihr Bankkonto zu überweisen", $this->config->getEncoding()
                            );

                            // Order is not paid yet
                            $histories[] = array(
                                "orders_id"         => $dbOrder["orders_id"],
                                "orders_status_id"  => $status,
                                "date_added"        => date('Y-m-d H:i:s'),
                                "customer_notified" => false,
                                "comments"          => ShopgateWrapper::db_prepare_input($comments)
                            );
                        }

                        break;
                    case ShopgateOrder::DEBIT:
                        $qry            = "
                            SELECT
                                *
                            FROM banktransfer b
                            WHERE b.orders_id = '{$dbOrder['orders_id']}'";
                        $result         = xtc_db_query($qry);
                        $dbBanktransfer = xtc_db_fetch_array($result);

                        if (!empty($dbBanktransfer)) {
                            $banktransferData                          = array();
                            $banktransferData["banktransfer_owner"]    = $paymentInfo["bank_account_holder"];
                            $banktransferData["banktransfer_number"]   = $paymentInfo["bank_account_number"];
                            $banktransferData["banktransfer_bankname"] = $paymentInfo["bank_name"];
                            $banktransferData["banktransfer_blz"]      = $paymentInfo["bank_code"];
                            xtc_db_perform(
                                "banktransfer", $banktransferData, "update", "orders_id = {$dbOrder['orders_id']}"
                            );

                            $comments = $this->stringFromUtf8(
                                "Shopgate: Zahlungsinformationen wurden aktualisiert: \n\n",
                                $this->config->getEncoding()
                            );
                            $comments .= $this->createPaymentInfos(
                                $paymentInfo, $dbOrder['orders_id'], $status, false
                            );

                            $histories[] = array(
                                "orders_id"         => $dbOrder["orders_id"],
                                "orders_status_id"  => $status,
                                "date_added"        => date('Y-m-d H:i:s'),
                                "customer_notified" => false,
                                "comments"          => ShopgateWrapper::db_prepare_input($comments)
                            );
                        }

                        break;
                    case ShopgateOrder::PAYPAL:

                        // Save payment info in history
                        $history             =
                            $this->createPaymentInfos($paymentInfo, $dbOrder["orders_id"], $status);
                        $history['comments'] = $this->stringFromUtf8(
                                "Shopgate: Zahlungsinformationen wurden aktualisiert: \n\n",
                                $this->config->getEncoding()
                            ) . $history['comments'];
                        $histories[]         = $history;

                        break;
                    default:
                        // mobile_payment

                        // Save paymentinfos in history
                        $history             =
                            $this->createPaymentInfos($paymentInfo, $dbOrder["orders_id"], $status);
                        $history['comments'] = $this->stringFromUtf8(
                                "Shopgate: Zahlungsinformationen wurden aktualisiert: \n\n",
                                $this->config->getEncoding()
                            ) . $history['comments'];
                        $histories[]         = $history;

                        break;
                }

                foreach ($histories as $history) {
                    xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $history);
                }
            }

            $ordersShopgateOrder = array(
                "payment_infos" => $this->jsonEncode($paymentInfosUtf8),
                "modified"      => "now()",
            );
            xtc_db_perform(
                TABLE_SHOPGATE_ORDERS, $ordersShopgateOrder, "update",
                "shopgate_order_id = {$dbOrder['shopgate_order_id']}"
            );

        }


        if ($order->getUpdateShipping() == 1) {

            if (!is_null($statusShoppingSystemOrderIsShippingBlocked)
                && $order->getIsShippingBlocked() == $statusShoppingSystemOrderIsShippingBlocked
            ) {
                $errorOrderStatusAlreadySet[] = 'shipping';
            } else {
                if ($status != $this->config->getOrderStatusShipped()) {
                    if ($order->getIsShippingBlocked() == 1) {
                        $status = $this->config->getOrderStatusShippingBlocked();
                    } else {
                        $status = $this->config->getOrderStatusOpen();
                    }
                }

                $orderStatus                      = array();
                $orderStatus["orders_id"]         = $dbOrder["orders_id"];
                $orderStatus["date_added"]        = date('Y-m-d H:i:s');
                $orderStatus["customer_notified"] = false;
                $orderStatus['orders_status_id']  = $status;
                if ($order->getIsShippingBlocked() == 0) {
                    $orderStatus["comments"] = "Bestellstatus von Shopgate geändert: Versand ist nicht mehr blockiert!";
                } else {
                    $orderStatus['comments'] = 'Bestellstatus von Shopgate geändert: Versand ist blockiert!';
                }

                $orderStatus['comments'] =
                    $this->stringFromUtf8($orderStatus['comments'], $this->config->getEncoding());

                xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $orderStatus);

                $ordersShopgateOrder = array(
                    "is_shipping_blocked" => (int)$order->getIsShippingBlocked(),
                    "modified"            => "now()",
                );
                xtc_db_perform(
                    TABLE_SHOPGATE_ORDERS, $ordersShopgateOrder, "update",
                    "shopgate_order_id = {$dbOrder['shopgate_order_id']}"
                );

                // Save status in order
                $orderData                  = array();
                $orderData["orders_status"] = $status;
                $orderData["last_modified"] = date('Y-m-d H:i:s');
                xtc_db_perform(TABLE_ORDERS, $orderData, "update", "orders_id = {$dbOrder['orders_id']}");

                $this->pushOrderToAfterbuy($dbOrder["orders_id"], $order);
                $this->pushOrderToDreamRobot($dbOrder["orders_id"], $order);
            }
        }

        if ($errorOrderStatusIsSent) {
            throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_ORDER_STATUS_IS_SENT);
        }

        if (!empty($errorOrderStatusAlreadySet)) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_ORDER_ALREADY_UP_TO_DATE, implode(',', $errorOrderStatusAlreadySet),
                true
            );
        }

        return array(
            'external_order_id'     => $dbOrder["orders_id"],
            'external_order_number' => $dbOrder["orders_id"]
        );
    }

    protected function createReviewsCsv()
    {

        $reviewModel = new ShopgateReviewModel();
        $reviewModel->setLanguageId($this->languageId);

        $limit  = 10;
        $page   = 1;
        $offset = ($page - 1) * $limit;

        while ($query = xtc_db_query($reviewModel->getReviewQuery($limit, $offset))) {
            $count = xtc_db_num_rows($query);
            if ($count == 0) {
                break;
            }

            $reviews = array();
            while ($entry = xtc_db_fetch_array($query)) {
                $review = $this->buildDefaultReviewRow();

                $review['item_number']      = $entry['products_id'];
                $review['update_review_id'] = $entry['reviews_id'];
                $review['score']            = $entry['reviews_rating'] * 2;
                $review['name']             = $entry['customers_name'];
                $review['date']             = $entry['date_added'];
                $review['title']            = '';
                $review['text']             = $entry['reviews_text'];

                $reviews[] = $review;
            }

            foreach ($reviews as $review) {
                $this->addReviewRow($review);
            }

            $page++;
            $offset = ($page - 1) * $limit;
        }
    }

    public function getSettings()
    {
        $customerModel                 = new ShopgateCustomerModel($this->config, $this->languageId);
        $customerGroups                = $customerModel->getCustomerGroups();

        // Tax rates are pretty much a combination of tax rules and tax rates in osCommerce. So we're using them to generate both:
        $oscTaxRates = $this->getTaxRates();
        $customerTaxClass = array(
            'id'         => "1",
            'key'        => 'default',
            'is_default' => "1");
        $taxRates    = array();
        $taxRules    = array();
        foreach ($oscTaxRates as $oscTaxRate) {
            // build and append tax rate
            $taxRates[] = array(
                'id'            => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                'key'           => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                'display_name'  => $oscTaxRate['tax_description'],
                'tax_percent'   => $oscTaxRate['tax_rate'],
                'country'       => $oscTaxRate['countries_iso_code_2'],
                'state'         => (!empty($oscTaxRate['countries_iso_code_2']) && !empty($oscTaxRate['zone_code']))
                    ? ShopgateXtcMapper::getShopgateStateCode(
                        $oscTaxRate['countries_iso_code_2'], $oscTaxRate['zone_code']
                    )
                    : '',
                'zip_code_type' => 'all',
            );

            // build and append tax rule
            if (!empty($taxRules[$oscTaxRate['tax_rates_id']])) {
                $taxRules[$oscTaxRate['tax_rates_id']]['tax_rates'][] = array(
                    // one rate per rule (since rates are in fact also rules) in xtModified
                    'id'  => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                    'key' => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                );
            } else {
                $taxRules[$oscTaxRate['tax_rates_id']] = array(
                    'id'                   => $oscTaxRate['tax_rates_id'],
                    'name'                 => $oscTaxRate['tax_description'],
                    'priority'             => $oscTaxRate['tax_priority'],
                    'product_tax_classes'  => array(
                        array(
                            'id'  => $oscTaxRate['tax_class_id'],
                            'key' => $oscTaxRate['tax_class_title'],
                        )
                    ),
                    'customer_tax_classes' => array(
                        array(
                            'id' => 1,
                            'key' => 'default'
                        )
                    ),
                    'tax_rates'            => array(
                        array(
                            'id'  => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                            'key' => $oscTaxRate['countries_iso_code_2'] . '-' . $oscTaxRate['tax_rates_id'],
                        )
                    ),
                );
            }
        }

        return array(
            'customer_groups' => $customerGroups,
            'tax'             => array(
                'product_tax_classes'  => $this->getTaxClasses(),
                'customer_tax_classes' => array($customerTaxClass),
                'tax_rates'            => $taxRates,
                'tax_rules'            => $taxRules,
            ),
        );
    }

    public function createShopInfo()
    {
        $shopInfo = array();

        $productCountQuery      = "SELECT count(*) cnt FROM " . TABLE_PRODUCTS . " AS p WHERE p.products_status = 1";
        $result                 = xtc_db_query($productCountQuery);
        $row                    = xtc_db_fetch_array($result);
        $shopInfo['item_count'] = $row['cnt'];

        $catQry                     = "SELECT count(*) cnt FROM " . TABLE_CATEGORIES;
        $result                     = xtc_db_query($catQry);
        $row                        = xtc_db_fetch_array($result);
        $shopInfo['category_count'] = $row['cnt'];

        $revQry                   = "SELECT COUNT(*) AS cnt FROM " . TABLE_REVIEWS;
        $result                   = xtc_db_query($revQry);
        $row                      = xtc_db_fetch_array($result);
        $shopInfo['review_count'] = $row['cnt'];

        // Not provided by Osc
        $shopInfo['plugins_installed '] = array();

        return $shopInfo;
    }

    public function checkCart(ShopgateCart $cart)
    {
        $locationModel = new ShopgateLocationModel();
        $cartItemModel = new ShopgateItemCartModel();
        $customerModel = new ShopgateCustomerModel($this->config, $this->languageId);
        $customer      = $customerModel->getCustomerById($cart->getExternalCustomerId());

        $customerGroupId = (empty($customer) && empty($customer['customers_status'])
            ? DEFAULT_CUSTOMERS_STATUS_ID
            : $customer['customers_status']);

        $includeXtcFiles = array(
            "/inc/xtc_get_tax_class_id.inc.php",
            "/inc/xtc_get_products_stock.inc.php",
        );

        foreach ($includeXtcFiles as $xtcFile) {
            $file = rtrim(DIR_FS_CATALOG, "/") . $xtcFile;
            if (file_exists($file)) {
                include_once $file;
            }
        }

        $result["shipping_methods"] = $this->getShipping($cart, $locationModel, $cartItemModel);
        $result["currency"]         = $this->config->getCurrency();
        $result["items"]            = $this->checkCartItems($cart, $customerGroupId);
        $result['external_coupons'] = $this->checkCoupons($cart, $customerGroupId);
        $customerId                 = $cart->getExternalCustomerId();
        if (!empty($customerId)) {
            $result["customer"] = $this->getCustomerById($customerId);
        }

        return $result;
    }

    /**
     * check the validity of coupons
     *
     * @param ShopgateCart $cart
     * @param int          $customerGroupId
     *
     * @return array
     */
    public function checkCoupons(ShopgateCart $cart, $customerGroupId)
    {
        if (!defined("MODULE_ORDER_TOTAL_COUPON_STATUS") || MODULE_ORDER_TOTAL_COUPON_STATUS !== "true") {
            return array();
        }

        $result        = array();
        $couponModel   = new ShopgateCouponModel(
            $this->config, $this->languageId, $this->language, $this->currency, $this->countryId
        );
        $cartItemModel = new ShopgateItemCartModel();
        $orderAmount   = $cartItemModel->getCompleteAmount($cart);
        foreach ($cart->getExternalCoupons() as $sgCoupon) {
            $coupon = $couponModel->getCouponByCode($sgCoupon->getCode());

            if (empty($coupon)) {
                $sgCoupon->setNotValidMessage(ShopgateLibraryException::COUPON_CODE_NOT_VALID);
                $sgCoupon->setIsValid(false);
                $result[] = $sgCoupon;
                continue;
            }

            $validationResult = $couponModel->validateCoupon($cart, $cartItemModel, $coupon, $orderAmount);

            if (!empty($validationResult)) {
                $sgCoupon->setNotValidMessage($validationResult);
                $sgCoupon->setIsValid(false);
            } else {
                $sgCoupon->setIsValid(true);
                $couponModel->setCouponData($coupon, $sgCoupon, $cart, $cartItemModel, $customerGroupId);
            }
            $result[] = $sgCoupon;
        }

        $reverse          = array_reverse($result);
        $validCouponFound = false;
        /** @var ShopgateExternalCoupon $cp */
        foreach ($reverse as $cp) {

            if ($cp->getIsValid()) {
                if (!$validCouponFound) {
                    $validCouponFound = true;
                } else {
                    $cp->setIsValid(false);
                }
            }
        }

        return $result;
    }

    public function redeemCoupons(ShopgateCart $cart)
    {
    }

    public function cron($jobname, $params, &$message, &$errorcount)
    {
        switch ($jobname) {
            case 'set_shipping_completed':
                $this->cronSetOrdersShippingCompleted($message, $errorcount);
                break;
            case 'cancel_orders':
                $this->cronCancelOrders($message, $errorcount);
                break;
            default:
                throw new ShopgateLibraryException(
                    ShopgateLibraryException::PLUGIN_CRON_UNSUPPORTED_JOB, 'Job name: "' . $jobname . '"', true
                );
        }
    }

    public function checkStock(ShopgateCart $cart)
    {
        $includeXtcFiles = array(
            "/inc/xtc_get_tax_class_id.inc.php",
            "/inc/xtc_get_products_stock.inc.php",
        );

        foreach ($includeXtcFiles as $xtcFile) {
            $file = rtrim(DIR_FS_CATALOG, "/") . $xtcFile;
            if (file_exists($file)) {
                include_once $file;
            }
        }
        $result     = array();
        $itemHelper = new ShopgateItemHelper();
        $itemModel  = new ShopgateItemModel($this->config);
        $itemModel->setLanguageId($this->languageId);
        $itemModel->setStringHelper($this->getHelper(ShopgateObject::HELPER_STRING));
        $itemModel->setModifiedVersion($this->modifiedVersion);

        foreach ($cart->getItems() as $orderItem) {
            $sgCartItem  = new ShopgateCartItem();
            $sgOrderInfo = $this->jsonDecode(stripslashes($orderItem->getInternalOrderInfo()), true);
            $id          = $itemModel->getProductIdFromOrderItem($orderItem);
            $quantity    = $itemHelper->getProductQuantity($id, $sgOrderInfo);
            $status      = $this->xtc_get_products_status($id);
            $sgCartItem->setItemNumber($id);
            $sgCartItem->setStockQuantity($quantity);
            $sgCartItem->setIsBuyable($status);
            if (!$status) {
                $sgCartItem->setIsBuyable(false);
                $sgCartItem->setStockQuantity(0);
                $sgCartItem->setError(ShopgateLibraryException::CART_ITEM_PRODUCT_NOT_FOUND);
                $result[] = $sgCartItem;
                continue;
            }
            if (STOCK_CHECK == 'true' && STOCK_ALLOW_CHECKOUT == 'false') {
                if ($quantity <= 0) {
                    $sgCartItem->setIsBuyable(false);
                    $sgCartItem->setStockQuantity(0);
                    $sgCartItem->setError(ShopgateLibraryException::CART_ITEM_OUT_OF_STOCK);
                    $result[] = $sgCartItem;
                    continue;
                }
                if ($quantity < $orderItem->getQuantity()) {
                    $sgCartItem->setIsBuyable(true);
                    $sgCartItem->setError(ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE);
                    $result[] = $sgCartItem;
                    continue;
                }
            }
            $orderItemTaxClassId = xtc_get_tax_class_id($id);
            $orderItemTaxRate    = xtc_get_tax_rate(
                $orderItemTaxClassId, $this->countryId, $this->zoneId
            );

            $sgCartItem->setOptions($itemModel->getOptionsToProduct($id, $sgOrderInfo, $orderItemTaxRate));
            $sgCartItem->setAttributes($itemModel->getAttributesToProduct($orderItem));

            // not supported
            $sgCartItem->setInputs(array());
            $result[] = $sgCartItem;
        }

        return $result;
    }

    protected function createCategories($limit = null, $offset = null, array $uids = array())
    {

        $model = new ShopgateCategoryXmlModel();
        $model->setLanguageId($this->languageId);

        $maxOrder = $model->getCategoryMaxOrder($this->config->getReverseCategoriesSortOrder());

        $this->buildCategoriesTree(0, $maxOrder, 'xml', $limit, $offset, $uids);

        if ($this->config->getExportNewProductsCategory()) {
            /** @var mixed[] $row */
            $row                    = $this->buildDefaultCategoryRow();
            $row['parent_id']       = '';
            $row['category_number'] =
                $this->config->getExportNewProductsCategoryId();
            $row['category_name']   = 'Neue Produkte';
            $row["is_active"]       = 1;
            $row['url_deeplink']    = xtc_href_link('products_new.php');
            $this->addCategoryRow($row);
        }
    }

    public function getOrders(
        $customerToken, $customerLanguage, $limit = 10, $offset = 0, $orderDateFrom = '', $sortOrder = 'created_desc'
    ) {
        $orderModel = new ShopgateCustomerOrderModel($this->config, $this->languageId);

        return $orderModel->getOrders($customerToken, $customerLanguage, $limit, $offset, $orderDateFrom, $sortOrder);
    }

    protected function createMediaCsv()
    {
        // TODO: Implement createMediaCsv() method.
    }

    protected function createItems($limit = null, $offset = null, array $uids = array())
    {
        $customerModel = new ShopgateCustomerModel($this->config, $this->languageId);
        $itemXmlModel  = new ShopgateItemXmlModel($this->config);
        $itemXmlModel->setLanguageId($this->languageId);
        $itemXmlModel->setDefaultCustomerPriceGroup(DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
        $itemXmlModel->setExportLimit($limit);
        $itemXmlModel->setExportOffset($offset);
        $itemXmlModel->setCountryId($this->countryId);
        $itemXmlModel->setZoneId($this->zoneId);
        $itemXmlModel->setExchangeRate($this->exchangeRate);
        $itemXmlModel->setCurrencyData($this->currency);
        $itemXmlModel->setStringHelper($this->getHelper(ShopgateObject::HELPER_STRING));
        $itemXmlModel->setReverseItemSortOrder($this->config->getReverseItemsSortOrder());
        $itemXmlModel->setModifiedVersion($this->modifiedVersion);
        $itemXmlModel->setLog(ShopgateLogger::getInstance());

        $_SESSION['languages_id'] = $this->languageId;
        $_SESSION['country']      = $this->countryId;
        $xtPricesByCustomerGroups = array();
        foreach ($customerModel->getCustomerGroups() as $customerGroup) {
            // In modified will be checked if the customer group id is empty on price calculation.
            // In this case the default group id will be set. This causes that one group will be exported twice.
            // This causes issues.(Group with id zero is admin group.)
            if ($customerGroup['id'] == 0) {
                continue;
            }
            $xtPricesByCustomerGroups[$customerGroup['id']]                                             =
                new xtcPrice($this->currency['code'], $customerGroup['id']);
            $xtPricesByCustomerGroups[$customerGroup['id']]->cStatus['customers_status_show_price_tax'] = 1;
        }

        $itemXmlModel->setXtPricesByCustomerGroups($xtPricesByCustomerGroups);
        $result = ShopgateWrapper::db_query($itemXmlModel->getProductQuery($uids));

        while ($item = ShopgateWrapper::db_fetch_array($result)) {
            $actualXmlModel = clone $itemXmlModel;
            $actualXmlModel->setItem($item);
            $this->addItemModel($actualXmlModel->generateData());
        }
    }

    public function syncFavouriteList($customerToken, $items)
    {
        // TODO: Implement syncFavouriteList() method.
    }

    protected function createReviews($limit = null, $offset = null, array $uids = array())
    {
        $model = new ShopgateReviewXmlModel($this->config);
        $model->setLanguageId($this->languageId);
        $query  = $model->getReviewQuery($limit, $offset, $uids);
        $result = ShopgateWrapper::db_query($query);

        while ($row = ShopgateWrapper::db_fetch_array($result)) {
            $actualDataModel = clone $model;
            $actualDataModel->setItem($row);
            $this->addReviewModel($actualDataModel->generateData());
        }
    }

    /**
     * generates a customer token if no exist
     *
     * @param array $customerData
     *
     * @return bool|string
     */
    private function getCustomerToken($customerData)
    {
        $customerModel = new ShopgateCustomerModel($this->config, $this->languageId);
        if (!$customerModel->hasCustomerToken($customerData["customers_id"])) {
            return $customerModel->insertToken(
                $customerData["customers_id"], $customerData["customers_email_address"]
            );
        }

        return $customerModel->getCustomerToken($customerData["customers_id"]);
    }

    /**
     * get all products to the virtual category "special products"
     *
     * @var ShopgateItemModel $itemModel
     * @return array
     */
    private function getSpecialProducts($itemModel)
    {
        $result                = array();
        $specialProductsResult = xtc_db_query($itemModel->getProductsToSpecialCategoryQuery());
        while ($specialProduct = xtc_db_fetch_array($specialProductsResult)) {
            $result[] = $specialProduct;
        }

        return $result;
    }

    /**
     * get all products to the virtual category "new products"
     *
     * @var ShopgateItemModel $itemModel
     * @return array
     */
    private function getNewProducts($itemModel)
    {
        $result            = array();
        $newProductsResult = xtc_db_query($itemModel->getProductsToNewCategoryQuery());
        while ($products_new = xtc_db_fetch_array($newProductsResult)) {
            $result[] = $products_new;
        }

        return $result;
    }

    /**
     * tax classes
     *
     * @return array
     * @throws ShopgateLibraryException
     */
    protected function getTaxClasses()
    {
        $sqlQuery    = "SELECT `tbl_tc`.`tax_class_id` 'id', `tbl_tc`.`tax_class_title` 'key' FROM `" . TABLE_TAX_CLASS
            . "` AS tbl_tc";
        $queryResult = xtc_db_query($sqlQuery);
        $result      = array();
        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR, "Shopgate Plugin - Error selecting.", true
            );
        } else {
            while ($row = xtc_db_fetch_array($queryResult)) {
                foreach ($row AS &$value) {
                    $value = utf8_encode($value);
                }
                array_push($result, $row);
            }
        }

        return $result;
    }

    /**
     * get tax rates
     *
     * @return array<string, mixed>[]
     * @throws ShopgateLibraryException
     */
    protected function getTaxRates()
    {
        $query =
            "SELECT tr.tax_rates_id, tr.tax_description, tr.tax_rate, tr.tax_priority, " .
            "c.countries_iso_code_2, z.zone_code, tc.tax_class_id, tc.tax_class_title " .

            "FROM `" . TABLE_TAX_RATES . "` AS tr " .

            "JOIN `" . TABLE_GEO_ZONES . "` AS gz ON tr.tax_zone_id = gz.geo_zone_id " .
            "JOIN `" . TABLE_ZONES_TO_GEO_ZONES . "` AS ztgz ON gz.geo_zone_id = ztgz.geo_zone_id " .

            "JOIN `" . TABLE_COUNTRIES . "` AS c ON ztgz.zone_country_id = c.countries_id " .
            "LEFT OUTER JOIN `" . TABLE_ZONES . "` AS z ON ztgz.zone_id = z.zone_id "
            . // zone (aka state) might not be mapped, rate applies for whole country in that case
            "JOIN `" . TABLE_TAX_CLASS . "` tc ON tr.tax_class_id = tc.tax_class_id;";

        $result = xtc_db_query($query);
        if (!$result) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR, "Shopgate Plugin - Error selecting.", true
            );
        }

        $taxRates = array();
        while ($row = xtc_db_fetch_array($result)) {
            foreach ($row AS &$value) {
                $value = utf8_encode($value);
            }
            $taxRates[] = $row;
        }

        return $taxRates;
    }

    /**
     * return an array with all valid shipping methods to an order
     *
     * @param ShopgateCart          $sgShoppingCart
     * @param ShopgateLocationModel $locationModel
     * @param ShopgateItemCartModel $cartItemModel
     *
     * @return array
     */
    private function getShipping(
        ShopgateCart $sgShoppingCart, ShopgateLocationModel $locationModel, ShopgateItemCartModel $cartItemModel
    ) {
        $this->log("Start getting the shop system's shipping methods.", ShopgateLogger::LOGTYPE_DEBUG);
        $resultShippingMethods = array();
        $sgDeliveryAddress = $sgShoppingCart->getDeliveryAddress();

        if (empty($sgDeliveryAddress) || !defined('MODULE_SHIPPING_INSTALLED') || MODULE_SHIPPING_INSTALLED == "") {
            return $resultShippingMethods;
        }

        /* include globals */
        global $total_count, $total_weight,
               $shipping_num_boxes, $cart, $order, $sendto, $billto, $ot_shipping;

        $neededFilesFromShopSystem = array(
            "/shopping_cart.php",
            "/order.php",
            "/order_total/ot_shipping.php",
            "/shipping.php"
        );

        foreach ($neededFilesFromShopSystem as $file) {
            if (file_exists(rtrim(DIR_WS_CLASSES, "/") . $file)) {
                include_once(rtrim(DIR_WS_CLASSES, "/") . $file);
            }
            if (file_exists(rtrim(DIR_WS_MODULES, "/") . $file)) {
                include_once(rtrim(DIR_WS_MODULES, "/") . $file);
            }
        }

        $total_count  = count($sgShoppingCart->getItems());
        $total_weight = 0;
        if ($total_count > 0) {
            $total_weight = $cartItemModel->getProductsWeight($sgShoppingCart->getItems());
        }
        $shipping_num_boxes = 1;
        $cart               = new shoppingCart();

        foreach ($sgShoppingCart->getItems() as $product) {
            $options    = $product->getOptions();
            $sgOptions  = array();
            $itemNumber = !is_null($product->getParentItemNumber())
                ? $product->getParentItemNumber()
                : $product->getItemNumber();

            foreach ($options as $option) {
                $sgOptions[$option->getOptionNumber()] = $option->getValueNumber();
            }

            $cart->add_cart($itemNumber, $product->getQuantity(), $sgOptions);
        }

        if (is_array($_SESSION)) {
            $_SESSION['cart'] = $cart;
        }

        $country = $locationModel->getCountryByIso2Name($sgDeliveryAddress->getCountry());
        $zone = $locationModel->getZoneByCountryId($country["countries_id"]);
        $deliveryPostcode = $sgDeliveryAddress->getZipcode();
        $sendto = array(
            "firstname" => $sgDeliveryAddress->getFirstName(),
            "lastname" => $sgDeliveryAddress->getLastName(),
            "company" => $sgDeliveryAddress->getCompany(),
            "street_address" => $sgDeliveryAddress->getStreet1(),
            "suburb" => "",
            "postcode" => $deliveryPostcode,
            "city" => $sgDeliveryAddress->getCity(),
            "zone_id" => $zone["zone_id"],
            "zone_name" => $zone["zone_name"],
            "country_id" => $country["countries_id"],
            "country_iso_code_2" => $country["countries_iso_code_2"],
            "country_iso_code_3" => $country["countries_iso_code_3"],
            "address_format_id" => "",
        );

        $sgInvoiceAddress = $sgShoppingCart->getInvoiceAddress();
        if (empty($sgInvoiceAddress)) {
            $billto = $sendto;
        } else {
            $country  = $locationModel->getCountryByIso2Name($sgInvoiceAddress->getCountry());
            $zone     = $locationModel->getZoneByCountryId($country["countries_id"]);
            $postcode = $sgInvoiceAddress->getZipcode();
            $billto   = array(
                "firstname"          => $sgInvoiceAddress->getFirstName(),
                "lastname"           => $sgInvoiceAddress->getLastName(),
                "company"            => $sgInvoiceAddress->getCompany(),
                "street_address"     => $sgInvoiceAddress->getStreet1(),
                "suburb"             => "",
                "postcode"           => $postcode,
                "city"               => $sgInvoiceAddress->getCity(),
                "zone_id"            => $zone["zone_id"],
                "zone_name"          => $zone["zone_name"],
                "country_id"         => $country["countries_id"],
                "country_iso_code_2" => $country["countries_iso_code_2"],
                "country_iso_code_3" => $country["countries_iso_code_3"],
                "address_format_id"  => "",
            );
        }

        $order = new order();
        $order->cart();
        $order->delivery['country']['iso_code_2'] = $country["countries_iso_code_2"];
        $order->delivery['country']['id']         = $country["countries_id"];
        $order->delivery['country']['zone_id']    = $zone["zone_id"];
        $order->delivery['postcode']              = $deliveryPostcode;
        $_SESSION['delivery_zone']                = $country["countries_iso_code_2"];
        $ot_shipping                              = new ot_shipping();
        $ot_shipping->process();

        $shipping_modules = new shipping;

        if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING')
            && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')
        ) {
            $pass = false;

            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                case 'national':
                    if ($order->delivery['country']['id'] == STORE_COUNTRY) {
                        $pass = true;
                    }
                    break;
                case 'international':
                    if ($order->delivery['country']['id'] != STORE_COUNTRY) {
                        $pass = true;
                    }
                    break;
                case 'both':
                    $pass = true;
                    break;
            }

            $free_shipping = false;
            if (($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                $free_shipping = true;
                include(DIR_WS_LANGUAGES . $this->language . '/modules/order_total/ot_shipping.php');
            }
        } else {
            $free_shipping = false;
        }

        //if shipping is free all other shipping methods will be ignored
        if ($free_shipping) {
            $sgShippingMethod = new ShopgateShippingMethod();
            $sgShippingMethod->setDescription(
                "Total amount over "
                . MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER . " is free"
            );
            $sgShippingMethod->setTitle("Free Shipping");
            $sgShippingMethod->setAmount(0);

            return array($sgShippingMethod);
        }

        $quotes             = $shipping_modules->quote();
        $unsupportedModules = array("United States Postal Service");
        foreach ($quotes AS $shippingModule) {
            //we dont support usps as shopgate plugin shipping method, also on error continue
            foreach ($unsupportedModules AS $moduleName) {
                if (strpos($shippingModule['module'], $moduleName) !== false || !empty($shippingModule['error'])) {
                    continue;
                }
            }

            $sgShippingMethod = new ShopgateShippingMethod();
            $sgShippingMethod->setId($shippingModule["id"]);
            $sgShippingMethod->setTitle($shippingModule["module"]);
            $sgShippingMethod->setTaxPercent($shippingModule["tax"]);

            if (isset($shippingModule["tax"]) && !empty($shippingModule["tax"])) {
                $sgShippingMethod->setTaxClass($locationModel->getTaxClassByValue($shippingModule["tax"]));
            }

            if (!empty($shippingModule["methods"]) && is_array($shippingModule["methods"])) {
                foreach ($shippingModule["methods"] as $method) {

                    $tmp_shipping = $sgShippingMethod;
                    $cost         = $method["cost"];

                    if (isset($shippingModule["tax"]) && !empty($shippingModule["tax"])) {
                        $costWithTax = $this->formatPriceNumber($cost * (1 + ($shippingModule["tax"] / 100)), 2);
                        $tmp_shipping->setAmountWithTax($costWithTax);
                        $tmp_shipping->setAmount($cost);
                    } else {
                        $tmp_shipping->setAmountWithTax($cost);
                    }

                    $resultShippingMethods[] = $tmp_shipping;
                }
            }
        }

        return $resultShippingMethods;
    }

    /**
     * get customer data by id from the database
     *
     * @param $customerId
     *
     * @return array|bool|mixed
     */

    public function getCustomerById($customerId)
    {

        $qry = "SELECT"
            . " customers_status"
            . " FROM " . TABLE_CUSTOMERS
            . " WHERE customers_id = " . $customerId . ";";

        // user exists?
        $customerResult = xtc_db_query($qry);
        if (empty($customerResult)) {
            return null;
        }

        $customerData  = xtc_db_fetch_array($customerResult);
        $customerGroup = new ShopgateCartCustomerGroup();
        $customerGroup->setId($customerData['customers_status']);
        $customerGroups[] = $customerGroup;
        $sgCustomer       = new ShopgateCartCustomer();
        $sgCustomer->setCustomerGroups($customerGroups);

        return $sgCustomer;
    }

    /**
     * validate all items which the cart contains
     *
     * @param ShopgateCart $cart
     *
     * @param int          $customerGroupId
     *
     * @return array
     * @throws ShopgateLibraryException
     */
    private function checkCartItems(ShopgateCart $cart, $customerGroupId)
    {
        $return     = array();
        $itemHelper = new ShopgateItemHelper();
        $itemModel  = new ShopgateItemModel($this->config);
        $itemModel->setLanguageId($this->languageId);
        $itemModel->setStringHelper($this->getHelper(ShopgateObject::HELPER_STRING));
        $itemModel->setModifiedVersion($this->modifiedVersion);

        foreach ($cart->getItems() AS $orderItem) {
            $sgCartItem  = new ShopgateCartItem();
            $sgOrderInfo = $this->jsonDecode(stripslashes($orderItem->getInternalOrderInfo()), true);
            $id          = $itemModel->getProductIdFromOrderItem($orderItem);
            $status      = $this->xtc_get_products_status($id);
            $sgCartItem->setItemNumber($id);
            $sgCartItem->setIsBuyable($status);
            if (!$status) {
                $sgCartItem->setIsBuyable(false);
                $sgCartItem->setQtyBuyable(0);
                $sgCartItem->setStockQuantity(0);
                $sgCartItem->setError(ShopgateLibraryException::CART_ITEM_PRODUCT_NOT_FOUND);
                $return[] = $sgCartItem;
                continue;
            }

            //tax
            $orderItemTaxClassId = xtc_get_tax_class_id($id);
            $orderItemTaxRate    = xtc_get_tax_rate(
                $orderItemTaxClassId, $this->countryId, $this->zoneId
            );

            //price
            $xtcPrice     = new xtcPrice($this->currency["code"], $customerGroupId);
            $priceWithTax = $xtcPrice->xtcGetPrice(
                $id,
                false,
                $orderItem->getQuantity(),
                $orderItemTaxClassId,
                $orderItem->getUnitAmount(),
                1
            );
            $price        = $xtcPrice->xtcGetPrice(
                $id,
                false,
                $orderItem->getQuantity(),
                null,
                $orderItem->getUnitAmount(),
                1
            );

            $sgCartItem->setUnitAmount($price);
            $sgCartItem->setUnitAmountWithTax($priceWithTax);

            $quantity = $itemHelper->getProductQuantity($id, $sgOrderInfo);
            $sgCartItem->setStockQuantity($quantity);
            $sgCartItem->setQtyBuyable($orderItem->getQuantity());

            if (STOCK_CHECK == 'true' && STOCK_ALLOW_CHECKOUT == 'false') {
                if ($quantity <= 0) {
                    $sgCartItem->setIsBuyable(false);
                    $sgCartItem->setQtyBuyable(0);
                    $sgCartItem->setStockQuantity(0);
                    $sgCartItem->setError(ShopgateLibraryException::CART_ITEM_OUT_OF_STOCK);
                    $return[] = $sgCartItem;
                    continue;
                }
                if ($quantity < $orderItem->getQuantity()) {
                    $sgCartItem->setIsBuyable(true);
                    $sgCartItem->setQtyBuyable($quantity);
                    $sgCartItem->setError(ShopgateLibraryException::CART_ITEM_REQUESTED_QUANTITY_NOT_AVAILABLE);
                    $return[] = $sgCartItem;
                    continue;
                }
            }

            $sgCartItem->setOptions(
                $itemModel->getOptionsToProduct(
                    $id, $sgOrderInfo, $orderItemTaxRate
                )
            );
            $sgCartItem->setAttributes($itemModel->getAttributesToProduct($orderItem));

            // not supported
            $sgCartItem->setInputs(array());

            $return[] = $sgCartItem;
        }

        return $return;
    }

    /**
     * get all orders which has been flagged as set int the setting "$this->config->getOrderStatusCanceled()"
     * and send information of this orders to shopgate
     *
     * @param string $message
     * @param int    $errorCount
     */
    protected function cronCancelOrders(&$message, &$errorCount)
    {
        $query = "SELECT `sgo`.`orders_id`, `sgo`.`shopgate_order_number`" .
            " FROM `" . TABLE_SHOPGATE_ORDERS . "` sgo" .
            " INNER JOIN `" . TABLE_ORDERS . "` xto ON (`xto`.`orders_id` = `sgo`.`orders_id`) " .
            " INNER JOIN `" . TABLE_LANGUAGES . "` xtl ON (`xtl`.`directory` = `xto`.`language`) " .
            " WHERE `sgo`.`is_cancellation_sent_to_shopgate` = 0" .
            " AND `xto`.`orders_status` = " . xtc_db_input($this->config->getOrderStatusCanceled()) .
            " AND `xtl`.`code` = '" . xtc_db_input($this->config->getLanguage()) . "';";

        $result = xtc_db_query($query);
        while ($shopgateOrder = xtc_db_fetch_array($result)) {
            try {
                $this->sendOrderCancellation($shopgateOrder['shopgate_order_number'], $this->merchantApi);
                $message .= "full cancellation sent for shopgate order: {$shopgateOrder['shopgate_order_number']}\n";
            } catch (Exception $e) {
                $errorCount++;
                $message .= "Shopgate order number {$shopgateOrder['shopgate_order_number']} error: {$e->getMessage()}\n";
            }
        }
    }

    /**
     * send a cancellation request from an order to shopgate
     *
     * @param int                          $shopgateOrderNumber
     * @param ShopgateMerchantApiInterface $merchantApi
     *
     * @throws ShopgateMerchantApiException
     */
    protected function sendOrderCancellation($shopgateOrderNumber, ShopgateMerchantApiInterface $merchantApi)
    {
        try {
            $merchantApi->cancelOrder($shopgateOrderNumber, true);
        } catch (ShopgateMerchantApiException $e) {
            if ($e->getCode() != ShopgateMerchantApiException::ORDER_ALREADY_CANCELLED) {
                throw $e;
            }
        }
        $updateQuery = 'UPDATE `' . TABLE_SHOPGATE_ORDERS
            . "` SET `is_cancellation_sent_to_shopgate` = 1 WHERE `shopgate_order_number` = {$shopgateOrderNumber}";
        xtc_db_query($updateQuery);
    }

    /**
     * fills the referenced array "$categories" with categories
     * hint: this function is recursive
     *
     * @param int    $parentId
     * @param int    $maxOrder
     * @param string $type
     * @param null   $limit
     * @param null   $offset
     * @param array  $uids
     */
    private function buildCategoriesTree(
        $parentId = 0, $maxOrder = 0, $type = "csv", $limit = null, $offset = null, array $uids = array()
    ) {

        $this->log(
            "Start buldiding Categories tree: parent_id = " . $parentId . "...",
            ShopgateLogger::LOGTYPE_DEBUG
        );

        $qry = "
        SELECT DISTINCT
        c.categories_id,
        c.parent_id,
        c.categories_image,
        c.categories_status,
        c.sort_order,
        cd.categories_name
        FROM " . TABLE_CATEGORIES . " c
        LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.categories_id = cd.categories_id
        AND cd.language_id = $this->languageId)";

        if (!($uids == array())) {
            $qry .= " WHERE c.categories_id IN (" . implode(',', $uids) . ")";
        }

        $qry .= " ORDER BY c.categories_id ASC";

        if (is_numeric($limit) && is_numeric($offset)) {
            $qry .= " LIMIT " . $offset . "," . $limit . "";
        }

        $qry = xtc_db_query($qry);

        while ($item = xtc_db_fetch_array($qry)) {

            $this->log(
                "cheking if category is blacklisted ...",
                ShopgateLogger::LOGTYPE_DEBUG
            );
            /** @var mixed[] $row */
            $row = $this->buildDefaultCategoryRow();

            $row["category_number"] = $item["categories_id"];
            $row["parent_id"]       = (empty($item["parent_id"])
                || ($item['parent_id'] == $item['categories_id'])) ? ""
                : $item["parent_id"];
            $row["category_name"]   = htmlentities(
                $item["categories_name"], ENT_NOQUOTES,
                $this->config->getEncoding()
            );

            if (!empty($item["categories_image"])) {
                $row["url_image"] =
                    HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES . "categories/"
                    . $item["categories_image"];
            }

            if (!empty($item["sort_order"])
                || ((string)$item['sort_order'] === '0')
            ) {
                if ($this->config->getReverseCategoriesSortOrder()) {
                    // reversed means the contrary to ordering system in shopgate - order_index is a priority system - high number = top position
                    // so just taking over the values means reversing the order
                    $row["order_index"] = $item["sort_order"];
                } else {
                    $row["order_index"] = $maxOrder - $item["sort_order"];
                }
            }

            $row["is_active"]    = $item["categories_status"];
            $row['url_deeplink'] = xtc_href_link(
                FILENAME_DEFAULT, xtc_category_link(
                $item['categories_id'], $item['categories_name']
            ), 'NONSSL', false
            );

            if ($type == 'csv') {
                $this->addCategoryRow($row);
            } else {
                $node = new ShopgateCategoryXmlModel($this->config);
                $node->setItem($row);
                $this->addCategoryModel($node->generateData());
            }
        }
    }

    /**
     * Returns a array with all Variations of the Product
     *
     * @param int               $productId
     * @param ShopgateItemModel $itemModel
     *
     * @return array
     */
    private function getVariations(ShopgateItemModel $itemModel, $productId, $tax_rate)
    {

        $this->log("execute _getVariations() ...", ShopgateLogger::LOGTYPE_DEBUG);

        $sg_prod_var = array();
        $query       = xtc_db_query(
            $itemModel->getAttributesToProductQuery($productId, $this->config->getExportOptionAsInputField())
        );

        //        $options = array_pad(array(), 5, "");
        $options = array();

        $i   = -1;
        $old = null;
        while ($variation = xtc_db_fetch_array($query)) {
            // empty option value names are not allowed at Shopgate, so display double dashes instead
            if (trim($variation['products_options_values_name']) == '') {
                $variation['products_options_values_name'] = '--';
            }

            if ($variation["products_options_id"] != $old || is_null($old)) {
                $i++;
                $old = $variation["products_options_id"];
            }
            $options[$i][] = $variation;
        }

        if (empty($options)) {
            return array();
        }

        // Find and rename duplicate option-value names
        foreach ($options as $optionIndex => $singleOption) {
            // Check all option-value names for duplicate names
            foreach ($singleOption as $key => $optionVariation) {
                if (!empty($optionVariation)) {
                    // Compare with following entries
                    $indexNumber = 1;
                    for ($i = $key + 1; $i < count($singleOption); $i++) {
                        if (trim($singleOption[$i]['products_options_values_name']) == trim(
                                $optionVariation['products_options_values_name']
                            )
                        ) {
                            $indexNumber++;
                            $options[$optionIndex][$i]['products_options_values_name'] =
                                trim($singleOption[$i]['products_options_values_name']) . " $indexNumber";
                        }
                    }
                    // Add index 1 to the actual name if duplicate name-entries found
                    if ($indexNumber > 1) {
                        $options[$optionIndex][$key]['products_options_values_name'] .= " 1";

                        // Refresh the working variable for further operation
                        $singleOption = $options[$optionIndex];
                    }
                }
            }
        }

        $countVariations = 1;
        foreach ($options as $option) {
            $countVariations *= count($option);
        }

        if ($countVariations > $this->config->getMaxAttributes()) {
            $this->buildOptions($sg_prod_var, $options, $tax_rate);
            $sg_prod_var["has_options"] = 1;
        } else {
            $this->buildAttributes($sg_prod_var, $options);
            $sg_prod_var["has_options"] = 0;
        }

        return $sg_prod_var;
    }

    /**
     * Build the Productvariations as options
     *
     * @param &array $sg_prod_var
     * @param array $variations
     * @param float $tax_rate
     */
    private function buildOptions(&$sg_prod_var, $variations, $tax_rate)
    {
        $this->log("execute _buildOptions() ...", ShopgateLogger::LOGTYPE_DEBUG);

        $tmp = array();
        $i   = 0;
        foreach ($variations as $_variation) {
            $i++;
            $tmp["option_$i"] =
                $_variation[0]["products_options_id"] . '=' . strip_tags($_variation[0]["products_options_name"]);

            $options = array();
            foreach ($_variation as $option) {
                // Currency and tax must be included here because the data is directly used for the item
                $optionOffsetPrice =
                    $option["options_values_price"] * $this->exchangeRate * (1 + ($tax_rate / 100)); // Include Tax
                $optionOffsetPrice = round($optionOffsetPrice * 100, 0); // get euro-cent

                $field = strip_tags($option["products_options_values_id"]) . "=" . strip_tags(
                        $option["products_options_values_name"]
                    );
                $field .= ($option["options_values_price"] != 0)
                    ? "=>" . $option["price_prefix"] . $optionOffsetPrice
                    : "";

                $options[] = $field;
            }
            $tmp["option_" . $i . "_values"] = implode("||", $options);
        }

        $sg_prod_var = $tmp;
    }

    /**
     * Build the Productvariations recursively
     *
     * @param       $sg_prod_var
     * @param       $variations
     * @param int   $index
     * @param array $baseVar
     */
    private function buildAttributes(&$sg_prod_var, $variations, $index = 0, $baseVar = array())
    {
        $this->log("execute _buildAttributes() ...", ShopgateLogger::LOGTYPE_DEBUG);

        if ($index == 0) {
            // Index 0 sind die Überschriften. Diese müssen als erstes hinzugefügt werden
            for ($i = 0; $i < count($variations); $i++) {
                $sg_prod_var[0]['attribute_' . ($i + 1)] = $variations[$i][0]['products_options_name'];
            }
        }

        foreach ($variations[$index] as $variation) {
            $tmpNewVariation = array();

            // copy all prvious attributes (inclusive the order info)
            if (!empty($baseVar)) {
                for ($i = 1; $i <= 10; $i++) {
                    $keyName = 'attribute_' . $i;
                    if (array_key_exists($keyName, $baseVar)) {
                        $tmpNewVariation[$keyName]               = $baseVar[$keyName];
                        $tmpNewVariation['order_info'][$keyName] = $baseVar['order_info'][$keyName];
                    } else {
                        break;
                    }
                }
            }

            if (count($variations) == 1) {
                // only if 1 dimension
                $tmpNewVariation['item_number'] = $variation['attributes_model'];
            }

            $tmpNewVariation['attribute_' . ($index + 1)]               = $variation['products_options_values_name'];
            $tmpNewVariation['order_info']['attribute_' . ($index + 1)] = array(
                $variation['products_attributes_id'] => array(
                    'options_id'        => $variation['products_options_id'],
                    'options_values_id' => $variation['products_options_values_id'],
                ),
            );

            $tmpNewVariation['stock_quantity'] = $variation['attributes_stock'];
            if (isset($baseVar['stock_quantity']) && $baseVar['stock_quantity'] < $variation['attributes_stock']) {
                $tmpNewVariation['stock_quantity'] = $baseVar['stock_quantity'];
            }

            // Kalkuliere den Preisunterschied (Steuern und Währung werden noch nicht hier berücksichtigt)
            $price = $variation['options_values_price'];
            if ($variation['price_prefix'] == '-') {
                $price = -1 * $price;
            }
            if (empty($baseVar['offset_amount'])) {
                $baseVar['offset_amount'] = 0;
            }
            $tmpNewVariation['offset_amount'] = $baseVar['offset_amount'] + $price;

            // Kalkuliere den Gewichtsunterschied
            $weight = (float)$variation['options_values_weight'];
            if ($variation['weight_prefix'] == '-') {
                $weight = -1 * $weight;
            }
            if (empty($baseVar['offset_weight'])) {
                $baseVar['offset_weight'] = 0;
            }
            $tmpNewVariation['offset_weight'] = $baseVar['offset_weight'] + (double)$weight;

            if ($index < (count($variations) - 1)) {
                // Fahre mit nächstem Attribute fort (mit aktuellem Zwischenattribut als Basis für die Gewicht, Stock und Preisberechnung)
                // Das aktuelle Zwischenattribut enthält das Gesamtgewicht, den Gesamtpreis und den max-Stock, der für weitere Berechnungen notwendig ist
                $this->buildAttributes($sg_prod_var, $variations, $index + 1, $tmpNewVariation);
            } else {
                // Wenn kein Attribut mehr existiert, dieses auf den Stack legen
                $sg_prod_var[] = $tmpNewVariation;
            }
        }
    }

    /**
     * gathers input field data to a product
     *
     * @param                   $productId
     * @param ShopgateItemModel $itemModel
     *
     * @return array|void
     */
    private function getInputFields(ShopgateItemModel $itemModel, $productId)
    {
        $this->log("execute _getInputFields() ...", ShopgateLogger::LOGTYPE_DEBUG);
        $query = xtc_db_query(
            $itemModel->getAttributesInputFieldsToProductsQuery(
                $productId, $this->config->getExportOptionAsInputField()
            )
        );
        $i     = 0;
        $old   = '';
        while ($inputFields = xtc_db_fetch_array($query)) {
            if ($inputFields["products_options_id"] != $old) {
                $i++;
                $old = $inputFields["products_options_id"];
            }
            $inputFieldsAll[$i][] = $inputFields;
        }

        if (empty($inputFieldsAll)) {
            return;
        }

        $sg_product_var = $this->buildInputFields($inputFieldsAll);

        return $sg_product_var;
    }

    /**
     * convert the input field data from the shop system into the Shopgate-specific structure
     *
     * @param $inputFieldsAll
     *
     * @return array
     */
    private function buildInputFields($inputFieldsAll)
    {
        $sg_product_var = array();
        $i              = 0;
        foreach ($inputFieldsAll as $inputField) {
            $i++;

            //            $sg_product_var["has_input_fields"] = 1;
            $sg_product_var["input_field_" . $i . "_number"]     = $inputField[0]['products_options_id'];
            $sg_product_var["input_field_" . $i . "_type"]       = 'text';
            $sg_product_var["input_field_" . $i . "_label"]      = strip_tags($inputField[0]["products_options_name"]);
            $sg_product_var["input_field_" . $i . "_add_amount"] = ($inputField[0]["options_values_price"] != 0)
                ? $inputField[0]["price_prefix"] . round($inputField[0]["options_values_price"], 2)
                : "";
            // keine Angabe möglich
            $sg_product_var["input_field_" . $i . "_infotext"] = '';
            $sg_product_var["input_field_" . $i . "_required"] = 0;
        }

        return $sg_product_var;
    }

    /**
     * Load all Categories of the product and build its category-path
     *
     * The categories are seperated by a =>. The Paths are seperated b< a double-pipe ||
     *
     * Example: kategorie_1=>kategorie_2||other_1=>other_2
     *
     * @param int $productId
     *
     * @return string
     */
    private function getProductPath($productId)
    {
        $this->log("execute _getProductPath() ...", ShopgateLogger::LOGTYPE_DEBUG);

        $catsQry   = "
            SELECT DISTINCT ptc.categories_id
            FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
            INNER JOIN " . TABLE_CATEGORIES . " c ON ptc.categories_id = c.categories_id
            WHERE ptc.products_id = '$productId'
              AND c.categories_status = 1
            ORDER BY products_sorting
        ";
        $catsQuery = xtc_db_query($catsQry);

        $categories = "";
        while ($category = xtc_db_fetch_array($catsQuery)) {
            $cats = xtc_get_category_path($category["categories_id"]);
            $cats = preg_replace("/\_/", ",", $cats);

            $q = "
                SELECT DISTINCT cd.categories_name
                FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd
                WHERE cd.categories_id IN (" . $cats . ")
                    AND cd.language_id = " . $this->languageId . "
                ORDER BY find_in_set(cd.categories_id, '$cats')
            ";

            $q    = xtc_db_query($q);
            $cats = "";
            while ($cd = xtc_db_fetch_array($q)) {
                if (!empty($cats)) {
                    $cats .= "=>";
                }
                $cats .= $cd["categories_name"];
            }
            if (!empty($categories)) {
                $categories .= "||";
            }
            $categories .= $cats;
        }

        return $categories;
    }

    /**
     * generates a string containing the image urls to an product separated by the delimiter ||
     *
     * @param ShopgateItemModel $itemModel
     * @param string            $product
     *
     * @return array
     */
    private function getProductsImages($itemModel, $product)
    {
        $this->log("execute _getProductImages() ...", ShopgateLogger::LOGTYPE_DEBUG);
        $images = $itemModel->generateImageUrls($product);

        return implode("||", $images);
    }

    /**
     * generates a string containing special offer data to products, separated by the delimiter ||
     *
     * @param $product
     * @param $tax_rate
     *
     * @return string
     */
    private function getPackages($product, $tax_rate)
    {
        $customerStatusId = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;

        $qry = "
            SELECT *
            FROM " . TABLE_PERSONAL_OFFERS_BY . "$customerStatusId
            WHERE products_id = '" . $product["products_id"] . "'
              AND quantity > 1
            ORDER BY quantity
        ";

        $specialOffers  = array();
        $_specialOffers = xtc_db_query($qry);

        while ($specialOffer = xtc_db_fetch_array($_specialOffers)) {
            $specialOffers[] = implode(
                "=>", array(
                        "qty"            => $specialOffer["quantity"],
                        "personal_offer" => round($specialOffer["personal_offer"] * (1 + ($tax_rate / 100)), 2),
                    )
            );
        }

        return implode("||", $specialOffers);
    }

    /**
     * creates a guest user in the shop system
     *
     * @param ShopgateOrder $order
     *
     * @return array
     */
    private function createGuestUser(ShopgateOrder $order)
    {
        //        $order = new ShopgateOrder();
        $address        = $order->getInvoiceAddress();
        $customerStatus = DEFAULT_CUSTOMERS_STATUS_ID_GUEST;

        $customer                                 = array();
        $customer["customers_vat_id_status"]      = 0;
        $customer["customers_status"]             = $customerStatus;
        $customer["customers_gender"]             = $address->getGender();
        $customer["customers_firstname"]          = $address->getFirstName();
        $customer["customers_lastname"]           = $address->getLastName();
        $customer["customers_email_address"]      = $order->getMail();
        $customer["customers_default_address_id"] = "";
        $customer["customers_telephone"]          = $order->getPhone();
        $customer["customers_fax"]                = "";
        $customer["customers_newsletter"]         = 0;
        $customer["customers_newsletter_mode"]    = 0;
        $customer["member_flag"]                  = 0;
        $customer["delete_user"]                  = 1;
        $customer["account_type"]                 = 1;
        $customer["refferers_id"]                 = 0;
        $customer["customers_date_added"]         = date('Y-m-d H:i:s');
        $customer["customers_last_modified"]      = date('Y-m-d H:i:s');

        xtc_db_perform(TABLE_CUSTOMERS, $customer);
        $customerId = xtc_db_insert_id();

        $qry     = "SELECT countries_id FROM " . TABLE_COUNTRIES
            . " WHERE UPPER(countries_iso_code_2) = UPPER('" . $address->getCountry() . "')";
        $qry     = xtc_db_query($qry);
        $country = xtc_db_fetch_array($qry);
        if (empty($country)) {
            $country = array(
                'countries_id' => 81,
            );
        }

        $qry  = "SELECT zone_id, zone_name FROM " . TABLE_ZONES
            . " WHERE zone_country_id = {$country['countries_id']} AND zone_code = '"
            . ShopgateXtcMapper::getXtcStateCode($address->getState()) . "'";
        $qry  = xtc_db_query($qry);
        $zone = xtc_db_fetch_array($qry);
        if (empty($zone)) {
            $zone = array(
                'zone_id'   => null,
                'zone_name' => $address->getState(),
            );
        }

        $_address = array(
            "customers_id"          => $customerId,
            "entry_gender"          => $address->getGender(),
            "entry_company"         => $address->getCompany(),
            "entry_firstname"       => $address->getFirstName(),
            "entry_lastname"        => $address->getLastName(),
            "entry_street_address"  => $address->getStreet1() . (strlen($address->getStreet2()) > 0 ? (' '
                    . $address->getStreet2()) : ''),
            "entry_suburb"          => "",
            "entry_postcode"        => $address->getZipcode(),
            "entry_city"            => $address->getCity(),
            "entry_state"           => $zone['zone_name'],
            "entry_country_id"      => $country["countries_id"],
            "entry_zone_id"         => $zone['zone_id'],
            "address_date_added"    => date('Y-m-d H:i:s'),
            "address_last_modified" => date('Y-m-d H:i:s'),
        );
        xtc_db_perform(TABLE_ADDRESS_BOOK, $_address);
        $addressId = xtc_db_insert_id();

        $customer = array(
            "customers_default_address_id" => $addressId
        );
        xtc_db_perform(TABLE_CUSTOMERS, $customer, "update", "customers_id = $customerId");

        $_info = array(
            "customers_info_id"                         => $customerId,
            "customers_info_date_of_last_logon"         => date('Y-m-d H:i:s'),
            "customers_info_number_of_logons"           => '1',
            "customers_info_date_account_created"       => date('Y-m-d H:i:s'),
            "customers_info_date_account_last_modified" => date('Y-m-d H:i:s'),
            "global_product_notifications"              => 0
        );
        xtc_db_perform(TABLE_CUSTOMERS_INFO, $_info);

        $customerMemo                 = array();
        $customerMemo["customers_id"] = $customerId;
        $customerMemo["memo_date"]    = date('Y-m-d');
        $customerMemo["memo_title"]   = "Shopgate - Account angelegt";
        $customerMemo["memo_text"]    = "Account wurde von Shopgate angelegt";
        $customerMemo["poster_id"]    = null;
        xtc_db_perform("customers_memo", $customerMemo);

        $result   = xtc_db_query("SELECT * FROM " . TABLE_CUSTOMERS . " WHERE customers_id = " . $customerId);
        $customer = xtc_db_fetch_array($result);

        return $customer;
    }

    /**
     * get the address format id from the database, regarding the iso2 code and if set the country id
     *
     * @param string $isoCode2
     * @param null   $countryId
     *
     * @return mixed
     */
    private function getAddressFormatId($isoCode2 = 'DE', $countryId = null)
    {
        $isoCode2 = strtoupper($isoCode2);
        if (!empty($countryId)) {
            $qry = "
                SELECT c.address_format_id
                FROM " . TABLE_COUNTRIES . " c
                WHERE c.countries_id = '$countryId'
            ";
        } else {
            $qry = "
                SELECT c.address_format_id
                FROM " . TABLE_COUNTRIES . " c
                WHERE UPPER(c.countries_iso_code_2) = '$isoCode2'
            ";
        }

        $result = xtc_db_query($qry);
        $item   = xtc_db_fetch_array($result);

        return $item["address_format_id"];
    }

    /**
     * inserts the status to an order into the database
     *
     * @param ShopgateOrder $order
     * @param               $dbOrderId
     * @param               $currentOrderStatus
     */
    private function insertStatusHistory(ShopgateOrder $order, $dbOrderId, &$currentOrderStatus)
    {
        ///////////////////////////////////////////////////////////////////////
        // Speicher Kommentare zur Bestellung in der Historie
        ///////////////////////////////////////////////////////////////////////

        $comment = "";
        if ($order->getIsTest()) {
            $comment .= "#### DIES IST EINE TESTBESTELLUNG ####\n";
        }
        $comment .= "Bestellung durch Shopgate hinzugefügt.";
        $comment .= "\nBestellnummer: " . $order->getOrderNumber();

        $paymentTransactionNumber = $order->getPaymentTransactionNumber();
        if (!empty($paymentTransactionNumber)) {
            $comment .= "\nPayment-Transaktionsnummer: " . $paymentTransactionNumber . "\n";
        }

        if ($order->getIsShippingBlocked() == 0) {
            $comment .= "\nHinweis: Der Versand der Bestellung ist bei Shopgate nicht blockiert!";
        } else {
            $comment .= "\nHinweis: Der Versand der Bestellung ist bei Shopgate blockiert!";
            $currentOrderStatus = $this->config->getOrderStatusShippingBlocked();
        }
        if ($order->getIsCustomerInvoiceBlocked()) {
            $comment .= "\nHinweis: Für diese Bestellung darf keine Rechnung versendet werden!";
        }

        $comment = $this->stringFromUtf8($comment, $this->config->getEncoding());

        $orderModel = new ShopgateOrderModel();
        $orderModel->setOrderId($dbOrderId);
        $orderModel->saveHistory($currentOrderStatus, $comment);
    }

    /**
     * stores the payment information to an order in the database
     *
     * @param ShopgateOrder $order
     * @param               $dbOrderId
     * @param               $currentOrderStatus
     */
    private function setOrderPayment(ShopgateOrder $order, $dbOrderId, &$currentOrderStatus)
    {
        $payment      = $order->getPaymentMethod();
        $paymentInfos = $order->getPaymentInfos();

        $orderData = array();

        $histories = array();

        $paymentName         = '';
        $paymentWasMapped    = false;
        $paymentMapping      = array();

        $paymentMappingStrings = explode(';', $this->config->getPaymentNameMapping());
        foreach ($paymentMappingStrings as $paymentMappingString) {
            $paymentMappingArray = explode('=', $paymentMappingString);
            if (isset($paymentMappingArray[1])) {
                $paymentMapping[$paymentMappingArray[0]] = $paymentMappingArray[1];
            }
        }
        if (isset($paymentMapping[$payment])) {
            $comments = $this->stringFromUtf8(
                "Zahlungsweise '" . $payment . "' durch '" . $paymentMapping[$payment] . "' ersetzt",
                $this->config->getEncoding()
            );
            $histories[] = array(
                "orders_id"         => $dbOrderId,
                "orders_status_id"  => $currentOrderStatus,
                "date_added"        => date('Y-m-d H:i:s'),
                "customer_notified" => false,
                "comments"          => xtc_db_prepare_input($comments)
            );
            $paymentName = $paymentMapping[$payment];
            $paymentWasMapped = true;
        }

        switch ($payment) {
            case ShopgateOrder::SHOPGATE:
                $orderData["payment_method"] = ($paymentWasMapped) ? $paymentName : "shopgate";
                $orderData["payment_class"]  = "shopgate";

                break;
            case ShopgateOrder::PREPAY:
                $orderData["payment_method"] = ($paymentWasMapped) ? $paymentName : "eustandardtransfer";
                $orderData["payment_class"]  = "eustandardtransfer";

                if (!$order->getIsPaid()) {
                    $comments = $this->stringFromUtf8(
                        "Der Kunde wurde angewiesen Ihnen das Geld mit dem Verwendungszweck \"",
                        $this->config->getEncoding()
                    );
                    $comments .= $paymentInfos['purpose'];
                    $comments .= $this->stringFromUtf8(
                        "\" auf Ihr Bankkonto zu überweisen", $this->config->getEncoding()
                    );

                    // Order is not paid yet
                    $histories[] = array(
                        "orders_id"         => $dbOrderId,
                        "orders_status_id"  => $currentOrderStatus,
                        "date_added"        => date('Y-m-d H:i:s'),
                        "customer_notified" => false,
                        "comments"          => ShopgateWrapper::db_prepare_input($comments)
                    );
                }

                break;
            case ShopgateOrder::INVOICE:
                $orderData["payment_method"] = ($paymentWasMapped) ? $paymentName : "invoice";
                $orderData["payment_class"]  = "invoice";

                break;
            case ShopgateOrder::COD:
                $orderData["payment_method"] = ($paymentWasMapped) ? $paymentName : "cod";
                $orderData["payment_class"]  = "cod";

                break;
            case ShopgateOrder::DEBIT:
                $orderData["payment_method"] = ($paymentWasMapped) ? $paymentName : "banktransfer";
                $orderData["payment_class"]  = "banktransfer";

                $banktransferData                          = array();
                $banktransferData["orders_id"]             = $dbOrderId;
                $banktransferData["banktransfer_owner"]    = $paymentInfos["bank_account_holder"];
                $banktransferData["banktransfer_number"]   = $paymentInfos["bank_account_number"];
                $banktransferData["banktransfer_bankname"] = $paymentInfos["bank_name"];
                $banktransferData["banktransfer_blz"]      = $paymentInfos["bank_code"];
                $banktransferData["banktransfer_status"]   = "0";
                $banktransferData["banktransfer_prz"]      = $dbOrderId;
                $banktransferData["banktransfer_fax"]      = null;
                xtc_db_perform("banktransfer", $banktransferData);

                $comments = $this->stringFromUtf8(
                    "Sie müssen nun den Geldbetrag per Lastschrift von dem Bankkonto des Kunden abbuchen: \n\n",
                    $this->config->getEncoding()
                );
                $comments .= $this->createPaymentInfos($paymentInfos, $dbOrderId, $currentOrderStatus, false);

                $histories[] = array(
                    "orders_id"         => $dbOrderId,
                    "orders_status_id"  => $currentOrderStatus,
                    "date_added"        => date('Y-m-d H:i:s'),
                    "customer_notified" => false,
                    "comments"          => ShopgateWrapper::db_prepare_input($comments)
                );

                break;
            case ShopgateOrder::PAYPAL:

                $paymentModulesInstalledQuery  = 'SELECT c.configuration_value AS cv 
                                                    FROM configuration AS c 
                                                    WHERE c.configuration_key LIKE "%MODULE_PAYMENT_INSTALLED%" LIMIT 1;';
                $paymentModulesInstalledResult = xtc_db_query($paymentModulesInstalledQuery);
                $installedPaymentModules       = xtc_db_fetch_array($paymentModulesInstalledResult);

                if (strpos($installedPaymentModules["cv"], "paypal_ipn") !== false) {
                    $orderData["payment_method"] = ($paymentWasMapped) ? $paymentName : "paypal_ipn";
                    $orderData["payment_class"]  = "paypal_ipn";
                } else {
                    $orderData["payment_method"] = ($paymentWasMapped) ? $paymentName : "paypal";
                    $orderData["payment_class"]  = "paypal";
                }

                // Save paymentinfos in history
                $histories[] = $this->createPaymentInfos($paymentInfos, $dbOrderId, $currentOrderStatus);

                break;
            default:
                $orderData["payment_method"] = ($paymentWasMapped) ? $paymentName : "mobile_payment";
                $orderData["payment_class"]  = "shopgate";

                // Save paymentinfos in history
                $histories[] = $this->createPaymentInfos($paymentInfos, $dbOrderId, $currentOrderStatus);

                break;
        }

        foreach ($histories as $history) {
            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $history);
        }

        xtc_db_perform(TABLE_ORDERS, $orderData, "update", "orders_id = {$dbOrderId}");
    }

    /**
     * Parse the paymentInfo - array and get as output a array or a string
     *
     * @param Array   $paymentInfos
     * @param Integer $dbOrderId
     * @param Integer $currentOrderStatus
     *
     * @return mixed History-Array or String
     */
    private function createPaymentInfos($paymentInfos, $dbOrderId, $currentOrderStatus, $asArray = true)
    {
        $paymentInformation = '';
        foreach ($paymentInfos as $key => $value) {
            $paymentInformation .= $key . ': ' . $value . "\n";
        }

        if ($asArray) {
            return array(
                "orders_id"         => $dbOrderId,
                "orders_status_id"  => $currentOrderStatus,
                "date_added"        => date('Y-m-d H:i:s'),
                "customer_notified" => false,
                "comments"          => ShopgateWrapper::db_prepare_input($paymentInformation)
            );
        } else {
            return $paymentInformation;
        }
    }

    /**
     * inserts all items to an order into the database
     *
     * @param ShopgateOrder       $order
     * @param                     $dbOrderId
     * @param                     $currentOrderStatus
     * @param ShopgateCouponModel $couponModel
     */
    private function insertOrderItems(
        ShopgateOrder $order, $dbOrderId, &$currentOrderStatus, ShopgateCouponModel $couponModel
    ) {
        ///////////////////////////////////////////////////////////////////////
        // Speichert die Produkte
        ///////////////////////////////////////////////////////////////////////
        $errors = '';
        foreach ($order->getItems() as $orderItem) {

            $orderInfo = stripslashes($orderItem->getInternalOrderInfo());
            $orderInfo = $this->jsonDecode($orderInfo, true);

            $item_number = $orderItem->getItemNumber();
            if (isset($orderInfo["base_item_number"])) {
                $item_number = $orderInfo["base_item_number"];
            }

            $this->log('db: get product ', ShopgateLogger::LOGTYPE_DEBUG);

            $qry = xtc_db_query(
                "SELECT * FROM " . TABLE_PRODUCTS . " WHERE"
                . " products_id = '" . $item_number . "'"
                . " LIMIT 1"
            );

            $dbProduct = xtc_db_fetch_array($qry);
            if (empty($dbProduct) && ($item_number == 'COUPON' || $item_number == 'PAYMENT_FEE')) {
                $this->log('product is COUPON or PAYMENTFEE', ShopgateLogger::LOGTYPE_DEBUG);

                // workaround for shopgate coupons
                $dbProduct                   = array();
                $dbProduct['products_id']    = 0;
                $dbProduct['products_model'] = $item_number;
            } else {
                if (empty($dbProduct)) {
                    $this->log('no product found', ShopgateLogger::LOGTYPE_DEBUG);

                    $this->log(
                        ShopgateLibraryException::buildLogMessageFor(
                            ShopgateLibraryException::PLUGIN_ORDER_ITEM_NOT_FOUND,
                            'Shopgate-Order-Number: ' . $order->getOrderNumber() . ', DB-Order-Id: ' . $dbOrderId
                            . '; item (item_number: ' . $orderItem->getItemNumber() . '). The item will be skipped.'
                        )
                    );
                    $errors .= "\nItem (item_number: " . $item_number
                        . ") can not be found in your shoppingsystem. Please contact Shopgate. The item will be skipped.";

                    $dbProduct['products_id']    = 0;
                    $dbProduct['products_model'] = $item_number;
                }
            }

            $this->log('db: orders_products', ShopgateLogger::LOGTYPE_DEBUG);

            $productData = array(
                "orders_id"              => $dbOrderId,
                "products_model"         => $dbProduct["products_model"],
                "products_id"            => $item_number,
                "products_name"          => ShopgateWrapper::db_prepare_input($orderItem->getName()),
                "products_price"         => $orderItem->getUnitAmountWithTax(),
                "products_discount_made" => 0,
                "final_price"            => $orderItem->getQuantity() * ($orderItem->getUnitAmountWithTax()),
                "products_shipping_time" => "",
                "products_tax"           => $orderItem->getTaxPercent(),
                "products_quantity"      => $orderItem->getQuantity(),
                "allow_tax"              => 1,
            );

            xtc_db_perform(TABLE_ORDERS_PRODUCTS, $productData);
            $productsOrderId = xtc_db_insert_id();

            $qry    = "SHOW FIELDS FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES;
            $result = xtc_db_query($qry);
            while ($tmpData = xtc_db_fetch_array($result)) {
                $titleKeyNames                  = array_keys($tmpData);
                $fieldName                      = $tmpData[$titleKeyNames[0]];
                $tblAttributeFields[$fieldName] = $fieldName;
            }

            $options = $orderItem->getOptions();
            if (!empty($options)) {
                $this->log('process options', ShopgateLogger::LOGTYPE_DEBUG);
                foreach ($options as $option) {
                    $attribute_model  = $option->getValueNumber();
                    $attribute_number = $option->getOptionNumber();

                    $this->log('db: get attributes', ShopgateLogger::LOGTYPE_DEBUG);

                    // Hole das Attribut aus der Datenbank
                    $qry = "
                        SELECT
                            po.products_options_name,
                            pov.products_options_values_name,
                            pa.options_values_price,
                            pa.price_prefix
                        FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                        INNER JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON pa.options_id = po.products_options_id AND po.language_id = $this->languageId
                        INNER JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povtpo ON povtpo.products_options_id = po.products_options_id
                        INNER JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON (povtpo.products_options_values_id = pov.products_options_values_id AND pa.options_values_id = pov.products_options_values_id AND pov.language_id = $this->languageId)
                        WHERE pa.products_id = '" . $dbProduct["products_id"] . "'
                        " . (!empty($attribute_number)
                            ? "AND pa.options_id = '{$attribute_number}'
                        "
                            : "") .
                        "AND pa.options_values_id = '{$attribute_model}'
                        LIMIT 1
                    ";

                    $qry         = xtc_db_query($qry);
                    $dbAttribute = xtc_db_fetch_array($qry);
                    if (empty($dbAttribute)) {
                        continue;
                    } //Fehler

                    $this->log('db: save order product attributes', ShopgateLogger::LOGTYPE_DEBUG);

                    $productAttributeData = array(
                        "orders_id"               => $dbOrderId,
                        "orders_products_id"      => $productsOrderId,
                        "products_options"        => $dbAttribute['products_options_name'],
                        "products_options_values" => $dbAttribute["products_options_values_name"],
                        "options_values_price"    => $dbAttribute["options_values_price"],
                        "price_prefix"            => $dbAttribute["price_prefix"],
                    );
                    // check if the optional fields are available and set them if so
                    $optionalAttributeFields = array(
                        'orders_products_options_id'        => $attribute_number,
                        'orders_products_options_values_id' => $attribute_model,
                    );
                    foreach ($optionalAttributeFields as $fieldName => $value) {
                        if (!empty($tblAttributeFields[$fieldName])) {
                            $productAttributeData[$fieldName] = $value;
                        }
                    }
                    xtc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $productAttributeData);
                }
            } else {

                $this->log('attributes?', ShopgateLogger::LOGTYPE_DEBUG);

                for ($i = 1; $i <= 10; $i++) {
                    if (!isset($orderInfo["attribute_$i"])) {
                        break;
                    }
                    $tmpAttr          = $orderInfo["attribute_$i"];
                    $attribute_number = "";
                    // Code for support of the old internal_order_info structure
                    if (!is_array($tmpAttr)) {
                        $attribute_model = $tmpAttr;
                    } else {
                        // Den ersten und einzigen key nutzen (zur Sicherheit auf den start des Arrays setzen)
                        reset($tmpAttr);
                        $attribute_number = $tmpAttr[key($tmpAttr)]['options_id'];
                        $attribute_model  = $tmpAttr[key($tmpAttr)]['options_values_id'];
                    }

                    $this->log('db: get attribute', ShopgateLogger::LOGTYPE_DEBUG);

                    // Hole das Attribut aus der Datenbank
                    $qry = "
                        SELECT
                            po.products_options_name,
                            pov.products_options_values_name,
                            pa.options_values_price,
                            pa.price_prefix "
                        . " FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa "
                        . " INNER JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON pa.options_id = po.products_options_id AND po.language_id = $this->languageId
                        INNER JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " povtpo ON povtpo.products_options_id = po.products_options_id
                        INNER JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON (povtpo.products_options_values_id = pov.products_options_values_id AND pa.options_values_id = pov.products_options_values_id AND pov.language_id = $this->languageId)
                        WHERE pa.products_id = '" . $dbProduct["products_id"] . "'
                        " .
                        // Still support the old internal_order_info structure
                        (!empty($attribute_id)
                            ? "AND pa.products_attributes_id = '" . $attribute_model . "'
                        "
                            : "AND pa.options_id = '{$attribute_number}'
                        AND pa.options_values_id = '{$attribute_model}'
                        ") .
                        "LIMIT 1
                    ";

                    $qry         = xtc_db_query($qry);
                    $dbAttribute = xtc_db_fetch_array($qry);
                    if (empty($dbAttribute)) {
                        continue;
                    } //Fehler

                    $this->log('db: save order product attributes', ShopgateLogger::LOGTYPE_DEBUG);

                    $productAttributeData = array(
                        "orders_id"               => $dbOrderId,
                        "orders_products_id"      => $productsOrderId,
                        "products_options"        => $dbAttribute["products_options_name"],
                        "products_options_values" => $dbAttribute["products_options_values_name"],
                        "options_values_price"    => $dbAttribute["options_values_price"],
                        "price_prefix"            => $dbAttribute["price_prefix"],
                    );

                    // check if the optional fields are available and set them if so
                    $optionalAttributeFields = array(
                        'orders_products_options_id'        => $attribute_number,
                        'orders_products_options_values_id' => $attribute_model,
                    );
                    foreach ($optionalAttributeFields as $fieldName => $value) {
                        if (!empty($tblAttributeFields[$fieldName])) {
                            $productAttributeData[$fieldName] = $value;
                        }
                    }

                    xtc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $productAttributeData);
                    //        $query = 'select configuration_value from `configuration` WHERE configuration_key = "DOWNLOAD_ENABLED" LIMIT 1';
                    //$result = $qry = xtc_db_query($qry);
                    //$download = xtc_db_fetch_array($qry);
                    if (DOWNLOAD_ENABLED == 'true') {

                        $query =
                            " SELECT pad.products_attributes_maxdays,    pad.products_attributes_maxcount,    pad.products_attributes_filename  FROM "
                            . TABLE_PRODUCTS_ATTRIBUTES . " pa "
                            . " LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD
                            . " pad ON pa.products_attributes_id=pad.products_attributes_id "
                            . " WHERE  pa.options_id = '{$attribute_number}' AND pa.options_values_id = '{$attribute_model}' AND pa.products_id = '"
                            . $dbProduct["products_id"] . "'";
                        $this->log('query : ' . $query, ShopgateLogger::LOGTYPE_DEBUG);
                        $result  = xtc_db_query($query);
                        $dlEntry = xtc_db_fetch_array($result);

                        if (isset ($dlEntry['products_attributes_filename'])
                            && xtc_not_null(
                                $dlEntry['products_attributes_filename']
                            )
                            && !empty($dlEntry['products_attributes_filename'])
                        ) {
                            $sql_data_array = array(
                                'orders_id'                => $dbOrderId,
                                'orders_products_id'       => $productsOrderId,
                                'orders_products_filename' => $dlEntry['products_attributes_filename'],
                                'download_maxdays'         => $dlEntry['products_attributes_maxdays'],
                                'download_count'           => $dlEntry['products_attributes_maxcount']
                            );
                            xtc_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                        }
                    }
                }
            }
            $inputFields = $orderItem->getInputs();
            if (!empty($inputFields)) {
                foreach ($inputFields as $inputField) {
                    $price       = ($inputField->getAdditionalAmountWithTax() < 0)
                        ? ($inputField->getAdditionalAmountWithTax() * -1)
                        : $inputField->getAdditionalAmountWithTax();
                    $pricePrefix = ($inputField->getAdditionalAmountWithTax() < 0)
                        ? '-'
                        : '+';

                    $data                            = array();
                    $data["orders_id"]               = $dbOrderId;
                    $data["orders_products_id"]      = $productsOrderId;
                    $data["products_options_values"] = $inputField->getUserInput();
                    $data["products_options"]        = $inputField->getLabel();
                    $data["products_options_values"] = $inputField->getUserInput();
                    $data["options_values_price"]    = $price;
                    $data["price_prefix"]            = $pricePrefix;

                    xtc_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $data);
                }
            }
        }

        $coupons = $order->getExternalCoupons();
        if (!empty($coupons)) {
            foreach ($coupons as $coupon) {
                $couponModel->redeemCoupon($coupon, $order->getExternalCustomerId());
            }
        }

        $this->log('method: updateItemsStock', ShopgateLogger::LOGTYPE_DEBUG);
        $this->updateItemsStock($order);

        if (!empty($errors)) {
            $this->log('db: save errors in history', ShopgateLogger::LOGTYPE_DEBUG);
            $comments = $this->stringFromUtf8(
                'Es sind Fehler beim Importieren der Bestellung aufgetreten: ', $this->config->getEncoding()
            );
            $comments .= $errors;

            $history = array(
                "orders_id"         => $dbOrderId,
                "orders_status_id"  => $currentOrderStatus,
                "date_added"        => date("Y-m-d H:i:s", time() - 5),
                // "-5" Damit diese Meldung als erstes oben angezeigt wird
                "customer_notified" => false,
                "comments"          => ShopgateWrapper::db_prepare_input($comments),
            );

            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $history);
        }
    }

    /**
     * updates the stock value of items to an order in the database
     *
     * @param ShopgateOrder $order
     */
    private function updateItemsStock(ShopgateOrder $order)
    {
        foreach ($order->getItems() as $item) {
            // Skip "coupon" and "payment_fee" items
            if ($item->getItemNumber() == 'COUPON' || $item->getItemNumber() == 'PAYMENT_FEE') {
                continue;
            }

            // Attribute ids are set inside the internal order info
            $internalOrderInfo = $this->jsonDecode(stripslashes($item->getInternalOrderInfo()), true);

            $usesProductsAttributes = false;

            // Get id (parent id for child products)
            $productId = $item->getItemNumber();
            if (!empty($internalOrderInfo['base_item_number'])) {
                $productId              = $internalOrderInfo['base_item_number'];
                $usesProductsAttributes = true;
            }

            $itemOptions = $item->getOptions();
            if (!empty($itemOptions)) {
                $usesProductsAttributes = true;
            }

            // Update products stock if reduction enabled
            if (STOCK_LIMITED == 'true') {
                $qry = "UPDATE `" . TABLE_PRODUCTS . "` AS `p`    
                            SET `p`.`products_quantity` = `p`.`products_quantity` - {$item->getQuantity()}
                        WHERE `p`.`products_id` = '{$productId}';";
                xtc_db_query($qry);

                $stock_query  = xtc_db_query(
                    "SELECT products_quantity FROM " . TABLE_PRODUCTS . " WHERE products_id = '" . $productId . "'"
                );
                $stock_values = xtc_db_fetch_array($stock_query);
                $stock_left   = $stock_values['products_quantity'];

                // Deactivate product if checkout is not allowed and the stock level reaches zero
                if (STOCK_ALLOW_CHECKOUT == 'false') {
                    // commerce:seo has an additional constant that tells if the product may be deactivated (STOCK_ALLOW_CHECKOUT_DEACTIVATE)
                    if ($stock_left < 1
                        && STOCK_CHECKOUT_UPDATE_PRODUCTS_STATUS == 'true'
                    ) { // don't update if defined and not true
                        $qry = "
                            UPDATE `" . TABLE_PRODUCTS . "` AS `p`
                                SET `p`.`products_status` = 0
                            WHERE `p`.`products_id` = '{$productId}' AND `p`.`products_quantity` <= 0
                        ;";
                        xtc_db_query($qry);
                    }
                }
            }

            // Attribute items also need to be reduced in stock
            if ($usesProductsAttributes) {
                // Build additional SQL snippets to update the attributes stock (not using the products_attributes_id because they all change on each update of any attribute in the backend)
                $attributeSQLQueryParts = array();
                if (!empty($internalOrderInfo['base_item_number'])) {
                    for ($i = 1; $i <= 10; $i++) {
                        if (!empty($internalOrderInfo["attribute_{$i}"])) {
                            $tmpAttr = $internalOrderInfo["attribute_{$i}"];
                            if (!is_array($tmpAttr)) {
                                $attributeSQLQueryParts[] = " ATTRIBUTES_ID='{$tmpAttr}'";
                            } else {
                                // Only the first element is relevant since there can only be one per attribute-number
                                reset($tmpAttr);
                                $attributeSQLQueryParts[] = 'OPTIONS_ID=\'' . $tmpAttr[key($tmpAttr)]['options_id']
                                    . '\' AND OPTIONS_VALUES_ID=\'' . $tmpAttr[key($tmpAttr)]['options_values_id']
                                    . '\'';
                            }
                        }
                    }
                } else {
                    // Attributes was exported as options
                    foreach ($itemOptions as $itemOption) {
                        $attributeSQLQueryParts[] =
                            'OPTIONS_ID=\'' . $itemOption->getOptionNumber() . '\' AND OPTIONS_VALUES_ID=\''
                            . $itemOption->getValueNumber() . '\'';
                    }
                }
                if (!empty($attributeSQLQueryParts)) {
                    // Attribute stock is ALWAYS reduced (no matter what is set as STOCK_LIMITED or the other constants)!
                    $attributeSQLConditionSnippet = '(' . str_replace(
                            array('OPTIONS_ID', 'OPTIONS_VALUES_ID', 'ATTRIBUTES_ID'),
                            array('`pa`.`options_id`', '`pa`.`options_values_id`', '`pa`.`products_attributes_id`'),
                            implode(') OR (', $attributeSQLQueryParts)
                        ) . ')';

                    // Update attributes stock
                    $qry = "
                        UPDATE `" . TABLE_PRODUCTS_ATTRIBUTES . "` AS `pa`
                            SET `pa`.`attributes_stock` = `pa`.`attributes_stock` - {$item->getQuantity()}
                        WHERE `pa`.`products_id` = '{$productId}'
                            AND ({$attributeSQLConditionSnippet})
                    ;";
                    xtc_db_query($qry);
                }
            }

            // Specials stock and active status
            if (!empty($internalOrderInfo['is_special_price'])) {
                // Always update specials quantity if it is a special
                $qry = "
                    UPDATE `" . TABLE_SPECIALS . "` AS `s`
                        SET `s`.`specials_quantity` = `s`.`specials_quantity` - {$item->getQuantity()}
                    WHERE `s`.`products_id` = '{$productId}'
                ;";
                xtc_db_query($qry);

                $reduceQuantitySqlSnippet = '';
                if (STOCK_CHECK == 'true') {
                    // only if stock check is active we have to deactivate specials
                    $reduceQuantitySqlSnippet = " OR `s`.specials_quantity <= 0 AND `s`.`products_id` = '{$productId}'";
                }

                // Always deactivate specials that have turned to a value equal to or less than zero and deactivate all specials that are expired
                $qry = "
                    UPDATE `" . TABLE_SPECIALS . "` AS `s`
                        SET `s`.`status` = 0
                    WHERE
                        `s`.`status` != 0
                        AND
                            (`s`.`expires_date` < NOW() AND `s`.`expires_date` != '0000-00-00 00:00:00' AND `s`.`expires_date` IS NOT NULL
                        " . $reduceQuantitySqlSnippet . ")
                ;";
                xtc_db_query($qry);
            }
        }
    }

    /**
     * inserts the total amounts to an order into the database
     *
     * @param ShopgateOrder       $order
     * @param int                 $dbOrderId
     * @param ShopgateCouponModel $couponModel
     */
    private function insertOrderTotal(ShopgateOrder $order, $dbOrderId, ShopgateCouponModel $couponModel)
    {
        ///////////////////////////////////////////////////////////////////////
        // Speicher den Gesamtbetrag
        ///////////////////////////////////////////////////////////////////////

        $amountWithTax     = $order->getAmountComplete();
        $taxes             = $this->getOrderTaxes($order);
        $xtPrice           = new xtcPrice($this->currency["code"], DEFAULT_CUSTOMERS_STATUS_ID_GUEST);
        $shippingCosts     = $order->getAmountShipping();
        $shippingInfo      = $order->getShippingInfos();
        $shippingSortOrder = MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER;
        $this->log('_insertOrderTotal(): add subtotal', ShopgateLogger::LOGTYPE_DEBUG);

        $ordersTotal               = array();
        $ordersTotal["orders_id"]  = $dbOrderId;
        $ordersTotal["title"]      =
            ShopgateWrapper::db_prepare_input(MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_SUBTOTAL . ":");
        $ordersTotal["text"]       = $xtPrice->xtcFormat($order->getAmountItems(), true);
        $ordersTotal["value"]      = $order->getAmountItems();
        $ordersTotal["class"]      = "ot_subtotal";
        $ordersTotal["sort_order"] = MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER;
        xtc_db_perform(TABLE_ORDERS_TOTAL, $ordersTotal);
        $this->log('_insertOrderTotal(): add shipping costs total', ShopgateLogger::LOGTYPE_DEBUG);

        $couponAmount = 0;
        $coupons      = $order->getExternalCoupons();
        if (!empty($coupons)) {
            foreach ($coupons as $coupon) {
                $couponAmount += $couponModel->insertOrderTotal($dbOrderId, $coupon);
            }
        }

        $ordersTotal               = array();
        $ordersTotal["orders_id"]  = $dbOrderId;
        $ordersTotal["title"]      = ShopgateWrapper::db_prepare_input(
            MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_SHIPPING . ($shippingInfo && $shippingInfo->getDisplayName()
                ? ' (' . $shippingInfo->getDisplayName() . ')'
                : ($shippingInfo->getName() ? ' (' . $shippingInfo->getName() . ')' : '')) . ':'
        );
        $ordersTotal["text"]       = $xtPrice->xtcFormat($shippingCosts, true);
        $ordersTotal["value"]      = $shippingCosts;
        $ordersTotal["class"]      = ShopgateCustomerOrderModel::TOTAL_CLASS_SHIPPING;
        $ordersTotal["sort_order"] = $shippingSortOrder;
        xtc_db_perform(TABLE_ORDERS_TOTAL, $ordersTotal);
        // insert payment costs.
        //
        //WARNING: On modify: Change the taxes calculation too!
        if ($order->getAmountShopPayment() != 0) {
            $this->log('db: save payment fee', ShopgateLogger::LOGTYPE_DEBUG);
            $paymentInfo       = $order->getPaymentInfos();

            $ordersTotal               = array();
            $ordersTotal["orders_id"]  = $dbOrderId;
            $ordersTotal["title"]      = ShopgateWrapper::db_prepare_input(
                MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_PAYMENTFEE . (!empty($paymentInfo['shopgate_payment_name'])
                    ? ' (' . $paymentInfo['shopgate_payment_name'] . '):' : '')
            );
            $ordersTotal["text"]       = $xtPrice->xtcFormat($order->getAmountShopPayment(), true);
            $ordersTotal["value"]      = $order->getAmountShopPayment();
            $ordersTotal["class"]      = ShopgateCustomerOrderModel::TOTAL_CLASS_PAYMENT;
            $ordersTotal["sort_order"] = ++$shippingSortOrder;
            xtc_db_perform(TABLE_ORDERS_TOTAL, $ordersTotal);

        }

        $this->log('_insertOrderTotal(): add tax totals', ShopgateLogger::LOGTYPE_DEBUG);

        foreach ($taxes as $percent => $tax_value) {
            $ordersTotal               = array();
            $ordersTotal["orders_id"]  = $dbOrderId;
            $ordersTotal["title"]      = "inkl. UST {$percent} %";
            $ordersTotal["text"]       = $xtPrice->xtcFormat($tax_value, true);
            $ordersTotal["value"]      = $tax_value;
            $ordersTotal["class"]      = "ot_tax";
            $ordersTotal["sort_order"] = MODULE_ORDER_TOTAL_TAX_SORT_ORDER;
            xtc_db_perform(TABLE_ORDERS_TOTAL, $ordersTotal);
        }

        $this->log('_insertOrderTotal(): add order total', ShopgateLogger::LOGTYPE_DEBUG);

        $ordersTotal               = array();
        $ordersTotal["orders_id"]  = $dbOrderId;
        $ordersTotal["title"]      = "<b>" . MODULE_PAYMENT_SHOPGATE_ORDER_LINE_TEXT_TOTAL . ":</b>";
        $ordersTotal["text"]       = "<b>" . $xtPrice->xtcFormat($amountWithTax, true) . "</b>";
        $ordersTotal["value"]      = $amountWithTax;
        $ordersTotal["class"]      = "ot_total";
        $ordersTotal["sort_order"] = MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER;
        xtc_db_perform(TABLE_ORDERS_TOTAL, $ordersTotal);
    }

    /**
     * calculate the taxes to an complete order
     *
     * @param ShopgateOrder $order
     *
     * @return array
     */
    private function getOrderTaxes(ShopgateOrder $order)
    {
        $this->log('_getOrderTaxes(): start', ShopgateLogger::LOGTYPE_DEBUG);

        $taxes = array();

        foreach ($order->getItems() as $orderItem) {

            $tax = $orderItem->getTaxPercent();

            $tax       = intval($tax * 100) / 100;
            $tax_value = $orderItem->getUnitAmountWithTax() - $orderItem->getUnitAmount();

            if (!isset($taxes[$tax])) {
                $taxes[$tax] = 0;
            }

            $taxes[$tax] += $tax_value * $orderItem->getQuantity();
        }

        $shippingTaxRate = $this->getOrderShippingTaxRate($order);
        if (!empty($shippingTaxRate)) {

            $shippingTaxRate = intval($shippingTaxRate * 100) / 100;

            if (!isset($taxes[$shippingTaxRate])) {
                $taxes[$shippingTaxRate] = 0;
            }
            $taxes[$shippingTaxRate] += $order->getAmountShipping() - $this->getOrderShippingAmountWithoutTax(
                    $order, $shippingTaxRate
                );
        }

        // set taxes for payment method
        if ($order->getAmountShopPayment() != 0) {
            $tax       = $order->getPaymentTaxPercent();
            $tax       = intval($tax * 100) / 100;
            $tax_value = $order->getAmountShopPayment() - round(
                    ($order->getAmountShopPayment() * 100) / ($order->getPaymentTaxPercent() + 100), 2
                );

            if (!isset($taxes[$tax])) {
                $taxes[$tax] = 0;
            };

            $taxes[$tax] += $tax_value;
        }

        $this->log('_getOrderTaxes(): end', ShopgateLogger::LOGTYPE_DEBUG);

        return $taxes;
    }

    /**
     * calculates the tax rate to an shipping method
     *
     * @param ShopgateOrder $order
     *
     * @return float|int
     */
    private function getOrderShippingTaxRate(ShopgateOrder $order)
    {
        $this->log('_getOrderShippingTaxRate(): start', ShopgateLogger::LOGTYPE_DEBUG);

        $shippingTaxRate = 0;

        // Check if a shipping method is set in config
        $shippingMethod    = $this->config->getShipping();
        $orderCountryCode2 = $order->getInvoiceAddress()->getCountry();

        if (!empty($shippingMethod)) {
            $this->log('db: get configuration value ', ShopgateLogger::LOGTYPE_DEBUG);

            // Get tax value from shipping module
            $taxQuery =
                "SELECT `c`.`configuration_value`, `tr`.`tax_rate` " .
                "FROM `" . TABLE_CONFIGURATION . "` AS `c` " .
                "INNER JOIN `" . TABLE_TAX_RATES . "` AS `tr` ON(`c`.`configuration_value`=`tr`.`tax_class_id`) " .
                "INNER JOIN `" . TABLE_ZONES_TO_GEO_ZONES
                . "` AS `geozones` ON(`tr`.`tax_zone_id`=`geozones`.`geo_zone_id`) " .
                "INNER JOIN `" . TABLE_COUNTRIES . "` AS `co` ON(`geozones`.`zone_country_id`=`co`.`countries_id`) " .
                "WHERE " .
                "`c`.`configuration_key` = 'MODULE_SHIPPING_" . strtoupper($shippingMethod) . "_TAX_CLASS' " .
                "AND " .
                "`co`.`countries_iso_code_2`='$orderCountryCode2';";
        } else {
            $taxQuery = "SELECT MAX(tr.tax_rate) AS tax_rate  FROM tax_rates AS tr";
        }

        $result           = xtc_db_query($taxQuery);
        $moduleTaxSetting = xtc_db_fetch_array($result);

        if (!empty($moduleTaxSetting) && !empty($moduleTaxSetting['tax_rate'])) {
            $shippingTaxRate = intval($moduleTaxSetting['tax_rate'] * 100) / 100;
        }

        $this->log('_getOrderShippingTaxRate(): end', ShopgateLogger::LOGTYPE_DEBUG);

        return $shippingTaxRate;
    }

    /**
     * add tax to the complete shipping amount
     *
     * @param ShopgateOrder $order
     * @param int           $shippingTaxRate
     *
     * @return float|int
     */
    private function getOrderShippingAmountWithoutTax(ShopgateOrder $order, $shippingTaxRate = 0)
    {
        $shippingAmountWithoutTax = $order->getAmountShipping();

        // Check if a shipping method is set in config
        $shippingMethod = $this->config->getShipping();
        if (!empty($shippingTaxRate)) {
            // remove tax from shipping costs
            $shippingAmountWithoutTax /= 1 + $shippingTaxRate / 100;
        }

        return $shippingAmountWithoutTax;
    }

    /**
     * send order information to afterbuy
     *
     * @param               $iOrderId
     * @param ShopgateOrder $order
     */
    private function pushOrderToAfterbuy($iOrderId, ShopgateOrder $order)
    {
        if (!$order->getIsShippingBlocked() && defined('AFTERBUY_ACTIVATED') && AFTERBUY_ACTIVATED == 'true') {
            $this->log("START TO SEND ORDER TO AFTERBUY", ShopgateLogger::LOGTYPE_ACCESS);

            require_once(DIR_WS_CLASSES . 'afterbuy.php');
            $aBUY = new xtc_afterbuy_functions($iOrderId);
            if ($aBUY->order_send()) {
                $aBUY->process_order();
                $this->log("SUCCESSFUL ORDER SEND TO AFTERBUY", ShopgateLogger::LOGTYPE_ACCESS);
            } else {
                $this->log("ORDER ALREADY SEND TO AFTERBUY", ShopgateLogger::LOGTYPE_ACCESS);
            }

            $this->log("FINISH SEND ORDER TO AFTERBUY", ShopgateLogger::LOGTYPE_ACCESS);
        }
    }

    /**
     * send order information to dreambot
     *
     * @param               $dbOrderId
     * @param ShopgateOrder $shopgateOrder
     */
    private function pushOrderToDreamRobot($dbOrderId, ShopgateOrder $shopgateOrder)
    {
        if (!$shopgateOrder->getIsShippingBlocked() && file_exists(DIR_FS_CATALOG . 'dreamrobot_checkout.inc.php')) {
            require_once(DIR_FS_CATALOG . 'includes/classes/order.php');
            $this->log("START TO SEND ORDER TO DREAMROBOT", ShopgateLogger::LOGTYPE_ACCESS);

            $order                        = new order($dbOrderId);
            $_SESSION['tmp_oID']          = $dbOrderId;
            $order->info['shipping_cost'] = $shopgateOrder->getAmountShipping();
            include_once('./dreamrobot_checkout.inc.php');

            $this->log("FINISH SEND ORDER TO DREAMROBOT", ShopgateLogger::LOGTYPE_ACCESS);
        }
    }

    /**
     * @param $version
     *
     * @return bool
     */
    private function assertMinimumVersion($version)
    {
        return version_compare($this->modifiedVersion, $version, '>=');
    }

    /**
     * logic for sending mails was taken out of the shop system
     *
     * @param $insert_id
     * @param $userId
     *
     * @throws ShopgateLibraryException
     */
    private function sendOrderEmail($insert_id, $userId)
    {
        if (!$this->assertMinimumVersion('1.00')) {
            return;
        }

        $_SESSION['customer_id'] = $userId;
        require_once(DIR_FS_INC . 'xtc_get_order_data.inc.php');
        require_once(DIR_FS_INC . 'xtc_get_attributes_model.inc.php');
        require_once(DIR_WS_INCLUDES . "classes/order.php");

        if (!defined('SEND_EMAILS')) {
            define('SEND_EMAILS', 'true');
        }

        if (!$this->assertMinimumVersion('1.05')) {
            require_once(DIR_FS_CATALOG
                . "includes/external/shopgate/base/inc/shopgate_php_mail_older_shop_version.inc.php");
            // check if customer is allowed to send this order!
            $order_query_check = xtc_db_query(
                "SELECT
                customers_id
                FROM " . TABLE_ORDERS . "
                WHERE orders_id='" . $insert_id . "'"
            );

            $order_check = xtc_db_fetch_array($order_query_check);
            if ($_SESSION['customer_id'] == $order_check['customers_id']) {
                $order  = new order($insert_id);
                $smarty = new Smarty;
                $smarty->assign(
                    'address_label_customer',
                    xtc_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />')
                );
                $smarty->assign(
                    'address_label_shipping',
                    xtc_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />')
                );
                if ($_SESSION['credit_covers'] != '1') {
                    $smarty->assign(
                        'address_label_payment',
                        xtc_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />')
                    );
                }
                $smarty->assign('csID', $order->customer['csID']);
                $order_total = $order->getTotalData($insert_id);
                $smarty->assign('order_data', $order->getOrderData($insert_id));
                $smarty->assign('order_total', $order_total['data']);
                // assign language to template for caching
                $smarty->assign('language', $_SESSION['language']);
                $smarty->assign('tpl_path', 'templates/' . CURRENT_TEMPLATE . '/');
                $smarty->assign('logo_path', HTTP_SERVER . DIR_WS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/img/');
                $smarty->assign('oID', $insert_id);
                if ($order->info['payment_method'] != '' && $order->info['payment_method'] != 'no_payment') {
                    include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/payment/'
                        . $order->info['payment_method'] . '.php');
                    $payment_method =
                        constant(strtoupper('MODULE_PAYMENT_' . $order->info['payment_method'] . '_TEXT_TITLE'));
                }
                $smarty->assign('PAYMENT_METHOD', $payment_method);
                $smarty->assign('DATE', xtc_date_long($order->info['date_purchased']));
                $smarty->assign('NAME', $order->customer['name']);
                $smarty->assign('COMMENTS', $order->info['comments']);
                $smarty->assign('EMAIL', $order->customer['email_address']);
                $smarty->assign('PHONE', $order->customer['telephone']);
                /** BEGIN BILLPAY CHANGED **/
                if ($this->assertMinimumVersion('2.0.0.0')) {
                    require_once(DIR_FS_CATALOG . 'includes/external/billpay/utils/billpay_mail.php');
                } else {
                    require_once(DIR_FS_CATALOG . 'includes/billpay/utils/billpay_mail.php');
                }
                /** EOF BILLPAY CHANGED **/
                //BOF  - web28 - 2010-03-27 PayPal Bezahl-Link
                unset ($_SESSION['paypal_link']);
                if ($order->info['payment_method'] == 'paypal_ipn') {

                    $paypal_link     = array();
                    $payment_modules = new paypal_ipn;
                    $payment_modules->create_paypal_link();
                    $smarty->assign('PAYMENT_INFO_HTML', $paypal_link['html']);
                    $smarty->assign('PAYMENT_INFO_TXT', MODULE_PAYMENT_PAYPAL_IPN_TXT_EMAIL . $paypal_link['text']);
                    $_SESSION['paypal_link'] = $paypal_link['checkout'];

                }
                //EOF  - web28 - 2010-03-27 PayPal Bezahl-Link

                // PAYMENT MODUL TEXTS
                // EU Bank Transfer
                if ($order->info['payment_method'] == 'eustandardtransfer') {
                    $smarty->assign('PAYMENT_INFO_HTML', MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION);
                    $smarty->assign(
                        'PAYMENT_INFO_TXT', str_replace("<br />", "\n", MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION)
                    );
                }

                // MONEYORDER
                if ($order->info['payment_method'] == 'moneyorder') {
                    $smarty->assign('PAYMENT_INFO_HTML', MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION);
                    $smarty->assign(
                        'PAYMENT_INFO_TXT', str_replace("<br />", "\n", MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION)
                    );
                }
                // -------Trustedshops Kundenbewertung -----------
                if (TS_SHOW_RATING_MAIL) {
                    getTSRatingButtonOrder($_SESSION['customer_id'], $ts_rating_button_dest_url, $ts_rating_button_img);
                    $smarty->assign('TS_RATING_MAIL_TXT', TS_RATING_EMAIL . ":\n" . $ts_rating_button_dest_url);
                    $smarty->assign('TS_RATING_MAIL_HTML', TS_RATING_EMAIL . ":<br/>" . $ts_rating_button_dest_url);
                }
                // -------Trustedshops Kundenbewertung -----------

                // dont allow cache
                $smarty->caching = false;

                $html_mail = $smarty->fetch(CURRENT_TEMPLATE . '/mail/' . $_SESSION['language'] . '/order_mail.html');
                $txt_mail  = $smarty->fetch(CURRENT_TEMPLATE . '/mail/' . $_SESSION['language'] . '/order_mail.txt');

                // create subject
                $order_subject = str_replace('{$nr}', $insert_id, EMAIL_BILLING_SUBJECT_ORDER);
                $order_subject = str_replace('{$date}', strftime(DATE_FORMAT_LONG), $order_subject);
                $order_subject = str_replace('{$lastname}', $order->customer['lastname'], $order_subject);
                $order_subject = str_replace('{$firstname}', $order->customer['firstname'], $order_subject);

                // send mail to admin
                //BOF Dokuman - 2009-08-19 - BUGFIX: #0000227 customers surname in reply address in orders mail to admin
                //    xtc_php_mail(EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, EMAIL_BILLING_ADDRESS, STORE_NAME, EMAIL_BILLING_FORWARDING_STRING, $order->customer['email_address'], $order->customer['firstname'], '', '', $order_subject, $html_mail, $txt_mail);
                //xtc_php_mail(EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, EMAIL_BILLING_ADDRESS, STORE_NAME, EMAIL_BILLING_FORWARDING_STRING, $order->customer['email_address'], $order->customer['firstname'].' '.$order->customer['lastname'], '', '', $order_subject, $html_mail, $txt_mail);
                if (!shopgate_php_mail(
                    EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, EMAIL_BILLING_ADDRESS, STORE_NAME,
                    EMAIL_BILLING_FORWARDING_STRING, $order->customer['email_address'],
                    $order->customer['firstname'] . ' ' . $order->customer['lastname'], '', '', $order_subject,
                    $html_mail, $txt_mail
                )
                ) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_EMAIL_SEND_ERROR,
                        "Error while sending order confirmation mail to admin",
                        true
                    );
                }
                //EOF Dokuman - 2009-08-19 - BUGFIX: #0000227 customers surname in reply address in orders mail to admin

                // send mail to customer
                //BOF - Dokuman - 2009-10-17 - Send emails to customer only, when set to "true" in admin panel
                if (SEND_EMAILS == 'true') {
                    //EOF - Dokuman - 2009-10-17 - Send emails to customer only, when set to "true" in admin panel
                    //xtc_php_mail(EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, $order->customer['email_address'], $order->customer['firstname'].' '.$order->customer['lasNtname'], '', EMAIL_BILLING_REPLY_ADDRESS, EMAIL_BILLING_REPLY_ADDRESS_NAME, '', '', $order_subject, $html_mail, $txt_mail);
                    if (!shopgate_php_mail(
                        EMAIL_BILLING_ADDRESS, EMAIL_BILLING_NAME, $order->customer['email_address'],
                        $order->customer['firstname'] . ' ' . $order->customer['lastname'], '',
                        EMAIL_BILLING_REPLY_ADDRESS, EMAIL_BILLING_REPLY_ADDRESS_NAME, '', '', $order_subject,
                        $html_mail, $txt_mail
                    )
                    ) {
                        throw new ShopgateLibraryException(
                            ShopgateLibraryException::PLUGIN_EMAIL_SEND_ERROR,
                            "Error while sending order confirmation mail to customer",
                            true
                        );
                    }
                    //BOF - Dokuman - 2009-10-17 - Send emails to customer only, when set to "true" in admin panel
                }
                //EOF - Dokuman - 2009-10-17 - Send emails to customer only, when set to "true" in admin panel

                if (AFTERBUY_ACTIVATED == 'true') {
                    require_once(DIR_WS_CLASSES . 'afterbuy.php');
                    $aBUY = new xtc_afterbuy_functions($insert_id);
                    if ($aBUY->order_send()) {
                        $aBUY->process_order();
                    }
                }
            }
        } else {
            if ($this->assertMinimumVersion('2.0.0.0')) {
                require_once(DIR_FS_EXTERNAL . 'phpmailer/PHPMailerAutoload.php');
            }
            require_once(DIR_FS_CATALOG . "includes/external/shopgate/base/inc/shopgate_xtc_php_mail.inc.php");
            $smarty = new Smarty;

            // check if customer is allowed to send this order!
            $order_query_check = xtc_db_query(
                "SELECT customers_id
                                 FROM " . TABLE_ORDERS . "
                                WHERE orders_id='" . $insert_id . "'"
            );

            $order_check = xtc_db_fetch_array($order_query_check);
            //BOF - web28 - 2010-03-20 - Send Order by Admin
            if ($_SESSION['customer_id'] == $order_check['customers_id'] || $send_by_admin) {

                //EOF - web28 - 2010-03-20 - Send Order by Admin
                $order = new order($insert_id);
                if (empty($order->info['language'])) {
                    $order->info['language'] = $this->language;
                }
                // BOF - Tomcraft - 2009-10-03 - Paypal Express Modul
                $sessionAccountPassword = ($this->assertMinimumVersion('2.0.0.0'))
                    ? : isset($_SESSION['ACCOUNT_PASSWORD']) && $_SESSION['ACCOUNT_PASSWORD'] == 'true';

                if (isset($_SESSION['paypal_express_new_customer'])
                    && $_SESSION['paypal_express_new_customer'] == 'true'
                    && $sessionAccountPassword
                ) {
                    require_once(DIR_FS_INC . 'xtc_create_password.inc.php');
                    require_once(DIR_FS_INC . 'xtc_encrypt_password.inc.php');
                    $password_encrypted = xtc_RandomString(ENTRY_PASSWORD_MIN_LENGTH * 2);
                    $password           = xtc_encrypt_password($password_encrypted);

                    if (!defined('PROJECT_MAJOR_VERSION')) {
                        xtc_db_query(
                            "update " . TABLE_CUSTOMERS . " set customers_password = '" . $password
                            . "' where customers_id = '" . (int)$_SESSION['customer_id'] . "'"
                        );
                    } else {
                        xtc_db_query(
                            "update " . TABLE_CUSTOMERS . " set customers_password = '" . $password
                            . "', password_request_time = now() where customers_id = '" . (int)$_SESSION['customer_id']
                            . "'"
                        );
                    }

                    $smarty->assign('NEW_PASSWORD', $password_encrypted);
                }
                // EOF - Tomcraft - 2009-10-03 - Paypal Express Modul
                //BOF - web28 - 2010-03-20 - Send Order by Admin
                if (isset($send_by_admin)) {//DokuMan - 2010-09-18 - Undefined variable: send_by_admin
                    $xtPrice = new xtcPrice($order->info['currency'], $order->info['status']);
                }
                //EOF - web28 - 2010-03-20 - Send Order by Admin
                $smarty->assign(
                    'address_label_customer',
                    xtc_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />')
                );
                $smarty->assign(
                    'address_label_shipping',
                    xtc_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />')
                );
                $smarty->assign(
                    'address_label_payment',
                    xtc_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />')
                );
                $smarty->assign('csID', $order->customer['csID']);

                $order_total = $order->getTotalData(
                    $insert_id
                ); //ACHTUNG für Bestellbestätigung  aus Admin Funktion in admin/includes/classes/order.php
                $smarty->assign(
                    'order_data', $order->getOrderData($insert_id)
                ); //ACHTUNG für Bestellbestätigung  aus Admin Funktion in admin/includes/classes/order.php
                $smarty->assign('order_total', $order_total['data']);

                // assign language to template for caching Web28 2012-04-25 - change all $_SESSION['language'] to $order->info['language']
                $smarty->assign('language', $order->info['language']);
                $smarty->assign('tpl_path', 'templates/' . CURRENT_TEMPLATE . '/');
                $smarty->assign('logo_path', HTTP_SERVER . DIR_WS_CATALOG . 'templates/' . CURRENT_TEMPLATE . '/img/');
                //$smarty->assign('oID', $insert_id);
                $smarty->assign('oID', $order->info['order_id']); //DokuMan - 2011-08-31 - fix order_id assignment
                //shipping method
                if ($order->info['shipping_class'] != '') {
                    $shipping_class = explode('_', $order->info['shipping_class']);
                    include(DIR_FS_CATALOG . 'lang/' . $order->info['language'] . '/modules/shipping/'
                        . $shipping_class[0] . '.php');
                    $shipping_method = constant(strtoupper('MODULE_SHIPPING_' . $shipping_class[0] . '_TEXT_TITLE'));
                }
                $smarty->assign('SHIPPING_METHOD', $shipping_method);
                //payment method
                if ($order->info['payment_method'] != '' && $order->info['payment_method'] != 'no_payment') {
                    include_once(DIR_FS_CATALOG . 'lang/' . $order->info['language'] . '/modules/payment/'
                        . $order->info['payment_method'] . '.php');
                    $payment_method =
                        constant(strtoupper('MODULE_PAYMENT_' . $order->info['payment_method'] . '_TEXT_TITLE'));
                }
                $smarty->assign('PAYMENT_METHOD', $payment_method);

                $smarty->assign('DATE', xtc_date_long($order->info['date_purchased']));
                $smarty->assign('NAME', $order->customer['name']);

                //BOF - web28 - 2010-08-20 - Fix for more personalized e-mails to the customer (show salutation and surname)
                $gender_query = xtc_db_query(
                    "SELECT customers_gender FROM " . TABLE_CUSTOMERS . " WHERE customers_id = '"
                    . $order->customer['id'] . "'"
                );
                $gender       = xtc_db_fetch_array($gender_query);
                if ($gender['customers_gender'] == 'f') {
                    $smarty->assign('GENDER', FEMALE);
                } elseif ($gender['customers_gender'] == 'm') {
                    $smarty->assign('GENDER', MALE);
                } else {
                    $smarty->assign('GENDER', '');
                }
                //EOF - web28 - 2010-08-20 - Fix for more personalized e-mails to the customer (show salutation and surname)
                //BOF - web28 - 2010-08-20 - Erweiterung Variablen für Bestätigungsmail
                $smarty->assign('CITY', $order->customer['city']);
                $smarty->assign('POSTCODE', $order->customer['postcode']);
                $smarty->assign('STATE', $order->customer['state']);
                $smarty->assign('COUNTRY', $order->customer['country']);
                $smarty->assign('COMPANY', $order->customer['company']);
                $smarty->assign('STREET', $order->customer['street_address']);
                $smarty->assign('FIRSTNAME', $order->customer['firstname']);
                $smarty->assign('LASTNAME', $order->customer['lastname']);
                //EOF - web28 - 2010-08-20 - Erweiterung Variablen für Bestätigungsmail

                $smarty->assign('COMMENTS', $order->info['comments']);
                $smarty->assign('EMAIL', $order->customer['email_address']);
                $smarty->assign('PHONE', $order->customer['telephone']);

                //BOF  - web28 - 2010-03-27 PayPal Bezahl-Link
                unset ($_SESSION['paypal_link']);
                if ($order->info['payment_method'] == 'paypal_ipn') {

                    //BOF - web28 - 2010-06-11 - Send Order  by Admin Paypal IPN
                    if (isset($send_by_admin)) { //DokuMan - 2010-09-18 - Undefined variable: send_by_admin
                        require(DIR_FS_CATALOG_MODULES . 'payment/paypal_ipn.php');
                        include(DIR_FS_LANGUAGES . $order->info['language'] . '/modules/payment/paypal_ipn.php');
                        $payment_modules = new paypal_ipn;
                    }
                    //EOF - web28 - 2010-06-11 - Send Order  by Admin Paypal IPN

                    $paypal_link = array();
                    $payment_modules->create_paypal_link();

                    $smarty->assign('PAYMENT_INFO_HTML', $paypal_link['html']);
                    $smarty->assign('PAYMENT_INFO_TXT', MODULE_PAYMENT_PAYPAL_IPN_TXT_EMAIL . $paypal_link['text']);
                    $_SESSION['paypal_link'] = $paypal_link['checkout'];

                }
                //EOF  - web28 - 2010-03-27 PayPal Bezahl-Link
                // PAYMENT MODUL TEXTS
                // EU Bank Transfer
                if ($order->info['payment_method'] == 'eustandardtransfer') {
                    $smarty->assign('PAYMENT_INFO_HTML', MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION);
                    $smarty->assign(
                        'PAYMENT_INFO_TXT', str_replace("<br />", "\n", MODULE_PAYMENT_EUSTANDARDTRANSFER_TEXT_DESCRIPTION)
                    );
                }

                // MONEYORDER
                if ($order->info['payment_method'] == 'moneyorder') {
                    $smarty->assign('PAYMENT_INFO_HTML', MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION);
                    $smarty->assign(
                        'PAYMENT_INFO_TXT', str_replace("<br />", "\n", MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION)
                    );
                }

                // Cash on Delivery
                if ($order->info['payment_method'] == 'cod') {
                    $smarty->assign('PAYMENT_INFO_HTML', MODULE_PAYMENT_COD_TEXT_INFO);
                    $smarty->assign('PAYMENT_INFO_TXT', str_replace("<br />", "\n", MODULE_PAYMENT_COD_TEXT_INFO));
                }

                //allow duty-note in email
                if (empty($main) || !is_object($main)) {
                    require_once(DIR_FS_CATALOG . 'includes/classes/main.php');
                    $main = new main();
                }
                if (method_exists($main, "getDeliveryDutyInfo")) {
                    $smarty->assign(
                        'DELIVERY_DUTY_INFO', $main->getDeliveryDutyInfo($order->delivery['country_iso_2'])
                    );

                    //absolute image path
                    $smarty->assign(
                        'img_path', HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES . 'product_images/' . (defined(
                                      'SHOW_IMAGES_IN_EMAIL_DIR'
                                  ) ? SHOW_IMAGES_IN_EMAIL_DIR : 'thumbnail') . '_images/'
                    );
                    // dont allow cache
                    $smarty->caching = 0;

                    // BOF - Tomcraft - 2011-06-17 - Added revocation to email
                    $shop_content_data = $main->getContentData(REVOCATION_ID);
                    $revocation        = $shop_content_data['content_text'];
                    $smarty->assign('REVOCATION_HTML', $revocation);
                    $smarty->assign(
                        'REVOCATION_TXT', $revocation
                    ); //replace br, strip_tags, html_entity_decode are allready execute in xtc_php_mail  function
                }
                // EOF - Tomcraft - 2011-06-17 - Added revocation to email

                $html_mail =
                    $smarty->fetch(CURRENT_TEMPLATE . '/mail/' . $order->info['language'] . '/order_mail.html');
                $txt_mail  = $smarty->fetch(CURRENT_TEMPLATE . '/mail/' . $order->info['language'] . '/order_mail.txt');

                //email attachments
                $email_attachments = defined('EMAIL_BILLING_ATTACHMENTS') ? EMAIL_BILLING_ATTACHMENTS : '';
                // create subject
                $order_subject = str_replace('{$nr}', $insert_id, EMAIL_BILLING_SUBJECT_ORDER);
                $order_subject = str_replace(
                    '{$date}', xtc_date_long($order->info['date_purchased']), $order_subject
                ); // Tomcraft - 2011-12-28 - Use date_puchased instead of current date in E-Mail subject
                $order_subject = str_replace('{$lastname}', $order->customer['lastname'], $order_subject);
                $order_subject = str_replace('{$firstname}', $order->customer['firstname'], $order_subject);

                // send mail to admin
                if (!shopgate_php_mail(
                    EMAIL_BILLING_ADDRESS,
                    EMAIL_BILLING_NAME,
                    EMAIL_BILLING_ADDRESS,
                    STORE_NAME,
                    EMAIL_BILLING_FORWARDING_STRING,
                    $order->customer['email_address'],
                    $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                    $email_attachments,
                    '',
                    $order_subject,
                    $html_mail,
                    $txt_mail
                )
                ) {
                    throw new ShopgateLibraryException(
                        ShopgateLibraryException::PLUGIN_EMAIL_SEND_ERROR,
                        "Error while sending order confirmation mail to admin"
                    );
                }
                // send mail to customer
                if (SEND_EMAILS == 'true' || $send_by_admin) {
                    if (!shopgate_php_mail(
                        EMAIL_BILLING_ADDRESS,
                        EMAIL_BILLING_NAME,
                        $order->customer['email_address'],
                        $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                        '',
                        EMAIL_BILLING_REPLY_ADDRESS,
                        EMAIL_BILLING_REPLY_ADDRESS_NAME,
                        $email_attachments,
                        '',
                        $order_subject,
                        $html_mail,
                        $txt_mail
                    )
                    ) {
                        throw new ShopgateLibraryException(
                            ShopgateLibraryException::PLUGIN_EMAIL_SEND_ERROR,
                            "Error while sending order confirmation mail to customer"
                        );
                    }
                }

                if (AFTERBUY_ACTIVATED == 'true') {
                    require_once(DIR_WS_CLASSES . 'afterbuy.php');
                    $aBUY = new xtc_afterbuy_functions($insert_id);
                    if ($aBUY->order_send()) {
                        $aBUY->process_order();
                    }
                }
                //BOF - web28 - 2010-03-20 - Send Order by Admin
                if (isset($send_by_admin)) { //DokuMan - 2010-09-18 - Undefined variable: send_by_admin
                    $customer_notified = '1';
                    //Comment out the next line for setting  the $orders_status_id= '1 '- Auskommentieren der nächste Zeile, um die $orders_status_id = '1' zu setzen
                    $orders_status_id = ($order->info['orders_status'] < 1) ? '1' : $order->info['orders_status'];

                    //web28 - 2011-03-20 - Fix order status
                    xtc_db_query(
                        "UPDATE " . TABLE_ORDERS . "
                 SET orders_status = '" . xtc_db_input($orders_status_id) . "',
                     last_modified = now()
               WHERE orders_id = '" . xtc_db_input($insert_id) . "'"
                    );

                    //web28 - 2011-08-26 - Fix order status history
                    xtc_db_query(
                        "INSERT INTO " . TABLE_ORDERS_STATUS_HISTORY . "
                      SET orders_id = '" . xtc_db_input($insert_id) . "',
                          orders_status_id = '" . xtc_db_input($orders_status_id) . "',
                          date_added = now(),
                          customer_notified = '" . $customer_notified . "',
                          comments = '" . COMMENT_SEND_ORDER_BY_ADMIN . "'"
                    );

                    $messageStack->add_session(SUCCESS_ORDER_SEND, 'success');

                    if (isset($_GET['site']) && $_GET['site'] == 1) { //DokuMan - 2010-09-18 - Undefined variable
                        xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'oID=' . $_GET['oID'] . '&action=edit'));
                    } else {
                        xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'oID=' . $_GET['oID']));
                    }
                }
                //EOF - web28 - 2010-03-20 - Send Order by Admin
            }
        }
    }

    /**
     * Marks shipped orders as "shipped" at Shopgate.
     *
     * This will find all orders that are marked "shipped" in the shop system but not at Shopgate yet and marks them "shipped" at Shopgate via
     * Shopgate Merchant API.
     *
     * @param string $message    Process log will be appended to this reference.
     * @param int    $errorcount This reference gets incremented on errors.
     */
    protected function cronSetOrdersShippingCompleted(&$message, &$errorcount)
    {
        $query  =
            "SELECT `sgo`.`orders_id`, `sgo`.`shopgate_order_number` " .
            "FROM `" . TABLE_SHOPGATE_ORDERS . "` sgo " .
            "INNER JOIN `" . TABLE_ORDERS . "` xto ON (`xto`.`orders_id` = `sgo`.`orders_id`) " .
            "INNER JOIN `" . TABLE_LANGUAGES . "` xtl ON (`xtl`.`directory` = `xto`.`language`) " .
            "WHERE `sgo`.`is_sent_to_shopgate` = 0 " .
            "AND `xto`.`orders_status` = " . xtc_db_input($this->config->getOrderStatusShipped()) . " " .
            "AND `xtl`.`code` = '" . xtc_db_input($this->config->getLanguage()) . "';";
        $result = xtc_db_query($query);

        if (empty($result)) {
            return;
        }

        while ($shopgateOrder = xtc_db_fetch_array($result)) {
            if (!$this->setOrderShippingCompleted(
                $shopgateOrder['shopgate_order_number'], $shopgateOrder['orders_id'], $this->merchantApi, $this->config
            )
            ) {
                $errorcount++;
                $message .= 'Shopgate order number "' . $shopgateOrder['shopgate_order_number'] . '": error' . "\n";
            }
        }
    }

    /**
     * Sets the order status of a Shopgate order to "shipped" via Shopgate Merchant API
     *
     * @param string $shopgateOrderNumber   The number of the order at Shopgate.
     * @param int    $orderId               The ID of the order in the shop system.
     * @param        ShopgateMerchantApi    The SMA object to use for the request.
     * @param        ShopgateConfigModified The configuration to use for the order's status history.
     *
     * @return bool true on success, false on failure.
     */
    protected function setOrderShippingCompleted(
        $shopgateOrderNumber, $orderId, ShopgateMerchantApi &$merchantApi, ShopgateConfigModified &$config
    ) {
        $success = false;

        // These are expected and should not be added to error count:
        $ignoreCodes = array(
            ShopgateMerchantApiException::ORDER_ALREADY_COMPLETED,
            ShopgateMerchantApiException::ORDER_SHIPPING_STATUS_ALREADY_COMPLETED
        );

        try {
            $merchantApi->setOrderShippingCompleted($shopgateOrderNumber);

            $statusArr = array(
                "orders_id"         => $orderId,
                "orders_status_id"  => $config->getOrderStatusShipped(),
                "date_added"        => date('Y-m-d H:i:s'),
                "customer_notified" => 1,
                "comments"          => "[Shopgate] Bestellung wurde bei Shopgate als versendet markiert",
            );

            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $statusArr);

            $success = true;
        } catch (ShopgateLibraryException $e) {
            $response = $this->stringFromUtf8($e->getAdditionalInformation(), $config->getEncoding());

            $statusArr = array(
                "orders_id"         => $orderId,
                "orders_status_id"  => $config->getOrderStatusShipped(),
                "date_added"        => date('Y-m-d H:i:s'),
                "customer_notified" => 0,
                "comments"          => "[Shopgate] Ein Fehler ist im Shopgate Modul aufgetreten ({$e->getCode()}): {$response}",
            );

            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $statusArr);
        } catch (ShopgateMerchantApiException $e) {
            $response = $this->stringFromUtf8($e->getMessage(), $config->getEncoding());

            $statusArr = array(
                "orders_id"         => $orderId,
                "orders_status_id"  => $config->getOrderStatusShipped(),
                "date_added"        => date('Y-m-d H:i:s'),
                "customer_notified" => 0,
                "comments"          => "[Shopgate] Ein Fehler ist bei Shopgate aufgetreten ({$e->getCode()}): {$response}",
            );

            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $statusArr);

            $success = (in_array($e->getCode(), $ignoreCodes)) ? true : false;
        } catch (Exception $e) {
            $response = $this->stringFromUtf8($e->getMessage(), $config->getEncoding());

            $statusArr = array(
                "orders_id"         => $orderId,
                "orders_status_id"  => $config->getOrderStatusShipped(),
                "date_added"        => date('Y-m-d H:i:s'),
                "customer_notified" => 0,
                "comments"          => "[Shopgate] Ein unbekannter Fehler ist aufgetreten ({$e->getCode()}): {$response}",
            );

            xtc_db_perform(TABLE_ORDERS_STATUS_HISTORY, $statusArr);
        }

        // Update shopgate order on success
        if ($success) {
            $qry =
                'UPDATE `' . TABLE_SHOPGATE_ORDERS . '` SET `is_sent_to_shopgate` = 1 WHERE `shopgate_order_number` = '
                . $shopgateOrderNumber . ';';
            xtc_db_query($qry);
        }

        return $success;
    }

    /**
     * Set the shipping status for a list of order IDs.
     *
     * @param int[] $orderIds The IDs of the orders in the shop system.
     * @param int   $status   The ID of the order status that has been set in the shopping system.
     */
    public function updateOrdersStatus($orderIds, $status)
    {
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $query  = xtc_db_input(
            "SELECT `sgo`.`orders_id`, `sgo`.`shopgate_order_number`, `xtl`.`code` " .
            "FROM `" . TABLE_SHOPGATE_ORDERS . "` sgo " .
            "INNER JOIN `" . TABLE_ORDERS . "` xto ON (`xto`.`orders_id` = `sgo`.`orders_id`) " .
            "INNER JOIN `" . TABLE_LANGUAGES . "` xtl ON (`xtl`.`directory` = `xto`.`language`) " .
            "WHERE `sgo`.`orders_id` IN (" . xtc_db_input(implode(", ", $orderIds)) . ")"
        );
        $result = xtc_db_query($query);

        if (empty($result)) {
            return;
        }

        /** @var ShopgateConfigModified[] $configurations */
        $configurations = array();
        $merchantApis   = array();
        while ($shopgateOrder = xtc_db_fetch_array($result)) {
            $language = $shopgateOrder['code']; // convenience

            if (empty($merchantApis[$language])) {
                try {
                    $config = new ShopgateConfigModified();
                    $config->loadByLanguage($language);
                    $builder                   = new ShopgateBuilder($config);
                    $merchantApis[$language]   = &$builder->buildMerchantApi();
                    $configurations[$language] = $config;
                } catch (ShopgateLibraryException $e) {
                    // do not abort. the error will be logged
                }
            }

            if ($status == $configurations[$language]->getOrderStatusShipped()) {
                $this->setOrderShippingCompleted(
                    $shopgateOrder['shopgate_order_number'], $shopgateOrder['orders_id'], $merchantApis[$language],
                    $configurations[$language]
                );
            }
            if ($status == $configurations[$language]->getOrderStatusCanceled()) {
                $this->sendOrderCancellation($shopgateOrder['shopgate_order_number'], $merchantApis[$language]);
            }
        }
    }

    /**
     * Function to Parse Options like [TAB:xxxx] in the Description
     *
     * @param $description
     *
     * @return mixed
     */
    private function parseDescription($description)
    {
        $tabs = array();
        preg_match_all("/\[TAB:[\w\s\d\&\;]*\]/", $description, $tabs);

        foreach ($tabs[0] as $replace) {
            $replacement = preg_replace("/(\[TAB:)|\]/", "", $replace);
            $replacement = "<h1>" . $replacement . "</h1>";

            $description = preg_replace("/" . preg_quote($replace) . "/", $replacement, $description);
        }

        return $description;
    }


    /**
     * get the status of a product from database
     *
     * @param int $products_id
     *
     * @return int
     */
    private function xtc_get_products_status($products_id)
    {
        $products_id  = xtc_get_prid($products_id);
        $stock_query  = xtc_db_query(
            "SELECT products_status FROM " . TABLE_PRODUCTS . " WHERE products_id = '" . $products_id . "'"
        );
        $stock_values = xtc_db_fetch_array($stock_query);

        return $stock_values['products_status'];
    }

    /**
     * Requires all files the plugin depends on with require_once.
     */
    private function requireFiles()
    {
        // load helper class(es)
        require_once(dirname(__FILE__) . '/helper/ShopgatePluginInitHelper.php');
        require_once(dirname(__FILE__) . '/helper/ShopgateItemHelper.php');

        // load model classes
        require_once(dirname(__FILE__) . '/Model/category/ShopgateCategoryModel.php');
        require_once(dirname(__FILE__) . '/Model/category/ShopgateCategoryXmlModel.php');
        require_once(dirname(__FILE__) . '/Model/coupon/ShopgateCouponModel.php');
        require_once(dirname(__FILE__) . '/Model/customer/ShopgateCustomerModel.php');
        require_once(dirname(__FILE__) . '/Model/customer/ShopgateCustomerOrderModel.php');
        require_once(dirname(__FILE__) . '/Model/global/ShopgateCustomFieldModel.php');
        require_once(dirname(__FILE__) . '/Model/item/ShopgateItemModel.php');
        require_once(dirname(__FILE__) . '/Model/item/ShopgateItemCartModel.php');
        require_once(dirname(__FILE__) . '/Model/item/ShopgateItemXmlModel.php');
        require_once(dirname(__FILE__) . '/Model/location/ShopgateLocationModel.php');
        require_once(dirname(__FILE__) . '/Model/location/ShopgateShippingModel.php');
        require_once(dirname(__FILE__) . '/Model/order/ShopgateOrderModel.php');
        require_once(dirname(__FILE__) . '/Model/review/ShopgateReviewModel.php');
        require_once(dirname(__FILE__) . '/Model/review/ShopgateReviewXmlModel.php');

        // load modified eCommerce files
        require_once(DIR_FS_INC . 'xtc_validate_password.inc.php');
        require_once(DIR_FS_INC . 'xtc_format_price_order.inc.php');
        require_once(DIR_FS_INC . 'xtc_get_tax_class_id.inc.php');
        require_once(DIR_FS_INC . 'xtc_get_products_stock.inc.php');
        if (file_exists(DIR_WS_CLASSES .'xtcPrice.php')) {
            require_once(DIR_WS_CLASSES.'xtcPrice.php');
        }

        if (!defined('DIR_FS_ADMIN')) {
            $admin = defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/';
            define('DIR_FS_ADMIN', DIR_FS_CATALOG . $admin);
        }

        if (file_exists(DIR_FS_ADMIN . 'includes/version.php')) {
            require_once(DIR_FS_ADMIN. 'includes/version.php');
        }

        if (!defined('PROJECT_MAJOR_VERSION')) {
            require_once(DIR_FS_INC . 'xtc_db_prepare_input.inc.php');
        } else {
            require_once(DIR_FS_INC . 'db_functions_' . DB_MYSQL_TYPE . '.inc.php');
        }

        if (!defined('DIR_FS_EXTERNAL')) {
            define('DIR_FS_EXTERNAL', DIR_FS_CATALOG . 'includes/external/');
        }

        // include Shopgate plugin files
        require_once(DIR_FS_EXTERNAL . 'shopgate/base/shopgate_wrapper.php');
        require_once(DIR_FS_EXTERNAL . 'shopgate/base/shopgate_config.php');
        require_once(DIR_FS_EXTERNAL . 'shopgate/base/lang/' . $this->language . '/modules/payment/shopgate.php');
    }
}

class ShopgateXtcMapper
{

    /**
     * The countries with non-ISO-3166-2 state codes in xt:Commerce 3 are mapped here.
     *
     * @var string[][]
     */
    protected static $stateCodesByCountryCode = array(
        'DE' => array(
            "BW" => "BAW",
            "BY" => "BAY",
            "BE" => "BER",
            "BB" => "BRG",
            "HB" => "BRE",
            "HH" => "HAM",
            "HE" => "HES",
            "MV" => "MEC",
            "NI" => "NDS",
            "NW" => "NRW",
            "RP" => "RHE",
            "SL" => "SAR",
            "SN" => "SAS",
            "ST" => "SAC",
            "SH" => "SCN",
            "TH" => "THE",
        ),
        "AT" => array(
            "1" => "BL",
            "2" => "KN",
            "3" => "NO",
            "4" => "OO",
            "5" => "SB",
            "6" => "ST",
            "7" => "TI",
            "8" => "VB",
            "9" => "WI",
        ),
        //"CH" => ist in xt:commerce bereits korrekt
        //"US" => ist in xt:commerce bereits korrekt
    );

    /**
     * Finds the corresponding Shopgate state code for a given xt:Commerce 3 state code (zone_code).
     *
     * @param string $countryCode  The code of the country to which the state belongs
     * @param string $xtcStateCode The code of the state / zone as found in the default "zones" table of xt:Commerce 3
     *
     * @return string The state code as defined at Shopgate Wiki
     *
     * @throws ShopgateLibraryException if one of the given codes is unknown
     */
    public static function getShopgateStateCode($countryCode, $xtcStateCode)
    {
        $countryCode  = strtoupper($countryCode);
        $xtcStateCode = strtoupper($xtcStateCode);

        if (!isset(self::$stateCodesByCountryCode[$countryCode])) {
            return $countryCode . '-' . $xtcStateCode;
        }

        $codes = array_flip(self::$stateCodesByCountryCode[$countryCode]);
        if (!isset($codes[$xtcStateCode])) {
            return $countryCode . '-' . $xtcStateCode;
        }

        $stateCode = $codes[$xtcStateCode];

        return $countryCode . '-' . $stateCode;
    }

    /**
     * Finds the corresponding xt:Commerce 3 state code (zone_code) for a given Shopgate state code
     *
     * @param string $shopgateStateCode The Shopgate state code as defined at Shopgate Wiki
     *
     * @return string The zone code for xt:Commerce 3
     *
     * @throws ShopgateLibraryException if the given code is unknown
     */
    public static function getXtcStateCode($shopgateStateCode)
    {
        $splitCodes = null;
        preg_match('/^([A-Z]{2})\-([A-Z]{2})$/', $shopgateStateCode, $splitCodes);

        if (empty($splitCodes) || empty($splitCodes[1]) || empty($splitCodes[2])) {
            return null;
        }

        if (!isset(self::$stateCodesByCountryCode[$splitCodes[1]])
            || !isset(self::$stateCodesByCountryCode[$splitCodes[1]][$splitCodes[2]])
        ) {
            //throw new ShopgateLibraryException(ShopgateLibraryException::PLUGIN_UNKNOWN_STATE_CODE, 'Code: '.$shopgateStateCode);
            return $splitCodes[2];
        } else {
            return self::$stateCodesByCountryCode[$splitCodes[1]][$splitCodes[2]];
        }
    }
}
