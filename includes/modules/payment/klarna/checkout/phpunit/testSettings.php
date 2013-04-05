<?php
//~ require_once 'PHPUnit/Extensions/OutputTestCase.php';
require_once ("../classes/class.KlarnaDispatcher.php");
require_once ("../../api/Klarna.php");

class SettingsTest extends PHPUnit_Framework_TestCase {
    protected function setUp() {
        session_start ();
        $klarna = new Klarna();
        $this -> checkout = new KlarnaAPI ('se', 'sv', 'invoice', 1000,
            KlarnaFlags::CHECKOUT_PAGE, $klarna);
    }

    protected function tearDown() {
        session_destroy ();
    }

    public function provideCountry() {
        return array(
            array('se', KlarnaCountry::SE),
            array('NO', KlarnaCountry::NO),
            array(KlarnaCountry::NL, KlarnaCountry::NL)
        );
    }

    /**
     * @dataProvider provideCountry
     */
    public function testSetCountry($input, $expected) {
        $this -> checkout -> setCountry ($input);
        $this -> assertEquals ($expected,
            $this -> checkout -> getCountry ());
    }

    public function provideLanguage() {
        return array(
            array('nl', KlarnaLanguage::NL),
            array('FI', KlarnaLanguage::FI),
            array(KlarnaLanguage::DA, KlarnaLanguage::DA)
        );
    }

    /**
     * @dataProvider provideLanguage
     */
    public function testSetLanguage($input, $expected) {
        $this -> checkout -> setLanguage ($input);
        $this -> assertEquals ($expected,
            $this -> checkout -> getLanguage ());
    }
}

