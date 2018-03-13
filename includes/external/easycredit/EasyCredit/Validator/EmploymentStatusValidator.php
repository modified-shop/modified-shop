<?php


namespace EasyCredit\Validator;

/**
 * Class EmploymentStatusValidator
 * @package EasyCredit\Validator
 */
class EmploymentStatusValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $validValues = array(
        'ANGESTELLTER',
        'ANGESTELLTER_OEFFENTLICHER_DIENST',
        'ARBEITER',
        'BEAMTER',
        'RENTNER',
        'SELBSTAENDIGER',
        'ARBEITSLOSER',
        'SONSTIGES',
    );

    /**
     * @return bool
     */
    public function validate()
    {
        if (!in_array($this->data, $this->validValues)) {
            $this->addMessage();
            return false;
        }

        return true;
    }
}
