<?php

namespace EasyCredit\Validator;

/**
 * Class ErrorMessage
 *
 * Provides standard messages to errors
 *
 * @package EasyCredit\Validator
 */
class ErrorMessage
{
    /**
     * Used no static initializer to support PHP < 5.6
     *
     * @var array
     */
    protected static $messages;

    /**
     * Returns a default error message for a given error key.
     * If an invalid key is given, an Exception is thrown.
     *
     * @param string $key
     * @return string
     * @throws \Exception
     */
    public static function getDefaultMessage($key)
    {
        static::initialize();
        if (array_key_exists($key, static::$messages)) {
            return static::$messages[$key];
        }

        throw new \Exception('There is no error message with the given key '.$key);
    }

    /**
     * Helper function to initialize the the error messages array.
     * Used no static initializer to support PHP < 5.6
     */
    protected static function initialize()
    {
        if (is_array(static::$messages)) {
            return;
        }

        static::$messages = array(
            Error::ERROR_ADDRESS_PACKSTATION => 'Die Lieferung an eine Packstation ist nicht möglich.',
            Error::ERROR_ADDRESS_NOT_IN_GERMANY => 'Die Liefer- und Rechnungsadresse müssen in Deutschland sein.',
            Error::ERROR_ADDRESS_UNEQUAL => 'Liefer- und Rechnungsadresse müssen gleich sein.',
            Error::ERROR_BIRTHDATE_INVALID => 'Bitte geben Sie ein gültiges Geburtsdatum ein.',
            Error::ERROR_INCOME_INVALID => 'Bitte geben Sie Ihr Einkommen an.',
            Error::ERROR_INCOME_INVALID_INTEGER => 'Bitte geben Sie Ihr Einkommen als ganze Zahl an.',
            Error::ERROR_BANKCODE_INVALID => 'Bitte geben Sie Ihre Bankleitzahl an.',
            Error::ERROR_ACCOUNTNUMBER_INVALID => 'Bitte geben Sie Ihre Kontonummer an.',
            Error::ERROR_IBAN_INVALID => 'Bitte korrigieren Sie Ihre IBAN-Bankverbindung.',
            Error::ERROR_CONSENT_INVALID => 'Bitte lesen und stimmen Sie der Einverständniserklärung für die Zahlungsart easyCredit-Ratenkauf zu.',
            Error::ERROR_SEPA_AGREEMENT_INVALID => 'SEPA Zustimmung fehlt',
            Error::ERROR_MOBILEPHONE_INVALID => 'Bitte geben Sie Ihre Mobiltelefonnummer an.',
        );
    }
}
