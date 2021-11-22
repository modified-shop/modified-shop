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

/**
 * @class Shopgate_Model_Review
 * @see   http://developer.shopgate.com/file_formats/xml/reviews
 *
 * @method          setUid(string $value)
 * @method string   getUid()
 *
 * @method          setItemUid(string $value)
 * @method string   getItemUid()
 *
 * @method          setScore(int $value)
 * @method int      getScore()
 *
 * @method          setReviewerName(string $value)
 * @method string   getReviewerName()
 *
 * @method          setDate(string $value)
 * @method string   getDate()
 *
 * @method          setTitle(string $value)
 * @method string   getTitle()
 *
 * @method          setText(string $value)
 * @method string   getText()
 */
class Shopgate_Model_Catalog_Review extends Shopgate_Model_AbstractExport
{
    /**
     * @var string
     */
    protected $itemNodeIdentifier = '<reviews></reviews>';

    /**
     * @var string
     */
    protected $identifier = 'reviews';

    /**
     * define xsd file location
     *
     * @var string
     */
    protected $xsdFileLocation = 'catalog/reviews.xsd';

    /**
     * @var array
     */
    protected $fireMethods = array(
        'setUid',
        'setItemUid',
        'setScore',
        'setReviewerName',
        'setDate',
        'setTitle',
        'setText',
    );

    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'Uid',
        'ItemUid',
        'Score',
        'ReviewerName',
        'Date',
        'Title',
        'Text',
    );

    /**
     * @param Shopgate_Model_XmlResultObject $itemNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemNode)
    {
        /**
         * @var Shopgate_Model_XmlResultObject $reviewNode
         */
        $reviewNode = $itemNode->addChild('review');
        $reviewNode->addAttribute('uid', $this->getUid());
        $reviewNode->addChild('item_uid', $this->getItemUid());
        $reviewNode->addChild('score', $this->getScore());
        $reviewNode->addChildWithCDATA('reviewer_name', $this->getReviewerName());
        $reviewNode->addChild('date', $this->getDate());
        $reviewNode->addChildWithCDATA('title', $this->getTitle());
        $reviewNode->addChildWithCDATA('text', $this->getText());

        return $itemNode;
    }

    /**
     * @return array|null
     */
    public function asArray()
    {
        $reviewNode = new Shopgate_Model_Abstract();
        $reviewNode->setData('uid', $this->getUid());
        $reviewNode->setData('item_uid', $this->getItemUid());
        $reviewNode->setData('score', $this->getScore());
        $reviewNode->setData('reviewer_name', $this->getReviewerName());
        $reviewNode->setData('date', $this->getDate());
        $reviewNode->setData('title', $this->getTitle());
        $reviewNode->setData('text', $this->getText());

        return $reviewNode->getData();
    }
}

/**
 * Class Shopgate_Model_Review
 *
 * @deprecated use Shopgate_Model_Catalog_Review
 */
class Shopgate_Model_Review extends Shopgate_Model_Catalog_Review
{
}
