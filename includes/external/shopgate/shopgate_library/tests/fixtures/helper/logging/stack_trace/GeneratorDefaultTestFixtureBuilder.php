<?php

class Shopgate_Helper_Logging_Stack_Trace_GeneratorDefaultTestFixtureBuilder
{
    /** @var PHPUnit_Framework_TestCase */
    private $testCase;
    
    /**
     * @param PHPUnit_Framework_TestCase $testCase The calling test case; mock objects might be built using this.
     */
    public function __construct(PHPUnit_Framework_TestCase $testCase)
    {
        $this->testCase = $testCase;
    }
    
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub|Throwable
     */
    public function getSimpleException()
    {
        return $this->buildMockFromFixture(
            array(
                'exception_class' => 'DumboLandingException',
                'message'         => 'Landing failed.',
                'code'            => 99,
                'file'            => '/Animals/Mammals/Elephants/Dumbo.php',
                'line'            => 34,
                'trace'           => $this->getTraceFixture('DumboLandingException'),
                'previous'        => null,
            )
        );
    }
    
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub|Throwable
     */
    public function getExceptionWithPreviousExceptions()
    {
        return $this->buildMockFromFixture(
            array(
                'exception_class' => 'DumboLandingException',
                'message'         => 'Landing failed.',
                'code'            => 99,
                'file'            => '/Animals/Mammals/Elephants/Dumbo.php',
                'line'            => 34,
                'trace'           => $this->getTraceFixture('DumboLandingException'),
                'previous'        => array(
                    'exception_class' => 'DumboHurtException',
                    'message'         => 'Dumbo is hurt.',
                    'code'            => 168,
                    'file'            => '/Animals/Mammals/Elephants/Dumbo/Legs.php',
                    'line'            => 48,
                    'trace'           => $this->getTraceFixture('DumboHurtException'),
                    'previous'        => array(
                        'exception_class' => 'DumboBrokenLegException',
                        'message'         => 'Dumbo has a broken leg.',
                        'code'            => 256,
                        'file'            => '/Animals/Mammals/Elephants/Dumbo/Legs/Leg.php',
                        'line'            => 130,
                        'trace'           => $this->getTraceFixture('DumboBrokenLegException'),
                        'previous'        => null,
                    ),
                ),
            )
        );
    }
    
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub|Throwable
     */
    public function getExceptionExampleForFailedGetCustomer()
    {
        return $this->buildMockFromFixture(
            array(
                'exception_class' => 'ShopgateLibraryExceptionStub',
                'message'         => 'wrong username or password: Username or password is incorrect',
                'code'            => 71,
                'file'            => '/var/www/cart/plugins/shopgate/plugin.php',
                'line'            => 158,
                'trace'           => $this->getTraceFixture('ShopgateLibraryExceptionStub'),
                'previous'        => array(
                    'exception_class' => 'LoginException',
                    'message'         => 'Wrong username or password',
                    'code'            => 196,
                    'file'            => '/var/www/cart/classes/User.php',
                    'line'            => 223,
                    'trace'           => $this->getTraceFixture('LoginException'),
                    'previous'        => null,
                ),
            )
        );
    }
    
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub|Throwable
     */
    public function getExceptionWithMissingFileAndLineFixture()
    {
        $trace = $this->getTraceFixture('DumboLandingException');
        unset($trace[0]['file']);
        unset($trace[0]['line']);
        unset($trace[1]['file']);
        unset($trace[1]['line']);
        
        return $this->buildMockFromFixture(
            array(
                'exception_class' => 'DumboLandingException',
                'message'         => 'Landing failed.',
                'code'            => 99,
                'file'            => '/Animals/Mammals/Elephants/Dumbo.php',
                'line'            => 34,
                'trace'           => $trace,
                'previous'        => null,
            )
        );
    }
    
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub|Throwable
     */
    public function getExceptionWithMissingClassAndTypeFixture()
    {
        $trace = $this->getTraceFixture('DumboLandingException');
        unset($trace[0]['class']);
        unset($trace[0]['type']);
        unset($trace[1]['class']);
        unset($trace[1]['type']);
        
        return $this->buildMockFromFixture(
            array(
                'exception_class' => 'DumboLandingException',
                'message'         => 'Landing failed.',
                'code'            => 99,
                'file'            => '/Animals/Mammals/Elephants/Dumbo.php',
                'line'            => 34,
                'trace'           => $trace,
                'previous'        => null,
            )
        );
    }
    
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub|Throwable
     */
    public function getExceptionWithMissingFunctionFixture()
    {
        $trace = $this->getTraceFixture('DumboLandingException');
        unset($trace[0]['function']);
        
        return $this->buildMockFromFixture(
            array(
                'exception_class' => 'DumboLandingException',
                'message'         => 'Landing failed.',
                'code'            => 99,
                'file'            => '/Animals/Mammals/Elephants/Dumbo.php',
                'line'            => 34,
                'trace'           => $trace,
                'previous'        => null,
            )
        );
    }
    
    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub|Throwable
     */
    public function getExceptionWithMissingArgsFixture()
    {
        $trace = $this->getTraceFixture('DumboLandingException');
        unset($trace[0]['args']);
        
        return $this->buildMockFromFixture(
            array(
                'exception_class' => 'DumboLandingException',
                'message'         => 'Landing failed.',
                'code'            => 99,
                'file'            => '/Animals/Mammals/Elephants/Dumbo.php',
                'line'            => 34,
                'trace'           => $trace,
                'previous'        => null,
            )
        );
    }
    
