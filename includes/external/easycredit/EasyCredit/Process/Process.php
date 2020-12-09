<?php

namespace EasyCredit\Process;

use EasyCredit\Api\ApiClient;
use EasyCredit\Process\Event\MessageCollector;
use EasyCredit\Process\Validator\DecideValidator;
use EasyCredit\Process\Validator\InitializeValidator;
use EasyCredit\Process\Validator\UpdateSepaValidator;
use EasyCredit\Process\Validator\UpdateValidator;
use EasyCredit\SaveHandler\SaveHandlerInterface;
use EasyCredit\Transfer\PersonData;
use EasyCredit\Transfer\ProcessData;
use EasyCredit\Transfer\ProcessInitialize;
use EasyCredit\Transfer\ProcessSave;
use EasyCredit\Transfer\ProcessSaveInput;
use EasyCredit\Transfer\TechnicalShopParams;
use EasyCredit\Process\Validator\MTanValidator;
use EasyCredit\Transfer\ProcessVerifyMTan;
use EasyCredit\Transfer\GetCommonProcessDataResponse;
use EasyCredit\Transfer\ProcessConfirm;

/**
 * Class Process
 *
 * This class holds the relevant data for one single EasyCredit process.
 * This object will be re-created for each request.
 *
 * @package EasyCredit\Process
 */
class Process extends MessageCollector
{
    /**
     * @var SaveHandlerInterface
     */
    protected $saveHandler;

    /**
     * @var ApiClient
     */
    protected $apiClient;

    /**
     * All process relevant customer and shopping basket data
     *
     * @var ProcessData
     */
    protected $processData;

    /**
     * @var Process
     */
    protected static $instance = null;

    /**
     * @var Event\HandlerRegistry
     */
    protected $eventHandlerRegistry;

    /**
     * Internal constructor for a new EC process.
     *
     * @param ApiClient        $apiClient
     * @param ProcessData|null $processData
     */
    protected function __construct(ApiClient $apiClient, ProcessData $processData = null)
    {
        $this->apiClient = $apiClient;
        if ($processData === null) {
            $this->processData = new ProcessData();
            if ($this->saveHandler) {
                $this->processData->setSaveHandler($this->saveHandler);
            }
            $this->processData->initEmpty();
            $this->processData->load();
        } else {
            $this->processData = $processData;
        }

        $this->eventHandlerRegistry = new Event\HandlerRegistry($this);
    }

    /**
     * Retrieves the current process instance.
     * Throws an exception if none exists.
     *
     * @return Process
     * @throws \Exception
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            throw new \Exception("No process instance existing!");
        }

        return static::$instance;
    }

    /**
     * Creates a new EC process instance.
     * Throws an exception if one already exists.
     *
     * @param ApiClient        $apiClient
     * @param ProcessData|null $processData
     * @return Process
     * @throws \Exception
     */
    public static function createInstance(ApiClient $apiClient, ProcessData $processData = null)
    {
        if (static::$instance !== null) {
            throw new \Exception("A process instance is already existing!");
        }

        return static::$instance = new static($apiClient, $processData);
    }

    /**
     * Destroys the instance.
     */
    final public function destroy()
    {
        if (static::$instance === null) {
            throw new \Exception("No process instance existing!");
        }

        static::$instance = null;
    }

    /**
     * Returns the data associated with this process
     *
     * @return ProcessData
     */
    public function getProcessData()
    {
        return $this->processData;
    }

    /**
     * @return Event\HandlerRegistry
     */
    public function getEventHandlerRegistry()
    {
        return $this->eventHandlerRegistry;
    }

    /**
     * @param string $expectedStatus
     * @param string $integrationType
     * @throws Exception\InvalidTransitionException
     */
    protected function checkExpectedStatus($expectedStatus, $integrationType = ProcessInitialize::INTEGRATION_TYPE_PAYMENT_PAGE)
    {
        // Check transition
        if (!Status::isValidTransition($this->processData->getStatus(), $expectedStatus, $integrationType)) {
            throw new Exception\InvalidTransitionException(
                "The transition"
                ." from status ".$this->processData->getStatus()
                ." to status ".$expectedStatus
                ." is invalid!"
            );
        }
    }

