<?php
/**
 * @author info@senbyte.com
 * @copyright 2016 senByte UG
 * @license
 */

namespace EasyCredit\Transfer;

/**
 * Class CallbackUrls
 *
 * @package EasyCredit\Transfer
 */
class CallbackUrls extends AbstractObject
{
    /**
     * @var string
     * @apiName urlErfolg
     */
    protected $urlSucceeded;

    /**
     * @var string
     * @apiName urlAbbruch
     */
    protected $urlCancelled;

    /**
     * @var string
     * @apiName urlAblehnung
     */
    protected $urlDenied;

    /**
     *
     * @return string
     */
    public function getUrlSucceeded()
    {
        return $this->urlSucceeded;
    }

    /**
     *
     * @param string $urlSucceeded
     */
    public function setUrlSucceeded($urlSucceeded)
    {
        $this->urlSucceeded = $urlSucceeded;
    }

    /**
     *
     * @return string
     */
    public function getUrlCancelled()
    {
        return $this->urlCancelled;
    }

    /**
     *
     * @param string $urlCancelled
     */
    public function setUrlCancelled($urlCancelled)
    {
        $this->urlCancelled = $urlCancelled;
    }

    /**
     *
     * @return string
     */
    public function getUrlDenied()
    {
        return $this->urlDenied;
    }

    /**
     *
     * @param string $urlDenied
     */
    public function setUrlDenied($urlDenied)
    {
        $this->urlDenied = $urlDenied;
    }

}
