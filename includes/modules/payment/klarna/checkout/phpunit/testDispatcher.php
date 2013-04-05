<?php
//~ require_once 'PHPUnit/Extensions/OutputTestCase.php';
require_once ("../classes/class.KlarnaDispatcher.php");
require_once ("../../api/Klarna.php");

class DispatcherTest extends PHPUnit_Extensions_OutputTestCase {
    protected function setUp() {
        session_start ();

        $this -> provider = $this -> getMock ('Provider',
            array('spam', 'egg', '__private'));
        $this -> dispatcher = new KlarnaDispatcher($this -> provider);
    }

    protected function tearDown() {
        session_destroy ();
    }

    public function provideDispatch() {
        return array(
            array("spam"),
            array("egg")
        );
    }

    /**
     * @dataProvider provideDispatch
     */
    public function testDispatch($fun) {
        $expected = "spam spam egg bacon and spam";
        $this -> provider -> expects($this -> once ()) ->
            method ($fun) ->
            will ($this -> returnValue ($expected));

        $this -> expectOutputString ($expected);
        $ret = $this -> dispatcher -> dispatch ($fun);
    }

    public function testPrivate() {
        $expected = '<?xml version="1.0"?>
<errorMessages><error><type>KlarnaApiException</type><message>Invalid action</message><code>0</code></error></errorMessages>
';
        $this -> expectOutputString ($expected);
        $ret = $this -> dispatcher -> dispatch ('__private');
    }

    public function testMissing() {
        $expected = '<?xml version="1.0"?>
<errorMessages><error><type>KlarnaApiException</type><message>Invalid action</message><code>0</code></error></errorMessages>
';
        $this -> expectOutputString ($expected);
        $ret = $this -> dispatcher -> dispatch ('doesnotexist');
    }
}

