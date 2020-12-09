<?php


namespace EasyCredit\Api\Request\Process;

use EasyCredit\Http\Request;
use EasyCredit\Api\DataMapper;
use EasyCredit\Api\Request\AbstractRequest;
use EasyCredit\Api\Request\RequestInterface;
use EasyCredit\Transfer\BaseResponse;
use EasyCredit\Transfer\DecisionResponse;
use EasyCredit\Transfer\ProcessConfirm;

/**
 * Class Agree
 *
 * @package EasyCredit\Api\Request\Process
 */
class Agree extends AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $path = '/v2/vorgang/%s/bestaetigen';

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
     * @param ProcessConfirm $processConfirm
     * @param DataMapper        $dataMapper
     */
    public function __construct(
        $shopId,
        $shopToken, 
        $tbProcessIdentifier,
        ProcessConfirm $processConfirm,
        DataMapper $dataMapper)
    {
        $this->headers[] = 'tbk-rk-shop: '.$shopId;
        $this->headers[] = 'tbk-rk-token: '.$shopToken;
        $this->path = sprintf($this->path, $tbProcessIdentifier);
        $this->body = $dataMapper->mapRequest($processConfirm);
    }

    /**
     * @return DecisionResponse
     */
    public function getTransferClass()
    {
        return new BaseResponse();
    }
}
