<?php


namespace EasyCredit\Api\Request\Process;

use EasyCredit\Http\Request;
use EasyCredit\Api\DataMapper;
use EasyCredit\Api\Request\AbstractRequest;
use EasyCredit\Api\Request\RequestInterface;
use EasyCredit\Transfer\ProcessInitializeResponse;
use EasyCredit\Transfer\ProcessInitialize;

/**
 * Class Initialize
 *
 * @package EasyCredit\Api\Request\Process
 */
class Initialize extends AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $path = '/v2/vorgang';

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
     * Initialize constructor.
     *
     * @param string            $shopId
     * @param string            $shopToken
     * @param ProcessInitialize $processInitialize
     * @param DataMapper        $dataMapper
     */
    public function __construct($shopId, $shopToken, ProcessInitialize $processInitialize, DataMapper $dataMapper)
    {
        $this->headers[] = 'tbk-rk-shop: '.$shopId;
        $this->headers[] = 'tbk-rk-token: '.$shopToken;
        $this->body = $dataMapper->mapRequest($processInitialize);
    }

    /**
     * @return ProcessInitializeResponse
     */
    public function getTransferClass()
    {
        return new ProcessInitializeResponse();
    }
}
