<?php
/**
 * object which contains the shop system specific config
 *
 * @category   Billpay
 * @package    Billpay\Base\Bankdata
 * @link       https://www.billpay.de/
 */
class Billpay_Base_Bankdata
{
    const TABLE = 'billpay_bankdata';

    /**
     * contains the data loaded from the database
     *
     * @access private
     * @var array
     */
    var $_attributes = array();

    function loadByOrdersId($ordersId)
    {
        $fieldData = $this->buildStatement('orders_id = ' . (int)$ordersId);
        $this->setAttributes($fieldData);

        return $this;
    }

    function loadByApiReference($referenceId)
    {
        $fieldData = $this->buildStatement('api_reference_id = "' . mysql_real_escape_string($referenceId) . '"');
        $this->setAttributes($fieldData);

        return $this;
    }

    function buildStatement($condition)
    {
        $qry = 'SELECT *
                FROM ' . self::TABLE . '
                WHERE ' . $condition . '
                LIMIT 1';

        $resource = xtc_db_query($qry);
        $data = xtc_db_fetch_array($resource);

        return $data;
    }

    function setAttributes($dataArray)
    {
        $this->_attributes = $dataArray;

        return $this;
    }

    function hasAttributes()
    {
        return empty($this->_attributes) === false;
    }

    function getAttributes()
    {
        return $this->_attributes;
    }

    function setAttribute($key, $value)
    {
        $this->_attributes[$key] = $value;

        return $this;
    }

    function hasAttribute($key)
    {
        return isset($this->_attributes[$key]);
    }

    function getAttribute($key, $default = null)
    {
        if ($this->hasAttribute($key)) {
            return $this->_attributes[$key];
        }

        return $default;
    }

    /**
     * Create a string representation from special formatted array that can be stored in the database
     *
     * Result:
     * Example data (incl. date): 20110305#8415:20110405#6211:20110505#6211:20110605#6211:20110705#6211:20110805#6211
     * Example data (before activation): #8415:#6211:#6211:#6211:#6211:#6211
     *
     * @param array $dueDateArray
     *
     * @return string
     */
    function serializeDueDateArray($dueDateArray)
    {
        $serializedDueDateList = '';
        foreach ($dueDateArray as $entry) {
            if (empty($serializedDueDateList) === false) {
                $serializedDueDateList .= ':';
            }
            $date = $entry['date'] ? $entry['date'] : '';
            $serializedDueDateList .= $date . '#' . $entry['value'];
        }
        return $serializedDueDateList;
    }

    /**
     * Create array representation out of serialized due date string (Format specification input param see 'serializeDueDateArray')
     *
     * @param $serializedDueDates
     *
     * @return array
     */
    function unserializeDueDates($serializedDueDates)
    {
        $dueListParts = explode(":", $serializedDueDates);

        $result = array();
        foreach ($dueListParts as $entry) {
            $entryParts = explode("#", $entry);

            $result[] = array(
                'date'  => $entryParts[0],
                'value' => $entryParts[1]
            );
        }
        return $result;
    }

    function getApiReferenceId()
    {
        return $this->getAttribute('api_reference_id');
    }

    function getAccountHolder()
    {
        return $this->getAttribute('account_holder');
    }

    function getAccountNumber()
    {
        return $this->getAttribute('account_number');
    }

    function getBankCode()
    {
        return $this->getAttribute('bank_code');
    }

    function getBankName()
    {
        return $this->getAttribute('bank_name');
    }

    function getInvoiceReference()
    {
        return $this->getAttribute('invoice_reference');
    }

    function getInvoiceDueData()
    {
        return $this->getAttribute('invoice_due_date');
    }

    function getTxId()
    {
        return $this->getAttribute('tx_id');
    }

    function getOrdersId()
    {
        return $this->getAttribute('orders_id');
    }

    function getRateSurcharge()
    {
        return $this->getAttribute('rate_surcharge');
    }

    function getRateTotalAmount()
    {
        return $this->getAttribute('rate_total_amount');
    }

    function getRateCount()
    {
        return $this->getAttribute('rate_count');
    }

    function getRateDues()
    {
        $rateDuesRaw = $this->getRateDuesRaw();
        return $this->unserializeDueDates($rateDuesRaw);
    }

    function getRateDuesRaw()
    {
        return $this->getAttribute('rate_dues');
    }

    function hasRateDues()
    {
        return count($this->getRateDues()) > 0;
    }

    function getInterestRate()
    {
        return $this->getAttribute('rate_interest_rate');
    }

    function getAnnualRate()
    {
        return $this->getAttribute('rate_anual_rate');
    }

    function getRateBaseAmount()
    {
        return $this->getAttribute('rate_base_amount');
    }

    function getFee()
    {
        return $this->getAttribute('rate_fee');
    }

    function getFeeTax()
    {
        return $this->getAttribute('rate_fee_tax');
    }

    function getPrePayment()
    {
        return $this->getAttribute('prepayment_amount');
    }

    function getAdditionalCosts()
    {
        if ($this->getRateTotalAmount() === null) {
            return 0;
        }

        return $this->getRateTotalAmount()
             - $this->getRateSurcharge()
             - $this->getFee()
             - $this->getRateBaseAmount()
             - $this->getPrePayment();
    }

    function getCustomerCacheRaw()
    {
        return $this->getAttribute('customer_cache');
    }

    function getCustomerCache()
    {
        $customerCacheRaw = $this->getCustomerCacheRaw();
        if (empty($customerCacheRaw) === false
            && ($customerCache = unserialize($customerCacheRaw)) !== false
        ) {
            return $customerCache;
        }
        return array();
    }
}