    /**
     * Initializes the EC process.
     * @param string $integrationType
     * @param TechnicalShopParams $shopParams
     * @param string $processIdentifierShop
     * @return boolean|ProcessInitializeResponse
     * 
     * @throws Exception\InvalidTransitionException
     */
    public function initialize(string $integrationType, TechnicalShopParams $shopParams = null, $processIdentifierShop = null)
    {
        $this->clearMessages();
        $targetStatus = Status::INITIALIZED;
        $this->checkExpectedStatus($targetStatus, $integrationType);

        $this->getEventHandlerRegistry()->fire('beforeInitialize');
        // Create process data
        $processInitialize = $this->createProcessInitialize($integrationType, $shopParams, $processIdentifierShop);
        
        // validate process data
        $initializeValidator = new InitializeValidator($this->getProcessData());
        if (!$initializeValidator->validate()) {
            $messages = $initializeValidator->getMessages();
            $this->getProcessData()->setMessages($messages);
            $this->getProcessData()->save();
            $this->getEventHandlerRegistry()->fire('errorInitialize');

            return false;
        }
        
        // Execute caller
        $processInitializeResponse = $this->apiClient->init($processInitialize);

        $messages = $processInitializeResponse->getMessages();

        $error = false;
        foreach ($messages['messages'] as $message) {
            if ($message['severity'] == "ERROR") {
                if ($message['key'] == "AdressdatenValidierenUndNormierenServiceActivityMsg.Errors.ADRESSE_UNBEKANNT") {
                    $message['renderedMessage'] .= ' Bitte prüfen Sie im Schritt \'Ihre Daten\' die Adresse.';
                }
                $prefix = null != $message['field'] ? $message['field'] . ': ' : '';
                $this->processData->addMessage($prefix . $message['renderedMessage'], $message['key']);
                $error = true;
            }

            if ($message['key'] == 'AllgemeineMeldungenMsg.Errors.VORGANG_UNBEKANNT') {
                $this->processData->initEmpty();
                $this->processData->save();
                $this->initialize();

                return $processInitializeResponse;
            }
        }

        if ($error || $processInitializeResponse->getTbProcessIdentifier() === null) {
            return $processInitializeResponse;
        }
        // Process response
        $this->processData->setTbaId($processInitializeResponse->getTbProcessIdentifier());
        $this->processData->setTechnicalTbaId($processInitializeResponse->getTechnicalProcessIdentifier());
        $this->processData->setStatus($targetStatus);
        $validUntil = new \DateTime();
        $validUntil->add(new \DateInterval('PT25M'));
        $this->processData->setValidUntil($validUntil);
        $this->processData->save();

        $this->getEventHandlerRegistry()->fire('afterInitialize');

        return $processInitializeResponse;
    }

    /**
     * @param string $integrationType
     * @return boolean
     */
    public function updateSepa($integrationType)
    {
        $this->clearMessages();
        $targetStatus = Status::SAVED;
        $this->checkExpectedStatus($targetStatus, $integrationType);
        // validate process data
        $updateSepaValidator = new UpdateSepaValidator($this->getProcessData());
        if (!$updateSepaValidator->validate()) {
            $messages = $updateSepaValidator->getMessages();
            $this->processData->setMessages($messages);
            $this->processData->save();
            $this->getEventHandlerRegistry()->fire('errorUpdateSepa');

            return false;
        }
        $this->processData->setMessages(array());
        $this->processData->save();
        $this->messages = array();
        $processSave = $this->createProcessSave();
        // Execute caller
        $processInitializeResponse = $this->apiClient->update($this->getProcessData()->getTbaId(), $processSave);

        if ($processInitializeResponse->getUuid() === null) {
            return false;
        }

        $messages = $processInitializeResponse->getMessages();
        $error = false;
        foreach ($messages['messages'] as $message) {
            if ($message['severity'] == "ERROR") {
                $this->processData->addMessage($message['renderedMessage'], $message['key']);
                $error = true;
            }

            if ($message['key'] == 'AllgemeineMeldungenMsg.Errors.VORGANG_UNBEKANNT') {
                $this->processData->initEmpty();
                $this->processData->save();
                $this->initialize();

                return false;
            }
        }

        if ($error) {
            $this->getEventHandlerRegistry()->fire('errorUpdateSepa');
            $this->processData->save();

            return false;
        }

        // Process response
        $this->processData->setStatus($targetStatus);
        $this->processData->save();

        $this->getEventHandlerRegistry()->fire('afterUpdateSepa');

        return true;
    }

