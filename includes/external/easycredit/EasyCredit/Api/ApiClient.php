<?php

namespace EasyCredit\Api;

use EasyCredit\Http\Request;
use EasyCredit\Api\Request\Calculation\PlanGet;
use EasyCredit\Api\Request\Calculation\PlanGetBest;
use EasyCredit\Api\Request\Process\Agree;
use EasyCredit\Api\Request\Process\Decide;
use EasyCredit\Api\Request\Process\Get;
use EasyCredit\Api\Request\Process\GetFinancingDetails;
use EasyCredit\Api\Request\Process\GetCommonProcessData;
use EasyCredit\Api\Request\Process\GetDecision;
use EasyCredit\Api\Request\Process\Initialize;
use EasyCredit\Api\Request\Process\Save;
use EasyCredit\Api\Request\Process\SepaMandateDetails;
use EasyCredit\Transfer\DecisionResponse;
use EasyCredit\Transfer\FinancingDetails;
use EasyCredit\Transfer\CommonProcessData;
use EasyCredit\Transfer\LegislativeText;
use EasyCredit\Transfer\ModelCalculation;
use EasyCredit\Transfer\ModelCalculationShort;
use EasyCredit\Transfer\ProcessInitialize;
use EasyCredit\Transfer\ProcessInitializeResponse;
use EasyCredit\Transfer\ProcessSave;
use EasyCredit\Transfer\SepaDirectDebitText;
use EasyCredit\Transfer\VerificationSnipped;
use EasyCredit\Api\Request\Text\GetLegislativeText;
use EasyCredit\Api\Request\Limit\ResidualAmountPurchaseLimit;
use EasyCredit\Transfer\GetCommonProcessDataResponse;
use EasyCredit\Transfer\ProcessVerifyMTan;
use EasyCredit\Api\Request\Process\VerifyMTAN;
use EasyCredit\Api\Request\Process\ResendMTAN;
use EasyCredit\Transfer\ProcessConfirm;

/**
 * Class ApiClient
 *
 * @package EasyCredit\Api
 */
