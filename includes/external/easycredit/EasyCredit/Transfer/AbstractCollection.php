<?php

namespace EasyCredit\Transfer;

/**
 * Class AbstractCollection
 *
 * @package EasyCredit\Transfer
 */
abstract class AbstractCollection implements \IteratorAggregate, \Countable, \ArrayAccess, TransferInterface
{

    /**
     * @var AbstractObject[]
     */
    protected $items = array();

    /**
     * @var string
     */
    protected $objectClassName;

    /**
     * AbstractCollection constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = array())
    {
        $this->setItems($items);
    }

    /**
     * @param array $data
     *
     * @return AbstractObject
     */
    public function createObjectClass($data = array())
    {
        $class = $this->objectClassName;

        return new $class($data);
    }

    /**
     * Replace the Items
     *
     * @param AbstractObject[] $items
     */
    public function setItems(array $items)
    {
        $this->items = array();
        foreach ($items as $item) {
            if (is_array($item)) {
                $item = $this->createObjectClass($item);
            }
            $this->addItem($item);
        }
    }

    /**
     * @param AbstractObject $object
     *
     * @throws \Exception
     */
    public function addItem(AbstractObject $object)
    {
        if (!$object instanceof $this->objectClassName) {
            throw new \Exception("Cannot add item. Class ".$this->objectClassName." required");
        }
        $this->items[] = $object;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param int $offset
     *
     * @return AbstractObject|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->offsetExists($offset) ? $this->items[$offset] : null;
    }

    /**
     * @param int $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param int $offset
     * @param int $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items[$offset] = $value;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = array();

        foreach ($this->items as $item) {
            $data[] = $item->toArray();
        }

        return $data;
    }
}
