<?php


namespace EasyCredit\Transfer;

/**
 * Class BankDataOld
 *
 * @package EasyCredit\Transfer
 */
class BankDataOld extends AbstractObject
{

    /**
     * @var string
     * @apiName ktoNr
     */
    protected $accountNumber;

    /**
     * @var string
     * @apiName blz
     */
    protected $bankCode;

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = str_replace(' ', '', $accountNumber);
    }

    /**
     * @return string
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }

    /**
     * @param string $bankCode
     */
    public function setBankCode($bankCode)
    {
        $this->bankCode = $bankCode;
    }
}
