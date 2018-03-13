<?php


namespace EasyCredit\Transfer;

/**
 * Class RiskRelatedInfo
 *
 * @package EasyCredit\Transfer
 */
class RiskRelatedInfo extends AbstractObject
{

    /**
     * @const
     */
    const CUSTOMER_STATUS_NEW_CUSTOMER = 'NEUKUNDE';
    /**
     * @const
     */
    const CUSTOMER_STATUS_EXISTING_CUSTOMER = 'BESTANDSKUNDE';
    /**
     * @const
     */
    const CUSTOMER_STATUS_PREMIUM = 'PREMIUMKUNDE';

    /**
     * @const
     */
    const NEGATIVE_PAYMENT_INFORMATION_NO_NEGATIV_INFORMATION = 'KEINE_ZAHLUNGSSTOERUNGEN';

    /**
     * @const
     */
    const NEGATIVE_PAYMENT_INFORMATION_DELAY_INFORMATION = 'ZAHLUNGSVERZOEGERUNG';

    /**
     * @const
     */
    const NEGATIVE_PAYMENT_INFORMATION_NO_INFORMATION = 'KEINE_INFORMATION';

    /**
     * @const
     */
    const NEGATIVE_PAYMENT_INFORMATION_NEGATIV_INFORMATION = 'ZAHLUNGSAUSFALL';

    /**
     * @var string
     * @apiName kundenstatus
     */
    protected $customerStatus;

    /**
     * @var \DateTime
     * @apiName   kundeSeit
     * @apiFormat Y-m-d
     */
    protected $customerRegistrationDate;

    /**
     * @var boolean
     * @apiName bestellungErfolgtUeberLogin
     */
    protected $customerRegistrated;

    /**
     * @var integer
     * @apiName anzahlProdukteImWarenkorb
     */
    protected $cartItemsCount;

    /**
     * @var integer
     * @apiName anzahlBestellungen
     */
    protected $orderCount;

    /**
     * @var string
     * @apiName negativeZahlungsinformation
     */
    protected $negativePaymentInformation;

    /**
     * @var boolean
     * @apiName risikoartikelImWarenkorb
     */
    protected $riskItemInCart;
    
    /**
     * @var string
     * @apiName logistikDienstleister
     */
    protected $logisticServiceProvider;

    /**
     * @return string
     */
    public function getCustomerStatus()
    {
        return $this->customerStatus;
    }

    /**
     * @param string $customerStatus
     */
    public function setCustomerStatus($customerStatus)
    {
        $this->customerStatus = $customerStatus;
    }

    /**
     * @param string|null $format
     * @return \DateTime|string
     */
    public function getCustomerRegistrationDate($format = null)
    {
        if ($format !== null && $this->customerRegistrationDate instanceof \DateTime) {
            return $this->customerRegistrationDate->format($format);
        }

        return $this->customerRegistrationDate;
    }

    /**
     * @param \DateTime $customerRegistrationDate
     */
    public function setCustomerRegistrationDate($customerRegistrationDate)
    {
        $this->customerRegistrationDate = $customerRegistrationDate;
    }

    /**
     * @return boolean
     */
    public function getCustomerRegistrated()
    {
        return $this->customerRegistrated;
    }

    /**
     * @param boolean $customerRegistrated
     */
    public function setCustomerRegistrated($customerRegistrated)
    {
        $this->customerRegistrated = $customerRegistrated;
    }

    /**
     * @return int
     */
    public function getCartItemsCount()
    {
        return $this->cartItemsCount;
    }

    /**
     * @param int $cartItemsCount
     */
    public function setCartItemsCount($cartItemsCount)
    {
        $this->cartItemsCount = $cartItemsCount;
    }

    /**
     * @return int
     */
    public function getOrderCount()
    {
        return $this->orderCount;
    }

    /**
     * @param int $orderCount
     */
    public function setOrderCount($orderCount)
    {
        $this->orderCount = $orderCount;
    }

    /**
     * @return string
     */
    public function getNegativePaymentInformation()
    {
        return $this->negativePaymentInformation;
    }

    /**
     * @param string $negativePaymentInformation
     */
    public function setNegativePaymentInformation($negativePaymentInformation)
    {
        $this->negativePaymentInformation = $negativePaymentInformation;
    }

    /**
     * @return boolean
     */
    public function getRiskItemInCart()
    {
        return $this->riskItemInCart;
    }

    /**
     * @param boolean $riskItemInCart
     */
    public function setRiskItemInCart($riskItemInCart)
    {
        $this->riskItemInCart = $riskItemInCart;
    }

    /**
     * @return string
     */
    public function getLogisticServiceProvider()
    {
        return $this->logisticServiceProvider;
    }

    /**
     * @param string $logisticServiceProvider
     */
    public function setLogisticServiceProvider($logisticServiceProvider)
    {
        $this->logisticServiceProvider = $logisticServiceProvider;
    }
 
}
