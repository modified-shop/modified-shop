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
class ShopgateReviewModel extends Shopgate_Model_Catalog_Review
{
    /**
     * @var int $languageId
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
     * generates the database query to get the review data for export
     *
     * @param null $limit
     * @param null $offset
     *
     * @return string
     */
    public function getReviewQuery($limit = null, $offset = null,
        $uids = array()
    ) {
        return
            "SELECT
                r.reviews_id,
                r.products_id,
                r.customers_name,
                r.reviews_rating,
                r.date_added,
                rd.reviews_text
            FROM
            " . TABLE_REVIEWS . " as r
            INNER JOIN
            " . TABLE_REVIEWS_DESCRIPTION . " as rd ON r.reviews_id = rd.reviews_id
            WHERE rd.languages_id = '" . $this->languageId . "'" .
            ((count($uids) > 0) ? " AND r.reviews_id IN (" . implode(',', $uids)
                . ")" : "")
            . " ORDER BY r.products_id ASC" . (!empty($limit) && !empty($offset)
                ? " LIMIT $offset,$limit" : "");
    }

    /**
     * calculates shopgate score from shop score
     *
     * @param int $shopScore
     *
     * @return int
     */
    public function buildScore($shopScore)
    {
        return intval($shopScore * 2);
    }

    /**
     * returns a Shopgate review title from review text
     *
     * @param string $text
     *
     * @return string
     */
    public function buildTitle($text)
    {
        return substr($text, 0, 20) . "";
    }

    /**
     * returns a Shopgate time string
     *
     * @param string $date
     *
     * @return string
     */
    public function buildDate($date)
    {
        return empty($date) ? "" : strftime("%Y-%m-%d", strtotime($date));
    }
}
