<?php

namespace EasyCredit\Transfer;

use EasyCredit\Utility\AnnotationReader;
use EasyCredit\Utility\Inflector;

/**
 * Class AbstractObject
 *
 * @package EasyCredit\Transfer
 */
abstract class AbstractObject implements \ArrayAccess, \Serializable, TransferInterface
{

    /**
     * @var AnnotationReader
     */
    private $annotationReader;
    
    protected $httpStatusCode;

    /**
     * AbstractObject constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->setData($data);
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }
            $this->setValue($key, $value);
        }
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->isEmptyArray($this->toArray());
    }

    /**
     * @TODO REFACTORING
     * @param array $data
     * @return bool
     */
    public function isEmptyArray($data)
    {
        $empty = true;

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $empty = $this->isEmptyArray($v);
            } else {
                $v = (($v != '') ? trim($v) : '');
                if (!empty($v)) {
                    $empty = false;
                }
            }


            if ($empty === false) {
                break;
            }
        }

        return $empty;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @throws \Exception
     */
    public function setValue($key, $value)
    {
        $propertyKey = Inflector::toCamelcase($key);
        if (!property_exists($this, $propertyKey)) {
            throw new \Exception('Property '.$propertyKey.' doesn\'t exists on class '.get_class($this));
        }

        if (!is_object($value) && ($class = $this->getDataClassFromProperty($propertyKey))) {
            $value = new $class($value);
        }

        $this->{'set'.ucfirst($propertyKey)}($value);
    }

    /**
     * @param string $key
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getValue($key)
    {
        $propertyKey = Inflector::toCamelcase($key);
        if (!property_exists($this, $propertyKey)) {
            throw new \Exception('Property '.$propertyKey.' doesn\'t exists on class '.get_class($this));
        }

        return $this->{'get'.ucfirst($propertyKey)}();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = array();
        $reflectionClass = new \ReflectionClass(get_class($this));

        foreach ($reflectionClass->getProperties() as $property) {
            $key = Inflector::toUnderscore($property->getName());
            if (!method_exists($this, 'get'.ucfirst($property->getName()))) {
                continue;
            }
            $value = $this->{'get'.ucfirst($property->getName())}();
            if (is_object($value) && !$value instanceof \DateTime) {
                $value = $value->toArray();
            }
            if (is_object($value) && $value instanceof \DateTime) {
                $value = $value->format(\DateTime::W3C);
            }

            $data[$key] = $value;

        }

        return $data;
    }

    /**
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        $propertyKey = Inflector::toCamelcase($offset);

        return property_exists($this, $propertyKey);

    }

    /**
     * @param string $offset
     *
     * @return null
     */
    public function offsetGet(mixed $offset): mixed
    {
        $propertyKey = Inflector::toCamelcase($offset);

        return $this->{'get'.ucfirst($propertyKey)}();
    }

    /**
     * @param string $offset
     * @param string $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $propertyKey = Inflector::toCamelcase($offset);

        $this->{'set'.ucfirst($propertyKey)}($value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        $propertyKey = Inflector::toCamelcase($offset);
        $this->$propertyKey = null;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->toArray());
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->__construct(unserialize($serialized));
    }

    /**
     * @param array $$data
     */
    public function __unserialize(array $data): void
    {
        $this->__construct($data);
    }

    /**
     * @param string $propertyKey
     * @return string|null
     */
    protected function getDataClassFromProperty($propertyKey)
    {
        $reflactionClass = new \ReflectionClass($this);
        $property = $reflactionClass->getProperty($propertyKey);

        return $this->getAnnotationReader()->getPropertyDoc($property, 'transferClass');
    }

    /**
     * @return AnnotationReader
     */
    protected function getAnnotationReader()
    {
        if ($this->annotationReader) {
            return $this->annotationReader;
        }

        return $this->annotationReader = new AnnotationReader();
    }

    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;
        return $this;
    }
 
}
