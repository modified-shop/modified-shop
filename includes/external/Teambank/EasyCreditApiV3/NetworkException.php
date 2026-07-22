<?php
/**
 * NetworkException
 *
 * @category Class
 * @package  Teambank\EasyCreditApiV3
 */

namespace Teambank\EasyCreditApiV3;

use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Thrown by Client::sendRequest() when a request could not be sent at all
 * (e.g. connection failure, timeout) - as required by PSR-18 for network
 * failures.
 */
class NetworkException extends \Exception implements NetworkExceptionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct($message, RequestInterface $request, $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->request = $request;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
