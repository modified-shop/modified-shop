<?php
//~ require_once 'PHPUnit/Extensions/OutputTestCase.php';
require_once ("../classes/class.KlarnaLanguagePack.php");
require_once ("../../api/Klarna.php");

class LanguagePackTest extends PHPUnit_Framework_TestCase {
    protected function setUp() {
        $this -> lpack = new KlarnaLanguagePack('phpunit/test_language.xml');
    }

    public function provideFetch() {
        return array(
            array('invoice', 'lasku', 'fi'),
            array('invoice', 'faktura', KlarnaLanguage::SV),
            array('invoice', 'rechnung', 'DE')
        );
    }

    /**
     * @dataProvider provideFetch
     */
    public function testFetch($key, $expected, $lang) {
        $r = $this -> lpack -> fetch($key, $lang);
        $this -> assertEquals ($expected, $r);
    }

    /**
     * @dataProvider provideFetch
     */
    public function testStaticFetch($key, $expected, $lang) {
        $r = KlarnaLanguagePack::fetch_from_file($key, $lang,
            'phpunit/test_language.xml');
        $this -> assertEquals ($expected, $r);
    }
}
