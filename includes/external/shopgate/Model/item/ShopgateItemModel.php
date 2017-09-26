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
class ShopgateItemModel extends Shopgate_Model_Catalog_Product
{

    /**
     * @var ShopgateLogger $log
     */
    private $log;

    /**
     * @var int
     */
    private $languageId;

    /**
     * @var int
     */
    private $defaultCustomerPriceGroup;

    /**
     * @var null|int
     */
    private $exportOffset = null;
    /**
     * @var null|int
     */
    private $exportLimit = null;

    /**
     * @var Shopgate_Helper_String $stringHelper
     */
    private $stringHelper;

    /**
     * @var bool
     */
    private $reverseItemSortOrder;

    /**
     * @var ShopgateConfigModified
     */
    protected $config;

    /**
     * @var int
     */
    protected $countryId;

    /**
     * @var int
     */
    protected $zoneId;

    /**
     * @var int
     */
    protected $exchangeRate;

    /**
     * @var array
     */
    protected $currencyData;

    /**
     * @var string
     */
    protected $modifiedVersion = '';

    const SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_OTHER      = 0;
    const SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_TEXT_FIELD = 1;
    const SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_ALL        = 2;

    /**
     * ShopgateItemModel constructor.
     *
     * @param ShopgateConfigModified $config
     */
    public function __construct(ShopgateConfigModified $config)
    {
        $this->config = $config;
        parent::__construct();
    }

    /**
     * @param int $currencyData
     */
    public function setCurrencyData($currencyData)
    {
        $this->currencyData = $currencyData;
    }

    /**
     * @param string $modifiedVersion
     */
    public function setModifiedVersion($modifiedVersion)
    {
        $this->modifiedVersion = $modifiedVersion;
    }

    /**
     * @param mixed $log
     */
    public function setLog($log)
    {
        $this->log = $log;
    }

    /**
     * @param Shopgate_Helper_String $stringHelper
     */
    public function setStringHelper($stringHelper)
    {
        $this->stringHelper = $stringHelper;
    }

    /**
     * @param mixed $languageId
     */
    public function setLanguageId($languageId)
    {
        $this->languageId = $languageId;
    }

    /**
     * @param mixed $defaultCustomerPriceGroup
     */
    public function setDefaultCustomerPriceGroup($defaultCustomerPriceGroup)
    {
        $this->defaultCustomerPriceGroup = $defaultCustomerPriceGroup;
    }

    /**
     * @param null|int $exportOffset
     */
    public function setExportOffset($exportOffset)
    {
        $this->exportOffset = $exportOffset;
    }

    /**
     * @param null|int $exportLimit
     */
    public function setExportLimit($exportLimit)
    {
        $this->exportLimit = $exportLimit;
    }

    /**
     * @param mixed $reverseItemSortOrder
     */
    public function setReverseItemSortOrder($reverseItemSortOrder)
    {
        $this->reverseItemSortOrder = $reverseItemSortOrder;
    }

    /**
     * @param int $countryId
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;
    }

    /**
     * @param int $zoneId
     */
    public function setZoneId($zoneId)
    {
        $this->zoneId = $zoneId;
    }

    /**
     * @param int $exchangeRate
     */
    public function setExchangeRate($exchangeRate)
    {
        $this->exchangeRate = $exchangeRate;
    }

    /**
     * generate the query to get all needed data for the product export
     *
     * @return string
     */
    public function getProductQuery($uids)
    {
        $this->log("generate SQL get products ...", ShopgateLogger::LOGTYPE_DEBUG);

        $manufacturerColumn = ShopgateItemHelper::manufacturerColumnAvailable($this->modifiedVersion)
            ? "p.products_manufacturers_model,"
            : "";

        $qry = "
            SELECT DISTINCT
                p.products_id,
                p.products_model,
                p.products_ean,
                p.products_quantity,
                p.products_image,
                p.products_price,
                DATE_FORMAT(p.products_last_modified, '%Y-%m-%d') AS products_last_modified,
                p.products_weight,
                p.products_status,
                sp.specials_new_products_price,
                sp.specials_quantity,
                pdsc.products_keywords,
                pdsc.products_name,
                pdsc.products_description,
                pdsc.products_short_description,
                shst.shipping_status_name,
                mf.manufacturers_id,
                mf.manufacturers_name,
                " . $manufacturerColumn . "
                p.products_tax_class_id,
                p.products_fsk18,
                p.products_vpe_status,
                p.products_vpe_value,
                vpe.products_vpe_name,
                p.products_sort,
                p.products_startpage,
                p.products_startpage_sort,
                p.products_discount_allowed,
                p.products_date_available
            FROM " . TABLE_PRODUCTS . " p
            LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION
            . " pdsc ON (p.products_id = pdsc.products_id AND pdsc.language_id = '" . $this->languageId . "')
            LEFT JOIN " . TABLE_SHIPPING_STATUS
            . " shst ON (p.products_shippingtime = shst.shipping_status_id AND shst.language_id = '" . $this->languageId
            . "')
            LEFT JOIN " . TABLE_MANUFACTURERS . " mf ON (mf.manufacturers_id = p.manufacturers_id)
            LEFT JOIN " . TABLE_SPECIALS . " sp ON (sp.products_id = p.products_id AND sp.status = 1 AND (sp.expires_date > now() OR sp.expires_date = '0000-00-00 00:00:00' OR sp.expires_date IS NULL))
            LEFT JOIN " . TABLE_PRODUCTS_VPE . " vpe ON (vpe.products_vpe_id = p.products_vpe AND vpe.language_id = pdsc.language_id)
            WHERE p.products_status = 1
            ";

        // Code for enabling to download specific products (for debugging purposes only, at this time)
        if (!empty($uids) && is_array($uids)) {
            $qry .= " AND p.products_id IN ('" . implode("', '", $uids) . "') ";
        }

        // Ahorn24 fix. 10 products were not found without sorting.
        $qry .= ' ORDER BY p.products_id ASC ';

        if (!is_null($this->exportLimit) && !is_null($this->exportOffset)) {
            $qry .= " LIMIT {$this->exportOffset}, {$this->exportLimit}";
        }

        return $qry;
    }

