<?php

namespace EasyCredit\Api\Request\Text;
/**
 * @author info@senbyte.com
 *
 * @copyright 2017 senByte UG
 * @license
 */

use EasyCredit\Http\Request;
use EasyCredit\Api\Request\AbstractRequest;
use EasyCredit\Api\Request\RequestInterface;
use EasyCredit\Transfer\LegislativeText;

/**
 * Class GetLegislativeText
 *
 * @package EasyCredit\Api\Request\Text
 */
class GetLegislativeText extends AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $path = '/v2/texte/zustimmung/%s';

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
     * @return LegislativeText
     */
    public function getTransferClass()
    {
        return new LegislativeText();
    }
}
