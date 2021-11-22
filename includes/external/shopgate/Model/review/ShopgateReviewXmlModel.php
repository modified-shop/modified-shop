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
class ShopgateReviewXmlModel extends ShopgateReviewModel
{
    public function setUid()
    {
        parent::setUid($this->item['reviews_id']);
    }

    public function setItemUid()
    {
        parent::setItemUid($this->item['products_id']);
    }

    public function setScore()
    {
        parent::setScore($this->buildScore($this->item['reviews_rating']));
    }

    public function setReviewerName()
    {
        parent::setReviewerName($this->item['customers_name']);
    }

    public function setDate()
    {
        parent::setDate($this->buildDate($this->item['date_added']));
    }

    public function setTitle()
    {
        parent::setTitle($this->buildTitle(''));
    }

    public function setText()
    {
        parent::setText($this->item['reviews_text']);
    }
}