    /**
     * get the highest product position
     *
     * @return int
     */
    public function getMaxProductSortPosition()
    {
        $this->log('execute SQL get max sort position ...', ShopgateLogger::LOGTYPE_DEBUG);
        /** @noinspection SqlDialectInspection */
        $result = xtc_db_query("SELECT MAX(products_sort) max_sort FROM " . TABLE_PRODUCTS);
        $maxId  = xtc_db_fetch_array($result);

        return (int)isset($maxId['max_sort']) ? $maxId['max_sort'] : 0;
    }

    /**
     * fill the referenced variables with the right data
     *
     * @param $maxOrder int maximum order index
     * @param $minOrder int minimum order index
     * @param $addToOrderIndex
     */
    public function getProductOrderValues(&$maxOrder, &$minOrder, &$addToOrderIndex)
    {
        $this->log("execute SQL min_order, max_order ...", ShopgateLogger::LOGTYPE_DEBUG);
        // order_index for the products
        $result          = xtc_db_query(
            "SELECT MIN(products_sort) AS 'min_order', MAX(products_sort) AS 'max_order' FROM " . TABLE_PRODUCTS
        );
        $orderIndices    = xtc_db_fetch_array($result);
        $maxOrder        = $orderIndices["max_order"] + 1;
        $minOrder        = $orderIndices["min_order"];
        $addToOrderIndex = 0;

        if ($minOrder < 0) {
            // make the sort_order positive
            $addToOrderIndex += abs($minOrder);
        }
    }

    /**
     * get a shop systems product from the database regarding the id
     *
     * @param int $productId
     *
     * @return array|bool|mixed
     */
    public function getProductById($productId)
    {
        $productQuery    = "select p.products_tax_class_id,
                                          p.products_id,
                                          pd.products_name,
                                          p.products_price,
                                          sp.specials_quantity,
                                          sp.specials_new_products_price,
                                          sp.expires_date
                                        from products p
                                            LEFT JOIN products_description pd ON p.products_id = pd.products_id AND pd.language_id = {$this->languageId}
                                            LEFT JOIN specials sp ON  (sp.products_id = p.products_id AND sp.status = 1 AND (sp.expires_date > now() OR sp.expires_date = '0000-00-00 00:00:00' OR sp.expires_date IS NULL))
                                        where p.products_id = {$productId}";
        $dbProductResult = xtc_db_query($productQuery);
        $dbProduct       = xtc_db_fetch_array($dbProductResult);

        return $dbProduct;
    }


    /**
     * if the current order item (product) is an child product the item number is
     * generated in the schema <productId>_<attributeId>
     *
     * @param ShopgateOrderItem $sgOrderItem
     *
     * @return int
     */
    public function getProductIdFromOrderItem(ShopgateOrderItem $sgOrderItem)
    {
        $parentId = $sgOrderItem->getParentItemNumber();
        if (empty($parentId)) {
            $id = $sgOrderItem->getItemNumber();
            if (strpos($id, "_") !== false) {
                $productIdArr = explode('_', $id);

                return $productIdArr[0];
            }

            return $id;
        }

        return $parentId;
    }

