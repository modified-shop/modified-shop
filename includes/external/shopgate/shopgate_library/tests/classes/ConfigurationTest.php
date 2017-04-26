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
 * @author             Shopgate GmbH <interfaces@shopgate.com>
 * @author             Rainer Skistims <rainer.skistims@shopgate.com>
 * @group              Shopgate_Library
 *
 * @coversDefaultClass ShopgateConfig
 */
class Shopgate_ConfigurationTest extends PHPUnit_Framework_TestCase
{
    /** @var ShopgateConfig $class */
    protected $class;

    public function setUp()
    {
        $this->class = new ShopgateConfig(array());
    }

    /**
     * Tests the setter and getter for exclude_item_ids because the setter has logic
     *
     * @dataProvider excludeItemIdsProvider
     * @covers ::setExcludeItemIds
     *
     * @param array|string $input
     * @param array $expected
     */
    public function testExcludeItemIds($input, $expected)
    {
        $this->class->setExcludeItemIds($input);
        $returned = $this->class->getExcludeItemIds();
        $this->assertEquals($expected, $returned);
    }

    /**
     * @return array
     */
    public function excludeItemIdsProvider()
    {
        return array(
            'array with data to array with data' => array(
                array(1, 2),
                array(1, 2)
            ),
            'empty array to empty array' => array(
                array(),
                array()
            ),
            'empty string to empty array' => array(
                '',
                array()
            ),
            'JSON to array with data' => array(
                '[4,9,5,7,345,9864,1]',
                array(4, 9, 5, 7, 345, 9864, 1)
            ),
        );
    }
}
