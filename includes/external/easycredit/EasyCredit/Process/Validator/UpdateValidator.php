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
 * Class InitializeValidator
 *
 * @package EasyCredit\Process\Validator
 */
class UpdateValidator extends AbstractValidator
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
        $this->addValidator($this->getConsentValidator());
        $this->addValidator($this->getSepaAgreementValidator());
        $this->addValidator($this->getIncomeValidator());
        $this->addValidator($this->getEmploymentStatusValidator());

        if ($this->data->getBankData()->getBankData() instanceof BankDataOld) {
            $this->addValidator($this->getBankAccountValidator());
            $this->addValidator($this->getBankCodeValidator());
        } else {
            $this->addValidator($this->getIBanValidator());
        }
        $this->addValidator($this->getMobilePhoneValidator());
        $this->addValidator($this->getBirthdateValidator());
    }

    /**
     * @return AddressNotAPackstationValidator
     */
    public function getAddressNotAPackstationValidator()
    {
        return new AddressNotAPackstationValidator($this->data->getDeliveryAddress());
    }

    /**
     * @return CheckboxValidator
     */
    protected function getConsentValidator()
    {
        $config = array(
            'error_message' => array(
                'key' => Error::ERROR_CONSENT_INVALID,
                'message' => ErrorMessage::getDefaultMessage(Error::ERROR_CONSENT_INVALID),
            ),
        );

        $validator = new CheckboxValidator(
            $this->data->getAgreement()->getDataProcessing(),
            $config
        );

        return $validator;
    }

    /**
     * @return CheckboxValidator
     */
    protected function getSepaAgreementValidator()
    {
        $config = array(
            'error_message' => array(
                'key' => Error::ERROR_SEPA_AGREEMENT_INVALID,
                'message' => ErrorMessage::getDefaultMessage(Error::ERROR_SEPA_AGREEMENT_INVALID),
            ),
        );

        $validator = new CheckboxValidator(
            $this->data->getAgreement()->getSepa(),
            $config
        );

        return $validator;
    }

    /**
     * @return AddressEqualityValidator
     */
    protected function getAdressEqualityValidator()
    {
        $billingAddress = $this->data->getBillingAddress();
        $deliveryAddress = $this->data->getDeliveryAddress();

        return new AddressEqualityValidator($billingAddress, $deliveryAddress);
    }

    /**
     * @return AddressInGermanyValidator
     */
    protected function getAdressInGermanyValidator()
    {
        $billingAddress = $this->data->getBillingAddress();

        return new AddressInGermanyValidator($billingAddress);
    }


    /**
     * @return BirthdateValidator
     */
    protected function getBirthdateValidator()
    {
        $validator = new BirthdateValidator(
            $this->data->getCustomer()->getPersonData()->getBirthDate()
        );

        return $validator;
    }

    /**
     * @return NumberValidator
     */
    protected function getMobilePhoneValidator()
    {
        $config = array(
            'error_message' => array(
                'key' => Error::ERROR_MOBILEPHONE_INVALID,
                'message' => ErrorMessage::getDefaultMessage(Error::ERROR_MOBILEPHONE_INVALID),
            ),
        );

        $validator = new NotBlankValidator(
            $this->data->getCustomer()->getContact()->getMobilphone(),
            $config
        );

        return $validator;
    }

    /**
     * @return EmploymentStatusValidator
     */
    protected function getEmploymentStatusValidator()
    {
        $validator = new EmploymentStatusValidator(
            $this->data->getCustomer()->getEmploymentData()->getEmploymentStatus()
        );

        return $validator;
    }

    /**
     * @return NumberValidator
     */
    protected function getIncomeValidator()
    {
        $incomeConfig = array(
            'min_value' => 1,
            'error_message' => array(
                'default' => array(
                    'key' => Error::ERROR_INCOME_INVALID_INTEGER,
                    'message' => ErrorMessage::getDefaultMessage(Error::ERROR_INCOME_INVALID_INTEGER),
                ),
                'min_value' => array(
                    'key' => Error::ERROR_INCOME_INVALID,
                    'message' => ErrorMessage::getDefaultMessage(Error::ERROR_INCOME_INVALID),
                ),
            ),
        );

        $validator = new NumberValidator(
            $this->data->getCustomer()->getEmploymentData()->getMonthlyIncome(),
            $incomeConfig
        );

        return $validator;
    }

    /**
     * @return NumberValidator
     */
    protected function getBankAccountValidator()
    {
        $incomeConfig = array(
            'max_length' => 10,
            'min_length' => 6,
            'error_message' => array(
                'default' => array(
                    'key' => Error::ERROR_ACCOUNTNUMBER_INVALID,
                    'message' => ErrorMessage::getDefaultMessage(Error::ERROR_ACCOUNTNUMBER_INVALID),
                ),
            ),

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
