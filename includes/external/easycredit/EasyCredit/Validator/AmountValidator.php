<?php

namespace EasyCredit\Validator;

/**
 * Class AmountValidator
 *
 * @package EasyCredit\Validator
 */
class AmountValidator extends AbstractValidator
{
    /**
     * Minimal valid amount
     *
     * @var float
     */
    protected $minimal;

    /**
     * Maximal valid amount
     *
     * @var float
     */
    protected $maximal;

    /**
     * Amount to be validated
     *
     * @var float
     */
    protected $actual;

    /**
     * Constructor of the AmountValidator
     *
     * @param float $minimal
     * @param float $maximal
     * @param float $actual
     */
    public function __construct($minimal, $maximal, $actual)
    {
        $this->minimal = (float) $minimal;
        $this->maximal = (float) $maximal;
        $this->actual = (float) $actual;
    }

    /**
     * Returns true if the actual amount is between the minimal and maximal value.
     *
     * @return boolean
     */
    public function validate()
    {
        if (($this->minimal > $this->actual) || ($this->actual > $this->maximal)) {
            $this->addMessage(
                sprintf('Der Betrag muss zwischen %.2f und %.2f liegen!', $this->minimal, $this->maximal),
                'ERROR_AMOUNT'
            );
            return false;
        }

        return true;
    }
}
