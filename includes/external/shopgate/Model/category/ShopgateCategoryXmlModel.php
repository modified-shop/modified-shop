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
