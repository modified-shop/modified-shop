<?php


namespace EasyCredit\Transfer;

/**
 * Class EmploymentData
 *
 * @package EasyCredit\Transfer
 */
class EmploymentData extends AbstractObject
{
    /**
     * @const
     */
    const EMPLOYMENT_STATUS_EMPLOYEE = 'ANGESTELLTER';
    
    /**
     * @const
     */
    const EMPLOYMENT_STATUS_EMPLOYEE_PUBLIC_SECTOR = 'ANGESTELLTER_OEFFENTLICHER_DIENST';
    
    /**
     * @const
     */
    const EMPLOYMENT_STATUS_WORKER = 'ARBEITER';
    
    /**
     * @const
     */
    const EMPLOYMENT_STATUS_CIVIL_SERVANT = 'BEAMTER';
    
    /**
     * @const
     */
    const EMPLOYMENT_STATUS_PENSIONER = 'RENTNER';
    
    /**
     * @const
     */
    const EMPLOYMENT_STATUS_SELF_EMPLOYED = 'SELBSTAENDIGER';
    
    /**
     * @const
     */
    const EMPLOYMENT_STATUS_UNEMPLOYED = 'ARBEITSLOSER';
    
    /**
     * @const
     */
    const EMPLOYMENT_STATUS_OTHER = 'SONSTIGES';

    /**
     * @var string
     * @apiName beschaeftigung
     */
    protected $employmentStatus;

    /**
     * @var float
     * @apiName monatlichesNettoeinkommen
     */
    protected $monthlyIncome;

    /**
     * @return string
     */
    public function getEmploymentStatus()
    {
        return $this->employmentStatus;
    }

    /**
     * @param string $employmentStatus
     */
    public function setEmploymentStatus($employmentStatus)
    {
        $this->employmentStatus = $employmentStatus;
    }

    /**
     * @return float
     */
    public function getMonthlyIncome()
    {
        return $this->monthlyIncome;
    }

    /**
     * @param float $monthlyIncome
     */
    public function setMonthlyIncome($monthlyIncome)
    {
        $this->monthlyIncome = $monthlyIncome;
    }
}
