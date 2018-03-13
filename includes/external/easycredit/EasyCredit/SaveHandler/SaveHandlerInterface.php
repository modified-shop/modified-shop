<?php

namespace EasyCredit\SaveHandler;

use EasyCredit\Transfer\TransferInterface;

interface SaveHandlerInterface
{
    /**
     * @param TransferInterface $data
     */

    public function save(TransferInterface $data);
    
    /**
     * @param TransferInterface $data
     */
    public function get(TransferInterface $data);
}
