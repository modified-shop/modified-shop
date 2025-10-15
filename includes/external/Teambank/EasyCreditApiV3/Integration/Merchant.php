<?php
namespace Teambank\EasyCreditApiV3\Integration;

use Teambank\EasyCreditApiV3\Service\TransactionApi;
use Teambank\EasyCreditApiV3\ApiException;
use Teambank\EasyCreditApiV3\Model\CaptureRequest;
use Teambank\EasyCreditApiV3\Model\RefundRequest;

class Merchant implements MerchantInterface {

    protected $transactionApi;
    protected $logger;

    public function __construct(
        TransactionApi $transactionApi,
        $logger
    ) {
        $this->transactionApi = $transactionApi;
        $this->logger = $logger;
    }

    public function getTransaction($transactionId) {
        return $this->transactionApi->apiMerchantV3TransactionTransactionIdGet($transactionId);
    }

    public function searchTransactions(array $params = []) {
        return call_user_func_array([$this->transactionApi, 'apiMerchantV3TransactionGet'], $params);
    }

    public function confirmShipment(string $transactionId, $trackingNumber = null) {
        $captureRequest = new CaptureRequest(['trackingNumber' => $trackingNumber]);
        return call_user_func_array([$this->transactionApi, 'apiMerchantV3TransactionTransactionIdCapturePost'],[$transactionId, $captureRequest]);
    }

    public function cancelOrder(string $transactionId, float $amount = null) {
        $refundRequest = null;
        if ($amount !== null) {
            $refundRequest = new RefundRequest(['value' => $amount]);
        }
        return call_user_func_array([$this->transactionApi, 'apiMerchantV3TransactionTransactionIdRefundPost'],[$transactionId, $refundRequest]);
    }
}
