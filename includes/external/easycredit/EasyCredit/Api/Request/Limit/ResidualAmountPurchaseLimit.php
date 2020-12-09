<?php

namespace EasyCredit\Api\Request\Limit;
/**
 * @author info@senbyte.com
 *
 * @copyright 2017 senByte UG
 * @license
 */

use EasyCredit\Http\Request;
use EasyCredit\Api\Request\AbstractRequest;
use EasyCredit\Api\Request\RequestInterface;
use EasyCredit\Transfer\ResidualAmountPurchaseLimitResponse;

/**
 * Class ResidualAmountPurchaseLimit
 *
 * @package EasyCredit\Api\Request\Limit
 */
class ResidualAmountPurchaseLimit extends AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $path = '/v1/webshop/%s/restbetragankaufobergrenze';

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
    public function __construct($shopId, $shopToken)
    {
        $this->headers[] = 'tbk-rk-shop: '.$shopId;
        $this->headers[] = 'tbk-rk-token: '.$shopToken;
        $this->path = sprintf($this->path, $shopId);
    }

    /**
     * @return ResidualAmountPurchaseLimitResponse
     */
    public function getTransferClass()
    {
        return new ResidualAmountPurchaseLimitResponse();
    }
}
