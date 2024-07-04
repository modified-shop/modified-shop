<?php

namespace EasyCredit\SaveHandler;

use \EasyCredit\Transfer\TransferInterface;

/**
 * Class SessionHandler
 */
class SessionHandler implements SaveHandlerInterface
{

    /**
     * @param TransferInterface $data
     */
    public function clear(TransferInterface $data)
    {
        unset($_SESSION[md5(get_class($data))]);
    }

    /**
     * @param TransferInterface $data
     */
    public function save(TransferInterface $data)
    {
        $_SESSION[md5(get_class($data))] = $data->serialize();
    }

    /**
     * @param TransferInterface $data
     * @return null
     */
    public function get(TransferInterface $data)
    {
        return (isset($_SESSION[md5(get_class($data))]) ? unserialize($_SESSION[md5(get_class($data))]) : null);
    }
}
