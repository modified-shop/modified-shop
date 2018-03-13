<?php

namespace EasyCredit\Validator;

use EasyCredit\Transfer\AddressInterface;

/**
 * Class AddressNotAPackstationValidator
 *
 * @package EasyCredit\Validator
 */
class AddressNotAPackstationValidator extends AbstractValidator
{
    /**
     * The key that is looked after
     *
     * @const
     */
    const KEY_PACKSTATION   = 'Packstation';

    /**
     * The address to be validated
     *
     * @var AddressInterface
     */
    protected $address;

    /**
     * Constructor of the AddressNotAPackstationValidator
     *
     * @param AddressInterface $address
     */
    public function __construct(AddressInterface $address)
    {
        $this->address = $address;
    }

    /**
     * Returns true if the given address is not a Packstation,
     * otherwise false.
     *
     * @return boolean
     */
    public function validate()
    {
        $this->clearMessages();

        if (stristr($this->address->getStreet(), self::KEY_PACKSTATION)) {
            $this->addMessage(
                ErrorMessage::getDefaultMessage(Error::ERROR_ADDRESS_PACKSTATION),
                Error::ERROR_ADDRESS_PACKSTATION
            );
            return false;
        }

        return true;
    }
}
