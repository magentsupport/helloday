<?php

class Multidots_BoxProducts_Model_Observer {

    /**
     * Manipulate the custom product options
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function checkoutCartProductAddAfter(Varien_Event_Observer $observer) {
        try {
            if (Mage::getSingleton('api2/request')->getApiType() != 'rest') {
                $item = $observer->getQuoteItem();
                $action = Mage::app()->getFrontController()->getAction();
                if ($action->getFullActionName() == 'checkout_cart_add') {
                    if ($action->getRequest()->getParam('extra_options')) {
                        // EXTRA OPTIONS ARE PRESENT, SO LETS ADD IT
                        $item = $observer->getProduct();
                        $additionalOptions = array();
                        $additionalOptions[] = array(
                            'label' => Mage::helper('boxproducts')->getPreOrderText(),
                            'code' => 'additional_options',
                            'value' => $action->getRequest()->getParam('extra_options')
                        );
                        $item->addCustomOption('additional_options', serialize($additionalOptions));
                    }
                }
            }
        } catch (Exception $e) {
            Mage::helper('boxproducts')->insertLog('preorderIssue', $e->getMessage());
            Mage::app()->getResponse()->setRedirect(Mage::getBaseUrl());
        }
    }

    public function salesConvertQuoteItemToOrderItem(Varien_Event_Observer $observer) {
        try {
            $quoteItem = $observer->getItem();
            if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
                $orderItem = $observer->getOrderItem();
                $options = $orderItem->getProductOptions();
                $options['additional_options'] = unserialize($additionalOptions->getValue());
                $orderItem->setProductOptions($options);
            }
        } catch (Exception $e) {
            Mage::helper('boxproducts')->insertLog('preorderIssue', $e->getMessage());
            Mage::app()->getResponse()->setRedirect(Mage::getBaseUrl());
        }
    }

}
