<?php
/**
 * Experimental class
 *
 * DO NOT SEND THIS FILE TO ANY MERCHANT/CUSTOMER!
 */
class KlarnaExperimental extends Klarna {

    /**
     * Constants used with TESTING mode for the communications with Klarna.
     *
     * @see Klarna::BETA
     * @see Klarna::LIVE
     * @see self::VALIDATE
     *
     * @var int
     */
    const TESTING = 2;

    /**
     * Constants used with VALIDATE mode for the communications with Klarna.
     *
     * @see Klarna::BETA
     * @see Klarna::LIVE
     * @see self::TESTING
     *
     * @var int
     */
    const VALIDATE = 3;

    /**
     * URL/Address to the test/havsmus Klarna Online server.
     * Port used is 8124.
     *
     * @var string
     */
    private static $test_addr = 'havsmus';

    /**
     * URL/Address to the validate/taggmakrill PHP script.
     * Port used is 80.
     *
     * @var string
     */
    private static $val_addr = 'taggmakrill';

    /**
     * Class constructor
     */
    public function __construct() {
        $this->PROTO; //Still 4.1 protocol version
        $this->VERSION .= ':exp';
    }

    /**
     * This method overrides init() so the calls are ALWAYS toward our test machine, havsmus.
     * @see Klarna::init
     */
    protected function init() {
        parent::init();

        //Is mode set to TESTING?
        $url = '/';
        if($this->mode === self::TESTING) {
            $this->port = 8124;
            $this->addr = self::$test_addr;
            $this->ssl = false;
        }
        else if($this->mode === self::VALIDATE) {
            $this->port = 80;
            $this->addr = self::$val_addr;
            $this->ssl = false;
            $url = '/keis/xsdaemon/validate.php';
        }

        $this->xmlrpc = new xmlrpc_client($url, $this->addr, $this->port, (($this->ssl) ? 'https' : 'http'));
        $this->xmlrpc->request_charset_encoding = 'ISO-8859-1';
    }

    /**
     * @TODO fixme
     * Existing method, it needs fixing before we include it in the normal API?
     * Only works for Sweden?
     * Reservations flagged as "cancelled" can still be "checked."
     *
     * @param  string      $pno     Personal number, SSN, date of birth, etc.
     * @param  string      $rno     Reservation number.
     * @param  int         $amount  Amount including VAT.
     * @param  KlarnaAddr  $addr    The address used.
     * @return bool        True, if ?
     */
    public function checkReservation($pno, $rno, $amount, $addr) {
        //@TODO no supplied encoding?
        $encoding = null;
        $encoding = ($encoding === null) ? $this->getPNOEncoding() : $encoding;
        $this->checkPNO($pno, $encoding, __METHOD__);
        $this->checkRNO($rno, __METHOD__);

        //Calculate automatically the amount from goodsList.
        if($amount === -1) {
            $amount = 0;
            foreach($this->goodsList as $goods) {
                $amount += $goods['goods']['price'] * intval($goods['qty']);
            }
        }
        $this->checkAmount($amount, __METHOD__);
        if($amount <= 0) {
            throw new KlarnaException("Error in " . __METHOD__ . ": Amount needs to be larger than 0! ($amount)");
        }

        if(!($addr instanceof KlarnaAddr)) {
            throw new KlarnaException('Error in ' . __METHOD__ . ': Specified address is not a KlarnaAddr object!');
        }

        //@TODO do we need checks for the addr?
        $digestSecret = self::digest($this->colon($this->eid, $rno, $amount, $this->secret));
        $paramList = array($this->eid, $rno, $amount, $addr->toArray(), $digestSecret, $pno);

        self::printDebug('check_reservation array', $paramList);

        $result = $this->xmlrpc_call('check_reservation', $paramList);

        self::printDebug('check_reservation result', $result);

        return ($result == 'ok');
    }

    /**
     * <b>STILL UNDER DEVELOPMENT!</b><br>
     * Specification not done ?
     *
     * Enhanced version of {@link Klarna::getAddresses()} which returns has_account, has_ILT_test and flag in addition<br>
     * to the registered addresses for the customer.<br>
     *
     * @param  string  $identifier  PNO, email, phone or other personal identifier.
     * @param  int     $encoding    {@link KlarnaEncoding PNO encoding} or other encoding constant.
     * @param  int     $type        ???
     * @param  string  $code        ???
     * @return array   An array where the first index is a array of addresses, second index is has_account,<br>third index is has_ILT_test and third index is flag.
     */
    public function prepareTransaction($identifier, $encoding = null, $type = 0, $code = "") {
        $encoding = ($encoding === null) ? $this->getPNOEncoding() : $encoding;

        $digestSecret = self::digest($this->colon($this->eid, $identifier, $this->secret)); //@TODO how?

        $paramList = array(
            $identifier, $this->eid, $digestSecret, $encoding, $type, $this->getClientIP(),
            $this->sid, $this->shipInfo, $code, $this->comment
        );

        self::printDebug("prepare_transaction array", $paramList);

        $result = $this->xmlrpc_call('prepare_transaction', $paramList);

        self::printDebug("prepare_transaction result array", $result);

        $tmp = array();
        //Convert the address array to an array of KlarnaAddr's.
        foreach($result[0] as $addrs) {
            $tmp[] = new KlarnaAddr(
                    @$addrs['email'],
                    @$addrs['telno'],
                    @$addrs['cellno'],
                    @$addrs['firstname'],
                    @$addrs['lastname'],
                    @$addrs['careof'],
                    @$addrs['street'],
                    @$addrs['zip'],
                    @$addrs['city'],
                    @$addrs['country'],
                    @$addrs['house_number'],
                    @$addrs['house_extension']
            );
        }

        //Return the new array of addresses and the other info from result.
        $tmp = array($tmp);
        unset($result[0]);
        return array_merge($tmp, $result);
    }
}