    /**
     * Check if the current Hash is valid
     * @return bool
     */
    protected function validHash()
    {
        $hash = $this->processData->getHash();
        if (!empty($hash) && $hash === $this->processData->generateHash()) {
            return true;
        }
        return false;
    }

    /**
     * @param string $integrationType
     * @return boolean|\EasyCredit\Transfer\BaseResponse
     */
    public function update($integrationType)
    {
        if ($this->validHash()) {
            return true;
        }

        $this->processData->setHash(null);

        $this->clearMessages();
        $targetStatus = Status::SAVED;
        $this->checkExpectedStatus($targetStatus, $integrationType);

        $this->getEventHandlerRegistry()->fire('beforeUpdate');

        // validate process data
        $initializeValidator = new UpdateValidator($this->getProcessData());
        if (!$initializeValidator->validate()) {
            $messages = $initializeValidator->getMessages();
            $this->processData->setMessages($messages);
            $this->processData->save();
            $this->getEventHandlerRegistry()->fire('errorUpdate');

            return false;
        }
        $this->processData->setMessages(array());
        $this->processData->save();
        $this->messages = array();

        $processSave = $this->createProcessSave();

        // Execute caller
        $processInitializeResponse = $this->apiClient->update($this->getProcessData()->getTbaId(), $processSave);

        if ($processInitializeResponse->getUuid() === null) {
            return $processInitializeResponse;
        }

        $messages = $processInitializeResponse->getMessages();
        $error = false;
        foreach ($messages['messages'] as $message) {
            if ($message['severity'] == "ERROR") {
                $this->processData->addMessage($message['renderedMessage'], $message['key']);
                $error = true;
            }

            if ($message['key'] == 'AllgemeineMeldungenMsg.Errors.VORGANG_UNBEKANNT') {
                $this->processData->initEmpty();
                $this->processData->save();
                $this->initialize();

                return $processInitializeResponse;
            }
        }

        if ($error) {
            $this->getEventHandlerRegistry()->fire('errorUpdate');
            $this->processData->save();

            return $processInitializeResponse;
        }

        // Process response
        $this->processData->setStatus($targetStatus);
        $this->processData->save();

        $this->getEventHandlerRegistry()->fire('afterUpdate');

        return $processInitializeResponse;
    }

    /**
     * @param string $integrationType
     * @return boolean|\EasyCredit\Transfer\DecisionResponse
     */
    public function decide($integrationType)
    {
        $this->clearMessages();
        $targetStatus = Status::ACCEPTED;

        if ($this->validHash()
            && $this->getProcessData()->getStatus() === $targetStatus
        ) {
            return true;
        }

        $this->checkExpectedStatus($targetStatus, $integrationType);

        $this->getEventHandlerRegistry()->fire('beforeDecide');

        // validate process data
        $initializeValidator = new DecideValidator($this->getProcessData());
        if (!$initializeValidator->validate()) {
            $messages = $initializeValidator->getMessages();
            $this->addMessages($messages);
            $this->getEventHandlerRegistry()->fire('errorDecide');

            return false;
        }

        // Execute caller
        $processDecideResponse = $this->apiClient->decide($this->getProcessData()->getTbaId());
        $success = false;

        if ($processDecideResponse->getResult() == 'ROT') {
            $targetStatus = Status::DECLINED;
            $this->processData->setStatus($targetStatus);
            $this->processData->save();
            $success = false;
        }
        if ($processDecideResponse->getResult() == 'GRUEN') {
            $success = true;
        }

        if ($success === false) {
            return $processDecideResponse;
        }

        // Process response
        $this->processData->setStatus($targetStatus);
        $this->processData->setHash($this->processData->generateHash());
        $this->processData->save();

        $this->getEventHandlerRegistry()->fire('afterInitialize');

        return $processDecideResponse;
    }

