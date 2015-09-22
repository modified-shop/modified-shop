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
class ShopgateCategoryModel extends Shopgate_Model_Catalog_Category
{
    
    /**
     * @var int
     */
    private $languageId;
    
    /**
     * @param mixed $languageId
     */
    public function setLanguageId($languageId)
    {
        $this->languageId = $languageId;
    }
    
    /**
     * get child categories to a parent category
     * 
     * @param $parentId
     *
     * @return string
     */
    public function getCategoriesByParentQuery($parentId)
    {
        return "SELECT DISTINCT
				c.categories_id,
				c.parent_id,
				c.categories_image,
				c.categories_status,
				c.sort_order,
				cd.categories_name
			FROM " . TABLE_CATEGORIES . " c
			LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (c.categories_id = cd.categories_id
			AND cd.language_id = $this->languageId)
			WHERE c.parent_id = $parentId ORDER BY c.categories_id ASC";
    }
    
    /**
     * export the virtual category "new products"
     * 
     * @param $categoryId
     *
     * @return array
     */
    public function getNewProductsCategoryData($categoryId)
    {
        return array(
            "parent_id"       => '',
            "category_number" => $categoryId,
            "category_name"   => MODULE_PAYMENT_SHOPGATE_LABEL_NEW_PRODUCTS,
            "is_active"       => 1,
            "url_deeplink"    => xtc_href_link('products_new.php')
        );
    }
    
    /**
     * export the virtual category "special products"
     * 
     * @param $categoryId
     *
     * @return array
     */
    public function getSpecialProductsCategoryData($categoryId)
    {
        return array(
            "parent_id"       => '',
            "category_number" => $categoryId,
            "category_name"   => MODULE_PAYMENT_SHOPGATE_LABEL_SPECIAL_PRODUCTS,
            "is_active"       => 1,
            "url_deeplink"    => xtc_href_link('specials.php')
        );
    }
    
    /**
     * get the maximum sort value for categories from the database
     *
     * @param $hasReverseSortOrder
     *
     * @return int
     */
    public function getCategoryMaxOrder($hasReverseSortOrder)
    {
        if ($hasReverseSortOrder) {
            $maxOrder = 0;
        } else {
            $qry      = "SELECT MAX( sort_order ) sort_order FROM " . TABLE_CATEGORIES;
            $result   = xtc_db_query($qry);
            $maxOrder = xtc_db_fetch_array($result);
            $maxOrder = $maxOrder["sort_order"] + 1;
        }
        
        return $maxOrder;
    }
}