class ApiClient
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var DataMapper
     */
    protected $dataMapper;

    /**
     * @var string
     */
    protected $shopId;

    /**
     * @var string
     */
    protected $shopToken;

    /**
     * ApiClient constructor.
     *
     * @param string          $shopId
     * @param string          $shopToken
     * @param Request         $request
     * @param DataMapper|null $dataMapper
     */
    public function __construct($shopId, $shopToken, Request $request, DataMapper $dataMapper = null)
    {
        $this->shopId = $shopId;
        $this->shopToken = $shopToken;
        $this->request = $request;
        $this->dataMapper = $dataMapper;
    }

    /**
     * @return string
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * Get all possible Installment Calculations
     *
     * @param float $amount
     *
     * @return ModelCalculation
     */
    public function get($amount)
    {
        $requestType = new PlanGet($this->shopId, $amount);

        $response = $this->request->doRequest($requestType);

        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }

    /**
     * Get the best Installment Plan with lowest monthly rate
     *
     * @param float $amount
     *
     * @return ModelCalculationShort
     */
    public function getBest($amount)
    {
        $requestType = new PlanGetBest($this->shopId, $amount);

        $response = $this->request->doRequest($requestType);

        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }

    /**
     *
     * @param ProcessInitialize $processInitialize
     *
     * @return ProcessInitializeResponse
     */
    public function init(ProcessInitialize $processInitialize)
    {
        $requestType = new Initialize($this->shopId, $this->shopToken, $processInitialize, $this->dataMapper);

        $response = $this->request->doRequest($requestType);
        
        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }

    /**
     * @param string      $tbProcessIdentifier
     * @param ProcessSave $processSave
     *
     * @return \EasyCredit\Transfer\BaseResponse
     */
    public function update($tbProcessIdentifier, ProcessSave $processSave)
    {
        $requestType = new Save($this->shopId, $this->shopToken, $tbProcessIdentifier, $processSave, $this->dataMapper);

        $response = $this->request->doRequest($requestType);

        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }

    /**
     * @param string $tbProcessIdentifier
     *
     * @return DecisionResponse
     */
    public function decide($tbProcessIdentifier)
    {
        $requestType = new Decide($this->shopId, $this->shopToken, $tbProcessIdentifier);

        $response = $this->request->doRequest($requestType);

        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }

    /**
     * @param string $tbProcessIdentifier
     *
     * @return FinancingDetails
     */
    public function getFinancingDetails($tbProcessIdentifier)
    {
        $requestType = new GetFinancingDetails($this->shopId, $this->shopToken, $tbProcessIdentifier);

        $response = $this->request->doRequest($requestType);

        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }

    /**
     * @param string $tbProcessIdentifier
     *
     * @return \EasyCredit\Transfer\TransferInterface
     */
    public function agreeInstallment($tbProcessIdentifier, ProcessConfirm $processConfirm)
    {
        $requestType = new Agree($this->shopId, $this->shopToken, $tbProcessIdentifier, $processConfirm, $this->dataMapper);

        $response = $this->request->doRequest($requestType);

        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }

    /**
     * @param string $tbProcessIdentifier
     *
     * @return LegislativeText
     */
    public function getLegislativeText()
    {
        $requestType = new GetLegislativeText($this->shopId, $this->shopToken);
        
        $response = $this->request->doRequest($requestType);
        
        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }

    /**
     * @param string $tbProcessIdentifier
     *
     * @return VerificationSnipped
     */
    public function getVerificationSnipped($tbProcessIdentifier)
    {
        $snippedId = VerificationSnipped::SNIPPED_ID_PROD;
        if (strtolower(substr($this->shopId, 0, 4)) == '2.de') {
            $snippedId = VerificationSnipped::SNIPPED_ID_TEST;
        }

        return new VerificationSnipped($tbProcessIdentifier, $snippedId);
    }

    /**
     * @param string $tbProcessIdentifier
     * @return \EasyCredit\Transfer\SepaMandateDetailResponse
     */
    public function getSepaMandateDetails($tbProcessIdentifier)
    {
        $requestType = new SepaMandateDetails($this->shopId, $this->shopToken, $tbProcessIdentifier);

        $response = $this->request->doRequest($requestType);

        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }
    
    /**
     * @param string $tbProcessIdentifier
     * @return GetCommonProcessDataResponse
     */
    public function getCommonProcessData($tbProcessIdentifier)
    {
        $requestType = new GetCommonProcessData($this->shopId, $this->shopToken, $tbProcessIdentifier);
        
        $response = $this->request->doRequest($requestType);
        
        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }
    
    /**
     * @param string $tbProcessIdentifier
     * @return \EasyCredit\Transfer\GetDecisionResponse
     */
    public function getDecision($tbProcessIdentifier)
    {
        $requestType = new GetDecision($this->shopId, $this->shopToken, $tbProcessIdentifier);
        
        $response = $this->request->doRequest($requestType);
        
        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }
    
    /**
     * @return \EasyCredit\Transfer\ResidualAmountPurchaseLimitResponse
     */
    public function getResidualAmountPurchaseLimit()
    {
        $requestType = new ResidualAmountPurchaseLimit($this->shopId, $this->shopToken);
    
        $response = $this->request->doRequest($requestType);
    
        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }
    
    /**
     * 
     * @param string $tbProcessIdentifier
     * @param ProcessVerifyMTan $processVerifyMtan
     * @return \EasyCredit\Transfer\TransferInterface
     */
    public function verifyMTan($tbProcessIdentifier, ProcessVerifyMTan $processVerifyMtan)
    {
        $requestType = new VerifyMTAN($this->shopId, $this->shopToken, $tbProcessIdentifier, $processVerifyMtan, $this->dataMapper);
    
        $response = $this->request->doRequest($requestType);
    
        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }
    
    /**
     * 
     * @param string $tbProcessIdentifier
     * @return \EasyCredit\Transfer\TransferInterface
     */
    public function resendMTan($tbProcessIdentifier)
    {
        $requestType = new ResendMTAN($this->shopId, $this->shopToken, $tbProcessIdentifier);
    
        $response = $this->request->doRequest($requestType);
    
        $transferInterface = $this->dataMapper->mapResponse($requestType->getTransferClass(), $response->getBody());
        
        $transferInterface->setHttpStatusCode($response->getStatusCode());
        
        return $transferInterface;
    }
}