    /**
     * @return string
     */
    public function getSimpleExceptionExpected()
    {
        return <<<STACK_TRACE
DumboLandingException: Landing failed.

thrown from /Animals/Mammals/Elephants/Dumbo.php on line 34
at \Animals\Mammals\Elephants\Dumbo->checkHealth() called in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) called in /Animals/Mammals/Elephants/Dumbo.php:12
STACK_TRACE;
    }
    
    /**
     * @return string
     */
    public function getExceptionWithPreviousExceptionsExpected()
    {
        return <<<STACK_TRACE
DumboLandingException: Landing failed.

thrown from /Animals/Mammals/Elephants/Dumbo.php on line 34
at \Animals\Mammals\Elephants\Dumbo->checkHealth() called in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) called in /Animals/Mammals/Elephants/Dumbo.php:12

caused by DumboHurtException: Dumbo is hurt.

thrown from /Animals/Mammals/Elephants/Dumbo/Legs.php on line 48
at \Animals\Mammals\Elephants\Dumbo\Leg->checkHealth() called in /Animals/Mammals/Elephants/Dumbo/Legs.php:45
at \Animals\Mammals\Elephants\Dumbo->checkHealth() called in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) called in /Animals/Mammals/Elephants/Dumbo.php:12

caused by DumboBrokenLegException: Dumbo has a broken leg.

thrown from /Animals/Mammals/Elephants/Dumbo/Legs/Leg.php on line 130
at \Animals\Mammals\Elephants\Dumbo\Legs\Leg->checkHealth() called in /Animals/Mammals/Elephants/Dumbo/Legs/Leg.php:114
at \Animals\Mammals\Elephants\Dumbo\Legs->checkHealthFor(left_front) called in /Animals/Mammals/Elephants/Dumbo/Legs.php:163
at \Animals\Mammals\Elephants\Dumbo\Legs->checkHealthFor(left_front) called in /Animals/Mammals/Elephants/Dumbo/Legs.php:163
at \Animals\Mammals\Elephants\Dumbo\Legs->checkHealth() called in /Animals/Mammals/Elephants/Dumbo/Legs.php:45
at \Animals\Mammals\Elephants\Dumbo->checkHealth() called in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) called in /Animals/Mammals/Elephants/Dumbo.php:12
STACK_TRACE;
    }
    
    /**
     * @return string
     */
    public function getExceptionWithPreviousExceptionsDepth2Expected()
    {
        return <<<STACK_TRACE
DumboLandingException: Landing failed.

thrown from /Animals/Mammals/Elephants/Dumbo.php on line 34
at \Animals\Mammals\Elephants\Dumbo->checkHealth() called in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) called in /Animals/Mammals/Elephants/Dumbo.php:12

caused by DumboHurtException: Dumbo is hurt.

