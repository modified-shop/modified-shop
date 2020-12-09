<?php

namespace EasyCredit\Api;

use EasyCredit\Transfer\AbstractCollection;
use EasyCredit\Transfer\AbstractObject;
use EasyCredit\Transfer\TransferInterface;
use EasyCredit\Utility\AnnotationReader;
use EasyCredit\Utility\Inflector;

/**
 * Class DataMapper
 *
 * @package Api\Installment
 */
class DataMapper
{
    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * DataMapper constructor.
     * @param object|null $annotationReader
     */
    public function __construct($annotationReader = null)
    {
        if (!$annotationReader) {
            $annotationReader = new AnnotationReader();
        }
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param TransferInterface $transfer
     * @param array             $data
     * @param integer           $statusCode
     *
     * @return TransferInterface
     */
    public function mapResponse(TransferInterface $transfer, $data, $statusCode = null)
    {
        $transferProperties = $this->annotationReader->getProperties($transfer, 'apiName');
        foreach ($transferProperties as $transferProperty => $annotations) {
            $apiName = trim($annotations['apiName']);
            if (!isset($data[$apiName])) {
                continue;
            }
            $value = $data[$apiName];
            
            if (isset($annotations['transferClass']) && substr($annotations['transferClass'], -10) == 'Collection') {
                $value = $this->mapResponseCollection($annotations['transferClass'], $value);
            } elseif (isset($annotations['transferClass'])) {
                $transferClass = trim($annotations['transferClass']);
                $value = $this->mapResponse(new $transferClass(), $value);
            }

            $transfer->{'set'.Inflector::classify($transferProperty)}($value);
        }
        
        return $transfer;
    }

    /**
     * @param TransferInterface $transfer
     *
     * @return array
     */
    public function mapRequest(TransferInterface $transfer)
    {
        $data = array();

        $transferProperties = $this->annotationReader->getProperties($transfer, 'apiName');
        foreach ($transferProperties as $transferProperty => $annotations) {
            $value = $this->mapValue($transfer, $transferProperty, $annotations);

            if ($value !== null) {
                $data[trim($annotations['apiName'])] = $value;
            }
        }

        return $data;
    }

    /**
     * @param TransferInterface $transfer
     * @param string            $transferProperty
     * @param array             $annotations
     *
     * @return mixed
     */
    protected function mapValue(TransferInterface $transfer, $transferProperty, $annotations)
    {
        $getter = 'get'.ucfirst($transferProperty);
        if (isset($annotations['apiFormat'])) {
            $value = $transfer->$getter(trim($annotations['apiFormat']));
        } else {
            $value = $transfer->$getter();
        }

        if ($value instanceof AbstractObject && $value->isEmpty()) {
            $value = null;
        } else {
            $value = $this->mapValueObject($value, $annotations);
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param array $annotations
     *
     * @return mixed
     */
    protected function mapValueObject($value, $annotations)
    {
        if (empty($value) || !isset($annotations['transferClass'])) {
            return $value;
        }

        if (substr($annotations['transferClass'], -10) == 'Collection') {
            $value = $this->mapRequestCollection($value);
        } else {
            $value = $this->mapRequest($value);
        }

        return $value;
    }

    /**
     * @param string $transferClassName
     * @param array  $data
     *
     * @return AbstractCollection
     */
    protected function mapResponseCollection($transferClassName, $data)
    {
        $collection = new $transferClassName();
        if (!is_array($data)) {
            return $collection;
        }
        foreach ($data as $item) {
            $itemClass = $collection->createObjectClass();
            $this->mapResponse($itemClass, $item);
            $collection->addItem($itemClass);
        }

        return $collection;
    }

    /**
     * @param AbstractCollection $transferCollection
     *
     * @return array
     */
    protected function mapRequestCollection(AbstractCollection $transferCollection)
    {
        $data = array();

        foreach ($transferCollection as $transfer) {
            $data[] = $this->mapRequest($transfer);
        }

        return $data;
    }
}
