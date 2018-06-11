<?php

/**
 * Class C3_UKShippingTax_Helper_Data
 */
class C3_UKShippingTax_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get the pro-rata'ed shipping tax rate
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return float
     */
    public function calculateProRataShippingTaxRate(Mage_Sales_Model_Quote_Address $address)
    {
        // Base proprtions on inclusive vs exclusive values
        $calcBasedOnInclTax = (Mage::getStoreConfig('tax/calculation/pro_rata_shipping_incl') == 1);
        $store = $address->getQuote()->getStore();

        // Iterate over all items with prices to get proportional rate
        $items = $address->getAllItems();
        $totalPrice = 0.0;
        $totalTax = 0.0;
        foreach ($items as $item) {
            // Skip simple children of configurable products
            if ($item->getParentItemId()) {
                continue;
            }

            // Add to total tax and price (note that logic for $calcBasedOnInclTax *looks* reversed
            // This is correct as price is denominator in rate calculation)
            $totalTax += $item->getTaxAmount();
            $totalPrice += $calcBasedOnInclTax ? $item->getRowTotal() : $item->getRowTotalInclTax();
            $totalPrice -= $item->getDiscountAmount();

            // If discounts include tax, then to get pre-tax amount, remove discount tax
            if (!$calcBasedOnInclTax) {
                $totalPrice += $item->getHiddenTaxAmount();
            }
        }

        // Divide total tax by price in order to get weighted tax rate
        $weightedTaxRate = ($totalPrice > 0.0) ? ($totalTax / $totalPrice) : 0.0;

        // Convert rate to shipping tax rate (invert tax)
        $rate = (1.0 / (1.0 - $weightedTaxRate)) - 1.0;

        return $rate * 100.0;
    }
}
