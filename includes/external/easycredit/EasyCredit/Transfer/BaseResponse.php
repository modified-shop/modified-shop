<?php


namespace EasyCredit\Transfer;

/**
 * Class BaseResponse
 *
 * @package EasyCredit\Transfer
 */
class BaseResponse extends AbstractObject
{

    /**
     * @var string
     * @apiName uuid
     */
    protected $uuid;

    /**
     * @var array
     * @apiName wsMessages
     */
    protected $messages;
    
    protected $httpStatusCode;

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return integer|null
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * @param integer $httpStatusCode
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;
    }
 
}
