<?php
//~ require_once 'PHPUnit/Extensions/OutputTestCase.php';
require_once ("../classes/class.KlarnaLanguagePack.php");
require_once ("../../api/Klarna.php");
require_once ("../../api/phpunit/jsonfileiterator.php");

class AddressTest extends PHPUnit_Framework_TestCase {
    protected function setUp() {
        $klarna = new Klarna();
        $this -> checkout = new KlarnaAPI ('se', 'sv', 'invoice', 1000,
            KlarnaFlags::CHECKOUT_PAGE, $klarna);
    }

    public function provideSplitAddress() {
        return new JSONFileIterator(dirname(__FILE__) . '/splitaddresses.json');
    }

    /**
     * @dataProvider provideSplitAddress
     */
    public function testSplitAddress($combined, $street, $housenumber, $houseext) {
        $split = KlarnaAPI::splitAddress ($combined);

        $this -> assertEquals ($street, $split[0]);
        $this -> assertEquals ($housenumber, $split[1]);
        $this -> assertEquals ($houseext, $split[2]);
    }

    public function testSetAddress() {
        $addr = new KlarnaAddr();
        $addr -> setStreet ('Hellersbergstraße');
        $addr -> setFirstName ('Testperson-de');
        $addr -> setLastName ('Approved');

        $this -> checkout -> setAddress ($addr);

        $values = $this -> checkout -> getInputValues ();
        $this -> assertEquals ('Hellersbergstraße', $values['street']);
        $this -> assertEquals ('Testperson-de', $values['firstName']);
        $this -> assertEquals ('Approved', $values['lastName']);
        $this -> assertEquals ('Testperson-de Approved', $values['reference']);
    }
}
