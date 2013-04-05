<?php
/**
 * Address handling functions
 *
 * PHP Version 5.2
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
require_once "class.KlarnaCore.php";

/**
 * Address handling class.
 *
 * @category Payment
 * @package  Klarna_Module_XtCommerce
 * @author   MS Dev <ms.modules@klarna.com>
 * @license  http://opensource.org/licenses/BSD-2-Clause BSD2
 * @link     http://integration.klarna.com
 */
class KlarnaAddressXtc
{
    /**
    * Build a KlarnaAddr from an osCommerce order address array, and takes
    * missing information from $_POST (collected from our checkout).
    *
    * @param array $xtcAddress xtcommerce order address array
    *
    * @return KlarnaAddr klarnaAddr object
    */
    public function xtcAddressToKlarnaAddr($xtcAddress)
    {
        $country = strtolower($xtcAddress['country']['iso_code_2']);
        $splitAddr = KiTT_Addresses::splitStreet(
            $xtcAddress["street_address"], $country
        );
        $street = '';
        $houseno = '';
        $housext = '';

        if (array_key_exists('street', $splitAddr)) {
            $street = $splitAddr['street'];
        }

        if (array_key_exists('house_number', $splitAddr)) {
            $houseno = $splitAddr['house_number'];
        }

        if (array_key_exists('house_extension', $splitAddr)) {
            $housext = $splitAddr['house_extension'];
        }

        $address = new KlarnaAddr(
            KiTT_String::encode($_POST["klarna_email"]),
            KiTT_String::encode($_POST["klarna_phone"]),
            KiTT_String::encode($_POST["klarna_phone"]),
            KiTT_String::encode($xtcAddress["firstname"]),
            KiTT_String::encode($xtcAddress["lastname"]),
            "",
            KiTT_String::encode($street),
            KiTT_String::encode($xtcAddress["postcode"]),
            KiTT_String::encode($xtcAddress["city"]),
            KiTT_String::encode($country),
            KiTT_String::encode($houseno),
            KiTT_String::encode($housext)
        );
        return $address;
    }

    /**
     * Match an address from the checkout with an address from getAddress, and
     * return the matching address.
     *
     * @param array  &$errors reference to errors array
     * @param string $option  payment method
     *
     * @return object KlarnaAddr object
     */
    public function getMatchingAddress(
        &$errors, $option
    ) {
        $addrs = array();

        $pno = $_POST["klarna_{$option}_pno"];

        $_SESSION['klarna_data']['pno'] = $pno;
        $_SESSION['klarna_data']['phone']
            = $_POST["klarna_{$option}_phone_number"];
        $address = new KlarnaAddr;
        $KITTaddr = new KiTT_Addresses(KiTT::api('SE'));

        try {
            $address = $KITTaddr->getMatchingAddress(
                $pno,
                $_POST["klarna_{$option}_address_key"]
            );
            $address->setTelno($_POST["klarna_{$option}_phone_number"]);
            $address->setCellno($_POST["klarna_{$option}_phone_number"]);
            $address->setEmail($_POST["klarna_email"]);
        } catch (Exception $e) {
            Klarna::printDebug('Error in __METHOD__', $e->getMessage());
            $errors[] = "error_no_address";
        }
        return $address;
    }

    /**
     * Convert a given array to a KlarnaAddr object.
     *
     * @param array  $array   an array of customer data
     * @param string $country the customers country
     *
     * @return KlarnaAddr object
     */
    public function buildKlarnaAddressFromArray($array, $country)
    {
        $address = new KlarnaAddr(
            "",
            KiTT_String::encode($array["phone_number"]),
            KiTT_String::encode($array["phone_number"]),
            KiTT_String::encode($array["first_name"]),
            KiTT_String::encode($array["last_name"]),
            "",
            KiTT_String::encode($array["street"]),
            KiTT_String::encode($array["zipcode"]),
            KiTT_String::encode($array["city"]),
            $country,
            KiTT_String::encode($array["house_number"]),
            KiTT_String::encode($array["house_extension"])
        );

        if ($array["klarna_invoice_type"] == "company") {
            $address->isCompany = true;
            $address->setCompanyName(
                KiTT_String::encode($array["company_name"])
            );


            $name = explode(
                ' ', KiTT_String::encode($array["reference"]), 2
            );

            if (strlen($name[0]) > 0) {
                $address->setFirstName($name[0]);
            } else {
                $address->setFirstName(" ");
            }
            if (count($name) > 1 && strlen($name[1]) > 0) {
                $address->setLastName($name[1]);
            } else {
                $address->setLastName(" ");
            }
        }
        return $address;
    }

    /**
     * Handle the values from the checkout (in the _POST) so we can save and
     * use them later.
     *
     * @param string $option 'inv', 'part' or 'spec'
     *
     * @return array
     */
    public function addressArrayFromPost($option)
    {
        return array(
            "gender" => $_POST["klarna_{$option}_gender"],
            "pno" => $_POST["klarna_{$option}_pno"],
            "first_name" => $_POST["klarna_{$option}_first_name"],
            "last_name" => $_POST["klarna_{$option}_last_name"],
            "street" => $_POST["klarna_{$option}_street"],
            "house_number" => $_POST["klarna_{$option}_house_number"],
            "zipcode" => $_POST["klarna_{$option}_zipcode"],
            "house_extension" => $_POST["klarna_{$option}_house_extension"],
            "reference" => $_POST["klarna_{$option}_reference"],
            "city" => $_POST["klarna_{$option}_city"],
            "phone_number" => $_POST["klarna_{$option}_phone_number"],
            "company_name" => $_POST["klarna_{$option}_company_name"],
            "klarna_invoice_type" => $_POST["klarna_{$option}_invoice_type"]
        );
    }

    /**
     * Build an xtcommerce address Array from a KlarnaAddr object.
     *
     * @param object $address KlarnaAddr object
     *
     * @return array xtcommerce address
     */
    public function klarnaAddrToXtcAddr($address)
    {
        global $order;
        return array(
            'firstname' => KiTT_String::decode($address->getFirstName()),
            'lastname' => KiTT_String::decode($address->getLastName()),
            'street_address' => KiTT_String::decode(
                $address->getStreet() . ' ' . $address->getHouseNumber() .
                ' ' . $address->getHouseExt()
            ),
            'postcode' => KiTT_String::decode($address->getZipCode()),
            'city' => KiTT_String::decode($address->getCity()),
            'telephone' => KiTT_String::decode($address->getTelNo()),
            'email_address' => KiTT_String::decode($address->getEmail()),
            'company' => KiTT_String::decode($address->getCompanyName()),

            //Set same country information as delivery
            'state' => $order->delivery['state'],
            'zone_id' => $order->delivery['zone_id'],
            'country_id' => $order->delivery['country_id'],
            'country' => array(
                'id' => $order->delivery['country']['id'],
                'title' => $order->delivery['country']['title'],
                'iso_code_2' => $order->delivery['country']['iso_code_2'],
                'iso_code_3' => $order->delivery['country']['iso_code_3']
            )
        );
    }
}
