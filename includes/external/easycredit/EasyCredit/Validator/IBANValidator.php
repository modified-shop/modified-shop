<?php

namespace EasyCredit\Validator;

/**
 * Class IBANValidator
 *
 * @package EasyCredit\Validator
 */
class IBANValidator extends AbstractValidator
{
    /**
     * Returns true if the given data satifies the IBAN format,
     * otherwise false.
     *
     * @return boolean
     */
    public function validate()
    {
        $formatValid = preg_match('/^([A-Z]{2})([0-9]{2})([0-9A-Z]{12,30})/', $this->data);

        if (!$formatValid) {
            $this->invalidate();
            return false;
        }

        $isValid = self::validateIBAN($this->data);

        if (! $isValid) {
            $this->invalidate();
        }

        return $isValid;
    }

    /**
     * Returns true if the given data satifies the IBAN format,
     * otherwise false.
     *
     * @param string $iban
     * @return bool
     */
    public static function validateIBAN($iban)
    {
        return self::validateDigits(self::iban2digits($iban));
    }

    protected function invalidate()
    {
        $this->addMessage(
            ErrorMessage::getDefaultMessage(Error::ERROR_IBAN_INVALID),
            Error::ERROR_IBAN_INVALID
        );
    }

    /**
     * Function to check the number integrity.
     * The final remainer should be 1.
     *
     * @param string $digits
     * @return bool
     */
    protected static function validateDigits($digits)
    {
        $availDigits = $digits;
        $remainder = '';
        $neededDigits = 9;

        // Calculate remainder in chunks of 9 digits
        while (strlen($availDigits) > 0) {
            if (strlen($availDigits) > $neededDigits) {
                $curDigits = $remainder . substr($availDigits, 0, $neededDigits);
                $availDigits = substr($availDigits, $neededDigits);
            } else {
                $curDigits = $remainder . $availDigits;
                $availDigits = '';
            }

            // Calculate new remainder
            $remainder = (int) $curDigits % 97;
            $neededDigits = 9 - strlen($remainder);
        }

        // Check if the final remainder equals 1
        return ($remainder === 1);
    }

    /**
     * Helper function to build digits out of an IBAN
     * The digits can then be validated.
     *
     * @param string $iban
     * @return string
     */
    protected static function iban2digits($iban)
    {
        // move prefix to tail
        $input = substr($iban, 4) . substr($iban, 0, 4);

        // recalculate to digits
        $digits = '';
        for ($c = 0; $c < strlen($input); $c++) {
            $digits .= self::getVal(substr($input, $c, 1));
        }

        return $digits;
    }

    /**
     * Helper function to interchange alphanumerics to numbers
     *
     * @param string $char
     * @return int|string
     */
    protected static function getVal($char)
    {
        if (strpos('0123456789', $char)) {
            return (int) $char;
        }

        if (strpos('ABCDEFGHIJKLMNOPQRSTUVWXYZ', $char)) {
            return (ord($char) - 55);
        }

        return '';
    }
}
