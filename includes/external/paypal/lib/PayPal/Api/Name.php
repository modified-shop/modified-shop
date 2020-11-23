<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class Name
 *
 * The name of the payer.
 *
 * @package PayPal\Api
 *
 * @property string given_name
 * @property string surname
 * @property string full_name
 */
class Name extends PayPalModel
{
    /**
     * When the party is a person, the party's given, or first, name. 
     *
     * @param string $given_name
     * 
     * @return $this
     */
    public function setGivenName($given_name)
    {
        $this->given_name = $given_name;
        return $this;
    }

    /**
     * When the party is a person, the party's given, or first, name. 
     *
     * @return string
     */
    public function getGivenName()
    {
        return $this->given_name;
    }

    /**
     * When the party is a person, the party's surname or family name. Also known as the last name. Required when the party is a person. Use also to store multiple surnames including the matronymic, or mother's, surname. 
     *
     * @param string $currency
     * 
     * @return $this
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
        return $this;
    }

    /**
     * When the party is a person, the party's surname or family name. Also known as the last name. Required when the party is a person. Use also to store multiple surnames including the matronymic, or mother's, surname. 
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }
    
    /**
     * When the party is a person, the party's full name. 
     *
     * @param string $full_name
     * 
     * @return $this
     */
    public function setFullName($full_name)
    {
        $this->full_name = $full_name;
        return $this;
    }

    /**
     * When the party is a person, the party's full name. 
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

}
