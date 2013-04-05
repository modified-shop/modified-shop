<?php
/**
 * Class to build apropriate titles for each payment method
 *
 * PHP Version 5.3
 *
 * @category  Payment
 * @package   KITT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */

/**
 * Title module for the Klarna Integration ToolkiT
 *
 * @category  Payment
 * @package   KITT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KITT_Payment_Title
{

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var string
     */
    protected $_paymentCode;

    /**
     * @var KiTT_Formatter
     */
    protected $_formatter;

    /**
     * @var KiTT_PClassCollection
     */
    protected $_pclassCollection;

    /**
     * @var KiTT_Translator
     */
    protected $_translator;

    /**
     * @var KiTT_Locale
     */
    protected $_locale;

    /**
     * Construct the Title class.
     *
     * Option is an assoc array. Supported fields with default value first.
     * "invoiceFee" 0 or invoice fee
     * "feepos"     title or extra
     * "feeformat"  short or long
     * "nofeepos"   extra or title
     *
     * @param string $paymentCode      invoice, part or spec
     * @param object $formatter        imlpementation of KiTT_Formatter
     * @param object $pclassCollection KiTT_PClassCollection object
     * @param object $translator       KiTT_Translator object
     * @param array  $options          associative array of options
     *
     * @throws InvalidArgumentException if a required argument is
     * @return void
     */
    public function __construct(
        $paymentCode, $locale, $formatter, $pclassCollection, $translator,
        $options = array()
    ) {
        $default = array(
            "invoiceFee" => 0,
            "feepos" => "title",
            "feeformat" => "short",
            "nofeepos" => "extra"
        );
        $this->_options = array_merge($default, $options);

        if ($locale === null) {
            throw new InvalidArgumentException(
                '$locale is not an instance of KiTT_Locale'
            );
        }
        $this->_locale = $locale;

        if (!$formatter instanceof KiTT_Formatter) {
            throw new InvalidArgumentException(
                '$formatter is not an instance of KiTT_Formatter'
            );
        }
        $this->_formatter = $formatter;

        if ($paymentCode == null) {
            throw new InvalidArgumentException(
                '$paymentCode is not set'
            );
        }
        if ($paymentCode != 'part'
            && $paymentCode != 'invoice'
            && $paymentCode != 'spec'
        ) {
            throw new InvalidArgumentException(
                '$paymentCode must be "invoice", "part" or "spec"'
            );
        }
        $this->_paymentCode = $paymentCode;

        if (!$translator instanceof KiTT_Translator) {
            throw new InvalidArgumentException(
                '$translator is not an instance of KiTT_Translator'
            );
        }
        $this->_translator = $translator;

        $this->_pclassCollection = $pclassCollection;
    }

    /**
     * Retrieve the title built from the set options as an associative array
     * with title and extra.
     *
     * @return array
     */
    public function getTitle()
    {
        switch ($this->_paymentCode) {
        case'invoice':
            return $this->_invoiceTitle();
        case'part':
            return $this->_partPaymentTitle();
        case'spec':
            return $this->_specCampaignTitle();
        default:
            throw new KiTT_Exception("Unsupported payment option");
        }
    }

    /**
     * Invoice titles
     *
     * @return array
     */
    private function _invoiceTitle()
    {
        $title = $this->_translator->translate('INVOICE_TITLE');

        if ($this->_options['invoiceFee'] == 0) {
            return $this->_noFeeTitle($title);
        }

        if ($this->_options['feeformat'] == 'short') {
            return $this->_shortFeeTitle($title);
        }

        return $this->_longFeeTitle($title);
    }

    /**
     * Title with no fee
     *
     * @param string $title title from language pack
     *
     * @return array
     */
    private function _noFeeTitle($title)
    {
        $noFee = $this->_translator->translate('NO_INVOICE_FEE');
        if ($this->_options['nofeepos'] == 'extra') {
            return array(
                'title' => str_replace('(+XX)', "", $title),
                'extra' => $noFee
            );
        }
        return array(
            'title' => str_replace('+XX', $noFee, $title),
            'extra' => ""
        );
    }

    /**
     * Title with short invoice fee formatting
     *
     * @param string $title title from language pack
     *
     * @return array
     */
    private function _shortFeeTitle($title)
    {
        $shortFee = $this->_formatter->formatPrice(
            $this->_options['invoiceFee'], $this->_locale
        );
        if ($this->_options['feepos'] == 'title') {
            return array(
                "title" => str_replace('+XX', $shortFee, $title),
                "extra" => ""
            );
        }
        return array(
            "title" => str_replace('(+XX)', "", $title),
            "extra" => $shortFee
        );
    }

    /**
     * Title with long invoice fee formatting
     *
     * @param string $title title from language pack
     *
     * @return array
     */
    private function _longFeeTitle($title)
    {
        $longFee = str_replace(
            "(xx)",
            $this->_formatter->formatPrice(
                $this->_options['invoiceFee'], $this->_locale
            ),
            $this->_translator->translate('format_invoicefee_not_included')
        );
        if ($this->_options['feepos'] == 'title') {
            return array(
                "title" => str_replace('+XX', $longFee, $title),
                "extra" => ""
            );
        }
        return array(
            "title" => str_replace('(+XX)', "", $title),
            "extra" => $longFee
        );
    }

    /**
     * Part payment title
     *
     * @return array
     */
    private function _partPaymentTitle()
    {
        if (count($this->_pclassCollection->pclasses) > 0) {
            $price = $this->_pclassCollection->minimumPClass();
            return array(
                "title" => str_replace(
                    'xx',
                    $price,
                    $this->_translator->translate('PARTPAY_TITLE')
                ),
                "extra" => ""
            );
        }
        return array(
            "title" => $this->_translator->translate('PARTPAY_TITLE_NOSUM'),
            "extra" => ""
        );
    }

    /**
     * Special Campaign title
     *
     * @return array
     */
    private function _specCampaignTitle()
    {
        if (count($this->_pclassCollection->pclasses) > 0) {
            $default = $this->_pclassCollection->defaultPClass();
            return array(
                "title" => $default['description'],
                "extra" => ""
            );
        }
        return array(
            "title" => $this->_translator->translate('SPEC_TITLE'),
            "extra" => ""
        );
    }
}
