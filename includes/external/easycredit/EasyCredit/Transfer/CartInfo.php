<?php


namespace EasyCredit\Transfer;

/**
 * Class CartInfo
 *
 * @package EasyCredit\Transfer
 */
class CartInfo extends AbstractObject
{
    /**
     * @var string
     * @apiName produktbezeichnung
     */
    protected $name;

    /**
     * @var integer
     * @apiName menge
     */
    protected $quantity;

    /**
     * @var float
     * @apiName preis
     */
    protected $price;

    /**
     * @var string
     * @apiName hersteller
     */
    protected $manufacture;

    /**
     * @var string
     * @apiName produktkategorie
     */
    protected $category;

    /**
     * @var ArticleIdCollection
     * @apiName       artikelnummern
     * @transferClass EasyCredit\Transfer\ArticleIdCollection
     */
    protected $articleId;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getManufacture()
    {
        return $this->manufacture;
    }

    /**
     * @param string $manufacture
     */
    public function setManufacture($manufacture)
    {
        $this->manufacture = $manufacture;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return ArticleIdCollection
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * @param ArticleIdCollection $articleId
     */
    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;
    }
}
