<?php
namespace Teambank\EasyCreditApiV3\Integration;

use Teambank\EasyCreditApiV3\Model\Transaction;

interface CheckoutInterface {

    public function getRedirectUrl();
    public function start(Transaction $request);
    public function getConfig();
    public function isInitialized();
    public function loadTransaction();
    public function authorize($orderId = null);
    public function verifyAddress(Transaction $request, $preCheck);
    public function isAmountValid(Transaction $request);
    public function clear();
}