    /**
     * Internal helper function to prepare the initialize process
     * @param string $integrationType
     * @param TechnicalShopParams $shopParams
     * @param string $processIdentifierShop
     * @return ProcessInitialize
     */
    protected function createProcessInitialize($integrationType, TechnicalShopParams $shopParams = null, $processIdentifierShop = null)
    {
        // Prepare initialize process transfer objects
        $processInitialize = new ProcessInitialize();
        $processInitialize->setPersonData($this->processData->getCustomer()->getPersonData());
        $processInitialize->setAdditionalPersonData($this->processData->getCustomer()->getAdditionalPersonData());
        if (!$this->processData->getBillingAddress()->isEmpty()) {
            $processInitialize->setBillingAddress($this->processData->getBillingAddress());
        }
        if (!$this->getProcessData()->getCustomer()->getContact()->isEmpty()) {
            $processInitialize->setContact($this->getProcessData()->getCustomer()->getContact());
        }
        if($integrationType == ProcessInitialize::INTEGRATION_TYPE_SERVICE_INTEGRATION) {
            $processInitialize->setEmploymentData($this->getProcessData()->getCustomer()->getEmploymentData());
        }
        $processInitialize->setDeliveryAddress($this->processData->getDeliveryAddress());
        $processInitialize->setAmount($this->processData->getOrderTotal());
        $processInitialize->setShopId($this->apiClient->getShopId());
        $processInitialize->setRiskRelatedInfo($this->processData->getRiskInfo());
        $processInitialize->setCartInfos($this->processData->getProducts());
        $processInitialize->setCallbackUrls($this->processData->getCallbackUrls());
        $processInitialize->setTerm($this->processData->getTerm());
        $processInitialize->setIntegrationType($integrationType);
        if (null != $shopParams) {
            $processInitialize->setTechnicalShopParams($shopParams);
        }
        if (null != $processIdentifierShop) {
            $processInitialize->setProcessIdentifierShop($processIdentifierShop);
        }
        
        return $processInitialize;
    }

    /**
     * @return \EasyCredit\Transfer\ProcessSave
     */
    protected function createProcessSave()
    {
        $processSaveInput = new ProcessSaveInput();
        $processSaveInput->setTerm($this->getProcessData()->getTerm());
        $processSaveInput->setEmploymentData($this->getProcessData()->getCustomer()->getEmploymentData());
        $processSaveInput->setContact($this->getProcessData()->getCustomer()->getContact());
        $processSaveInput->setAgreement($this->getProcessData()->getAgreement());
        $processSaveInput->setBankData($this->getProcessData()->getBankData());

        $personData = new PersonData();
        $personData->setBirthDate($this->getProcessData()->getCustomer()->getPersonData()->getBirthDate());

        $processSaveInput->setPersonData($personData);

        $processUpdate = new \EasyCredit\Transfer\ProcessSave();
        $processUpdate->setProcessSaveInput($processSaveInput);

        return $processUpdate;
    }

