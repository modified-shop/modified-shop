<?php


namespace EasyCredit\Transfer;

/**
 * Class BankData
 *
 * @package EasyCredit\Transfer
 */
class BankData extends AbstractObject
{

    /**
     * @var BankDataOld
     * @apiName       bankverbindungKtoBlz
     * @transferClass EasyCredit\Transfer\BankDataOld
     */
    protected $bankData;

    /**
     * @var BankDataSepa
     * @apiName       bankverbindung
     * @transferClass EasyCredit\Transfer\BankDataSepa
     */
    protected $bankDataSepa;

    /**
     * @return BankDataOld
     */
    public function getBankData()
    {
        return $this->bankData;
    }

    /**
     * @param BankDataOld $bankData
     */
    public function setBankData($bankData)
    {
        $this->bankData = $bankData;
    }

    /**
     * @return BankDataSepa
     */
    public function getBankDataSepa()
    {
        return $this->bankDataSepa;
    }

    /**
     * @param BankDataSepa $bankDataSepa
     */
    public function setBankDataSepa($bankDataSepa)
    {
        $this->bankDataSepa = $bankDataSepa;
    }
}
