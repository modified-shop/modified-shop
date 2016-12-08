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
class Shopgate_Helper_Logging_ObfuscatorTest extends PHPUnit_Framework_TestCase
{
    /** @var Shopgate_Helper_Logging_Obfuscator */
    private $obfuscator;
    
    public function setUp()
    {
        $this->obfuscator = new Shopgate_Helper_Logging_Obfuscator();
    }
    
    public function testAddObfuscationFields()
    {
        $this->obfuscator->addObfuscationFields(array('test'));
        $data = array(
            'mytestData' => 'this must be readable',
            'user'       => 'this must be readable',
            'pass'       => 'this is secure',
            'test'       => 'this is secure'
        );
        
        $expected = array(
            'mytestData' => 'this must be readable',
            'user'       => 'this must be readable',
            'pass'       => 'XXXXXXXX',
            'test'       => 'XXXXXXXX'
        );
        $this->assertEquals(
            $expected,
            $this->obfuscator->cleanParamsForLog($data)
        );
    }
    
    /**
     * @param array  $data
     * @param string $expectedResult
     *
     * @dataProvider addRemoveFieldProvider
     */
    public function testAddRemoveFields($data, $expectedResult)
    {
        $this->obfuscator->addRemoveFields(array('pass'));
        $this->assertEquals(
            $expectedResult,
            $this->obfuscator->cleanParamsForLog($data)
        );
    }
    
    public function addRemoveFieldProvider()
    {
        return array(
            'remove pass' => array(
                array(
                    'user' => 'this must be readable',
                    'pass' => 'this shall be removed',
                ),
                array(
                    'user' => 'this must be readable',
                    'pass' => '<removed>',
                ),
            ),
            'remove cart' => array(
                array(
                    'user' => 'this must be readable',
                    'cart' => array(
                        'amount'    => 12.34,
                        'all infos' => 'in this array must be removed'
                    ),
                ),
                array(
                    'user' => 'this must be readable',
                    'cart' => '<removed>',
                ),
            ),
        );
    }
    
    /**
     * @param array  $data
     * @param string $resultString
     *
     * @dataProvider cleanParamsForLogDefaultProvider
     */
    public function testCleanParamsForLogDefault($data, $resultString)
    {
        $loggingResult = $this->obfuscator->cleanParamsForLog($data);
        
        $this->assertEquals($resultString, $loggingResult);
    }
    
    public function cleanParamsForLogDefaultProvider()
    {
        return array(
            'secure'      => array(
                array(
                    'username' => 'this must be readable',
                    'pass'     => 'this is secure',
                ),
                array(
                    'username' => 'this must be readable',
                    'pass'     => 'XXXXXXXX',
                ),
            ),
            'secure only' => array(
                array(
                    'pass' => 'this is secure',
                ),
                array(
                    'pass' => 'XXXXXXXX',
                ),
            ),
            'no secure'   => array(
                array(
                    'test' => 'this must be readable',
                ),
                array(
                    'test' => 'this must be readable',
                ),
            ),
        );
    }
    
}