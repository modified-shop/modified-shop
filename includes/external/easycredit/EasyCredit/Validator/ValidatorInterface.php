<?php

namespace EasyCredit\Validator;

/**
 * Interface ValidatorInterface
 *
 * @package EasyCredit\Validator
 */
interface ValidatorInterface
{
    /**
     * @return boolean
     */
    public function validate();

    /**
     * @return array
     */
    public function getMessages();
}
