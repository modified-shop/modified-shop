<?php

/**
 * Shopgate GmbH
 *
 * URHEBERRECHTSHINWEIS
 *
 * Dieses Plugin ist urheberrechtlich geschützt. Es darf ausschließlich von Kunden der Shopgate GmbH
 * zum Zwecke der eigenen Kommunikation zwischen dem IT-System des Kunden mit dem IT-System der
 * Shopgate GmbH über www.shopgate.com verwendet werden. Eine darüber hinausgehende Vervielfältigung, Verbreitung,
 * öffentliche Zugänglichmachung, Bearbeitung oder Weitergabe an Dritte ist nur mit unserer vorherigen
 * schriftlichen Zustimmung zulässig. Die Regelungen der §§ 69 d Abs. 2, 3 und 69 e UrhG bleiben hiervon unberührt.
 *
 * COPYRIGHT NOTICE
 *
 * This plugin is the subject of copyright protection. It is only for the use of Shopgate GmbH customers,
 * for the purpose of facilitating communication between the IT system of the customer and the IT system
 * of Shopgate GmbH via www.shopgate.com. Any reproduction, dissemination, public propagation, processing or
 * transfer to third parties is only permitted where we previously consented thereto in writing. The provisions
 * of paragraph 69 d, sub-paragraphs 2, 3 and paragraph 69, sub-paragraph e of the German Copyright Act shall remain unaffected.
 *
 * @author Shopgate GmbH <interfaces@shopgate.com>
 */

/**
 * Generates a stack trace with obfuscation of arguments and flattening of objects or arrays.
 *
 * In the stack trace, function calls will be presented with arguments, unless those are obfuscated or filtered by the
 * Shopgate_Helper_Logging_Obfuscator passed in the constructor.
 *
 * An argument that is an object will be converted to 'Object'.
 * An argument that is an array will be converted to 'Array'.
 * An argument that is boolean true / false will be converted to 'true' / 'false'.
 */
class Shopgate_Helper_Logging_Stack_Trace_GeneratorDefault
    implements Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface
{
    /** @var Shopgate_Helper_Logging_Obfuscator */
    protected $obfuscator;
    
    /** @var Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderInterface */
    protected $namedParameterProvider;
    
    /** @var array [string, string[]] */
    protected $functionArgumentsCache;
    
    public function __construct(
        Shopgate_Helper_Logging_Obfuscator $obfuscator,
        Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderInterface $namedParameterProvider
    ) {
        $this->obfuscator             = $obfuscator;
        $this->namedParameterProvider = $namedParameterProvider;
        $this->functionArgumentsCache = array();
    }
    
    /**
     * @param Exception|Throwable $exception
     * @param int                 $maxDepth
     *
     * @return string
     */
    public function generate($exception, $maxDepth = 10)
    {
        $formattedHeader = $this->generateFormattedHeader($exception);
        $formattedTrace = $this->generateFormattedTrace($exception->getTrace());
        $messages = array($formattedHeader . "\n" . $formattedTrace);
        
        $depthCounter = 1;
        $previousException = $this->getPreviousException($exception);
        
        while ($previousException !== null && $depthCounter < $maxDepth) {
            $messages[] =
                $this->generateFormattedHeader($previousException, false) . "\n" .
                $this->generateFormattedTrace($previousException->getTrace());
            
            $previousException = $this->getPreviousException($previousException);
            $depthCounter++;
        }
        
        return implode("\n\n", $messages);
    }
    
    
    /**
     * Returns previous exception.
     * Some customers are still running PHP below version 5.3, but method Exception::getPrevious is available since
     * version 5.3. Therefor we check if method is existent, if not method returns null
     *
     * @param Exception|Throwable $exception
     *
     * @return Exception|null
     */
    private function getPreviousException($exception)
    {
        $previousException = null;
        
        if (method_exists($exception, 'getPrevious')) {
            $previousException = $exception->getPrevious();
        }
        
        return $previousException;
    }
    
    /**
     * @param Exception|Throwable $e
     * @param bool                $first
     *
     * @return string
     */
    private function generateFormattedHeader($e, $first = true)
    {
        $prefix = $first
            ? ""
            : "caused by ";
        
        $exceptionClass = get_class($e);
        
        return "{$prefix}{$exceptionClass}: {$e->getMessage()}\n\nthrown from {$e->getFile()} on line {$e->getLine()}";
    }
    
    /**
     * @param array $traces
     *
     * @return string
     */
    private function generateFormattedTrace(array $traces)
    {
        $formattedTraceLines = array();
        $traces              = array_reverse($traces);
        foreach ($traces as $trace) {
            if (!isset($trace['class'])) {
                $trace['class'] = '';
                $trace['type']  = '';
            }
            
            if (!isset($trace['file'])) {
                $trace['file'] = 'unknown file';
                $trace['line'] = 'unknown line';
            }
            
            if (!isset($trace['function'])) {
                $trace['function'] = 'unknown function';
            }
            
            if (!isset($trace['args']) || !is_array($trace['args'])) {
                $trace['args'] = array();
            }
            
            $arguments = $this->namedParameterProvider->get($trace['class'], $trace['function'], $trace['args']);
            $arguments = $this->obfuscator->cleanParamsForLog($arguments);
            
            array_walk($arguments, array($this, 'flatten'));
            $arguments = implode(', ', $arguments);
            
            $formattedTraceLines[] =
                "at {$trace['class']}{$trace['type']}{$trace['function']}({$arguments}) " .
                "called in {$trace['file']}:{$trace['line']}";
        }
        
        return implode("\n", $formattedTraceLines);
    }
    
    /**
     * Function to be passed to array_walk(); will remove sub-arrays or objects.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @post $value contains 'Object' if it was an object before.
     * @post $value contains 'Array' if it was an array before.
     * @pist $value contains 'true' / 'false' if it was boolean true / false before.
     * @post $value is left untouched if it was any other simple type before.
     */
    private function flatten(
        &$value,
        /** @noinspection PhpUnusedParameterInspection */
        $key
    ) {
        if (is_object($value)) {
            $value = 'Object';
        }
        
        if (is_array($value)) {
            $value = 'Array';
        }
        
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
    }
}