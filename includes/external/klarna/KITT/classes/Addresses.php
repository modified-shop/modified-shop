<?php
/**
 * Class for working with addresses in KRED
 *
 * PHP Version 5.3
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */

/**
 * KiTT_Addresses
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_Addresses
{
    /**
     * @var string
     */
    private static $_session_key = 'klarna_address';

    /**
     * @var Klarna
     */
    private $_api;

    /**
     * Create KlarnaAddresses
     *
     * @param Klarna $api api used to delegate remote calls
     */
    public function __construct($api)
    {
        $this->_api = $api;
    }

    /**
     * Get a unique key used to identify the given address
     *
     * The key is a hash of the lower bit ascii portion of company name,
     * first name, last name and street joined with pipes
     *
     * @param KlarnaAddr $addr address
     *
     * @return string key for this address
     */
    public static function getAddressKey($addr)
    {
        return hash(
            'crc32',
            preg_replace(
                '/[^\w]*/', '',
                $addr->getCompanyName() . '|' .
                $addr->getFirstName() . '|' .
                $addr->getLastName() . '|' .
                $addr->getStreet()
            )
        );
    }

    /**
     * Wrapper around remote get_addresses call that caches result in
     * the session.
     *
     * @param string $pno pno to call get_addresses for
     *
     * @return array the result of get_addresses or the cached value
     */
    public function getAddresses($pno)
    {
        $cache = array();

        // Check the session for calls
        if (array_key_exists(self::$_session_key, $_SESSION)) {
            $cache = unserialize(
                base64_decode($_SESSION[self::$_session_key])
            );
        }

        if (array_key_exists($pno, $cache)) {
            return $cache[$pno];
        }

        $addrs = $this->_api->getAddresses(
            $pno, null, KlarnaFlags::GA_GIVEN
        );

        $cache[$pno] = $addrs;
        $_SESSION[self::$_session_key] = base64_encode(
            serialize($cache)
        );

        return $addrs;
    }

    /**
     * Get the address identified by unique key
     *
     * @param string $pno the person to get addresses for
     * @param string $key a unique key as generate by getAddressKey
     *
     * @throws KlarnaException when no matching address was found
     * @return KlarnaAddr address identified by key
     */
    public function getMatchingAddress($pno, $key)
    {
        $addrs = $this->getAddresses($pno);
        if (count($addrs) == 1 && strlen($key) < 1) {
            return $addrs[0];
        }

        foreach ($addrs as $addr) {
            $akey = self::getAddressKey($addr);
            if ($akey == $key) {
                return $addr;
            }
        }

        throw new KiTT_Exception("No matching address found");
    }

    /**
     * Get the formatted street required for a Klarna Addr
     *
     * @param string $street  The street to split
     * @param mixed  $country The country to split for
     *
     * @return array
     */
    public static function splitStreet($street, $country)
    {
        $country = KiTT_Locale::parseCountry($country);

        $split = KiTT_CountryLogic::getSplit($country);
        $elements = self::splitAddress($street);

        $result = array('street' => $elements[0]);

        if (in_array('house_extension', $split)) {
            $result['house_extension'] = $elements[2];
        } else {
            $elements[1] .= ' ' . $elements[2];
        }

        if (in_array('house_number', $split)) {
            $result['house_number'] = $elements[1];
        } else {
            $result['street'] .= ' ' . $elements[1];
        }

        return array_map('trim', $result);
    }

    /**
     * Split a string into an array consisting of Street, House Number and
     * House extension.
     *
     * @param string $address Address string to split
     *
     * @return array
     */
    public static function splitAddress($address)
    {
        // Get everything up to the first number with a regex
        $hasMatch = preg_match('/^[^0-9]*/', $address, $match);

        // If no matching is possible, return the supplied string as the street
        if (!$hasMatch) {
            return array($address, "", "");
        }

        // Remove the street from the address.
        $address = str_replace($match[0], "", $address);
        $street = trim($match[0]);

        // Nothing left to split, return
        if (strlen($address) == 0) {
            return array($street, "", "");
        }
        // Explode address to an array
        $addrArray = explode(" ", $address);

        // Shift the first element off the array, that is the house number
        $housenumber = array_shift($addrArray);

        // If the array is empty now, there is no extension.
        if (count($addrArray) == 0) {
            return array($street, $housenumber, "");
        }

        // Join together the remaining pieces as the extension.
        $extension = implode(" ", $addrArray);

        return array($street, $housenumber, $extension);
    }
}
