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
     * @throws Exception\InvalidTransitionException
     */
    protected function checkExpectedStatus($expectedStatus)
    {
        // Check transition
        if (!Status::isValidTransition($this->processData->getStatus(), $expectedStatus)) {
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
     * @return boolean
     */
    public function initialize($integrationType, TechnicalShopParams $shopParams = null)
    {
        $this->clearMessages();
        $targetStatus = Status::INITIALIZED;
        $this->checkExpectedStatus($targetStatus);

        $this->getEventHandlerRegistry()->fire('beforeInitialize');
        // Create process data
        $processInitialize = $this->createProcessInitialize($integrationType, $shopParams);

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

                return false;
            }
        }

        if ($error || $processInitializeResponse->getTbProcessIdentifier() === null) {
            return false;
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

        return true;
    }

    public function updateSepa()
    {
        $this->clearMessages();
        $targetStatus = Status::SAVED;
        $this->checkExpectedStatus($targetStatus);
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

    public function update()
    {
        if ($this->validHash()) {
            return true;
        }

        $this->processData->setHash(null);

        $this->clearMessages();
        $targetStatus = Status::SAVED;
        $this->checkExpectedStatus($targetStatus);

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
            $this->getEventHandlerRegistry()->fire('errorUpdate');
            $this->processData->save();

            return false;
        }

        // Process response
        $this->processData->setStatus($targetStatus);
        $this->processData->save();

        $this->getEventHandlerRegistry()->fire('afterUpdate');

        return true;
    }

    public function decide()
    {
        $this->clearMessages();
        $targetStatus = Status::ACCEPTED;

        if ($this->validHash()
            && $this->getProcessData()->getStatus() === $targetStatus
        ) {
            return true;
        }

        $this->checkExpectedStatus($targetStatus);

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
            return false;
        }

        // Process response
        $this->processData->setStatus($targetStatus);
        $this->processData->setHash($this->processData->generateHash());
        $this->processData->save();

        $this->getEventHandlerRegistry()->fire('afterInitialize');

        return true;
    }

    /**
     * Internal helper function to prepare the initialize process
     * @param string $integrationType
     * @param TechnicalShopParams $shopParams
     * @return ProcessInitialize
     */
    protected function createProcessInitialize($integrationType, TechnicalShopParams $shopParams = null)
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
     * @return bool
     */
    public function agree()
    {
        $this->clearMessages();
        $this->checkExpectedStatus(Status::CONFIRMED);

        $this->getEventHandlerRegistry()->fire('beforeDecide');
        $agreeResponse = $this->apiClient->agreeInstallment($this->getProcessData()->getTbaId());

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

        $this->getEventHandlerRegistry()->fire('afterInitialize');

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
     * @return \EasyCredit\Transfer\FinancingDetails
     */
    public function getFinancingDetails()
    {
        return $this->apiClient->getFinancingDetails($this->getProcessData()->getTbaId());
    }

    /**
     * @return \EasyCredit\Transfer\LegislativeText
     */
    public function getLegislativeText()
    {
        return $this->apiClient->getLegislativeText();
    }
    
    /**
     * @return \EasyCredit\Transfer\CommonProcessData
     */
    public function getCommonProcessData()
    {
        return $this->apiClient->getCommonProcessData($this->getProcessData()->getTbaId());
    }
    
    /**
     * @return \EasyCredit\Transfer\GetDecisionResponse
     */
    public function getDecision()
    {
        $decisionResponse = $this->apiClient->getDecision($this->getProcessData()->getTbaId());
        
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
}
