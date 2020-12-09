<?php

namespace EasyCredit\Api\Request\Calculation;

use EasyCredit\Http\Request;
use EasyCredit\Api\Request\AbstractRequest;
use EasyCredit\Api\Request\RequestInterface;
use EasyCredit\Transfer\ModelCalculationShort;

/**
 * Class PlanGetBest
 *
 * @package EasyCredit\Api\Request\Calculation
 */
class PlanGetBest extends AbstractRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $path = '/v2/modellrechnung/guenstigsterRatenplan';

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
     * PlanGetBest constructor.
     *
     * @param string $webShopId
     * @param float  $amount
     */
    public function __construct($webShopId, $amount)
    {
        $this->parameters = array('webshopId' => $webShopId, 'finanzierungsbetrag' => $amount);
    }

    /**
     * @return ModelCalculationShort
     */
    public function getTransferClass()
    {
        return new ModelCalculationShort();
    }
}
