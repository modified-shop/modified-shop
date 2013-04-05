<?php
//~ require_once 'PHPUnit/Extensions/OutputTestCase.php';
require_once ("../classes/class.KlarnaDispatcher.php");
require_once ("../../api/Klarna.php");

class RetrieveHTMLTest extends PHPUnit_Framework_TestCase {
    protected function setUp() {
        $this -> klarna = new Klarna();
    }

    protected function tearDown() {
    }

    public function provideCountry() {
        return array(
            array('se'),
            array('no'),
            array('fi'),
            array('dk'),
            array('de'),
            array('nl')
        );
    }

    /**
     * @dataProvider provideCountry
     */
    public function testInvoice($country) {
        $checkout = new KlarnaAPI ('se', 'sv', 'invoice', 1000,
            KlarnaFlags::CHECKOUT_PAGE, $this -> klarna, null, './');
        $checkout -> setCountry ($country);
        $checkout -> addSetupValue('threatmetrix', 'foo');

        $checkout -> retrieveHTML();

        // not crashing is a pass
    }
}
