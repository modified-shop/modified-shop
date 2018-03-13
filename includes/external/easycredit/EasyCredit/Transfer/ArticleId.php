<?php


namespace EasyCredit\Transfer;

/**
 * Class ArticleId
 *
 * @package EasyCredit\Transfer
 */
class ArticleId extends AbstractObject
{

    /**
     * @const
     */
    const TYPE_EAN = "EAN";
    
    /**
     * @const
     */
    const TYPE_SKU = "SKU";
    
    /**
     * @const
     */
    const TYPE_GTIN = "GTIN";
    
    /**
     * @var string
     * @apiName nummerntyp
     */
    protected $type;


    /**
     * @var string
     * @apiName nummer
     */
    protected $id;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
