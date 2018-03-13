<?php

namespace EasyCredit\Utility;

use EasyCredit\Transfer\TransferInterface;

/**
 * Class AnnotationReader
 *
 * @package EasyCredit\Installment
 */
class AnnotationReader
{

    /**
     * @var string
     */
    protected $keyPattern = "[A-z0-9\_\-]+";

    /**
     * @var string
     */
    protected $endPattern = "[ ]*(?:@|\r\n|\n)";

    /**
     * @param object      $object
     * @param string|null $withDocBlockType
     *
     * @return array
     */
    public function getProperties($object, $withDocBlockType = null)
    {
        $reflectionClass = new \ReflectionClass($object);
        $reflectionProperties = $reflectionClass->getProperties();

        $properties = array();

        foreach ($reflectionProperties as $reflectionProperty) {
            $docBlockParams = $this->parsePropertyDocBlock($reflectionProperty);
            if ($withDocBlockType !== null && !isset($docBlockParams[$withDocBlockType])) {
                continue;
            }
            $properties[$reflectionProperty->getName()] = $docBlockParams;
        }

        return $properties;
    }

    /**
     * @param \ReflectionProperty $property
     * @param string              $key
     *
     * @return string|null
     */
    public function getPropertyDoc(\ReflectionProperty $property, $key)
    {
        $docBlock = $this->parsePropertyDocBlock($property);

        return isset($docBlock[$key]) ? $docBlock[$key] : null;
    }

    /**
     * @param \ReflectionProperty $property
     *
     * @return array
     */
    protected function parsePropertyDocBlock(\ReflectionProperty $property)
    {
        $docBlock = $property->getDocComment();

        $docBlockContent = array();
        $pattern = "/@(?=(.*)".$this->endPattern.")/U";

        preg_match_all($pattern, $docBlock, $matches);

        foreach ($matches[1] as $parameter) {
            if (preg_match("/^(".$this->keyPattern.") (.*)$/", $parameter, $match)) {
                $docBlockContent[$match[1]] = $match[2];
            }
        }

        return $docBlockContent;
    }
}
