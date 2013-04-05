<?php
/**
 * The Klarna AJAX Dispatcher.
 * Dispatches calls to the AJAX provider
 *
 * PHP Version 5.3
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */

/**
 * KiTT_Dispatcher
 *
 * Dispatches calls by name to a object in a safe way. By being a external
 * class it's restricted to public methods of the object and special care is
 * taken to not call methods starting with __ even if public
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_Dispatcher
{
    public static $charset = 'ISO-8859-1';

    /**
     * Proxy to session functions
     *
     * @var KiTT_Session
     */
    private $_session;

    /**
     * Object to dispatch calls to
     */
    private $_target;

    /**
     * Create a KlarnaDispatcher
     *
     * @param KiTT_Session $session session function proxy
     * @param object       $target  object to dispatch calls to
     */
    public function __construct ($session, $target)
    {
        $this->_session = $session;
        $this->_target = $target;
    }

    /**
     * Dispatch calls to method with matching name on target and echo the
     * response or an error message
     *
     * if no action is passed the action will be taken from GET or POST as
     * 'action'
     *
     * @param object $action name of action
     *
     * @return void
     */
    public function dispatch ($action = null)
    {
        try {
            $sid = $this->_session->session_id();
            if (empty($sid)) {
                throw new KiTT_Exception("No session");
            }

            // Grab action from GET/POST if not passed explicitly
            if ($action === null) {
                $action = KiTT_HTTPContext::toString('action');
            }

            // Check that we have a valid action
            if ($action == null) {
                throw new KiTT_Exception("No action defined!");
            }

            if (substr($action, 0, 2) == '__') {
                throw new KiTT_Exception("Invalid action");
            }

            if (!method_exists($this->_target, $action)) {
                throw new KiTT_Exception("Invalid action");
            }

            // call implementation, this may raise an exception
            $response = $this->_target->$action();

            $this->outputResponse($response);

        } catch(Exception $e) {
            $this->outputError($e);
        }
    }

    /**
     * Sends the Content-Type header
     *
     * @param string $type    content-type
     * @param string $charset charset
     *
     * @return void
     */
    protected function contentType($type = null, $charset = null)
    {
        if ($type === null) {
            $type = 'text/plain';
        }
        if ($charset === null) {
            $charset = self::$charset;
        }
        header("Content-Type: {$type}; charset={$charset}");
    }

    /**
     * Given an Exception constructs an error json and echos
     *
     * @param Exception $e the exception to render
     *
     * @return void
     */
    protected function outputError($e)
    {
        $this->contentType('application/json');
        header("HTTP/1.0 500 Internal Server Error");
        echo json_encode(
            array(
                'error' => array(
                    'type' => get_class($e),
                    'message' => utf8_encode($e->getMessage())
                )
            )
        );
    }

    /**
     * Outputs the response
     * if response is an array it's expected to have these members
     * value - the response the be sent
     * type - the mime type
     * [charset] - charset extension of content-type (optional)
     *
     * @param array|string $response array or string containing the response data
     *
     * @return void
     */
    protected function outputResponse($response)
    {
        if (is_array($response)) {
            $this->contentType($response['type'], @$response['charset']);
            echo $response['value'];
        } else {
            $this->contentType();
            echo $response;
        }
    }
}
