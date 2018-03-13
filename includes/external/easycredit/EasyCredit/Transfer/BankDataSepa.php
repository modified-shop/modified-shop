<?php


namespace EasyCredit\Transfer;

/**
 * Class BankDataSepa
 *
 * @package EasyCredit\Transfer
 */
class BankDataSepa extends AbstractObject
{

    /**
     * @var string
     * @apiName iban
     */
    protected $iban;

    /**
     * @var string
     * @apiName bic
     */
    protected $bic;
    
    /**
     * @var string
     * @apiName kontoinhaber
     */
    protected $accountHolder;
    
    /**
     * @var string
     * @apiName kreditinstitut
     */
    protected $financialInstitution;

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = str_replace(' ', '', $iban);
    }

    /**
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    /**
     * @return string
     */
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * @param string $accountHolder
     */
    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;
    }

    /**
     * @return string
     */
    public function getFinancialInstitution()
    {
        return $this->financialInstitution;
    }

    /**
     * @param string $financialInstitution
     */
    public function setFinancialInstitution($financialInstitution)
    {
        $this->financialInstitution = $financialInstitution;
    }
 
}
