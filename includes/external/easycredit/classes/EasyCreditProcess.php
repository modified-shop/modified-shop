<?php

/**
 * @author info@senbyte.com 
 * @copyright 2016 senByte UG
 * @license 
 */

class EasyCreditProcess extends \EasyCredit\Process\Process
{
    /**
     * @var SessionHandler
     */
    protected $saveHandler;
    
    public function __construct(\EasyCredit\Api\ApiClient $apiClient, $processData)
    {
        $this->saveHandler = new \EasyCredit\SaveHandler\SessionHandler();
        parent::__construct($apiClient, $processData);
    }
}