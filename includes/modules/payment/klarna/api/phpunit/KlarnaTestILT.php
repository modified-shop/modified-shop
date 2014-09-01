<?php

require_once('../Klarna.php');

/**
 * Test class for Klarna.
 */
class KlarnaTestILT extends PHPUnit_Framework_TestCase
{
    /**
     * @var Klarna
     */
    protected $object;

    public static function setUpBeforeClass()
    {
        date_default_timezone_set('Europe/Stockholm');
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = $this->getMock('Klarna', array('xmlrpc_call', 'sendStat'));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->object = null;
    }

    /**
     *
     */
    public function testCheckILT() {
        //Mock the object to return the questions
        $this->object->expects($this->any())->
            method('xmlrpc_call')->
            with($this->logicalOr(
                $this->equalTo('check_ilt')
            ))->will($this->returnValue(
                array(
                    'children_under_18' => array(
                        'text' => 'Aantal kinderen onder de 18 jaar die thuiswonen?',
                        'type' => 'drop-down',
                        'values' => array('0','1','2','3','4','>5')
                    ),
                    'people_in_household' => array(
                        'text' => 'Aantal mensen in uw huishouden?',
                        'type' => 'drop-down',
                        'values' => array('1','2','3','4','>5')
                    ),
                    'household_income' => array(
                        'text' => 'Totaal netto inkomen van uw huishouden?',
                        'type' => 'drop-down',
                        'values' => array()
                    ),
                    'monthly_payment' => array(
                        'text' => 'Maandelijks bedrag aan kale huur of bruto hypotheek?',
                        'type' => 'drop-down',
                        'values' => array('0','49','50-99','...','>1450')
                    ),
                    'credit_expenses' => array(
                        'text' => 'Maandelijkse uitgaven aan leningen elders?',
                        'type' => 'drop-down',
                        'values' => array('500-549','550-599','...','>4500')
                    )
                )
            )
        );

        $this->object->setCountry('NL');

        $addr = new KlarnaAddr(
            $email = 'always_denied@klarna.com',
            $telno = '0704404000',
            $cellno = '+31612345678',
            $fname = 'Testperson-nl',
            $lname = 'Denied',
            $careof = '',
            $street = 'Neherkade',
            $zip = '1521VA',
            $city = 'Gravenhage',
            $country = KlarnaCountry::NL,
            $houseNo = '1',
            $houseExt = 'XI'
        );
        $this->object->setAddress(KlarnaFlags::IS_SHIPPING, $addr);

        $ilt = $this->object->checkILT(300, '01071970', KlarnaFlags::MALE);

        $this->assertNotEmpty($ilt);
    }

    /**
     * @depends testCheckILT
     */
    public function testEmptyCheckILT() {
        //Mock the object to return no questions
        $this->object->expects($this->any())->
            method('xmlrpc_call')->
            with($this->logicalOr(
                $this->equalTo('check_ilt')
            ))->will($this->returnValue(
                array()
            )
        );

        $this->object->setCountry('NL');

        $addr = new KlarnaAddr(
            $email = 'always_denied@klarna.com',
            $telno = '0704404000',
            $cellno = '+31612345678',
            $fname = 'Testperson-nl',
            $lname = 'Denied',
            $careof = "",
            $street = 'Neherkade',
            $zip = '1521VA',
            $city = 'Gravenhage',
            $country = KlarnaCountry::NL,
            $houseNo = '1',
            $houseExt = 'XI'
        );
        $this->object->setAddress(KlarnaFlags::IS_SHIPPING, $addr);

        $ilt = $this->object->checkILT(100, '01071970', KlarnaFlags::MALE);

        $this->assertEmpty($ilt);
    }
}
