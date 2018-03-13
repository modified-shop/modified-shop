<?php

namespace EasyCredit\Process\Validator;

use EasyCredit\Transfer\ProcessData;
use EasyCredit\Validator\BasicValidatorGroup;

/**
 * Class AbstractValidator
 *
 * @package EasyCredit\Process\Validator
 */
abstract class AbstractValidator extends BasicValidatorGroup
{
    /**
     * AbstractValidator constructor.
     *
     * @param ProcessData $data
     */
    public function __construct(ProcessData $data)
    {
        parent::__construct($data);
        $this->initValidators();
    }

    /**
     * Implement your setup stuff here
     */
    abstract protected function initValidators();
}