    /**
     * @param string $integrationType
     * @param string $processIdentifierShop
     * @return \EasyCredit\Transfer\TransferInterface
     */
    public function agree($integrationType, $processIdentifierShop = null)
    {
        $this->clearMessages();
        $this->checkExpectedStatus(Status::CONFIRMED, $integrationType);
        
        $processConfirm = $this->createProcessConfirm($processIdentifierShop);

        $this->getEventHandlerRegistry()->fire('beforeConfirm');
        $agreeResponse = $this->apiClient->agreeInstallment($this->getProcessData()->getTbaId(), $processConfirm);

        $verified = false;
        if ($agreeResponse->getMessages() !== null) {
            $messages = $agreeResponse->getMessages();

            if (isset($messages['messages'])) {
                foreach ($messages['messages'] as $message) {
                    if ($message['key'] == "BestellungBestaetigenServiceActivity.Infos.ERFOLGREICH") {
                        $verified = true;
                        // Process response
                        $this->processData->setStatus(Status::CONFIRMED);
                        $this->processData->save();
                    }
                }
            }
        }

        if ($verified === false) {
            $this->processData->setStatus(Status::DECLINED);
        }
        $this->processData->setStatus(Status::CONFIRMED);
        $this->processData->save();

        $this->getEventHandlerRegistry()->fire('afterConfirm');

        return $verified;
    }

    /**
     * @param float $amount
     * @return \EasyCredit\Transfer\ModelCalculation
     */
    public function getModelCalculation($amount)
    {
        return $this->apiClient->get($amount);
    }

    /**
     * @param float $amount
     * @return \EasyCredit\Transfer\ModelCalculationShort
     */
    public function getBestModelCalculation($amount)
    {
        return $this->apiClient->getBest($amount);
    }

    /**
     * @return \EasyCredit\Transfer\SepaMandateDetailResponse
     */
    public function getSepaMandateDetails()
    {
        return $this->apiClient->getSepaMandateDetails($this->getProcessData()->getTbaId());
    }

    /**
     * @return \EasyCredit\Transfer\VerificationSnipped
     */
    public function getVerificationSnipped()
    {
        return $this->apiClient->getVerificationSnipped($this->getProcessData()->getTbaId());
    }

    /**
     * @param string $tbProcessIdentifier
     * 
     * @return \EasyCredit\Transfer\FinancingDetails
     */
    public function getFinancingDetails($tbProcessIdentifier = null)
    {
        if (null == $tbProcessIdentifier) {
            $tbProcessIdentifier = $this->getProcessData()->getTbaId();
        }
        return $this->apiClient->getFinancingDetails($tbProcessIdentifier);
    }

    /**
     * @return \EasyCredit\Transfer\LegislativeText
     */
    public function getLegislativeText()
    {
        return $this->apiClient->getLegislativeText();
    }
    
    /**
     * @param string $tbProcessIdentifier
     * @return GetCommonProcessDataResponse
     */
    public function getCommonProcessData($tbProcessIdentifier = null)
    {
        if (null == $tbProcessIdentifier) {
            $tbProcessIdentifier = $this->getProcessData()->getTbaId();
        }
        return $this->apiClient->getCommonProcessData($tbProcessIdentifier);
    }
    
    /**
     * @return \EasyCredit\Transfer\GetDecisionResponse
     */
    public function getDecision($tbProcessIdentifier = null)
    {
        if (null == $tbProcessIdentifier) {
            $tbProcessIdentifier = $this->getProcessData()->getTbaId();
        }
        $decisionResponse = $this->apiClient->getDecision($tbProcessIdentifier);
        
        if ($decisionResponse->getDecision()->getResult() == 'ROT') {
            $this->processData->setStatus(Status::DECLINED);
            $this->processData->save();
            
            return $decisionResponse;
        }elseif ($decisionResponse->getDecision()->getResult() == 'GRUEN') {
            $this->processData->setStatus(Status::ACCEPTED);
            $this->processData->setHash($this->processData->generateHash());
            $this->processData->save();
            
            return $decisionResponse;
        }
        
        return null;
    }
    
