<?php

/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
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
class Shopgate_Helper_Logging_Stack_Trace_GeneratorDefault implements Shopgate_Helper_Logging_Stack_Trace_GeneratorInterface
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
        $formattedTrace  = $this->generateFormattedTrace($exception->getTrace());
        $messages        = array($formattedHeader . "\n" . $formattedTrace);

        $depthCounter      = 1;
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
            $value = $value
                ? 'true'
                : 'false';
        }
    }
}
