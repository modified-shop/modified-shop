<?php
/**
 * Form input values
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
 * KiTT_InputValues
 *
 * Data for input values
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_InputValues
{
    /**
     * Update members with values for given array
     *
     * @param type $array data to update with
     *
     * @return void
     */
    public function merge($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Extract values for input fields from address object
     *
     * @param KlarnaAddr $addr address to get data from
     *
     * @return void
     */
    public function setAddress($addr)
    {
        $reference = @($addr->getFirstName() . ' ' . $addr->getLastName());
        $cellno = $addr->getCellno();
        $telno = $addr->getTelno();
        $phone = (strlen($cellno) > 0) ? $cellno : $telno;
        $this->company_name = utf8_encode($addr->getCompanyName());
        $this->first_name = utf8_encode($addr->getFirstName());
        $this->last_name = utf8_encode($addr->getLastName());
        $this->phone_number = utf8_encode($phone);
        $this->zipcode = utf8_encode($addr->getZipCode());
        $this->city = utf8_encode($addr->getCity());
        $this->street = utf8_encode($addr->getStreet());
        $this->house_number = utf8_encode($addr->getHouseNumber());
        $this->house_extension = utf8_encode($addr->getHouseExt());
        $this->reference = utf8_encode($reference);
    }

    /**
     * Given a ISO 8601 date string (YYYY-MM-DD) sets birth_year, birth_month
     * and birth_day
     *
     * @param string $dob Date of birth
     *
     * @return void
     */
    public function setBirthDay($dob)
    {
        $splitbday = explode('-', $dob);
        $this->birth_year = @$splitbday[0];
        $this->birth_month = @$splitbday[1];
        $this->birth_day = @$splitbday[2];
    }
}
