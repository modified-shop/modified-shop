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
    public function getReviewQuery($limit = null, $offset = null, $uids = array())
    {
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
            ((count($uids) > 0) ? " AND r.reviews_id IN (" . implode(',', $uids) . ")" : "")
            . " ORDER BY r.products_id ASC" . (!empty($limit) && !empty($offset) ? " LIMIT $offset,$limit" : "");
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
