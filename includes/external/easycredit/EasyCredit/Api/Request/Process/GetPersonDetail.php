<?php


namespace EasyCredit\Api\Request\Process;

use EasyCredit\Http\Request;
use EasyCredit\Api\DataMapper;
use EasyCredit\Api\Request\AbstractRequest;
use EasyCredit\Api\Request\RequestInterface;
use EasyCredit\Transfer\GetPersonResponse;

/**
 * Class GetPersonDetail
 *
 * @package EasyCredit\Api\Request\Process
 */
class GetPersonDetail extends AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $path = '/vorgang/%s/person';

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
     * @return GetPersonResponse
     */
    public function getTransferClass()
    {
        return new GetPersonResponse();
    }
}
