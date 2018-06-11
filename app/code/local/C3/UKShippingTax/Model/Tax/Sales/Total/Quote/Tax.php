<?php

/**
 * Tax totals calculation model modified to calculate shipping tax correctly for UK
 */
class C3_UKShippingTax_Model_Tax_Sales_Total_Quote_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    /**
     * Tax caclulation for shipping price
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   Varien_Object $taxRateRequest
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _calculateShippingTax(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        // Return parent function if module is not enabled.
        if (Mage::getStoreConfig('tax/calculation/pro_rata_shipping_enabled') != 1) {
            return parent::_calculateShippingTax($address, $taxRateRequest);
        }

        $taxRateRequest->setProductClassId($this->_config->getShippingTaxClass($this->_store));
        $rate = $this->_calculator->getRate($taxRateRequest);
        $inclTax = $address->getIsShippingInclTax();

        // @modification Calculate subtotal theoretically liable for tax and zero-rated portion
        $proRataCountries = explode(',', Mage::getStoreConfig('tax/calculation/pro_rata_shipping_countries'));
        if (in_array($taxRateRequest->getCountryId(), $proRataCountries)){
            $rate = Mage::helper('c3_ukshippingtax')->calculateProRataShippingTaxRate($address);
        }
        // End modification

        $address->setShippingTaxAmount(0);
        $address->setBaseShippingTaxAmount(0);
        $address->setShippingHiddenTaxAmount(0);
        $address->setBaseShippingHiddenTaxAmount(0);
        $appliedRates = $this->_calculator->getAppliedRates($taxRateRequest);
        // @modification Change applied rates
        foreach ($appliedRates as &$appliedRate) {
            $appliedRate['percent'] = $rate;
            if (isset($appliedRate['rates'][0]) && isset($appliedRate['rates'][0]['percent'])) {
                $appliedRate['rates'][0]['percent'] = $rate;
            }
        }
        // End modification
        // This function was introduced in magento 1.8.1.0
        if (method_exists($this, '_calculateShippingTaxByRate')){
            if ($inclTax) {
                $this->_calculateShippingTaxByRate($address, $rate, $appliedRates);
            } else {
                foreach ($appliedRates as $appliedRate) {
                    $taxRate = $appliedRate['percent'];
                    $taxId = $appliedRate['id'];
                    $this->_calculateShippingTaxByRate($address, $taxRate, array($appliedRate), $taxId);
                }
            }
        } else {
            // Magento installation predates 1.8.1.0
            $this->_pre181CalculateShippingTaxByRate($address, $rate, $inclTax, $taxRateRequest);
        }
        return $this;
    }

    /**
     * This function is used when the Magento installation predates 1.8.1.0
     * The logic contained within was refactored into the
     * _calculateShippingTaxByRate in Magento release 1.8.1.0
     *
     * @param $address
     * @param $rate
     * @param $inclTax
     * @param $taxRateRequest
     *
     * @return $this
     */
    protected function _pre181CalculateShippingTaxByRate($address, $rate, $inclTax, $taxRateRequest) {
        $shipping       = $address->getShippingTaxable();
        $baseShipping   = $address->getBaseShippingTaxable();
        $rateKey        = (string)$rate;

        $hiddenTax      = null;
        $baseHiddenTax  = null;
        switch ($this->_helper->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $tax        = $this->_calculator->calcTaxAmount($shipping, $rate, $inclTax, false);
                $baseTax    = $this->_calculator->calcTaxAmount($baseShipping, $rate, $inclTax, false);
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount     = $address->getShippingDiscountAmount();
                $baseDiscountAmount = $address->getBaseShippingDiscountAmount();
                $tax = $this->_calculator->calcTaxAmount(
                    $shipping - $discountAmount,
                    $rate,
                    $inclTax,
                    false
                );
                $baseTax = $this->_calculator->calcTaxAmount(
                    $baseShipping - $baseDiscountAmount,
                    $rate,
                    $inclTax,
                    false
                );
                break;
        }
        $version = Mage::getVersion();
        if ($this->_config->getAlgorithm($this->_store) == Mage_Tax_Model_Calculation::CALC_TOTAL_BASE) {
            // Added in Magento v1.8.0.0
            if (version_compare($version, '1.8.0.0', '>=')){
                $this->_addAmount(max(0, $tax));
                $this->_addBaseAmount(max(0, $baseTax));
            }
            $tax        = $this->_deltaRound($tax, $rate, $inclTax);
            $baseTax    = $this->_deltaRound($baseTax, $rate, $inclTax, 'base');
        } else {
            $tax        = $this->_calculator->round($tax);
            $baseTax    = $this->_calculator->round($baseTax);
            // Added in Magento v1.8.0.0
            if (version_compare($version, '1.8.0.0', '>=')){
                $this->_addAmount(max(0, $tax));
                $this->_addBaseAmount(max(0, $baseTax));
            }
        }

        if ($inclTax && !empty($discountAmount)) {
            $hiddenTax      = $this->_calculator->calcTaxAmount($discountAmount, $rate, $inclTax, false);
            $baseHiddenTax  = $this->_calculator->calcTaxAmount($baseDiscountAmount, $rate, $inclTax, false);
            $this->_hiddenTaxes[] = array(
                'rate_key'   => $rateKey,
                'value'      => $hiddenTax,
                'base_value' => $baseHiddenTax,
                'incl_tax'   => $inclTax,
            );
        }

        // This will apply for Magento versions less than v.1.8.0.0 down to our lowest supported version of 1.6.0.0
        if (version_compare($version, '1.8.0.0', '<')){
            $this->_addAmount(max(0, $tax));
            $this->_addBaseAmount(max(0, $baseTax));
        }
        $address->setShippingTaxAmount(max(0, $tax));
        $address->setBaseShippingTaxAmount(max(0, $baseTax));
        $applied = $this->_calculator->getAppliedRates($taxRateRequest);
        $this->_saveAppliedTaxes($address, $applied, $tax, $baseTax, $rate);

        return $this;
    }
}
