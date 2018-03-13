<?php


namespace EasyCredit\Transfer;

/**
 * Class ProcessSave
 *
 * @package EasyCredit\Transfer
 */
class ProcessSave extends AbstractObject
{

    /**
     * @var ProcessSaveInput
     * @apiName       vorgangSpeichernInput
     * @transferClass EasyCredit\Transfer\ProcessSaveInput
     */
    protected $processSaveInput;

    /**
     * @return ProcessSaveInput
     */
    public function getProcessSaveInput()
    {
        return $this->processSaveInput;
    }

    /**
     * @param ProcessSaveInput $processSaveInput
     */
    public function setProcessSaveInput($processSaveInput)
    {
        $this->processSaveInput = $processSaveInput;
    }
}
