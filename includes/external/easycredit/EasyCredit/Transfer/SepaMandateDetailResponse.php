<?php


namespace EasyCredit\Transfer;

/**
 * Class SepaMandateDetailResponse
 * @package EasyCredit\Transfer
 */
class SepaMandateDetailResponse extends BaseResponse
{

    /**
     * @var string
     * @apiName text
     */
    protected $text;

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }
}
