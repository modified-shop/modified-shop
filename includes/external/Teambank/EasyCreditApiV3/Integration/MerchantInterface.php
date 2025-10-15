<?php
namespace Teambank\EasyCreditApiV3\Integration;

interface MerchantInterface {
    public function getTransaction($transactionId);
    public function searchTransactions(array $params = []);
    public function confirmShipment(string $transactionId, $trackingNumber = null);
    public function cancelOrder(string $transactionId, float $amount = null);
}
