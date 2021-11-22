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
            $qry      = "SELECT MAX( sort_order ) sort_order FROM "
                . TABLE_CATEGORIES;
            $result   = xtc_db_query($qry);
            $maxOrder = xtc_db_fetch_array($result);
            $maxOrder = $maxOrder["sort_order"] + 1;
        }

        return $maxOrder;
    }
}
