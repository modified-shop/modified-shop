<?php

namespace EasyCredit\Transfer;

/**
 * Interface AddressInterface
 *
 * @package EasyCredit\Transfer
 */
interface AddressInterface
{
    /**
     * @return string
     */
    public function getStreet();

    /**
     * @return string
     */
    public function getAddressAdditional();

    /**
     * @return string
     */
    public function getZip();

    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string
     */
    public function getCountryCode();
}
