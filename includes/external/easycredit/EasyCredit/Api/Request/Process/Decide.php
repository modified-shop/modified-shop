<?php


namespace EasyCredit\Api\Request\Process;

use EasyCredit\Http\Request;
use EasyCredit\Api\DataMapper;
use EasyCredit\Api\Request\AbstractRequest;
use EasyCredit\Api\Request\RequestInterface;
use EasyCredit\Transfer\DecisionResponse;

/**
 * Class Decide
 *
 * @package EasyCredit\Api\Request\Process
 */
class Decide extends AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $path = '/v2/vorgang/%s/entscheiden';

    /**
     * @var string
     */
    protected $method = Request::METHOD_POST;

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
     * @return DecisionResponse
     */
    public function getTransferClass()
    {
        return new DecisionResponse();
    }
}
