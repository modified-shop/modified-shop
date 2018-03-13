<?php

namespace EasyCredit\Validator;

use EasyCredit\Transfer\AddressInterface;

/**
 * Class AddressInGermanyValidator
 *
 * @package EasyCredit\Validator
 */
class AddressInGermanyValidator extends AbstractValidator
{
    /**
     * Allowed countries, comma separated
     *
     * @const
     */
    const ALLOWED_COUNTRIES = 'DE';

    /**
     * The address to be validated
     *
     * @var AddressInterface
     */
    protected $address;

    /**
     * Constructor of the AddressInGermanyValidator
     *
     * @param AddressInterface $address
     */
    public function __construct(AddressInterface $address)
    {
        $this->address = $address;
    }

    /**
     * Returns true if the given address is in Germany,
     * otherwise false.
     *
     * @return boolean
     */
    public function validate()
    {
        $this->clearMessages();

        $countryCode = strtoupper($this->address->getCountryCode());

        if (!in_array($countryCode, $this->getAllowedCountries())) {
            $this->addMessage(
                ErrorMessage::getDefaultMessage(Error::ERROR_ADDRESS_NOT_IN_GERMANY),
                Error::ERROR_ADDRESS_NOT_IN_GERMANY
            );
            return false;
        }

        return true;
    }

    /**
     * Returns a list of allowed countries.
     * The list contains 2-letter country codes.
     *
     * @return array
     */
    private function getAllowedCountries()
    {
        return explode(',', self::ALLOWED_COUNTRIES);
    }
}