    /**
     * get the attribute data to an product from the database and fill the ShopgateOrderItemAttribute with
     * the data as it is
     *
     * @param ShopgateOrderItem $item
     *
     * @return array
     */
    public function getAttributesToProduct(ShopgateOrderItem $item)
    {
        $attributes   = $item->getAttributes();
        $dbAttributes = array();
        foreach ($attributes as $attribute) {

            $query = "SELECT
                            po.products_options_name AS `name`,
                            pov.products_options_values_name AS `value`
                        FROM products AS p
                            LEFT JOIN products_attributes AS pa ON p.products_id = pa.products_id
                            LEFT JOIN products_options AS po ON (pa.options_id = po.products_options_id AND po.language_id = 1)
                            LEFT JOIN products_options_values AS pov ON (pa.options_values_id = pov.products_options_values_id AND po.language_id = pov.language_id)
                        WHERE 
                            pa.products_id = {$this->getProductIdFromOrderItem($item)} 
                        AND po.products_options_name = '{$attribute->getName()}' 
                        AND pov.products_options_values_name = '{$attribute->getValue()}'";

            $result = ShopgateWrapper::db_query($query);

            while ($dbProductAttributes = ShopgateWrapper::db_fetch_array($result)) {
                $sgAttribute = new ShopgateOrderItemAttribute();
                $sgAttribute->setName($dbProductAttributes["option_name"]);
                $sgAttribute->setValue($dbProductAttributes["value"]);
                $dbAttributes[] = $sgAttribute;
            }
        }

        return $dbAttributes;
    }

    /**
     * get the option data to an product from the database
     *
     * @param int   $productId
     * @param array $sgOrderInfo
     * @param int   $taxRate
     *
     * @return array
     */
    public function getOptionsToProduct($productId, $sgOrderInfo, $taxRate)
    {
        $resultAttributes = array();
        $attributeIds     = ShopgateItemHelper::filterOptionsFromOrderInfo($sgOrderInfo);

        foreach ($attributeIds as $attributeId) {
            $query = "SELECT
                        o.products_options_id AS `options_id`,
                        ov.products_options_values_id AS `values_id`,
                        ov.products_options_values_name AS `values_name`,
                        pa.price_prefix AS `prefix`,
                        pa.options_values_price AS `price`,
                        o.products_options_name AS `name`
                    FROM products AS p
                        LEFT JOIN products_attributes         AS pa ON p.products_id = pa.products_id
                        LEFT JOIN products_options             AS o  ON o.products_options_id = pa.options_id AND o.language_id = {$this->languageId}
                        LEFT JOIN products_options_values     AS ov ON (ov.products_options_values_id = pa.options_values_id AND o.language_id = ov.language_id)
                    WHERE     p.products_id                         = {$productId} AND
                            pa.products_attributes_id             = {$attributeId["products_attributes_id"]} AND
                            o.products_options_id                 = {$attributeId["options_id"]} AND
                            ov.products_options_values_id     = {$attributeId["options_values_id"]}";

            $result       = xtc_db_query($query);
            $optionResult = xtc_db_fetch_array($result);
            $sgOption     = new ShopgateOrderItemOption();
            $sgOption->setName($this->stringToUtf8($optionResult["name"]));
            $sgOption->setOptionNumber($optionResult["options_id"]);
            $sgOption->setValue(
                $this->stringToUtf8($optionResult["values_name"])
            );
            $sgOption->setValueNumber($optionResult["values_id"]);

            if (!empty($optionResult["prefix"])) {
                $price =
                    ($optionResult["prefix"] == "-") ? ($optionResult["price"]
                        * (-1)) : $optionResult["price"];
            } else {
                $price = $optionResult["price"];
            }

            $sgOption->setAdditionalAmountWithTax(
                $price * (1 + ($taxRate / 100))
            );
            $resultAttributes[] = $sgOption;
        }

        return $resultAttributes;
    }

    /**
     * get all customer as they are in the shop system
     *
     * @return mixed
     */
    public function getCustomerGroups()
    {
        // get customer-group first
        $qry = "SELECT"
            . " status.customers_status_name,"
            . " status.customers_status_discount,"
            . " status.customers_status_discount_attributes"
            . " FROM " . TABLE_CUSTOMERS_STATUS . " AS status"
            . " WHERE status.customers_status_id = " . $this->defaultCustomerPriceGroup
            . " AND status.language_id = " . $this->languageId
            . ";";

        // Check if the customer group exists (ignore if not)
        return xtc_db_query($qry);
    }

    /**
     * fill the referenced variables with the discount data to customer groups
     *
     * @param string $customerGroupMaxPriceDiscount
     * @param string $customerGroupDiscountAttributes
     */
    public function getDiscountToCustomerGroups(&$customerGroupMaxPriceDiscount, &$customerGroupDiscountAttributes)
    {
        if ($queryResult = $this->getCustomerGroups()) {
            $customerGroupResult = xtc_db_fetch_array($queryResult);
            if (!empty($customerGroupResult) && isset($customerGroupResult['customers_status_discount'])) {
                $customerGroupMaxPriceDiscount = $customerGroupResult['customers_status_discount'];
            }
            if (!empty($customerGroupResult) && isset($customerGroupResult['customers_status_discount'])) {
                $customerGroupDiscountAttributes =
                    $customerGroupResult['customers_status_discount_attributes'] ? true : false;
            }
        }
    }

