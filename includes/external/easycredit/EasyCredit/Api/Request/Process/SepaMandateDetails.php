<?php

namespace EasyCredit\Api\Request\Process;

use EasyCredit\Http\Request;
use EasyCredit\Api\Request\AbstractRequest;
use EasyCredit\Api\Request\RequestInterface;
use EasyCredit\Transfer\SepaMandateDetailResponse;

/**
 * Class SepaMandateDetails
 * @package EasyCredit\Api\Request\Process
 */
class SepaMandateDetails extends AbstractRequest implements RequestInterface
{

    /**
     * @var string
     */
    protected $path = '/v2/vorgang/%s/zustimmung/sepa';

    /**
     * @var string
     */
    protected $method = Request::METHOD_GET;

    /**
     * @var array
     */
    protected $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
    );

    /**
     * Save constructor.
     *
     * @param string $shopId
     * @param string $shopToken
     * @param string $tbProcessIdentifier
     */
    public function __construct($shopId, $shopToken, $tbProcessIdentifier)
    {
        $this->headers[] = 'tbk-rk-shop: '.$shopId;
        $this->headers[] = 'tbk-rk-token: '.$shopToken;
        $this->path = sprintf($this->path, $tbProcessIdentifier);
    }

    /**
     * @return SepaMandateDetailResponse
     */
    public function getTransferClass()
    {
        return new SepaMandateDetailResponse();
    }
}