   /**
    * @param string $integrationType
    * @return boolean|\EasyCredit\Transfer\TransferInterface
    */
    public function verifyMtan($integrationType)
    {
        $this->clearMessages();
        $targetStatus = Status::MTAN;
        
        $this->checkExpectedStatus($targetStatus, $integrationType);
        $this->getEventHandlerRegistry()->fire('beforeVerifyMtan');
    
        // validate process data
        $mTanValidator = new MTanValidator($this->getProcessData());
        if (!$mTanValidator->validate()) {
            $messages = $mTanValidator->getMessages();
            $this->addMessages($messages);
            $this->getEventHandlerRegistry()->fire('errorVerifyMtan');

            return false;
        }
        
        $processVerifyMTan = $this->createProcessVerifyMTan();
        // Execute caller
        $processVerifyMTanResponse = $this->apiClient->verifyMTan($this->getProcessData()->getTbaId(), $processVerifyMTan);

        if ($processVerifyMTanResponse->getUuid() === null) {
            return $processVerifyMTanResponse;
        }
        
        $messages = $processVerifyMTanResponse->getMessages();
        $error = false;
        foreach ($messages['messages'] as $message) {
            if ($message['key'] == 'FreigabeFuerEcommerceDurchfuehrenActivityMsg.Errors.MTAN_NICHT_VALIDE_KEIN_WEITERER_VERSUCH' ||
                $message['key'] == 'FreigabeFuerEcommerceDurchfuehrenActivityMsg.Errors.MTAN_NICHT_VALIDE_WEITERER_VERSUCH') {
                    
                $this->processData->addMessage($message['renderedMessage'], $message['key']);
                $error = true;
            }
            
            if ($message['key'] == 'FreigabeFuerEcommerceDurchfuehrenActivityMsg.Errors.MTAN_VORGANG_UNGUELTIG') {
                $this->processData->addMessage($message['renderedMessage'], $message['key']);
                $error = true;
                
                $this->processData->initEmpty();
                $this->processData->save();
                $this->initialize();
        
                return $processVerifyMTanResponse;
            }
        }
        
        if ($error) {
            $this->getEventHandlerRegistry()->fire('errorVerifyMtan');
            $this->processData->save();
        
            return $processVerifyMTanResponse;
        }
        
    
        // Process response
        $this->processData->setStatus($targetStatus);
        $this->processData->setHash($this->processData->generateHash());
        $this->processData->save();
        $this->getEventHandlerRegistry()->fire('afterVerifyMtan');
    
        return $processVerifyMTanResponse;
    }
    
    /**
     * @param string $tbProcessIdentifier
     * 
     * @return \EasyCredit\Transfer\TransferInterface
     */
    public function resendMtan($tbProcessIdentifier = null)
    {
        $this->clearMessages();
        
        if (null == $tbProcessIdentifier) {
            $tbProcessIdentifier = $this->getProcessData()->getTbaId();
        }
        
        $this->getEventHandlerRegistry()->fire('beforeResendMtan');
        $resendMTanResponse = $this->apiClient->resendMTan($tbProcessIdentifier);
        
        if ($resendMTanResponse->getUuid() === null) {
            return $resendMTanResponse;
        }
        
        if($resendMTanResponse->getHttpStatusCode() != 200) {
            $this->getEventHandlerRegistry()->fire('errorResendMtan');
            $messages = $resendMTanResponse->getMessages();
            
            foreach ($messages['messages'] as $message) {
                $this->processData->addMessage($message['renderedMessage'], $message['key']);
            }
            
            return $resendMTanResponse;
        }
        
        $this->getEventHandlerRegistry()->fire('afterResendMtan');
        
        return $resendMTanResponse;
    }
    
    /**
     * 
     * @return \EasyCredit\Transfer\ProcessVerifyMTan
     */
    protected function createProcessVerifyMTan()
    {
        $processVerifyMan = new ProcessVerifyMTan();
        $processVerifyMan->setMTan($this->getProcessData()->getMTan());
    
        return $processVerifyMan;
    }
    
    /**
     * @param string $processIdentifierShop
     * @return \EasyCredit\Transfer\ProcessConfirm
     */
    protected function createProcessConfirm($processIdentifierShop = null)
    {
        $processConfirm = new ProcessConfirm();
        $processConfirm->setCustomIdentifier($processIdentifierShop);
    
        return $processConfirm;
    }
}
