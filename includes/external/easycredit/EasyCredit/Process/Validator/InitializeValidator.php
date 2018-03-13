<?php

namespace EasyCredit\Process\Validator;

use EasyCredit\Validator\AddressEqualityValidator;
use EasyCredit\Validator\AddressInGermanyValidator;
use EasyCredit\Validator\AddressNotAPackstationValidator;

/**
 * Class InitializeValidator
 *
 * @package EasyCredit\Process\Validator
 */
class InitializeValidator extends AbstractValidator
{
    /**
     * Creates the list of relevant validators for the initialize step
     */
    protected function initValidators()
    {
        $billingAddress = $this->data->getBillingAddress();
        $deliveryAddress = $this->data->getDeliveryAddress();
        $this->addValidator(new AddressEqualityValidator($billingAddress, $deliveryAddress));
        $this->addValidator(new AddressInGermanyValidator($billingAddress));
        $this->addValidator(new AddressNotAPackstationValidator($deliveryAddress));
    }
}
