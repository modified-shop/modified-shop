<?php


namespace EasyCredit\Validator;

/**
 * Class BirthdateValidator
 * @package EasyCredit\Validator
 */
class BirthdateValidator extends AbstractValidator
{

    /**
     * @return bool
     */
    public function validate()
    {
        if ($this->data instanceof \DateTime
            && $this->data->diff(new \DateTime())->y >= 18
            && $this->data->diff(new \DateTime())->y < 100
        ) {
            return true;

        }

        $this->addMessage(
            ErrorMessage::getDefaultMessage(Error::ERROR_BIRTHDATE_INVALID),
            Error::ERROR_BIRTHDATE_INVALID
        );

        return false;
    }
}
