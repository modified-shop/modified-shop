<?php


namespace EasyCredit\Transfer;

/**
 * Class Agreement
 *
 * @package EasyCredit\Transfer
 */
class Agreement extends AbstractObject
{

    /**
     * @var boolean
     * @apiName sepaMandat
     */
    protected $sepa;

    /**
     * @var boolean
     * @apiName datenverarbeitung
     */
    protected $dataProcessing;

    /**
     * @var boolean
     * @apiName zustimmungWerbungEmail
     */
    protected $newsletter;
    
    /**
     * @var boolean
     * @apiName zustimmungZurHandlungInEigenemNamen
     */
    protected $inItsOwnName;

    /**
     * @return boolean
     */
    public function getSepa()
    {
        return $this->sepa;
    }

    /**
     * @param boolean $sepa
     */
    public function setSepa($sepa)
    {
        $this->sepa = $sepa;
    }

    /**
     * @return boolean
     */
    public function getDataProcessing()
    {
        return $this->dataProcessing;
    }

    /**
     * @param boolean $dataProcessing
     */
    public function setDataProcessing($dataProcessing)
    {
        $this->dataProcessing = $dataProcessing;
    }

    /**
     * @return boolean
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * @param boolean $newsletter
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * @return boolean
     */
    public function getInItsOwnName()
    {
        return $this->inItsOwnName;
    }

    /**
     * @param boolean $inItsOwnName
     */
    public function setInItsOwnName($inItsOwnName)
    {
        $this->inItsOwnName = $inItsOwnName;
    }
 
}
