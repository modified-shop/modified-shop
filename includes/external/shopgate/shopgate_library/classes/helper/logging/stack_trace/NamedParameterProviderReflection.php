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
 * Tries to get the argument names from a function and map the actual values it was called with to them using Reflection.
 */
class Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderReflection
    implements Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderInterface
{
    /** @var array [string, ReflectionParameter[]] */
    protected $functionArgumentsCache;
    
    public function __construct()
    {
        $this->functionArgumentsCache = array();
    }
    
    public function get($className, $functionName, array $arguments)
    {
        if (!empty($className) && !class_exists($className)) {
            return $arguments;
        }
        
        if (!$this->exists($className, $functionName)) {
            return $arguments;
        }
        
        $fullFunctionName                                = $this->getFullFunctionName($className, $functionName);
        $this->functionArgumentsCache[$fullFunctionName] = array();
        
        $namedArguments = $this->getNamedArguments($className, $functionName, $arguments);
        
        for ($i = count($namedArguments); $i < count($arguments); $i++) {
            $namedArguments['unnamed argument ' . $i] = $arguments[$i];
        }
        
        return $namedArguments;
    }
    
    /**
     * @param string  $className
     * @param string  $functionName
     * @param mixed[] $arguments The list of arguments.
     *
     * @return array [string, mixed] An array of the arguments with named indices according to function parameter names.
     */
    private function getNamedArguments($className, $functionName, array $arguments)
    {
        $fullFunctionName = $this->getFullFunctionName($className, $functionName);
        
        if (empty($this->functionArgumentsCache[$fullFunctionName])) {
            $this->functionArgumentsCache[$fullFunctionName] =
                $this->buildReflectionFunction($className, $functionName)->getParameters();
        }
        
        $i              = 0;
        $namedArguments = array();
        foreach ($this->functionArgumentsCache[$fullFunctionName] as $parameter) {
            /** @var ReflectionParameter $parameter */
            try {
                $defaultValue = '[defaultValue:' . $this->sanitize($parameter->getDefaultValue()) . ']';
            } catch (ReflectionException $e) {
                $defaultValue = '';
            }
            
            $namedArguments[$parameter->getName()] = isset($arguments[$i]) ? $arguments[$i] : $defaultValue;
            $i++;
        }
        
        return $namedArguments;
    }
    
    /**
     * @param string $className
     * @param string $functionName
     *
     * @return string
     */
    private function getFullFunctionName($className, $functionName)
    {
        return empty($className)
            ? $functionName
            : $className . '::' . $functionName;
    }
    
    /**
     * @param string $className
     * @param string $functionName
     *
     * @return ReflectionFunctionAbstract
     */
    private function buildReflectionFunction($className, $functionName)
    {
        return (empty($className))
            ? new ReflectionFunction($this->getFullFunctionName($className, $functionName))
            : new ReflectionMethod($className, $functionName);
    }
    
    /**
     * @param string $className
     * @param string $functionName
     *
     * @return bool
     */
    private function exists($className, $functionName)
    {
        return
            function_exists($this->getFullFunctionName($className, $functionName))
            || method_exists($className, $functionName);
    }
    
    /**
     * @param mixed $value
     *
     * @return string
     */
    private function sanitize($value)
    {
        if ($value === null) {
            $value = 'null';
        }
        
        if (is_array($value)) {
            $value = 'array';
        }
        
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        
        return $value;
    }
}