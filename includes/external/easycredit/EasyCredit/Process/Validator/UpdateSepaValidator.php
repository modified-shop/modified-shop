<?php

namespace EasyCredit\Process\Validator;

use EasyCredit\Process\Process;
use EasyCredit\Transfer\BankDataOld;
use EasyCredit\Transfer\BankDataSepa;
use EasyCredit\Transfer\ProcessData;
use EasyCredit\Validator\AddressEqualityValidator;
use EasyCredit\Validator\AddressInGermanyValidator;
use EasyCredit\Validator\AddressNotAPackstationValidator;
use EasyCredit\Validator\BirthdateValidator;
use EasyCredit\Validator\CheckboxValidator;
use EasyCredit\Validator\EmploymentStatusValidator;
use EasyCredit\Validator\Error;
use EasyCredit\Validator\ErrorMessage;
use EasyCredit\Validator\IBANValidator;
use EasyCredit\Validator\NotBlankValidator;
use EasyCredit\Validator\NumberValidator;

/**
 * Class UpdateSepaValidator
 *
 * @package EasyCredit\Process\Validator
 */
class UpdateSepaValidator extends AbstractValidator
{
    /**
     * @var ProcessData
     */
    protected $data;


    /**
     * Creates the list of relevant validators for the initialize step
     */
    protected function initValidators()
    {
        if ($this->data->getBankData()->getBankData() instanceof BankDataOld) {
            $this->addValidator($this->getBankAccountValidator());
            $this->addValidator($this->getBankCodeValidator());
        } else if ($this->data->getBankData()->getBankDataSepa() instanceof BankDataSepa) {
            $this->addValidator($this->getIBanValidator());
        }
    }

    /**
     * @return NumberValidator
     */
    protected function getBankAccountValidator()
    {
        $incomeConfig = array(
            'max_length' => 10,
            'min_length' => 6,
        );

        $validator = new NumberValidator(
            $this->data->getBankData()->getBankData()->getAccountNumber(),
            $incomeConfig
        );

        return $validator;
    }

    /**
     * @return NumberValidator
     */
    protected function getBankCodeValidator()
    {
        $incomeConfig = array(
            'max_length' => 8,
            'min_length' => 8,
            'error_message' => array(
                'default' => array(
                    'key' => Error::ERROR_BANKCODE_INVALID,
                    'message' => ErrorMessage::getDefaultMessage(Error::ERROR_BANKCODE_INVALID),
                ),
            ),
        );

        $validator = new NumberValidator(
            $this->data->getBankData()->getBankData()->getBankCode(),
            $incomeConfig
        );

        return $validator;
    }

    /**
     * @return IBANValidator
     */
    protected function getIBanValidator()
    {
        $iban = null;

        if ($this->data->getBankData()->getBankDataSepa() instanceof BankDataSepa) {
            $iban = $this->data->getBankData()->getBankDataSepa()->getIban();
        }
        return new IBANValidator($iban);
    }
}
