<?php
/**
 * The Klarna AJAX Dispatcher.
 * Dispatches calls to the AJAX provider
 *
 * @package     Klarna Standard Kassa API
 * @version     ##KLARNA_CHECKOUT_VERSION##
 * @since       ##KLARNA_CHECKOUT_DATE##
 * @link        http://integration.klarna.com/
 * @copyright   Copyright (c) 2011 Klarna AB (http://klarna.com)
 */

require_once ('class.KlarnaAPI.php');
require_once ('class.KlarnaHTTPContext.php');

/**
 * KlarnaDispatcher
 *
 * Dispatches calls by name to a object in a safe way. By being a external
 * class it's restricted to public methods of the object and special care is
 * taken to not call methods starting with __ even if pubilc
 */
class KlarnaDispatcher {
    public static $charset = 'ISO-8859-1';

    private $target;

    public function __construct ($target) {
        $this -> target = $target;
    }

    public function dispatch ($action = null) {
        try {
            $sid = session_id ();
            if  (empty ($sid)) {
                throw new KlarnaApiException("No session");
            }

            // Grab action from GET/POST if not passed explicitly
            if ($action === null) {
                $action = KlarnaHTTPContext::toString('action');
            }

            // Check that we have a valid action
            if ($action == null) {
                throw new KlarnaApiException("No action defined!");
            }

            if (substr ($action, 0, 2) == '__') {
                throw new KlarnaApiException("Invalid action");
            }

            if (!method_exists ($this->target, $action)) {
                throw new KlarnaApiException("Invalid action");
            }

            // call implementation, this may raise an exception
            $response = $this -> target -> $action ();

            $this -> outputResponse ($response);

        } catch(Exception $e) {
            $this -> outputError($e);
        }
    }

    protected function contentType($type = null, $charset = null) {
        if ($type === null) {
            $type = 'text/plain';
        }
        if ($charset === null) {
            $charset = self::$charset;
        }
        header ("Content-Type: {$type}; charset={$charset}");
    }

    /**
     * Given an Exception constructs an error xml and echos
     */
    protected function outputError($e) {
        $this -> contentType ('text/xml');
        $xml = new SimpleXMLElement('<errorMessages><error/></errorMessages>');
        $error = $xml -> children();
        $error -> addChild ('type', get_class ($e));
        $error -> addChild ('message',
            Klarna::num_htmlentities ($e -> getMessage ()));
        $error -> addChild ('code', $e -> getCode ());

        echo $xml -> asXML ();
    }

    /**
     * Outputs the response
     * if response is an array it's expected to have these members
     * value - the response the be sent
     * type - the mime type
     * [charset] - charset extension of content-type (optional)
     *
     * @param mixed $response array or string containing the response data
     */
    protected function outputResponse($response) {
        if (is_array ($response)) {
            $this -> contentType ($response['type'], @$response['charset']);
            echo $response['value'];
        } else {
            $this -> contentType ();
            echo $response;
        }
    }
}

?>
