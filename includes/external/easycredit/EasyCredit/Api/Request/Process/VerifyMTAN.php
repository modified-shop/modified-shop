<?php


namespace EasyCredit\Api\Request\Process;

use EasyCredit\Http\Request;
use EasyCredit\Api\DataMapper;
use EasyCredit\Api\Request\AbstractRequest;
use EasyCredit\Api\Request\RequestInterface;
use EasyCredit\Transfer\BaseResponse;
use EasyCredit\Transfer\ProcessVerifyMTan;

/**
 * Class VerifyMTAN
 *
 * @package EasyCredit\Api\Request\Process
 */
class VerifyMTAN extends AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $path = '/v2/vorgang/%s/mTAN';

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
     * VerifyMTAN constructor.
     *
     * @param string            $shopId
     * @param string            $shopToken
     * @param string            $tbProcessIdentifier
     * @param ProcessVerifyMTan $processVerifyMTan
     * @param DataMapper        $dataMapper
     */
    public function __construct(
        $shopId,
        $shopToken,
        $tbProcessIdentifier,
        ProcessVerifyMTan $processVerifyMTan,
        DataMapper $dataMapper
    ) {
        $this->headers[] = 'tbk-rk-shop: '.$shopId;
        $this->headers[] = 'tbk-rk-token: '.$shopToken;
        $this->path = sprintf($this->path, $tbProcessIdentifier);
        $this->body = $dataMapper->mapRequest($processVerifyMTan);
    }

    /**
     * @return BaseResponse
     */
    public function getTransferClass()
    {
        return new BaseResponse();
    }
}
