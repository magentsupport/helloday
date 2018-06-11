<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Rma
 */

class Amasty_Rma_Model_System_Config_Source_Statuses
{
    protected $_options;
        
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = array_merge(
                array(Amasty_Rma_Model_Item::ALL_STATUES_RETURN_MODE => Mage::helper('amrma')->__('-- Allow return for all statuses --')),
                Mage::getResourceModel('sales/order_status_collection')->toOptionArray()
            );
        }
        return $this->_options;
    }
}