thrown from /Animals/Mammals/Elephants/Dumbo/Legs.php on line 48
at \Animals\Mammals\Elephants\Dumbo\Leg->checkHealth() called in /Animals/Mammals/Elephants/Dumbo/Legs.php:45
at \Animals\Mammals\Elephants\Dumbo->checkHealth() called in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) called in /Animals/Mammals/Elephants/Dumbo.php:12
STACK_TRACE;
    }
    
    /**
     * @return string
     */
    public function getExceptionExampleForFailedGetCustomerObfuscationExpected()
    {
        return <<<STACK_TRACE
ShopgateLibraryExceptionStub: wrong username or password: Username or password is incorrect

thrown from /var/www/cart/plugins/shopgate/plugin.php on line 158
at ShopgatePluginMyCart->getCustomer(herp@derp.com, XXXXXXXX) called in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:80
at ShopgatePluginApi->getCustomer() called in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:857
at ShopgatePluginApi->handleRequest(Array) called in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:238
at ShopgatePlugin->handleRequest(Array) called in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/core.php:1590

caused by LoginException: Wrong username or password

thrown from /var/www/cart/classes/User.php on line 223
at User->login(herp@derp.com, XXXXXXXX) called in /var/www/cart/plugins/shopgate/plugin.php:215
at ShopgatePluginMyCart->getCustomer(herp@derp.com, XXXXXXXX) called in /var/www/cart/plugins/shopgate/plugin.php:80
at ShopgatePluginApi->getCustomer() called in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:857
at ShopgatePluginApi->handleRequest(Array) called in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:238
at ShopgatePlugin->handleRequest(Array) called in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/core.php:1590
STACK_TRACE;
    }
    
    /**
     * @return string
     */
    public function getExceptionIncompleteStackTraceInformationExpected()
    {
        return <<<STACK_TRACE
ShopgateLibraryExceptionStub: wrong username or password: Username or password is incorrect

thrown from /var/www/cart/plugins/shopgate/plugin.php on line 158
at Shopgate_Helper_Error_Handling_ErrorHandler->handle(256, nope!, /src/htdocs/public/shopify/classes/base/PluginBase.php, 296, Array) called in :
at User->login(herp@derp.com, XXXXXXXX) called in /var/www/cart/plugins/shopgate/plugin.php:215
at ShopgatePluginMyCart->getCustomer(herp@derp.com, XXXXXXXX) called in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:80
at ShopgatePluginApi->getCustomer() called in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:857
at ShopgatePluginApi->handleRequest(Array) called in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php:238
at ShopgatePlugin->handleRequest(Array) called in /var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/core.php:1590
STACK_TRACE;
    }
    
    /**
     * @return string
     */
    public function getExceptionWithMissingFileAndLineExpected()
    {
        return <<<STACK_TRACE
DumboLandingException: Landing failed.

thrown from /Animals/Mammals/Elephants/Dumbo.php on line 34
at \Animals\Mammals\Elephants\Dumbo->checkHealth() called in unknown file:unknown line
at \Animals\Mammals\Elephants\Dumbo->land(90, 30) called in unknown file:unknown line
STACK_TRACE;
    }
    
    /**
     * @return string
     */
    public function getExceptionWithMissingClassAndTypeExpected()
    {
        return <<<STACK_TRACE
DumboLandingException: Landing failed.

thrown from /Animals/Mammals/Elephants/Dumbo.php on line 34
at checkHealth() called in /Animals/Mammals/Elephants/Dumbo.php:23
at land(90, 30) called in /Animals/Mammals/Elephants/Dumbo.php:12
STACK_TRACE;
    }
    
    /**
     * @return string
     */
    public function getExceptionWithMissingFunctionExpected()
    {
        return <<<STACK_TRACE
DumboLandingException: Landing failed.

thrown from /Animals/Mammals/Elephants/Dumbo.php on line 34
at \Animals\Mammals\Elephants\Dumbo->checkHealth() called in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->unknown function(90, 30) called in /Animals/Mammals/Elephants/Dumbo.php:12
STACK_TRACE;
    }
    
    /**
     * @return string
     */
    public function getExceptionWithMissingArgsExpected()
    {
        return <<<STACK_TRACE
DumboLandingException: Landing failed.

thrown from /Animals/Mammals/Elephants/Dumbo.php on line 34
at \Animals\Mammals\Elephants\Dumbo->checkHealth() called in /Animals/Mammals/Elephants/Dumbo.php:23
at \Animals\Mammals\Elephants\Dumbo->land() called in /Animals/Mammals/Elephants/Dumbo.php:12
STACK_TRACE;
    }
    
    /**
     * @param string $index
     *
     * @return array
     */
    public function getTraceFixture($index)
    {
        $fixtures = array(
            'DumboLandingException'        => array(
                array(
                    'file'     => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'     => 12,
                    'class'    => '\Animals\Mammals\Elephants\Dumbo',
                    'type'     => '->',
                    'function' => 'land',
                    'args'     => array('angle' => 90, 'speed' => 30),
                ),
                array(
                    'file'     => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'     => 23,
                    'class'    => '\Animals\Mammals\Elephants\Dumbo',
                    'type'     => '->',
                    'function' => 'checkHealth',
                    'args'     => array(),
                ),
            ),
            'DumboHurtException'           => array(
                array(
                    'file'     => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'     => 12,
                    'class'    => '\Animals\Mammals\Elephants\Dumbo',
                    'type'     => '->',
                    'function' => 'land',
                    'args'     => array('angle' => 90, 'speed' => 30),
                ),
                array(
                    'file'     => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'     => 23,
                    'class'    => '\Animals\Mammals\Elephants\Dumbo',
                    'type'     => '->',
                    'function' => 'checkHealth',
                    'args'     => array(),
                ),
                array(
                    'file'     => '/Animals/Mammals/Elephants/Dumbo/Legs.php',
                    'line'     => 45,
                    'class'    => '\Animals\Mammals\Elephants\Dumbo\Leg',
                    'type'     => '->',
                    'function' => 'checkHealth',
                    'args'     => array(),
                )
            ),
            'DumboBrokenLegException'      => array(
                array(
                    'file'     => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'     => 12,
                    'class'    => '\Animals\Mammals\Elephants\Dumbo',
                    'type'     => '->',
                    'function' => 'land',
                    'args'     => array('angle' => 90, 'speed' => 30),
                ),
                array(
                    'file'     => '/Animals/Mammals/Elephants/Dumbo.php',
                    'line'     => 23,
                    'class'    => '\Animals\Mammals\Elephants\Dumbo',
                    'type'     => '->',
                    'function' => 'checkHealth',
                    'args'     => array(),
                ),
                array(
                    'file'     => '/Animals/Mammals/Elephants/Dumbo/Legs.php',
                    'line'     => 45,
                    'class'    => '\Animals\Mammals\Elephants\Dumbo\Legs',
                    'type'     => '->',
                    'function' => 'checkHealth',
                    'args'     => array(),
                ),
                array(
                    'file'     => '/Animals/Mammals/Elephants/Dumbo/Legs.php',
                    'line'     => 163,
                    'class'    => '\Animals\Mammals\Elephants\Dumbo\Legs',
                    'type'     => '->',
                    'function' => 'checkHealthFor',
                    'args'     => array('leg' => 'left_front'),
                ),
                array(
                    'file'     => '/Animals/Mammals/Elephants/Dumbo/Legs.php',
                    'line'     => 163,
                    'class'    => '\Animals\Mammals\Elephants\Dumbo\Legs',
                    'type'     => '->',
                    'function' => 'checkHealthFor',
                    'args'     => array('left_front'),
                ),
                array(
                    'file'     => '/Animals/Mammals/Elephants/Dumbo/Legs/Leg.php',
                    'line'     => 114,
                    'class'    => '\Animals\Mammals\Elephants\Dumbo\Legs\Leg',
                    'type'     => '->',
                    'function' => 'checkHealth',
                    'args'     => array(),
                ),
            ),
            'ShopgateLibraryExceptionStub' => array(
                array(
                    'file'     => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/core.php',
                    'line'     => 1590,
                    'class'    => 'ShopgatePlugin',
                    'type'     => '->',
                    'function' => 'handleRequest',
                    'args'     => array(
                        array(
                            'action'      => 'get_customer',
                            'shop_number' => '23456',
                            'user'        => 'herp@derp.com',
                            'pass'        => 'herpiderp'
                        ),
                    ),
                ),
                array(
                    'file'     => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php',
                    'line'     => 238,
                    'class'    => 'ShopgatePluginApi',
                    'type'     => '->',
                    'function' => 'handleRequest',
                    'args'     => array(
                        array(
                            'action'      => 'get_customer',
                            'shop_number' => '23456',
                            'user'        => 'herp@derp.com',
                            'pass'        => 'herpiderp'
                        ),
                    ),
                ),
                array(
                    'file'     => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php',
                    'line'     => 857,
                    'class'    => 'ShopgatePluginApi',
                    'type'     => '->',
                    'function' => 'getCustomer',
                    'args'     => array(),
                ),
                array(
                    'file'     => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php',
                    'line'     => 80,
                    'class'    => 'ShopgatePluginMyCart',
                    'type'     => '->',
                    'function' => 'getCustomer',
                    'args'     => array('herp@derp.com', 'herpiderp',),
                ),
            ),
            'LoginException'               => array(
                array(
                    'file'     => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/core.php',
                    'line'     => 1590,
                    'class'    => 'ShopgatePlugin',
                    'type'     => '->',
                    'function' => 'handleRequest',
                    'args'     => array(
                        array(
                            'action'      => 'get_customer',
                            'shop_number' => '23456',
                            'user'        => 'herp@derp.com',
                            'pass'        => 'herpiderp'
                        ),
                    ),
                ),
                array(
                    'file'     => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php',
                    'line'     => 238,
                    'class'    => 'ShopgatePluginApi',
                    'type'     => '->',
                    'function' => 'handleRequest',
                    'args'     => array(
                        array(
                            'action'      => 'get_customer',
                            'shop_number' => '23456',
                            'user'        => 'herp@derp.com',
                            'pass'        => 'herpiderp'
                        ),
                    ),
                ),
                array(
                    'file'     => '/var/www/cart/plugins/shopgate/vendor/shopgate/library/classes/apis.php',
                    'line'     => 857,
                    'class'    => 'ShopgatePluginApi',
                    'type'     => '->',
                    'function' => 'getCustomer',
                    'args'     => array(),
                ),
                array(
                    'file'     => '/var/www/cart/plugins/shopgate/plugin.php',
                    'line'     => 80,
                    'class'    => 'ShopgatePluginMyCart',
                    'type'     => '->',
                    'function' => 'getCustomer',
                    'args'     => array('herp@derp.com', 'herpiderp',),
                ),
                array(
                    'file'     => '/var/www/cart/plugins/shopgate/plugin.php',
                    'line'     => 215,
                    'class'    => 'User',
                    'type'     => '->',
                    'function' => 'login',
                    'args'     => array('herp@derp.com', 'herpiderp',),
                ),
            ),
        );
        
        return $fixtures[$index];
    }
    
    /**
     * @param array $values
     *
     * @return PHPUnit_Framework_MockObject_MockObject|ThrowableStub
     */
    private function buildMockFromFixture(array $values)
    {
        $exceptions = array();
        
        do {
            $exception = $this->testCase->getMockBuilder('ThrowableStub')
                                        ->setMockClassName($values['exception_class'])
                                        ->getMock()
            ;
            
            $exception->expects($this->testCase->any())->method('getMessage')->willReturn($values['message']);
            $exception->expects($this->testCase->any())->method('getCode')->willReturn($values['code']);
            $exception->expects($this->testCase->any())->method('getFile')->willReturn($values['file']);
            $exception->expects($this->testCase->any())->method('getLine')->willReturn($values['line']);
            $exception->expects($this->testCase->any())->method('getTrace')->willReturn($values['trace']);
            
            $exceptions[] = $exception;
            
            if (!empty($values['previous'])) {
                $values = $values['previous'];
            } else {
                break;
            }
        } while (true);
        
        $previous = null;
        foreach (array_reverse($exceptions) as $exception) {
            /** @var ThrowableStub|PHPUnit_Framework_MockObject_MockObject $exception */
            $exception->expects($this->testCase->any())->method('getPrevious')->willReturn($previous);
            $previous = $exception;
        }
        
        return $exceptions[0];
    }
    
    /**
     * @param array $values A stack trace fixture as returned by getTraceFixture().
     *
     * @return array A list of stack trace entries with "class", "function" and "arguments" (similar to what Exception::getTrace() returns).
     */
    public function buildMockFromTraceFixture(array $values)
    {
        $arguments = array();
        
        foreach (array_reverse($values) as $trace) {
            $arguments[] = array(
                $trace['class'],
                $trace['function'],
                $trace['args']
            );
        }
        
        return $arguments;
    }
}