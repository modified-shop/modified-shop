<?php

namespace EasyCredit\Transfer;

/**
 * Class ModelCalculation
 *
 * @package EasyCredit\Transfer
 * @apiName modellrechnungDurchfuehrenResponse
 */
class ModelCalculation extends AbstractObject
{

    /**
     * @var InstallmentPlanCollection
     * @apiName       ergebnis
     * @transferClass EasyCredit\Transfer\InstallmentPlanCollection
     */
    protected $results;

    /**
     * @var string
     * @apiName repraesentativesBeispiel
     */
    protected $representativeExample;
    
    

    /**
     * @return InstallmentPlanCollection
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param InstallmentPlanCollection $results
     */
    public function setResults(InstallmentPlanCollection $results)
    {
        $this->results = $results;
    }

    /**
     * @return string
     */
    public function getRepresentativeExample()
    {
        return $this->representativeExample;
    }

    /**
     * @param string $representativeExample
     */
    public function setRepresentativeExample($representativeExample)
    {
        $this->representativeExample = $representativeExample;
    }
}
