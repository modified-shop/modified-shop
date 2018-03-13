<?php

namespace EasyCredit\Api\Request;

use EasyCredit\Transfer\TransferInterface;

/**
 * Interface RequestInterface
 *
 * @package EasyCredit\Api\Request
 */
interface RequestInterface
{
    /**
     * @return TransferInterface
     */
    public function getTransferClass();


    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return string
     */
    public function getPath();

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @return string|null
     */
    public function getBody();

    /**
     * @return array
     */
    public function getHeaders();
}
