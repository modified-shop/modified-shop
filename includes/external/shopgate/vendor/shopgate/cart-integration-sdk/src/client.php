<?php
/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

class ShopgateClient extends ShopgateContainer
{
    const TYPE_MOBILESITE       = 'mobilesite';
    const TYPE_IPHONEAPP        = 'iphoneapp';
    const TYPE_IPADAPP          = 'ipadapp';
    const TYPE_ANDROIDPHONEAPP  = 'androidphoneapp';
    const TYPE_ANDROIDTABLETAPP = 'androidtabletapp';

    /** @var string */
    protected $type;

    /**
     * @param string
     */
    public function setType($data)
    {
        return $this->type = $data;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isMobileWebsite()
    {
        return $this->type == self::TYPE_MOBILESITE;
    }

    /**
     * @return bool
     */
    public function isApp()
    {
        $appTypes = array(
            self::TYPE_ANDROIDPHONEAPP,
            self::TYPE_ANDROIDTABLETAPP,
            self::TYPE_IPADAPP,
            self::TYPE_IPHONEAPP,
        );

        return in_array($this->type, $appTypes);
    }

    /**
     * @param ShopgateContainerVisitor $v
     */
    public function accept(ShopgateContainerVisitor $v)
    {
        $v->visitClient($this);
    }
}
