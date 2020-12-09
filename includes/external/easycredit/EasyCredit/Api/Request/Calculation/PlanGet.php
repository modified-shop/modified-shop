<?php

namespace EasyCredit\Api\Request\Calculation;

use EasyCredit\Http\Request;
use EasyCredit\Api\Request\AbstractRequest;
use EasyCredit\Api\Request\RequestInterface;
use EasyCredit\Transfer\ModelCalculation;

/**
 * Class PlanGet
 *
 * @package EasyCredit\Api\Request\Calculation
 */
class PlanGet extends AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $path = '/v2/modellrechnung/durchfuehren';

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
     * PlanGet constructor.
     *
     * @param string $webShopId
     * @param float  $amount
     */
    public function __construct($webShopId, $amount)
    {
        $this->headers[] = 'tbk-rk-shop: '.$webShopId;
        $this->parameters = array('webshopId' => $webShopId, 'finanzierungsbetrag' => $amount);
    }

    /**
     * @return ModelCalculation
     */
    public function getTransferClass()
    {
        return new ModelCalculation();
    }
}
