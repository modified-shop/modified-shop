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
class ShopgateCategoryXmlModel extends ShopgateCategoryModel
{
    
    public function setUid()
    {
        parent::setUid($this->item['category_number']);
    }
    
    public function setSortOrder()
    {
        parent::setSortOrder($this->item['order_index']);
    }
    
    public function setParentUid()
    {
        parent::setParentUid($this->item["parent_id"]);
    }
    
    public function setIsActive()
    {
        parent::setIsActive($this->item['is_active']);
    }
    
    public function setName()
    {
        parent::setName($this->item['category_name']);
    }
    
    public function setDeeplink()
    {
        parent::setDeeplink($this->item['url_deeplink']);
    }
    
    public function setImage()
    {
        $image = new Shopgate_Model_Media_Image();
        if ($this->item["url_image"]) {
            $image->setUid(1);
            $image->setSortOrder(1);
            $image->setUrl($this->item["url_image"]);
            $image->setTitle($this->item["category_name"]);
        }
        
        parent::setImage($image);
    }
}
