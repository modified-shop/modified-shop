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

    public static function LoadByOrdersId($ordersId)
    {
        $ret = new Billpay_Base_Bankdata();
        $fieldData = self::buildStatement('orders_id = ' . (int)$ordersId);
        $ret->setAttributes($fieldData);
        return $ret;
    }

    public static function LoadByTxId($tx_id)
    {
        $ret = new Billpay_Base_Bankdata();
        $fieldData = self::buildStatement('tx_id = "' . mysql_real_escape_string($tx_id) . '"');
        $ret->setAttributes($fieldData);
        return $ret;
    }

    public static function LoadByApiReference($referenceId)
    {
        $ret = new Billpay_Base_Bankdata();
        $fieldData = self::buildStatement('api_reference_id = "' . mysql_real_escape_string($referenceId) . '"');
        $ret->setAttributes($fieldData);
        return $ret;
    }

    /**
     * @param string $referenceId
     * @return mixed
     * @static
     */
    function GetTxIdFromApiReference($referenceId)
    {
        $query = "SELECT tx_id FROM billpay_bankdata WHERE api_reference_id = '$referenceId' LIMIT 1";
        $resource = xtc_db_query($query);
        $data = xtc_db_fetch_array($resource);
        return $data['tx_id'];
    }

    /**
     * Method creates new row in Bankdata, saving basic fields from request.
     *
     * @param ipl_preauthorize_request  $req
     * @param float                     $order_total_gross
     * @param string                    $transaction_id
     * @static
     */
    public static function SaveRequest($req, $order_total_gross, $transaction_id)
    {
        $query_proto = 'INSERT INTO billpay_bankdata
                           (tx_id, account_holder,
                            account_number, bank_code,
                            bank_name,
                            total_amount,
                            api_reference_id)
                    VALUES ("%s", "%s","%s",
                            "%s","%s", "%s",
                            "%s")';
        $query = sprintf($query_proto,
            $transaction_id, $req->get_account_holder(), $req->get_account_number(),
            $req->get_bank_code(), $req->get_bank_name(), $order_total_gross,
            $transaction_id
            );
        xtc_db_query($query);
    }

    /**
     * Method updates existing record with additional data.
     *
     * @param string $txId
     * @param Array $newData
     * @static
     */
    public static function UpdateByTxId($txId, $newData)
    {
        $sets = array();
        foreach ($newData as $key => $val) {
            $sets[] = $key . ' = "'.$val.'"';
        }
        $query_array_string = join(",\n", $sets);
        $query = "UPDATE billpay_bankdata SET $query_array_string WHERE tx_id = '$txId' LIMIT 1";
        xtc_db_query($query);
    }

    public static function BuildStatement($condition)
    {
        $table = self::TABLE;
        $qry = "SELECT * FROM $table WHERE $condition LIMIT 1";
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
    public static function serializeDueDateArray($dueDateArray)
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

    function getInvoiceDueDate()
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

    public function getTotalAmount()
    {
        return $this->getAttribute('total_amount');
    }

    /**
     * Returns number of months that payment credit will last.
     * Ie. If customer borrows money for 12 months, this function will return 12.
     * @return int
     */
    function getCreditLength()
    {
        $duration = $this->getAttribute('duration');
        if (empty($duration)) {
            // fallback for old orders
            $duration = $this->getAttribute('rate_count');
        }
        return $duration;
    }

    /**
     * Returns number of rates.
     * Ie. If customer borrows money for 12 months in Swiss and will pay it in 4 rates, this function will return 4.
     * @return int
     */
    function getRateCount()
    {
        $instalment_count = $this->getAttribute('instalment_count');
        if (empty($instalment_count)) {
            // fallback for old orders
            $instalment_count = $this->getAttribute('rate_count');
        }
        return $instalment_count;
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

    /**
     * Returns VAT tax on credit fee.
     * @param string $country3
     * @return float
     */
    function getFeeTax($country3 = 'DEU')
    {
        switch ($country3) {
            case 'DEU':
                $taxPercent = 19;
                break;
            // AUT is 20%
            // CHE is 8%
            default:
                $taxPercent = 19;
        }
        return $taxPercent * 0.01 * (float)$this->getAttribute('rate_fee') / 100;
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

    // TODO: add method to save token to cache

    /**
     * Checks if token saved in "customerCache" is the same as one created during preauth
     * @param $token
     * @return bool
     */
    function isValidToken($token)
    {
        $customerCache = $this->getCustomerCache();
        if (empty($customerCache['token']) || empty($token)) {
            return false;
        }
        return $customerCache['token'] === $token;
    }

}