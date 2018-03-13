<?php

namespace EasyCredit\Validator;

use EasyCredit\Transfer\AddressInterface;

/**
 * Class AddressEqualityValidator
 *
 * @package EasyCredit\Transfer\AddressInterface
 */
class AddressEqualityValidator extends AbstractValidator
{
    /**
     * Address to be checked for equality
     *
     * @var AddressInterface
     */
    protected $addressA;

    /**
     * Address to be checked for equality
     *
     * @var AddressInterface
     */
    protected $addressB;

    /**
     * Constructor of the AddressEqualityValidator
     *
     * @param AddressInterface $addressA
     * @param AddressInterface $addressB
     */
    public function __construct(AddressInterface $addressA, AddressInterface $addressB)
    {
        $this->addressA = $addressA;
        $this->addressB = $addressB;
    }

    /**
     * Returns true if the two addresses are exactly the same
     *
     * @return boolean
     */
    public function validate()
    {
        if ($this->addressA->getCountryCode() !== $this->addressB->getCountryCode()) {
            $this->invalidate();
            return false;
        }

        if ($this->addressA->getZip() !== $this->addressB->getZip()) {
            $this->invalidate();
            return false;
        }

        if ($this->addressA->getCity() !== $this->addressB->getCity()) {
            $this->invalidate();
            return false;
        }

        if ($this->addressA->getStreet() !== $this->addressB->getStreet()) {
            $this->invalidate();
            return false;
        }

        if ($this->addressA->getAddressAdditional() !== $this->addressB->getAddressAdditional()) {
            $this->invalidate();
            return false;
        }

        return true;
    }

    /**
     * Helper function to add an error message.
     */
    private function invalidate()
    {
        $this->addMessage(
            ErrorMessage::getDefaultMessage(Error::ERROR_ADDRESS_UNEQUAL),
            Error::ERROR_ADDRESS_UNEQUAL
        );
    }
}
