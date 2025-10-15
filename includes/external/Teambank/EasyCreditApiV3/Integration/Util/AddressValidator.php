<?php
namespace Teambank\EasyCreditApiV3\Integration\Util;

use Teambank\EasyCreditApiV3\Model\Transaction;
use Teambank\EasyCreditApiV3\Model\Address;
use Teambank\EasyCreditApiV3\Model\ShippingAddress;
use Teambank\EasyCreditApiV3\Model\ModelInterface;
use Teambank\EasyCreditApiV3\Integration\ValidationException;
use Teambank\EasyCreditApiV3\Integration\AddressValidationException;

class AddressValidator {

    protected function isCustomerSameAsBilling(Transaction $request) {
        if (!$request->getCustomerRelationship()->getOrderDoneWithLogin()) {
            return true;
        }

        if (
            trim((string)$request->getCustomer()->getFirstName()) != trim((string)$request->getOrderDetails()->getInvoiceAddress()->getFirstName()) ||
            trim((string)$request->getCustomer()->getLastName()) != trim((string)$request->getOrderDetails()->getInvoiceAddress()->getLastName())
        ) {
            return false;
        }
        return true;
    }

    public function hashAddress($address) {
        return sha1((string) $address);
    }

    public function addressesEqual(Transaction $request) {
        $diff = array_diff_assoc(
            $this->convert($request->getOrderDetails()->getShippingAddress(), true), 
            $this->convert($request->getOrderDetails()->getInvoiceAddress(), true)
        );
        return (count($diff) === 0);
    }

    public function convert($address, $convertForCompare = false)
    {
        $_address = array(
            'strasseHausNr' => $address->getAddress(),
            'plz' => $address->getZip(),
            'ort' => $address->getCity(),
            'land' => $address->getCountry()
        );

        if ($address->getAdditionalAddressInformation()) {
            $_address['adresszusatz'] = $address->getAdditionalAddressInformation();

        }

        if (!$convertForCompare && $address instanceof ShippingAddress) {
            $_address = array_merge($_address, array(
                'vorname' => $address->getFirstname(),
                'nachname'  => $address->getLastname()
            ));

            if ($address->getIsPackstation()) {
                $_address['packstation'] = true;
            }
        }

        return $_address;
    }

	public function validate($request) {
        if (!$this->isCustomerSameAsBilling($request)) {
            throw new ValidationException('Zur Zahlung mit easyCredit, müssen der Rechnungsempfänger und der Inhaber des Kundenkontos identisch sein.
                Bitte ändern Sie den Namen des Rechnungsempfängers entsprechend ab.');
        }

        if (!$this->addressesEqual($request)) {
            throw new AddressValidationException('Zur Zahlung mit easyCredit muss die Rechnungsadresse mit der Lieferadresse übereinstimmen.');
        }

        $company = $request->getCustomer()->getCompanyName();
        if (trim((string) $company) != '') {
            throw new ValidationException('Die Zahlung mit easyCredit ist nur für Privatpersonen möglich.');
        }
    }
}
