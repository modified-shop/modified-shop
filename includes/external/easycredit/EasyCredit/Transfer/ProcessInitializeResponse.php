<?php


namespace EasyCredit\Transfer;

/**
 * Class ProcessInitializeResponse
 *
 * @package EasyCredit\Transfer
 * @apiName vorgangInitialisierenResponse
 */
class ProcessInitializeResponse extends BaseResponse
{

    /**
     * @var string
     * @apiName deviceIdentToken
     */
    protected $deviceIdentToken;

    /**
     * @var string
     * @apiName fachlicheVorgangskennung
     */
    protected $technicalProcessIdentifier;

    /**
     * @var string
     * @apiName tbVorgangskennung
     */
    protected $tbProcessIdentifier;

    /**
     * @return string
     */
    public function getDeviceIdentToken()
    {
        return $this->deviceIdentToken;
    }

    /**
     * @param string $deviceIdentToken
     */
    public function setDeviceIdentToken($deviceIdentToken)
    {
        $this->deviceIdentToken = $deviceIdentToken;
    }

    /**
     * @return string
     */
    public function getTechnicalProcessIdentifier()
    {
        return $this->technicalProcessIdentifier;
    }

    /**
     * @param string $technicalProcessIdentifier
     */
    public function setTechnicalProcessIdentifier($technicalProcessIdentifier)
    {
        $this->technicalProcessIdentifier = $technicalProcessIdentifier;
    }

    /**
     * @return string
     */
    public function getTbProcessIdentifier()
    {
        return $this->tbProcessIdentifier;
    }

    /**
     * @param string $tbProcessIdentifier
     */
    public function setTbProcessIdentifier($tbProcessIdentifier)
    {
        $this->tbProcessIdentifier = $tbProcessIdentifier;
    }
}