    /**
     * returns all sub categories including the given parent as a list that is a mapping
     * from one category to a higher category if a given depth is exceeded
     *
     * @param int $maxDepth
     * @param int $parentId
     * @param int $copyId
     * @param int $depth
     *
     * @throws ShopgateLibraryException
     * @return array
     */
    public function getCategoryReducementMap($maxDepth = null, $parentId = null, $copyId = null, $depth = null)
    {
        $this->log("execute _getCategoryReducementMap() ...", ShopgateLogger::LOGTYPE_DEBUG);

        $circularDepthStop = 50;
        if (empty($depth)) {
            $depth = 1;
        } elseif ($depth > $circularDepthStop) {// disallow circular category connections (detect by a maximum depth)
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                'error on loading sub-categories: Categories-Depth exceedes a value of ' . $circularDepthStop .
                '. Check if there is a circular connection (referenced categories ids: ' . $parentId . '=>', true
            );
        }

        // select by parent id, if set
        $qry = "SELECT `categories_id` FROM `" . TABLE_CATEGORIES . "` WHERE" .
            (!empty($parentId) ? " (`parent_id` = '{$parentId}')"
                : " (`parent_id` IS NULL OR `parent_id` = 0 OR `parent_id` = '')");

        $qryResult = xtc_db_query($qry);
        if (!$qryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR, 'error on selecting categories', true
            );
        }

        // add all sub categories to a simple one-dimensional array
        $categoryMap = array();
        while ($row = xtc_db_fetch_array($qryResult)) {
            // copy only if a maximum depth is set, yet
            if (!empty($maxDepth)) {
                if ($depth == $maxDepth) {
                    $copyId = $row['categories_id'];
                }
            }
            // Check if a mapping to a higher category needs to be applied
            if (!empty($copyId) && !empty($row['categories_id'])) {
                $categoryMap[$row['categories_id']] = $copyId;
            } else {
                // no mapping to other categories, map to itself!
                $categoryMap[$row['categories_id']] = $row['categories_id'];
            }

            $subCategories = $this->getCategoryReducementMap($maxDepth, $row['categories_id'], $copyId, $depth + 1);
            if (!empty($subCategories)) {
                $categoryMap = $categoryMap + $subCategories;
            }
        }

        return $categoryMap;
    }

    /**
     * get all products to new categories
     *
     * the shop systems constant "MAX_DISPLAY_NEW_PRODUCTS_DAYS" contains the data how much days the product
     * needs to be displayed
     *
     * @return string
     */
    public function getProductsToNewCategoryQuery()
    {
        $group_check              = '';
        $fsk_lock                 = '';
        $_SESSION['languages_id'] = $this->languageId;
        //logic taken from file products_new.php in dir /
        $date_new_products =
            date("Y-m-d", mktime(1, 1, 1, date("m"), date("d") - MAX_DISPLAY_NEW_PRODUCTS_DAYS, date("Y")));
        $days              = " and p.products_date_added > '" . $date_new_products . "' ";

        return "SELECT DISTINCT
                                    p.products_id,
                                    p.products_fsk18,
                                    pd.products_name,
                                    pd.products_short_description,
                                    p.products_image,
                                    p.products_price,
                                    p.products_vpe,
                                    p.products_vpe_status,
                                    p.products_vpe_value,
                                    p.products_tax_class_id,
                                    p.products_shippingtime,
                                    p.products_date_added,
                                    m.manufacturers_name
                            FROM " . TABLE_PRODUCTS . " p
                            LEFT JOIN " . TABLE_MANUFACTURERS . " m
                            ON p.manufacturers_id = m.manufacturers_id
                            LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                    ON p.products_id = pd.products_id,
                                    " . TABLE_CATEGORIES . " c,
                                    " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                            WHERE pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                            AND c.categories_status=1
                            AND p.products_id = p2c.products_id
                            AND c.categories_id = p2c.categories_id
                            AND products_status = '1'
                                " . $group_check . "
                                " . $fsk_lock . "
                                " . $days . "
                            ORDER BY p.products_date_added DESC ";
    }

    /**
     * get products data from the database including special prices
     *
     * @return string
     */
    public function getProductsToSpecialCategoryQuery()
    {
        $group_check              = '';
        $fsk_lock                 = '';
        $_SESSION['languages_id'] = $this->languageId;

        //logic taken from file specials.php in dir /
        return "SELECT DISCTINCT
                    pd.products_name,
                    p.products_price,
                    p.products_id,
                    p.products_tax_class_id,
                    p.products_shippingtime,
                    p.products_image,
                    p.products_vpe_status,
                    p.products_vpe_value,
                    p.products_vpe,
                    p.products_fsk18,
                    s.expires_date,
                    s.specials_new_products_price
                FROM " . TABLE_PRODUCTS . " p
                    LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id = pd.products_id
                    LEFT JOIN " . TABLE_SPECIALS . " s ON p.products_id=s.products_id
                WHERE 
                    p.products_status='1'
                AND s.products_id=p.products_id
                AND p.products_id=pd.products_id " . $group_check . "  " . $fsk_lock . " 
                AND pd.language_id='" . (int)$_SESSION['languages_id'] . "'
                AND s.status='1'
                ORDER BY s.specials_date_added DESC";
    }

    /**
     * generates a query to get different type of html elements represented by attributes
     *
     * there are two different types:
     *
     *   - text field:  SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_TEXT_FIELD
     *   - other types: SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_OTHER
     *
     * the other types can be a checkbox, radio buttons etc.
     *
     * @param int    $productId
     * @param string $type
     * @param string $optionsAsInputFields Comma-separated list of option IDs that should be exported as input fields
     *
     * @return string|void
     */
    private function getAttributeQuery($productId, $type, $optionsAsInputFields = '')
    {
        $optionsAsInputFields = trim($optionsAsInputFields, ',');
        $optionsAsInputFields = (!empty($optionsAsInputFields))
            ? 'AND ({$condition} (' . $optionsAsInputFields . '))'
            : '';

        switch ($type) {
            case self::SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_TEXT_FIELD:
                $optionsAsInputFields = empty($optionsAsInputFields)
                    ? ' AND pov.products_options_values_name = \'TEXTFELD\' '
                    : str_replace(
                        '{$condition}',
                        'pov.products_options_values_name = \'TEXTFELD\' OR pa.options_id IN',
                        $optionsAsInputFields
                    );
                $query                = $optionsAsInputFields . " ORDER BY po.products_options_id, pa.sortorder";
                break;

            case self::SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_OTHER:
                $optionsAsInputFields = empty($optionsAsInputFields)
                    ? ' AND pov.products_options_values_name != \'TEXTFELD\' '
                    : str_replace(
                        '{$condition}',
                        'pov.products_options_values_name != \'TEXTFELD\' OR pa.options_id NOT IN',
                        $optionsAsInputFields
                    );
                $query                = $optionsAsInputFields . " ORDER BY po.products_options_id, pa.sortorder";
                break;

            case self::SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_ALL:
                $optionsAsInputFields = str_replace('{$condition}', '', $optionsAsInputFields);
                $query                = $optionsAsInputFields . " ORDER BY po.products_options_id, pa.sortorder ASC";
                break;

            default:
                return "";
                break;
        }

        return "SELECT
                    pa.products_attributes_id,
                    pa.sortorder,
                    po.products_options_id,
                    pov.products_options_values_id,
                    po.products_options_name,
                    pov.products_options_values_name,
                    pa.attributes_model,
                    pa.options_values_price,
                    pa.price_prefix,
                    pa.options_values_weight,
                    pa.attributes_stock,
                    pa.attributes_ean,
                    pa.attributes_model,
                    pa.weight_prefix
            FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa
            INNER JOIN " . TABLE_PRODUCTS_OPTIONS . " po ON (pa.options_id = po.products_options_id AND po.language_id = {$this->languageId})
            INNER JOIN " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ON (pa.options_values_id = pov.products_options_values_id AND pov.language_id = {$this->languageId})
            WHERE pa.products_id = '" . $productId . "' " . $query;
    }

    /**
     * wrapper function which returns all attributes to an
     * product of the type SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_OTHER
     *
     * @param int    $productId
     * @param string $optionsAsInputFields Comma-separated list of option IDs that should be exported as input fields
     *
     * @return string|void
     */
    public function getAttributesToProductQuery($productId, $optionsAsInputFields = '')
    {
        return $this->getAttributeQuery($productId, self::SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_OTHER, $optionsAsInputFields);
    }

    /**
     * wrapper function which returns all attributes to an
     * product of the type SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_TEXT_FIELD
     *
     * @param int    $productId
     * @param string $optionsAsInputFields Comma-separated list of option IDs that should be exported as input fields
     *
     * @return string|void
     */
    public function getAttributesInputFieldsToProductsQuery($productId, $optionsAsInputFields = '')
    {
        return $this->getAttributeQuery(
            $productId, self::SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_TEXT_FIELD, $optionsAsInputFields
        );
    }

    /**
     * generate a query to get all attributes to an product
     *
     * @param int $productId
     *
     * @return string|void
     */
    public function getAllAttributesToProductsQuery($productId)
    {
        return $this->getAttributeQuery($productId, self::SHOPGATE_PRODUCT_ATTRIBUTE_TYPE_ALL);
    }

    /**
     * generate a query to get all images to an product
     *
     * @param int $productId
     *
     * @return string
     */
    private function getImagesToProductQuery($productId)
    {
        return "SELECT *
                FROM " . TABLE_PRODUCTS_IMAGES . "
                WHERE products_id = '" . $productId . "'
                ORDER BY image_nr";
    }

    /**
     * using the shop systems constants to generate the local image path
     *
     * @return string
     */
    private function getLocalMainImagePath()
    {
        return DIR_FS_CATALOG . DIR_WS_ORIGINAL_IMAGES;
    }

    /**
     * using the shop systems constants to generate the url where the image is reachable
     *
     * @return string
     */
    private function getMainImageUrl()
    {
        return HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_ORIGINAL_IMAGES;
    }

    /**
     * generate locale image path
     *
     * @return string
     */
    private function getLocalThumbImagePath()
    {
        return DIR_FS_CATALOG . DIR_WS_POPUP_IMAGES;
    }

    /**
     * generate image url path
     *
     * @return string
     */
    private function getThumbImageUrl()
    {
        return HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_POPUP_IMAGES;
    }

    /**
     * generates the image local path and the url where to one product.
     *
     * @param array $product
     *
     * @return array
     */
    public function generateImageUrls($product)
    {
        $images = array();
        if (!empty($product['products_image'])) {
            if (file_exists($this->getLocalMainImagePath() . $product['products_image'])) {
                $images[] = $this->getMainImageUrl() . $product['products_image'];
            } elseif (file_exists($this->getLocalThumbImagePath() . $product['products_image'])) {
                $images[] = $this->getThumbImageUrl() . $product['products_image'];
            }
        }

        $query = xtc_db_query($this->getImagesToProductQuery($product["products_id"]));
        while ($image = xtc_db_fetch_array($query)) {
            if (file_exists($this->getLocalMainImagePath() . $image['image_name'])) {
                $images[] = $this->getMainImageUrl() . $image['image_name'];
            } elseif (file_exists($this->getLocalThumbImagePath() . $image['image_name'])) {
                $images[] = $this->getThumbImageUrl() . $image['image_name'];
            }
        }

        return $images;
    }

    /**
     * generate the deep link to a product using the shop systems functions
     *
     * @param int    $productId
     * @param string $productName
     *
     * @return mixed
     */
    public function generateDeepLinkToProduct($productId, $productName)
    {
        return xtc_href_link('product_info.php', xtc_product_link($productId, $productName), 'NONSSL', false);
    }

    /**
     * calculate the products price as the shop system does
     *
     * @param array  $item
     * @param string $tax_rate
     * @param string $customerGroupMaxPriceDiscount
     * @param string $price
     * @param string $oldPrice
     */
    public function calculateProductPrice(
        $item, $tax_rate, $customerGroupMaxPriceDiscount, &$price, &$oldPrice
    ) {
        // Special offers for a Customer group
        $pOffers = $this->getPersonalOffersPrice($item, $tax_rate);
        if (!empty($pOffers) && round($pOffers, 2) > 0) {
            $price = $pOffers;
            // Ignore the "old price" if it is lower than the offer amount (xtc3 also tells the old price here, but it's not very intuitive)
            if ($pOffers < $item["products_price"]) {
                $oldPrice = $item["products_price"];
            }
        }

        // General special offer or customer group price reduction
        if (!empty($item["specials_new_products_price"])) {
            if (STOCK_CHECK == 'true' && STOCK_ALLOW_CHECKOUT == 'false') {
                if ($item["specials_quantity"] > 0) {
                    // Nur wenn die quantity > 0 ist dann specialprice setzen, ansonsten normalen Preis mit normalem Stock
                    $item["products_quantity"] =
                        $item["specials_quantity"] > $item["products_quantity"] ? $item["products_quantity"]
                            : $item["specials_quantity"];
                }
            }
            // setting specialprice
            $oldPrice = $item["products_price"];
            $price    = $item["specials_new_products_price"];

            $orderInfos['is_special_price'] = 1;

        } elseif (!empty($customerGroupMaxPriceDiscount) && round($customerGroupMaxPriceDiscount, 2) > 0
            && !empty($item['products_discount_allowed'])
            && round($item['products_discount_allowed'], 2) > 0
        ) {
            $productDiscount = round($item['products_discount_allowed'], 2);

            // Limit discount to the customer groups maximum discount
            if (round($customerGroupMaxPriceDiscount, 2) < $productDiscount) {
                $productDiscount = round($customerGroupMaxPriceDiscount, 2);
            }

            $oldPrice = $price;
            if ($oldPrice < $item['products_price']) {
                $oldPrice = $item['products_price'];
            }

            // Reduce price to the discounted price
            $price = $this->getDiscountPrice($price, $productDiscount);
        }
    }

    /**
     * Takes a price value and a discount percent value and returns the new discounted price
     *
     * @param string $price
     * @param string $discountPercent
     *
     * @return float
     */
    public function getDiscountPrice($price, $discountPercent)
    {
        $discountedPrice = $price * (1 - $discountPercent / 100);

        return $discountedPrice;
    }

    /**
     * get the offer price to an product from the database
     *
     * @param mixed[] $product
     * @param mixed[] $tax
     *
     * @return float
     */
    private function getPersonalOffersPrice($product, $tax)
    {
        $this->log("execute _getPersonalOffersPrice() ...", ShopgateLogger::LOGTYPE_DEBUG);

        $customerStatusId = $this->defaultCustomerPriceGroup;
        if (empty($customerStatusId)) {
            return false;
        }

        $qry = "SELECT * FROM " . TABLE_PERSONAL_OFFERS_BY . "$customerStatusId
        WHERE products_id = '" . $product["products_id"] . "'
        AND quantity = 1";

        $qry = xtc_db_query($qry);
        if (!$qry) {
            return false;
        }

        $specialOffer = xtc_db_fetch_array($qry);

        return floatval($specialOffer["personal_offer"]);
    }


    /**
     * generate the description regarding the description settings from the shop system
     *
     * @param array  $item
     * @param string $descriptionType
     *
     * @return mixed
     */
    public function getDescriptionToProduct($item, $descriptionType)
    {
        // create the description, based on the settings
        $desc        = $this->stringHelper->removeTagsFromString($item["products_description"]);
        $shortDesc   = $this->stringHelper->removeTagsFromString($item["products_short_description"]);
        $description = '';
        switch ($descriptionType) {
            case SHOPGATE_SETTING_EXPORT_DESCRIPTION:
                $description = $desc;
                break;
            case SHOPGATE_SETTING_EXPORT_SHORTDESCRIPTION:
                $description = $shortDesc;
                break;
            case SHOPGATE_SETTING_EXPORT_DESCRIPTION_SHORTDESCRIPTION:
                $description = $desc . "<br/><br/>" . $shortDesc;
                break;
            case SHOPGATE_SETTING_EXPORT_SHORTDESCRIPTION_DESCRIPTION:
                $description = $shortDesc . "<br/><br/>" . $desc;
                break;
        }

        return preg_replace("/\n|\r/", "", $description);
    }

    /**
     * generate properties to an product as sting, concatenated with the delimiter ||
     * (properties can be used as filter e.g. on the mobile page)
     *
     * @param array      $product
     * @param bool|false $asArray
     *
     * @return string|array
     */
    public function generatePropertiesToProduct($product, $asArray = false)
    {
        $properties = array();

        if (!empty($product["products_fsk18"]) && $product["products_fsk18"] == 1) {
            $properties[] = $asArray ? array("label" => "Altersbeschränkung", "value" => "18 Jahre")
                : "Altersbeschränkung=>18 Jahre";
        }

        return $asArray ? $properties : implode("||", $properties);
    }

    /**
     * generates data which contains all category ids which point to a product
     *
     * @param $item array
     *
     * @return array
     */
    public function getProductCategoryNumbers($item)
    {
        $this->log('execute _getProductCategoryNumbers() ...', ShopgateLogger::LOGTYPE_DEBUG);
        $this->getProductOrderValues($maxOrder, $minOrder, $addToOrderIndex);
        $categories = array();
        /** @noinspection SqlDialectInspection */
        $catsQry   = "
            SELECT DISTINCT
                ptc.categories_id,
                c.products_sorting2
            FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc
            INNER JOIN " . TABLE_CATEGORIES . " c ON (ptc.categories_id = c.categories_id)
            WHERE ptc.products_id = '" . $item['products_id'] . "'
                AND c.categories_status = 1
            ";
        $catsQuery = xtc_db_query($catsQry);

        while ($category = xtc_db_fetch_array($catsQuery)) {
            if (empty($category['categories_id'])) {
                continue;
            }

            $catNumber = '';
            if ($category['products_sorting2'] != 'ASC') {
                if ($this->reverseItemSortOrder) {
                    $sort = $this->getMaxProductSortPosition() - $item['products_sort'];
                } else {
                    $sort = $item['products_sort'];
                }
            } else {
                if ($this->reverseItemSortOrder) {
                    $sort = $item['products_sort'];
                } else {
                    $sort = $this->getMaxProductSortPosition() - $item['products_sort'];
                }
            }

            if (!empty($sort) || ((string)$sort === '0')) {
                $sort += $addToOrderIndex;
                $catNumber = '=>' . $sort;
            }
            $catNumber    = $category['categories_id'] . $catNumber;
            $categories[] = $catNumber;
        }

        return $categories;
    }

    /**
     * generates a string containing the xseller id to products, separated by the delimiter ||
     *
     * @param int        $products_id
     * @param bool|false $asArray
     *
     * @return array|string
     */
    public function getRelatedShopItems($products_id, $asArray = false)
    {
        $this->log("execute _getRelatedShopItems() ...", ShopgateLogger::LOGTYPE_DEBUG);
        $qry = "
            SELECT px.xsell_id
            FROM " . TABLE_PRODUCTS_XSELL . " px
            INNER JOIN " . TABLE_PRODUCTS . " p ON (px.products_id = p.products_id)
            WHERE p.products_id = '$products_id'
                AND (p.products_date_available < NOW() OR p.products_date_available IS NULL)
            ORDER BY px.sort_order
        ";

        $xSellIds = array();
        $query    = xtc_db_query($qry);

        while ($row = xtc_db_fetch_array($query)) {
            $xSellIds[] = $row["xsell_id"];
        }

        return $asArray ? $xSellIds : implode("||", $xSellIds);
    }


    /**
     * read the title to an tax class from the database by uid
     *
     * @param int $uid
     *
     * @return array
     *
     * @throws ShopgateLibraryException
     */
    protected function getTaxClassTitleById($uid)
    {
        $sqlQuery    = "SELECT tax_class_title AS title FROM `" . TABLE_TAX_CLASS . "` WHERE tax_class_id={$uid}";
        $queryResult = xtc_db_query($sqlQuery);

        if (!$queryResult) {
            throw new ShopgateLibraryException(
                ShopgateLibraryException::PLUGIN_DATABASE_ERROR,
                "Shopgate Plugin - Error while executing the query: " . $sqlQuery, true
            );
        }

        $result = xtc_db_fetch_array($queryResult);

        return !empty($result) ? $result['title'] : "";
    }

    /**
     * calculate a taxed price
     *
     * @param int    $taxRate
     * @param string $price
     *
     * @return mixed
     */
    protected function addTaxToPrice($taxRate, $price)
    {
        return ($price * (1 + ($taxRate / 100)));
    }

    /**
     * calculate the package unit to an product regarding the price
     *
     * @param array  $product
     * @param string $price
     *
     * @return string
     */
    public function getProductVPE($product, $price)
    {
        $vpe         = "";
        $priceHelper = $this->getHelper(ShopgateObject::HELPER_PRICING);

        if (!empty($product["products_vpe_value"]) && !empty($product["products_vpe_name"])
            && $product["products_vpe_value"] != 0.0000
        ) {

            if ($product["products_vpe_status"] == 1) {

                $factor = 1;
                switch (strtolower($product["products_vpe_name"])) {
                    case "ml":
                    case "mg":
                        // don't know why this logic was create
                        // it's used and there was no failure with it in the past
                        $factor = $product["products_vpe_value"] < 250 ? 100 : 1000;
                        break;
                }

                $_price = ($price / $product["products_vpe_value"]) * $factor;

                $vpe = $this->currencyData["symbol_left"];

                $vpe .= $priceHelper->formatPriceNumber(
                    $_price,
                    $this->currencyData["decimal_places"],
                    $this->currencyData["decimal_point"],
                    $this->currencyData["thousands_point"]
                );

                $vpe .= " " . trim($this->currencyData["symbol_right"]);
                $vpe .= ' pro ' . (($factor == 1) ? '' : $factor . ' ');
                $vpe .= $product["products_vpe_name"];
            }
        }

        return $vpe;
    }

    /**
     * check if the shop settings regard or ignore the products stock
     *
     * @return int
     */
    public function generateUseStock()
    {
        return (STOCK_ALLOW_CHECKOUT == 'true' || STOCK_CHECK != 'true')
            ? 0
            : 1;
    }

    /**
     * Generates an available text based on the date available field
     *
     * @param array  $item
     * @param string $defaultStatusName
     *
     * @return mixed|string
     */
    public function getAvailableText($item = array(), $defaultStatusName = '')
    {
        if (empty($item) || empty($item['shipping_status_name']) && empty($defaultStatusName)) {
            return '';
        }

        if (!empty($defaultStatusName)) {
            $availableText = (string)$defaultStatusName;
        } else {
            $availableText = (string)$item['shipping_status_name'];
        }

        // Check if the product is available in the future
        if (!empty($item['products_date_available'])) {
            // Check if the date is in the future
            $availableOnTimestamp = strtotime(
                substr($item['products_date_available'], 0, 10) . ' 00:00:00'
            ); // Take the date beginning at 00:00:00 o' clock
            // Set the "available on" text only if it is at least one day in the future
            if ($availableOnTimestamp - time() > 60 * 60 * 24) { // 60sec * 60min * 24h == count seconds in 1 day
                switch (strtolower($this->config->getLanguage())) {
                    case 'de':
                        $dateAvailableFormatted = date('d.m.Y', $availableOnTimestamp);
                        break;
                    case 'en':
                    default:
                        $dateAvailableFormatted = date('m/d/Y', $availableOnTimestamp);
                        break;
                }
                $availableText = str_replace(
                    '#DATE#', $dateAvailableFormatted, SHOPGATE_PLUGIN_FIELD_AVAILABLE_TEXT_AVAILABLE_ON_DATE
                );
            }
        }

        // return a default string as fallback
        return $availableText;
    }

    /**
     * calculate the amount of all option combinations
     *
     * @param array $options
     *
     * @return int
     */
    protected function calculateVariationAmountByOptions($options)
    {
        $countVariations = 1;
        foreach ($options as $option) {
            $countVariations *= count($option);
        }

        return $countVariations;
    }

    /**
     * read variation data from the database by the query
     * optionally the cross products of all variations can be generated
     *
     * @param string     $query
     * @param bool|false $asOption
     *
     * @return array|array[][]
     * @throws ShopgateLibraryException
     */
    protected function generateAttributes($query, $asOption = false)
    {
        $variations      = array();
        $variationResult = ShopgateWrapper::db_query($query);

        while ($variation = ShopgateWrapper::db_fetch_array($variationResult)) {
            $variations[$variation['products_options_id']][] = $variation;
        }

        if (!$asOption) {
            $helper   = $this->getHelper(self::HELPER_DATASTRUCTURE);
            $children = $helper->arrayCross($variations);

            return $children;
        }

        return $variations;
    }
}
