<?php

namespace EasyCredit\Validator;

/**
 * Class BasicValidatorGroup
 *
 * @package EasyCredit\Validator
 */
class BasicValidatorGroup extends AbstractValidator
{
    /**
     * @var ValidatorInterface[]
     */
    protected $validators = array();

    /**
     * Adds a new validator to the validators list
     *
     * @param ValidatorInterface
     */
    public function addValidator(ValidatorInterface $validator)
    {
        if (!in_array($validator, $this->validators)) {
            array_push($this->validators, $validator);
        }
    }

    /**
     * Returns the list of validators
     *
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Validates with all validators
     *
     * @return boolean
     */
    public function validate()
    {
        $valid = true;
        $this->clearMessages();

        foreach ($this->validators as $validator) {
            if (!$validator->validate()) {
                $valid = false;
                $this->addMessages($validator->getMessages());
            }
        }

        return $valid;
    }
}
