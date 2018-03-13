<?php

namespace EasyCredit\Validator;

use EasyCredit\Process\Event\MessageCollector;

/**
 * Class AbstractValidator
 *
 * @package EasyCredit\Validator
 */
abstract class AbstractValidator extends MessageCollector implements ValidatorInterface
{
    /**
     * The data to be validated
     *
     * @var mixed
     */
    protected $data;

    /**
     * Constructor of the AbstractValidator.
     * The given $data will be stored in $this->data
     * and can be re-used in validate()
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        $this->data = $data;
    }

    /**
     * Implementations should return a boolean value
     * whether the validation was successfull or not
     *
     * @return boolean
     */
    abstract public function validate();
}
