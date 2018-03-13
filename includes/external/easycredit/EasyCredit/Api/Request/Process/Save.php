<?php


namespace EasyCredit\Api\Request\Process;

use EasyCredit\Http\Request;
use EasyCredit\Api\DataMapper;
use EasyCredit\Api\Request\AbstractRequest;
use EasyCredit\Api\Request\RequestInterface;
use EasyCredit\Transfer\BaseResponse;
use EasyCredit\Transfer\ProcessSave;

/**
 * Class Save
 *
 * @package EasyCredit\Api\Request\Process
 */
class Save extends AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $path = '/vorgang/';

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
     * @param string      $shopId
     * @param string      $shopToken
     * @param string      $tbProcessIdentifier
     * @param ProcessSave $processSave
     * @param DataMapper  $dataMapper
     */
    public function __construct(
        $shopId,
        $shopToken,
        $tbProcessIdentifier,
        ProcessSave $processSave,
        DataMapper $dataMapper
    ) {
        $this->headers[] = 'tbk-rk-shop: '.$shopId;
        $this->headers[] = 'tbk-rk-token: '.$shopToken;
        $this->path .= $tbProcessIdentifier;
        $this->body = $dataMapper->mapRequest($processSave);
    }

    /**
     * @return BaseResponse
     */
    public function getTransferClass()
    {
        return new BaseResponse();
    }
}
