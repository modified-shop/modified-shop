<?php

namespace EasyCredit\Transfer;

/**
 * @author info@senbyte.com
 *
 * @copyright 2017 senByte UG
 * @license
 */

/**
 * Class Decision
 * @package EasyCredit\Transfer
 */
class Decision extends AbstractObject
{
    /**
     * @var TermLimitation
     * @apiName       laufzeitgrenzen
     * @transferClass EasyCredit\Transfer\TermLimitation
     */
    protected $termLimitation;
    
    /**
     * @var     string
     * @apiName entscheidungsergebnis
     */
    protected $result;

    /**
     * @var string
     * @apiName textbaustein
     */
    protected $boilerplate;

    /**
     * @return TermLimitation
     */
    public function getTermLimitation()
    {
        return $this->termLimitation;
    }

    /**
     * @param TermLimitation $termLimitation
     */
    public function setTermLimitation($termLimitation)
    {
        $this->termLimitation = $termLimitation;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return string
     */
    public function getBoilerplate()
    {
        return $this->boilerplate;
    }

    /**
     * @param string $boilerplate
     */
    public function setBoilerplate($boilerplate)
    {
        $this->boilerplate = $boilerplate;
    }
}